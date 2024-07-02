<?php


class SR_listContainer extends SR_interfaceContainer
{
    function __construct($name = ''){
        $this->name=$name;
        $this->parentWrap['start']='<ul '.$this->name.'">';
            $this->childWrap['start']='<li>';
            $this->childWrap['end']='</li>';
        $this->parentWrap['end']='</ul>';
    }

    function getLayout($method = '') {
        return parent::getLayout('showEmbeded');
    }
}