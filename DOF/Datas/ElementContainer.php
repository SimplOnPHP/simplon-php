<?php
namespace DOF\Datas;

class ElementContainer extends Data {
		
	protected $element;
	protected $parent;
	
	public function __construct( \DOF\Elements\Element $element, $label=null, $vcuslr=null, $element_id=null) {
		
		$this->element($element);			
		
		parent::__construct($label,$field,$vcuslr,$element_id);
	}
	
	
	function showView($template = null)
	{
		//return $this->parent()->getClass();
		
		
		return $this->element()->showView();
	}	
	
	function showInput($template = null)
	{
		return $this->parent()->getClass();
		
		
		//return $this->element()->showView();
	}
	
	
}