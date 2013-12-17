/**
	Editor de texto de la GUI

	@author Antonio Juan Sánchez Martín
	@class Editor
	@version 0.9
	@param {Controlador} contenedor - Referencia al componente padre
*/
		

function Editor (controlador)
{ 
		// --------------------------------------------------------
		// Creación de la ventana en el body
		// --------------------------------------------------------

		// $id de la ventana
		$("body").append ('<div id="editor_capa"><div class="scroll-pane"><div id="solapas"></div></div><div id="editor_contenedor" class="editor"></div></div>');	
		// --------------------------------------------------------
		var editor 					= $("#editor_contenedor");
		var contenedor				= $("#editor_capa");
		var solapas 				= $("#solapas");
		var controlador				= controlador;
		var autoref					= this;				// Autoreferencia del objeto, útil para llamadas dentro de funciones
		var mieditor				= null;				// Editor de códigos. En prinpcio no está creado, se crea bajo demanda 
		// --------------------------------------------------------
		// Control de solapas
		var id_editor_activo		= -1;				// ID para solapa creada
		var lista_editores			= new Array(); 		// Para saber a qué editor corresponde el archivo
														// Contiene ( [archivo] = [id, contenido] )
		var num_editores			= 0; 				// Para saber cuántos archivos editados hay
		
		//	-----------------------------------------------------		
		//	Cerrar documento
		//	-----------------------------------------------------			


		/**
			Cierra una pestaña del editor
		    @function cerrarEditor 
		    @param {string} archivo Nombre del archivo
		    @memberof Editor
		*/
		this.cerrarEditor = function (archivo)
		{
   			// Cerramos la solapa y el editor que no usamos
   			if (lista_editores[archivo])
    		if ($('#capa_solapa_'+lista_editores[archivo][0]))
    		{
	    		$('#capa_solapa_'+lista_editores[archivo][0]).remove();
	    						// Borramos de la lista de activos
				
				lista_editores[archivo] = null;

				id_editor_activo = -1; // Inactivos todos	
				num_editores --;
	    		
	    		// Buscamos una capa nueva que activar
	    		// Si hay más solapas abiertas, dejamos abierta la primera que se se abrió
	    		if (num_editores > 0)
	    		{
	    			for (var abierto in lista_editores)
					{
						if (lista_editores[abierto] != null)
						{
	    					// activamos la siguiente solapa
	    					this.activarEditor (abierto);
	    					break;
	    				}
	    			}
	    		}
	    		else 
	    		{
	    			mieditor 			= null;
	    			$("#editor_codigo").remove();
	    			
	    		}
	    		solapas.width(num_editores*230+"px");
    		}
		}


		/**
			Crea una pestaña para un archivo nuevo
		    @function crearEditor
		    @param {string} archivo Nombre del archivo
		    @param {string} contenido Contenido del archivo
		    @param {string} [línea] Línea que debe aparecer marcada
		    @memberof Editor
		*/
		this.crearEditor = function (archivo, contenido, linea)
		{
			if (archivo != undefined)
			{
				// Nombre corto para la solapa
				var nombre_fichero = archivo.substring(archivo.lastIndexOf("\/")+1,archivo.length);

				// --------------------------------------------------------
				// Actualizamos índices
				var id_editor  				= ++num_editores; 	// Solo crece
				lista_editores[archivo] 	= [ id_editor, contenido ];
				
				// --------------------------------------------------------
				// Creamos una nueva solapa al principio				
				solapas.prepend ('<div class="solapa" id="capa_solapa_'+id_editor+'"><input type="button" id="solapa_'+id_editor+'" 	\
					 class="nombre" value="'+nombre_fichero+'" rel="'+id_editor+'" ref="'+archivo+'" ><input rel="'+id_editor+'" 		\
					 class="cerrar" id="solapa_cerrar_'+id_editor+'" type="button"></div>');
				$('#capa_solapa_'+id_editor).addClass('solapa_activa');
				$('#capa_solapa_'+id_editor).hide();
				$('#capa_solapa_'+id_editor).show(200);
					

				// --------------------------------------------------------
				$('#solapa_'+id_editor).click (function() 
						{ 
							var id_capa = $(this).attr('rel');
							autoref.activarEditor (archivo);
						});

				$('#solapa_cerrar_'+id_editor).click (function() 
						{ 
							var id_capa = $(this).attr('rel');

							AttentionBox.showMessage( _("Está cerrando el documento sin guardarlo"),
							{
							    buttons : 
							    [
							        { caption : _("Cancelar"), cancel: true },
							        { caption : _("Cerrar sin guardar") },
							        { caption : _("Guardar") }
							    ],
								callback: function(action, inputs)
	    						{
	    							if (action ==  _("Cancelar"))
	    							{
	    								// No se hace nada, se cierra el diálogo y listo
	    							}
	    							else if (action ==  _("Guardar"))
	    							{
	    								// Se guarda el archivo
	    								var micontenido = null;
	    								var miarchivo 	= null;

	    								// Si el archivo no está cargado en el editor, se busca en los arrays
	    								if (id_editor 	!= id_editor_activo)
	    								{
	    									miarchivo = archivo;
	    									contenido = lista_editores[archivo][1];
	    								}
	    								var error = controlador.guardarArchivo(miarchivo, micontenido);
	    								// y cerramos
	    								if (error == 0) controlador.cerrarArchivo(archivo);
	    							}
	    							else if (action ==  _("Cerrar sin guardar"))
	    							{
	    								// Cerramos
	    								controlador.cerrarArchivo(archivo);
	    							}
	    						}
	    					});

						});

				// --------------------------------------------------------				
				// Ampliamos la capa para generar la scrollbar en caso de ser necesario
				solapas.width(num_editores*230+"px");


				// Creamos un nuevo editor
				if (num_editores == 1)
				{
					editor.append ('<pre id="editor_codigo" class="pre_editor"></pre>');
					mieditor = ace.edit('editor_codigo'); 	
				}
				
				// Aplicación del tema del editor
				mieditor.setTheme("ace/theme/twilight");
				
				// Cargamos el contenido
				this.activarEditor (archivo, linea);
			}
		}


		/**
			Muestra la pestaña ya abierta del archivo indicado
		    @function activarEditor 
		    @param {string} archivo Nombre del archivo
		    @param {string} [línea] Línea que debe aparecer marcada
		    @memberof Editor
		*/
		this.activarEditor = function (archivo, linea)
		{
			if (lista_editores[archivo] !=undefined)
			{
				id_editor = lista_editores[archivo][0]; // Buscamos el id en la lista
				if (id_editor != id_editor_activo)
				{
					// Desactiva la marcada y activa la nueva
					// desactivamos la solapa vieja
					if (id_editor_activo != -1)
					{
						$('#capa_solapa_'+id_editor_activo).removeClass('solapa_activa');
						// guardamos los valores del viejo
						var archivo_antiguo = $('#solapa_'+id_editor_activo).attr('ref');
						lista_editores[archivo_antiguo] = [ id_editor_activo, mieditor.getValue() ];

					}
					$('#capa_solapa_'+id_editor).addClass('solapa_activa');
					var archivo_nuevo = $('#solapa_'+id_editor).attr('ref');
					// activamos el editor nuevo
					mieditor.setValue (lista_editores[archivo_nuevo][1], 0);
					mieditor.clearSelection();
	           		mieditor.getSelection().moveCursorFileStart ();
					
					// Marcamos la nueva como activa
					id_editor_activo = id_editor;
				}

				if (linea != null) 
				{
					mieditor.getSelection().moveCursorTo (linea-1, 0);
					mieditor.getSelection().setSelectionAnchor (linea-1, 0);
					mieditor.getSelection().selectLine();
				} 

				mieditor.getSession().setMode("ace/mode/"+this.conseguirModoTexto(archivo));		
			}
		}

		/**
			Devuelve el nombre del archivo que está en edición
		    @function archivoActivo 
		    @memberof Editor
		*/
		this.archivoActivo = function ()
		{
			if (id_editor_activo != -1)
			{
				return $('#solapa_'+id_editor_activo).attr('ref');
			}
			return null;
		}

		/**
			Devuelve una referencia al contenedor padre
		    @function contenedor
		    @memberof Editor
		*/
		this.contenedor = function () { return contenedor; }

		/**
			Devuelve el contenido del archivo que está en edición
		    @function contenedor
		    @memberof Editor
		*/
		this.contenidoActivo = function ()
		{
			if (id_editor_activo != -1)
			{
				return mieditor.getValue();
			}
			return null;
		}

		//	-----------------------------------------------------		
		//	Para controlar los tipos de archivo
		// -----------------------------------------------------			
		
		/**
			Devuelve el tipo de coloración de sintaxis del archivo indicado
		    @function conseguirModoTexto
		    @param {string} archivo Nombre del archivo
		    @memberof Editor
		*/
		this.conseguirModoTexto = function (archivo)
		{
			var extension = archivo.substring(archivo.lastIndexOf(".")+1,archivo.length);

			if (extension == 'java') 								return 'java';
			else if (extension == 'html') 							return 'html';
			else if (extension == 'js') 							return 'javascript';
			else if (extension == 'jsp') 							return 'jsp';
			else if (extension == 'sh') 							return 'sh';
			else if ((extension == 'c') || (extension == 'cpp'))	return 'c_cpp';
			else if (extension == 'jade') 							return 'jade';
			else if (extension == 'perl') 							return 'perl';
			else if (extension == 'json') 							return 'json';
			else if (extension == 'php') 							return 'php';
			else return 'text';
		}

		// Detección de eventos de teclado
		// Ctrl + S

		/**
			Registra los eventos de teclado para la asignación de teclas rápidas
			@function detectarTeclas
		    @param {string} evento Evento de teclado
		    @memberof Editor
		*/
		this.detectarTeclas = function (event)
		{
			if (!((tecla = event.which) && event.ctrlKey) && !(event.which == 19)) return true;
		    
		    switch(tecla)
		    {
		    	case 17: // Ctrl + Q
		    		controlador.salir ();
		    		break;
		    	case 18: // Ctrl + R
		    		controlador.ejecutar('ejecucion');
		    		break;
		    	case 19: // Ctrl + S
		    		controlador.guardarArchivo ();
		    		break;
		    	case 20: // Ctrl + T
		    		controlador.ejecutar('compilacion');
		    		break;	
		    	default:
		    		return true;
		    }
		    return false;
		}

		



}