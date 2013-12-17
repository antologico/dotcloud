<?php
/*
	----------------------------------------------------		 
	Proyecto : 	.cloud
	Autor :		Antonio Juan Sánchez Martín
	Fecha : 	5 / 11 /2012	

	Listado de usuairos para proyectos
	Salida:
		Listado JSON
        usuarios:       listado de todos los usuarios
            - id
            - nombre
        pariticpantes:  para modificar y borrar
            - id
            - nombre
            - rango (nombre del rango)
        rangos:
            - id
            - nombre            
	----------------------------------------------------
*/	

	include_once ('../sesion.php'); 			// Define el objeto $conexion y el objeto $sesion
    include_once ('servidor.retrasar.php');
   	include_once ('../clases/Proyecto.php');	
    include_once ('../clases/Rango.php');

    // Se comprueba corrección de la sesion, tipo y permisos de usuario
    if ($sesion->correcta ())
    {
    	$proyecto = new Proyecto ($sesion);
        $rango = new Rango ($sesion);

        // id - nombre - de todos los usuarios
        $usuarios = $sesion->listarUsuarios ();       
        
        // id - nombre -idrango de los participantes en el proyecto
        $participantes = $proyecto->listarUsuarios ();        
        
        // id - nombre de los participantes en el proyecto
        $rangos = $rango->listar ();        


        print_r(json_encode(array(array('usuarios' => $usuarios, "participantes" => $participantes, "rangos" => $rangos))));

    }
    else echo "-1"; 
?>