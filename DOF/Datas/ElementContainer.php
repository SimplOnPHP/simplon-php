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

        
        }
    }
    
	
	function showView($template = null)
	{
		//return $this->parent()->getClass();
		$id = $this->element()->{$this->element()->field_id()}();
		//if($id !== null) {
            if($template) {
                $template = Main::loadDom($template);
                $template = $template['.SimplOn.'.$this->element()->getClassName().'.sid'.$this->element()->sid()].'';
            } else {
                $tempSid = $this->element()->sid();
                $this->element()->sid(1);
            }
			$dom = \phpQuery::newDocument($this->element()->showView($template));
            
            if(@$tempSid) {
                $this->element()->sid($tempSid);
                $this->sidFix($dom);
            }
			return $dom['.SimplOn.'.$this->element()->getClassName()].'';
		//} else {
		//	return '';
		//}
	}
	
	public function val($val = null) {
		if($val === '') {
			$class = $this->element->getClass();
			$this->element = new $class;
		} else	if($val !== null) {
			$this->element->fillFromDSById($val);
		} else {
			return @$this->element->{$this->element->field_id()}();
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
        /*
        session_start();
        if(empty($_SESSION['nestedElements'])){
            $_SESSION['nestedElements']=array();
        }
        
        $_SESSION['nestedElements'][$this->sid()]=$this->parent()->templateFilePath('View');
        */
        
        return  '
			<a class="SimplOn lightbox" href="'.$this->parent->encodeURL(array(),'callDataMethod',array($this->name(),'showSelect') ).'">List</a>
			<a class="SimplOn lightbox" href="'.$this->element->encodeURL(array(),'showCreate').'">Add</a>
            <div class="SimplOn preview">
                '.$this->showInputView().'
            </div>
            <input class="SimplOn input" name="'.$this->name().'" type="hidden" value="'.($fill?$this->val():'').'" />
		';
	}
    
	public function showInputView()
	{
        $nextStep = $this->parent->encodeURL(array($this->parent->getId()),'callDataMethod', array($this->name(), 'makeSelection'));
        return '
                <div class="SimplOn actions">
                    <a class="SimplOn lightbox" href="'.$this->element->encodeURL(array(),'showUpdate',array('',$this->element->encodeURL(array(),'processUpdate',array($nextStep))  )).'">Edit</a>
                    <a class="SimplOn delete" href="#">X</a>
                </div>
                <div class="SimplOn view">'.$this->element->showView($this->parent->templateFilePath('View'), true).'</div>
        ';
	}

    
  	public function showSelect()
	{
        
        $this->element->addOnTheFlyAttribute('parentClass' , new Hidden(null,'CUSf', $this->parent->getClassName(), '' )    );
        $this->element->addOnTheFlyAttribute('dataName' , new Hidden(null,'CUSf', $this->name(), '' )    );
        $this->element->addOnTheFlyAttribute('parentId' , new Hidden(null,'CUSf', $this->parent->getId(), '' )    );
        $this->element->addOnTheFlyAttribute('selectAction' , new SelectAction('', array('Select')) );


        // http://localhost/SimplON/sample_site/Fe         /2       |callDataMethod/"home    "/"makeSelection"
        // http://localhost/SimplON/sample_site/parentClass/parentId|callDataMethod/"dataName"/"makeSelection"
    
        
        return $this->element->obtainHtml(
                "showSearch", 
                $this->element->templateFilePath('Search'), 
                $this->parent->encodeURL(array(),'callDataMethod',array($this->name(), 'processSelect') )
        ) . $this->element->processSelect(null, 'multi');
	}   
    
    
    
    function sidFix(&$dom, &$base = 1) {
        $dom[$this->element->cssSelector('', $base)]->attr('class', $this->element->htmlClasses());
        foreach($dom['.SimplOn.Data.sid'.$base] as $node) {
            $domNode = pq($node);
            $data = explode(' ', $domNode->attr('class'));
            $data = $this->element->{'O'.$data[4]}();
            
            $domNode->attr('class',$data->htmlClasses());
            if( $data instanceof \DOF\Datas\ElementContainer ){
                $base++;
                $domNode = $data->sidFix($domNode, $base);
            }
        }
    }

    function processSelect(){
        
        
        //$this->element->fillFromRequest();
        return $this->element->processSelect();
    }


    function makeSelection($id){ 
        /*@var parentElement /DOF/Elements/Element */
        //$orig_sid = Main::$globalSID;
       
        $this->element->fillFromDSById($id);
        //$parentElement = new $parentClass();
        //Main::$globalSID = $orig_sid;
        
        
        //$template = $parentElement->templateFilePath('View');
        
        header('Content-type: application/json');
        echo json_encode(array(
			'status' => true,
			'type' => 'commands',
			'data' => array(
                array(
                    'func' => 'changeValue',
                    'args' => array($this->element->id())
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
        ));

        
    }
    
}