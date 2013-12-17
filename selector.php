<?
/*
    ----------------------------------------------------         
    Proyecto :  .cloud
    Autor :     Antonio Juan Sánchez Martín
    Fecha :     5 / 11 /2012    

    Página de inicio - Formulario de entrada
    ----------------------------------------------------
*/  

    include_once ('sesion.php');
    include_once ('clases/Pagina.php');

    // Si la sesion no es correcta, se carga el index
    if ($sesion->correcta ()) 
    {

        // Recursos
        // ---------------------------------------------------------------------------------------------------------------------

        $pagina = new Pagina (
                array ( 
                    "css/aplicacion.general.css" ,
                    "css/selector.general.css",
                    "css/jquery.attention.box.css",                             // JQUERY
                    "css/jquery.jscrollpane.css",                               // JQUERY - Scroll pane
                    "css/jquery.ui.css"
                    ),
                array (
                    "javascript/jquery.min.js",                                 // JQUERY
                    "javascript/jquery.jscrollpane.min.js",                     // JQUERY - Scroll pane
                    "javascript/jquery.ui.js",
                    "javascript/jquery.mousewheel.js",                          // JQUERY - Rueda sobre scroll
                    "javascript/jquery.mwheelIntent.js",
                    "javascript/jquery.attention.box.js",
                    "javascript/editor.internacionalizacion.diccionario.js",     // INTERNACIONALIZACIÓN
                    "javascript/editor.internacionalizacion.js", 
                    "javascript/clases/Cargador.js",     
                    "javascript/clases/Controlador.js",     
                    "javascript/clases/Selector.js"         
                    ),
                array (
                    "javascript/selector.iniciacion.js"               // JavaScript propio de la página
                    ),
                array (
                    "title"       => ".cloud",
                    "description" => "API de desarrollo multilenguaje para sistemas Cloud",
                    "author"      => "Antonio Juan Sánchez Martín",
                    "keywords"    => "Desarrollo, Compilación On-line, Cloud Compiling"
                    )
            );

        // Estructura de la página
        // ---------------------------------------------------------------------------------------------------------------------
    }
    else
    { 
        // Si la sesion no es correcta, se carga el index
        include_once ('index.php');
    }
?>