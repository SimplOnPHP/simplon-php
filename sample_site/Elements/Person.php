<?php
class Person extends \DOF\Elements\Element
{
	public function construct($id = null, $storage=null, &$specialDataStorage = null)
	{
	    $this->id = new \DOF\Datas\NumericId('CURP');
		$this->firstname = new \DOF\Datas\String('Name','VCUSL');
		$this->lastname = new \DOF\Datas\String('Last Name', 'RsL');
        $this->RadioButtonSelfId = new \DOF\Datas\RadioButtonText('Numerico', array('a','b','c'));
		
	}
}