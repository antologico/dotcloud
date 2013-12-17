<?php
// ---------------------------------------------------------------------------
/**
* Paquete .cloud
* @package dotcloud
*/
// ---------------------------------------------------------------------------
/**
* Clase entidad para la gestión de los objetos de la base de datos
* @author Antonio Juan Sánchez Martín
* @version 0.9
*/

class ObjetoBD
{
	/**
	* @var Sesion onexión con la sesión
	* @access protected
	*/
	protected $sesion 		= null;		
	/**
	* @var string Nombre de la tabla
	* @access protected
	*/
	protected $tabla 		= null;		

	// ---------------------------------------------------------------------------
	/** 
	* Constructor
	* @param string tipo Tipo del objeto.
	* @param $id ID del objeto
	* @param string $tabla Nombre de la tabla de datos
	*/
	public function __construct ($sesion, $id, $tabla) 
	{
		$this->sesion 		= $sesion;
		$this->id 			= $id;
		$this->tabla		= $tabla;
	} 

	// ---------------------------------------------------------------------------
	/** 
	* Devuelve nombre del objeto
	* @param string $valor Valor del elemento
	* @return string Valor de la propiedad
	*/
	public function nombre ($valor = null)
	{
		return $this->p ("nombre",  $valor);
	}

	// ---------------------------------------------------------------------------
	/** 
	* Devuelve el id del objeto
	* @return string Valor de la propiedad
	*/
	public function id ()
	{
		return $this->id;
	}

	// ---------------------------------------------------------------------------
	/** Devuelve el id de la sesión
	* @return string Valor de la propiedad
	*/
	public function sesion ()
	{
		return $this->sesion;
	}	

	// ---------------------------------------------------------------------------
	/** 
	* Devuelve o modifica la propiedad indicada del objeto
	* @param string $propiedad Nombre de la propiedad
	* @param string $valor Valor del elemento
	* @return string Valor de la propiedad
	*/
	protected function p ($propiedad, $valor = null)
	{
		return $this->sesion->propiedad ($this->tabla, $propiedad, $this->id, $valor);
	}

	// ---------------------------------------------------------------------------
	/** 
	* Consulta directa a la BD
	* @param string $consulta Consulta SQL
	* @return array Resultado de la consulta
	*/
	protected function consulta ($consulta)
	{
		return $this->sesion->consulta ($consulta);
	}

	// ---------------------------------------------------------------------------
	/** 
	* Elimina el objeto de la BD
	* @param int $id ID del objeto a borrar
	* @return int Si ha tenido éxito devuelve un 0
	*/
	protected function borrar ($id)
	{
		
		if ($this->sesion->consulta ("DELETE ".$this->tabla." FROM ".$this->tabla." WHERE id=".$id) !=null) 
			return 0;
		else return -1;
	}


}

?>

