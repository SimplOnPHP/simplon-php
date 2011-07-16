<?php
namespace DOF\Datas;

class Date extends Data {
	
	protected $view = true;
	protected $create = true;
	protected $update = true;
	
	function showInput($fill)
	{
		return  '<input class="DOF input '.$this->getClass().'" name="'.$this->inputName().'" '.(($fill)?'value="'.$this->val().'"':'').' type="text" />';
	}
}