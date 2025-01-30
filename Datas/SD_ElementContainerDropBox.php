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

class SD_ElementContainerDropBox extends SD_ElementContainer {

	function options()
	{

		if(empty($this->options)){
			$search = new SE_Search(array($this->element->getClass()));

			$search->getResults(array());//need to send an empty array to avoid fillFrom Request to be called and thus allways properly read all the options	
			$options = array();
			$options[' '] = '';

			foreach($search->searchResults() as $elementIndex => $element) {
				$options[$element->getId()] = $element->showEmbededStrip();	
			}
			$this->options = $options;
		}
		return $this->options;
	}

	public function val($val = null) {
		if($val === '' OR $val === ' '){
			$this->val = null;
		}elseif($val===null){
			return $this->val;
		}elseif(isset($val)){
			if(!$this->fixedValue) {
				$this->val = $val;
			}
		}
	}

	public function selected($test = null) {
		return ($test == $this->val);
	}
	
	public function viewVal() {
		$this->options();
		if(isset($this->options[$this->val]))
		{return $this->options[$this->val];}
	}


	public function showCreate() {
		if($this->renderOverride()=='showEmpty'){return '';}elseif($this->fixedValue()) {$input =  new SI_FixedValue($this->name(), $this->viewVal());
		}else{
			$input = new SI_Select($this->options(), $this->name(), '', $this->htmlId());
		}
		$inputBox = new (SC_Main::$RENDERER::$InputBox_type)($input, $this->label());
		return $inputBox;
	}
		
	public function showUpdate() {
		if($this->renderOverride()=='showEmpty'){return '';}elseif($this->fixedValue()) {$input =  new SI_FixedValue($this->name(), $this->viewVal());
		}else{
			$input = new SI_Select($this->options(), $this->name(), $this->val(), null, $this->label(), $this->required(), $this->ObjectId());	
		}
		$inputBox = new (SC_Main::$RENDERER::$InputBox_type)($input, $this->label());
		return $inputBox;
	}

	public function showDelete() {}

	public function showSearch() {
		if($this->renderOverride()=='showEmpty'){return '';}elseif($this->fixedValue()) {$input =  new SI_FixedValue($this->name(), $this->viewVal());
		}else{
			$input = new SI_Select($this->options(), $this->name(), $this->val(), null, $this->label(), null, $this->ObjectId());
		}
		$inputBox = new (SC_Main::$RENDERER::$InputBox_type)($input, $this->label());
		return $inputBox;
	}





}