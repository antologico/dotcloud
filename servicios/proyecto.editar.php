<?php
/*
	----------------------------------------------------		 
	Proyecto : 	.cloud
	Autor :		Antonio Juan Sánchez Martín
	Fecha : 	5 / 11 /2012	

	Administración de proyectos: altas bajas y modificaciones

	Entradas:
		Valroes post [POST]
		accion: 		                    [ crear ] / [ modificar ] / [ borrar ] 
		idproyecto_administrar: 			para modificar y borrar
		nombre:			                    para modifcar y crear
		idcompilador:	                    para modifcar y crear

	Salida:
		-1 en caso de error
	----------------------------------------------------
*/	

	include_once ('../sesion.php'); 			// Define el objeto $conexion y el objeto $sesion
    include_once ('servidor.retrasar.php');
   	include_once ('../clases/Proyecto.php');	

    // Se comprueba corrección de la sesion, tipo y permisos de usuario
    if (($sesion->correcta ()) && isset($_POST['accion']))
    {
    	$id = $sesion->idproyecto();
    	if (isset($_POST['idproyecto_administrar']) != null) $id = $_POST['idproyecto_administrar'];    	
    	
        $proyecto = new Proyecto ($sesion, $id);
        $compilador = new Compilador ($sesion);
        $propiedades_compilacion = array ();

        foreach ($_POST as $key => $value) 
        {
            if (($key != "proyecto_nombre") && ($key != "proyecto_idcompilador") && ($key != "accion"))
            {
                $propiedades_compilacion[$key] = $value; 
            }
        }

        $nombre = $_POST['proyecto_nombre'];
        $idcompilador = $_POST['proyecto_idcompilador'];

    	switch ($_POST['accion'])
    	{
    		case 'crear':
    			echo $proyecto->crear ($nombre, $idcompilador);
    			break;

    		case 'modificar':
    			if ($proyecto->modificar ($nombre, $idcompilador, $id) != -1)
                    echo $compilador->propiedades ($id, $propiedades_compilacion);
                else echo "-2";
    			break;

    		case 'borrar':
    			echo $proyecto->borrar ($id);

		} 
    }

?>