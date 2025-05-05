<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

class SI_SystemMessage extends SI_Item {
    protected 
        $logo,
        $content;

    function __construct( $message = null, $logo = null) {

        $this->message = $message;

        $this->logo = $logo;
        $this->addClass('SI_SystemMenu');

        $this->addStylesToAutoCSS('
            .page-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.4);
                z-index: 9998;
                display: none;
            }
            .message{
                position:absolute;
                top:50%;
                left:50%;
                transform:translate(-50%,-50%);
                background-color:#4CAF50;
                color:white;padding:1rem;
                font-size:0rem;
                box-shadow:0 0 20px rgba(0,0,0,.5);
                border-radius:5px;
                text-align:center;
                width:0;
                z-index:9999
            }
            .close{
                position:absolute;
                top:-.75rem;right:.5rem;
                color:white;
                font-weight:bold;cursor:pointer
            }

            .EA_message{
                width: 1.35rem;
            }
            ');
        
        static::$cssfiles['pico.min.css'] = './css/pico.min.css'; 
        static::$cssfiles['simplon-base.css'] = './css/simplon-base.css'; 
        static::$cssfiles['simplon-auto.css'] = './css/simplon-auto.css'; 
        static::$cssfiles['colorbox.css'] = './css/colorbox.css'; 
        static::$jsfiles['00_jquery-3.7.1.min.js'] = './js/00_jquery-3.7.1.min.js'; 
        static::$jsfiles['05_jquery-ui-1.8.19.custom.min.js'] = './js/05_jquery-ui-1.8.19.custom.min.js';
        static::$jsfiles['11_SystemMessage.js'] = './js/11_SystemMessage.js';
    }

    function setTagsVals($renderVals = null) {


        $item = new SI_Item();
        $item->addClass('EA_message');

        $overlay = new SI_Item();
        $overlay->addClass('page-overlay');

        $message = new SI_Item();
        $message->addClass('message');
            $messageText = new SI_Item($renderVals['message']);
            $messageText->addClass('messageText');
            $messageText->tag('span');
            $close = new SI_Item('&times;');
            $close->addClass('close');
            $close->tag('span');
        $message->content([$messageText, $close]);

        
        if($renderVals['message']){
            $icon = new SI_Image('notice.svg');
            $icon->addClass('show-message-button');

            $item->content([$overlay, $message, $icon]);
        }else{
            $item->content('');
        }
        $this->SI_Item = $item;



    }
}
