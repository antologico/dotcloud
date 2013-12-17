<?
/*
    ----------------------------------------------------         
    Proyecto :  .cloud
    Autor :     Antonio Juan Sánchez Martín
    Fecha :     5 / 11 /2012    

    Página de inicio - Formulario de entrada
    ----------------------------------------------------
*/

    if (!file_exists('configuracion.php'))
    {
        include_once ('instalador.php');
    }
    else
    {
        
    include_once ('sesion.php');
    include_once ('clases/Pagina.php');


    if (isset($_SESSION['email']))      $_POST['form_inicio_email'] = $_SESSION['email'];
    if (isset($_SESSION['password']))   $_POST['form_inicio_password'] = $_SESSION['password'];    

    // Recursos
    // ---------------------------------------------------------------------------------------------------------------------

    $pagina = new Pagina (
            array ( 
                    "css/aplicacion.general.css" ,
                    "css/aplicacion.index.css", 
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
                "javascript/aplicacion.iniciacion.js"               // JavaScript propio de la página
                ),
            array (
                "title"       => ".cloud - Cloud Compiling",
                "description" => "API de desarrollo multilenguaje para sistemas Cloud",
                "author"      => "Antonio Juan Sánchez Martín",
                "keywords"    => "Desarrollo, Compilación On-line, Cloud Compiling"
                )
        );


   

    // Estructura de la página
    // ---------------------------------------------------------------------------------------------------------------------

    $pagina->incluirElemento (new ElementoHTML ("div" , array ("class" => "index"), array (
                new ElementoHTML ("div" , array ("id" => "logo_capa"), array (
                        new ElementoHTML ("img", array ( "src" => "imagenes/logo_index.png", "id" => "logo", "alt" => ".cloud")))),

                new ElementoHTML ("div", array ("class" => "formulario dialogo"), array(

                        new ElementoHTML ("div", array ("id" => "cargador"), array(
                            new ElementoHTML ("img", array ( "src" => "imagenes/cargador.gif", "alt" => "loading"))
                            )),

                        new Formulario ("form_inicio", "form_inicio", "POST", null, array(
                                array ("email",     "INPUT",    "email"),
                                array ("password",  "PASSWORD", "password"),
                                array ("",          "BOTON",    "entrar", "")
                            )))
                        ),
                new ElementoHTML ("div", array ("id" => "nueva_cuenta"), array  (
                        new ElementoHTML ("input", array("id"=>"crear_cuenta", "type"=>"button")),
                        new ElementoHTML ("input", array("id"=>"boton_idioma", "type"=>"button", "value"=>"")))
                    )),
                new ElementoHTML ("div", array ("id" => "capa_mensajes"), array  (
                        new ElementoHTML ("img", array ( "src" => "imagenes/error.png", "id"=>"icono_error", "alt" => "error")),
                        new ElementoHTML ("p", array ("id" => "mensajes"))
                    ))
                

            ));
    }

?>
