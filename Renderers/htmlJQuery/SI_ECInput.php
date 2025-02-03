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


        $createAction = SC_Main::$RENDERER->encodeURL($this->element->getClass(), [$renderVals['element']->element()->getClass()],'showElementCreate');
        $create = new SI_Modal($createAction, $renderVals['element']::$CreateMsg,'addIcon.webp');

        $selectAction = SC_Main::$RENDERER->encodeURL($this->element->getClass(), [$renderVals['element']->element()->getClass()],'showElementSelect');
        $select = new SI_Modal($selectAction, $renderVals['element']::$CreateMsg,'selectIcon.webp');
        
        if($this->element->element()->id()) {$view = $this->element->element()->showEmebeded();}
        else{ $view = SC_Main::L('Create or select a '.$this->element->element()->name());}
        $input = new SI_Input($this->element->name(),$this->element->val(),'hidden');




        $this->start =  new SI_HContainer([$create,$select,$input.$view],null,'1rem 1rem auto');
       // $this->end = $action;	
    }
}

