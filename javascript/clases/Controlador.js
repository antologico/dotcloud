/**
 	Controlador del GUI
	
	@author Antonio Juan Sánchez Martín
	@class Cargador
	@version 0.9
*/

function Controlador ()
{
		// Propiedades generales
		// --------------------------------------------------------
		var cargador 	= new Cargador ("cargador_controlador");	// Cargador para esperas, lo redefine
		var autoref		= this;										// Autoreferencia del objeto, útil para llamadas dentro de funciones
		var ayudaactiva = "inactiva";								// Activación de la ayuda en el hover
		
		/**
			Carga el controlador, una vez se haya especificado el idioma. Es invocado desde this.mostrar()
		    @function mostrar
		    @param {string} idioma Idioma con el que se carga la GUI
		    @memberof Controlador
		*/
		this.mostrar  = function (idioma)
		{
			// Reescribir por los controladores hijos
		}

		/**
			Inicia los componenetes GUI
		    @function iniciar
		    @param {string} idioma Idioma con el que se carga la GUI
		    @memberof Controlador
		*/
		this.iniciar = function ()
		{	
			$.get ('servicios/idioma.php', null, this.mostrar);
		}


		// --------------------------------------------------------
		// Funciones de la ayuda
		// --------------------------------------------------------

		/**
			Activa / desactiva las funciones de ayuda
		    @function cambiarEstadoAyuda
		    @memberof Controlador
		*/
		this.cambiarEstadoAyuda = function ()
		{
			this.mostrarAyuda ();

			if (ayudaactiva == "inactiva") ayudaactiva = "activa";
			else ayudaactiva = "inactiva";
			
			return ayudaactiva;

		}

		/**
			Asigna a cada componente los eventos necesarios para mostrar la ayuda por pantalla
		    @function mostrarAyuda
		    @memberof Controlador
		*/
		this.mostrarAyuda = function ()
		{
			$(".ayuda").hover(function(){
					if (ayudaactiva == "activa")
					{
						if ($(this).attr("ayuda"))
						{
							var elemento = this;
							var pos = $(elemento).position();
							// offset de los  contenedored
							var width = $(elemento).outerWidth();
							$("body").append ("<div id='ventanaayuda'></div>");
							$("#ventanaayuda").addClass ("ventanaayuda");
							$("#ventanaayuda").html ($(elemento).attr("ayuda"));
							$("#ventanaayuda").fadeIn("slow");
						}
					}

				}, 
				function()
				{
					if ($("#ventanaayuda"))
						$("#ventanaayuda").remove();	
				});
			
		}

		// --------------------------------------------------------
		// Control de eventos
		// --------------------------------------------------------

		/**
			Prepara la salida hacia otra página
		    @function salir
		    @memberof Controlador
		*/
		this.salir = function ()
		{
			cargador.activar ();
			$.post("servicios/sesion.cerrar.php", { }, function(valor)
							{
								cargador.desactivar();
								// Cuando se ha cerrado la session recargamos la página
								autoref.irA('index.php');
							}
					);
			
		}

		/**
			Función de control y manejo de eventos del botón de idioma
			@function seleccionarIdioma
		    @memberof Controlador
		*/
		this.seleccionarIdioma = function ()
		{
			var misidiomas  = {};
			

			$.each(i18n, function (elidioma, elvalor)
			{
				misidiomas  [elidioma] = elidioma;
			});


			AttentionBox.showMessage( _("Propiedades del idioma"),
			{
						    inputs : 
						    [
						    	{ caption : _("Idioma"), rel:"proyecto.idioma", id: "conf_gui_idioma", type: "select", values: misidiomas, selectvalue: idioma }
						    ],
						    buttons : 
						    [
						        { caption : _("Cancelar"), cancel: true },
						        { caption : _("Cambiar") },
						    ],
							callback: function(action, inputs)
    						{
    							if (action == _("Cambiar"))
    							{
    								// Enviamos el idioma nuevo al servicio cambiamos el valor definido para la GUI
    								idioma = inputs[0].value;
    								$.post("servicios/idioma.php", { idioma: idioma }, function(valor){ 
    										if (valor == "") AttentionBox.showMessage( _("Error guardando el idioma de la session"));
    										else AttentionBox.showMessage( _("La página se va a recargar"), {
										    buttons : 
										    [
										        { caption : _("Esperar"), cancel: true },
										        { caption : _("Recargar") },
										    ],
											callback: function(action)
				    						{
				    							if (action == _("Recargar"))
    												autoref.recargar();
    										}});

    								});
    							}	
    						}
    		});
		}

		/**
			Recarga la página con suavizado
			@function recargar
		    @memberof Controlador
		*/
		this.recargar = function ()
		{	
			$("body").fadeOut(1000, function () 
			{
				// Fade suave
			  	window.location.reload();
			});
		}

		/**
			Carga una nueva página con suavizado
			@function irA
		    @param {string} pagina URL de la nueva página
		    @memberof Controlador
		*/
		this.irA = function (pagina)
		{
			$("body").fadeOut(1000, function () 
			{
				// Fade suave
			  	window.location = pagina;
			});
		}
}		
