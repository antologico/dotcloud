<?php
// ---------------------------------------------------------------------------
/**
* Paquete .cloud
* @package dotcloud
*/
// ---------------------------------------------------------------------------
include_once ("ObjetoBD.php");
include_once ("Proyecto.php");
include_once ("Directorio.php");

// ---------------------------------------------------------------------------
/**
* Clase para manejo de compiladores
* @author Antonio Juan Sánchez Martín
* @version 0.9
*/
class Compilador extends ObjetoBD
{
	// ---------------------------------------------------------------------------
	/**
	* Constructor
	* @param Sesion $sesion Variable de sesión
	* @param int $id ID del objeto que se desea instanciar
	*/
	public function __construct ($sesion, $id = null) 
	{
		$this->sesion 		= $sesion;
		if ($id == null)
		{
			// Cogemos el del proyecto en uso
			$proyecto = new Proyecto ($sesion);
			$id = $proyecto->idcompilador();
		}
		
		parent::__construct ($sesion, $id, 'compilador');

	} 

	// ---------------------------------------------------------------------------
	/**	
	* Devuelve un Array con las propiedades que puede tener el proyecto y sus valores por defecto o bien Actualiza o inserta valores de propiedades
	* @param int $idproyecto ID del proyecto
	* @param array $listado_valores Listado de valores de las propiedades
	* @return array Array con las propiedades que puede tener el proyecto y sus valores por defecto
	*/
	public function propiedades ($idproyecto = null, $listado_valores = null)
	{
		$retorno = array ();

		if ($this->sesion)
		{
			if ($idproyecto == null)
			{
				// Propiedades generales del compilador
				$resultado =  $this->sesion->consulta ("SELECT id, nombre, valordefecto FROM compilador_propiedad WHERE idcompilador=".$this->id);
				if ($resultado != null)
				while ($obj = $resultado->fetch_object())
		        {
		        	$retorno[] = $obj;
		        }
		    }
		    else
		    {
		    	if ($listado_valores == null)
		    	{
			    	// Propiedades específicas de un proyecto
			    	$resultado =  $this->sesion->consulta ("SELECT compilador_propiedad.id as id, compilador_propiedad.nombre as nombre,  compilador_propiedad.valordefecto as valordefecto, (		    												
			    												SELECT proyecto_propiedad.valor as valordefecto 
			    												FROM proyecto_propiedad
			    												WHERE 	proyecto_propiedad.idpropiedad = id
			    														AND proyecto_propiedad.idproyecto=".$idproyecto.") as valor
			    												FROM compilador_propiedad WHERE idcompilador=".$this->id );
			    	
					if ($resultado != null)
					while ($obj = $resultado->fetch_object())
			        {
			        	$retorno[] = $obj;
			        }
			    }
			    else
			    {
			    	// Iniciar transacción
			    	$this->sesion->transaccion ("inicio");
			    	
					$retorno = 0;

			    	foreach ($listado_valores as $key => $valor) 
			    	{
			    		$result = $this->sesion->consulta ("SELECT idpropiedad FROM proyecto_propiedad WHERE idpropiedad='$key' AND idproyecto='$idproyecto'");
			    		
			    		

			    		if ($obj = $result->fetch_object())
						{
							// Si las variables han sido creadas antes...
							if ($this->sesion->consulta ("UPDATE proyecto_propiedad SET valor='$valor' 
																 WHERE idpropiedad='$key' AND idproyecto='$idproyecto'") == null) 
								$retorno = -2;
			    			
			    		}
			    		else 
			    		{
			    			// Si no lo han sido ...
			    			if ($this->sesion->consulta ("INSERT INTO proyecto_propiedad (idpropiedad, idproyecto, valor) 
																VALUES ('$key', '$idproyecto', '$valor')") == null) 
			    				$retorno = -3;
			    		}
			    		if ($retorno != 0)  break;
			    	}

			    	if ($retorno != 0) $this->sesion->transaccion("abortar");
			    	else $this->sesion->transaccion("fin");

			    	// Fin de la transacción
			    	
			    }
		    }
	    }

	    return $retorno;
	}

	// ---------------------------------------------------------------------------
	/**	
	* Consulta o asigna el valor de una propiedad
	* @param int $nombre de la propiedad
	* @param int $idproyecto ID del proyecto
	* @param string $valor Valor de la propiedad
	* @return string Valor de la propiedad
	*/
	public function propiedad ($nombre, $idproyecto=null, $valor=null)
	{
		
		$resultado = null; 
		$idpropiedad = null;
		if ($this->sesion != null)
		{

			// Si se quiere insertar un valor y la propiedad no ha sido creada, se crea
			if (($valor != null) or ($valor == " "))
			{
				if ($valor == " ") $valor = "";
				// Se inserta de forma general
				$resultado = $this->sesion->consulta ("INSERT INTO compilador_propiedad (nombre, valordefecto, idcompilador)
						VALUES ('".$nombre."','".$valor."','".$this->id."')");

				// Se obtiene el id
				$res = null;
				$idpropiedad = null;

				if ($resultado != null)
				{
					$res = $this->sesion->consulta ("SELECT @@identity AS id");
				}
				else
				{
					$res = $this->sesion->consulta ("SELECT id FROM compilador_propiedad WHERE nombre ='".$nombre."' AND idcompilador=".$this->id);
				}

				if ($res != null)
				{
						if ($obj = $res->fetch_object())
							$idpropiedad = $obj->id;
				
						if ($idproyecto != null)
						{
							// Se crea en el caso de no estar creada 
							$this->sesion->consulta ("INSERT INTO proyecto_propiedad (idproyecto, idpropiedad, valor)
								VALUES ('".$idproyecto."', '".$idpropiedad."','".$valor."')");
							// Y se actualiza
							if ($resultado != null)
								$resultado = $this->sesion->consulta ("UPDATE proyecto_propiedad SET valor = '".$valor."'
									WHERE idproyecto='".$idproyecto."' AND idpropiedad='".$idpropiedad."'");
						}
						else
						{
							// Actualiza valores por defecto
							$resultado = $this->sesion->consulta ("UPDATE compilador_propiedad SET valordefecto = '".$valor."'
									WHERE id='".$idpropiedad."'");
						}
				}
				
				// OK
				if ($resultado != null) $resultado = "";
			}
			else
			{
				// Sólo para consultas
				if ($idproyecto == null)
					// Referidas a valores por defecto
					$resultado = $this->sesion->consulta ("SELECT id, nombre, valordefecto FROM compilador_propiedad 
						WHERE idcompilador=".$this->id." AND nombre='".$nombre."' LIMIT 0,1");
				else
					// Referidas al compilador
					$resultado = $this->sesion->consulta ("SELECT id, nombre, valor
												FROM proyecto_propiedad
						 						WHERE idcompilador = ".$this->id." 
						 						AND idproyecto = ".$this->sesion->idproyecto()." LIMIT 0,1");

				if ($resultado)
					if($obj = $resultado->fetch_object())
					{
						$resultado = $obj->valordefecto;
					}
					else $resultado = "";
				else $resultado = "";

			}
		}
		if ($resultado == null)
			$resultado = "";
		
		return $resultado;
	}

	// ---------------------------------------------------------------------------
	/**	
	* Procesa el resultado de una compilación de acuerdo a la regla de compilación
	* @param string $texto Texto a procesar
	* @return array Texto procesado
	*/
	public function procesarCompilacion ($texto)
	{
		$array_cadenas = array ();

		if ($this->id != null)
		{
			$patron = $this->propiedad ('Regla de Compilación');

			// Paso 1:  es transformar el patrón en expresión regular
			$expresionregular = str_replace("(archivo)", "([a-zA-Z0-9\/_.]*)", $patron);
			$expresionregular = str_replace("(linea)", "([0-9]+)", $expresionregular);
			$expresionregular = str_replace("(mensaje)", "(.*)", $expresionregular);
			
			// Paso 2: dividir el texto en líneas
			$lineas = explode("\n", $texto);

			// Paso 3: comprobar línea a línea con la expresión regular, las que no 
			// concuerdan las guardamos tal cual
			$expresionregular2 = "";

			for ($i=0; $i<substr_count ($patron, "("); $i++)
				$expresionregular2 .= "([()].*[)])"; 
			
			// Paso 4: comprobar línea a línea con la expresión regular, las que no 
			// concuerdan las guardamos tal cual
			if (preg_match ("/$expresionregular2/", $patron, $partesRegla))
			foreach ($lineas as $linea)
			{
				if ($linea != " ")
				{
					$elemento= array ();
					if (preg_match ("/$expresionregular/", $linea, $partes))
					{
						$num_elementos = count($partes);
						// Pasamos del primer elemento que es el $patron tal cual
						for ($i=1; $i<$num_elementos; $i++) 
						{
							if (($partesRegla[$i] == '(archivo)') ||
								($partesRegla[$i] == '(linea)') ||
								($partesRegla[$i] == '(mensaje)'))
								{
									$elemento[substr(substr($partesRegla[$i], 1), 0, -1)] = $partes[$i];
								}
						}
					}
					$elemento['error'] = str_replace(" ", "&nbsp;", $linea);

					$array_cadenas[]['texto'] = $elemento;
				}
			}
		}
		return ($array_cadenas);
	}

	// ---------------------------------------------------------------------------
	/**	
	* Dar de alta un nuevo compilador
	* @param string $nombre Nombre del compilador
	* @return int Error (0 si no hay error)
	*/
	public function crear ($nombre)
	{
		$usuario = new Usuario ($this->sesion);
		if ($usuario->rango() == "Administrador") 
		{
			$resultado= $this->sesion->consulta ("INSERT INTO compilador (nombre) VALUES ('".$nombre."')");
			if ($resultado != null) 
			{
				$res = $this->sesion->consulta ("SELECT @@identity AS id");
				if ($obj = $res->fetch_object())
					$this->id = $obj->id;
				return 0;
			}
		}
		return -2;
	}

	// ---------------------------------------------------------------------------
	/**	
	* Lista los compiladores del sistema
	* @return array Lista de compiladores
	*/
	public function listar ()
	{
		$retorno = array ();
		if ($this->sesion)
		{
			// Coge el rango del usuario de la sesión
			$user = new Usuario ($this->sesion);
			$rango = '"'.$user->rango().'" as rango';
			
			$resultado =  $this->sesion->consulta ("SELECT id, nombre, ".$rango." FROM compilador");

			while ($obj = $resultado->fetch_object())
	        {
	        	$retorno[] = $obj;
	        }

		}
		return $retorno;
	}

	// ---------------------------------------------------------------------------
	/**	
	* Borrar un nuevo compilador
	* @param id $id ID del compilador
	* @return int Error (0 si no hay error)
	*/
	public function borrar ($id)
	{
		// Sólo los administradores pueden borrar usuarios
		if ($this->sesion != null)
		{
			$user = new Usuario ($this->sesion);
			if ($user->rango() == "Administrador") 
			return parent::borrar ($id);
		}
		return -15;
	}

	// ---------------------------------------------------------------------------
	/**	
	* Compila el proyecto en uso
	* @return int Error (0 si no hay error)
	*/
	public function compilar ()
	{
		$texto = "";
		
		if ($this->sesion != null)
		{
			$directorio = new Directorio ($this->sesion->directorioProyectos ().DIRECTORY_SEPARATOR.$this->sesion->idproyecto());

			// Cargamos el comando de compilación
			$comandoCompilacion = $this->propiedad ("Compilación");
			// Para el caso de normas específicas
			if ($comandoCompilacion == "")
				$comandoCompilacion = $this->propiedad ("Compilación", $this->sesion->idproyecto() );
			
			// Sustituimos los valores en el caso de que haya variables en función del proyecto
			$propiedades = $this->propiedades  ($this->sesion->idproyecto());
			
			foreach ($propiedades as $propiedad) 
			{
				$comandoCompilacion = str_replace( "(".($propiedad->nombre).")",  $propiedad->valor, $comandoCompilacion);
			}
			
			// Y ejecutamos en cada directorio
			// Con 2>&1 redirigimos el error estandar
			if ($comandoCompilacion != "")
			$texto = $directorio->ejecutarSubdirectorios ($comandoCompilacion. " 2>&1");
			
		}
		return $texto;
	}

	// ---------------------------------------------------------------------------
	/**	
	* Ejecuta el proyecto en uso
	* @param string $fifo Tubería de salida
	* @param string $entrada Valor de la entrada
	* @return int Error (0 si no hay error)
	*/
	public function ejecutar ($fifo, $entrada)
	{
		$pid = null;

		
		if ($this->sesion != null)
		{
			$dir_ejecucion = $this->sesion->directorioProyectos ().DIRECTORY_SEPARATOR.$this->sesion->idproyecto();
			$directorio = new Directorio ($dir_ejecucion);

			// Cargamos el comando de compilación
			$comandoEjecucion = $this->propiedad ("Ejecucion");
			// Para el caso de normas específicas
			if ($comandoEjecucion == "")
				$comandoEjecucion  = $this->propiedad ("Ejecucion", $this->sesion->idproyecto() );
			
			// Sustituímos los valores en el caso de que haya variables en función del proyecto			
			$propiedades = $this->propiedades  ($this->sesion->idproyecto());	

			foreach ($propiedades as $propiedad) 
			{
				$comandoEjecucion = str_replace( "(".($propiedad->nombre).")",  $propiedad->valor, $comandoEjecucion);
			}

			if ($comandoEjecucion != "")
			{
				$pid = $directorio->ejecutar($comandoEjecucion, $dir_ejecucion.DIRECTORY_SEPARATOR."proyecto".DIRECTORY_SEPARATOR, 
					$fifo, $entrada, "proy".$this->sesion->idproyecto());
			}
		}
		
		return $pid;
	}
}

?>

