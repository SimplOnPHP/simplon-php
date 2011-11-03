<?php

use \DOF\Elements\Element, \DOF\Datas;

/**
 * Tutorial ejercicio 1.1 HolaMundo
 * 
 * 
 */

class HolaMundo extends Element {
    
    public function construct($id = null, &$specialDataStorage = null) {
        $this->id = new Datas\Id('Id');
        $this->nombre = new Datas\String('Nombre','u','yo');
        $this->frase = new Datas\String('Frase','c');
    }   
    
    function saluda(){
       echo "Hola Mundo, ".$this->nombre().' '.$this->frase();
    }

}

