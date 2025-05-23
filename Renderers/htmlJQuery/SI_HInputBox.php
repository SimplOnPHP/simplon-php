<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

class SI_HInputBox extends SI_InputBox {    
    function setTagsVals($renderVals = null) {

        $this->start = "";
        $this->end = "";
        
        if($renderVals['label']){ 
            $renderVals['label'] = new SI_InputLabel($renderVals['label'],$renderVals['input']->ObjectId(),$renderVals['input']->required()); 
        }else{ 
            $renderVals['label'] = new SI_InputLabel($renderVals['input']->getAttribute('placeHolder'),$renderVals['input']->ObjectId(),$renderVals['input']->required());
        }
        $renderVals['input']->removeAttribute('placeHolder');
        $this->content = new SI_HContainer([$renderVals['label'],$renderVals['input']],'r l');
    }
}
