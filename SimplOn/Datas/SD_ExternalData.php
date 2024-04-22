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
 * ComplexData is a Data made by combining other Datas 
 * belonging to the same "logic parent" (the same Element).
 * @example SE_Example_ComplexData.php 
 */
class SD_ExternalData extends SD_ComplexData {

	public function __construct($label,$element,$data,$flags=null,$searchOp=null){
		// $this->sources is an array with items to be used for complex data
		if(is_string($element)){
			$this->element = new $element();
		}else{
			$this->element = $element;
		}
		$this->data = $data;
		parent::__construct($label,$flags,null,$searchOp);
	}
    
    public function showView($template = NULL, $sources = NULL){
        /** @var SR_html $redender */
        $redender = $GLOBALS['redender'];
        
        if(!$template){ $template = $redender->getElementTemplate($this->parent, 'showView'); } 

        return $redender->renderData($this->element->{'O'.$this->data}(),'showView',$template,1);
        //return $this->element->{'O'.$this->data}()->showView($template, $sources);
    }
    
}

