<?php

class Clase extends \SimplOn\Elements\Element{
	
	
	function construct($id_or_array = null, &$specialDataStorage = null) {
		$this->id = new \SimplOn\Datas\NumericId();
		$this->nombre = new \SimplOn\Datas\String('Nombre');
		$this->maestro = new \SimplOn\Datas\ElementContainer(new Maestro());
		$this->alumnos = new \SimplOn\Datas\ElementsContainer(array('Alumno'));
	}
	
}

?>
