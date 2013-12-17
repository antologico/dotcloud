<?php
// ---------------------------------------------------------------------------
/**
* Paquete .cloud
* @package dotcloud
*/
// ---------------------------------------------------------------------------

include_once ("ObjetoBD.php");
include_once ("Proyecto.php");
include_once ("Rango.php");
include_once("phpmailer/class.phpmailer.php");
include_once("phpmailer/class.smtp.php");

// ---------------------------------------------------------------------------
/**
* Clase entidad para la gestión de los usuarios
* @author Antonio Juan Sánchez Martín
* @version 0.9
*/
class Usuario extends ObjetoBD
{
	// ---------------------------------------------------------------------------
	/** 
	* Constructor
	* @param Sesion $sesion Referencia a la sesión
	* @param int $idusuario ID del objeto
	*/
	public function __construct ($sesion, $idusuario = null) 
	{
		$miid = $idusuario;
		if ($miid == null) $miid = $sesion->idusuario();
		parent::__construct ($sesion, $miid, 'usuario');
	} 

	// ---------------------------------------------------------------------------
	/** 
	* Devuelve los apellidos del usuario
	* @param string $valor Valor del elemento	
	* @return string Valor de la propiedad
	*/
	public function apellidos ($valor = null)
	{
		return  $this->p ("apellidos", $valor);
	}

	// ---------------------------------------------------------------------------
	/** 
	* Devuelve el email del usuario
	* @param string $valor Valor del elemento	
	* @return string Valor de la propiedad
	*/
	public function email ($valor = null)
	{
		return  $this->p ("email", $valor);
	}


	// ---------------------------------------------------------------------------
	/** 
	* Devuelve el password del usuario
	* @param string $valor Valor del elemento	
	* @return string Valor de la propiedad
	*/
	public function password ($valor = null)
	{
		if ($valor == null) return "";
		
		return $this->p ("password", hash('SHA256', $valor));
	}


	// ---------------------------------------------------------------------------
	/** 
	* Devuelve el nombre del compilador que utilizará proyecto
	* @param string $valor Valor del elemento	
	* @return string Valor de la propiedad
	*/
	public function rango ($valor = null)
	{
		if ($valor != null)
			return $this->p ("idrango", $valor);

		return $this->sesion->propiedad ("rango", "nombre", $this->p ("idrango"));
	}

	// ---------------------------------------------------------------------------
	/** 
	* Devuelve el nombre del compilador que utilizará proyecto
	* @return string Valor de la propiedad
	*/
	public function idrango ()
	{
		return $this->p ("idrango");
	}

	// ---------------------------------------------------------------------------
	/** 
	* Devuelve el id, nombre y rango de todos los proyectos del usuario
	* @return array Listado de proyectos
	*/
	public function proyectos ()
	{

		if ($this->rango() == "Administrador") 
			return $this->sesion->consulta ("SELECT DISTINCT proyecto.id, proyecto.nombre as nombre, 'Administrador' as rango FROM proyecto ORDER BY proyecto.nombre");
		else
			return $this->sesion->consulta ("SELECT DISTINCT proyecto.id, proyecto.nombre as nombre, rango.nombre as rango FROM proyecto_usuario, proyecto, rango
									WHERE 	( proyecto_usuario.idusuario=".$this->id." AND proyecto_usuario.idproyecto = proyecto.id AND proyecto_usuario.idrango = rango.id )
											ORDER BY proyecto.nombre");
	}	

	// ---------------------------------------------------------------------------
	/** 
	* Devuelve el id, nombre y rango de todos los usuarios, a los que el usuario actual puede acceder
	* @return array Listado de usuarios
	*/
	public function listar ()
	{

		$retorno = array ();
		if ($this->sesion)
		{
			$resultado = null;
			if ($this->rango() == "Administrador") 
				$resultado= $this->sesion->consulta ("SELECT DISTINCT usuario.id, concat(usuario.apellidos, ', ', usuario.nombre) as nombre, 'Administrador' as rango FROM usuario ORDER BY usuario.apellidos");
			else
				$resultado= $this->sesion->consulta ("SELECT usuario.id, usuario.nombre as nombre, rango.nombre as rango FROM usuario, rango
									WHERE usuario.idrango = rango.id AND usuario.id = "+$this->id);
			
			if ($resultado != null)
			while ($obj = $resultado->fetch_object())
	        {
	        	$retorno[] = $obj;
	        }
	    }

	    return $retorno;
	}	

	// ---------------------------------------------------------------------------
	/** 
	* Elimina el objeto de la BD
	* @param int $id ID del usuario
	* @return int Si ha tenido éxito devuelve un 0
	*/
	public function borrar ($id)
	{
		// Sólo los administradores pueden borrar usuarios
		if ($this->rango() == "Administrador") 
			return parent::borrar ($id);
		return -2;
	}

	// ---------------------------------------------------------------------------
	/** 
	* Crea un usuario en la BD de datos
	* @param string $nombre Nombre de usuario
	* @param string $apellidos Apellidos de usuario
	* @param string $email Email de usuario
	* @param string $password Password de usuario
	* @param string $idrango ID del rango de usuario					
	*/
	public function crear ($nombre, $apellidos, $email, $password, $idrango=3)
	{
		if ($this->rango() == "Administrador") 
		{
			$resultado= $this->sesion->consulta ("INSERT INTO usuario (nombre, apellidos, email, password, idrango) VALUES (
				'".$nombre."', '".$apellidos."', '".$email."', '".hash('SHA256', $password)."', '".$idrango."')");
			
			if ($resultado != null) 
			{
				$res = $this->sesion->consulta ("SELECT @@identity AS id");
				if ($obj = $res->fetch_object())
					$this->id = $obj->id;


				// Enviamos correo al usuario
				
				include("../configuracion.php");

				$mail = new PHPMailer();
				$mail->IsSMTP();
				$mail->SMTPAuth = true;
				$mail->SMTPSecure = "ssl";
				$mail->Host = $mail_host;
				$mail->Port = $mail_puerto;
				$mail->Username = $mail_usuario;
				$mail->Password = $mail_password;

				$mail->From = $mail_usuario;
				$mail->FromName = ".dotcloud";
				$mail->Subject = "Bienvenio a / Welcome to .Cloud";
				$mail->AltBody = "Bienvenio a / Welcome to .Cloud, ".$nombre."\n\n Su password es / Your password is: ".$password;
				$mail->MsgHTML("Bienvenio a / Welcome to .Cloud, ".$nombre."<br /><br /> <ul> Su password es / Your password is: <b>".$password."</b></ul> Esperamos que disfrute de la programación / Enjoy programming !");

				$mail->AddAddress($email, $nombre);
				$mail->IsHTML(true);

				$mail->Send();

				return 0;
			}
		}
		return -2;
	}
}

?>

