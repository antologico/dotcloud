<?php
/*
    ----------------------------------------------------         
    Proyecto :  .cloud
    Autor :     Antonio Juan Sánchez Martín
    Fecha :     5 / 11 /2012    

    Listado de archivos
    ----------------------------------------------------
*/  
    
    include_once ('sesion.php');
    include_once ('clases/Pagina.php');
    include_once ('clases/Proyecto.php');
    
    $acceso_proyecto = true;

    // Si la sesion no es correcta, se carga el index
    if (!$sesion->correcta ()) $acceso_proyecto = false;
    else
    {
        if (isset($_GET['proyecto'])) $sesion->asignaProyecto ($_GET['proyecto']);
        
        // Se comprueban los permisos de usuario en el proyectos
        if ($sesion->idproyecto() == null) 
            $acceso_proyecto = false;
    }

    if  ($acceso_proyecto)
    {
        // Si va todo correcto asignamos el proyecto
        

        // Recursos
        // ---------------------------------------------------------------------------------------------------------------------
        $proyecto       = new Proyecto ($sesion);
        $compilador     = new Compilador ($sesion);
        
        $pagina = new Pagina (
                array ( 
                    "css/aplicacion.general.css" ,
                    "css/editor.general.css",
                    "css/editor.explorador_archivos.css",
                    "css/editor.barra_herramientas.css",
                    "css/editor.ventana_ejecucion.css",
                    "css/editor.ventana_usuarios.css",
                    "css/jquery.attention.box.css",                     // JQUERY
                    "css/jquery.jscrollpane.css",                       // JQUERY - Scroll pane
                    "css/jquery.ui.css",
                    "css/jquery.filetree.css"                           // Árboles de archivos
                    ),
                array (
                    "javascript/jquery.min.js",                         // JQUERY
                    "javascript/jquery.jscrollpane.min.js",             // JQUERY - Scroll pane
                    "javascript/jquery.ui.js",
                    "javascript/jquery.attention.box.js",               // JQUERY - Alertas
                    "javascript/jquery.mousewheel.js",                  // JQUERY - Rueda sobre scroll
                    "javascript/jquery.mwheelIntent.js",
                    "javascript/jquery.contextmenu.js",                 // JQUERY - Menus contextuales
                    "javascript/jquery.html5uploader.js",               // JQUERY - Subida de archivos
                    "javascript/editor.internacionalizacion.diccionario.js",     // INTERNACIONALIZACIÓN
                    "javascript/editor.internacionalizacion.js",
                    "javascript/jquery.FileTree.js",                     // Árboles de archivos
                    "javascript/ace/src-noconflict/ace.js",             // ACE editor de archivos
                    "javascript/clases/VentanaEjecucion.js",            // Clases propies de la aplicacion
                    "javascript/clases/ExploradorArchivos.js",
                    "javascript/clases/Editor.js",
                    "javascript/clases/BarraHerramientas.js",
                    "javascript/clases/Cargador.js",
                    "javascript/clases/VentanaUsuarios.js",
                    "javascript/clases/Controlador.js",
                    "javascript/clases/EntornoTrabajo.js"
                    
                    ),
                array (
                    "javascript/editor.iniciacion.js"                   // JavaScript propio de la página
                    ),
                array (
                    "title"       => ".cloud : ".$proyecto->nombre()." : ".$compilador->nombre (),
                    "description" => "API de desarrollo multilenguaje para sistemas Cloud",
                    "author"      => "Antonio Juan Sánchez Martín",
                    "keywords"    => "Desarrollo, Compilación On-line, Cloud Compiling"
                    )
            );
            $pagina->cargarPlugins ($sesion, 'plugins/', 'instalacion/plugins/');
        }
    else
    {     
        // Si la sesion no es correcta, se carga el index
        include_once ('index.php');
    }

?>