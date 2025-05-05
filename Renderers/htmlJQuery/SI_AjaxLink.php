<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/
class SI_AjaxLink extends SI_Item {
    protected $href;
    protected $text;
    
    function __construct($href, $content, $icon = null) {
        $this->addAttribute('href',$href);
        $this->content = $content;
        $this->icon = $icon;
        $this->addStylesToAutoCSS('.SI_Link .icon{
                height: 1.5em;
                width: 1.5em;
        }');
        $this->addClass('SI_Link');
        $this->addClass('Ajax');
    }

    function setTagsVals($renderVals = null) {
        if($renderVals['icon'] AND is_string($renderVals['content'])){
            $image = new SI_Image($renderVals['icon'],$renderVals['content']);
            $image->addClass('icon');
            $renderVals['content'] = $image;
        }
        $this->start = "<a {$this->attributesString()}>";
        $this->end = "</a>\n";
    }
}