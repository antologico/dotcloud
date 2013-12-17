/**
 	Controlador de la página de edición
	
	@author Antonio Juan Sánchez Martín
	@class EntornoTrabajo
	@version 0.9
*/	

function EntornoTrabajo ()
{
		// --------------------------------------------------------
		// Controles
		var autoref		= this;						// Autoreferencia del objeto, útil para llamadas dentro de funciones
		var lista_archivos_abiertos	= new Array();	// Lista para controlar los archivos abiertos
		var ultimaejecucion			= "";			// Para control de reejecuciones
		var ejecutando				= 'lista';

		// --------------------------------------------------------
		// Elementos de la GUI
		var herramientas			= null;
		var explorador				= null;
		var usuarios				= null;
		var editor					= null;
		var cargador				= null;
		var ventana_ejecucion 		= null;
		
		
		/**
			Carga el controlador, una vez se haya especificado el idioma. Es invocado desde this.mostrar()
		    @function mostrar
		    @param {string} idioma Idioma con el que se carga la GUI
		    @memberof EntornoTrabajo 
		*/
		this.mostrar  = function (miidioma)
		{
			// asignamos el idioma del sistema
			idioma 					= miidioma.replace(/\s/g,''); // Le quitamos los espacios
			// Cargamos el idioma del usuario previa construcción de la GUI
			// --------------------------------------------------------
			herramientas			= new BarraHerramientas (autoref);
			explorador				= new ExploradorArchivos (autoref);
			usuarios				= new VentanaUsuarios (autoref);
			editor					= new Editor (autoref);
			cargador				= new Cargador ("cargador_editor");
			ventana_ejecucion 		= new VentanaEjecucion (autoref);
		
			$.post("servicios/proyecto.listar.php", null, 
					function(valor)
					{
						herramientas.cargarIconosRapidos (eval(valor));
					});

			$.post("servicios/proyecto.verpropiedades.php", null, 
					function(valor)
					{
						valor = eval (valor);
						if (valor[0]['error']+0 == 0)
						{
							herramientas.cargarInformacionProyecto (valor[0]['nombre'], valor[0]['creador'], valor[0]['fecha'], valor[0]['compilador']);
						}
					});

			// --------------------------------------------------------
			// Activación del resize automático    
			explorador.contenedor().bind("resize", function (event, ui) 
			{
	            editor.contenedor().css ({"position": "absolute","left": ui.size.width+"px", "right": "0"});
	       	});
			explorador.contenedor().resizable({handles: 'e, w'});

			// --------------------------------------------------------
			// Asignación de teclas rápidas, por defecto al editor
			autoref.asignarDeteccionTeclas ();

			autoref.mostrarAyuda ();
		}

		//	-----------------------------------------------------
		//	Funciones de archivos
		//	-----------------------------------------------------			
	
		/**
			Abre una ventana nueva y descarga un archivo
		    @function descargarArchivo
		    @param {string} archivo Archivo a descargar
		    @memberof EntornoTrabajo 
		*/
		this.descargarArchivo = function (archivo)
		{
			if (archivo == null)
			{
				// Buscamos el archivo activo
				archivo 		= editor.archivoActivo ();
			}
			if (archivo != undefined)
				if (archivo != null)
					// Crea una nueva ventana para la descarga
					window.open("servicios/archivo.descargar.php?archivo="+encodeURIComponent(archivo), "_blank");
			
		}

		/**
			Abre el editor con el archivo indicado
		    @function cargarArchivo
		    @param {string} nombre_archivo Archivo a cargar
		    @param {int} [linea] Número de línea que aparecerá señalada
		    @memberof EntornoTrabajo 
		*/
		this.cargarArchivo = function (nombre_archivo, linea)
		{
		
			// Comprobamos que el archivo no se haya abierto
			if ((lista_archivos_abiertos == undefined) || (lista_archivos_abiertos[nombre_archivo] == undefined))
			{
				// Carga el texto contenido en un archivo en el editor indicado
				cargador.activar();

				// --------------------------------------------------------
				// Si no está creada la lista de archivos abiertos, la creamos
				if 	(this.lista_archivos_abiertos == undefined) 
					this.lista_archivos_abiertos = new Object();
				
				lista_archivos_abiertos[nombre_archivo] = 'abierto';
				
				// Debe ser asíncrono porque si no va mal la escritura en el editor
				$.post("servicios/archivo.leer.php", { archivo: nombre_archivo }, function(resultado)
					{
						if (resultado)
						{
							var resultado = eval(resultado);
							
							for(var json in resultado)
							{
									if (resultado[json]["errores"] != "0")
									{
										AttentionBox.showMessage(_("Error abriendo archivo.")+". Error: "+resultado[json]["errores"] );
										autoref.cerrarArchivo (nombre_archivo);
									}
									else
									{
										editor.crearEditor (nombre_archivo, resultado[json]["contenido"], linea); 
		
			            			}
			            			cargador.desactivar();
			            	}
			            }
					}
				);
			}
			else if (lista_archivos_abiertos[nombre_archivo] != undefined)
			{
				editor.activarEditor (nombre_archivo, linea); 
			}
			
		}

		/**
			Guarda el contenido de un archivo
		    @function surbirArchivo
		    @param {string} destino Archivo destino
		    @param {Obejct} manejadorArchivo Manejador del archivo (Ajax)
		    @memberof EntornoTrabajo 
		*/
		this.surbirArchivo = function (destino, manejadorArchivo)
		{
			if ((destino != undefined) && (manejadorArchivo))
			{
				var formData = new FormData();
				var xmlHttpRequest = new XMLHttpRequest();
				xmlHttpRequest.onreadystatechange = function (e) 
				{
					if (xmlHttpRequest.readyState == 4)
					{
						var error = xmlHttpRequest.responseText;
						if (error+0 != 0)
						AttentionBox.showMessage(_("Error abriendo archivo")+". Error: "+error );
						else
						explorador.cargarArchivosExplorador();
					}
				};
				xmlHttpRequest.open("POST", "servicios/archivo.cargar.php", true);
                formData.append("archivo", manejadorArchivo );
                formData.append("destino", destino );
                xmlHttpRequest.send(formData);
			}
			return false;
		}


		/**
			Guarda el contenido de un archivo
		    @function guardarArchivo
		    @param {string} nombre_archivo Archivo destino
		    @param {Obejct} contenido_archivo Contenido del archivo
		    @memberof EntornoTrabajo 
		*/
		this.guardarArchivo = function (nombre_archivo, contenido_archivo)
		{
			if (nombre_archivo == null)
			{
				// Buscamos el archivo activo
				nombre_archivo  		= editor.archivoActivo ();
				if (nombre_archivo 		!= null)
					contenido_archivo 	= editor.contenidoActivo ();
			}

			if (nombre_archivo != null)
			{
				// Buscamos en la lista de archivos si ha sido considerado de sólo lectura
				var sololectura	= "escritura";

				if (sololectura == "escritura")
				{

					cargador.activar();

					$.post("servicios/archivo.guardar.php", { archivo: nombre_archivo, contenido: contenido_archivo }, function(error)
							{
								$('#cargador_editor').fadeOut();
								if (error+0 != 0)
								{
									AttentionBox.showMessage(_("Error guardando el archivo")+". Error: "+error );
									return error;
								}
								cargador.desactivar ();
							}
					);
				}
				else AttentionBox.showMessage(_("Este documento se encuentra siendo editado actualmente por otro usuario"));
			} 
			else AttentionBox.showMessage(_("No hay documentos abiertos") );
 
			return 0;
		}

		/**
			Cierra un archivo para liberar su posesión
		    @function cerrarArchivo
		    @param {string} nombre_archivo Archivo destino
		   	@memberof EntornoTrabajo 
		*/
		this.cerrarArchivo = function (nombre_archivo)
		{
			if (nombre_archivo == null)
			{
				// Buscamos el archivo activo
				nombre_archivo  = editor.archivoActivo ();
			}

			if (nombre_archivo != null)
			{
				$.post("servicios/archivo.cerrar.php", { archivo: nombre_archivo }, function(error)
					{
								if (error+0 != 0)
								{
									AttentionBox.showMessage(_("Error liberando el archivo")+". Error: "+error );
									return error;
								}
								lista_archivos_abiertos[nombre_archivo] = undefined;
								editor.cerrarEditor (nombre_archivo);
					});
			}
			else AttentionBox.showMessage(_("No hay documentos abiertos"));
 
			return 0;
		}

		/**
			Elimina un archivo 
		    @function eliminarArchivo
		    @param {string} nombre_archivo Archivo destino
		   	@memberof EntornoTrabajo 
		*/
		this.eliminarArchivo = function (nombre_archivo) 
		{
			if (nombre_archivo == null)
			{
				// Buscamos el archivo activo
				nombre_archivo  = editor.archivoActivo ();
			}

			if (nombre_archivo != null)
			AttentionBox.showMessage( "¿"+_("Esta seguro de borrar ")+" "+nombre_archivo +"?",
			{
							buttons : 
						    [
						        { caption : _("Cancelar"), cancel: true },
						        { caption : _("Borrar") },
						    ],
							callback: function(action, inputs)
    						{
    							if (action ==  _("Cancelar"))
    							{
    								// No se hace nada, se cierra el diálogo y listo
    							}
    							else if (action ==  _("Borrar"))
    							{
    								// Cerramos el archivo
									if (autoref.lista_archivos_abiertos != undefined)
									if (autoref.lista_archivos_abiertos[nombre_archivo ] != undefined)
									if (autoref.lista_archivos_abiertos[nombre_archivo ] != null)
									{
										// Si el archivo está abierto, lo cerramos
										autoref.cerrarArchivo (nombre_archivo );
									}

									// Si el elemento a borrar es un directorio, buscamos todos los archivos abiertos de ese directorio y los cerramos
									if (nombre_archivo.charAt(nombre_archivo.length-1)=="/")
									{
										if (autoref.lista_archivos_abiertos != undefined)
										$.each( autoref.lista_archivos_abiertos, function( fichero, valor) 
										{
											if (valor != null)
											if (fichero.indexOf(nombre_archivo ) == 1)
											{
												if(autoref.lista_archivos_abiertos)
 												{
													autoref.cerrarArchivo (autoref.lista_archivos_abiertos[fichero]);
													
												}
											}
										});
										
									}

									// Ponemos cargando el explorador
									explorador.explorar (false);

									// Solicitamos la eliminacion
									$.post("servicios/archivo.eliminar.php", { archivo: nombre_archivo  }, function(error)
									{
										// En el retorno recargamos el explorador
										if (error+0 == 0)
										{
											AttentionBox.showMessage( _("Archivo eliminado"));
											if (nombre_archivo.charAt(nombre_archivo.length-1)!="/")
											if(autoref.lista_archivos_abiertos)
												autoref.cerrarArchivo (autoref.lista_archivos_abiertos[nombre_archivo]);
										}
										else
											AttentionBox.showMessage(_("No se ha podido borrar el archivo")+". Error: "+error );
										
										explorador.explorar (true);
										explorador.cargarArchivosExplorador();			
									});
    							}
    						}
    		});
			else AttentionBox.showMessage(_("No hay documentos abiertos"));
  
		}

		/**
			Recrea una sesión anterior abriendo los documentos que no se cerraron
		    @function recrearSesion 
		    @memberof EntornoTrabajo 
		*/
		this.recrearSesion = function ()
		{
			$.post("servicios/sesion.verabiertos.php", null, function (lista) 
			{
								if (lista != null)
								{
									var proyectos = eval (lista);
								
									for(var json in proyectos)
									    for(var archivo in proyectos[json])
									        if (proyectos[json][archivo] == "abierto")
									        {
									        	autoref.cargarArchivo(archivo);
									        }
								}
			});
		}

		/**
			Carga un nuevo proyecto en el editor
		    @function recrearSesion 
		    @param {string} idproyecto ID del proyecto a cargar
		   	@memberof EntornoTrabajo 
		*/
		this.cargarProyecto = function (idproyecto)
		{
			// Preguntamos antes

			AttentionBox.showMessage( _("Si cambia de proyecto, todos los cambios de los archivos no guardados se perderán"),
			{
						    buttons : 
						    [
						        { caption : _("Cancelar"), cancel: true },
						        { caption : _("Cambiar de proyecto") },
						    ],
							callback: function(action, inputs)
    						{
    							if (action ==  _("Cambiar de proyecto"))
    							{
									autoref.irA ("editor.php?proyecto="+idproyecto);
								}
							}  
			});
		}

		
		//	-----------------------------------------------------		
		//	Ejecución
		//	-----------------------------------------------------			
		

		/**
			Ejecuta una acción remota
		    @function ejecutar 
		    @param {string} tipo Tipo de acción
		    @memberof EntornoTrabajo 
		*/
		this.ejecutar = function (tipo, iniciar)
		{
			ventana_ejecucion.mostrar();
			if ((ejecutando == 'lista')
				 || (ultimaejecucion != tipo) )
			{
				var ultimaejecucion	= tipo;
				ejecutando = 'continuar';
				ventana_ejecucion.limpiar();
				ventana_ejecucion.ejecutando(true);
				this.iniciarServicioEjecucion (tipo, iniciar);
				// Indicamos a la ventana de ejecución que continúa la ejecución
				
			}
		}


		/**
			Reejecuta una acción remota
		    @function reejecutar 
		    @param {string} tipo Tipo de acción
		    @memberof EntornoTrabajo 
		*/
		this.reejecutar = function (tipo)
		{
			if (ejecutando == 'parada')
			{
				ejecutando = 'lista';
				this.ejecutar (tipo, true);
			}
		}


		/**
			Envia datos al proceso remoto que se está ejecutando
		    @function enviarDatoEjecucion
		    @param {string} dato Entrada de la consola
		    @memberof EntornoTrabajo 
		*/
		this.enviarDatoEjecucion = function (dato)
		{
			if (ejecutando == 'continuar')
			{
				$.post("servicios/proyecto.leerentrada.php", { valor_entrada : (dato+"").charCodeAt(0)+0 }, function (resultado) 
				{
					if (resultado+0 != 0)
					{
						AttentionBox.showMessage( _("Error en el servicio de envio de entrada")+". Error: "+resultado);
					}
				});
			}
		}

		/**
			Lanza el servicio de ejecución remota
		    @function iniciarServicioEjecucion 
		    @param {string} tipo Tipo de acción
		    @memberof EntornoTrabajo 
		*/
		this.iniciarServicioEjecucion = function (tipo, iniciar)
		{
			var servicio = null;

			if ((tipo == 'ejecucion') || (tipo == 'compilacion')) servicio = tipo;
			
			// Lanzamos el servicio de compilación constantemente, hasta que se acabe
			// o hasta que se pare la compilación
			
			if (servicio != null)
			{
				if (ejecutando == 'continuar')
				{
					$.post("servicios/proyecto.ejecutar.php", { tipo : servicio }, function (resultado) 
					{
						resultado = eval (resultado);

						if (resultado[0]["error"]+0 == 0)
						{
							resultado = eval (resultado);
							
							if (tipo == 'compilacion') ventana_ejecucion.listar (resultado[0]["datos"]);
							else 
								{
									if (iniciar != undefined)
									{ 
										// ventana_ejecucion.imprimir ("Iniciado");
										autoref.enviarDatoEjecucion ("");	
									}
									ventana_ejecucion.imprimir (resultado[0]["datos"]);
								}

							if (resultado[0]["fin"] == 'true') ejecutando = 'parada';


							autoref.iniciarServicioEjecucion (servicio);

						}
						else 
						{
							AttentionBox.showMessage( _("Error en el servicio")+". Error: "+resultado[0]["error"]);
							ejecutando = 'parada';
							autoref.iniciarServicioEjecucion (servicio);
						}
					});
				}
				else if (ejecutando == 'abortar')
				{
					$.post("servicios/proyecto.ejecutar.php", { tipo : 'parada' }, function (valor) 
					{
						if (parseInt(valor) == -1)
						{
							AttentionBox.showMessage( _("Error interno solicitando la parada")+". Error: "+valor);
						}
						else
						{
							ventana_ejecucion.detectarTeclas (false);
							ejecutando = 'parada'; // Parada correcta
							autoref.iniciarServicioEjecucion (servicio);
						}
					});
				}
				else 
				{
					ventana_ejecucion.ejecutando(false);
					ejecutando = 'parada';
					ventana_ejecucion.reejecutar(tipo);
					
				}
			}
		}


		/**
			Para el servicio de ejecución remota
		    @function iniciarServicioEjecucion 
		    @memberof EntornoTrabajo 
		*/
		this.pararServicioEjecucion = function ()
		{
			// indicamos a la ventana de ejecución que se va a parar la ejecución
			if (ejecutando == 'continuar') ejecutando = 'abortar';
		}

		
		/**
			Abre un diálogo para la creación de un nuevo archivo vacío
		    @function crearArchivo
		    @memberof EntornoTrabajo 
		*/
		this.crearArchivo = function ()
		{
			var misdirectorios  = explorador.listarDirectorios (); 

			AttentionBox.showMessage( _("Seleccione el directorio donde se creará"),
			{
						    inputs : 
						    [
						        { caption : _("Nombre"), id: "nombre_archivo_nuevo" },
						        { caption : _("Directorio"), id: "directorio_archivo_nuevo", type: "select", values: misdirectorios },
						    ],
						    buttons : 
						    [
						        { caption : _("Cancelar"), cancel: true },
						        { caption : _("Crear archivo") },
						        { caption : _("Crear directorio") },
						    ],
							callback: function(action, inputs)
    						{
    							if (action != "CANCELLED")
    							{
    								// Evitamos mandar nada por rapidez
    								if (inputs[0].value!='')
									{
										// Cargando...
										cargador.activar();

										// Mandamos crear el archivo
										if (action ==  _("Crear archivo"))
												$.post("servicios/archivo.guardar.php", {
												contenido: "", 
												archivo: inputs[1].value+inputs[0].value
												}, 
												function(valor)
												{
													if (parseInt(valor) != 0)
														AttentionBox.showMessage( _("No se pudo guardar el archivo")+". Error:"+valor);
													// A la vuelta recargamos el explorador
													explorador.cargarArchivosExplorador();
													
													// Y mostramos el archivo
													autoref.cargarArchivo (inputs[1].value+inputs[0].value);
													cargador.desactivar();
												})
										// Mandamos crear el directorio	
										else if (action ==  _("Crear directorio"))
											$.post("servicios/archivo.creardirectorio.php", {
												ruta: inputs[1].value+inputs[0].value
												}, 
												function(valor)
												{
													if (parseInt(valor) != 0)
													AttentionBox.showMessage( _("No se pudo crear el directorio")+"Error:"+valor);
													// A la vuelta recargamos el explorador
													explorador.cargarArchivosExplorador();
													cargador.desactivar();
													
												});
										else cargador.desactivar();
									}
								}
								
							}
			});

			// Cargamos el select creado por el attentionbox con los valores de los directorios

		}


		/**
			Abre un diálogo para la solictud del nuevo nombre
		    @function renombrarArchivo
		    @param {string} nombre_archivo archivo a renombrar
		    @memberof EntornoTrabajo 
		*/
		this.renombrarArchivo = function (nombre_archivo)
		{
			var misdirectorios  = explorador.listarDirectorios (); 
			var nombre_actual 		= nombre_archivo.substr(nombre_archivo.lastIndexOf("\/")+1);
			var directorio_actual 	= nombre_archivo.substr(0, nombre_archivo.lastIndexOf("\/"))+"\/";
		
			AttentionBox.showMessage( _("Seleccione el nombre final y el destino"),
			{
				
						    inputs : 
						    [
						        { caption : _("Nombre"), id: "nombre_archivo_nuevo", value: nombre_actual},
						        { caption : _("Directorio"), id: "directorio_archivo_nuevo", type: "select", values: misdirectorios, selectvalue:directorio_actual },
						    ],
						    buttons : 
						    [
						        { caption : _("Cancelar"), cancel: true },
						        { caption : _("Renombrar") },
						    ],
							callback: function(action, inputs)
    						{
    							if (action ==  _("Renombrar"))
    							{
    								// Evitamos mandar nada por rapidez
    								if (inputs[0].value!='')
									{
										// Cargando...
										cargador.activar();

										// Mandamos crear el archivo
										$.post("servicios/archivo.renombrar.php", {
												contenido: "", 
												archivo: nombre_archivo,
												nombre_nuevo: inputs[1].value+inputs[0].value
												}, 
												function(valor)
												{
													if (parseInt(valor) != 0)
														AttentionBox.showMessage( _("No se pudo renombrar el archivo")+". Error:"+valor);
													else
													{
														autoref.cerrarArchivo (nombre_archivo);
														autoref.cargarArchivo (inputs[1].value+inputs[0].value);
													}
													// A la vuelta recargamos el explorador
													explorador.cargarArchivosExplorador();

													// Y mostramos el archivo
													autoref.cargarArchivo (inputs[1].value+inputs[0].value);
													cargador.desactivar();
												});

									}
								}
								
							}
			});

			// Cargamos el select creado por el attentionbox con los valores de los directorios

		}




		/*
			-----------------------------------------------------		
			Panel de configuración para proyecto. Tipo de compilación
			-----------------------------------------------------			
		*/

		/**
			Abre un diálogo con el panel de configuración del proyecto (con los valores de compilación)
		    @function configurar
		    @param {int} idcompilador ID del compilador usado por el proyecto
		    @memberof EntornoTrabajo 
		*/
		this.configurar = function (idcompilador)
		{
			// Cargando...
			cargador.activar();

			var enviar_post = null;
			if (idcompilador != null) enviar_post = { idcompilador : idcompilador }; 

			// Cargamos los tipos de compilador
			$.post("servicios/proyecto.verpropiedades.php", enviar_post, 
												function(valor)
												{
													var compiladores = eval (valor);
														if (compiladores[0]['error']+0 == 0)
															{
																// tranformamos el json en un array comprensible
																var miscompiladores = {};
																var mispropiedades 	= {};

																for (i=0; i<compiladores[0]['compiladores'].length; i++)
																{
																		miscompiladores[compiladores[0]['compiladores'][i].nombre] = compiladores[0]['compiladores'][i].id;
																}

																for (i=0; i<compiladores[0]['propiedades'].length; i++)
																{
																		mispropiedades [compiladores[0]['propiedades'][i].nombre] = [compiladores[0]['propiedades'][i].id, compiladores[0]['propiedades'][i].valordefecto, compiladores[0]['propiedades'][i].valor];
																}

																if (idcompilador) compiladores[0]['idcompilador'] = idcompilador; 

																autoref.abrirVentanaConfiguracion(compiladores[0]['nombre'], mispropiedades, miscompiladores , compiladores[0]['idcompilador']);
													}
													else
														AttentionBox.showMessage( _("Error recuperando datos")+". Error:"+valor);
													
													cargador.desactivar();
												});
		}


		/**
			Abre un diálogo de configuración de compilación
		    @function abrirVentanaConfiguracion
		    @param {string} nombre Nombre del proyecto
		    @param {string[]} mispropiedades Propiedades del compilador
		    @param {string[]} miscompiladores Lista de compiladores
		    @param {int} idcompilador ID del compilador utilizado
		    @memberof EntornoTrabajo 
		*/
		this.abrirVentanaConfiguracion = function (nombre, mispropiedades, miscompiladores, idcompilador)
		{
			// Generales
			var accionCambioCompilador = function() {
				// Si cambia el compilador hay que cambiar todas las variables
				var idcompilador = $(this).val();
				// Cerramos el diálogo y lanzamos la ventana nueva
				AttentionBox.closeWindow (null, "yes", "RELOAD", idcompilador);
			};

			var misinputs = [
						        { caption : _("Nombre"), rel:"proyecto.nombre", id: "conf_proyecto_nombre", value: nombre },
						        { caption : _("Compilador"), rel:"proyecto.idcompilador", id: "conf_proyecto_idcompilador", 
						        			type: "select", values: miscompiladores, selectvalue: idcompilador, change: accionCambioCompilador },
						    ];

			// Específicas de cada proyecto
			if (mispropiedades != undefined)
			$.each(mispropiedades, function(nombre, valores) 
			{
				// Desactivamos los input que tienen ya valores por defecto
				var valor = valores[1];
				var disabled = true;
				if (valores[1] == "") 
				{
					disabled = false;
					valor = valores[2];
				}
				misinputs[misinputs.length] = { caption : nombre, rel: valores[0], id: "propiedad_proyecto_"+valores[0], value: valor, disabled: disabled };
			});

			AttentionBox.showMessage( _("Propiedades del proyecto"),
			{
						    inputs : misinputs,
						    buttons : 
						    [
						        { caption : _("Cancelar"), cancel: true },
						        { caption : _("Modificar") },
						    ],
							callback: function(action, inputs)
    						{
    							if (action == "RELOAD")
    							{
    								// Lanzamos la ventana nueva
    								autoref.configurar (inputs);
    							}
    							else if (action == "Modificar")
    							{
    								// Evitamos mandar nada por rapidez
    								if (inputs[0].value!='')
									{
										// Cargando...
										cargador.activar();

										var contenido_post = {};
										for (i=0; i<misinputs.length; i++)
										{	
											contenido_post[misinputs[i].rel] = inputs[i].value; 
										}
										
										contenido_post['accion'] = 'modificar'; 

										// Mandamos la modificación
										if (action ==  _("Modificar"))
												$.post("servicios/proyecto.editar.php", 
												contenido_post, 
												function(valor)
												{
													if (parseInt(valor) != 0)
														AttentionBox.showMessage( _("No se pudo modificar el proyecto")+". Error:"+valor);
													else
														AttentionBox.showMessage( _("Cambios realizados. Se recargará la página"), 
															{	
																buttons : 
							    								[ { caption : _("Recargar"), cancel: true } ],
																callback: function ()
	    														{
	    															document.location.reload();
	    														}
	    													});
													// A la vuelta recargamos el explorador
													cargador.desactivar();
												});
										else cargador.desactivar();
									}
								}
								
							}
			});
		}

		/**
			Abre un diálogo con el panel de configuración de compilación
		    @function configurarUsuarios
		    @param {int} idusuario ID del usuario
		  	@param {int} idrango ID del rango del usuario
		    @memberof EntornoTrabajo 
		*/
		this.configurarUsuarios = function (idusuario, idrango)
		{
			if (idusuario != null)
			{
				$.post("servicios/proyecto.modificarpermisos.php", 
					{
						idusuario_modificar: idusuario,
						idrango_modificar: idrango
					}, function(valor) 
					{
						if (parseInt(valor) == 0)
							// Cambio realizado
							autoref.configurarUsuarios ();
						else
							// Error
							AttentionBox.showMessage( _("No se pudo modificar el permiso")+". Error:"+valor);
					});
			}
			else
			{
				// Recuperamos a todos los usuarios del sistema
				$.post("servicios/proyecto.listarusuarios.php", null, function(valor) 
				{
					if (parseInt(valor) != "0")
					{
						$lista1 = eval (valor);

						var users = [];
						var participantes = [];
						var rangos = [];
	
						valor = eval (valor);
						valor = valor [0];

						for (var i=0; i<valor.usuarios.length; i++)
						{
							users[users.length] = [ valor.usuarios[i].id,  valor.usuarios[i].nombre  ];
						}
							
						for (var i=0; i<valor.participantes.length; i++)
						{
							participantes[participantes.length] = [ valor.participantes[i].id,  valor.participantes[i].nombre+" ("+_(valor.participantes[i].rango+")") ];
						}

						for (var i=0; i<valor.rangos.length; i++)
						{
							rangos[rangos.length] = [ valor.rangos[i].id,  valor.rangos[i].nombre ];
						}

						// Mostramos la ventana de configuración
						usuarios.actualizarListas (users, participantes, rangos);
					}
					else
						AttentionBox.showMessage( _("No se puede cargar la lista de usuarios")+". Error:"+valor);
					

				});
			}
		}


		/**
			Abre un diálogo con el panel de configuración de compilación
		    @function asignarDeteccionTeclas
		    @param {Object} componente Componente de origen
		  	@memberof EntornoTrabajo 
		*/
		this.asignarDeteccionTeclas = function (componente)
		{
			if (componente)
			{
				if (componente!= null)
					$(window).keypress(componente.detectarTeclas);
			}
			else
			{
				// Valor por defecto: teclas rápidas del editor
				if (editor!= null)
					$(window).keypress(editor.detectarTeclas);
			}
		}

		/**
			Cierra el editor y vuelve al selector
		    @function salir 
		    @memberof EntornoTrabajo 
		*/
		this.salir = function ()
		{
			this.irA ("selector.php");
			
		}

}	

EntornoTrabajo.prototype = new Controlador ();	
