<?php
/**
 * Hidden data type 
 * 
 * Does not print a label and sends a hidden input.
 * 
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */
namespace SimplOn\Datas;

class Hidden extends Data
{
    /**
     *
     * @var boolean $view,$create,$update - these variables are 
     * flags to indicate if this input will be displayed in the different templates.
     * 
     * @var boolean $required - This variable indicates if the input will be required or not. 
     */
	protected
		$view = false,
		$create = false,
		$update = true,
		$required = false;
	/**
         * 
         * function showInput - This function prints the input with the
         * correct format (class, name) to be used in the forms.
         * 
         * @param boolean $fill
         * @return string
         */
	public function showInput($fill)
	{
		if($this->val())
		{
		  return '<input class="SimplOn input '. $this->getClass() .'" name="'. $this->name() .'" '.(($fill)?' value="'.$this->val() .'"':'').' type="hidden" />';
		} 
	}
		
	public function label() {}
}