<?php
// ---------------------------------------------------------------------------
/**
* Paquete .cloud
* @package dotcloud
*/
// ---------------------------------------------------------------------------
require_once ("ConexionMySQL.php");
require_once ("Proyecto.php");
// ---------------------------------------------------------------------------
/**
* Control de la sesión y sus variables
* @author Antonio Juan Sánchez Martín
* @version 0.9
*/
class Sesion
{
	private	$conexion				= null;
	private $email 					= null;
	private $password				= null;
	private $idrango				= null;
	private $idusuario				= null;	
	private $correcta				= false;
	private $idproyecto				= null;
	private $archivos_abiertos 		= array ();
	private $momento				= null;
	private $direcotrio_proyectos	= null; 
	private $tiempoMax				= 1800; // El tiempo máximo de sesión es de 30 minutos (1800 segundos)

	// ---------------------------------------------------------------------------
	/** 
	* Constructor
	* @param ConexionMySQL $conexion Conexión a la BD
	* @param string $email Nombre del usuario
	* @param string $password Password del usuario, plano, sin encriptado
	*/
	public function __construct ($conexion, $direcotrio_proyectos, $email=null, $password=null) 
	{

		$this->direcotrio_proyectos = $direcotrio_proyectos;

		// Comprobamos que hay sesión y que por tiempo no ha expirado
		if (isset($_SESSION['email']) && isset($_SESSION['password']) && isset($_SESSION['momento']))
		{
			if ((time () - $_SESSION['momento']) < $this->tiempoMax)
			{
				$this->email 	= $_SESSION['email'];
				$this->password	= $_SESSION['password'];
				$this->momento	= $_SESSION['momento'] =time();
			}
			else
			{
				// Nada
			}
		}	

		// Si están definidos como parámetros, tienen privilegio frente a los ya definidos en sesión
		if (($email != null) && ($password !=null))
		{
			$this->email 	= $email;
			$this->password	= $password;
		}

		// Comprobación con la base de datos
		if ($conexion !=null)
		{
			// Nos guardamos la conexión
			$this->conexion = $conexion;

			// Comprobamos los datos en la BD
			$resultado = $conexion->consulta ("SELECT id, idrango FROM usuario WHERE email='".$this->email."' AND password='".$this->password."' LIMIT 0,1");
			
			if($obj = $resultado->fetch_object())
			{
				// Cargamos las variables de sesión
				$this->idrango 			= $obj->idrango;
				$this->idusuario		= $obj->id;
				$this->correcta 		= true;
				$this->momento			= time();
				$_SESSION['email']		= $this->email ;
				$_SESSION['password']	= $this->password ;
				$_SESSION['momento']	= $this->momento ;

				// Asignamos el proyecto si estaba definido
				// Con filtro para evitar problemas
				if (isset($_SESSION['proyecto']))
					if ($_SESSION['proyecto'] != "")
						$this->asignaProyecto ($_SESSION['proyecto']);

				// Apuntamos inicio de session en BD
				$conexion->consulta ("INSERT INTO sesion (idusuario) VALUES ('".$this->idusuario."')");
			}
			else
			{
				// Indicamos un acceso incorrecto
				// Sólo si el usuario ha sido correcto
				$resultado = $conexion->consulta ("SELECT id FROM usuario WHERE email='".$this->email."' LIMIT 0,1");
				if($obj = $resultado->fetch_object())
						$conexion->consulta ("INSERT INTO sesion (idusuario, correcto) VALUES ('".$obj->id."', 0)");

				$this->idrango 	= null;
			}
		
		}

	} 

	// ---------------------------------------------------------------------------
	/** 
	* Devuelve true si está abierta una sesión y false en caso contrario
	* @return string Valor de la propiedad	
	*/
	public function correcta ()
	{
		return $this->correcta;
	}

	// ---------------------------------------------------------------------------
	/** 
	* Devuelve el rango del usuario registrado
	* @return string Valor de la propiedad
	*/	
	public function rango ()
	{
		if ($this->conexion !=null)
		{
			if ($this->idrango != null)
			$resultado = $conexion->consulta ("SELECT nombre FROM rango WHERE idrango='".$this->idrango."' LIMIT 0,1");
				
			if($obj = $resultado->fetch_object())
			{
				return $this->idrango;
			}
		}
		return null;
	}

	// ---------------------------------------------------------------------------
	/** 
	* Devuelve el rango del usuario registrado
	* @param $idproyecto ID del proyecto
	* @return string Valor de la propiedad
	*/
	public function rangoProyecto ($idproyecto = null)
	{
		if ($this->conexion != null)
		if ($idproyecto != null)
		if ($idproyecto != "")
		{
			$proy = new Proyecto ($this, $idproyecto);
			return $proy->usuarioRango ($this->idusuario);	
		}
		return null;
	}

	// ---------------------------------------------------------------------------
	/** 
	* Devuelve el id del usuario
	* @return string Valor de la propiedad
	*/	
	public function idusuario ()
	{
		return $this->idusuario;
	}

	// ---------------------------------------------------------------------------
	/** 
	* Devuelve true si el email indicado está formado correctamente y false en caso contrario
	* @param string $email Correo a analizar
	* @return string Valor de la propiedad
	*/
	static function validarEmail ($email)
	{
		
		if ($email != "" )
		{
			// Expresión regular del email
			// /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

			return true; // Email correcto
		}

		return false;
		
	}

	// ---------------------------------------------------------------------------
	/** 
	* Devuelve el valor de una variable de SESSION o null si ésta no existe
	* @param string $variable Variable solicitada
	* @return string Valor de la variable
	*/	
	static function varSesion ($variable)
	{
		if (isset($_SESSION[$variable]))
			return $_SESSION[$variable];

		return null;
	}

	// ---------------------------------------------------------------------------
	/** 
	* Asigna un proyecto a la session
	* @param string $idproyecto ID del proyecto solicitado
	*/
	public function asignaProyecto ($idproyecto)
	{
		if ($this->compruebaProyecto ($idproyecto))
		{
			$_SESSION["proyecto"]= $this->idproyecto= $idproyecto;
			
			// Mantenemos el entorno del resto de proyectos
			if (!isset($_SESSION["archivos_abiertos"]))
						$_SESSION["archivos_abiertos"]	= $this->archivos_abiertos	= array();
			if (!isset($_SESSION["archivos_abiertos"][$this->idproyecto]))
					$_SESSION["archivos_abiertos"][$this->idproyecto] 				= array();

			// Buscamos en BD todos los archivos del proyecto que han quedado abiertos
			$resultado = $this->consulta ("SELECT archivo FROM proyecto_archivo 
													WHERE idusuario=".$this->idusuario."
													AND idproyecto=".$this->idproyecto);
			

			if ($resultado != null) 
			while ($obj = $resultado->fetch_object())
			{
				$_SESSION["archivos_abiertos"][$this->idproyecto][$obj->archivo] = 'abierto';
			}
			// Fin de añadir archivos abiertos

		} 
		else
		{
			// Si alguien trata de entrar donde no tiene permiso se borrará todo
			$_SESSION["proyecto"]			= $this->idproyecto 					= null;
			// $_SESSION["archivos_abiertos"]	= $this->archivos_abiertos				= null;
		}
	}

	// ---------------------------------------------------------------------------
	/** 
	* Comprueba que el proyecto indicado pueda ser cargado para el usuario de la sesión y devuelve el rango en la edicion. 0 en caso de fallo
	* @param string $idproyecto ID del proyecto solicitado
	* @return boolean Devuelve 1 si el acceso al proyecto es correcto
	*/
	public function compruebaProyecto ($idproyecto)
	{
		if ($this->idusuario != null)
		if ($this->conexion != null)
		if ($idproyecto != null)
		{

			if ($this->rangoProyecto ($idproyecto) != null) return 1;
		}

		return 0;
	}

	// ---------------------------------------------------------------------------
	/** 
	* Devuelve el proyecto que se encuentra cargado
	* @return string Valor de la propiedad
	*/
	public function idproyecto ()
	{
		return $this->idproyecto ;
	}
	
	// ---------------------------------------------------------------------------
	/** 
	* Devuelve los archivos abiertos
	* @return array Listado archivos abiertos
	*/
	public function archivosAbiertos ()
	{
		return $_SESSION["archivos_abiertos"][$_SESSION['proyecto']];
	}

	// ---------------------------------------------------------------------------
	/** 
	* Ejecuta una consulta. Deriva la ejecución a Conexión
	* @param string $consulta Consulta MySQL
	* @return array Resultado de la consulta
	*/
	public function consulta ($consulta)
	{
		if ($this->conexion ==null) return null;
		
		return $this->conexion->consulta($consulta) ;
	}


	// ---------------------------------------------------------------------------
	/** 
	* Ejecuta varias consultas. Deriva la ejecución a Conexión
	* @param string $consulta Consulta MySQL
	* @return array Resultado de la consulta
	*/
	public function consultas ($consultas)
	{
		if ($this->conexion ==null) return null;
		
		return $this->conexion->consulta($consultas) ;
	}

	// ---------------------------------------------------------------------------
	/** 
	* Ejecuta una transacción en la conexión de la BD
	* @param string $tipo Tipo de transacción. COMMIT
	*/
	public function transaccion ($tipo)
	{
		switch ($tipo) {
			case 'inicio':
				$this->conexion->inicioTransaccion ();
				break;
			
			case 'fin':
				$this->conexion->finTransaccion();
				break;
			case 'abortar':
				$this->conexion->abortarTransaccion();
				break;
		}
	}

	// ---------------------------------------------------------------------------
	/** 
	* Devuelve el directorio de trabajo el proyecto cargado
	* @return string Valor de la propiedad
	*/
	public function directorioProyecto ()
	{
		if ($this->idproyecto != null)
		if ($this->compruebaProyecto ($this->idproyecto) )
		{
			// Dirección = Directorio de la aplicación + idproyecto
			return $this->direcotrio_proyectos."/".$this->idproyecto."/";
		}
		return "";
	}

	// ---------------------------------------------------------------------------
	/** 
	* Devuelve el directorio donde están los proyectos
	* @return string Valor de la propiedad
	*/
	public function directorioProyectos ()
	{
		// Directorio de proyectos
		return $this->direcotrio_proyectos;
	}

	// ---------------------------------------------------------------------------
	/** 
	* Añadimos un fichero a la vista activa
	* @param string $fichero Nombre del fichero
	* @return string Valor de la propiedad
	*/
	public function abrirVista ($fichero)
	{
		if ($this->idproyecto != null)
		{
			$_SESSION["archivos_abiertos"][$this->idproyecto][$fichero] = "abierto";
			// Eliminamos duplicados
			// $_SESSION["archivos_abiertos"][$this->idproyecto]		= array_unique($_SESSION["archivos_abiertos"][$this->idproyecto]);
		}
	}


	// ---------------------------------------------------------------------------
	/** 
	* Quitamos un fichero a la vista activa
	* @param string $fichero: nombre del fichero
	*/
	public function cerrarVista ($fichero)
	{
		if ($this->idproyecto != null)
		{
			$_SESSION["archivos_abiertos"][$this->idproyecto][$fichero] 	= "cerrado";
		}
	}

	// ---------------------------------------------------------------------------
	/** 
	* Cerramos la session y borramos todas las variables asociadas
	*/
	public function cerrar ()
	{

		foreach($_SESSION as $k => $v) $_SESSION[$k] = null;
		$_SESSION = array ();
		
		// Borramos las cookies de la session
		if (ini_get("session.use_cookies")) 
		{
		    $params = session_get_cookie_params();
		    /*
		    setcookie(session_name(), '', time() - 42000,
		        $params["path"], $params["domain"],
		        $params["secure"], $params["httponly"]
		    );
		    */
		}
		// Finalmente, destruir la sesión.
		session_destroy();
		$this->correcta = false;
	}

	// ---------------------------------------------------------------------------
	/** 
	* Devuelve una propiedad de un objeto de la BD
	* @param string $nombre Nombre de la propiedad (ejemplo: "proyecto.nombre")
	* @param string $id ID del objeto (ejemplo: "5")
	* @return string Devuelve una cadena con la propiedad
	*/
	public function propiedad ($tabla, $nombre, $id, $valor=null)
	{

		if ($this->conexion ==null) return null;
		if ($valor != null) $valor = array($valor);
		$resultado = $this->conexion->consultaBasica ($tabla, array($nombre), $id, $valor);

		if ($resultado != null)
			if ($valor == null)
				if($obj = $resultado->fetch_object())
				{
					return $obj->$nombre;
				}

		if ($resultado == null) return "";
		return $resultado ;
	}

	// ---------------------------------------------------------------------------
	/** 
	* Devuelve un listado de los usuarios del sistema [id, (apellidos.", ".nombre)]
	* @param string $nombre Nombre de la propiedad (ejemplo: "proyecto.nombre")
	* @param string $id ID del objeto (ejemplo: "5")
	* @return string Devuelve una cadena con la propiedad
	*/
	public function listarUsuarios ()
	{
		$retorno = array ();

		$resultado = $this->consulta ("SELECT usuario.id, CONCAT(usuario.apellidos,', ',usuario.nombre) as nombre FROM usuario WHERE id !=".$this->idusuario()." ORDER BY usuario.apellidos ");
				
		if ($resultado != null)
			while ($obj = $resultado->fetch_object())
		   		$retorno[] = $obj;
		   	
	    return $retorno;		
	}

}

?>