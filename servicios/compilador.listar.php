<?php
/*
	----------------------------------------------------		 
	Proyecto : 	.cloud
	Autor :		Antonio Juan Sánchez Martín
	Fecha : 	5 / 11 /2012	

	Listado de los compiladores disponibles

	Servicio:
		Salida:
			compiladores: 		(vacio si error) listado de compiladores
					-id:			id compilador
					-nombre:		nombre del compilador
					-rango: 		nombre del rango (para modificaciones)
			-1: 					Si hay error
					
					
	----------------------------------------------------
*/	

	include_once ('../sesion.php');
	include_once ('../clases/Compilador.php');	
	include_once ('servidor.retrasar.php');	

    $retorno = array ();
    $retorno = 0;

    if ($sesion->correcta ())
    {
		
		$compilador = new Compilador ($sesion);
		//listado de compiladores
		$retorno = $compilador->listar ();
	}
	else 
		$retorno = -1;

	print_r(json_encode($retorno));
?>