<?php
// ---------------------------------------------------------------------------
/**
* Paquete .cloud
* @package dotcloud
*/
// ---------------------------------------------------------------------------	
// Control de documento a nivel de archivo
// Códigos de error que pueden resultar al no resolverse las peticiones:
//		-1 : Datos introducidos incorrectos
//		 0 : Sin error
//		 1 : No se encontró archivo
//		 2 : No tiene permisos para ejecutar esa acción (general)
//		 3 : No tiene permisos para ejecutar esa acción (archivo destino)
//		 4 : Desconocido

include_once ("Usuario.php");

// ---------------------------------------------------------------------------
/**
* Control de archivos y directorios
* @author Antonio Juan Sánchez Martín
* @version 0.9
*/
class Directorio
{
    
	var $directorio_proyecto 	= "";
	var $directorio_temporal 	= "";
	var $directorio_general 	= "";

	//---------------------------------------------------------------------------
	/**
	* Constructor
	* @param string $directorio ruta relativa a la cual harán referencias el resto de las variables $ruta utilizadas en esta clase
	*/
	public function __construct($ruta_absoluta = null, $usuario = null) 
	{
		if ($ruta_absoluta != null)
		{
			$this->directorio_general 	= $ruta_absoluta; 
			if (!file_exists ($ruta_absoluta))
				mkdir ($ruta_absoluta, 0777);
			
			$this->directorio_proyecto 	= $ruta_absoluta.'/proyecto/'; 
			if (!file_exists ($this->directorio_proyecto))
				mkdir ($this->directorio_proyecto, 0777);
			
			$this->directorio_temporal 	= $ruta_absoluta.'/temporal/'; 
			if (!file_exists ($this->directorio_temporal))
				mkdir ($this->directorio_temporal, 0777);

			if ($usuario != null)
			{
				// chown($this->directorio_general, $usuario);
				chmod($this->directorio_general, 0770);
				// chown($this->directorio_proyecto, $usuario);
				chmod($this->directorio_proyecto, 0770);
				// chown($this->directorio_temporal, $usuario);
				chmod($this->directorio_temporal, 0770);
			}
		}
	} 

	//---------------------------------------------------------------------------
	/** 
	* Recoge una achivo subido por el usuario y lo coloca en su destino
	* @param string $rarchivoPOST: identificador del archivo $_FILE["nomrebarchivo"]
	* @param string  $ruta_destino: Ruta relativa del archivo destino. Ejemplo:	var/acopiar/
	* @return int Devuelve 0 si no hay error
	*/
	public function cargar ($archivoPOST, $ruta_destino, $sesion)
	{
		if (!$ruta_destino) return -1;
		if ($ruta_destino == "") return -1;

		if (filesize($archivoPOST['tmp_name'] == null)) return -3;
		
		$nuevo_nombre = $archivoPOST['name'];

		while (file_exists($this->directorio_proyecto.$ruta_destino.'/'.$nuevo_nombre))
		{
			$nuevo_nombre	= "_".$nuevo_nombre;
		} 

		if (!move_uploaded_file($archivoPOST['tmp_name'], $this->directorio_proyecto.$ruta_destino.'/'.$nuevo_nombre)) return -5; 
		else 
		{
			chmod($this->directorio_proyecto.$ruta_destino.'/'.$nuevo_nombre, 0770);
			chown($this->directorio_proyecto.$ruta_destino.'/'.$nuevo_nombre, 'proy'.$sesion->idproyecto());
		}
		// Si todo va bien ...
		return 0;
	} 

	//---------------------------------------------------------------------------
	/** 
	* Guarda el texto que se indica en un archivo, si éste no existe, lo crea. Se comprueban permisos
	* @param string $ruta Ruta relativa del archivo
	* @param string $contenido Contenido del archivo
	* @return int Devuelve 0 si no hay error
	*/
	public function guardar ($ruta, $texto, $sesion)
	{
		if (!$ruta) return -1;
		if ($ruta == "") return -1;

		$sesion->transaccion ('inicio');

		if (!$this->permisoEscritura ($ruta, $sesion)) return 5;	// No se tiene permiso de escrituta
															// Debe volver a abrir el archivo para saber si todo va ok

		// Y guarda los datos en disco
		if (!$fichero = fopen ($this->directorio_proyecto.$ruta, "w"))
		{
			// El destino sin permisos de escritura
			$sesion->transaccion ('fin');
			return 2; 
		} 
		if (fwrite($fichero, $texto) === FALSE)
		{
			chmod($fichero, 0770);
			chown($fichero, 'proy'.$sesion->idproyecto());
			// No se puede escribir en el archivo. Error de I/O
			$sesion->transaccion ('fin');
			return 4; 
		} 

		$sesion->transaccion ('fin');
		return 0;
	} 

	//---------------------------------------------------------------------------
	/** 
	* Cierra un archivo y lo libera para su utilización por otro usuario
	* @param string $ruta Ruta relativa del archivo
	* @return int Devuelve 0 si no hay error
	*/
	public function cerrar ($ruta, $sesion)
	{
		if (!$ruta) return -1;
		if ($ruta == "") return -1;

		// Marcamos en la base de datos que el fichero está listo borrando la entrada de archivos en uso
		if ($sesion->consulta ("DELETE FROM proyecto_archivo 
										WHERE archivo='".$ruta."' 
											AND idproyecto='".$sesion->idproyecto()."' 
											AND idusuario='".$sesion->idusuario()."'; ") == null) return 1;
		// Quitamos el archivo de la sesión
		$sesion->cerrarVista ($ruta);

		return 0;
	} 


	//---------------------------------------------------------------------------
	/** 
	* Copia un archivo 
	* @param string $ruta_origen Ruta absoluta del archivo origen. Ejemplo: /dir/file.fis
	* @param string $ruta_destino Ruta relativa del archivo destino. Ejemplo: var/acopiar/
	* @return int Devuelve 0 si no hay error
	*/
	public function copiar ($ruta_origen, $ruta_destino)
	{
		// Existe el origen
		if (!file_exists ($ruta_origen)) return 1;
		// Permisos del origen
		if (!(fileperms($ruta_origen) & 0x0100)) return 1; // Archivo origen sin permisos de escritura 
		// Permisos del destino
		if (!(fileperms($this->directorio_proyecto.$ruta_destino) & 0x0080)) return 2; // Directorio destino sin permisos de escritura
		// Obtenemos nombre origen

		// Copiamos
		if (!copy($ruta_origen, $this->directorio_proyecto.$ruta_destino.'/'.$nombre_archivo)) return 4;
		return 0;
	}


	//---------------------------------------------------------------------------
	/** 
	* Crear un archivo nuevo vacío
	* @param string $ruta Ruta relativa del archivo nuevo
	* @return int Devuelve 0 si no hay error
	*/
	public function crear ($ruta)
	{
		if (file_exists ($ruta)) return 10;
		return guardar ($ruta, "");
		return 0;
	}


	//---------------------------------------------------------------------------
	/** 
	* Crear un directorio nuevo vacío
	* @param string $ruta Ruta relativa del directorio
	* @return int Devuelve 0 si no hay error
	*/
	public function crearDirectorio ($ruta)
	{
		if (file_exists ($ruta)) return 10;
		if ($this->directorio_proyecto != null)
		if (!mkdir ($this->directorio_proyecto.$ruta, 0770)) return 9;
		
		return 0;
	}

	//---------------------------------------------------------------------------
	/** 
	* Borra el archivo indicado, si existe
	* @param string $ruta Ruta relativa del archivo a borrar
	* @param Sesion $sesion Sesión activa	
	* @return int Devuelve 0 si no hay error
	*/
	public function borrar ($ruta, $sesion)
	{
		// Existe el origen
		if (!file_exists ($this->directorio_proyecto.$ruta)) return 1;
		
		if (!$this->permisoEscritura ($ruta, $sesion)) return 5;	// No se tiene permiso de escrituta
																	// Debe volver a abrir el archivo para saber si todo va ok

		if (is_dir($this->directorio_proyecto.$ruta))
		{
			if(!$this->borrarDirectorio($this->directorio_proyecto.$ruta)) return (8);
		}
		else 
		{
			if (!unlink ($this->directorio_proyecto.$ruta)) return (3);	
		}
		return 0;
	}


	//---------------------------------------------------------------------------
	/** 
	* Mueve el archivo indicado, a la ruta nueva
	* @param string $ruta Ruta relativa del archivo a mover
	* @param string $ruta_nueva Ruta relativa nueva para el archivo
	* @param Sesion $sesion Sesión activa	
	* @return int Devuelve 0 si no hay error
	*/
	public function mover ($ruta, $ruta_nueva, $sesion)
	{
		// Existe el origen
		if (!file_exists ($this->directorio_proyecto.$ruta)) return 1;
		
		if (!$this->permisoEscritura ($ruta, $sesion)) return 5;	// No se tiene permiso de escrituta
																	// Debe volver a abrir el archivo para saber si todo va ok

		if (!rename ($this->directorio_proyecto.$ruta, $this->directorio_proyecto.$ruta_nueva)) return (3);	
		
		return 0;
	}

	//---------------------------------------------------------------------------
	/** 
	* Más genérico y sin permisos
	* @param string $directorio Ruta relativa del archivo a borrar	
	* @return int Devuelve 0 si no hay error
	*/
	public function borrarDirectorio ($directorio)
	{
		foreach(glob($directorio . "/*") as $archivos_directorio)
	    {
	        if (is_dir($archivos_directorio))
	        {
	        	if ($archivos_directorio != ".")
				if ($archivos_directorio != "..")
	            if (!$this->borrarDirectorio($archivos_directorio)) 
	            	return (0);	
	        }
	        else
	        {
	        	if (!unlink ($archivos_directorio)) return (0);	
	        }
	    }
	    if (!rmdir($directorio)) return (0);
		return 1;
	}

	//---------------------------------------------------------------------------
	/** 
	* Prepara el archivo indicado para su descarga
	* @param string $ruta Ruta relativa del archivo a borrar
	* @return int Devuelve 0 si no hay error
	*/
	public function descargar ($ruta)
	{
		if (!$ruta) return -1;
		if ($ruta == "") return -1;
		if (!file_exists ($this->directorio_proyecto.$ruta)) return 1;
		// ---------------------------------------------
		$nombre_archivo = substr($ruta, strrchr($ruta, "\/")+1); 
		// Se extrae sólo el nombre del archivo
		header("Pragma: public"); 
		header("Expires: 0"); 
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 	
		header("content-type: application/force-download"); 
		header("content-type: application/octet-stream"); 
		header("content-type: application/download"); 
		header("Content-Disposition: attachment; filename=".$nombre_archivo); 	
		header("Content-Length: ".filesize($this->directorio_proyecto.$ruta)); 
		readfile($this->directorio_proyecto.$ruta); 
		return 0;
	}


	//---------------------------------------------------------------------------
	/** 
	* Imprime el contenido del archivo indicado
	* @param string $ruta Ruta relativa del archivo a borrar
	* @param Sesion $sesion Sesión activa	
	* @param int $codigo_error Almacena el código de error	
	* @return int en caso de no encontrar el archivo no imprime nada y en código error copia el código del error
	*/
	public function leer ($ruta, $sesion, &$codigo_error)
	{

		$codigo_error = 0;
		if (!$ruta) { $codigo_error = -1;  return ""; }
		if ($ruta == "") { $codigo_error = -2;  return ""; }
		if (!file_exists ($this->directorio_proyecto.$ruta)) { $codigo_error = -2;  return ""; }

		// ---------------------------------------------
		// Marcamos el fichero como en uso
   		$sesion->consulta ("INSERT INTO proyecto_archivo (idproyecto, archivo, idusuario) 
   							VALUES ('".$sesion->idproyecto()."', '".$ruta."', '".$sesion->idusuario()."')");
   		// ---------------------------------------------
		// Imprimimos el proyecto
		$fichero = fopen ($this->directorio_proyecto.$ruta, "r");
		if ($fichero == null) { $codigo_error = -4;  return ""; }

		$res = "";
		while ($texto = fgets($fichero))
		{
      			$res .= $texto;
   		}

   		// Añadimos el archivo de la sesión
		$sesion->abrirVista ($ruta);

		return $res;
	}

	//---------------------------------------------------------------------------
	/** 
	* Indica si el usuario actual tiene permiso de escritura sobre el archivo
	* @param string $ruta Ruta relativa del archivo a borrar
	* @param Sesion $sesion Sesión activa	
	* @return int Devuelve 1 si se le da permiso, 0 en caso contrario
	*/
	public function permisoEscritura ($ruta, $sesion)
	{	
		if ($sesion != null)
		{	
			if (!$ruta) return 0;
			if ($ruta == "") return 0;
			
			// Quitamos el file_exits, porque si el archivo no existe, tiene todos los permisos
			// Porque el usuario lo puede crear
			// if (!file_exists ($this->directorio_proyecto.$ruta)) return 0;
			
			// Si es administrador tiene permiso de escritura siempre
			$usuario = new Usuario ($sesion);
			if ($usuario->rango() == "Administrador") return 1;

			// ---------------------------------------------
			// Comprobamos si el fichero está en uso por otro usuario en la BD
	   		$resultado = $sesion->consulta ("SELECT * FROM proyecto_archivo
											WHERE archivo='".$ruta."' 
												AND idproyecto='".$sesion->idproyecto()."' 
												AND idusuario!='".$sesion->idusuario()."' LIMIT 0,1; ");

			// Se tiene permiso de escritura
			if (!$obj = $resultado->fetch_object()) return 1;
			// Si no se cumple todo, no se tiene permiso
		}
		return 0;
	}

	//---------------------------------------------------------------------------
	/** 
	* Listado de directorios y archivos
	* @param Sesion $sesion Sesión activa
	* @param string $ruta Ruta relativa del directorio a listar, en función del proyecto activo (opcional).
	* @param int $patron Patrón para realizar la búsqueda
	* @return int Devuelve un array con los directorios y archivos
	*/
	public function listar ($sesion, $ruta, $patron)
	{ 
		$directorios = array ();

		// abrimos la ruta
		if (is_dir($this->directorio_proyecto.$ruta))
	   	{
	   		if ($apuntador = opendir($this->directorio_proyecto.$ruta)) 
			{ 
				while (($directorio_hijo = readdir($apuntador)) !== false) 
				{ 
					// Se muestran sólo los directorios
					if (is_dir($this->directorio_proyecto.$ruta ."/". $directorio_hijo ) && ($directorio_hijo !=".") && ($directorio_hijo !=".."))
	            		{ 
							//sólo si el archivo es un directorio, distinto que "." y ".." 
							$directorios["directorios"][] = array (
											"class" => 			"directory expanded", 
											"rel" => 			$ruta."/".$directorio_hijo."/",
											"name" => 			$directorio_hijo,
											"directorios" => 	$this->listar ($sesion, $ruta."/".$directorio_hijo , $patron)); 
						} 
				}
				closedir($apuntador); 
				// Listamos los archivos del directorio que siguen un patrón
			}
			$directorios["archivos"] = $this->listarArchivos ($sesion, $ruta , $patron);	
		} 

		return ($directorios);
		
	} 


	//---------------------------------------------------------------------------
	/** 
	* Listado de archivos
	* @param string $sesion	Variable de de sesión
	* @param string $ruta Directorio del que hacer el listado
	* @param string $patron	Patrón de búsqueda (opcional).
	* @return int Devuelve un array con los archivos de un determinado directorio
	*/
	private function listarArchivos ($sesion, $ruta, $patron="")
	{
		$listado = array ();
			
		if ($patron != "")
		{
				$listadoFinal = array ();
				$tam_nombre_dir =  strlen ($this->directorio_proyecto);
				
				// Búsqueda por nombre

				exec("ls ".$this->directorio_proyecto.$ruta."/". $patron. "*", $archivos);
				foreach ($archivos as $archivo)
				{
					$archivo = substr($archivo, $tam_nombre_dir+1);
					$listadoFinal[] = $archivo;
				}

				// Búsqueda por contenido

				exec('grep -lis "' . $patron . '" ' . $this->directorio_proyecto.$ruta .'/* ', $archivos);
				
				foreach ($archivos as $archivo)
				{
					$archivo = substr($archivo, $tam_nombre_dir+1);
					$listadoFinal[] = $archivo;
				}


				// Eliminamos duplicados

				$listadoFinal = array_unique($listadoFinal);
				$listadoFinal = array_values($listadoFinal);

				// Ordenamos la lista

				sort($listadoFinal);

				foreach ($listadoFinal as $archivo)
				{
					// Marcamos los que son de sólo lectura
					$sololectura =  "";
					if (!$this->permisoEscritura ($ruta."/".$archivo, $sesion)) $sololectura =  "sololectura";
					$archivo = substr("/".$archivo, strrpos("/".$archivo, "/")+1);
					$listado[] = $this->componerEstiloArchivo ($ruta, $archivo, $sesion);
				}
		}
		else
		{
			if ($apuntador = opendir($this->directorio_proyecto.$ruta)) 
			{ 
				while (($archivo = readdir($apuntador)) !== false) 
				{ 
					// Se muestran sólo los no-directorios
					if ((!is_dir($this->directorio_proyecto.$ruta ."/". $archivo)) && ($archivo !=".") && ($archivo !=".."))
	            	{
	            			// Marcamos los que son de sólo lectura
					        $sololectura =  "";
							if (!$this->permisoEscritura ($ruta."/".$archivo, $sesion)) $sololectura =  "sololectura";
							

	            			$listado[] = $this->componerEstiloArchivo ($ruta, $archivo, $sesion);

	            	}
						 
				}
				closedir($apuntador); 
				// Listamos los archivos del directorio que siguen un patrón
			}
		}
		return $listado;
	}

	//---------------------------------------------------------------------------
	/** 
	* Sustituye espacio por "_" y quita acentos y tildes.
	* @param string $cadena Cadena a modificar
	* @return int Cadena modificada
	*/
	private function limpiaCadena ($cadena) 
	{
		$patron = array ('/ /','/Ã¡/','/Ã©/','/Ã/','/Ã³/','/Ãº/','/Ã±/','/Ã/','/Ã‰/','/Ã/','/Ã“/','/Ãš/','/Ã‘/');
		$reemplazo = array (' ','á','é','í','ó','ú','ñ','Á','É','Í','Ó','Ú','Ñ');
		return  utf8_decode(preg_replace($patron,$reemplazo, $cadena));
		return (($cadena));
	}

	//---------------------------------------------------------------------------
	/** 
	* Listado de directorios y archivos
	* @param string $ruta Directorio del archivo
	* @param string $archivo nombre del archivo
	* @return Devuelve un array con la información del archivo
	*/
	private function componerEstiloArchivo ($ruta, $archivo, $sesion)
	{
		// La ruta puede ser null si se trata del directorio raíz
		if (($archivo!=null) && ($sesion!=null))
		{
			// Marcamos los que son de sólo lectura
	        $sololectura =  "";
			if (!$this->permisoEscritura ($ruta."/".$archivo, $sesion)) $sololectura =  "sololectura";
							
			// Valoramos la extensión
			$extension = substr(strrchr($archivo, '.'), 1);		

			$valor = array ( "class" => 	"file ext_$extension  $sololectura", 
							 "rel" => 		$ruta."/".$archivo,
							 "name" => 		$archivo);
			return $valor;
		}
		else return array ();
	}


	//---------------------------------------------------------------------------
	/** 
	* Lee de la tubería asignada a la sesión
	* @param string $sesion Sesión de la aplicación
	* @return Devuelve una cadena con la información del archivo o -1 en caso de fallo
	*/
	function leerTuberia ($sesion)
	{
		$contenido		= null;
				
		// La ruta puede ser null si se trata del directorio raíz
		if ($fifo = $this->comprobarTuberiaOUT ($sesion))
			if ($fo = fopen($fifo , 'r+'))
			{ 

					$waitIfLocked 	= true;
				
					$contenido = "";
					
					// while ($contenido == "")
		        	if ($locked = flock($fo, LOCK_SH, $waitIfLocked))
		        	{ 
		              	// Leemos el contenido
			           	$contenido = file_get_contents($fifo); 

			            ftruncate($fo, 0);
			            // Desbloqueamos el archivo
			            flock($fo, LOCK_UN); 
			        }

			        // Cerramos el archivo
		        	fclose($fo); 
			}

		return $contenido;
	}

	//---------------------------------------------------------------------------
	/** 
	* Escribe en la tubería indicada
	* @param string $escritorPID PID del demonio escritor
	* @param string $sesion Sesión de la aplicación
	* @param string $datos Datos a escribir
	* @return PID del demonio escritor
	*/
	function escribirTuberia ($escritorPID, $sesion, $datos)
	{
		// LA ruta puede ser null si se trata del directorio raiz
		if ($fifo = $this->comprobarTuberiaIN ($sesion))
		{ 
			// Comprobamos que el demonio de escritura esté listo
			if ($escritorPID == null)  
			{
				$escritorPID = $this->ejecutar ("php escritorFIFO.php ".$sesion->idproyecto(), "../demonios", $fifo);
			}
			else
			{
				// escribomos en el puerto del que leer el driver lector
				$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
				if ($socket !== false) 
				{
					$result = socket_connect($socket, '127.0.0.1', 11000+intval($sesion->idproyecto()));
					if ($result !== false) 
					{
						socket_write($socket, $datos, strlen($datos));
					}
					socket_close($socket);
				}
				else $escritorPID = null;
			}
		}
		else $escritorPID = null;
		
		return $escritorPID;
	}

	//---------------------------------------------------------------------------
	/** 
	* Comprueba la existencia de la tubería asignada a la sesión
	* @param string $sesion Sesión de la aplicación
	* @return string Devuelve una cadena con la ruta de la tubería o -1 en caso de fallo
	*/
	function comprobarTuberiaOUT ($sesion)
	{	
		if  ($sesion!=null)
		{
			$fifo = $this->direccionTuberiaOUT ($sesion);
			if (file_exists($fifo)) return $fifo;
		}
		return null;
	}

	//---------------------------------------------------------------------------
	/** 
	* Comprueba la existencia de la tubería asignada a la sesión
	* @param string $sesion Sesión de la aplicación
	* @return  Devuelve una cadena con la ruta de la tubería o -1 en caso de fallo
	*/	
	function comprobarTuberiaIN ($sesion)
	{	
		if ($sesion!=null)
		{
			$direccion = $this->directorio_temporal."fifo".$sesion->idproyecto()."_".$sesion->idusuario().'.in';
			
			// Si la tubería no existe la creamos
			posix_mkfifo($direccion, 0777);
			// Archivo temporal de escritura
			// shell_exec("echo '' > ".$direccion.".tmp");
			return $direccion;
		}
		return null;
	}

	//---------------------------------------------------------------------------
	/** 
	* Devuelve la ruta de la tubería asignada a la sesión
	* @param string $sesion Sesión de la aplicación
	* @return Devuelve una cadena con la ruta de la tubería o -1 en caso de fallo
	*/
	function direccionTuberiaIN ($sesion)
	{	
		if ($sesion!=null)
		{
			$direccion = $this->directorio_temporal."fifo".$sesion->idproyecto()."_".$sesion->idusuario().'.in';
			
			// Si la tubería no existe la creamos
			posix_mkfifo($direccion, 0777);
			// Archivo temporal de escritura
			// shell_exec("echo '' > ".$direccion.".tmp");
			return $direccion;
		}
		return null;
	}


	//---------------------------------------------------------------------------
	/** 
	* Devuelve la ruta de la tubería asignada a la sesión
	* @param string $sesion Sesión de la aplicación
	* @return  Devuelve una cadena con la ruta de la tubería o -1 en caso de fallo
	*/
	function direccionTuberiaOUT ($sesion)
	{	
		if ($sesion!=null)
		{
			return $this->directorio_temporal."fifo".$sesion->idproyecto()."_".$sesion->idusuario().'.out';
		}
		return "";
	}

	//---------------------------------------------------------------------------
	/** 
	* Ejecutar un comando y devuelve el PID
	* @param string $comando Comando a ejecutar
	* @return  Devuelve un cadena con el comando o el error
	*/
	function ejecutar ($comando, $directorio=null, $salida = null, $entrada = null, $usuario=null)
	{
		if ($directorio != null) $directorio = "cd ".$directorio.";";
		else $directorio = ""; 

		if ($salida != null) $salida = " >&".$salida;
		else $salida = "";

		if ($entrada != null) $entrada = " < ".$entrada;
		else $entrada = "";


		if ($usuario != null)
			$usuairo = " su ".$usuario." | ";
		else $usuario = "";

		// Lanzamos ...
		return shell_exec($directorio.' nohup nice '.$comando.'  '.$salida.' '.$entrada.' & echo $!');
	}

	//---------------------------------------------------------------------------
	/** 
	* Comprueba que un comando está en ejecución
	* @param string $pid PID del proceso
	* @return  Devuelve un true si el proceso está en ejecución o false en caso contrario
	*/
	function comprobarProceso ($PID)
	{
		exec("ps $PID", $estado);
	  	// Si se han de devuelto más de 2 líneas entonces el proceso continúa
        return(count($estado) >= 2);
	}

	//---------------------------------------------------------------------------
	/** 
	* Ejecutar el comando en cada subdirecotrio de la ruta asignada
	* @param string $comando: Comando a ejecutar
	* @return Devuelve una cadena con el comando o el error
	*/
	function ejecutarSubdirectorios ($comando, $directorio=null)
	{
		$texto = "";

		// asignamos el directorio del proyecto, para no salirnos del ámbito
		if ($directorio == null) $directorio = $this->directorio_proyecto ;

		// Lanzamos el proyecto
    	foreach(glob($directorio . "/*") as $subdirectorio)
	    {
	        if (is_dir($subdirectorio))
	        if ($subdirectorio != ".")
			if ($subdirectorio != "..")
	        {
	        	// $texto .= $this->ejecutarSubdirectorios ($comando, $subdirectorio);
	        }
	    }
	    $texto .= "[+] $directorio/$comando\n";
	    $texto .= shell_exec('cd '.$directorio.'; '.$comando);
	    return $texto;
	}

}

?>