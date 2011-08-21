<?php
use \DOF\Elements\Element, \DOF\Datas;

class Fe extends Element
{
	public function construct($id = null, &$specialDataStorage = null) {
	    $this->id = new Datas\Id('Id');
		$this->cabeza = new Datas\String('¿Cuando tendrás Fe?', 'S');
		$this->contenido = new Datas\String('Contenido', 'S');
		$this->home = new Datas\ElementContainer(new Home(), 'Home');
	}
}