<?php

/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

class SI_Password extends SI_Input {
    
    function __construct($name, $value='', $placeHolder='(not changed)', $required=false, $id=false) {
        parent::__construct($name, $value, 'password', $placeHolder, $required, $id );
        self::addStylesToAutoCSS('
            .password-container {
                display: flex;
                align-items: center;
            }

            .password-container input {
                flex-grow: 1;
                margin-right: 0.2rem;
            }

            .password-container i {
                cursor: pointer;
                flex-shrink: 0;
                margin-right: 0.2rem;
            }
        ');
    }

    function setTagsVals($renderVals = null) {

        $required = $renderVals['required'] ? "required" : "";
        $htmlId = $this->instanceId();
        $name = $this->getAttribute('name');
        $placeholder = $this->getAttribute('placeHolder');


        $input = new SI_Input($name, @$renderVals['value'], 'password', $placeholder, $required, $htmlId);

        $seeImage =  new SI_Image('viewIcon.svg', 'Toggle password visibility');
        $seeImage->addClass("$htmlId");
        $dontSeeImage =  new SI_Image('dontSee.svg', 'Toggle password visibility');
        $dontSeeImage->addClass("$htmlId");
        $seeImage->addAttribute('onclick',"togglePasswordVisibility('$htmlId')");
        $dontSeeImage->addAttribute('onclick',"togglePasswordVisibility('$htmlId')");

        $this->start = new SI_HContainer([
            $input, 
            $seeImage,$dontSeeImage
        ],'l l hidden','auto auto');


        $this->end = new SI_Script('
            function togglePasswordVisibility(passwordFieldId) {
                    var passwordField = document.getElementById(passwordFieldId);  
                    if (passwordField.type === "password") {
                        passwordField.type = "text";
                        $("."+passwordFieldId).parent().toggle();
                    } else {
                        passwordField.type = "password";
                        $("."+passwordFieldId).parent().toggle();
                    }
            }
        ');
    }
}
