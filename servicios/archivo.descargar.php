<?php
/*
	----------------------------------------------------		 
	Proyecto : 	.cloud
	Autor :		Antonio Juan Sánchez Martín
	Fecha : 	5 / 11 /2012	

	Cargardor del contenido de los archivos
	Servicio:
		Entrada [POST]:
			- ruta del archivo deseado
		Salida:
			- contenido del archivo		
			- Error: código del error
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
		$error = $directorio->descargar($_GET['archivo'], $sesion);
		if ($error != 0) echo "Error: "+$error;
	}
?>