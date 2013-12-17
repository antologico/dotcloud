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
    	$usuario = new Usuario ($sesion);

      if ($usuario->rango() == "Administrador")
      {
      	switch ($_POST["accion"])
      	{
      		case "crear":
      			if (isset($_POST["nombre"]))
         		{
      				$retorno = $usuario->crear($_POST["nombre"], $_POST["apellidos"], $_POST["email"], $_POST["password"], $_POST["idrango"]);
      			}
      			else $retorno = -30;
      			break;
          case "borrar":
         			if (isset($_POST["id"]))
         			{
         				$retorno = $usuario->borrar($_POST["id"]);
         			}
         			else $retorno = -20;
      			break; 
          case "modificar":
              if (isset($_POST["id"]))
              {
                $sesion->transaccion ("iniciar");
                $usuario = new Usuario ($sesion, $_POST["id"]);
                $usuario->nombre ($_POST["nombre"]);
                $usuario->apellidos ($_POST["apellidos"]);
                $usuario->password ($_POST["password"]);
                $usuario->idrango ($_POST["idrango"]);
                $sesion->transaccion ("fin");
              }
              else $retorno = -20;
            break; 
      	}
      }
      else $retorno = -40;
    }
    else $retorno = -1;
    
    echo($retorno);
?>