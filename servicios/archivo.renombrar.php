<?php
/*
	----------------------------------------------------		 
	Proyecto : 	.cloud
	Autor :		Antonio Juan Sánchez Martín
	Fecha : 	5 / 11 /2012	

	Borra un archivo 
	Servicio:
		Entrada [POST]
			- archivo:		Nombre de un archivo 	
			- contenido:	Contenido del archivo
			- liberado: 	(opcional) Inidica que se cierra el archivo. Así puede ser abierto por otro usuario
		Salida:
			- exito:		0
			- erro: 		Código del error 	
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
		$error = $directorio->mover($_POST['archivo'], $_POST['nombre_nuevo'],  $sesion);
		echo $error;
	}
	else echo 1001;
?>