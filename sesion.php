<?php
/*
    ----------------------------------------------------         
    Proyecto :  .cloud
    Autor :     Antonio Juan Sánchez Martín
    Fecha :     5 / 11 /2012    

    Datos de la Sesion
    ----------------------------------------------------
*/
    session_start ();

    include_once ('clases/Sesion.php');
    include_once ('configuracion.php');

    // Datos de la conexion MySQL
    $conexion  = new ConexionMySQL (	
                    $conexion_servidor, 
   									$conexion_usuario, 
   									$conexion_password, 
   									$conexion_basedatos
   									);

    $sesion = new Sesion ($conexion, $directorio_proyectos);

?>