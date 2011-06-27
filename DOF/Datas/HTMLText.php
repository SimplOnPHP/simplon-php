<?php
namespace DOF\Datas;

class HTMLText extends Data
{
	function showInput($fill)
	{
		//@todo: display a RichText editor instead of Textarea
		return  "<textarea class='input".$this->getClass()." editor' id='".$this->inputName()."' name='".$this->inputName()."'>".(($fill)? $this->val :"")."</textarea>";
	}
}