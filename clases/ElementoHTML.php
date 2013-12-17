<?php
// ---------------------------------------------------------------------------
/**
* Paquete .cloud
* @package dotcloud
*/
// ---------------------------------------------------------------------------
/**
* Elemento con representación HTML
* @author Antonio Juan Sánchez Martín
* @version 0.9
*/
class ElementoHTML
{
    
	private $tipo 				= null;
	private $propiedades		= null;
	private $conenidos			= null;
	// Código completo del elemento y sus hijos
	private $codigoHTML 		= "";

	//---------------------------------------------------------------------------
	/** 
	* Constructor
	* @param string tipo Tipo del objeto
	* @param string propiedades Lista de claves-valor. Propiedades del objeto HTML. Éstas pueden ser: value, id, rel, ...
	* @param string contenido Array de objetos de tipo ElementoHTML
	*/
	public function __construct ($tipo, $propiedades, $contenidos=null) 
	{
		$this->tipo					= $tipo;
		$this->propiedades			= $propiedades;
		$this->contenidos			= $contenidos;
		$this->propiedades			= $propiedades;

		// Comprobamos si hay elementos $_POST con el mismo name para añadir a los campos value

		if ($this->propiedades != null)
		{
			if (isset($this->propiedades['name']))
			{
				if (isset ($_GET[$this->propiedades['name']])) 		$this->propiedades['value']	= $_GET [$this->propiedades['name']]; 
				if (isset ($_POST[$this->propiedades['name']])) 	$this->propiedades['value']	= $_POST [$this->propiedades['name']];
				if (isset ($_SESSION[$this->propiedades['name']])) 	$this->propiedades['value']	= $_SESSION [$this->propiedades['name']];
			}
		}
	} 

	//---------------------------------------------------------------------------
	/** 
	* Devuelve código completo del elemento y su contenido
	* @return string Código HTML del objeto
	*/
	public function codigo ()
	{

		$this->codigoHTML  .= '<'.$this->tipo.' ';
		
		if ($this->propiedades != null)  	
       	{
			foreach ($this->propiedades as $nombre => $valor)
			{		
				$this->codigoHTML .= $nombre.'="'.$valor.'" ';
			}
		}
		// Elementos contenidos
       	if ($this->contenidos != null)  	
       	{
       		$this->codigoHTML  .= ' >';
       		
       		foreach ($this->contenidos as $contenido)
			{		
				if ($contenido != null)
					$this->codigoHTML .= $contenido->codigo();
			}		
			$this->codigoHTML	.= ' </'.$this->tipo.'>'; 	//  Cierre clásico. P.E: </div>
		}
		else 	
			$this->codigoHTML	.= ' />'; 			// Cierre reducido. P.E: <br />
		

		return $this->codigoHTML;
	}

}

?>

