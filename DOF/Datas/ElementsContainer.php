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
namespace DOF\Datas;
use \DOF\Main;


/**
 * 
 * Encapsulates an Element so it acts as a Data. 
 * 
 * @author RSL
 */
class ElementsContainer extends Data {
	protected 
		$fetch = false,
        
		/**
		 * Logic parent
		 * @var DOF\Elements\Element
		 */
		$parent, 
            
		/**
		 * List of Elements
		 * @var array of DOF\Elements\Element
		 */
		$elements = array(),
            
        
        $allowedClassesInstances = array();
	
	public function __construct( array $allowedClassesInstances, $label=null, $flags=null, $element_id=null) {
		
        
            
        if( is_string($allowedClassesInstances[0]) ){
            foreach($allowedClassesInstances as $class){
                $this->allowedClassesInstances[$class] = new $class;
            }  
        }else if( $allowedClassesInstances[0] instanceof \DOF\Elements\Element ){
            foreach($allowedClassesInstances as $classInstance){
                $this->allowedClassesInstances[$classInstance->getClass()] = $classInstance;
            }
        }else{
             // error elements must be an array of valid classes names or Elements
        }


        
		parent::__construct($label,$flags,$element_id);
        
	}
	
	public function getJS($method) {
        $a_js = array();
        foreach($this->allowedClassesInstances as $classInstance)
            $a_js = array_merge($classInstance->getJS($method), $a_js);
            
		return array_map(
			function($fp) {
				return str_replace(Main::$REMOTE_ROOT, Main::$LOCAL_ROOT, $fp);
			},
            $a_js
		);
	}
	
    
    
    function parent(&$parent = null){
        if(!$parent){
            return $this->parent;
        } else {
            $this->parent=$parent;
        
            foreach($this->elements as $element){
                $element->nestingLevel($parent->nestingLevel()+1);
            }
        
            foreach($this->allowedClassesInstances as $classInstance){
                $classInstance->nestingLevel($parent->nestingLevel()+1);
            }
        }
    }
    
	
	function showView($template = null)
	{
        if($template) {
            $template = Main::loadDom($template);
            $template = $this->element->showView($template[$this->element->cssSelector()]);
        } else {
           // creates a dummy template
           $element = $this->element->getClass();
           $element = new $element($this->element->getId());
           $template = $element->showView(null, true);
        }
        $dom = \phpQuery::newDocument($template);

        if($element) {
            $this->nestingLevelFix($dom);
        }
        
        return $dom[$this->element->cssSelector()].'';
	}
	
	public function val($val = null) {
        /*
		if($val === '') {
			$class = $this->element->getClass();
			$this->element = new $class;
		} else	if($val !== null) {
			$this->element->fillFromDSById($val);
		} else {
			return $this->element->getId();
		}

        $this->element->addOnTheFlyAttribute('parentClass' , new Hidden(null,'CUSf', $this->parent->getClassName(), '' )    );
        $this->element->addOnTheFlyAttribute('dataName' , new Hidden(null,'CUSf', $this->name(), '' )    );
        $this->element->addOnTheFlyAttribute('parentId' , new Hidden(null,'CUSf', $this->parent->getId(), '' )    );

        $this->element->addOnTheFlyAttribute('selectAction' , new SelectAction('', array('Select')) );


        // http://localhost/SimplON/sample_site/Fe         /2       |callDataMethod/"home    "/"makeSelection"
        // http://localhost/SimplON/sample_site/parentClass/parentId|callDataMethod/"dataName"/"makeSelection"
         * 
         */
    
	}
	
	

	
	function showInput($fill)
	{
        
        $ret =  '
            <span class="SimplOn label">'.$this->label().'</span>:
			<a class="SimplOn lightbox" href="'.$this->parent->encodeURL(array(),'callDataMethod',array($this->name(),'showSelect') ).'">List</a>
            Add:     
            ';
                
        foreach($this->allowedClassesInstances as $classInstance){
            $nextStep = $this->parent->encodeURL(array($this->parent->getId()),'callDataMethod', array($this->name(), 'makeSelection', array( '', $classInstance->getClass() )));
            $ret.='<a class="SimplOn lightbox" href="'.$classInstance->encodeURL(array(),'showCreate',array('',$classInstance->encodeURL(array(),'processCreate',array($nextStep))  )).'">'.$classInstance->getClassName().'</a> ';
        }
        
        $elementsInputViews = array();
        foreach($this->elements as $element) {
            $elementsInputViews[] = $this->showInputView($element);
        }
        $ret .=  '
            <div class="SimplOn elements-box">
                '.implode("\n", $elementsInputViews).'
            </div>
		';
        
        return $ret;
	}
    
	public function showInputView($element)
	{
        if($element->getId()){
            $nextStep = $this->parent->encodeURL(array($this->parent->getId()),'callDataMethod', array($this->name(), 'makeSelection', array ($element->getId()) ));
            return '
                    <div class="SimplOn element-box">
                        <div class="SimplOn actions">
                            <a class="SimplOn lightbox" href="'.$element->encodeURL(array(),'showUpdate',array('',$element->encodeURL(array(),'processUpdate',array($nextStep))  )).'">Edit</a>
                            <a class="SimplOn delete" href="#">X</a>
                        </div>
                        <div class="SimplOn view">'.$element->showView($this->parent->showView(), true).'</div>
                        <input class="SimplOn input" name="'.$this->name().'[]" type="hidden" value="'.htmlentities($element->getId().'|'.$element->getClass()).'" />
                    </div>
            ';
        }else{
            return '';
        }
	}

    
    
    /// use a search element and add the onthefly params to the search element
  	public function showSelect()
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
                $this->parent->encodeURL(array(),'callDataMethod',array($this->name(), 'showSelect') ),
                array('footer' => $element->processSelect())
        );
	}
    
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

    function makeSelection($id, $class){ 
        $element = new $class($id);
        
        header('Content-type: application/json');
        echo json_encode(array(
			'status' => true,
			'type' => 'commands',
			'data' => array(
                array(
                    'func' => 'addContainedElement',
                    'args' => array($this->showInputView($element))
                ),
                array(
                    'func' => 'closeLightbox'
                ),
            )
        ));

        
    }
    
}