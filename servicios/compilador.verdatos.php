<?php
/*
  ----------------------------------------------------     
  Proyecto :  .cloud
  Autor :   Antonio Juan Sánchez Martín
  Fecha :   5 / 11 /2012  

  Listado de los proyectos en curso del usuario que se ha logeado

  Salida:
    - Listado JSON 
  ----------------------------------------------------
*/  

  include_once ('../sesion.php');     // Define el objeto $conexion y el objeto $sesion
    include_once ('servidor.retrasar.php');
    include_once ('../clases/Usuario.php');

    // Si la sesion no es correcta, se carga el index
    if ($sesion->correcta ()) 
    {
    $usuario = new Usuario ($sesion);
    if ($usuario->rango() == "Administrador")
    if (isset($_POST["id"]))
    {
          $compilador                       = new Compilador ($sesion, $_POST["id"]);
          $resultado                        = array ();
          $resultado["nombre"]              = $compilador->nombre();
          $resultado["compilacion"]         = $compilador->propiedad("Compilación");
          $resultado["ejecucion"]           = $compilador->propiedad("Ejecución");
          $resultado["regladecompilacion"]  = $compilador->propiedad("Regla de compilación");
          $resultado["main"]                = $compilador->propiedad("Main");

          // Adecuamos el objeto devuelto a un array para poder imprimirlo en JSON    
          print_r(json_encode( array ($resultado)));
        
      }
    }

?>