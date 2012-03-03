<?php
use \DOF\Elements\Element, \DOF\Datas;

class User extends Element
{
	public function construct($id = null, &$specialDataStorage = null)
	{
		$this->usuario = new Datas\StringId('User','VCUSL');
		$this->password = new Datas\Password('Password');
	
		//$this->filterCriteria('.cabeza OR contenido == "igual" OR contenido ^= "empieza" OR contenido $= "acaba" OR contenido ~= "papas a \"la\" .contenido francesa"');
		
	}
}