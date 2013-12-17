/**
 	Componente gráfico con la ventana de ejecución
	
	@author Antonio Juan Sánchez Martín
	@class VentanaUsuarios
	@version 0.9
	@param {Contenedor} contenedor - Referencia al componente padre
*/				

function VentanaUsuarios (controlador)
{ 
		// --------------------------------------------------------
		// Creación de la ventana en el body
		// --------------------------------------------------------
		$("body").append ('<div id="ventana_usuarios" class="fondo_carga"><div id="datos_usuarios" class="dialogo"> 	\
			<input type="button" class="cerrar" id="boton_cerrado_usuarios" />											\
			<div class="listado_usuarios">																				\
			<table class="tabla_usuarios">																				\
			<tr><th id="th_usuarios"></th><th class="center"></th><th id="th_seleccionados"></th></tr><tr><td>			\
				<select size="2" multiple="multiple" class="selector_usuarios" id="ventana_usuarios_listado"></select>	\
			</td><td class="center">																					\
				<input type="button" class="flecha flecha_izquierda" id="borrar_usuario" />								\
				<input type="button" class="flecha flecha_derecha" id="anadir_usuario" />								\
				<select size="2" multiple="multiple" class="selector_roles"  id="ventana_usuarios_roles"></select>		\
			</td><td>																									\
				<select size="2" multiple="false" class="selector_usuarios"  id="ventana_usuarios_seleccionados"></select>	\
			</td></tr></table></div></div></div>');	
		// --------------------------------------------------------
		var ventana					= $("#ventana_usuarios");								// $id de la capa de carga
		var cargador				= new Cargador ("cargador_usuarios", "datos_usuarios");	// $id de la capa de carga
		var boton_cerrado			= $("#boton_cerrado_usuarios");							// $id del Botón de cerrado de la ventana
		var borrar_usuario			= $("#borrar_usuario");									// $id del Botón de borrado 
		var anadir_usuario			= $("#anadir_usuario");									// $id del Botón de agregación
		var listado_general			= $("#ventana_usuarios_listado");						// $id del Select de listados de usuarios
		var listado_seleccionados	= $("#ventana_usuarios_seleccionados");					// $id del Select de listados de seleccionados
		var listado_rangos			= $("#ventana_usuarios_roles");							// $id del Select de listados de rangos
		// --------------------------------------------------------
		var autoref			= this;		
		var controlador		= controlador;													// Padre de la ejecución
		// --------------------------------------------------------
		// Eventos de los botones de la ventana
		// --------------------------------------------------------
		boton_cerrado.click (function()	{ autoref.cerrar() });
		// --------------------------------------------------------
		$("#th_usuarios").append (_("Todos los usuarios"));
		// --------------------------------------------------------
		$("#th_seleccionados").append (_("Usuarios en el proyecto"));
		// --------------------------------------------------------
		anadir_usuario.click (function()		
		{ 
			if (listado_general.val() && listado_rangos.val())
			{
				controlador.configurarUsuarios (listado_general.val(), listado_rangos.val());
			}
			else
				AttentionBox.showMessage( _("Debe seleccionar un usuario y un rango"));
		});
		// --------------------------------------------------------
		borrar_usuario.click (function()		
		{ 
			if (listado_seleccionados.val())
			{
				controlador.configurarUsuarios (listado_seleccionados.val(), null);
			}
			else
				AttentionBox.showMessage( _("Debe seleccionar un usuario del proyecto"));
		});
		// --------------------------------------------------------
		
		// -----------------------------------------------------		
		//	Acciones de los botones de añandir y quitar user
		//	-----------------------------------------------------			
		
		/**
			Actualiza las listas
		    @function actualizarListas
		    @param {string[]} usuarios Lista de usuarios
		    @param {string[]} seleccionados Lista de usuarios seleccionados
		    @param {string[]} rangos Lista de rangos
		    @memberof VentanaUsuarios
		*/
		this.actualizarListas = function (usuarios, seleccionados, rangos)
		{
			ventana.fadeIn ('slow');
			cargador.activar();
			
			this.actualizarSelect (listado_general, usuarios);
			this.actualizarSelect (listado_seleccionados, seleccionados);
			this.actualizarSelect (listado_rangos, rangos);
		}

		/**
			Actualiza la lista indicada
		    @function actualizarListas
		    @param {int} id Lista de usuarios
		    @param {string[]} lista Lista de valores [id, valor]
		    @memberof VentanaUsuarios
		*/
		this.actualizarSelect = function (id, lista)
		{
			id.removeAttr("multiple");;
			opciones = id.prop('options');
			$('option', id).remove();
			var options = {
            multiSelect: false
			}
			for (var i=0; i<lista.length; i++)
			{
				opciones[opciones.length] = new Option(lista[i][1], lista[i][0]);

			}
		}

		/**
			Cierra la ventana
		    @function cerrar
		    @memberof VentanaUsuarios
		*/
		this.cerrar = function ()
		{
			ventana.fadeOut ('slow');
		}

}