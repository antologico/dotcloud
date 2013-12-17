<?php
/*
	----------------------------------------------------		 
	Proyecto : 	.cloud
	Autor :		Antonio Juan Sánchez Martín
	Fecha : 	5 / 11 /2012	

	Listado de los compiladores disponibles

	Servicio:
		Salida:
			rango: 				rango del usuario en uso (-1 en casod e error)
					
	----------------------------------------------------
*/	

	include_once ('../sesion.php');
	include_once ('../clases/Usuario.php');
    include_once ('servidor.retrasar.php');	

    $retorno = array ();
    $retorno = -1;

    if ($sesion->correcta ())
    {
		$usuario = new Usuario ($sesion);
		$retorno = $usuario->rango();
	}

	echo($retorno);
?>