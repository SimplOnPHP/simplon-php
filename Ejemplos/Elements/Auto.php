<?php

use \SimplOn\Elements\Element, \SimplOn\Datas;

/**
 * Class Auto
 * 
 */            

class Auto extends Element {
       public function construct($id = null, &$specialDataStorage = null) {
        $this->id = new Datas\NumericId('Id');
        $this->test = new Datas\ElementsContainer(array(new Llantas()));
        
    }   
    
    function saluda(){
       //echo "Hola Mundo, ".$this->nombre().' '.$this->frase()." este es mi correo: ".$this->pegar().' enlace: '.$this->enlace();
    }
}
