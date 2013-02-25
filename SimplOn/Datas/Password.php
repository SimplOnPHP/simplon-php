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
/**
 * Password data type
 * 
 * This is a password data type which allow you create an input to introduce a password 
 * and other input to confirm it. 
 * 
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */
class Password extends String {
    /**
     *
     * @var boolean $encripttedFlag - this variable indicates if the value introduced 
     * will be encrypted or is encrupted or not.
     * @var string $valudationCurrent, $validationMatch - these variables are 
     * messages to be use in the exceptions
     * @var boolean $view, $list, $required, $search - these variables are flags 
     * to indicate if this input will be displayed in the different templates
     */
protected 
	$encriptedFlag = False,
	$valudationCurrent = 'The current password is incorrect',
	$validationMatch = "The new password and the validation doesn't matct",	
	$view = False,
	$list = False,
	$required = True,
	$search = False;
	
	/**
         * 
         * function val - This function verifies if the passwords introduced are the same
         * and if it's not throw an exception and if it's true store the password into the
         * database.
         * 
         * @param string $val
         * @return string
         * @throws \SimplOn\DataValidationException
         */
	
	function val($val = null) {
		// if $val is defined and isn't null, start to verify the value
		if(isset($val)) {
                    //if $val is an string store $val into the $this->val
			if(is_string($val)){
				$this->val=$val;
			}
                        //if $val is an array, checks if the passwords introduced are the same
                        else if(is_array($val)){
				//NOTE: Validation if 'current' is requered must be done in parrent according to the sitiation (Update, Create, etc)
				if((trim(@$val['current'])) && ($this->readFromDB() != md5($val['current']))) {
					throw new \SimplOn\DataValidationException($this->valudationCurrent);
				}
				//if(!trim($val['new'])){throw new \SimplOn\DataValidationException($this->validationRequired); return;}
				//if the passwords are different throw an exception
                                if($val['new']!=$val['confirm']){
					throw new \SimplOn\DataValidationException($this->validationMatch);
				}
				//if the new password doesn't have spaces stores $val into $this->val.
				if(trim($val['new'])){
					$this->val=$val['new'];
					$this->encriptedFlag = False;
				}
			}
			
		}
                //if $val is null or undefined return $this->val
                else {
			return $this->val;
		}
	}
	/**
         * 
         * function readFromDB - this function return a element from database to be read
         * without modify the other element's database that have been introduced
         * 
         * @return an element array
         */
	public function readFromDB(){
		$dataArray = $this->parent()->dataStorage()->readElement( $this->parent() );
		return $dataArray[$this->name];
	}
	
	public function showInput($fill=false) {
		if($this->encriptedFlag){ 
			$fill=false;
		}
        $data_id = 'SimplOn_'.$this->instanceId();
		return 
            ($this->label() ? '<label for="'.$data_id.'">'.$this->label().': </label>' : '') .
            '<input id="'.$data_id.'" name="'.$this->inputName().'" '.(($fill)?'value="'.$this->val().'"':'').' type="password" />';
	}
	
	/**
         * 
         * function showUpdate - this function display the inputs to introduce 
         * the current password, the new password and the inpunt to confirm the 
         * new password to be update.
         * 
         * @return string
         */
	function showUpdate(){
		$name=$this->inputName();
		$Label=$this->label();
		$ret = '';
		
		$this->label($Label.' (current)');
		$this->inputName($name.'[current]');
		$ret .= $this->showInput();
		
		$this->label($Label.' (new)');
		$this->inputName($name.'[new]');
		$ret .= $this->showInput();
		
		$this->label($Label.' (confirm)');
		$this->inputName($name.'[confirm]');
		$ret .= $this->showInput();
		
		$this->label($Label);
		$this->inputName($name);
		
		return $ret;
		
	}	
        /**
         * 
         * function showUpdate - this function display the inputs to introduce 
         * the new password and the inpunt to confirm it.
         * @return type
         */
	function showCreate(){
		$name=$this->inputName();
		$Label=$this->label();
		$ret = '';
		
		//$this->label($Label.' (current)');
		$this->inputName($name.'[new]');
		$ret .= $this->showInput();
		
		$this->label($Label.' (confirm)');
		$this->inputName($name.'[confirm]');
		$ret .= $this->showInput();
		
		$this->label($Label);
		$this->inputName($name);
		
		return $ret;
		
	}	

	
	/**
	 * It's important to distinguish between encrypted (from DB) and unencrypted
	 * (from User form and some of calls to ->val($val) ) values.
	 * All the operations regarding altering the DB will check for encription 
	 * and make it before interacting with the DB.
	 * When reading from the DB, the encriptedFlag will be set to true, 
	 * other interactions will have to check and set the flag.
	 */
	
	public function doRead() {
		$this->encriptedFlag = true;
		
		return parent::doRead();
	}
	
	public function doCreate() {
		if(!$this->encriptedFlag){ $this->val(md5($this->val)); }
		
		return parent::doCreate();
	}
		
	public function doUpdate() {
			if(!$this->encriptedFlag){ $this->val(md5($this->val)); }
			return parent::doUpdate();

	}

	public function doSearch() {
		if(!$this->encriptedFlag){ $this->val(md5($this->val)); }
		
		return parent::doSearch();	
	}	
	
}
