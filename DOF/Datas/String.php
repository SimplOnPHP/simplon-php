<?php
namespace DOF\Datas;

class String extends Data {
	
	protected $view = true;
	protected $create = true;
	protected $update = true;
	
	function showInput($fill)
	{
		return  '<input class="input '.str_replace('\\', ' ', $this->getClass()).'" name="'.$this->inputName().'" '.(($fill)?'value="'.$this->val().'"':'').' type="text" />';
	}
}