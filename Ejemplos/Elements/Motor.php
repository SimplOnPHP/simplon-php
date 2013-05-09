<?php

use \SimplOn\Elements\Element, \SimplOn\Datas;

            

class Motor extends Element {
   
    public function construct($id = null, &$specialDataStorage = null) {
        $this->id = new Datas\NumericId('Id');
        $this->motor = new Datas\String('Motor','');
    }
    function vista(){
	    echo $this->showCreate();
	}
}