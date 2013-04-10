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
 * TimeSince data type
 * 
 * This is a date data type which allow you show a datepicker (jQuery UI) to select a
 * correct date to put in the input, This date must be earlier than today. 
 * 
 * @author Rubén Schaffer Levine, Luca Lauretta <http://simplonphp.org/> and Cristopher Mendoza <http://twitter.com/charlybanya>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */
class TimeSince extends String {
    /**
     *
     * @var string $dbFormat - format to database
     * @var string $viewFormat - format to see in the views
     * @var void $viewVal - save the date which will be in the views
     * @var string $validationDate - an alert to introduce a correct format 
     * @var string $isnotaDate - an alert in case if value received isn't numeric
     */
	var 
            $dbFormat = 'Y-m-d H:i:s',
            $viewFormat = 'm/d/Y H:i:s',
            $viewVal,
            $validationDate = 'Invalid date received!',
            $timePrecision = 'seconds',
            $timerFormat;
        
        public function __construct($label, $flags, $timePrecision = null, $timerFormat = null, $val = null, $filterCriteria = null) {
                $this->timerFormat = $timerFormat;
                if(isset($timePrecision)){
                    $this->timePrecision = $timePrecision;
                } else {
                    $this->timePrecision = 'seconds';
                }
            
            parent::__construct($label, $flags, $val, $filterCriteria);
        }
/**
 * 
 * function val - This function allows validate the date format and if it's true
 * return the same date if it's not throw and exception.
 * 
 * @param string $val 
 * @return string
 * @throws \SimplOn\DataValidationException
 */	
	function val($val = null) {
            // if $val is defined and isn't null, start to verify the value
		if(isset($val)) {
			$val = trim($val);
                        //if $val is empty and is required then throw an exception.
			if(!$val && $this->required) {
				throw new \SimplOn\DataValidationException($this->validationDate);
			}
                        /**
                         * if val isn't empty and is required try to verify if $val is numeric then stores it into $dataObj
                         * but if the try block fail then throw an exception
                         */
                        else {
				try {
                                    if(is_numeric($val)) {
						$dateObj = new \DateTime();
						$dateObj->setTimestamp($val);
					} else {
                                            $dateObj = new \DateTime($val);
                                           // throw new \SimplOn\DataValidationException($this->isnotNumeric);
					}
                                    if($this->timePrecision == 'hours' || $this->timePrecision == 'minutes' || $this->timePrecision == 'seconds'){
                                         $limitDate = new \DateTime("now");
                                     }else{
                                         $limitDate = new \DateTime("yesterday");
                                     }
                                    if($dateObj > $limitDate){
                                        throw new \SimplOn\DataValidationException();
                                    }
				} catch(\Exception $e) {
					throw new \SimplOn\DataValidationException($this->validationDate);
				}
			}
                        // $this->val save the date with format for database
			$this->val = $dateObj->format($this->dbFormat);
                        // $this->viewVal save the the date with format to show in the view
			$this->viewVal = $dateObj->format($this->viewFormat);
		} else {
			return $this->val;
		}
	}
        
        function orderFormatFlags($flags){
            $flagValues = array(
                    'y' => 0,
                    'm' => 1,
                    'w' => 2,
                    'd' => 3,
                    'h' => 4,
                    'i' => 5,
                    's' => 6
                );
            $flagsArray = array();
            
            if(isset($flags)){
                foreach (str_split($flags) as $flag){
                    $flagsArray[$flagValues[strtolower($flag)]] = $flag;
                }
                ksort($flagsArray);
                $flagsOrdered = implode("", $flagsArray );
                return $flagsOrdered;
            } else {
                $flagsOrdered = 'ymwdhis';
                return $flagsOrdered;
            }
            
        }
        
        
        /**
 * function showView - This function shows the date selected in the input 
 * to be displayed in the view .
 * 
 * @return string
 */	
	function showView(){
            return '<div id="'.$this->ObjectId().'"></div><script type="text/javascript">timeSinceTimer("'.$this->viewVal.'", "'.$this->orderFormatFlags($this->timerFormat).'", "'.$this->ObjectId().'")</script>';
	}
	
	public function showInput($fill) {
            return 
            ($this->label() ? '<label for="'.$this->htmlId().'">'.$this->label().': </label>' : '') .
            '<input id="'.$this->htmlId().'" class="'.$this->htmlClasses('timeSince').' '.$this->timePrecision.'" name="'.$this->inputName().'" '.(($fill)?'value="'.$this->viewVal.'"':'').' type="text" autocomplete="off"/>';
	}
}