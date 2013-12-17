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

		include_once ('../sesion.php'); 	// Define el objeto $conexion
		include_once ('../clases/Sesion.php');
	    include_once ('servidor.retrasar.php');
	    
		if ($sesion != null)
		{
			$sesion->cerrar();
		}
?>