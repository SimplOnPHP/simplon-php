<?php
class Asistente extends \DOF\Elements\Element
{	
	public function construct($id = null, $storage=null, &$specialDataStorage = null) {
	    $this->id = new \DOF\Datas\NumericId('Id asistente');
	}
}