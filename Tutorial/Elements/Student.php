<?php
namespace Tutorial\Elements;


use SimplOn\Datas\Date;
use SimplOn\Datas\ElementsContainer;
use SimplOn\Datas\NumericId;
use SimplOn\Datas\String;
use SimplOn\Datas\TimeSince;
use SimplOn\Elements\Element;

class Student extends Element {
    function construct() {
        $this->id = new NumericId();
        $this->first_name = new String('First name', 'S');
        $this->last_name = new String('Last name', 'S');
        $this->emails = new ElementsContainer(array(new EmailAddress()), 'Emails');

        $this->birth_date = new Date('Birth date');
        $this->age = new TimeSince('Age', $this->birth_date, '%y', 'L');
    }
}