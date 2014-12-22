<?php
namespace Tutorial\Elements;

use SimplOn\Elements\Element;
use SimplOn\Datas\NumericId;
use SimplOn\Datas\String;
use SimplOn\Datas\ElementContainer;
use SimplOn\Datas\Date;
use SimplOn\Datas\TimeSince;
use SimplOn\Datas\Concat;

use Tutorial\Elements\Email;
use Tutorial\Elements\Phone;

class Student extends Element {
    
    function construct($id_or_array = null, &$specialDataStorage = null) {
        $this->id = new NumericId('Id');
        $this->fullName = new String('Full name','S');
        $this->emails = new ElementContainer(new Email(), 'Email addresses');
        $this->phones = new ElementContainer(new Phone(), 'Phone numbers');
        
        $this->birthDate = new Date('Date of birth', 'l');
        $this->age = new TimeSince('Age', 'L', 'years', $this->birthDate->val());
        
        $this->register = new Concat('Register', array('','fullName','birthDate', 'id'), 'V');
    }
	
}