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

class Html4 {
	static function table(array $contents, array $headers = array(), array $footers = array(), array $ths_matrix = array(), $caption = '') {
		
		$html = '<table>';
		foreach(array('headers' => 'thead', 'contents' => 'tbody', 'footers' => 'tfoot') as $dataVar => $tag) {
			$html .= '<'.$tag.'>';
			foreach($$dataVar as $row){
				$html .= '<tr>';	
				foreach($row as $cell){
					$html .= '<td>'.$cell.'</td>';
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