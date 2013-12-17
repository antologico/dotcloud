<?php
/*
	----------------------------------------------------		 
	Proyecto : 	.cloud
	Autor :		Antonio Juan Sánchez Martín
	Fecha : 	5 / 11 /2012	

	Listado de los proyectos en curso del usuario que se ha logeado

	Salida:
		- Listado JSON 
	----------------------------------------------------
*/	

	include_once ('../sesion.php'); 		// Define el objeto $conexion y el objeto $sesion
    include_once ('servidor.retrasar.php');
   	include_once ('../clases/Usuario.php');

    // Si la sesion no es correcta, se carga el index
    $retorno = 0;

    if ($sesion->correcta () && isset ($_POST['accion'])) 
    {
    	$proyecto = new Proyecto ($sesion);

    	switch ($_POST["accion"])
    	{
    		case "crear":
    			if (isset($_POST["nombre"]))
       		{
    				$retorno = $proyecto->crear($_POST["nombre"], $_POST["idcompilador"]);
    			}
    			else $retorno = -30;
    			break;
        case "borrar":
       			if (isset($_POST["id"]))
       			{
       				$retorno = $proyecto->borrar($_POST["id"]);
       			}
       			else $retorno = -20;
    			break; 
    	}
    }
    else $retorno = -1;
    
    echo($retorno);
?>