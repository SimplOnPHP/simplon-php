<?php


class SR_formButton extends SR_interfaceItem
{
    function __construct($type, $text, $extra = ''){
        $this->text=$text;
        if($type == 'cancel'){
            $extra .= ' onclick="window.history.go(-1); return false;" ';
            $type='button';
        }
        $this->type=$type;
        $this->extra=$extra;
    }
    
    function getLayout() {
        return pq('<button type="'.$this->type.'"  '.$this->extra.'>'.$this->text.'</button>');
    }

}

/**
 
 <button type="submit">Submit Form</button>
  <button type="button" onclick="window.history.go(-1); return false;">Cancel</button>


 */