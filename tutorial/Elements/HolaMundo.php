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
    }   
    
    function saluda(){
       echo "Hola Mundo";
    }

}

