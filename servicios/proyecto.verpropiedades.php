<?php
/*
	----------------------------------------------------		 
	Proyecto : 	.cloud
	Autor :		Antonio Juan Sánchez Martín
	Fecha : 	5 / 11 /2012	

	Listado de los compiladores disponibles

	Servicio:
		Entrada [POST]:
			idproyecto:			(opcional) id del proyecto consultado, en caso de no marcase se indica el actual
		Salida:
			error:				0 en caso de no-error
			idcompilador: 		compilador usuado por el proyecto indicado (vacio si error)
			nombre		
			compiladores: 		(vacio si error) listado de compiladores
					-id:			id compilador
					-nombre:		nombre del compilador
			propiedades:
					-id:			id de la propiedad
					-nombre:		nombre de la propiedad
					-valordefecto:	valor de la propiedad
					
					
	----------------------------------------------------
*/	

	include_once ('../sesion.php');
	include_once ('../clases/Compilador.php');	
	include_once ('../clases/Proyecto.php');
    include_once ('servidor.retrasar.php');	

    $retorno = array ();
    $retorno["error"] = 0;

    if ($sesion->correcta ())
    {
		$idproyecto = null;
		$idcompilador = null;
		
		if (isset($_POST['idcompilador'])) $idcompilador = $_POST['idcompilador'];

				
		$compilador = new Compilador ($sesion, $idcompilador);
		// Listado de compiladores

		$idproyecto = $sesion->idproyecto();

		// compilador usado
		$retorno["idcompilador"] = 0;
		if ($idproyecto != null)
		{
			$pro = new Proyecto ($sesion);
			$retorno["idcompilador"] = $pro->idcompilador ();
			$retorno["nombre"] = $pro->nombre ();
			$creador = new Usuario ($sesion, $pro->idcreador());
			$retorno["creador"] = $creador->nombre()." ".$creador->apellidos()." (".$creador->email().")";
			$retorno["fecha"] = $pro->fecha();
			$compi= new Compilador ($sesion, $retorno["idcompilador"]);
			$retorno["compilador"] = $compi->nombre();
		} 

		//listado de compiladores
		$retorno["compiladores"] = $compilador->listar ();



		$retorno["propiedades"] = $compilador->propiedades ($idproyecto);

		$retorno["error"] = 0;
	}
	else 
		$retorno["error"] = -1;

	print_r(json_encode(array($retorno)));
?>