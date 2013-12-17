<?php
// ---------------------------------------------------------------------------
/**
* Paquete .cloud
* @package dotcloud
*/
// ---------------------------------------------------------------------------
include_once ("ObjetoBD.php");
include_once ("Compilador.php");
include_once ("Directorio.php");
include_once ("Usuario.php");

// ---------------------------------------------------------------------------
/**
* Clase entidad para la gestión de los proyectos
* @author Antonio Juan Sánchez Martín
* @version 0.9
*/

class Proyecto extends ObjetoBD
{
	// ---------------------------------------------------------------------------
	/** 
	* Constructor
	* @param Sesion $sesion Referencia a la sesión
	* @param int $id ID del objeto
	*/
	public function __construct ($sesion, $id = null) 
	{
		if ($id == null) $id = $sesion->idproyecto();
		parent::__construct ($sesion, $id, 'proyecto');	
	} 

	// ---------------------------------------------------------------------------
	/** 
	* Devuelve el id del compilador que utilizará proyecto
	* @param string $valor Valor del elemento	
	* @return string Valor de la propiedad
	*/
	public function idcompilador ($valor = null)
	{
		return $this->p ("idcompilador", $valor);
	}
	
	// ---------------------------------------------------------------------------
	/** 
	* Devuelve la fecha de creación del proyecto
	* @param string $valor Valor del elemento
	* @return string Valor de la propiedad
	*/
	public function fecha ($valor = null)
	{
		return  $this->p ("fecha", $valor);
	}


	// ---------------------------------------------------------------------------
	/** 
	* Devuelve el id del usuario creador del proyecto
	* @param string $valor Valor del elemento	
	* @return string Valor de la propiedad
	*/
	public function idcreador ($valor = null)
	{
		return $this->p ("idusuario", $valor);
	}


	// ---------------------------------------------------------------------------
	/** 
	* Devuelve el estado del proyecto
	* @param string $valor Valor del elemento
	* @return string Valor de la propiedad
	*/
	public function estado ($valor = null)
	{

		return $this->p ("estado", $valor);
		
	}

	// ---------------------------------------------------------------------------
	/** 
	* Devuelve el id, nombre y email de todos los usuarios del proyecto. Null en caso de fallo
	* @return array Listado de usuarios
	*/
	public function usuarios ()
	{
		return $this->sesion->consulta ("SELECT usuario.id, usuario.nombre, usuario.email FROM proyecto_usuario, usuario 
									WHERE 	proyecto_usuario.idproyecto=".$id." 
											AND proyecto_usuario.idusuario = usuario.id ");
	}

	// ---------------------------------------------------------------------------
	/** 
	* Devuelve el rango de un usuario con respecto al proyecto
	* @param string $idusuario Valor del elemento
	* @return string Valor de la propiedad
	*/
	public function usuarioRango ($idusuario = null)
	{
		$res = null;

		if ($this->sesion)
		{

			if ($idusuario == null) $idusuario = $this->$sesion->idusuario();

			$usuario = new Usuario ($this->sesion, $idusuario);

			if (($usuario->rango() == "Administrador") || ($idusuario == $this->idcreador())) return "Administrador";

			$res = $this->sesion->consulta ("SELECT rango.nombre FROM proyecto_usuario, rango 
						WHERE proyecto_usuario.idproyecto='".$this->id."'
						AND proyecto_usuario.idrango= rango.id 
						AND proyecto_usuario.idusuario='".$idusuario."'");
		
			if ($res != null) 
			if($obj = $res->fetch_object())
					return ($obj->nombre);
			
		}
		return "";
	}

	// ---------------------------------------------------------------------------
	/** 
	* Crea un nuevo proyecto con los valores indicados asociados
	* @param string $nombre nombre del proyecto
	* @param string $idcompilador Id del compilador que utilizará el proyecto
	* @return string Devuelve -1 en caso de fallo
	*/ 
	public function crear ($nombre, $idcompilador)
	{
		$res = null;
		if ($this->sesion != null)
		{
			$usuario 		= new Usuario ($this->sesion);
			$usuariorango 	= $usuario->rango();
			$res 			= null;
			$accionar 		= 0;

			if (( $usuario->rango() == 'Administrador') ||
				( $usuario->rango() == 'Editor')) $accionar = true;

			if ($accionar)
			{
				$this->sesion->transaccion("iniciar");

				$res = $this->sesion->consulta ("INSERT INTO proyecto (nombre, idcompilador, idusuario) 
										VALUES ('".$nombre."', '".$idcompilador."', '".$usuario->id()."'); ");
				
				$id_proyecto_creado = null;


				$res = $this->sesion->consulta ("SELECT @@identity AS id");
				if ($obj = $res->fetch_object())
					$id_proyecto_creado = $obj->id;
				
				$this->id = $id_proyecto_creado;

				if (($res != null) && ($id_proyecto_creado != null))
				{
					// Creamos los directorios asociados

					$usuario = 'proy'.$id_proyecto_creado;
				    $uid   = 3000+$id_proyecto_creado; 
				    $gid   = $uid;

					$dir = new Directorio ($this->sesion->directorioProyectos().'/'.$id_proyecto_creado, $usuario);
					$this->sesion->transaccion("fin");

					
					// Creamos el usuario asociado al proyecto
					/*
					$fh = fopen("/etc/passwd", 'w+');
		            fwrite($fh, $usuario.":x:".$uid.":".$gid.":".$usuario.":/:/bin/bash");
		            fclose($fh);
		            
		            $fh = fopen("/etc/group", 'w+');
		            fwrite($fh, $usuario.":x:".$gid.":");
		            fclose($fh);
		            
		            $fh = fopen("/etc/shadow", 'w+');
		            fwrite($fh, $usuario.":".crypt($usuario.$usuario->id())."::0:::::0");
		            fclose($fh);
					*/
					// Todo correcto
					return 0;
				}
				
				$this->sesion->transaccion("abortar");
				
			}
		}
		return -5;
	}

	// ---------------------------------------------------------------------------
	/** 
	* Crea un nuevo proyecto con los valores indicados asociados
	* @param string $nombre nombre del proyecto
	* @param string $idcompilador ID del compilador que utilizará el proyecto
	* @param string $id ID del proyecto	
	* @return string Devuelve -1 en caso de fallo
	*/
	public function modificar ($nombre, $idcompilador, $id=null)
	{
		$usuario 		= new Usuario ($this->sesion);
		$res 			= null;
		$accionar 		= 0;

		if (( $usuario->rango() == 'Administrador') ||
			( $this->idcreador() == $usuario->id() ) ||
			( $this->usuarioRango() == 'Administrador') ||
			( $this->usuarioRango() == 'Editor'))
			$accionar = 1;			

		if ($id == null) $id = $this->id();

		if ($accionar)
		$res = $this->sesion->consulta ("UPDATE proyecto SET 
									nombre = '".$nombre."', idcompilador = '".$idcompilador."' WHERE id='".$id."'");
		
		if ($res == null) return -1;
		else return 0;
	}

	// ---------------------------------------------------------------------------
	/** 
	* Borrar el proyecto indicado
	* @param string $id id del proyecto, en caso de no indicarlo se borra el actual
	* @return string Devuelve 0 en caso de éxito, -1 en caso de fallo
	*/
	public function borrar ($id)
	{
		if ($id == null) return -10;
		$usuario 		= new Usuario ($this->sesion);
		$res 			= null;
		$accionar 		= 0;
		$proyecto 		= new Proyecto ($this->sesion, $id);

		if (( $usuario->rango() == 'Administrador') ||
			( $proyecto->idcreador() == $usuario->id() ))
			$accionar = 1;			

		if ($accionar)
		{

			$this->sesion->transaccion("iniciar");
			$res = $this->sesion->consulta ("DELETE proyecto FROM proyecto WHERE proyecto.id='".$id."'");
			if ($res != null)
			{
				$dir = new Directorio ();
				// Borramos también los archivos asociados
				if ($dir->borrarDirectorio ($this->sesion->directorioProyectos()."/".$id))
				{
					$this->sesion->transaccion("fin");
					return 0;
				}
			}
		}
		$this->sesion->transaccion("abortar");
		return -1;
	}	


	// ---------------------------------------------------------------------------
	// Altas, bajas y modificaciones de usuarios en proyecto
	// ---------------------------------------------------------------------------
	/** 
	* Devuelve el id, nombre y email de todos los usuarios del proyecto. Null en caso de fallo
	* @param string $idusuario id del usuario a tratar
	* @param string $idrango Sin el idrango == null o no definido lo que se hace es borrar el rol
	* @return string Devuelve 0 en caso de éxito, -1 en caso de fallo
	*/
	public function asignarRango ($idusuario, $idrango=null)
	{
		$usuario 		= new Usuario ($this->sesion);
		$res 			= null;
		$accionar 		= 0;


		if (( $usuario->rango() == 'Administrador') ||
			( $this->idcreador() == $usuario->id() ) ||
			( $this->usuarioRango() == 'Administrador') ||
			( $this->usuarioRango() == 'Editor')) 
			$accionar = 1;

		$id = $this->id();

		$this->sesion->transaccion ('inicio');


		if ($accionar)
		{
			if ($idrango)
			{
				if ($this->usuarioRango($idusuario) != '') 
				{
					$res = $this->sesion->consulta ("UPDATE proyecto_usuario SET idrango='".$idrango."' WHERE idproyecto='".$id."' AND idusuario='".$idusuario."'");
				}
				else
				{
					$res = $this->sesion->consulta ("INSERT INTO proyecto_usuario (idproyecto, idusuario, idrango) VALUES ('".$id."', '".$idusuario."', '".$idrango."')");

				}
			}
			else
			{
				// Borramos el ROL y sus aperturas de archivos
				$res = $this->sesion->consulta ("DELETE proyecto_usuario FROM proyecto_usuario WHERE idproyecto='".$id."' AND idusuario='".$idusuario."'");
				if ($res) $res = $this->sesion->consulta ("DELETE proyecto_archivo FROM proyecto_archivo WHERE idproyecto='".$id."' AND idusuario='".$idusuario."'");
			}
			
		}

		if ($res == null) 
		{
			$this->sesion->transaccion ('abortar');
			return -2;
		}
		
		$this->sesion->transaccion ('fin');
		return 0;
	}	


	// ---------------------------------------------------------------------------
	/** 
	* Devuelve el listado de usuarios del proyecto y su rango
	* @return array Listato de usuarios
	*/
	public function listarUsuarios ()
	{
		$retorno = array ();

		if ($this->sesion)
		{
			if ($this->sesion)
			{
				$resultado = $this->sesion->consulta ("SELECT usuario.id, CONCAT(usuario.apellidos,', ',usuario.nombre) as nombre, rango.nombre as 'rango'
											FROM proyecto_usuario, usuario, rango 
											WHERE 	proyecto_usuario.idusuario=usuario.id 
												AND rango.id = proyecto_usuario.idrango 
												AND proyecto_usuario.idproyecto='".$this->id."'
												AND usuario.id != ".$this->sesion->idusuario()."
												ORDER BY usuario.apellidos");
			
				if ($resultado != null)
				while ($obj = $resultado->fetch_object())
		        {
		        	$retorno[] = $obj;
		        }
		    }
	    }
	    
	    return $retorno;		
	}
}

?>

