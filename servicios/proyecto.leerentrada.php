<?php
/*
	----------------------------------------------------		 
	Proyecto : 	.cloud
	Autor :		Antonio Juan Sánchez Martín
	Fecha : 	5 / 11 /2012	

	Listado de los proyectos en curso del usuario que se ha logeado

	Servicio:
		Entrada [POST]:
			- valor_entrada: texto de entrada		
	Salida:
		error:	0 si todo correcto, -1 si hay fallo
	----------------------------------------------------
*/	

	include_once ('../sesion.php');
	include_once ('../clases/Directorio.php');

    $acceso_proyecto = true;
    $pid = null;

	if (!$sesion->correcta ()) $acceso_proyecto = false;
    if ($sesion->idproyecto() == null)  $acceso_proyecto = false;
    
	// unset ($_SESSION['PID']['escritor']);

    if (isset($_POST['valor_entrada']) && $acceso_proyecto)
    {
	// Comprobamos que no hay ya otro proceso en uso
		// Si es así lo cortamos
		if (isset($_SESSION['PID']))
			if (isset($_SESSION['PID']['proyecto']))
			if ($_SESSION['PID']['proyecto'] == $sesion->idproyecto()) 
				{
					// Recogemos el valor de entrada y lo escribimos en la tubería de entrada que tiene asignada el proyecto
					$directorio = new Directorio ($sesion->directorioProyecto ());
					
					// Para evitar declarar  $_SESSION['PID']['escritor'] como null...
					$pid = null;
					if (isset($_SESSION['PID']['escritor'])) $pid = $_SESSION['PID']['escritor'];
					
					// Lanzamos la escritura
					// Enviamos un \n adecional para forzar la escritura
					$tecla = intval($_POST['valor_entrada']);
					if ($tecla == 8) $tecla = 46;
					$pid = $directorio->escribirTuberia ($pid, $sesion,  chr($tecla));
					
					if ($pid != null)  $_SESSION['PID']['escritor'] = $pid;
					// destruir si hay error
					else $_SESSION['PID']['escritor'] = null;

				}
	}
	if ($pid == null) echo -1;
	else echo 0;
?>