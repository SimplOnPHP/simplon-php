<?php

use \SimplOn\Elements\Element, \SimplOn\Datas;

class Llantas extends Element {
   
    public function construct($id = null, &$specialDataStorage = null) {
        $this->id = new Datas\NumericId('Id');
        $this->llanta = new Datas\String('tipo llanta','US');
    }
}