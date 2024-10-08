<?php


class SR_verticalContainer extends SR_interfaceContainer
{
    function __construct($name = ''){
        $this->name=$name;
        $this->parentWrap['start']='<div class="SR_verticalContainer '.$this->name.'">';
        $this->parentWrap['end']='</div>';
    }
}