<?php
/*
	----------------------------------------------------		 
	Proyecto : 	.cloud
	Autor :		Antonio Juan Sánchez Martín
	Fecha : 	5 / 11 /2012	

	Inicio de sesión de usuario
	
	Servicio:
		Entrada [POST]:
			- idioma
		Salida:
			- idioma de la sesión		
	----------------------------------------------------
*/	

		include_once ('../sesion.php'); 		// Define el objeto $conexion y el objeto $sesion

		if (isset($_POST["idioma"])) 
		{
			// Redefinimos la session
			if (isset($_POST["idioma"]))
			$_SESSION['idioma'] = $_POST["idioma"];
		}
		if (isset($_SESSION["idioma"])) 
		echo $_SESSION['idioma'];

?>