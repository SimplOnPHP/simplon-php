<?php

class Persona extends \SimplOn\Elements\Element{
    
    function construct($id_or_array = null, &$specialDataStorage = null) {
		$this->id = new \SimplOn\Datas\NumericId('Id');
		$this->nombre = new \SimplOn\Datas\String('Nombre','S');
		$this->apellidoPaterno = new \SimplOn\Datas\String('Apellido Paterno');
		$this->apellidoMaterno = new \SimplOn\Datas\String('Apellido Materno','l');
    }
    
}
