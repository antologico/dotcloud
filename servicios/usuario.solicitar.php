<?php
/*
	----------------------------------------------------		 
	Proyecto : 	.cloud
	Autor :		Antonio Juan Sánchez Martín
	Fecha : 	5 / 11 /2012	

	Listado de los proyectos en curso del usuario que se ha logeado

	Salida:
		- Cod. de error 0 si no hay error
	----------------------------------------------------
*/	

  include_once ('../sesion.php'); 		// Define el objeto $conexion y el objeto $sesion
  include_once ('servidor.retrasar.php');

    // Si la sesion no es correcta, se carga el index
    $retorno = 0;

    if (isset ($_POST['accion'])) 
    {
    	$usuario = new Usuario ($sesion);

      switch ($_POST["accion"])
     	{
      		case "solicitar":
      			if (isset($_POST["nombre"]))
         		{
      				$retorno = $usuario->crear($_POST["nombre"], $_POST["apellidos"], $_POST["email"], $_POST["password"]);
      			}
      			else $retorno = -30;
      			break;
     	}

    }
    else $retorno = -1;
    
    echo($retorno);
?>