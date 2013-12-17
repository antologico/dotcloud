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
			JSON con los siguientes campos
			- Contenido: 	contenido del archivo		
			- Error: 		código del error (si hay, sino 0)
	----------------------------------------------------
*/	
	include_once ('../sesion.php');
	include_once ('../clases/Directorio.php');
    include_once ('servidor.retrasar.php');

    $acceso_proyecto = true;

	if (!$sesion->correcta ()) $acceso_proyecto = false;
    if ($sesion->idproyecto() == null)  $acceso_proyecto = false;
    $resultado = array (); 
    $resultado["contenido"] = "Error";
	$resultado["errores"] 	= -10; // error de acceso
    
    if ($acceso_proyecto)
    {
		$directorio = new Directorio ($sesion->directorioProyecto ());
		$cod_error = 0;
		$resultado["contenido"] = utf8_encode ($directorio->leer($_POST['archivo'], $sesion, $cod_error));
		$resultado["errores"] 	= $cod_error;

	}

	print_r(json_encode(array($resultado)));	
?>