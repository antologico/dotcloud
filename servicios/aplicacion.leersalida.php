<pre><?php
/*
	----------------------------------------------------		 
	Proyecto : 	.cloud
	Autor :		Antonio Juan Sánchez Martín
	Fecha : 	5 / 11 /2012	

	Inicio de sesión de usuario
	
	Servicio:
		Entrada [POST]:
			- id: idenficiador del proceso
		Salida:
			- salida del proceso		
	----------------------------------------------------
*/
    include_once ('servidor.retrasar.php');
	
		$path = '../so/pipes/fifo';

		$fo = fopen($path, 'r+'); 

		$waitIfLocked = true;

        $locked = flock($fo, LOCK_SH, $waitIfLocked); 
        
        if($locked) 
        {  

        	// Leemos el conteido
           	echo file_get_contents($path); 

            ftruncate($fo, 0);
            // Desbloqueamos el archivo
            flock($fo, LOCK_UN); 

        }

		// Cerramos el archivo
        fclose($fo); 
?></pre>