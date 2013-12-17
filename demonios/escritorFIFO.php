<?php
/*
    ----------------------------------------------------         
    Proyecto :  .cloud
    Autor :     Antonio Juan Sánchez Martín
    Fecha :     5 / 11 /2012    

    Demonio de escritura en la entrada de los programas
    ----------------------------------------------------
*/  

    error_reporting(E_ALL);

    // Permitir al script esperar para conexiones
    set_time_limit(0);

    // Activar el volcado de salida implícito, 
    //así veremos lo que estamo obteniendo
    // mientras llega.
    ob_implicit_flush();

    $address = '127.0.0.1';
    $port = 11000+intval($_SERVER['argv'][1]);  // numero de proeycto en argv
    echo "";

    $sock = null;

    if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) !== false) 
    {
        if (socket_bind($sock, $address, $port) !== false) 
        {
            if (socket_listen($sock, 5) !== false) 
            {
                do 
                {
                    if (($msgsock = socket_accept($sock)) !== false)
                    {
                        // El primer mensaje es el estado
                        if (false === ($buf = socket_read($msgsock, 2048, PHP_BINARY_READ))) break;
                        // Como habíamos enviado un \n adicional para forzar la escritura
                        // se lo quitamos ahora
                        // echo substr($buf, 0 , -1);
                        echo $buf;
                    } 
                    else break;
                }
                while (true);

            }
        }
    } 

    socket_close($sock);

?>