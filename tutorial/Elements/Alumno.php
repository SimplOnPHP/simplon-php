<?php

class Alumno extends Persona{
	
	
	function construct($id_or_array = null, &$specialDataStorage = null) {
		parent::construct($id_or_array, $specialDataStorage);
		
		$this->nombre->dataFlags('v');
		$this->apellidoPaterno->dataFlags('v');
		$this->apellidoMaterno->dataFlags('v');
		
		$this->nombreCompleto = new \SimplOn\Datas\Concat('Nombre completo',array(' - ','nombre','apellidoPaterno','apellidoMaterno'));
		
		$this->carrera = new \SimplOn\Datas\String('Carera');
		$this->semestre = new \SimplOn\Datas\Integer('Semestre');

		$this->c1 = new \SimplOn\Datas\Integer('c1','l');
		$this->c2 = new \SimplOn\Datas\Integer('c2','l');
		$this->c3 = new \SimplOn\Datas\Integer('c3','l');
		
		$this->promedio = new \SimplOn\Datas\Average('Promedio', array('c1','c2','c3'),'Lv');
	}
	
}