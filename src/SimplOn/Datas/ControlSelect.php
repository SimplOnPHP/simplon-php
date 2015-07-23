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
	/**
	 * val
	 * 
	 * this function verifies if $sources is defined and isn't null
	 * if true saves it in the variable $sources and returns if not returns null.
	 * 
	 * @param string $sources
	 * @return string
	 */
	function val($sources = null) {
		if (isset($sources)) {
			$this->val = $sources;
			return $this->val; 
		} else {
			return $this->val;
		}
	}

	/**
	 * getJS
	 * 
	 * get the javascript file for the correct operation of the selection box.
	 * 
	 * @param type $method
	 * @return array
	 */
	public function getJS($method) {
        $array_js = parent::getJS($method);
		$select_js1 = JS::getPath("11-ui.multiselect.js");
		$select_js2 = JS::getPath("12-ui-multiselect-es.js");
		$array_js[] = $select_js1;
		$array_js[] = $select_js2;
		return $array_js;
	}

	/**
	 * getCSS
	 * 
	 * get the stylesheet to show selection box.
	 * 
	 * @param type $method
	 * @return type
	 */
	public function getCSS($method) {
		$array_css = parent::getCSS($method);
		$local_css = CSS::getPath('01-jquery-ui.css');
		$array_css[] = $local_css;
		return $array_css;
	}

	/**
	 * showSearch
	 * 
	 * Displays the selection box in the search view all item data.
	 * 
	 * @return string
	 */
	public function showSearch() {
		$datas = $this->sources;
		$select = '';
		$select = '<label for="'.$this->htmlId().'">'.$this->label().': </label>'.
				  '<select id="'.$this->htmlId().'" class="'.$this->htmlClasses().'" name="'.$this->name().'[]'.'" multiple>';
		foreach ($datas as $label => $data){
			$select .= '<option value="'.$data.'"'.((is_array($this->val))
				? ((in_array($data, $this->val)) ? 'selected' : '')
				: '').'>'.$label.'</option>';
		}
		$select .= '</select>';
		return $select;
	}

	/**
	 * addCount
	 * 
	 * Add a data type count into the element.
	 * 
	 * @param array $sources
	 */
	public function addCount($sources=  array()) {
		$counter = 0;
		foreach ($sources as $value) {
			$this->parent->addOnTheFlyAttribute('count'.$counter , new \SimplOn\Datas\Count('count('.$value.')',$value));
			$counter +=1;
		}
	}

	/**
	 * addSum
	 * 
	 * Add a data type sum into the element.
	 * 
	 * @param array $sources
	 */
	public function addSum($sources = array()) {
		$counter = 0;
		foreach ($sources as $value) {
			$this->parent->addOnTheFlyAttribute('sum'.$counter , new \SimplOn\Datas\Sum('total('.$value.')',$value));
			$counter +=1;
		}
	}	
}

        
