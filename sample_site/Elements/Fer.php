<?php
use \SimplOn\Elements\Element, \SimplOn\Datas;

class Fer extends Element
{
	public function construct($id = null, $storage=null, &$specialDataStorage = null) {
	    $this->id = new \SimplOn\Datas\NumericId('Id');
		$this->user = new Datas\Alphanumeric('User Name', 'SR');
		$this->dateofbirth = new Datas\Date('Date of Birth', 'SR');
		$this->email = new Datas\Email('Email', 'SR');
	}
}