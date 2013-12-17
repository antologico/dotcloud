/*
	----------------------------------------------------		 
	Proyecto : 	.cloud
	Autor :		Antonio Juan Sánchez Martín
	Fecha : 	5 / 11 /2012	

	Control de acceso y GUI de la página de inicio
	----------------------------------------------------
*/		

// Pasar la contraseña a SHA2

var controlador = new Controlador ();
controlador.cargador = null;

controlador.mostrar  = function (idioma)
{
	cargador = new Cargador ("cargador_selector_general");

	// Control del envío del formulario
	$('#form_inicio_entrar').click (function() 
	{ 
		cargador.activar ();
		$("#capa_mensajes").hide();
		$.post("servicios/aplicacion.instalar.php", 	
											{ 	bd_nombre : $("#form_inicio_bd_nombre").val(), 
												bd_host : $("#form_inicio_bd_host").val(),
												bd_user : $("#form_inicio_bd_user").val(),  
												bd_password : $("#form_inicio_bd_password").val(),  
												dir_inst : $("#form_inicio_dir_inst").val(), 
												dir_proy : $("#form_inicio_dir_proy").val(),
												mail_host : $("#form_inicio_mail_host").val(), 
												mail_port : $("#form_inicio_mail_port").val(), 
												mail_user : $("#form_inicio_mail_user").val(), 
												mail_password: $("#form_inicio_mail_password").val(),  
												
												}, 
												function(error)
												{
													cargador.desactivar ();

													if (error+0 != 0)
													{
														$("#capa_mensajes").slideDown(300);
														// Se muestra el error en el cajetin de "mensajes"
														$("#mensajes").text (_(error));
													}
													else
													{
														$("body").fadeOut(1000, function () 
														{
															// Fade suave
														  	window.location = 'index.php';
														});
													}
												}
		);
	});

	// Texto de los botones
	$("#form_inicio_entrar").val(_("instalar")); 
	$("#form_inicio_dir_proy").val("/"+_("proyectos")); 
	$("#form_inicio_etiqueta_bd_nombre").text(_("Base de datos: nombre")); 
	$("#form_inicio_etiqueta_bd_host").text(_("Base de datos : Host")); 
	$("#form_inicio_etiqueta_bd_user").text(_("Base de datos : Usuario")); 
	$("#form_inicio_etiqueta_bd_password").text(_("Base de datos : Password")); 
	$("#form_inicio_etiqueta_dir_inst").text(_("Directorio de instalación")); 
	$("#form_inicio_etiqueta_dir_proy").text(_("Directorio de proyectos")); 
	$("#form_inicio_etiqueta_mail_host").text(_("Email: Host")); 
	$("#form_inicio_etiqueta_mail_port").text(_("Email: Puerto")); 
	$("#form_inicio_etiqueta_mail_user").text(_("Email: Usuario")); 
	$("#form_inicio_etiqueta_mail_password").text(_("Email: Password")); 

	// Animación de capas
	$("#cargador").hide();
	$("#capa_mensajes").hide();
	$(".formulario").slideDown("slow");


	var autoref = controlador;
	$("#boton_idioma").click (function () { autoref.seleccionarIdioma() });
	$("#boton_idioma").attr ("ayuda", _("Cambiar de idioma"));

}

controlador.iniciar ();

