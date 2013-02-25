<?php
class Asistente extends \SimplOn\Elements\Element
{	
	public function construct($id = null, $storage=null, &$specialDataStorage = null) {
	    $this->id = new \SimplOn\Datas\NumericId('Id asistente');
	}
}