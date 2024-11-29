<?php


class SI_Select extends SI_Item {

    protected
        $selectedVal = null,
        $items = [];



    function __construct($items = [], $selectedVal = null)
    {
        $this->selectedVal = $selectedVal;
        $this->items($items);
    }

    function selected($testVal){
        if($this->selectedVal){
            return strval($testVal)==strval($this->selectedVal());
        }else{
            return false;
        }
    }
}