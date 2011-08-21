<?php
class Asistente extends \DOF\Elements\Element
{	
	public function construct($id = null, &$specialDataStorage = null) {
	    $this->id = new \DOF\Datas\Id('Id asistente');
	}
}