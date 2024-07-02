<?php


class SR_link extends SR_interfaceItem
{
    function __construct($class, $method, $text, $construct_params = null, $method_params = null, $dataName = null){
        $href = SC_Main::$RENDERER->encodeURL($class, $construct_params, $method, $method_params, $dataName);
        $this->link = '<a href="'.$href.'"> '.$text." </a> \n";
    }

    function getLayout($method) {
        return pq($this->link);
    }
}