<?php
use \DOF\Elements\Element, \DOF\Datas;

class User extends Element
{
	public function construct($id = null, &$specialDataStorage = null)
	{
		$this->Usuario = new Datas\('Usuario','VCUSL');
		$this->contenido = new Datas\String('Contenido', 'sL');
		$this->owner = new Datas\ElementContainer(new Person(), 'Owner');
		$this->parasite = new Datas\ElementContainer(new Person(), 'Parasite');
		$this->storage('home');
		
		//$this->filterCriteria('.cabeza OR contenido == "igual" OR contenido ^= "empieza" OR contenido $= "acaba" OR contenido ~= "papas a \"la\" .contenido francesa"');
		
	}
}