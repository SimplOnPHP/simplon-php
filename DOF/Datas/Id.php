<?php
namespace DOF\Datas;

/**
* ID para las tablas
* --- No imprime un label y manda un input hidden.
*
* @version	1.0
* @author	Ruben Schaffer
* @todo
*/
class Id extends Data
{
	public function setDefaultsetVCUSLR()
	{
			$this->view(false);
			$this->create(false);
			$this->update(true);
			$this->search(false);
			$this->list(false);
			$this->required(false);
	}
	
	public function showUpdate($printval=true)
	{
		if($printval && $this->val())
		{
			return "<input name='".$this->field()."'".(($printval)?" value='".$this->val()."'":"")." type='hidden' />";
		}
	}
	
	public function label(){}
	
}