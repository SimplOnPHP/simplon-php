<?php
class SI_Image extends SI_Item {
    protected $imageName, $onclick;
    
    function __construct($imageName,$alt = null) {
        $this->imageName = $imageName;
        $this->alt= $alt;
    }

    function setTagsVals($renderVals = null) {

        $onclick = $renderVals['onclick'] ? "onclick='{$renderVals['onclick']}'" : "";
        
        if (strpos($renderVals['imageName'], '/') === false && strpos($renderVals['imageName'], '\\') === false) {
            $image = SC_Main::$RENDERER->imgsWebRoot().DIRECTORY_SEPARATOR.$renderVals['imageName'];
        } else {
            $image = $renderVals['imageName'];
        }
        if($renderVals['alt']){ $alt='title="'.$renderVals['alt'].'"'; }else{ $alt=''; }

        $class = $renderVals['class'] ? " class='{$renderVals['class']}'" : "";
        $this->start = '<img '.$class.' '.$onclick.' src="' . $image . '" '.$alt.'/>';;
    }



}
