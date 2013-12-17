<?php
/*
	----------------------------------------------------		 
	Proyecto : 	.cloud
	Autor :		Antonio Juan Sánchez Martín
	Fecha : 	5 / 11 /2012	

	Listado de los proyectos en curso del usuario que se ha logeado

	Servicio:
		Entrada [POST]:
			- tipo: 'parada', 'ejecucacion', 'compilacion' 		
	Salida:
		exito:		0 en el caso de 'parada'
		error:		-1 (en todos los casos)
		- Listado JSON
			fin:	Final de ejecución o no
			datos:	Datos devueltos por el programa
			error:	Código de erro. 0 sin error
	----------------------------------------------------
*/	

	include_once ('../sesion.php');
	include_once ('../clases/Directorio.php');
	include_once ('../clases/Compilador.php');	
    include_once ('servidor.retrasar.php');	

    $acceso_proyecto = true;

	if (!$sesion->correcta ()) $acceso_proyecto = false;
    if ($sesion->idproyecto() == null)  $acceso_proyecto = false;
    

    $valor = array();
    $valor["error"] = 0;
    $valor["fin"] = "false";

	if (isset($_POST['tipo']) && $acceso_proyecto)
    {


		$directorio 							= new Directorio ($sesion->directorioProyecto ());
		$fifo 									= $directorio->direccionTuberiaOUT ($sesion);
		$entrada 								= $directorio->direccionTuberiaIN ($sesion);
		$compilador  							= new Compilador ($sesion);
		// Si la tuberia no existe es que no se ha lanzado el proceso
		
		// Comprobamos que no hay ya otro proceso en uso
		// Si es así lo cortamos
		if (isset($_SESSION['PID']))
			if (isset($_SESSION['PID']['proyecto']))
			if ($_SESSION['PID']['proyecto'] != $sesion->idproyecto()) 
				{
					$_POST['tipo'] = 'abortar'; 
					$valor["error"] = -50;
				}

		switch ($_POST['tipo'])
		{	
			case 'ejecucion':
			{

		    	if (!isset($_SESSION['PID']))
		    	{
		    		$_SESSION['PID']['proyecto'] = $sesion->idproyecto(); 
		    		// Lanzamos el proyecto
		    		$pid = $compilador->ejecutar($fifo, $entrada);
		    		// Y nos quedamos con el PID
		    		if ($pid != null)
		    		{
		    			$_SESSION['PID']['pid'] = $pid;
		    			$valor["fin"] = "false";
		    		}
		    		else 
		    			$valor["error"] = -2;
		    	}

		    	// Si no se ha creado aquí ha habido algún error
		    	
		    	
		    	if ($valor["error"] == 0)
		    	{
		    	   	if ($directorio->comprobarTuberiaOUT ($sesion) != null)
			    	{
			    		// Datos de la tuebría
						$valor["datos"] = $directorio->leerTuberia ($sesion);
						// Si la tubería está abierta el proceso sigue en uso
			    		$valor["fin"] = "false";
			    		
			    		if (!$directorio->comprobarProceso ($_SESSION['PID']['pid']))
						{
							$valor["datos"] .= $directorio->leerTuberia ($sesion);
							$valor["fin"] = "true";
							if ($fifo != null)
								unlink ($fifo);
							if ($entrada != null)
								unlink ($entrada);
						}
					}
					else $valor["error"] = -1;
				}
				
			}
			break;
			case 'compilacion':
			{
				$valor["datos"] = array ();
				$valor["datos"]["errores"] = $compilador->procesarCompilacion ( $compilador->compilar () );
				$valor["fin"] = "true";
			}
			break;
			case 'parada':
			{
				$valor["fin"] = "true";
			}
			break;
		}

	}
	else $valor["error"] = -3;

	if (($valor["fin"] == "true") || ($valor["error"] != 0))
	{
		
		if (isset($_SESSION['PID']))
		{
			// Borramos las variables de sesion
			if (isset($_SESSION['PID']['pid'] ))
				$directorio->ejecutar("kill ".$_SESSION['PID']['pid'] );
		
			if (isset($_SESSION['PID']['escritor'] ))
				$directorio->ejecutar("kill ".$_SESSION['PID']['escritor'] );
			unset ($_SESSION['PID']);
		}
	}
	print_r(json_encode(array($valor)));
?>