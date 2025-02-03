<?php
class SI_SystemMenu extends SI_Item {
    protected 
        $logo,
        $content;

    function __construct( $content = null, $logo = null) {

        $this->content = $content;
        $this->logo = $logo;
        $this->addClass('SI_SystemMenu');

        $this->addStylesToAutoCSS('
            .SI_SystemMenu{
                display:grid;
                grid-template-columns: 15fr 92fr auto auto 8fr;
                min-height:1rem;
                z-index:999;
                background-color: var(--code-background-color);
            }
            
            
            div.logo img {
                margin: .35rem .35rem;
                height: 1rem;
            }
                        
            @media only screen and (min-width:600px){
                .SI_SystemMenu .hamburgerMenuIcon{display:none}
                .SI_SystemMenu .items{display:block}
                .SI_SystemMenu .items a{
                    display:inline;
                    margin:.1rem 1.0rem;
                }
            }
            
            @media only screen and (max-width:599px){
                .SI_SystemMenu .hamburgerMenuIcon{
                    display:block;
                    min-height:1rem;
                    max-width: 7vw;
                    z-index:1100;
                    margin:0 .3rem 0 0;
                    cursor:pointer;
                }
                .SI_SystemMenu .items{
                    display:none;
                    position:fixed;
                    top:0;
                    right:0;
                    width:auto;
                    min-width:40%;
                    max-width:80%;
                    height:100%;
                    z-index:999;
                    overflow-y:auto;
                    padding:1vh;
                    margin-top:1.7rem;
                    box-sizing:border-box;
                    background-color: var(--switch-background-color);
                }
                .SI_SystemMenu .items a{
                    display:block;
                    background-color: var(--switch-background-color);
                }
            }

            html[data-theme="light"] .hamburgerMenuIcon {
                filter: invert(1);
            }
            ');
        
        static::$cssfiles['pico.min.css'] = './css/pico.min.css'; 
        static::$cssfiles['simplon-base.css'] = './css/simplon-base.css'; 
        static::$cssfiles['simplon-auto.css'] = './css/simplon-auto.css'; 
        static::$cssfiles['colorbox.css'] = './css/colorbox.css'; 
        static::$jsfiles['00_jquery-1.7.2.min.js'] = './js/00_jquery-1.7.2.min.js'; 
        static::$jsfiles['05_jquery-ui-1.8.19.custom.min.js'] = './js/05_jquery-ui-1.8.19.custom.min.js'; 
    }

    function setTagsVals($renderVals = null) {

        if($this->logo instanceof SI_Image){ @$logo = $this->logo; }
        elseif(is_string($this->logo)){ @$logo = new SI_Image($this->logo); }
        elseif(empty($this->logo)){ @$logo = new SI_Image('favicon.ico'); }
        $hambuergerIcon = new SI_Image('menu.svg');

        $this->start = '<div '.$this->attributesString().'>
        <div class="logo">'.@$logo.'</div>
        <div>
            <div class="repeat items links">';




        $this->end = '</div>
        </div>
        <div class="hamburgerMenuIcon" onclick=\'$(".SI_SystemMenu .items").toggle();\'>
            '.$hambuergerIcon.'
        </div>
    </div>'."\n";

    }
}
