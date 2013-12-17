/*
	----------------------------------------------------		 
	Proyecto : 	.cloud
	Autor :		Antonio Juan Sánchez Martín
	Fecha : 	5 / 11 /2012	

	Script de asoción de subida de archivos
	
	----------------------------------------------------
*/

 (function ($) {
    $.fn.html5Uploader = function (destino, manejador) {
        
        return this.each(function () {
           		var $this = $(this);
                $this.bind("dragenter dragover", function () {
                    $(this).addClass("over");
                    return false;
                }).bind("drop", function (e) {

                    var archivos = e.originalEvent.dataTransfer.files;
                    for (var i = 0; i < archivos.length; i++) 
                    {
                        manejador(destino, archivos[i]);
                    }

                    $(this).removeClass("over");
                    
                    return false;
                }).bind("dragleave", function () {
                	// -------------------------
                    $(this).removeClass("over");
                    return false;
                    // -------------------------
                });

        });
    };
})(jQuery);