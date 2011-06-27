<?php
namespace DOF\Datas;

class String extends Data {
	
	protected $view = true;
	protected $create = true;
	protected $update = false;
	
	function showInput($fill)
	{
		return  '<input id="'.$id.'" class="input-'.$this->getClass().'" name="'.$this->inputName().'" '.(($fill)?'value="'.$this->val().'"':'').' type="text" />';
	}
}