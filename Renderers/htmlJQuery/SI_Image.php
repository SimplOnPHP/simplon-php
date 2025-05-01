<?php

/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/
class SI_Image extends SI_Item {
    protected $imageName, $onclick, $width, $height;
    
    function __construct($imageName, $title = null, $width = null, $height = null) {
        $this->imageName = $imageName;        
        $this->width = $width;
        $this->height = $height;
        if($title){$this->addAttribute('title',$title);}
    }

    function setTagsVals($renderVals = null) {

        if (strpos($renderVals['imageName'], '/') === false && strpos($renderVals['imageName'], '\\') === false) {
            $this->addAttribute('src',SC_Main::$RENDERER->imgsWebRoot().DIRECTORY_SEPARATOR.$renderVals['imageName']);
        } else {
            $this->addAttribute('src',$renderVals['imageName']);
        }
        
        // Add width attribute if set
        if (isset($renderVals['width']) && $renderVals['width'] !== null) {
            $this->addAttribute('width', $renderVals['width']);
        }
        
        // Add height attribute if set
        if (isset($renderVals['height']) && $renderVals['height'] !== null) {
            $this->addAttribute('height', $renderVals['height']);
        }

        $this->start = '<img '.$this->attributesString().' />';
    }
}
