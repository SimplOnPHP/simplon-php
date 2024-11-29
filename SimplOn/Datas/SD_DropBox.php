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

class SD_DropBox extends SD_String {

    protected $options;

	function __construct($label = null, $options = [], $flags = null, $val = null, $filterCriteria = null)
    {
        $this->options = $options;
        parent::__construct($label, $flags, $val, $filterCriteria);
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
		if(isset($this->options[$this->val]))
		{return $this->options[$this->val];}
	}



}

