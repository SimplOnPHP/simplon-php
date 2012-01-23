<?php
class Person extends \DOF\Elements\Element
{
	public function construct($id = null, &$specialDataStorage = null)
	{
	    $this->id = new \DOF\Datas\Id('CURP');
		$this->firstname = new \DOF\Datas\String('Name','VCUSL');
		$this->lastname = new \DOF\Datas\String('Last Name', 'sL');
	}
}