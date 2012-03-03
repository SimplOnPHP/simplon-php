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
namespace DOF\Datas;

class Password extends String {

	protected 
		$encriptedFlag = False,
			
		
		$view = False,
		$search = False;
	
	public function showInput($fill) {
        $data_id = 'DOF_'.$this->instanceId();
		return 
            ($this->label() ? '<label for="'.$data_id.'">'.$this->label().': </label>' : '') .
            '<input id="'.$data_id.'" class="'.$this->htmlClasses('input').'" name="'.$this->inputName().'" '.(($fill)?'value="'.$this->val().'"':'').' type="password" />';
	}
	
	
	/**
	 * It's important to distinguish between encrypted (from DB) and unencrypted
	 * (from User form and some of calls to ->val($val) ) values.
	 * All the operations regarding altering the DB will check for encription 
	 * and make it before interacting with the DB.
	 * When reading from the DB, the encriptedFlag will be set to true, 
	 * other interactions will have to check and set the flag.
	 */
	
	
	public function doRead()
	{
		$this->encriptedFlag = true;
		
		return parent::doRead();
	}
	
	public function doCreate()
	{
		if(!$this->encriptedFlag){ $this->val(md5($this->val)); }
		
		return parent::doCreate();
	}
		
	public function doUpdate()
	{
		if(!$this->encriptedFlag){ $this->val(md5($this->val)); }
		
		return parrent::doUpdate();
	}

	public function doSearch()
	{
		if(!$this->encriptedFlag){ $this->val(md5($this->val)); }
		
		return parent::doSearch();	
	}	
	
	
	
	
	
	
}