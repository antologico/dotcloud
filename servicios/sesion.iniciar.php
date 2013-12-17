<?php
/*
	----------------------------------------------------		 
	Proyecto : 	.cloud
	Autor :		Antonio Juan Sánchez Martín
	Fecha : 	5 / 11 /2012	

	Inicio de sesión de usuario
	
	Servicio:
		Entrada [POST]:
			- email 		
			- password
		Salida:
			- exito: 0
			- error: Códigod el error 		
	----------------------------------------------------
*/	

		include_once ('../sesion.php'); 		// Define el objeto $conexion y el objeto $sesion
    	include_once ('servidor.retrasar.php');

		if (isset($_POST["email"]) && isset($_POST["password"])) 
		{
			// Redefinimos la session
			$sesion = new Sesion ($conexion, $directorio_proyectos, $_POST["email"], $_POST["password"]);
		}

		if ($sesion->correcta()) echo 0;
		else echo 1;
?>