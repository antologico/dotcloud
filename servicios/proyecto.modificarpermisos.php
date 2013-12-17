<?php
/*
	----------------------------------------------------		 
	Proyecto : 	.cloud
	Autor :		Antonio Juan Sánchez Martín
	Fecha : 	5 / 11 /2012	

	Administración de proyectos: altas bajas y modificaciones

	Entradas:
		Valroes post [POST]
		idusuario_modificar: 		id del usuario 
		idrango_modificar: 			id del Rango
	Salida:
		-1 en caso de error
	----------------------------------------------------
*/	

	include_once ('../sesion.php');
    include_once ('../clases/Directorio.php');
    include_once ('../clases/Compilador.php');  
    include_once ('servidor.retrasar.php'); 

    $acceso_proyecto = true;

    if (!$sesion->correcta ()) $acceso_proyecto = false;
    if ($sesion->idproyecto() == null)  $acceso_proyecto = false;
    


    if ($acceso_proyecto && isset($_POST['idusuario_modificar']))
    {
    	$proyecto = new Proyecto ($sesion);
    	echo $proyecto->asignarRango ($_POST['idusuario_modificar'], $_POST['idrango_modificar']);
    }
    else echo '-65';

?>