<?php
/*
	----------------------------------------------------		 
	Proyecto : 	.cloud
	Autor :		Antonio Juan Sánchez Martín
	Fecha : 	5 / 11 /2012	

	Listado de archivos
	
	Entrada:
		Entrada [POST]:
		- patron: 	Patrón de busqueda 	> patron > Puede ser nulo > patron
		- dir:		Subdirectorio 		> Direcotrio del o desde el cual hacer el listado > 
		- html:		(opcional) Si está activo se produce una saldia formateada en HTML
	Salida:
		- Listado de archivo en JSON o en HTML (si se ha indicado)
	----------------------------------------------------
*/	

    include_once ('../sesion.php');
    include_once ('../clases/Directorio.php');
    include_once ('servidor.retrasar.php');
    
	// -------------------------------------------------
	// HTML del arbol de directorios
	// -------------------------------------------------	

	function imprimirHTML ($arbol)
	{
		echo "<ul class='jqueryFileTree'>"."\n";
		foreach ($arbol as $elemento => $campo)
		{
			//-----------------------------------------------
			if ($elemento == "directorios")
			{
				foreach ($campo as $directorio)
				{
					echo "<li class='".$directorio["class"]."'><a href='#' rel='".$directorio["rel"]."' >".$directorio["name"]."</a>"."\n";
					imprimirHTML ($directorio["directorios"]);
					echo "</li>"."\n";	
				}
			}
			//-----------------------------------------------
			if ($elemento == "archivos")
			{
				// Recorremos los archivos
				foreach ($campo as $archivo)
				{
					echo "<li class='".$archivo["class"]."'><a href='#' rel='".$archivo["rel"]."' >".$archivo["name"]."</a></li>"."\n";
				}
			}
		}
		echo "</ul>"."\n";	
	}

	// ----------------------------------------------------------------------- 
	// Si la sesion se correcta y se ha cargado un proyecto, se lista
	// -----------------------------------------------------------------------    

    if ($sesion->correcta ()) 
    if ($sesion->idproyecto () != null) 
    {

		$directorio 			= new Directorio($sesion->directorioProyecto());
		$directorio_general 	= '';
		$patron_busqueda 		= '';
		
		// -------------------------------------------------
		if (isset($_POST['dir']))
			if ($_POST['dir'] 			!= "")
				$directorio_general		= $_POST['dir'];
				
		// -------------------------------------------------
		if (isset($_GET['patron']))
			if ($_GET['patron'] 		!= "")
				$patron_busqueda		= $_GET['patron'];
		// -------------------------------------------------
		$arbol = $directorio->listar ($sesion, $directorio_general, $patron_busqueda); 

		if (isset($_GET['html']))
		{
			imprimirHTML ($arbol);
		}
		else
		{
			print_r (json_encode($arbol));
		}	
	}
?>
