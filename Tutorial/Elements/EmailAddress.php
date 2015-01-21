<?php
namespace Tutorial\Elements;


use SimplOn\Datas\Email;
use SimplOn\Datas\NumericId;
use SimplOn\Datas\String;
use SimplOn\Elements\Element;

class EmailAddress extends Element {
    function construct() {
        $this->id = new NumericId();
        $this->label = new String('Label');
        $this->address = new Email('Address', 'S');
    }
}