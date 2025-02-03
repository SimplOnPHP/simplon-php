<?php
class SI_Link extends SI_Item {
    protected $href, $text, $onclick = '';
    
    function __construct($href, $content, $icon = null) {
        if($href){$this->addAttribute('href', $href);}
        $this->addClass('SI_Link');
        $this->content = $content;
        $this->icon = $icon;
        $this->addStylesToAutoCSS('.SI_Link .icon{
                height: 1.5em;
                width: 1.5em;
        }
        .SI_Link{
            display: inline-block;
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