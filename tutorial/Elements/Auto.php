<?php

use \SimplOn\Elements\Element, \SimplOn\Datas;

/**
 * Tutorial ejercicio 2 Auto y Llantas
 */               

class Auto extends Element {

    public function construct($id = null, &$specialDataStorage = null) {
        $this->id_auto = new Datas\NumericId('Id');
        $this->test = new Datas\ElementContainer(new Llantas(),'Lantas','LS',null,'llanta');
    }   
}

