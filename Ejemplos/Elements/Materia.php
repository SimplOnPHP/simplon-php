<?php

use \SimplOn\Elements\Element, \SimplOn\Datas;
class Materia extends Element{
	function construct($id_or_array = null, &$specialDataStorage = null) {
		$this->id = new Datas\NumericId('Id');
		$this->materia = new Datas\String('Nombre de la Materia','CUR');
	}
	
}

