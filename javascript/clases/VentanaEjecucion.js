/**
 	Componente gráfico con la ventana de ejecución
	
	@author Antonio Juan Sánchez Martín
	@class VentanaEjecucion
	@version 0.9
	@param {Contenedor} contenedor - Referencia al componente padre
*/	

function VentanaEjecucion (controlador)
{ 
		// --------------------------------------------------------
		// Creación de la ventana en el body
		// --------------------------------------------------------
		$("body").append ('<div id="ventana_ejecucion" class="fondo_carga"><div id="datos_ejecucion" class="dialogo"><img id="en_ejecucion" src="imagenes/en_ejecucion.gif" alt="..."/><input type="button" class="cerrar" id="boton_cerrado_ejecucion"><pre class="scroll-pane" id="panel_ejecucion_salida"></pre><input id="panel_entrada_ejecucion" /><div class="scroll-pane" id="panel_lista_salida"></div><input type="button" id="reejecutar" /></div></div>');	
		var ventana 		= $("#ventana_ejecucion");									// $id de la capa de carga
		var boton_cerrado	= $("#boton_cerrado_ejecucion");							// $id del botón de cerrado de la ventana
		var ejecucion		= $("#en_ejecucion");										// $id de la imagen de ejecución
		var boton_reejecutar= $("#reejecutar");											// $id del botón de reejecucion
		var cargador		= new Cargador ("cargador_ejecucion", "datos_ejecucion");	// $id de la capa de carga
		var salida_pre		= $("#panel_ejecucion_salida");								// $id del panel de escritura	
		var entrada			= $("#panel_entrada_ejecucion");							// $id del panel de entrada de datos
		var salida_listado	= $("#panel_lista_salida");									// $id del panel de escritura	
		// --------------------------------------------------------
		var estado			= 'parada';
		var autoref			= this;		
		var controlador		= controlador;												// Padre de la ejecución
		// --------------------------------------------------------
		var detectartecla   				= false;
		var detectartecla_accion_anterior   = null;
		// --------------------------------------------------------
		// Activación del cierre
		boton_cerrado.click (function()		{ controlador.pararServicioEjecucion() });
		boton_reejecutar.click (function()	{ autoref.detectarTeclas (false); controlador.reejecutar($(this).attr('ref')); });
		
		/**
			Abre la ventana de ejecución
		    @function mostrar
		    @memberof VentanaEjecucion
		*/
		this.mostrar = function ()
		{
			ventana.fadeIn ('slow');
			cargador.activar();
			// Activación del cierre
			boton_cerrado.click (function()	{ autoref.ocultar() });
		}


		/**
			Asigna a un botón la propiedad seleccionada
		    @function detectarTeclas
		    @param {int} detectar Activa (1) / desactiva (0) la detección de eventos de teclado
		    @memberof VentanaEjecucion
		*/
		this.detectarTeclas = function (detectar)
		{
			if (detectar)
			{
				

				if (detectartecla == false)
				{
					
					controlador.enviarDatoEjecucion ("");

					detectartecla = true;

					entrada.keyup(function (evento) { 
								
								
								evento.preventDefault(); 
								

								input_length = $.trim($(this).val()).replace(/\s+/g, " ").split(' ').length;
								// if (input_length == 0) alert ("del");

								// Asignamos char
								var tecla = (evento.keyCode ? evento.keyCode : evento.which);
								var borrar = null;

								if (tecla == 13) tecla = "\n";
								else if ((tecla == 8) || (tecla == 48)) // Tecla DEL
								{
									borrar = true;
									tecla = String.fromCharCode(evento.which);
									
								}
								else
								tecla = String.fromCharCode(evento.which);

								// Lo sacamos por pantalla
								autoref.imprimir(tecla, borrar);
								controlador.enviarDatoEjecucion (tecla);	

						});

				}
			}
			else
			{
				if (detectartecla == null)
				{
					entrada.keyup (autoref.detectartecla_accion_anterior);
					detectartecla = false;
				}
			}
		}

		/**
			Activa o desactiva la capa de carga
		    @function ejecutando
		    @param {int} en_ejecucion En ejecución (1) / parado (0)
		    @memberof VentanaEjecucion
		*/
		this.ejecutando = function (en_ejecucion)
		{
			if (en_ejecucion == true)
			{
				ejecucion.show ();
				boton_reejecutar.hide ();
			}
			else
			{
				ejecucion.hide ();
				boton_reejecutar.show ();
			}
		}

		/**
			Asigna la propiedad de reejecuión al botón de acción
		    @function reejecutar
		    @param {string} tipo Tipo de acción
		    @memberof VentanaEjecucion
		*/
		this.reejecutar = function (tipo)
		{
			boton_reejecutar.removeClass ();
			boton_reejecutar.attr ("ref", tipo);
			boton_reejecutar.addClass (tipo);
			boton_reejecutar.show ();
		}

		/**
			Imprime los resultados en la salida tipo consola
		    @function imprimir
		    @param {string[]} resultados Tipo de acción
		    @memberof VentanaEjecucion
		*/
		this.imprimir = function (resultados, borrar)
		{
			this.detectarTeclas (true);


			salida_pre.show();
			salida_listado.hide();
			if ((borrar != null) && (borrar == true))
			{
				// Borramos
				texto  = salida_pre.text();
				salida_pre.text (texto.substr(0,texto.length-1));			
			}
			else
			salida_pre.append (resultados);
		}

		/**
			Lista y formatea los resultados de la compilación
		    @function listar
		    @param {string[]} resultados Tipo de acción
		    @memberof VentanaEjecucion
		*/
		this.listar = function (resultados)
		{
			this.detectarTeclas (false);


			salida_pre.hide();
			salida_listado.show();

			var resultado = "<ul>";

			for(var json in resultados)
				for(var errores in resultados[json])
					for(var linea in resultados[json][errores])
					{
						var error = 	resultados[json][errores][linea]['error'];
						var numlinea = 	resultados[json][errores][linea]['linea'];
						var archivo = 	resultados[json][errores][linea]['archivo'];
						var mensaje = 	resultados[json][errores][linea]['mensaje'];
						
						if (error != undefined)
						if (error != "")	
						{
							resultado 		+=  '<li';
							var marcado 	= 0;
							if (mensaje != undefined)
							{
								marcado 	= 	1;
								resultado 	+=  " class='marcado'><a";
								resultado 	+=  " linea='"+numlinea+"' ";
								resultado 	+=  " archivo='"+archivo+"' ";
								resultado 	+=  " mensaje='"+mensaje+"' ";
							}
							
							resultado += '>'+resultados[json][errores][linea]['error']
							if (marcado) resultado += '</a>';
							resultado += '</li>';
						}
					}
			resultado += "</ul>";

			salida_listado.append (resultado);

			// Añadimos una acción a todos los ítems marcados
			var lis = salida_listado.find("li.marcado");
			if (lis.length>0)
			{
				for(var i=0; i<lis.length; i++)
				{
					// Abrirá el documento que tenga señalado
					$(lis).find("a").click ( function ()	{
							autoref.ocultar ();
							controlador.cargarArchivo ("/"+$(this).attr('archivo'), $(this).attr('linea'));
						});

					resultado += "+";
				}
			}
		}

		/**
			Limpia las consolas de salida
		    @function limpiar
		    @memberof VentanaEjecucion
		*/
		this.limpiar = function ()
		{
			// Limpiamos la salida
			salida_pre.html ("");
			salida_listado.html ("");
		}


		/**
			Oculta la ventana de ejecución
		    @function ocultar
		    @memberof VentanaEjecucion
		*/
		this.ocultar = function ()
		{
			// Antes de ocultar, se manda parar los procesos en ejecución 
			ventana.fadeOut('slow');
			this.detectarTeclas (false);
		}
}