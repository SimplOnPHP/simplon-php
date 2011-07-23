<?php
namespace DOF\Datas;

class ElementContainer extends Data {
		
	protected $element;
	protected $parent;
	
	public function __construct( \DOF\Elements\Element $element, $label=null, $vcuslr=null, $element_id=null) {
		
		$this->element($element);			
		
		parent::__construct($label,$vcuslr,$element_id);
	}
	
	
	public function getJS($method) {
		$method = end(explode('::',$method));
		return array_map(
			function($fp) {
				return str_replace(\DOF\Main::$REMOTE_ROOT, \DOF\Main::$LOCAL_ROOT, $fp);
			},
			$this->element->getJS($method)
		);
	}
	
	
	function showView($template = null)
	{
		//return $this->parent()->getClass();
		$dom = \phpQuery::newDocumentHTML($this->element()->showView());
		
		return $dom['.DOF.'.$this->element()->getClass()].'';
	}
	
	function showInput($template = null)
	{
		return $this->parent()->getClass();
		
		
		//return $this->element()->showView();
	}


	public function val($val = null) {
		if($val !== null) {
			$this->element->fillFromDSById($val);
		} else {
			return @$this->element->id();
		}
	}
}