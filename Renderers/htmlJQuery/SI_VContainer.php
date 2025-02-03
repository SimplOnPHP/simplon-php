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
class SI_VContainer extends SI_Item {

    function __construct( $content = null, $aligmment = null )
    {
        $this->aligmment = $aligmment;  
        $this->content = $content;
        $this->addClass('SI_VContainer');
    }

    function itemStart($class = ''){
        if($class) $class = "class='$class'";
        return "<div $class>";
    }

    function setTagsVals($renderVals = null){
        $this->start = "<div {$this->attributesString()}>";
        $this->end = "</div>\n";
        $this->itemEnd = "</div>\n";
    }

    function html() {
        $renderVals = $this->getRenderVals();
        $aligmment = explode(' ',$renderVals['aligmment']);

        $innerHTML = '';
        if(is_array($this->content)){
            foreach($this->content as $item){
                if(is_array($aligmment)){
                    $class = array_shift($aligmment);
                }else{
                    $class = '';
                }
                $innerHTML .= $this->itemStart($class).$item.$this->itemEnd;
            }
        }else{
            $innerHTML = $renderVals['content'];
        }
        return $this->start.$innerHTML.$this->end;
    }
}
