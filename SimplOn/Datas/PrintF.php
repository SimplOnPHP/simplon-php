<?php
/**
 * PrintF data type
 * 
 * This is a PrintF data type which allow you print a text with format.
 * 
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */
namespace SimplOn\Datas;

class PrintF extends ComplexData {
	/**
         * 
         * Function val - The function checks if the value is null,if it's returns showView.
         *  
         */	
    public function val($val=null){
        if(!isset($val)){
            return $this->showView();
        }
        
    }
        /**
         * 
         * Function showView - This function overwrite the original showView 
         * function to show an output with format.
         * 
         */    
	public function showView($fill = null){
            return vsprintf(array_shift($this->sources), $this->sources);
	}
}
