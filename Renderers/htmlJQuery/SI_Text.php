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
class SI_Text extends SI_Item {

    function __construct($content)
    {
        $this->content = $content;
    }

    function setTagsVals($renderVals = null){

    }
}