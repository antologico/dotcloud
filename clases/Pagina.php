<?php
// ---------------------------------------------------------------------------
/**
* Paquete .cloud
* @package dotcloud
*/
// ---------------------------------------------------------------------------
include_once ('ElementoHTML.php');
include_once ('Formulario.php');
include_once ('Texto.php');
// ---------------------------------------------------------------------------
/**
* Base para la creación de las páginas del proyecto. La codificación de la página es UTF-8
* @author Antonio Juan Sánchez Martín
* @version 0.9
*/

class Pagina
{
	private $css 				= null;
	private $javascript_inicio	= null;
	private $javascript_fin		= null;	
	private $metas				= null;
	// Marcamos la fecha de emisión de la página para evitar problemas con la chaché
	private $fecha 				= null; 


	// ---------------------------------------------------------------------------
	/**
	* Constructor
	* @param array $css Listado de archivos CSS
	* @param array $javascript_inicio Listado de archivos JavaScript que se cargan al inicio
	* @param array $javascript_fin Listado de archivos JavaScript que se cargan al final
	* @param array $metas Listado de propiedades META			
	* @param boolean $cargarPlugins indica si de deben cargar lo plugins o no (false por defecto)
	*/
	public function __construct ($css, $javascript_inicio, $javascript_fin, $metas) 
	{

		$this->metas 				= $metas;
		$this->css 					= $css;
		$this->javascript_inicio	= $javascript_inicio;
		$this->javascript_fin 		= $javascript_fin;
		$this->fecha 				= date("YmdHms"); 		// Segundo actual
		// Cabecera de la página

		$this->imprimir ('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
		$this->imprimir ('<html xmlns="http://www.w3.org/1999/xhtml" lang="es">');	
		$this->imprimir ('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">');
 		$this->imprimir ('<link rel="shortcut icon" href="imagenes/favicon.ico" type="image/x-icon" />');


       		$this->imprimirMetas 		($metas);
			$this->imprimirArchivosJS	($javascript_inicio);
			$this->imprimirArchivosCSS	($css);

       	$this->imprimir ('<body>');	
	} 

	// ---------------------------------------------------------------------------
	/** 
	* Imprime el código html de los meta-tags
	* @param string $metas lista clasificada
	*/
	public function imprimirMetas ($metas)
	{
		if ($metas != null)
		foreach ($metas as $nombre => $meta)
			{		
				if ($nombre == 'title') $this->imprimir ('<title>'.$meta.'</title>');
				else $this->imprimir ('<meta name="'.$nombre.'" content="'.$meta.'" />');
			}
	}

 	// ---------------------------------------------------------------------------
	/** 
	* Imprime el código html de los link a archivos JavaScript
	* @param string $archivos array con archivos JavaScript
	*/
	public function imprimirArchivosJS ($archivosJS)
	{
		if ($archivosJS != null)
		foreach ($archivosJS as $i => $archivoJS)
			{		
				$this->imprimir ('<script type="text/javascript" src="'.$archivoJS.'?id='.$this->fecha .'"></script>');
			}
	}

 	// ---------------------------------------------------------------------------
	/** 
	* Imprime el código html de los link a archivos JavaScript
	* @param string $archivos array con archivos JavaScript
	*/
	public function imprimirArchivosCSS ($archivosCSS)
	{
		if ($archivosCSS != null)
		foreach ($archivosCSS as $i => $archivoCSS)
			{		
				$this->imprimir ('<link rel="stylesheet" href="'.$archivoCSS.'?id='.$this->fecha .'" title="estilo" type="text/css" />');
				
			}
	}

	// ---------------------------------------------------------------------------
	/**  
	* Imprime una línea de texto (añade un salto de línea al final de la impresión). Codifica en UTF-8
	* @param string $texto Línea de texto
	*/
	static function imprimir ($texto)
	{
			echo utf8_encode(utf8_decode($texto))."\n";
	}

	// ---------------------------------------------------------------------------
	/**  
	* Carga los plugins del directorio de plugins
	* @param Sesion $sesion Sesion definida (para la conexion con la base de datos)
	* @param string $ruta Directorio de plugins
	*/
	public function cargarPlugins ($sesion, $ruta, $rutaDesintalador)
	{

		$instalados = array ();

		if (is_dir ($ruta))
			if ($dh = opendir($ruta))  
    		while (($directorio= readdir($dh)) !== false) 
    		  	if (is_dir($ruta . $directorio) && $directorio!="." && $directorio!="..")
            	{
            		$instalados[] = $directorio;
            		if (file_exists($ruta.$directorio."/desinstalar.sql"))
            		{
            			// La instalación aun no se ha producido
            			// Guardamos el desinstaldor
            			
            			if (file_exists($ruta.$directorio."/desinstalar.sql"))
            			{
            				// Ejecutamos el instalador
            				$intalador = $ruta.$directorio."/instalar.sql";
            				$fichero_inst = file($intalador);
							$query = '';
							foreach($fichero_inst as $sql_linea)
							{
								// Quitamos comentarios
								if(trim($sql_linea) != "" && strpos($sql_linea, "--") === false)
								{
								    $query .= $sql_linea;
								    // La consulta crece hasta acabar en ;
								    if (substr(rtrim($query), -1) == ';')
								    {
								    	$sesion->consulta ($query);
								    	$query = "";
								    }
								}
							}

							rename ($ruta.$directorio."/desinstalar.sql" , $rutaDesintalador.$directorio.".sql" );
            			
						}
            		}
            		if (file_exists($ruta.$directorio."/plugin.css"))
            			$this->imprimirArchivosCSS	(array($ruta.$directorio."/plugin.css"));
            		if (file_exists($ruta.$directorio."/plugin.js"))
            			$this->imprimirArchivosJS	(array($ruta.$directorio."/plugin.js"));
				}

		// comprobamos que todos los plugins instalador permanecen
		if (is_dir ($rutaDesintalador))
			if ($dh = opendir($rutaDesintalador))  
    		while (($archivo= readdir($dh)) !== false) 
    		  	if (substr($archivo, -4) == ".sql")
    		  		if (!is_dir($ruta.substr($archivo, 0, -4)))
    		  		{
    		  			// El directorio ha sido borrado
    		  			// Se procede a la desisntalación

    		  			// Ejecutamos el instalador
            			$desintalador = $rutaDesintalador.$archivo;
            			$fichero_inst = file($desintalador);
						$query = '';
						foreach($fichero_inst as $sql_linea)
						{
							// Quitamos comentarios
							if(trim($sql_linea) != "" && strpos($sql_linea, "--") === false)
							{
							    $query .= $sql_linea;
							    // La consulta crece hasta acabar en ;
							    if (substr(rtrim($query), -1) == ';')
							    {
							    	$sesion->consulta ($query);
							    	$query = "";
							    }
							}
						}

						// Borramos el desinsintalador
						unlink($rutaDesintalador.$archivo);
    		  		}
	}


	// ---------------------------------------------------------------------------
	// ---------------------------------------------------------------------------
	// Funciones para la creación de objetos HTML
	// ---------------------------------------------------------------------------
	/**  
	* Imprime un elemento HTML en la página
	* @param string $elemento variable de tipo ElementoHTML. Elemento a incluir
	*/
	public function incluirElemento ($elemento)
	{	
		$this->imprimir ($elemento->codigo());
	}	

	// ---------------------------------------------------------------------------
	/**  
	* Destructor. Cierra la página
	*/
	function __destruct ()
	{
			$this->imprimir ('</body>');
			$this->imprimirArchivosJS ($this->javascript_fin);
			//Cierra la conexión
			$this->imprimir ('</html>');	
	}
}

?>

