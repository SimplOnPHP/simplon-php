<?php


class SR_horizontalContainer extends SR_interfaceContainer
{
    function __construct($name = ''){
        $this->name=$name;
        $this->parentWrap['start']='<div class="SR_horizontalContainer '.$this->name.'">';
        $this->parentWrap['end']='</div>';
        $this->childWrap['start']='<div>';
        $this->childWrap['end']='</div>';
        $this->widths = null;
    }

    function fillLayout($Layout) {
        if ($this->widths) {   
            $currentStyle = $Layout->attr('style');
            $newStyle = $currentStyle.' grid-template-columns: '.implode('fr ', $this->widths).'fr;';
            $Layout->attr('style', $newStyle);
        }
        return parent::fillLayout($Layout);
    }

}