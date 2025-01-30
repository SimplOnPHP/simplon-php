<?php



use voku\helper\HtmlDomParser;

/**
 * Los Items de interfaz SI_Item (Simplon Interface Item) son objetos que representan elementos de la interfaz de usuario.
 * Estos elementos deben ser redefinidos para cada Renderer ya que dependen de este.
 */
class SI_Input extends SI_Item {

    function __construct( $name, $value='', $type=null, $placeHolder=False, $required=False, $id = False) {
        $this->value = $value;
        $this->type = $type;
        $this->placeHolder = $placeHolder;
        $this->required = $required;
        $this->id = $id;
        $this->name = $name;
    }

    function setTagsVals($renderVals = null) {
        $required = $renderVals['required'] ? "required" : "";
        $id = $renderVals['id'] ? "id='".$renderVals['id']."'" : "";
        $type = $renderVals['type'] ? "type='".$renderVals['type']."'" : "";
        $placeHolder = $renderVals['placeHolder'] ? "placeholder='".$renderVals['placeHolder']."'" : "";
        $name = $renderVals['name'] ? 'name="'.$renderVals['name'].'"' : "";

        $this->start = "<input $id $type $name value='".$renderVals['value']."' $placeHolder $required />";
    }
}

