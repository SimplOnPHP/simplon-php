<?php
namespace Tutorial\Elements;

use SimplOn\Elements\Element;
use SimplOn\Datas\NumericId;
use SimplOn\Datas\String;
use SimplOn\Datas\Email as EmailString;

class Email extends Element {
    
    function construct($id_or_array = null, &$specialDataStorage = null) {
        $this->id = new NumericId('Id');
        $this->label = new String('Label');
        $this->emailAddress = new EmailString('Address', 'S');
    }
	
}