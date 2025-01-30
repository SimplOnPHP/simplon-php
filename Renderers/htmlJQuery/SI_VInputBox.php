<?php
class SI_VInputBox extends SI_InputBox {
    function setTagsVals($renderVals = null) {

        $this->start = "";
        $this->end = "";

        if($renderVals['label']){ $label = new SI_InputLabel($renderVals['label'],$renderVals['input']->ObjectId(),$renderVals['input']->required()); }
        else{ $renderVals['label'] = new SI_InputLabel($renderVals['input']->placeHolder(),$renderVals['input']->ObjectId(),$renderVals['input']->required()); }
        $renderVals['input']->placeHolder('');
        $this->content = new SI_VContainer([$label,$renderVals['input']]);
    }
}
