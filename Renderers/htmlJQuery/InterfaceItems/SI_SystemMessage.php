<?php

use voku\helper\HtmlDomParser;

class SI_SystemMessage extends SI_Item{

    protected
    $text,
    $style,
    $size;
    
    //function __construct($message = null, $icon='systemMessage.svg', $time = '2')
    function __construct($message = null, $icon='systemMessage.svg')
    {
        /** @var SR_main $redender */
        $redender = $GLOBALS['redender'];
        

        if(file_exists($redender->imgsPath().DIRECTORY_SEPARATOR.$icon)){
            $this->icon = $redender->imgsWebRoot().DIRECTORY_SEPARATOR.$icon;
        }

        if(!empty($message)){
            $this->message = $message;
        }elseif(!empty(SC_Main::$SystemMessage)){
            $this->message = SC_Main::$SystemMessage;
        }else{
            $this->message = '';
            $this->style = 'background-color: transparent;';
            $icon = 'clear.webp';
        }

        if(file_exists($redender->imgsPath().DIRECTORY_SEPARATOR.$icon)){
            $this->icon = $redender->imgsWebRoot().DIRECTORY_SEPARATOR.$icon;
        }
        //$this->time = $time;
    }
}