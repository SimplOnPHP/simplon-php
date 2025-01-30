<?php

class SI_CancelButton extends SI_Button {
    function __construct() {
        parent::__construct(
            SC_Main::L('Cancel'), 
            'window.history.back()'
        );
    }
}
