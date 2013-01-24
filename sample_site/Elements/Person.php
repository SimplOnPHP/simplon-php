<?php
class Person extends \SimplOn\Elements\Element
{
	public function construct($id = null, $storage=null, &$specialDataStorage = null) {
	    $this->id = new \SimplOn\Datas\NumericId('CURP');
		$this->firstname = new \SimplOn\Datas\String('Name','VCUSL');
		$this->lastname = new \SimplOn\Datas\String('Last Name', 'RsL');
        $this->RadioButtonSelfId = new \SimplOn\Datas\RadioButtonText('Numerico', array('a','b','c'));
	}
}