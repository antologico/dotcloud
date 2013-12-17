<?php
// ---------------------------------------------------------------------------
/**
* Paquete .cloud
* @package dotcloud
*/
// ---------------------------------------------------------------------------
/**
* Texto en html. Formato UTF-8
* @author Antonio Juan Sánchez Martín
* @version 0.9
*/

include_once ("ElementoHTML.php");

class Texto extends ElementoHTML
{
    
	// Código completo del elemento y sus hijos
	private $codigoHTML 		= "";

	// ---------------------------------------------------------------------------
	/** 
	* Constructor
	* Constructor
	* @param string $texto Texto para bloque HTML
	*/
	public function __construct ($texto) 
	{
		$this->codigoHTML = $texto;
	} 

	// ---------------------------------------------------------------------------
	/** 
	* Devuelve código completo del elemento y su contenido
	* @return string Código HTML del texto
	*/
	public function codigo ()
	{

		return htmlentities(utf8_encode(utf8_decode($this->codigoHTML)));
	}

}

?>

