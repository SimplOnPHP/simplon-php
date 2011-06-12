<?php
namespace DOF\Datas;

class HTMLText extends Abstract_
{
	//IMP falta hacer que realmente salga un editor en vez de la textArea
	public function updateInput($printval=true)
	{
		global $prefijo;
	
		$ret.="<textarea class='input".$this->getClass()." editor' id='".$this->inputName().$prefijo."' name='".$this->inputName()."'>".(($printval)?"$this->val":"")."</textarea>\n";

		return $ret;
	}


}