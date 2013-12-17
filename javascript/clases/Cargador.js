/**
 	Componente gráfico con pantalla de espera para esperas prolongadas
	
	@author Antonio Juan Sánchez Martín
	@class Cargador
	@version 0.9
	@param {string} id - ID de la capa de carga
	@param {Contenedor} contenedor - Referencia al componente padre
*/

function Cargador (id, contenedor)
{ 
		// --------------------------------------------------------
		// Creación de la ventana en el body
		if (contenedor == null) contenedor = "body"; 
		// $id de la ventana
		$(contenedor).append ('<div id="'+id+'" class="cargador" style="display: none;"><img src="imagenes/cargador.gif"> </div>');	
		// $id del cargador
		var cargador 				= $("#"+id);	
		// -----------------------------------------------------			
		var estados_en_carga		= 0;
		// -----------------------------------------------------			
		
		/**
	    	Mostrar capa de carga
	    	@function desactivar
	    	@memberof Cargador
	    */
		this.activar = function ()
		{
			estados_en_carga ++;
			cargador.fadeIn (100);
		}

		/**
	    	Ocultar capa de carga
	    	@function desactivar
	    	@memberof Cargador
	    */
		this.desactivar = function ()
		{
			estados_en_carga --;
			if (!estados_en_carga) cargador.fadeOut (100);
		}
}