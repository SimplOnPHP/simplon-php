<?php
namespace DOF\Datas;

class String extends Data {
	
	protected $view = true;
	protected $create = true;
	protected $update = true;
	
	function showInput($fill)
	{
		return  '<input id="'.$id.'" class="DOF input '.$this->getClass().'" name="'.$this->inputName().'" '.(($fill)?'value="'.$this->val().'"':'').' type="text" />';
	}
}