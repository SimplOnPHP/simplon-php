<?php
namespace DOF\Datas;

class DArray extends Data {
	
	protected $view = true;
	protected $create = true;
	protected $update = true;
	
	//@todo determine the proper input for the array
	function showInput($fill)
	{
		return '<input class="input '.str_replace('\\', ' ', $this->getClass()).'" name="'.$this->inputName().'" '.(($fill)?'value="'.$this->val().'"':'').' type="text" />';
	}


	function val($val=null){
		if(isset($val)){
			if(is_array($val))
				$this->val = $val;
			else
				throw new \DOF\Exception('Reveived value should be an array!');
		}else{
			return $this->val;
		}
	}
	
	
}