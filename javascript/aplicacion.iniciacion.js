/*
	----------------------------------------------------		 
	Proyecto : 	.cloud
	Autor :		Antonio Juan Sánchez Martín
	Fecha : 	5 / 11 /2012	

	Control de acceso y GUI de la página de inicio
	----------------------------------------------------
*/		

// Pasar la contraseña a SHA2

function SHA256(s) {
 
	var chrsz   = 8;
	var hexcase = 0;
 
 	// Operaciones bit a bit
 	// ------------------------
	function eliminaDesbordamiento (x, y) 
	{
		var lsw = (x & 0xFFFF) + (y & 0xFFFF);
		var msw = (x >> 16) + (y >> 16) + (lsw >> 16);
		return (msw << 16) | (lsw & 0xFFFF);
	}
	function S (X, n) { return ( X >>> n ) | (X << (32 - n)); }
	function R (X, n) { return ( X >>> n ); }
	function Ch(x, y, z) { return ((x & y) ^ ((~x) & z)); }
	function Maj(x, y, z) { return ((x & y) ^ (x & z) ^ (y & z)); }


	function Sigma0256(x) { return (S(x, 2) ^ S(x, 13) ^ S(x, 22)); }
	function Sigma1256(x) { return (S(x, 6) ^ S(x, 11) ^ S(x, 25)); }
	function Gamma0256(x) { return (S(x, 7) ^ S(x, 18) ^ R(x, 3)); }
	function Gamma1256(x) { return (S(x, 17) ^ S(x, 19) ^ R(x, 10)); }
 
	function iteracion (m, l) {
		var K = new Array(0x428A2F98, 0x71374491, 0xB5C0FBCF, 0xE9B5DBA5, 0x3956C25B, 0x59F111F1, 0x923F82A4, 0xAB1C5ED5, 0xD807AA98, 0x12835B01, 0x243185BE, 0x550C7DC3, 0x72BE5D74, 0x80DEB1FE, 0x9BDC06A7, 0xC19BF174, 0xE49B69C1, 0xEFBE4786, 0xFC19DC6, 0x240CA1CC, 0x2DE92C6F, 0x4A7484AA, 0x5CB0A9DC, 0x76F988DA, 0x983E5152, 0xA831C66D, 0xB00327C8, 0xBF597FC7, 0xC6E00BF3, 0xD5A79147, 0x6CA6351, 0x14292967, 0x27B70A85, 0x2E1B2138, 0x4D2C6DFC, 0x53380D13, 0x650A7354, 0x766A0ABB, 0x81C2C92E, 0x92722C85, 0xA2BFE8A1, 0xA81A664B, 0xC24B8B70, 0xC76C51A3, 0xD192E819, 0xD6990624, 0xF40E3585, 0x106AA070, 0x19A4C116, 0x1E376C08, 0x2748774C, 0x34B0BCB5, 0x391C0CB3, 0x4ED8AA4A, 0x5B9CCA4F, 0x682E6FF3, 0x748F82EE, 0x78A5636F, 0x84C87814, 0x8CC70208, 0x90BEFFFA, 0xA4506CEB, 0xBEF9A3F7, 0xC67178F2);
		var HASH = new Array(0x6A09E667, 0xBB67AE85, 0x3C6EF372, 0xA54FF53A, 0x510E527F, 0x9B05688C, 0x1F83D9AB, 0x5BE0CD19);
		var W = new Array(64);
		var a, b, c, d, e, f, g, h, i, j;
		var T1, T2;
 
		m[l >> 5] |= 0x80 << (24 - l % 32);
		m[((l + 64 >> 9) << 4) + 15] = l;
 
		for ( var i = 0; i<m.length; i+=16 ) {
			a = HASH[0];
			b = HASH[1];
			c = HASH[2];
			d = HASH[3];
			e = HASH[4];
			f = HASH[5];
			g = HASH[6];
			h = HASH[7];
 
			for ( var j = 0; j<64; j++) {
				if (j < 16) W[j] = m[j + i];
				else W[j] = eliminaDesbordamiento(eliminaDesbordamiento(eliminaDesbordamiento(Gamma1256(W[j - 2]), W[j - 7]), Gamma0256(W[j - 15])), W[j - 16]);
 
				T1 = eliminaDesbordamiento(eliminaDesbordamiento(eliminaDesbordamiento(eliminaDesbordamiento(h, Sigma1256(e)), Ch(e, f, g)), K[j]), W[j]);
				T2 = eliminaDesbordamiento(Sigma0256(a), Maj(a, b, c));
 
				h = g;
				g = f;
				f = e;
				e = eliminaDesbordamiento(d, T1);
				d = c;
				c = b;
				b = a;
				a = eliminaDesbordamiento(T1, T2);
			}
 
			HASH[0] = eliminaDesbordamiento(a, HASH[0]);
			HASH[1] = eliminaDesbordamiento(b, HASH[1]);
			HASH[2] = eliminaDesbordamiento(c, HASH[2]);
			HASH[3] = eliminaDesbordamiento(d, HASH[3]);
			HASH[4] = eliminaDesbordamiento(e, HASH[4]);
			HASH[5] = eliminaDesbordamiento(f, HASH[5]);
			HASH[6] = eliminaDesbordamiento(g, HASH[6]);
			HASH[7] = eliminaDesbordamiento(h, HASH[7]);
		}
		return HASH;
	}
 
	function stringAbinario (str) {
		var bin = Array();
		var mask = (1 << chrsz) - 1;
		for(var i = 0; i < str.length * chrsz; i += chrsz) {
			bin[i>>5] |= (str.charCodeAt(i / chrsz) & mask) << (24 - i%32);
		}
		return bin;
	}
 
	function codificacionUTF8(string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";
 
		for (var n = 0; n < string.length; n++) {
 
			var c = string.charCodeAt(n);
 
			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}
 
		}
 
		return utftext;
	}
 
	function bianrioAhexadecimal (binarray) {
		var hex_tab = hexcase ? "0123456789ABCDEF" : "0123456789abcdef";
		var str = "";
		for(var i = 0; i < binarray.length * 4; i++) {
			str += hex_tab.charAt((binarray[i>>2] >> ((3 - i%4)*8+4)) & 0xF) +
			hex_tab.charAt((binarray[i>>2] >> ((3 - i%4)*8  )) & 0xF);
		}
		return str;
	}

	s = codificacionUTF8(s);
	return bianrioAhexadecimal (iteracion(stringAbinario(s), s.length * chrsz));
 
}


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
		$.post("servicios/sesion.iniciar.php", 	{ 	email: 		$("#form_inicio_email").val(),
													password: 	SHA256($("#form_inicio_password").val()) 
												}, 
												function(error)
												{
													cargador.desactivar ();

													if (error+0 != 0)
													{
														$("#capa_mensajes").slideDown(300);
														// Se muestra el error en el cajetin de "mensajes"
														$("#mensajes").text (_("Usuario o password incorrectos")+". Error: "+error);
													}
													else
													{
														$("body").fadeOut(1000, function () 
														{
															// Fade suave
														  	window.location = 'selector.php';
														});
													}
												}
		);
	});


	// Solicitar nueva cuenta
	$('#crear_cuenta').click (function() 
	{ 
			cargador.activar ();
			AttentionBox.showMessage( _("Solicitar una cuenta"),
								{
								    inputs : 
								    [
								    	{ caption : _("Nombre"), id: "nombre_archivo_nuevo" },
								    	{ caption : _("Apellidos"), id: "apellidos_usuario_nuevo" },
								    	{ caption : _("Email"), id: "email_usuario_nuevo" },
								    	{ caption : _("Password"), id: "password_usuario_nuevo", type: "password" },
							
								    ],
								    buttons : 
								    [
								        { caption : _("Cancelar"), cancel: true },
								        { caption : _("Solicitar") },
								    ],
									callback: function(action, inputs)
		    						{
		    							if (action == _("Solicitar"))
		    								if ((inputs[0].value != "") && (inputs[1].value != ""))
											{	
												$.post("servicios/usuario.solicitar.php", 
												{
													nombre: inputs[0].value,
													apellidos: inputs[1].value,
													email: inputs[2].value,
													password: inputs[3].value,
													accion: "solicitar"
												}, 
												function(valor)
												{
													cargador.desactivar ();
													if (parseInt(valor) != 0)
														AttentionBox.showMessage( _("No de puedo crear la cuenta de visitante")+". Error:"+valor);
													else
														AttentionBox.showMessage( _("Cuenta de visitante creada"));
												});
											}
										
										cargador.desactivar ();
										
									}
								});
	});

	// Texto de los botones
	$("#form_inicio_entrar").val(_("entrar")); 
	$("#form_inicio_password").val(""); 
	$("#crear_cuenta").val(_("crear una cuenta"));

	// Animación de capas
	$("#cargador").hide();
	$("#capa_mensajes").hide();
	$(".formulario").slideDown("slow");
	$("#logo_capa").hide (); 
	$("#logo_capa").show (1500);

	var autoref = controlador;
	$("#boton_idioma").click (function () { autoref.seleccionarIdioma() });
	$("#boton_idioma").attr ("ayuda", _("Cambiar de idioma"));

}

controlador.iniciar ();

