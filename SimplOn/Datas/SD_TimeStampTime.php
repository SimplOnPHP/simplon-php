<?php
/*
	Copyright © 2015 Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
	
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


///RSL 2022 #todo NO SIRVE AUN INCOMPLETO
class SD_TimeStampTime extends SD_Date {
    // protected
    //     $view = true,
    //     $create = true,
    //     $update = true,
    //     $search = false,
    //     $list = false,
    //     $required = false;
        
	var 
        $dbFormat = 'Y-m-d H:i:s',
        $viewFormat = 'm/d/Y H:i:s',
        $viewVal;

    function val($val = null) {
            // if $val is defined and isn't null, start to verify the value
        if(isset($val) && $val) {
			if(!$this->fixedValue) {
                $val = trim($val);
                //if $val is empty and is required then throw an exception.
                if(!$val && $this->required) {
                    throw new SC_DataValidationException ($this->validationDate);
                 }else {
                /**
                 * if val isn't empty and is required try to verify if $val is numeric then stores it into $dataObj
                 * but if the try block fail then throw an exception
                 */
                    try {
                        if(is_numeric($val)) {
                            $dateObj = new \DateTime();
                            $dateObj->setTimestamp($val);
                        } else {
                            $dateObj = new \DateTime($val);
                            // throw new SC_DataValidationException ($this->isnotNumeric);
                        }
                    } catch(\Exception $e) {
                        throw new SC_DataValidationException ($this->validationDate);
                    }
                }
                            // $this->val save the date with format for database
                $this->val = $dateObj->format($this->dbFormat);
                            // $this->viewVal save the the date with format to show in the view
                $this->viewVal = $dateObj->format($this->viewFormat);
            }
        } else {
            if($this->val){
                return $this->val;
            }else{
                $time_now = new \DateTime();
                return $time_now->format($this->dbFormat);
            }
        }
    }

    /*
    public function val($val = null){
        $time_now = new \DateTime();
        // $this->val save the date with format for database
		$this->val = $time_now->format($this->dbFormat);
        return $this->val;
    }
    */

	public function showInput($fill=true)
	{
		return '';
	}

	public function showView($template = NULL)
	{
		return substr($this->val(), -8);
	}


}