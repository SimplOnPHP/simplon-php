<?php

use \SimplOn\Elements\Element, \SimplOn\Datas;

/**
 * Tutorial ejercicio 1.1 HolaMundo
 * 
 * echo "Abriendo Hola Mundo";
 */

class HolaMundo extends Element {

    public function construct($id = null, &$specialDataStorage = null) {
        $this->id = new Datas\NumericId('Id');
        $this->nombre = new Datas\String('Nombre','CUR','Tu nombre');
        $this->frase = new Datas\String('Frase','CUR','Tu frase');
    }   
    
    function saluda(){
       echo 'Hola Mundo, mi nombre es'.$this->nombre().' y mi frase es '.$this->frase();
    }
}

