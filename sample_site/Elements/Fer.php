<?php
use \DOF\Elements\Element, \DOF\Datas;

class Fer extends Element
{
	public function construct($id = null, $storage=null, &$specialDataStorage = null) {
	    $this->id = new \DOF\Datas\NumericId('Id');
		$this->user = new Datas\Alphanumeric('User Name', 'S');
		$this->email = new Datas\Email('Email', 'S');
	}
}