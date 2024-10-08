<?php


class SI_Link extends SI_Item {

    protected
        $text = '',
        $href = '';

    public function __construct($text, $href){

        $this->text = $text;

        if(is_string($href)){
            $this->href = $href;
        }
        // elseif(is_array($href)){
		//     $this->href = $this->renderer()->action($href[0], $href[1]);
        // }
    }   
}