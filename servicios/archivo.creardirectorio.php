<?php
/*
	----------------------------------------------------		 
	Proyecto : 	.cloud
	Autor :		Antonio Juan Sánchez Martín
	Fecha : 	5 / 11 /2012	

	Crea un nuevo directorio
	Servicio:
		Entrada [POST]
			- ruta:		Ruta completa (con el nombre del direcotorio nuevo incluido)
		Salida:
			- exito: 0
			- error: Código del error 	
	----------------------------------------------------
*/

	include_once ('../sesion.php');
	include_once ('../clases/Directorio.php');
    include_once ('servidor.retrasar.php');	


    $acceso_proyecto = true;

	if (!$sesion->correcta ()) $acceso_proyecto = false;
    if ($sesion->idproyecto() == null)  $acceso_proyecto = false;
    
    if ($acceso_proyecto)
    {
		$directorio = new Directorio ($sesion->directorioProyecto ());
		$error = $directorio->crearDirectorio($_POST['ruta']);
		if ($error != 0) echo $error;
		else echo 0;
	}
	else echo 1001;
?>