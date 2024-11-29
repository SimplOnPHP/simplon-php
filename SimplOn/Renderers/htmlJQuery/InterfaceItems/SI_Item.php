<?php


class SI_Item extends SC_BaseObject{
    protected
        $name = '',
        $renderer;  
    
    function __construct()
    {
        $this->renderer = SC_Main::$RENDERER;
    }

    function __toString(){
        return SC_Main::$RENDERER->render($this);
    }
}