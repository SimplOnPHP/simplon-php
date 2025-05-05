<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

class SI_SystemMenu extends SI_Item {
    protected 
        $logo,
        $content,
        $SYSmessage;

    function __construct( $content = null, $logo = null) {

        $this->content = $content;
        $this->content = $content;
        $this->logo = $logo;
        $this->addClass('SI_SystemMenu');

        $this->addStylesToAutoCSS('
            .SI_SystemMenu{
                margin-left: 0.25rem;
                z-index:999;
                background-color: var(--code-background-color);
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
                    margin-top:1.9rem;
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
        static::$jsfiles['00_jquery-3.7.1.min.js'] = './js/00_jquery-3.7.1.min.js'; 
        static::$jsfiles['05_jquery-ui-1.8.19.custom.min.js'] = './js/05_jquery-ui-1.8.19.custom.min.js';

        //needed here so that the JS files are properly included 
        $this->SYSmessage = new SI_SystemMessage(SC_Main::$SystemMessage); 
    }

    function setTagsVals($renderVals = null) {

        if($this->logo instanceof SI_Image){ @$logo = $this->logo; }
        elseif(is_string($this->logo)){ @$logo = new SI_Image($this->logo); }
        elseif(empty($this->logo)){ @$logo = new SI_Image('favicon.ico'); }

        // if not setted once more here I don't know why is set to blank
        $this->SYSmessage = new SI_SystemMessage(SC_Main::$SystemMessage);

        $links = new SI_Item($this->content);
            $links->addClass('links');
            $links->addClass('items');
        $gretting = new SI_Text(SC_Main::L(SC_Main::$PERMISSIONS->userName()));
        $hambuergerIcon = new SI_Image('menu.svg');
            $hambuergerIcon->addClass('hamburgerMenuIcon');
            $hambuergerIcon->addAttribute('onclick',"$('.SI_SystemMenu .items').toggle();");



        $topBar = new SI_HContainer(null,'r r c r r','1rem 1rem auto auto 1.7rem');
        $topBar->addClass('SI_SystemMenu');
            $topBar->addItem($logo);
            $topBar->addItem($this->SYSmessage);
            $topBar->addItem($links);
            $topBar->addItem($gretting);
            $topBar->addItem($hambuergerIcon);


        $this->SI_Item = $topBar;


    //     $this->start = '<div '.$this->attributesString().'>
    //     <div class="logo">'.@$logo.'</div>
    //     '.$message.'
    //     <div>
    //         <div class="items links">';




    //     $this->end = '</div>
    //     </div>
    //     <div class="hamburgerMenuIcon" onclick=\'$(".SI_SystemMenu .items").toggle();\'>
    //         '.$hambuergerIcon.'
    //     </div>
    // </div>'."\n";

    }
}
