<?php
/*
    ----------------------------------------------------         
    Proyecto :  .cloud
    Autor :     Antonio Juan Sánchez Martín
    Fecha :     5 / 11 /2012    

    Datos de la Sesion
    ----------------------------------------------------
*/

    function incluirLinea ($archivo, $linea)
    {
              $fh = fopen($archivo, 'w+');
              fwrite($fh, $linea);
              fclose($fh);
    }

    # alta en archivos del sistema
    # PASSWD usuario:x:2001:2001:Usuario Genérico:/home/usuario/www:/bin/bash - LA x indica que el password va en SHADOWS 
    # creamos un grupo para cada usuario
    $usuario = 'momo';
    $uid   = 2000+1; 
    $gid   = $uid;
    $password = crypt ($usuario.$token);

    // incluirLinea ("/etc/passwd", $usuario.":x:".$uid.":".$gid.":".$usuario.":/:/bin/bash");
    //incluirLinea ("/etc/group", $usuario.":x:".$gid.":");
    // incluirLinea ("/etc/shadow", $usuario.":".$usuario."::0:::::0");
    incluirLinea ("kk.kk", $usuario.":".$password."::0:::::0");

    echo exec ("cat kk.kk");
    echo "\n";
?>