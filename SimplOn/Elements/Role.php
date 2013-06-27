<?php
namespace SimplOn\Elements;
use \SimplOn\Datas;

class Role extends Element
{

	public function construct($id = null, &$specialDataStorage = null)
	{
		$this->name = new Datas\StringId();
                $this->level = new Datas\Integer();
		$this->description = new Datas\Text();
                
	
		//$this->filterCriteria('.cabeza OR contenido == "igual" OR contenido ^= "empieza" OR contenido $= "acaba" OR contenido ~= "papas a \"la\" .contenido francesa"');
		
	}

}