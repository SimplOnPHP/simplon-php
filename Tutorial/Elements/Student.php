<?php
namespace Tutorial\Elements;


use SimplOn\Datas\ElementsContainer;
use SimplOn\Datas\NumericId;
use SimplOn\Datas\String;
use SimplOn\Elements\Element;

class Student extends Element {
    function construct() {
        $this->id = new NumericId();
        $this->full_name = new String('Full name', 'S');
        $this->emails = new ElementsContainer(array(new EmailAddress()), 'Emails');
    }
}