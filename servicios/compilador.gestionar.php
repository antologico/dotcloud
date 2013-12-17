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
                $compilador = new Compilador ($sesion);
                $sesion->transaccion ("inicio");
      				  $retorno = $compilador->crear($_POST["nombre"]);
                if ($retorno != "") {
                  $sesion->transaccion ("abortar");
                  $retorno = -12;
                }
                else
                {
                    // asignamos las propiedades
                    $compilador->propiedad("Compilación", null, $_POST["compilacion"]);
                    $compilador->propiedad("Ejecución", null, $_POST["ejecucion"]);
                    $compilador->propiedad("Regla de compilación", null, $_POST["regladecompilacion"]);
                    $compilador->propiedad("Main", null, $_POST["main"]);
                    $sesion->transaccion ("fin");
                }
      			}
      			else $retorno = -30;
      			break;
          case "borrar":
         			if (isset($_POST["id"]))
         			{
                 $compilador = new Compilador ($sesion);
         				 $retorno = $compilador->borrar($_POST["id"]);
         			}
         			else $retorno = -20;
      			break; 
          case "modificar":
              if (isset($_POST["id"]))
              {
                $compilador = new Compilador ($sesion, $_POST["id"]);
                $sesion->transaccion ("iniciar");
                if (isset($_POST["nombre"]))
                  $compilador->nombre($_POST["nombre"]);
                if (isset($_POST["compilacion"]))
                  $compilador->propiedad("Compilación", null, $_POST["compilacion"]);
                if (isset($_POST["ejecucion"]))
                  $compilador->propiedad("Ejecución",null, $_POST["ejecucion"]);
                if (isset($_POST["regladecompilacion"]))
                 $compilador->propiedad("Regla de compilación",null, $_POST["regladecompilacion"]);
                if (isset($_POST["main"]))
                  $compilador->propiedad("Main",null, $_POST["main"]);
                
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