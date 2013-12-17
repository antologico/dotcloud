<?php
/*
	----------------------------------------------------		 
	Proyecto : 	.cloud
	Autor :		Antonio Juan Sánchez Martín
	Fecha : 	5 / 11 /2012	

	Guarda el contenido de un archivo  
	Servicio:
		Entrada [POST]
			- archivo:		Nombre de un archivo 	
			- contenido:	Contenido del archivo
			- liberado: 	(opcional) Inidica que se cierra el archivo. Así puede ser abierto por otro usuario
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
    

    if ($acceso_proyecto && isset($_FILES['archivo']) && isset($_POST['destino']))
    {
		$directorio = new Directorio ($sesion->directorioProyecto ());
		$error = $directorio->cargar($_FILES['archivo'], $_POST['destino']);
		if ($error != 0) echo $error;
		else echo 0;
	}
	else echo -15;
?>