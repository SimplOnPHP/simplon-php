<?php
abstract class SI_InputBox extends SI_Item {
    public function __construct(SI_Input $input, $label) {
        $this->label = $label;
        $this->input = $input;
    }

}
