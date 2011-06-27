<?php
namespace DOF\Datas;

class Float extends Data {
	function val($val = null) {
		if(isset($val)) {
			if(is_numeric($val))
				$this->val = floatval($val);
			else
				user_error('Non-numeric value received.');
		} else {
			return $this->val;
		}
	}
	
	public function showInput($fill)
	{
		return '<input name="'.$this->field().'" '.(($fill)?'value="'.$this->val().'"':'').' />';
	}
}