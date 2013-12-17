<?php
// ---------------------------------------------------------------------------
/**
* Paquete .cloud
* @package dotcloud
*/
// ---------------------------------------------------------------------------
include_once ("ObjetoBD.php");
include_once ("Proyecto.php");

// ---------------------------------------------------------------------------
/**
* Clase entidad para la gestión de los proyectos
* @author Antonio Juan Sánchez Martín
* @version 0.9
*/
class Rango extends ObjetoBD
{
		// ---------------------------------------------------------------------------
		/** 
		* Constructor
		* @param Sesion $sesion Referencia a la sesión
		* @param string $nombre Nombre del objeto a crear
		*/
		public function __construct ($sesion, $nombre=null) 
		{
			$id = null;

			if ($nombre != null)
			{
				$resultado = $sesion->consulta ("SELECT rango.id FROM rango WHERE rango.nombre = '"+$nombre+"'");

				if ($obj = $resultado->fetch_object()) 
					$id = $obj->id;
			}
			parent::__construct ($sesion, $id, 'rango');

		} 

		// ---------------------------------------------------------------------------
		/** 
		* Listado de rangos del sistema
		* @return array Lista de usuarios
		*/
		public function listar ()
		{
			$retorno = array ();

			if ($this->sesion)
			{
				$resultado = $this->sesion->consulta ("SELECT id, nombre FROM rango");
		
				if ($resultado != null)
					while ($obj = $resultado->fetch_object())
			        {
			        	$retorno[] = $obj;
			        }
		    }
		    
		    return $retorno;		
		}
}

?>

