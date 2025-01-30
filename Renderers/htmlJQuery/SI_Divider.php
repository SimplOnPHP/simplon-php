<?php
class SI_Divider extends SI_Item {
    protected 
        $width,
        $content;

    function __construct( $content = null, $width = '100%') {
        if(is_numeric($width AND $width > 0 AND $width <= 100)){ $width = $width . '%'; }
        else { $width = ''; }
        $this->content = $content;
        $this->width = $width;

        $this->styles = '
            div.divider {
                display: flex;
                align-items: center;
                text-align: center;
            }
            div.divider::before,
            div.divider::after {
                content: "";
                flex: 1;
                border-bottom: 1px solid var(--secondary);
                height: 50%;
            }

            div.divider:not(:empty)::before {
                margin-right: 10px;
            }
            
            hr.divider {
                border: 0;
                border-bottom: 1px solid var(--secondary);
                height: 1px;
            }

            div.divider:not(:empty)::after {
                margin-left: 10px;
            }
                
            .divider > h1, .divider > h2, .divider > h3, .divider > h4, .divider > h5, .divider > h6 {
                display: block;
                margin-block-start: 0em;
                margin-block-end: 0em;
                margin-inline-start: 0px;
                margin-inline-end: 0px;
                font-weight: bold;
                unicode-bidi: isolate;               
            }
            ';
        $this->addStylesToAutoCSS();
        
        static::$cssfiles['pico.min.css'] = './css/pico.min.css'; 
    }

    function setTagsVals($renderVals = null) {
        if(empty($this->content)){ 
            $style = '';    
            if($renderVals['width']){ $style = 'style="width:' . $renderVals['width'] .';';}
            $this->start = "<hr class='divider' $style />"; 
        }
        else{
            $this->start = '<div class="divider">';
            $this->end = "</div>\n";
        }
    }
}