<?php


class SI_Title extends SI_Item{
    protected
        $doe,
        $text,
        $priority;  
    
    function __construct($doe, $text = '', $priority = 1)
    {
        if( !(    is_int($priority) AND $priority >= 1 AND $priority <= 6    ) ){
            throw(new SC_Exception('SI_TItle priority must be an Integer between 1 and 6'));
        }
        $this->doe = $doe;
        $this->text = $text;
        $this->priority = $priority;
    }

    function readTemplate(){
        $itemDom = parent::readTemplate();
        
        if($this->priority !== '::1'){
            $itemDom = str_replace(['<h1','</h1>'], ['<h'.$this->priority,'</h'.$this->priority.'>'], $itemDom);
        }
        
        return $itemDom;
    }


}