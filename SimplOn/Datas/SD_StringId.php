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
 * StringId data type
 * 
 * This is a string id data type which define a string as id.
 *  
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
*/
class SD_StringId extends SD_Id
{
    /**
     * @var boolean $autoIncrement - this variable define if this field will be
     * auto incremented or not. 
     * @var boolean $view,$create,$update, $list and $search- these variables are 
     * flags to indicate if this input will be displayed in the different templates.
     * @var boolean $required - required indicates if this data will be required.
     */
        public $autoIncrement = false;
    	protected $view = true;
		protected $create = true;
		protected $update = true;
		protected $list = true;
		protected $search = true;
		protected $required = true;
	
	/**
         * 
         * function checkUnique - verify if exits the parent id into the database, if
         * it's true return false in otherwise return true.
         * 
         * @return boolean
         */
	
	public function checkUnique(){
		$dataArray = $this->parent()->dataStorage()->readElement( $this->parent() );
		if(empty ($dataArray)){
			return false;
		}else{
			return true;
		}
	}
	
	public function showInput($fill=true) {
        $data_id = 'SimplOn_'.$this->instanceId();
		return 
            ($this->label() ? '<label for="'.$data_id.'">'.$this->label().': </label>' : '') .
            '<input id="'.$data_id.'" class="'.$this->htmlClasses().'" name="'.$this->inputName().'" '.(($fill)?'value="'.$this->val().'"':'').' type="text" />';
	}
}
