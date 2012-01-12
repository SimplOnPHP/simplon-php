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
				return str_replace(\DOF\Main::$REMOTE_ROOT, \DOF\Main::$LOCAL_ROOT, $fp);
			},
			$this->element->getJS($method)
		);
	}
	
	
	function showView($template = null)
	{
		//return $this->parent()->getClass();
		$id = $this->element()->{$this->element()->field_id()}();
		//if($id !== null) {
            if($template) {
                $template = \phpQuery::newDocument($template);
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
	}
	
	
	public function parent(&$parent=null)
	{
        if($parent) {
            $this->parent =$parent ;
        } else {
            return $this->parent;
        }	
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
			<a class="lightbox" href="'.$this->element->encodeURL(array(),'showSelect',array('','', $this->parent->templateFilePath('View', '', true) , $this->element->sid() )).'">List</a>
			<a class="lightbox" href="'.$this->element->encodeURL(array(),'showCreate').'">Add</a>
			<div>
                <div>
                    <a class="lightbox" href="'.$this->element->encodeURL(array(),'showUpdate',array('',$this->element->encodeURL(array(),'processUpdate',array($this->parent->templateFilePath('View', '', true) , $this->element->sid()))  )).'">Edit</a>
                    <a class="lightbox" href="" onClick="">X</a>
                </div>
                <div class="preview">'.$this->showView().'</div>
            </div>
            <input class="SimplOn input" name="'.$this->name().'" type="hidden" value="'.($fill?$this->val():'').'" />
		';
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
}