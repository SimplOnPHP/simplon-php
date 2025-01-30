<?php
 

class SI_Title extends SI_Item{
    protected
        $text,
        $aling,
        $level;  

    function __construct($text, $level= 1, $aling = 'c' ) {
        $this->content = $text;
        $this->level = $level;
        $this->aling = $aling;
    }

    function setTagsVals($renderVals = null){
        $level = $renderVals['level'];
        if($this->aling){$class = " class='$this->aling'";}
        $this->start = "<h$level$class>";
        $this->end = "</h$level>";
    }
}