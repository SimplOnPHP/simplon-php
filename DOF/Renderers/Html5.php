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
		$tbody_html = '<tbody>';
		$thead_html = '';
		foreach($elements as $elementIndex => $element) {
			$thead_html = '<thead><tr class="SimplOn">';
			$tbody_html .= '<tr class="SimplOn tableRow '.$element->getClassName().'">';
			
			$columns = is_array($columns) ? $columns : $element->datasWith('list');
			foreach($columns as $column){
				$data = $element->{'O'.$column}();
				//@todo: this need to be improved in order to evetuly suport list Datas that are not common to all Elements
				$thead_html .= '<th class="SimplOn">'.$data->label().'</th>';
				$tbody_html.= '<td class="SimplOn tableData '.$data->getClassName().'">'.$data->showList().'</td>';
			}
			
			$tbody_html .= '</tr>';
			$thead_html .= '</tr></thead>';
		}
		$tbody_html .= '</tbody>';
		
		$html = '<table class="SimplOn Elements-Table">';
		$html.= @$thead_html;
		$html.= $tbody_html;
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
