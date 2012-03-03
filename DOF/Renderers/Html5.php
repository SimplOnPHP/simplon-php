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
namespace DOF\Renderers;

class Html5 {
    
	static function table_from_elements($elements, $columns = null) {
		$html = '<table class="SimplOn Elements-Table">';
		foreach($elements as $elementIndex => $element) {
			if(is_array($columns)){
				$html .= '<thead>';
				$html .= '<tr class="SimplOn">';
				foreach($columns as $column){
					$data = $element->{'O'.$column}();
					//@todo: this need to be improved in order to evetuly suport list Datas that are not common to all Elements
					$html .= '<th class="SimplOn">'.$data->label().'</th>';
				}
				$html .= '</tr>';	
				$html .= '</thead>';
			}else{
				$html .= '<tbody>';
				$html .= '<tr class="'.$element->htmlClasses().'">';	
			   foreach($element->dataAttributes() as $dataIndex => $dataName) {
					$data = $element->{'O'.$dataName}();
					if($data->list()) {
						$html .= '<td class="'.$data->htmlClasses().'">'.$data->label().'</td>';
					}
				}
				$html .= '</tr>';
				$html .= '</tbody>';
			}
		}
		$html.= '</table>';

		return $html;
	}
    
	static function table(array $contents, array $headers = array(), array $footers = array(), array $extra = array()) {
        $html = '<table class="SimplOn table '.@$extra['table_classes'].'">';
        
		foreach(array('headers' => 'thead', 'contents' => 'tbody', 'footers' => 'tfoot') as $dataVar => $tag) {
			$html .= '<'.$tag.'>';
                        $cell_tag = $tag == 'thead' ? 'th' : 'td';
			foreach($$dataVar as $ri => $row){
				$html .= '<tr class="'.@$extra['tr_classes'][$ri].'">';	
				foreach($row as $di => $cell){
					$html .= '<'.$cell_tag.' class="'.@$extra['td_classes'][$ri][$di].'">'.$cell.'</td>';
				}
				$html .= '</tr>';
			}
			$html .= '</'.$tag.'>';
		}
		$html.= '</table>';

		return $html;
	}
	
	static function button($content, $action, $name = null) {
		return '<button '.($name?'name="'.$name.'"':'').' onclick="'.$action.'">'.$content.'</button>';
	}
	
	static function link($content, $href, array $extra_attrs = array(), $auto_encode = true) {
		$extra = array();
		foreach($extra_attrs as $attr => $value) {
			if($auto_encode) $value = htmlentities($value, ENT_COMPAT, 'UTF-8');
			$extra[] = $attr.'="'.$value.'"';
		}
		if($auto_encode) {
			$href = htmlentities($href, ENT_COMPAT, 'UTF-8');
			//$content = htmlentities($content, ENT_COMPAT, 'UTF-8');
		}
		return '<a '.implode(' ',$extra).' href="'.$href.'">'.$content.'</a>';
	}

	
}
