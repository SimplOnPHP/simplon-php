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
namespace SimplOn\Datas;

use SimplOn\Main;
use SimplOn\Elements\Element;


/**
 * 
 * Encapsulates an Element so it acts as a Data. 
 * 
 * @author RSL
 */
class ElementContainer extends Data {
	protected 
		/**
		 * Logic parent
		 * @var Element
		 */
		$parent, 
            
		/**
		 * Encapsulated element
		 * @var Element
		 */
		$element,
		/*
		 * Column's name where values will be extracted
		 * @var String
		 */
		$column;

	public function __construct(Element $element, $label=null, $flags=null, $element_id=null, $column = null) {
		$this->column = $column;
		$this->element($element);
        
		parent::__construct($label,$flags,$element_id);
        
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
				return str_replace(Main::$REMOTE_ROOT, Main::$LOCAL_ROOT, $fp);
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
    
	/**
         * 
         * function showView - this function obtains the html content of the son
         * element for introduce just that part into the parent element in the 
         * differents templates and modifies the nesting level of the elements.
         * 
         * @param string $template
         * @return string
         */
	function showView($template = null)
	{
            
        if($template) {
            $template = Main::loadDom($template);
            $template = $this->element->showView($template[$this->cssSelector().' '.$this->element->cssSelector()],true);
        } else {
           // creates a dummy template
           $element = $this->element->getClass();
           $element = new $element($this->element->getId());
           $template = $element->showView(null, true);
        }
        $template=$template.'';
        $dom = \phpQuery::newDocument($template);
        
        if(@$element) {
            $this->nestingLevelFix($dom);
        }
        
        return $dom.'';
        
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
			$this->val = $val;
			return $this->val;
		}else if($val === '') {
			$class = $this->element->getClass();
			$this->element = new $class;
		} else	if($val !== null) {
			$this->element->fillFromDSById($val);
		} else {
			if(is_array($this->val)){
				return $this->val;
			} else return @$this->element->getId();
		}
        $this->element->addOnTheFlyAttribute('parentClass' , new Hidden(null,'CUSf', $this->parent->getClassName(), '' )    );
        $this->element->addOnTheFlyAttribute('dataName' , new Hidden(null,'CUSf', $this->name(), '' )    );
        $this->element->addOnTheFlyAttribute('parentId' , new Hidden(null,'CUSf', $this->parent->getId(), '' )    );
        $this->element->addOnTheFlyAttribute('selectAction' , new SelectAction('', array('Select')) );
	}
	/**
         * function input - this function displies the HTML to list and add new values into 
         * parent element. 
         * @param boolean $fill
         * @return string
         */
	function showInput($fill)
	{
        $nextStep = $this->encodeURL('makeSelection');
        $addHref = htmlentities(
			$this->element->encodeURL(
				array(),
				'showCreate',
				array(
					'',
					$this->element->encodeURL(
						array(),
						'processCreate',
						array($nextStep)
					)
				)
			)
		);
        return  '
            <span class="SimplOn label">'.$this->label().'</span>:
			<a class="SimplOn lightbox" href="'.htmlentities($this->encodeURL('showSelect')).'">List</a>
            <a class="SimplOn lightbox" href="'.$addHref.'">Add</a>
            <div class="SimplOn preview">
                '.$this->showInputView().'
            </div>
            <input class="SimplOn input" name="'.$this->name().'" type="hidden" value="'.($fill?$this->val():'').'" />
		';
	}
    /**
     * function showInputView - this function works after function showInput, after 
     * have listed or added new values you can edit them or delete them without modify 
     * the original values and put the HTML to do it.
     * 
     * @return string
     */
	public function showInputView()
	{
        $template=$this->parent->templateFilePath('View');
        if( !file_exists($template) ){
            $this->parent->showView();
        }
        
        if($this->element->getId()){
            $nextStep = $this->encodeURL('makeSelection', array($this->element->getId()));
            $href = htmlentities(
					$this->element->encodeURL(
							array(),
							'showUpdate',
							array(
								'',
								$this->element->encodeURL(
										array(),
										'processUpdate',
										array($nextStep)
								)
							)
					)
			);
			return '<div class="SimplOn actions">
                        <a class="SimplOn lightbox" href="'.$href.'">Edit</a>
                        <a class="SimplOn delete" href="#">X</a>
                    </div>
                    <div class="SimplOn view">'.$this->element->showView($template, true).'</div>
            ';
        }else{
            return '';
        }
	}
	
	function showSearch() {
		$class = $this->element;
		$element = new $class();
		$element->fillFromRequest();
		$parnt = $this->parent();
		$element->parent($parnt);
		$opt = $element->processCheckBox();
		$ret = '<br>'.$this->label().'<br>';
		foreach ($opt as $array) {
		    $idField = $array['SimplOn_field_id'];
		    $simplonClass = $array['SimplOn_class'];
		    $column = $array[$this->column];
		    $ret.='<div class="CheckBox"><input class="'
					.$this->getClass().
					'" name="'.$this->name().'[]'.
					'"value="'.$array[$idField].
					'"type="checkbox" '
					.((is_array($this->val))
					? ((in_array($array[$idField], $this->val)) ? 'Checked' : '')
					: '').
					'/><span class="name-column">'.$column.'</span></div>';
		}
		return  $ret;
	}
    
  	public function showSelect($class = null)
	{
        $element = $this->element->getClass();
        $element = new $element();
        $element->fillFromRequest();
        
        $element->addOnTheFlyAttribute('parentClass' , new Hidden(null,'CUSf', $this->parent->getClassName(), '' )    );
        $element->addOnTheFlyAttribute('dataName' , new Hidden(null,'CUSf', $this->name(), '' )    );
        $element->addOnTheFlyAttribute('parentId' , new Hidden(null,'CUSf', $this->parent->getId(), '' )    );
        $element->addOnTheFlyAttribute('selectAction' , new SelectAction('', array('Select')) );
        // http://localhost/SimplON/sample_site/Fe         /2       |callDataMethod/"home    "/"makeSelection"
        // http://localhost/SimplON/sample_site/parentClass/parentId|callDataMethod/"dataName"/"makeSelection"
   
        return $element->obtainHtml(
                "showSearch", 
                $element->templateFilePath('Search'), 
                $this->encodeURL('showSelect'),
                array('footer' => $element->processSelect())
        );
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
	
    /**
     * function nestingLevelFix - this function update the nesting level of the elements
     * every time that a new element is added.
     * 
     * @param type $dom
     */
    function nestingLevelFix(&$dom) {
        $startingNestingLevel = $this->parent->nestingLevel();
        foreach($dom['.SimplOn.Element, .SimplOn.Data'] as $node) {
            $domNode = pq($node);
            $classes = explode(' ', $domNode->attr('class'));
            if(substr($classes[2], 0, 4) == 'SNL-') {
                $nestingLevel = substr($classes[2], 4) + $startingNestingLevel;
                $classes[2] = 'SNL-' . $nestingLevel;
                $domNode->attr('class', implode(' ', $classes));
            }
        }
    }
/**
 * function makeSelection - this function pass the arguments to javascript file to 
 * display the light box.
 * @param type $id
 */
    function makeSelection($id){ 
        /*@var parentElement /SimplOn/Elements/Element */
        //$orig_sid = Main::$globalSID;
       
        $this->element->fillFromDSById($id);
        //$parentElement = new $parentClass();
        //Main::$globalSID = $orig_sid;
        //$template = $parentElement->templateFilePath('View');
        
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
                    //'args' => array($this->showInputView(Main::$GENERIC_TEMPLATES_PATH . $short_template, true).'')
                    'args' => array($this->showInputView())
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