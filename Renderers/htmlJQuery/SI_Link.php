<?php
class SI_Link extends SI_Item {
    protected $href, $text, $onclick = '';
    
    function __construct($href, $content, $icon = null) {
        $this->href = $href;
        $this->content = $content;
        $this->icon = $icon;
        $this->styles = '.SI_Link .icon{
                height: 1.5em;
                width: 1.5em;
        }
        .SI_Link{
            display: inline-block;
        }
        ';
        $this->addStylesToAutoCSS();
    }

    function setTagsVals($renderVals = null) {
        $onclick = $renderVals['onclick'] ? "onclick='{$renderVals['onclick']}'" : "";
        if($renderVals['icon'] AND is_string($renderVals['content'])){
            $image = new SI_Image($renderVals['icon'],$renderVals['content']);
            $image->class('icon');
            $renderVals['content'] = $image;
        }
        $this->start = '<a '.$onclick.' class="SI_Link" href="' . $renderVals['href'] . '">';
        $this->end = "</a>\n";
    }
}