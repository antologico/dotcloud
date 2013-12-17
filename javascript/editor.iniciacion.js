/*
	----------------------------------------------------		 
	Proyecto : 	.cloud
	Autor :		Antonio Juan Sánchez Martín
	Fecha : 	5 / 11 /2012	

	Carga de componentes del editor
	----------------------------------------------------
*/		

	var miControlador = new EntornoTrabajo ();		
	miControlador.iniciar();

	// Vista suave del body
	// ------------------------------------------------------------------
	$("body").hide();
	$("body").fadeIn(1000);

