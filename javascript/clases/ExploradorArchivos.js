/**
 	Explorador de archivos de la GUI
	
	@author Antonio Juan Sánchez Martín
	@class ExploradorArchivos
	@version 0.9
	@param {Contenedor} contenedor - Referencia al componente padre
*/		


function ExploradorArchivos (controlador)
{ 
		// --------------------------------------------------------
		// Creación del explorador en el body
		// --------------------------------------------------------
		// Creación de la capa del menú contextual para archivos
		$("body").append ('<div id="menucontextual" class="contextMenu"><ul><li id="descargar"><img src="imagenes/descargar.png" />'+_('Descargar')+'</li><li id="eliminar"><img src="imagenes/eliminar.png" />'+_('Eliminar')+'</li><li id="renombrar"><img src="imagenes/renombrar.png" />'+_('Renombrar / Mover')+'</li></ul></div>');
		$("#menucontextual").hide ();
		// $id de la explorador
		$("body").append ('<div id="explorador_archivos_capa"><div id="input_buscador_archivos_capa"><input id="buscador" type="text"  /> </div><div class="scroll-pane"><div id="explorador_archivos"> </div> </div> </div>');	
		// --------------------------------------------------------
		var explorador				= $("#explorador_archivos");	
		var buscador				= $("#buscador");
		var contenedor				= $("#explorador_archivos_capa");	
		var carga					= new Cargador ("explorador_archivos_carga", contenedor); 
		var controlador				= controlador;
		// --------------------------------------------------------
		var explorarando			= true;
		var autoref					= this;		// Autoreferencia del objeto, útil para llamadas dentro de funciones
		var primera_ejecucion		= true;		// Marca la primera carga del explorador. Para activar la primera búsqueda
		// --------------------------------------------------------

		
		// -----------------------------------------------------
		//	Iniciación del explorador de archivos
		//	-----------------------------------------------------			
		
		/**
			Carga el listado de archivos en el explorador de archivos
		    @function cargarArchivosExplorador
		    @memberof ExploradorArchivos
		*/
		this.cargarArchivosExplorador = function ()
		{
			if (!explorarando) return false;

			// --------------------------------------------------------
			// Obtenemos el patrón de búsqueda
			var patron = "";
			if (buscador.val() != "")  
				patron = "&patron="+buscador.val();
			// --------------------------------------------------------
			// 
			autoref.explorar (false);

			explorador.fileTree ({	
				        root: '',
				        script: 'servicios/archivo.listar.php?html'+patron,
				        expandSpeed: 1000,
				        collapseSpeed: 1000,
				        multiFolder: true
			}
			, function(archivo) 
			{
					controlador.cargarArchivo(archivo);
			}
			, function() 
			{ 
					autoref.cargarMenuContextual ();
					
					// Para la primera ejecución abrimos también los archivos que han quedado abiertos
					if (primera_ejecucion)
					{
						primera_ejecucion = false; 
						controlador.recrearSesion ();
						explorador.html5Uploader("/", controlador.surbirArchivo);
					}

					autoref.explorar (true);
			}		
			); 
		}

		// -----------------------------------------------------
		//	Activar/Desactivar el explorador
		//	-----------------------------------------------------			

		/**
			Activa el explorador (elimina la capa de carga)
		    @function explorar
		    @param {int} activo activo (1) / inactivo (0)
		    @memberof ExploradorArchivos
		*/
		this.explorar = function (activo)
		{
			if (activo)
			{
				if (!explorarando)
				{
					explorarando = true;
					carga.desactivar ();
				}
			}
			else
			{
				if (explorarando)
				{
					explorarando = false;
					carga.activar();
				}
			}
		}


		//  -----------------------------------------------------		
		// Asigna valores para el menú contextual
		// -----------------------------------------------------			

		/**
			Asigna el evento de menú contextual a cada uno de los ítems del explorador de archivos
		    @function cargarMenuContextual
		    @param {int} activo activo (1) / inactivo (0)
		    @memberof ExploradorArchivos
		*/
		this.cargarMenuContextual = function ()
		{
				explorador.find('LI A').contextMenu('menucontextual', 
				{
					bindings: 
					{
						'descargar': function(t) 
						{
							// Solicitamos descargar el archivo en ventana nueva
							controlador.descargarArchivo(t.rel);
						},
						'eliminar': function(t) 
						{
							// Solicitamos eliminar archivo vía post
							controlador.eliminarArchivo(t.rel);
						},
						'renombrar': function(t) 
						{
							// Solicitamos eliminar archivo vía post
							controlador.renombrarArchivo(t.rel);
						}

					}
					
				});
				
				// Drag and Drop sobre directorios y sobre el general
				// -------------------------------------------------
				
				
				var lis = explorador.find("li.directory");
				if (lis.length>0)
					for(var i=0; i<lis.length; i++)
					{
							$(lis[i]).html5Uploader($(lis[i]).find("A").attr("rel"), controlador.surbirArchivo  );
					}
				

		}

		//	-----------------------------------------------------		
		//	Listado de directorios
		//	-----------------------------------------------------			

		/**
			Devuelve un listado con los directorios del explorador
			@function clistarDirectorios
		    @memberof ExploradorArchivos
		*/
		this.listarDirectorios = function ()
		{
			var misdirectorios = { "/" : "/"};
			// Para evitar llamar al servicio web nuevamente, obtenemos el valor de la lista de archivo del explorador
			var lis = explorador.find("li.directory");
			if (lis.length>0)
			{
				for(var i=0; i<lis.length; i++)
				{
					dir = $(lis[i]).find("a").attr("rel");
					misdirectorios[dir] = dir;
				}
			}

			return (misdirectorios);
		}

		/**
			Devuelve una referencia al contenedor padre
		    @function contenedor
		    @memberof Editor
		*/
		this.contenedor = function () 
		{ 
			return contenedor; 
		}

		//	-----------------------------------------------------		
		//	Iniciación de la clase
		//	-----------------------------------------------------			
		this.cargarArchivosExplorador ();
		// --------------------------------------------------------
		//	Iniciacion del buscador de archivos
		buscador.keyup(function() { autoref.cargarArchivosExplorador (); }).change();
		// --------------------------------------------------------
}