/**
 	Controlador del selector de proyectos GUI
	
	@author Antonio Juan Sánchez Martín
	@class Selector
	@version 0.9
*/


function Selector ()
{ 
		// --------------------------------------------------------
		// Creación de la ventana en el body
		// --------------------------------------------------------
		// Creación del diálogo
		// --------------------------------------------------------
		$("body").append ('<div id="botones_administracion"></div><div id="marco" class="dialogo"  ><div id="logo_capa"><p id="texto_inicio"></p><img src="imagenes/logo_barra_herramientas.png" width="60px" id="logo" alt=".cloud"/></div><div id="listado_items" class="scroll-pane"><ul id="listado_ul_items"></ul><img src="imagenes/cargador.gif" id="cargador_items" alt="loading"  /></div><div class="campo"  ><input type="button" id="form_nuevo_nuevo" value="'+_("Nuevo")+'"  /> </div> </div>');	
		// Propiedades generales
		// --------------------------------------------------------
		var autoref		= this;			// Autoreferencia del objeto, útil para llamadas dentro de funciones
		var destino 	= -1;
		var cargador	= null;
		var configurado	= "proyecto";
		//	-----------------------------------------------------
		//	Inicio de la aplicación en el idioma deseado
		//	-----------------------------------------------------			

		/**
			Asigna a un botón la propiedad seleccionada
		    @function cargarArchivosExplorador
		    @param {Object} boton Referencia al botón (JQUery)
		    @memberof Selector
		*/
		this.seleccionar = function (boton)
		{
			configurado	= boton.attr("rel");
			$("#botones_administracion input").removeClass("seleccionado");
			boton.addClass ("seleccionado");
			this.listar ();
		}

		/**
			Carga el controlador una vez se haya especificado el idioma. Es invocado desde this.mostrar()
		    @function mostrar
		    @param {string} idioma Idioma con el que se carga la GUI
		    @memberof Selector
		*/
		this.mostrar  = function (idioma)
		{
			cargador = new Cargador ("cargador_selector_general");

			cargador.activar ();

			// Activación del JScrollPane    
			// ------------------------------------------------------------------
			$("#listado_items").resizable({handles: 'n, s'});
			// Inicio del listado de proyectos
			// -----------------------------------------------------------------
			autoref.listar ();
		
			// Para los botones de acción de la administración
			// ------------------------------------------------------------------
			$.post("servicios/usuario.verrango.php", null, 
			function(rango)
			{
				rango = rango.replace(/\s/g,'');
				cargador.desactivar ();

				$("#botones_administracion").append ('											\
					<input type="button" id="boton_salir" class="icono" /> 						\
					<input type="button" id="boton_ayuda" class="icono ayuda_inactiva" /> 		\
					<input type="button" id="boton_manual" class="icono" /> 					\
					<input type="button" id="boton_idioma" class="icono" />');
				
				$("#boton_salir").click (function () {autoref.salir () });
				$("#boton_salir").attr ("ayuda", _("Salir de la aplicación"));

				$("#boton_ayuda").click (function () 
				{
					$(this).removeClass ("ayuda_inactiva");
					$(this).removeClass ("ayuda_activa");
					estado = autoref.cambiarEstadoAyuda ();
					$(this).addClass ("ayuda_"+estado);
				});
				
				$("#boton_ayuda").attr ("ayuda", _("Mostrar/ocultar la información de ayuda"));

				$("#boton_idioma").click (function () {autoref.seleccionarIdioma() });
				$("#boton_idioma").attr ("ayuda", _("Cambiar de idioma"));

				$("#boton_manual").attr ("ayuda", _("Hagi click para abrir el manual de ayuda"));
				$("#boton_manual").click (function () 
				{
						window.open("manual/");
				});
				

				if (rango == "Administrador")
				{
					$("#botones_administracion").append (' 													\
						<input type="button" id="boton_proyectos" class="icono seleccionado" rel="proyecto" /> 	\
						<input type="button" id="boton_usuarios" class="icono" rel="usuario" /> 			\
						<input type="button" id="boton_configurar" class="icono" rel="compilador" />');
					// Acciones de los botones
					$("#boton_usuarios").click (function () { autoref.seleccionar ($(this)); });
					$("#boton_usuarios").attr ("ayuda", _("Gestión de usuarios"));
					
					$("#boton_proyectos").click (function () { autoref.seleccionar ($(this)); });
					$("#boton_proyectos").attr ("ayuda", _("Gestión de proyectos"));
					
					$("#boton_configurar").click (function () { autoref.seleccionar ($(this)); });
					$("#boton_configurar").attr ("ayuda", _("Gestión de compiladores"));
				}

				// Asignación de los textos de ayuda al usuario
				$("#botones_administracion input").addClass("ayuda");

			});
			
		}	

		// ------------------------------------------------------------------
		// Carga de los ítems utilizando el servicio
		// ------------------------------------------------------------------
		/**
			Carga de los ítems utilizando el servicio
			@function listar 
		    @memberof Selector
		*/
		this.listar = function ()
		{
			// Texto de la página
			// ------------------------------------------------------------------
			$("#texto_inicio").text(_("Seleccione el "+configurado+" que desea editar"));

			$("#cargador_items").show ();
			$("#listado_ul_items").html ("");

			$.post("servicios/"+configurado+".listar.php", null, 
				function(valor)
				{
					if (valor != "")
					{
						var items = eval(valor);

						// Creamos un botón por cada ítem
						// y le asignamos una acción

						if (items)
						for (i=0; i<items.length; i++)
						{
													var borrar 	= "";
													var clase  	= ""
													if ((items[i].rango == "Administrador") || (items[i].rango == "Editor"))
													{
														borrar 	= "<input type='button' id='borrar_"+items[i].id+"' rel='"+items[i].id+"' class='boton_borrar' />";
														clase	= "boton_entrar_borrar";
													}
													


													$("#listado_ul_items").append("<li><input type='button' class='boton_entrar "+clase+"' id='item_"+items[i].id+"' name='item_"+items[i].id+"' rel='"+items[i].id+"' value='"+items[i].nombre+"' />"+borrar+"</li>");											
													// Acción del botón: ir al editor

													$('#item_'+items[i].id).click (function() 
													{ 
														destino = $(this).attr('rel');
														autoref.editarItem (destino);
													});


													if (borrar != "")
													$('#borrar_'+items[i].id).click (function() 
													{ 
														var id = $(this).attr('rel');
														AttentionBox.showMessage( _("¿Está seguro de borrarlo?"),
														{
														    buttons : 
														    [
														        { caption : _("Cancelar"), cancel: true },
														        { caption : _("Borrar") },
														    ],
															callback: function(action, inputs)
													    	{

													    		if (action == _("Borrar"))
													    		{
																	// Solicitud de borrado
																	$("#cargador_items").show ();
																	$("#listado_ul_items").html("");
																	

																	$.post("servicios/"+configurado+".gestionar.php", 
																		{
																			id: id,
																			accion: "borrar"
																		}, 
																		function(valor)
																		{
																			$("#cargador_items").hide ();
																			if (parseInt(valor) != 0)
																				AttentionBox.showMessage( _("No se pudo borrar")+". Error:"+valor);
																			
																			autoref.listar();
																		});
																}
															}
														});
													});
													
						}
					}

					$("#cargador_items").hide ();
			});

			$("#form_nuevo_nuevo").click (function ()
			{
				autoref.asignarAccionNuevo ();
			});
		}
	
		// ------------------------------------------------------------------
		// Variaciones en función del contenido
		// ------------------------------------------------------------------

		// Variaciones para el botón de nuevo

		/**
			Controla la acción del botón de "nuevo"
			@function asignarAccionNuev
		    @memberof Selector
		*/
		this.asignarAccionNuevo = function ()
		{
			switch (configurado)
			{
				case "proyecto":
					this.nuevoProyecto();
					break;
				case "usuario":
					this.nuevoUsuario();
					break;
				case "compilador":
					this.nuevoCompilador();
					break;								
			}
		}

		// ------------------------------------------------------------------
		// Variaciones para el botón de edición
		// ------------------------------------------------------------------

		/**
			Controla la acción del botón de edición de ítems
			@function editarItem 
		    @param {string} [destino] Página de destino para la edición (sólo para proyectos) 
		    @memberof Selector
		*/
		this.editarItem = function (destino)
		{
			switch (configurado)
			{
				case "proyecto":
					autoref.irA('editor.php?proyecto='+destino);
					break;
				case "usuario":
					this.editarUsuario (destino);
					break;
				case "compilador":
					this.editarCompilador (destino);
					break;								
			}
		}

		// ------------------------------------------------------------------
		// Proyectos
		// ------------------------------------------------------------------

		/**
			Controla la creación de un nuevo proyecto
			@function nuevoProyecto
		    @memberof Selector
		*/
		this.nuevoProyecto = function ()
		{	
				
					cargador.activar ();

					$.post("servicios/compilador.listar.php", null, 
						function(compiladores)
						{
							var compiladores = eval (compiladores);
							
							cargador.desactivar ();

							if (compiladores+0 != "-1")
							{	
								// Creamos la lista de compiladores
								var miscompiladores = {};

								for (i=0; i<compiladores.length; i++)
								{
									miscompiladores[compiladores[i].nombre] = compiladores[i].id;
								}

								AttentionBox.showMessage( _("Crear un nuevo proyecto"),
								{
								    inputs : 
								    [
								    	{ caption : _("Nombre"), id: "nombre_archivo_nuevo" },
								    	{ caption : _("Compilador"), rel:"proyecto.idcompilador", id: "conf_proyecto_idcompilador", 
								        			type: "select", values: miscompiladores },
								    ],
								    buttons : 
								    [
								        { caption : _("Cancelar"), cancel: true },
								        { caption : _("Crear proyecto") },
								    ],
									callback: function(action, inputs)
		    						{
		    							if (action == _("Crear proyecto"))
		    								if ((inputs[0].value != "") && (inputs[1].value != ""))
											{	
												$.post("servicios/proyecto.gestionar.php", 
												{
													nombre: inputs[0].value,
													idcompilador: inputs[1].value,
													accion: "crear"
												}, 
												function(valor)
												{
													if (parseInt(valor) != 0)
													{
														if (parseInt(valor) == -5)
															AttentionBox.showMessage( _("No tiene permisos para realizar esta acción"));
														else
															AttentionBox.showMessage( _("No se pudo crear el proyecto")+". Error:"+valor);
													}
													else
														autoref.listar();
												});
											}
										
									}
								});
							}
							else 
								AttentionBox.showMessage( _("No se pudieron optener los compiladores")+". Error:"+compiladores);
					});

		}

		// ------------------------------------------------------------------
		// Usuarios
		// ------------------------------------------------------------------
		
		/**
			Controla la creación de un nuevo usuario
			@function nuevoUsuario
		    @memberof Selector
		*/
		this.nuevoUsuario = function ()
		{
			cargador.activar ();

			$.post("servicios/rango.listar.php", null, 
						function(tipos)
						{
							var tipos = eval (tipos);
							
							cargador.desactivar ();

							if (tipos+0 != "-1")
							{	
								// Creamos la lista de tipos
								var miscompiladores = {};

								for (i=0; i<tipos.length; i++)
								{
									miscompiladores[tipos[i].nombre] = tipos[i].id;
								}

								AttentionBox.showMessage( _("Crear un nuevo usuario"),
								{
								    inputs : 
								    [
								    	{ caption : _("Nombre"), id: "nombre_archivo_nuevo" },
								    	{ caption : _("Apellidos"), id: "apellidos_usuario_nuevo" },
								    	{ caption : _("Email"), id: "email_usuario_nuevo" },
								    	{ caption : _("Password"), id: "password_usuario_nuevo", type: "password" },
								    	{ caption : _("Rango"), rel:"usuarios.idrango", id: "rango_usuario_nuevo", 
								        			type: "select", values: miscompiladores },
								    ],
								    buttons : 
								    [
								        { caption : _("Cancelar"), cancel: true },
								        { caption : _("Dar de alta") },
								    ],
									callback: function(action, inputs)
		    						{
		    							if (action == _("Dar de alta"))
		    								if ((inputs[0].value != "") && (inputs[1].value != ""))
											{	
												$.post("servicios/usuario.gestionar.php", 
												{
													nombre: inputs[0].value,
													apellidos: inputs[1].value,
													email: inputs[2].value,
													password: inputs[3].value,
													idrango: inputs[4].value,
													accion: "crear"
												}, 
												function(valor)
												{
													if (parseInt(valor) != 0)
														AttentionBox.showMessage( _("No se pudo dar de alta al usuario")+". Error:"+valor);
													else
														autoref.listar();
												});
											}
										
									}
								});
							}
							else 
								AttentionBox.showMessage( _("No se pudieron optener lo rango de usuarios")+". Error:"+tipos);
					});
		}


		/**
			Controla la edición de un usuario
			@function editarUsuario
		    @param {int} idusuario ID del usuario
		    @memberof Selector
		*/
		this.editarUsuario = function (idusuario)
		{
			cargador.activar ();
			$.post("servicios/usuario.verdatos.php", {id: idusuario}, function(datos) {
				
				cargador.desactivar ();
				

				if (datos != "")
				{
					datos = eval (datos);

					cargador.activar ();
					
					$.post("servicios/rango.listar.php", null, 
						function(tipos)
						{
							var tipos = eval (tipos);
							
							cargador.desactivar ();

					
							if (tipos+0 != "-1")
							{	
								// Creamos la lista de tipos
								var miscompiladores = {};

								for (i=0; i<tipos.length; i++)
								{
									miscompiladores[tipos[i].nombre] = tipos[i].id;
								}

								AttentionBox.showMessage( _("Editar el usuario"),
								{
								    inputs : 
								    [
								    	{ caption : _("Nombre"), id: "nombre_archivo_nuevo", value: datos[0].nombre },
								    	{ caption : _("Apellidos"), id: "apellidos_usuario_nuevo", value: datos[0].apellidos },
								    	{ caption : _("Email"), id: "email_usuario_nuevo", value: datos[0].email },
								    	{ caption : _("Password"), id: "password_usuario_nuevo", type: "password", value: datos[0].password },
								    	{ caption : _("Rango"), rel:"usuarios.idrango", id: "rango_usuario_nuevo", 
								        			type: "select", values: miscompiladores, value: datos[0].idrango },
								    ],
								    buttons : 
								    [
								        { caption : _("Cancelar"), cancel: true },
								        { caption : _("Modificar") },
								    ],
									callback: function(action, inputs)
		    						{
		    							if (action == _("Modificar"))
		    							{
		    								if ((inputs[0].value != "") && (inputs[1].value != ""))
											{	
												$.post("servicios/usuario.gestionar.php", 
												{
													id: idusuario,
													nombre: inputs[0].value,
													apellidos: inputs[1].value,
													email: inputs[2].value,
													password: inputs[3].value,
													idrango: inputs[4].value,
													accion: "modificar"
												}, 
												function(valor)
												{
													if (parseInt(valor) != 0)
														AttentionBox.showMessage( _("No se pudo dar de alta al usuario")+". Error:"+valor);
													else
														autoref.listar();
												});
											}
										}
										
										
									}
								});
							}
							else 
								AttentionBox.showMessage( _("No se pudieron obtener los rangos de usuarios")+". Error:"+tipos);
					});
				}
			});
		}

		// ------------------------------------------------------------------
		// Usuarios
		// ------------------------------------------------------------------
		
		/**
			Controla la creación de un nuevo compilador
			@function nuevoCompilador
		    @memberof Selector
		*/
		this.nuevoCompilador = function ()
		{
			
					AttentionBox.showMessage( _("Crear un nuevo compilador"),
								{
								    inputs : 
								    [
								    	{ caption : _("Nombre"), id: "nombre_compilador_nuevo"},
								    	{ caption : _("Ejecucion"), id: "ejecucion_compilador_nuevo" },
								    	{ caption : _("Compilacion"), id: "compilacion_compilador_nuevo" },
										{ caption : _("Regla de compilacion"), id: "regla_compilacion_compilador_nuevo" },
										{ caption : _("Main"), id: "main_compilador_nuevo"},
								    ],
								    buttons : 
								    [
								        { caption : _("Cancelar"), cancel: true },
								        { caption : _("Dar de alta") },
								    ],
									callback: function(action, inputs)
		    						{
		    							if (action == _("Dar de alta"))
		    								if (inputs[0].value != "")
											{	
												$.post("servicios/compilador.gestionar.php", 
												{
													nombre: inputs[0].value,
													ejecucion: inputs[1].value,
													compilacion: inputs[2].value,
													regladecompilacion: inputs[3].value,
													main: inputs[4].value,
													accion: "crear"
												}, 
												function(valor)
												{
													if (parseInt(valor) != 0)
														AttentionBox.showMessage( _("No se pudo modificar el compilador")+". Error:"+valor);
													else
														autoref.listar();
												});
											}
										
									}
					});
					
		}


		/**
			Controla la edición de un compilador
			@function editarCompilador
		    @param {int} id ID del compilador
		    @memberof Selector
		*/
		this.editarCompilador = function (id)
		{
			cargador.activar ();
			$.post("servicios/compilador.verdatos.php", {id: id}, function(datos) {
				cargador.desactivar ();
				if (datos != "")
				{
					datos = eval (datos);

					AttentionBox.showMessage( _("Crear un nuevo compilador"),
								{
								    inputs : 
								    [
								    	{ caption : _("Nombre"), id: "nombre_compilador_nuevo", value:datos[0].nombre },
								    	{ caption : _("Ejecucion"), id: "ejecucion_compilador_nuevo", value:datos[0].ejecucion },
								    	{ caption : _("Compilacion"), id: "compilacion_compilador_nuevo", value:datos[0].compilacion },
										{ caption : _("Regla de compilacion"), id: "regla_compilacion_compilador_nuevo", value:datos[0].regladecompilacion },
										{ caption : _("Main"), id: "regla_nueva_compilador_nuevo", value: datos[0].main },
								    ],
								    buttons : 
								    [
								        { caption : _("Cancelar"), cancel: true },
								        { caption : _("Modificar") },
								    ],
									callback: function(action, inputs)
		    						{
		    							if (action == _("Modificar"))
		    								if ((inputs[0].value != ""))
											{	
												$.post("servicios/compilador.gestionar.php", 
												{
													id: id,
													nombre: inputs[0].value,
													ejecucion: inputs[1].value,
													compilacion: inputs[2].value,
													regladecompilacion: inputs[3].value,
													main: inputs[4].value,
													accion: "modificar"
												}, 
												function(valor)
												{
													
													if (parseInt(valor) != 0)
														AttentionBox.showMessage( _("No se pudo modificar el compilador")+". Error:"+valor);
													else
														autoref.listar();
												});
											}
									}
								});
					}
			});
						
		}
}

Selector.prototype = new Controlador ();
