<?php
// ---------------------------------------------------------------------------
/**
* Paquete .cloud
* @package dotcloud
*/
// ---------------------------------------------------------------------------
include_once ("ElementoHTML.php");

// ---------------------------------------------------------------------------
/**
* Elemento con representación HTML y conexión con BD añade carga de valores POST y GET al elemento
*
* Tipos de elementos definidos para el formulario
* INPUT 		-> Formateado de INPUT type="text"
* 	valores: nombre, identificador, correspondencia_bd 
* PASSWORD 	-> Formateado de INPUT type="password"
* 	valores: nombre, identificador, correspondencia_bd		
* BOTON 		-> Formateado de INPUT type="button"
* 	valores: nombre, identificador, accion, valor
* SELECTOR	-> Formateado para SELECT
* 	valores: nombre, identificador, correspondencia_bd, valores_option_value, valores_option_nombre
* 	valores: nombre, identificador, correspondencia_bd, array("valor" => "nombre")
* CHECKBOX	-> Formateado para CHECKBOX
* 	valores: nombre, identificador, correspondencia_bd
* TEXTAREA	-> Formateado para TEXTAREA
* 	valores: nombre, identificador, correspondencia_bd, valor
* @author Antonio Juan Sánchez Martín
* @version 0.9
*/
// ---------------------------------------------------------------------------


class Formulario extends ElementoHTML
{
    
	private $campos 		= array ();
	private $identificador 	= "";
	private $conexion 		= null;

	//---------------------------------------------------------------------------
	/** 
	* Constructor
	*
	* Ejemplo:
	* new Formulario ("nombre_formulario", "clase_form", "POST", $conexion_bd, array (
	*		array ("usuario", 	"input", 	"usuario.nombre")
	*  	array ("password",	"password",	"usuario.password")
	*		array ("rango?", 	"select" ,	"usuario.rango", 	"rango.id", "rango.nombre")
	*		);
	*
	* @param string $identificador Identificador del formulario
	* @param string $clase Nombre de la clase CSS del formulario
	* @param string $metodo_envio "POST" o "GET"
	* @param string $conexion Elemento de conexión con BD, de la clase ConexionMySQL 
	* @param string $campos Campos de conexión con la base de datos MySQL
	*/
	public function __construct ($identificador, $clase, $metodo_envio, $conexion, $campos=null) 
	{
		$this->campos 			= $campos;
		$this->identificador 	= $identificador;
		$this->conexion 		= $conexion;
		// Analiza los campos y construye el array para generar el código HTML
		parent::__construct ("form",  array (	"clase"=> $clase, 
												"id" => $identificador, 
												"name" => $identificador,
												"class" => $clase,
												"method" => $metodo_envio, 
											), $this->crearCampos ($campos) );
	} 

	//---------------------------------------------------------------------------
	/** 
	* Crea un array de elementos a partir de los descriptores
	* @param string $campos Campos del formulario
	* @return array $Elemento HTML
	*/
	private function crearCampos ($campos)
	{
		$elementos = array ();


		if ($campos != null)  	
       	foreach ($campos as $campo)
		{
			// Creamos la etiqueta que acompaña al campo
			if ($campo[0] != null)
			if ($campo[0] != "")
			{
				// Salvo para botones o etiquetas vacías
				$elementos[] = new ElementoHTML ("div",  array ("class" => "etiqueta", "id" => $this->identificador."_etiqueta_".$campo[2]), array (new Texto ($campo[0])));
			}

			// El segundo elemento del campo es el tipo
			$elemento = null;

			$mivalor = "";
			if (isset($campo[3])) $mivalor = $campo[3];

			switch (strtoupper($campo[1])) 
			{
				// -----------------------------------------------------------------------------------------
				// -----------------------------------------------------------------------------------------								
				case 'INPUT':
					$elemento = new ElementoHTML ("input",  array (	"type" 	=> "text",
																	"id" 	=> $this->identificador."_".$campo[2], 
																	"name" 	=> $this->identificador."_".$campo[2],
																	"rel" 	=> $campo[2],
																	"value" => $mivalor // Valor
											));
					break;
				// -----------------------------------------------------------------------------------------
				// -----------------------------------------------------------------------------------------									
				case 'PASSWORD':
					$elemento = new ElementoHTML ("input",  array (	"type" 	=> "password",
																	"id" 	=> $this->identificador."_".$campo[2], 
																	"name" 	=> $this->identificador."_".$campo[2],
																	"rel" 	=> $campo[2]
											));
					break;
				// -----------------------------------------------------------------------------------------
				// -----------------------------------------------------------------------------------------									
				case 'BOTON':
					$elemento = new ElementoHTML ("input",  array (	"type" 	=> "button",
																	"id" 	=> $this->identificador."_".$campo[2], 
																	"name" 	=> $this->identificador."_".$campo[2],
																	"rel" 	=> $campo[2],
																	"value" => $mivalor // Valor
											));
					break;
				// -----------------------------------------------------------------------------------------
				// -----------------------------------------------------------------------------------------									
				case 'TEXTAREA':
					$elemento = new ElementoHTML ("input",  array (	"type" 	=> "textarea",
																	"id" 	=> $this->identificador."_".$campo[2], 
																	"name" 	=> $this->identificador."_".$campo[2],
																	"rel" 	=> $campo[2]
											, array (new Texto (""))));
					break;
				// -----------------------------------------------------------------------------------------
				// -----------------------------------------------------------------------------------------
				case 'SELECTOR':

					// Comprobamos si es un array de elementos dados o si hay que conectar con la base de datos
					$listado_elementos = array ();

					if (count($campo == 4))
					{
						// Listado de campos indicado por el usuario
						if ($campo[3] != null)
						foreach ($campo[3] as $valor => $opcion)
						{
							$listado_elementos[] = new ElementoHTML ("option",  array (	"value" => $valor), array(new Texto($opcion) ) );
						}
					}
					else if (count($campo == 5)) 
					{
						// Conexión con base de datos
						// Extraemos value y nombre para los option
						$listado_bd = $this->conexion->consultaBasica (array ($campo[4], $campo[5]));
						foreach ($listado_bd as $opcion)
						{
							$listado_elementos[] = new ElementoHTML ("option",  array (	"value" => $opcion[$campo[4]]), array(new Texto($opcion[$campo[5]]) ) );
						}
					}

					$elemento = new ElementoHTML ("input",  array (	"type" 	=> "select",
																	"id" 	=> $this->identificador."_".$campo[2], 
																	"name" 	=> $this->identificador."_".$campo[2],
																	"rel" 	=> $campo[2]),
																	$listado_elementos);
					break;
				// -----------------------------------------------------------------------------------------
				// -----------------------------------------------------------------------------------------
				case 'CHECKBOX':

					$elemento = new ElementoHTML ("input",  array (	"type" 	=> "checkbox",
																	"id" 	=> $this->identificador."_".$campo[2], 
																	"name" 	=> $this->identificador."_".$campo[2],
																	"rel" 	=> $campo[2],
																	"value" => $campo[3] // Valor
											));
					break;
				// -----------------------------------------------------------------------------------------
				// -----------------------------------------------------------------------------------------	
				default:
					// Elemento no definido

					break;
			}

			// Sólo incluimos el elemento si ha sido reconocido
			if ($elemento != null)
				$elementos[] =  new ElementoHTML ("div",  array ("class" => "campo"), array ($elemento));

		}

		return $elementos;
	}

}

?>

