<?php

/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

class SI_CancelButton extends SI_Button {
    function __construct() {
        parent::__construct(
            SC_Main::L('Cancel'), 
            'window.history.back()'
        );
    }
}
