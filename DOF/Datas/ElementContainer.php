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
class ElementContainer extends Data {
	protected 
		/**
		 * Logic parent
		 * @var DOF\Elements\Element
		 */
		$parent, 
            
		/**
		 * Encapsulated element
		 * @var DOF\Elements\Element
		 */
		$element;
	
	public function __construct( \DOF\Elements\Element $element, $label=null, $flags=null, $element_id=null) {
		
		$this->element($element);
        
		parent::__construct($label,$flags,$element_id);
        
	}
	
	public function getJS($method) {
		return array_map(
			function($fp) {
				return str_replace(Main::$REMOTE_ROOT, Main::$LOCAL_ROOT, $fp);
			},
			$this->element->getJS($method)
		);
	}
	
    
    
    function parent(&$parent = null){
        if(!$parent){
            return $this->parent;
        } else {
            $this->parent=$parent;
        
            $this->element->nestingLevel($parent->nestingLevel()+1);
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

        if(@$element) {
            $this->nestingLevelFix($dom);
        }
        
        return $dom[$this->element->cssSelector()].'';
	}
	
	public function val($val = null) {
		if($val === '') {
			$class = $this->element->getClass();
			$this->element = new $class;
		} else	if($val !== null) {
			$this->element->fillFromDSById($val);
		} else {
			return @$this->element->getId();
		}

        $this->element->addOnTheFlyAttribute('parentClass' , new Hidden(null,'CUSf', $this->parent->getClassName(), '' )    );
        $this->element->addOnTheFlyAttribute('dataName' , new Hidden(null,'CUSf', $this->name(), '' )    );
        $this->element->addOnTheFlyAttribute('parentId' , new Hidden(null,'CUSf', $this->parent->getId(), '' )    );

        $this->element->addOnTheFlyAttribute('selectAction' , new SelectAction('', array('Select')) );


        // http://localhost/SimplON/sample_site/Fe         /2       |callDataMethod/"home    "/"makeSelection"
        // http://localhost/SimplON/sample_site/parentClass/parentId|callDataMethod/"dataName"/"makeSelection"
    
	}
	
	

	
	function showInput($fill)
	{
        $nextStep = $this->parent->encodeURL(array($this->parent->getId()),'callDataMethod', array($this->name(), 'makeSelection'));
        
        return  '
            <span class="SimplOn label">'.$this->label().'</span>:
			<a class="SimplOn lightbox" href="'.$this->parent->encodeURL(array(),'callDataMethod',array($this->name(),'showSelect') ).'">List</a>
            <a class="SimplOn lightbox" href="'.$this->element->encodeURL(array(),'showCreate',array('',$this->element->encodeURL(array(),'processCreate',array($nextStep))  )).'">Add</a>
            <div class="SimplOn preview">
                '.$this->showInputView().'
            </div>
            <input class="SimplOn input" name="'.$this->name().'" type="hidden" value="'.($fill?$this->val():'').'" />
		';
	}
    
	public function showInputView()
	{
        if($this->element->getId()){
            $nextStep = $this->parent->encodeURL(array($this->parent->getId()),'callDataMethod', array($this->name(), 'makeSelection', array ($this->element->getId()) ));
            return '<div class="SimplOn actions">
                        <a class="SimplOn lightbox" href="'.$this->element->encodeURL(array(),'showUpdate',array('',$this->element->encodeURL(array(),'processUpdate',array($nextStep))  )).'">Edit</a>
                        <a class="SimplOn delete" href="#">X</a>
                    </div>
                    <div class="SimplOn view">'.$this->element->showView($this->parent->showView(), true).'</div>
            ';
        }else{
            return '';
        }
	}

    
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

    function makeSelection($id){ 
        /*@var parentElement /DOF/Elements/Element */
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