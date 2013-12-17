/**
	Componente gráfico con la barra de herramientas de la GUI

	@author Antonio Juan Sánchez Martín
	@class BarraHerramientas
	@version 0.9
	@param {Controlador} contenedor Referencia al componente padre
*/
	

function BarraHerramientas (controlador)
{ 
		// --------------------------------------------------------
		// Creación de la ventana en el body
		// $id de la ventana
		$("body").append ('<div id="barra_herramientas_capa"><div id="info_proyecto"></div><div id="logo"><img src="imagenes/logo_barra_herramientas.png" alt=".cloud" /></div><div id="iconos_rapidos"></div></div>');	
		// --------------------------------------------------------
		var controlador			= controlador;
		var iconos_rapidos 		= $('#iconos_rapidos');
		var info_proyecto 		= $('#info_proyecto');
		
		/**
			Cargar la informacion dol proyecto en la barra de herramientas
		    @function cargarInformacionProyecto
		    @param {string[]} nombre Nombre del proyecto
		    @param {string[]} creador  Nombre del creador
		    @param {string[]} fecha  Fecha de creación del proyecto
		    @param {string[]} compilador  Compilador del proyecto
		    @memberof BarraHerramientas
		*/
		this.cargarInformacionProyecto = function (nombre, creador, fecha, compilador)
		{
			info_proyecto.append ("<p>"+_("Proyecto")+": <b>"+nombre+"</b><br />"+_("Creador")+": <b>"+creador+"</b><br />"+_("Fecha")+": <b>"+fecha+"</b><br />"+_("Compilador")+": <b>"+compilador+"</b></p>");

		}

		/**
			Mostrar capa de carga
		    @function cargarIconosRapidos
		    @param {string[]} proyectos Listado de los proyectos del sistema ["id", "nombre"]
		    @memberof BarraHerramientas
		*/
		this.cargarIconosRapidos = function (proyectos)
		{
				
				iconos_rapidos.append ('<select id="selector_proyectos" class="listado_proyectos"/></select>');
				iconos_rapidos.append ('<input type="button" id="boton_usuarios" 	/>');
				iconos_rapidos.append ('<input type="button" id="boton_crear" 		/>');
				iconos_rapidos.append ('<input type="button" id="boton_guardar" 	/>');
				iconos_rapidos.append ('<input type="button" id="boton_descargar" 	/>');
				iconos_rapidos.append ('<input type="button" id="boton_eliminar" 	/>');
				iconos_rapidos.append ('<input type="button" id="boton_compilar" 	/>');
				iconos_rapidos.append ('<input type="button" id="boton_ejecutar" 	/>');
				iconos_rapidos.append ('<input type="button" id="boton_configurar" 	/>');
				iconos_rapidos.append ('<input type="button" id="boton_idioma" 		/>');
				iconos_rapidos.append ('<input type="button" id="boton_manual" 		/>');
				iconos_rapidos.append ('<input type="button" id="boton_ayuda" 		/>');
				iconos_rapidos.append ('<input type="button" id="boton_salir" 		/>');

				
				// Icono con información del proyecto
				$('#logo').click (function() 		
					{ 
						if (info_proyecto.is(":visible"))
							info_proyecto.hide (); 
						else
							info_proyecto.show ('slow'); 
					} );

				
				/// Selector
				// -----------------------------------
				// Creamos un botón por cada proyecto
				// y le asignamos una acción
				$("#selector_proyectos").append("<option>"+_("Seleccione proyecto")+"</option>");

				for (i=0; i<proyectos.length; i++)
				{
					$("#selector_proyectos").append("<option value='"+proyectos[i].id+"'>"+proyectos[i].nombre+"</option>");
				}
				$("#selector_proyectos").change(function () {
					if ($(this).val() != '')
						controlador.cargarProyecto ($(this).val());
				});
				$('#salir').attr ("ayuda", _("Atención: Al cambiar de proyecto perderá todos los cambios de los archivos que no haya guardado"));
				
				// Botones y ayuda de éstos
				// -----------------------------------
				$("#boton_ayuda").click (function () 
				{
					$(this).removeClass ("ayuda_inactiva");
					$(this).removeClass ("ayuda_activa");
					estado = controlador.cambiarEstadoAyuda (); 
					$(this).addClass ("ayuda_"+estado);
				});
				$('#boton_ayuda').attr ("ayuda", _("Mostrar/ocultar la información de ayuda"));
				$('#boton_ayuda').addClass ("ayuda_inactiva");

				$('#boton_crear').click (function()		{ controlador.crearArchivo(); } );
				$('#boton_crear').attr ("ayuda", _("Crea un nuevo archivo o directorio en el proyecto"));

				$('#boton_usuarios').click (function()	{ controlador.configurarUsuarios(); } );
				$('#boton_usuarios').attr ("ayuda", _("Control y propiedades del acceso de usuarios al proyecto"));

				$('#boton_guardar').click (function()		{ controlador.guardarArchivo(); } );
				$('#boton_guardar').attr ("ayuda", _("Guardar el archivo que se está editando (Ctrl+S)"));

				$('#boton_descargar').click (function() 	{ controlador.descargarArchivo(); } );
				$('#boton_descargar').attr ("ayuda", _("Descargar el archivo en uso"));

				$('#boton_eliminar').click (function()	{ controlador.eliminarArchivo(); } );
				$('#boton_eliminar').attr ("ayuda", _("Eliminar el archivo en uso"));

				$('#boton_compilar').click (function() 	{ controlador.ejecutar('compilacion'); } );	
				$('#boton_compilar').attr ("ayuda", _("Compilar el proyecto (Ctrl+T)"));

				$('#boton_ejecutar').click (function()	{ controlador.ejecutar('ejecucion', true); } );
				$('#boton_ejecutar').attr ("ayuda", _("Ejecutar el proyecto (Ctrl+R)"));

				$('#boton_configurar').click (function() 	{ controlador.configurar(); } );	
				$('#boton_configurar').attr ("ayuda", _("Configurar las opciones de compilación del proyecto"));

				$('#boton_idioma').click (function() 		{ controlador.seleccionarIdioma(); } );
				$('#boton_idioma').attr ("ayuda", _("Cambiar de idioma"));
	
				$("#boton_manual").attr ("ayuda", _("Hagi click para abrir el manual de ayuda"));
				$("#boton_manual").click (function () 		{ window.open("manual/"); });

				$('#boton_salir').click (function() 		{ controlador.salir(); } );	
				$('#boton_salir').attr ("ayuda", _("Salir de la aplicación  (Ctrl+Q)"));


				$("#barra_herramientas_capa *").addClass("ayuda icono");	

		}


		// --------------------------------------------------------
		

}