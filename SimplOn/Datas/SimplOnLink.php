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
use \SimplOn\Main;

class SimplOnLink extends ComplexData {
	
	public function __construct($label, array $sources, $class, array $construct_params = array(), $method = 'showView', array $method_params = array(), $flags=null, $searchOp=null){
		$this->class = $class;
		$this->construct_params = $construct_params;
		$this->method = $method;
		$this->method_params = $method_params;
		
		parent::__construct($label,$sources,$flags,null,$searchOp);
	}

	public function showView($sources = null){
		if(!isset($sources)) $sources = $this->sources;
		
		$href = Main::encodeURL($this->class, $this->construct_params, $this->method, $this->method_params);
		$content = vsprintf(array_shift($sources), $this->sourcesToValues($sources));
		
		return Main::$DEFAULT_RENDERER->link($content, $href);
	}

}