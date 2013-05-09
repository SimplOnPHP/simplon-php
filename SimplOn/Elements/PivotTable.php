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
namespace SimplOn\Elements;

class PivotTable extends Element {
	public function construct($id = null, &$specialDataStorage = null) {
	    $this->id = new \SimplOn\Datas\NumericId();
	    $this->parentId = new \SimplOn\Datas\Integer('','S');
		$this->childId = new \SimplOn\Datas\Integer('','S');
		$this->childClass = new \SimplOn\Datas\String('','S');
	}
}