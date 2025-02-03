<?php



use voku\helper\HtmlDomParser;

/**
 * Los Items de interfaz SI_Item (Simplon Interface Item) son objetos que representan elementos de la interfaz de usuario.
 * Estos elementos deben ser redefinidos para cada Renderer ya que dependen de este.
 */
class SI_Input extends SI_Item {

    function __construct( $name, $value='', $type=null, $placeHolder=False, $required=False, $id = False) {
        $this->required = $required;
        $this->addAttribute('value',$value);

        if($type)           {$this->addAttribute('type',$type);}
        if($placeHolder)    {$this->addAttribute('placeHolder',$placeHolder);}
        if($id)             {$this->addAttribute('id',$id);}
        if($name)           {$this->addAttribute('name',$name);}
    }

    function setTagsVals($renderVals = null) {
        $this->start = "<input {$this->attributesString()} />";
    }
}

