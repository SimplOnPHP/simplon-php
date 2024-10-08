<?php


class SI_Page extends SC_BaseObject {
    protected
        $name,
        $jslinks,
        $csslinks,
        $tittle,
        $renderer,
        $content;
 
    function __construct($content = null, $title = '', $jslinks = [], $csslinks = [] )
    {        

        $this->renderer = SC_Main::$RENDERER;
        if(empty($jss)){ $jslinks = $this->renderer::$jslinks; }
        if(empty($csss)){ $csslinks = $this->renderer::$csslinks; }
        
        $this->jslinks = $jslinks;
        $this->csslinks = $csslinks;
        $this->$title = $title;
        $this->content = $content;
    }
}