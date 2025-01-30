<?php
class SI_IInputBox extends SI_InputBox {
    function html() {
        $this->setRenderVals();
        if($this->label){ $this->input->placeHolder($this->label); }
        return $this->input->html();
    }
}