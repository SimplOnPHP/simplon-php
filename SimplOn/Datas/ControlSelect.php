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
use \SimplOn\Main, \SimplOn\Elements\JS, SimplOn\Elements\CSS;
/**
 * Control Select data type
 * 
 * This data creates a select html to be use in search forms.
 * 
 * @author Castillo García Maximino and López Santiago Daniel
 * @copyright (c) 2013, Castillo García Maximino and López Santiago Daniel
 * @category Data
 */
class ControlSelect extends ComplexData {
	
	protected
			$view = false,
			$search = true;
	               
	function val($sources = null){
		if(isset($sources)){
			$this->val = $sources;
			return $this->val; 
		}else{
			return $this->val;
		}
	}

	public function getJS($method) {
        $array_js = parent::getJS($method);
		$select_js1 = JS::getPath("11-ui.multiselect.js");
		$select_js2 = JS::getPath("12-ui-multiselect-es.js");
		$array_js[] = $select_js1;
		$array_js[] = $select_js2;
		return $array_js;
	}
    
	public function getCSS($method) {
		$array_css = parent::getCSS($method);
		$local_css = CSS::getPath('01-jquery-ui.css');
		$array_css[] = $local_css;
		return $array_css;
	}


	public function showSearch() {
		$datas = $this->sources;
		$select = '';
		$select = '<label for="'.$this->htmlId().'">'.$this->label().': </label>'.
				'<select id="'.$this->htmlId().'" class="'.$this->htmlClasses().'" name="'.$this->name().'[]'.'" multiple>';
		foreach ($datas as $data){
			if(	$data === 'sonMessage' || 
				$data === 'viewAction' || 
				$data === 'createAction' || 
				$data === 'deleteAction' || 
				$data === 'updateAction' || 
				$data === 'SimplOn_count' ||
				$data === 'SimplOn_group'
			){
				$select .= '';
			}else{
				$select .= '<option value="'.$data.'">'.$data.'</option>';
			}
		}
		$select .= '</select>';
		return $select;
	}
	
	public function addCount($sources=  array()){
		$counter = 0;
		foreach ($sources as $value) {
			$this->parent->addOnTheFlyAttribute('count'.$counter , new \SimplOn\Datas\Count('count('.$value.')',$value));
			$counter +=1;
		}
	}
}

        