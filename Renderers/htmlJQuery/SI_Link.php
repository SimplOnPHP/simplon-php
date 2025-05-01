<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/
class SI_Link extends SI_Item {
    protected $href, $text, $onclick = '';
    
function __construct($href, $content, $icon = null, $Iconheight = '1.5em', $Iconwidth = '1.5em') {
        if($href){$this->addAttribute('href', $href);}
        $this->addClass('SI_Link');
        $this->content = $content;
        $this->icon = $icon;
        $this->addStylesToAutoCSS('.SI_Link .icon{
                height: '.$Iconheight.';
                width: '.$Iconwidth.';
        }');
    }

    function setTagsVals($renderVals = null) {
        if($renderVals['icon'] AND is_string($renderVals['content'])){
            $image = new SI_Image($renderVals['icon'],$renderVals['content']);
            $image->addClass('icon');
            $renderVals['content'] = $image;
        }
        $this->start = '<a '.$this->attributesString().'>';
        $this->end = "</a>\n";
    }
}