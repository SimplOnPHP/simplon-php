<?php



use voku\helper\HtmlDomParser;

/**
 * Los Items de interfaz SI_Item (Simplon Interface Item) son objetos que representan elementos de la interfaz de usuario.
 * Siempre deben recibir el Dato o elemento ($doe) del queu forman parte
 * Estos elementos deben ser redefinidos para cada Renderer ya que dependen de este.
 * 
 * En el Renderer htmlJQuery deben tener solo el atributo $doe que guarda a el Dato o Elemento del que forma parte el item de interfaz y atributos sencillos de valor de string para guardar los valores de lo que debe sustiturse en pantalla
 * 
 */
class SI_Form extends SI_Item {
    function __construct( array $content, $action, $method = 'POST', $enctype="")
    {
        $this->content = $content;
        $this->action = $action;
        $this->method = $method;
        $this->enctype = $enctype;
        $this->ajax = true;
        
        static::$jsfiles['00_jquery-1.7.2.min'] = './js/00_jquery-1.7.2.min.js';
        static::$jsfiles['05_jquery.form'] = './js/05_jquery.form.js';
        static::$jsfiles['05_jquery.validate.min'] = './js/05_jquery.validate.min.js';
        static::$jsfiles['10_SimplON'] = './js/10_SimplON.js';
    }

    function setTagsVals($renderVals = null) {
        $method = $renderVals['method'] ?? 'post';
        $action = $renderVals['action'] ?? '';
        $enctype = $renderVals['enctype'] ?? 'multipart/form-data';
        $id = $renderVals['id'] ?? '';
        if($this->ajax){$renderVals['class'] .= ' Ajax';}
        $class = $renderVals['class'] ? 'class="'.$renderVals['class'].'"' : '';
        
        $this->start = "<form method=\"$method\" action=\"$action\" enctype=\"$enctype\" id=\"$id\" $class>";
        $this->end = "</form>\n";
    }



}
