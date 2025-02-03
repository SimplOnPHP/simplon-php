<?php
class SI_Image extends SI_Item {
    protected $imageName, $onclick;
    
    function __construct($imageName,$title = null) {
        $this->imageName = $imageName;        
        if($title){$this->addAttribute('title',$title);}
    }

    function setTagsVals($renderVals = null) {

        if (strpos($renderVals['imageName'], '/') === false && strpos($renderVals['imageName'], '\\') === false) {
            $this->addAttribute('src',SC_Main::$RENDERER->imgsWebRoot().DIRECTORY_SEPARATOR.$renderVals['imageName']);
        } else {
            $this->addAttribute('src',$renderVals['imageName']);
        }

        $this->start = '<img '.$this->attributesString().' />';
    }



}
