<?php


class SI_Divider extends SI_Item {

    protected
        $text = '';

    public function __construct($text = null){
        parent::__construct();
        $this->text = $text;
    }   
}