<?php

/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

/**
 * Los Items de interfaz SI_Item (Simplon Interface Item) son objetos que representan elementos de la interfaz de usuario.
 * Estos elementos deben ser redefinidos para cada Renderer ya que dependen de este.
 */
class SI_ECsInput extends SI_Input {

    protected $elementConteiner;


    function __construct( $elementConteiner, $value='', $required=False, $id = False) {
        $this->elementConteiner = $elementConteiner;
        $this->element = $elementConteiner->element();
        $this->value = $value;
        $this->required = $required;
        $this->placeHolder  = '';
        $this->id = $id;
    }

    function setTagsVals($renderVals = null) {
        $required = $renderVals['required'] ? "required" : "";
        $id = $renderVals['id'] ? "id='".$renderVals['id']."'" : "";
        $tagId=substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);

        if($this->elementConteiner->allowCreateButton()){
            $createAction = SC_Main::$RENDERER->encodeURL($this->elementConteiner->getClass(), [$renderVals['element']->getClass()],'showElementCreate',[$tagId,$renderVals['elementConteiner']->name()]);
            $create = new SI_Modal($createAction, $renderVals['elementConteiner']->CreateMsg(),'addIcon.svg');
        }else{
            $create = '';
        }

        if($this->elementConteiner->checkPermissions($this->elementConteiner->element(),'showList')){
            $selectAction = SC_Main::$RENDERER->encodeURL($this->elementConteiner->getClass(), [$renderVals['element']->getClass()],'showElementSelect',[$tagId,$renderVals['elementConteiner']->name()]);
            $select = new SI_Modal($selectAction, $renderVals['elementConteiner']->selectMsg(),'selectIcon.svg');
        }else{
            $select = '';
        }

        if($this->value) {
            $renderVals['element']->setId($this->value);
            $remove = new SI_AjaxLink('#', 'Deselect', 'removeIcon.svg');
            $remove->addAttribute('onclick',"$('#$tagId').find('.value').val('');$('#$tagId > :nth-child(3)').html('');return false;");
            $view = $renderVals['element']->showEmbeded().$remove;
            //$view = $this->value;
        }else{ 
            //$view = SC_Main::L('Create or select a '.$this->elementConteiner->element()->name());
            $view = $this->value;
        }

        $this->start = new SI_HContainer([$create,$select,@$view],null,'1rem 1rem auto');
        $this->start->addAttribute('id',$tagId);
        $this->end = '';	
       // $this->end = $action;	
    }
}

