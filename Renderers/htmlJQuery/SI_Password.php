<?php

class SI_Password extends SI_Input {
    
    function __construct($name, $value='', $placeHolder='(not changed)', $required=false, $id=false) {
        parent::__construct($name, $value, 'password', $placeHolder, $required, $id );
        $this->styles ='
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
        ';
        self::addStylesToAutoCSS();
    }

    function setTagsVals($renderVals = null) {
        $required = $renderVals['required'] ? "required" : "";
        $htmlId = $this->instanceId();
        $name = $renderVals['name'];
        $placeholder = $renderVals['placeHolder'];


        $input = new SI_Input($name, $renderVals['value'], 'password', $placeholder, $required, $htmlId);

        $seeImage =  new SI_Image('see.svg', 'Toggle password visibility');
        $seeImage->class("$htmlId");
        $dontSeeImage =  new SI_Image('dontSee.svg', 'Toggle password visibility');
        $dontSeeImage->class("$htmlId");
        $seeImage->onclick('togglePasswordVisibility("'.$htmlId.'")');
        $dontSeeImage->onclick('togglePasswordVisibility("'.$htmlId.'")');



        $this->start = new SI_HContainer([
            $input, 
            $seeImage,$dontSeeImage
        ],'l l hidden','auto auto');


       // $this->start = '<span>'.$input.$seeImage.$dontSeeImage.'</span>';


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

//         $this->start = <<<HTML
//             <div class="password-container">
//                 <input id="$htmlId" 
//                        name="$name" 
//                        placeholder="$placeholder" 
//                        type="password"
//                        $required />
//                 <i onclick="togglePasswordVisibility('$htmlId')" class="fas fa-eye"></i>
//             </div>
//             <script>
//                 function togglePasswordVisibility(passwordFieldId) {
//                     var passwordField = document.getElementById(passwordFieldId);
//                     var icon = passwordField.nextElementSibling;
                    
//                     if (passwordField.type === "password") {
//                         passwordField.type = "text";
//                         icon.classList.remove("fa-eye");
//                         icon.classList.add("fa-eye-slash");
//                     } else {
//                         passwordField.type = "password";
//                         icon.classList.remove("fa-eye-slash");
//                         icon.classList.add("fa-eye");
//                     }
//                 }
//             </script>
// HTML;
    }
}
