<?php
/*
	----------------------------------------------------		 
	Proyecto : 	.cloud
	Autor :		Antonio Juan Sánchez Martín
	Fecha : 	5 / 11 /2012	

	Listado de los proyectos en curso del usuario que se ha logeado

	Salida:
		- Listado JSON 
	----------------------------------------------------
*/	

	include_once ('../sesion.php'); 		// Define el objeto $conexion y el objeto $sesion
    include_once ('servidor.retrasar.php');
   	include_once ('../clases/Usuario.php');

    // Si la sesion no es correcta, se carga el index
    if ($sesion->correcta ()) 
    {
		$usuario = new Usuario ($sesion);

    	$resultado = $usuario->proyectos();
        if ($resultado != null)
		{    
			// Adecuamos el objeto devuelto a un array para poder imprimirlo en JSON    
	        $json = array();
			 
	        while ($obj = $resultado->fetch_object())
	        {
	        	$json[] = $obj;
	        }
	        
	        print_r(json_encode($json));
	    }
    }
?>