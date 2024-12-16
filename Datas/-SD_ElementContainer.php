<?php
/*
	Copyright © 2011 Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
	
	This file is part of “SimplOn PHP”.
	
	“SimplOn PHP” is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation version 3 of the License.
	
	“SimplOn PHP” is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with “SimplOn PHP”.  If not, see <http://www.gnu.org/licenses/>.
*/



/**
 * 
 * Encapsulates an Element so it acts as a Data. 
 * 
 * @author RSL
 */
class SD_ElementContainer extends SD_Data {
	protected 
		/**
		 * Logic parent
		 * @var SC_Element
		 */
		$parent, 
            
		/**
		 * Encapsulated element
		 * @var SC_Element
		 */
		$element,
		/*
		 * Column's name where values will be extracted
		 * @var String
		 */
		$column;

	public function __construct( $element, $label=null, $column = null, $flags=null, $element_id=null) {

		$this->column = $column;

		if($element instanceof SC_Element){
			if($element_id){$element->setId($element_id);}
			$this->element($element);
		}else{
			//To avooid ciclic Calls Use a elements stack and do no create an element that depends on the elment thats calling it
			$GLOBALS['callersStack'][] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['class'];

			if(in_array($element,$GLOBALS['callersStack'])){

				$element = new SC_ElementContainerIDPlaceHolder();
				$this->element($element);
			}else{

				$element = new $element();
				$this->element($element);
			}
			array_pop($GLOBALS['callersStack']);
		}
		parent::__construct($label,$flags,$element_id);

	}

	public function name($name = null) {
        if($name){
			$this->name = $name;
            $this->element->nameInParent($name);
		}else{
			return $this->name;
		}
	}
	
	/**
         * 
         * function getJS - this function modifies the url of the encapsulated element to 
         * don't display a incorrect url
         * 
         * @param string $method
         * @return array
         */
	public function getJS($method) {
		return array_map(
			function($fp) {
				return str_replace(SC_Main::$WEB_ROOT, SC_Main::$LOCAL_ROOT, $fp);
			},
			$this->element->getJS($method)
		);
	}
    /**
     * 
     * function parent -in this function you can designate a specific parent to 
     * use in this data, if you don't to use another parent, this function return 
     * the origal logic parent else change the logic parent for the new parent and
     * execute this function in the element to change the parent of the element.
     * 
     * @param object $parent
     * @return object
     */
    function parent(&$parent = null){
        if(!$parent){
            return $this->parent;
        } else {
            $this->parent = $parent;
            $this->element->parent($parent);
        }
    }

	function showList(){
		return $this->element->showEmbededStrip();
	}

	public function viewVal() {
		if( $this->element->getId() != $this->val() ){
			$this->element->fillElementById($this->val());
		}
		return $this->element->showEmbededStrip();
	}
	
	function showEmbeded($template = null)
	{
        if($template) {
            $template = SC_Main::loadDom($template);
            $template = $this->element->showEmbeded($template[$this->cssSelector().' '.$this->element->cssSelector()],true);
        } else {
           // creates a dummy template
           /** @var SC_Element $element */
		   $element = $this->element->getClass();
           $element = new $element($this->element->getId());
           $template = $element->showEmbeded(null, true);
        }  
        $template=$template;
        $dom = QP::withHTML5($template);

        return $dom.'';
        
	}

	function showEmbededStrip($template = null){
		if($this->val()){
			$this->element->setId( $this->val() );
			return $this->element->showEmbededStrip();
		}
	}
	


	/**
         * 
         * function val - this function introduces the values from database of son element 
         * to database of parent element without modify the original values.
         * @param type $val
         * @return type
         */
	public function val($val = null) {
		if(is_array($val)){
			if(!$this->fixedValue) {
				$this->val = $val;
				return $this->val;
			}
		}else if($val === '') {
			$class = $this->element->getClass();
			$this->element = new $class;
		} else	if($val !== null) {
			if(!$this->fixedValue) {
				$this->element->fillFromDSById($val);
			}
		} else {
			if(is_array($this->val)){
				return $this->val;
			} else return @$this->element->getId();
		}
        $this->element->addData('parentClass' , new SD_Hidden(null,'CUSf', $this->parent->getClass() )    );
        $this->element->addData('dataName' , new SD_Hidden(null,'CUSf', $this->name(), '' )   );
        $this->element->addData('parentId' , new SD_Hidden(null,'CUSf', $this->parent->getId())  );
        $this->element->addData('selectAction' , new SD_SelectAction('', array('Select')) );
	}

	function optionsDisplay(){
		if($this->val()){
			return  'display: none';
		}else{
			return '';
		}
	}

    /**
     * function showEmbededUpdateInput - this function works after function showInput, after 
     * have listed or added new values you can edit them or delete them without modify 
     * the original values and put the HTML to do it.
     * 
     * @return string
     */
	public function showEmbededUpdateInput()
	{
		if($this->val()){
			return $this->element->showEmbededUpdateInput(null,1);
		}
	}

	public function hiddenSelector(){
		if ($this->element){
			return '';
		}else{
			return 'hidden';
		}
	}

	public function showEmbededSearchInput()
	{
		if($this->val()){
			return $this->element->showEmbededSearchInput(null,1);
		}
	}

	
	public function doSearch(){
		$value = $this->val();
		$num = 0;
		if(is_array($value)){
			$arr = array();
			if($this->search() && $this->fetch()){
				foreach ($value as $val){
					$arr[] = array($this->name().$num, $this->getClass(), $val, $this->filterCriteria());
					$num += 1;
				}
			}
		} else {
			$arr= ($this->search() && $this->fetch())
			? array(array($this->name(), $this->getClass(), $this->val(), $this->filterCriteria()))
			: null;
		}

		return $arr;
	}

	public function filterCriteria($filterCriteria = null) {
		$glue = '_';
		$name = $this->name();
		$num = 0;
		$paste = '';
		$ret =array();
		$values = $this->val();
		if(is_array($values)){
			foreach ($values as $value) {
				if(property_exists($this->parent,$name.$num)){
					$paste = $name.$glue.$num;
					while(property_exists($this->parent,$paste)){
						$paste .= $glue;
					}
					$ret[]=$name.' == :'.$paste;
				}else{
					$ret[]=$name.' == :'.$name.$num;
				}
				$num += 1;
			}
			$criteria = implode(' OR ', $ret);
			return $criteria;
		}else{
			return parent::filterCriteria($filterCriteria);
		}
	}
	
    function makeSelection($id){
        /*@var parentElement SC_Element */
        $this->element->fillFromDSById($id);

        $return = array(
			'status' => true,
			'type' => 'commands',
			'data' => array(
                array(
                    'func' => 'changeValue',
                    'args' => array($this->element->getId())
                ),
                array(
                    'func' => 'changePreview',
                    //'args' => array($this->showEmbededUpdateInput(SC_Main::$GENERIC_TEMPLATES_PATH . $short_template, true).'')
                    'args' => array($this->showEmbededStrip())
					//'args' => array('Hola tu')
                ),
                array(
                    'func' => 'closeLightbox'
                ),
            )
        );

        header('Content-type: application/json');
        echo json_encode($return);

        
    }
}