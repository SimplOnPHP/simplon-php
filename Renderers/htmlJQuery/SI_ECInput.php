<?php



use voku\helper\HtmlDomParser;

/**
 * Los Items de interfaz SI_Item (Simplon Interface Item) son objetos que representan elementos de la interfaz de usuario.
 * Estos elementos deben ser redefinidos para cada Renderer ya que dependen de este.
 */
class SI_ECInput extends SI_Input {

    function __construct( $element, $value='', $required=False, $id = False) {
        $this->element = $element;
        $this->value = $value;
        $this->required = $required;
        $this->placeHolder  = '';
        $this->id = $id;
    }

    function setTagsVals($renderVals = null) {
        $required = $renderVals['required'] ? "required" : "";
        $id = $renderVals['id'] ? "id='".$renderVals['id']."'" : "";


        $action = SC_Main::$RENDERER->encodeURL($this->element->getClass(), [$renderVals['element']->element()->getClass()],'showElementCreate');

        $this->start =  new SI_Modal($action, $renderVals['element']::$CreateMsg,'addIcon.webp');
       // $this->end = $action;	
    }
}

