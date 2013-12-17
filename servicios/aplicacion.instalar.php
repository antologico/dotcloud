<?php
/*
	----------------------------------------------------		 
	Proyecto : 	.cloud
	Autor :		Antonio Juan Sánchez Martín
	Fecha : 	5 / 11 /2012	

	Crear el archivo de configuracion: configuracion.php
	
	Servicio:
		- Devuelve 0 si todo ha ido correcto, en caso contrario el error	
	----------------------------------------------------
*/
		function ejecutarArchivoSQL($archivo, $conexion)
		{
			  $queries = explode(';', file_get_contents($archivo));
			  foreach($queries as $query)
			  {
				    if($query != '')
				    {
				      $conexion->query($query); // Asumo un objeto conexión que ejecuta consultas
				    }
			  }
		}



    	if (isset($_POST["bd_nombre"]) && 
			isset($_POST["bd_host"]) && 
			isset($_POST["bd_user"]) && 
			isset($_POST["bd_password"]) && 
			isset($_POST["dir_inst"]) && 
			isset($_POST["dir_proy"]) && 
			isset($_POST["mail_host"]) && 
			isset($_POST["mail_port"]) && 
			isset($_POST["mail_user"]) && 
			isset($_POST["mail_password"]) )
    	{
    		$conexionprueba = new mysqli ($_POST["bd_host"], $_POST["bd_user"], $_POST["bd_password"], $_POST["bd_nombre"]);

    		if ($conexionprueba->connect_errno)
    		{
    			echo "Los datos de la conexion MySQL son incorrectos ".$conexionprueba->connect_errno;
    		}
    		else 
    		{
    			if (file_exists($_POST["dir_inst"]))
    			{
    				if (file_exists($_POST["dir_proy"]))
	    			{
	    				// El mail no se comprueba

	    				// Escribimos los datos en el archivo y volcamos la base de datos
						$miarchivo = "../configuracion.php";
						if (!file_exists($miarchivo))
						{

		    				// Volcamos la base de datos al servidor de BD
		    				ejecutarArchivoSQL ("../instalacion/dotcloud.sql", $conexionprueba);
		    				
		    				// Creamos el archivo de configuracion

		    				$contenido = '<?php
/*
    ----------------------------------------------------         
    Proyecto :  .cloud
    Autor :     Antonio Juan Sánchez Martín
    Fecha :     5 / 11 /2012    

    Datos de la conexion MySQL con la base de datos y del sistema de archivos

    ----------------------------------------------------
*/

    // Datos de la conexion MySQL
    $conexion_servidor      =  "'.$_POST["bd_host"].'";
   	$conexion_usuario       =  "'.$_POST["bd_user"].'";
   	$conexion_password      =  "'.$_POST["bd_password"].'";
   	$conexion_basedatos     =  "'.$_POST["bd_nombre"].'";

    // Datos de los directorios
    $directorio_instalacion =  "'.$_POST["dir_inst"].'";
    $directorio_proyectos   =  "'.$_POST["dir_proy"].'";

    // Correo electrónico
    $mail_host      = "'.$_POST["mail_host"].'";
    $mail_puerto    = '.$_POST["mail_port"].';
    $mail_usuario   = "'.$_POST["mail_user"].'";
    $mail_password  = "'.$_POST["mail_password"].'";

?>';
						
							$fh = fopen($miarchivo, 'w+') or die ("Error creando archivo");
							fwrite($fh, $contenido);
							fclose($fh);
							echo 0;
						}
						else echo "El archivo ya de configuración existía";
	    				
	    			}
	    			else echo "El directorio de proyectos no es correcto";
    			}
    			else echo "El directorio de instalación no es correcto";
    		}
      	}
    	else echo "Debe completar todos los parámetros"
?>