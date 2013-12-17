/*
	----------------------------------------------------		 
	Proyecto : 	.cloud
	Autor :		Antonio Juan Sánchez Martín
	Fecha : 	5 / 11 /2012	

	Control de acceso a proyectos
	----------------------------------------------------
*/

	// Vista suave del body
	// ------------------------------------------------------------------
	$("body").hide();
	$("body").fadeIn(1000);
	
	var selector = new Selector ();
	
	selector.iniciar ();