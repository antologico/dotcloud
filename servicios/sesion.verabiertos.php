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
			- JSON con con los archivos abiertos de una determinada session 		
	----------------------------------------------------
*/	

		include_once ('../sesion.php'); 		// Define el objeto $conexion y el objeto $sesion
    	include_once ('servidor.retrasar.php');

		if (isset($_POST["email"]) && isset($_POST["password"])) 
		{
			// Redefinimos la session
			$sesion = new Sesion ($conexion, $_POST["email"], $_POST["password"]);
		}

		if ($sesion->correcta()) print_r(json_encode(array($sesion->archivosAbiertos ())));
?>