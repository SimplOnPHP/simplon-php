<?php
class Asistente extends \SimplOn\Elements\Element
{
	public function construct($id = null, $storage=null, &$specialDataStorage = null) {
	    $this->id = new \SimplOn\Datas\NumericId('Id');
		$this->firstname = new \SimplOn\Datas\String('Name','VCUSL');
		$this->lastname = new \SimplOn\Datas\String('Last Name', 'RsL');
	}
}