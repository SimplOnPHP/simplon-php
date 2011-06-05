<?php
namespace DOF;

class Browser extends BaseObject
{
	/* @var $element Element */
	protected $element;
	
	public function __construct($element){
		//@todo allow to recive arrays
		
		/*@var $element Element */
		$this->element = $element;
	}
  
	public function form($template=null)
	{
		return $this->element->formGetter('search', true);
	}
	
	
	
}