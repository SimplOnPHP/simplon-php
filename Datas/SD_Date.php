<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/


/**
 * Date data type
 * 
 * This is a date data type which allow you show a datepicker (jQuery UI) to select a
 * correct date to put in the input. 
 * 
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */
class SD_Date extends SD_String {
    /**
     *
     * @var string $dbFormat - format to database
     * @var string $viewFormat - format to see in the views
     * @var void $viewVal - save the date which will be in the views
     * @var string $validationDate - an alert to introduce a correct format 
     * @var string $isnotaDate - an alert in case if value received isn't numeric
     */
	var 
		$dbFormat = 'Y-m-d',
		$viewFormat = 'mm/dd/YY',
		$viewVal,
		$validationDate = 'Invalid date received!',
		$isnotNumeric = 'Is necessary introduce a numeric value';
			
	protected 
		$filterCriteria = 'name = :name';
       
		


	/**
	 * 
	 * function val - This function allows validate the date format and if it's true
	 * return the same date if it's not throw and exception.
	 * 
	 * @param string $val 
	 * @return string
	 * @throws SC_DataValidationException 
	 */	
	function val($val = null) {
            // if $val is defined and isn't null, start to verify the value
		if(isset($val) && $val) {
			if(!$this->fixedValue) {
				$val = trim($val);
							//if $val is empty and is required then throw an exception.
				if(!$val && $this->required) {
					throw new SC_DataValidationException ($this->validationDate);
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
			return $this->val;
		}
	}

	/**
	 * function getCSS get the stylesheet to show calendars
	 
	public function getCSS($method) {
		$array_css = parent::getCSS($method);
		$local_css = SE_CSS::getPath('01-jquery-ui.css');
		$array_css[] = $local_css;
		return $array_css;
	}*/

	/**
	 * function showView - This function shows the date selected in the input 
	 * to be displayed in the view .
	 * 
	 * @return string
	 
	function showView($template = null){
		return $this->viewVal;
	}*/	
	
	function viewVal(){
		if(!empty($this->viewVal))
			return $this->viewVal;
		else
			return $this->val;
	}

	/*
	public function showInput($fill = true) {
            return 
            ($this->label() ? '<label for="'.$this->htmlId().'">'.$this->label().': </label>' : '') .
            '<input id="'.$this->htmlId().'" class="'.$this->htmlClasses('date').'" name="'.$this->inputName().'" '.(($fill)?'value="'.$this->viewVal().'"':'').' type="text" />';
	}
	*/
}