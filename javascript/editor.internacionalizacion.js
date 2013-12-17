/*
		 
			Proyecto : 	.cloud
			Autor :		Antonio Juan Sánchez Martín
			Fecha : 	5 / 12 /2012	

			Internacionalizacion siguiendo el modelo i18n
*/


// Funicón para acceso rápido a las palablas del diccionario

function _(s) 
{
		// Si está definido el tipo en el idioma se devuelve la cadena
		if (typeof(idioma)!='undefined')
			if (typeof(i18n)!='undefined' && i18n[idioma])
				if (typeof(i18n[idioma])!='undefined' && i18n[idioma][s]) 
					return i18n[idioma][s];

 	// Sino se devuelve la cadena origen
 	return s;
}