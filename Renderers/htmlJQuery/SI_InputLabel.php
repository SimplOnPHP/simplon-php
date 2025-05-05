<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

/**
 * Los Items de interfaz SI_Item (Simplon Interface Item) son objetos que representan elementos de la interfaz de usuario.
 * Siempre deben recibir el Dato o elemento ($doe) del queu forman parte
 * Estos elementos deben ser redefinidos para cada Renderer ya que dependen de este.
 * 
 * En el Renderer htmlJQuery deben tener solo el atributo $doe que guarda a el Dato o Elemento del que forma parte el item de interfaz y atributos sencillos de valor de string para guardar los valores de lo que debe sustiturse en pantalla
 * 
 */
class SI_InputLabel extends SI_Item {
    protected
        $required = '';

    function __construct($label='', $id='', $required=False ) {
        $this->id = $id;
        $this->label = $label;
        $this->required = $required;
        
    }
     
    function setTagsVals($renderVals = null){
        if($this->required) {
            $required = "*";
            $this->content = $renderVals['label']." *:";
        }else{
            $this->content = $renderVals['label']." :";
        }
        $this->start = '<label for="'.$renderVals['id'].'">';
        $this->end = "</label>\n";
    }
}