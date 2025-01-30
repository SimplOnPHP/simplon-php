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
class SI_HContainer extends SI_Item {

    protected
        $width;

    function __construct( $content = null, $aligmment = null, $width = null )
    {
        $this->styles = '
        .SI_HContainer {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(5rem, 1fr)); /* Adjust 150px to your minimum column size */
            gap: 0.2rem; /* Adjust the gap between columns and rows as needed */
            padding: 0.2rem 0; /* Optional: Add some padding around the container */
            box-sizing: border-box; /* Ensure padding is included in the total size */
        }';
        $this->addStylesToAutoCSS();

        $this->aligmment = $aligmment;
        $this->width = $width;
        $this->content = $content;
    }

    function itemStart($class = ''){
        if($class) $class = " class='$class'";
        return "<div$class>";
    }

    function setTagsVals($renderVals = null){
        $width = $renderVals['width'];
        
        if($width){
        $this->start = "<div class='SI_HContainer' style='grid-template-columns: $width;'>";}
        else{
            $this->start = "<div class='SI_HContainer'>";
        }
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
