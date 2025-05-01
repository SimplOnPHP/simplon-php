<?php

/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

class SI_IInputBox extends SI_InputBox {
    function html() {
        $this->setRenderVals();
        if($this->label){ $this->input->placeHolder($this->label); }
        return $this->input->html();
    }
}