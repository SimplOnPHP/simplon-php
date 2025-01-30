<?php
class SI_AjaxLink extends SI_Item {
    protected $href;
    protected $text;
    
    function __construct($href, $content, $icon = null) {
        $this->href = $href;
        $this->content = $content;
        $this->icon = $icon;
        $this->styles = '.SI_Link .icon{
                height: 1.5em;
                width: 1.5em;
        }';
        $this->addStylesToAutoCSS();
    }

    function setTagsVals($renderVals = null) {
        if($renderVals['icon'] AND is_string($renderVals['content'])){
            $image = new SI_Image($renderVals['icon'],$renderVals['content']);
            $image->class('icon');
            $renderVals['content'] = $image;
        }
        $this->start = '<a class="SI_Link Ajax" href="' . $renderVals['href'] . '">';
        $this->end = "</a>\n";
    }
}