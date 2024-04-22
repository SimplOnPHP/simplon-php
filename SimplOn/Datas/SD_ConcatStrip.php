<?php
/**
 * Concat data type
 * 
 * This is a Concat data type which allow you print a text.
 * 
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */

/**
* Performs a concatenation of text, datas and items.
* 
* Concat data type
* 
* @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
* @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
* @category Data
*/
    
	
class SD_ConcatStrip extends SD_ComplexData {
 
	public function val($fill = null){
        /**
        * 
        * @var $glue array - This variable stores the sources except the first one
        * @var $sources array - This variable is an array with the elements to concat and the glue as the first value
        * @var $ret String - This variable stores the values ​​to concatenate.
        * 
        * 
        */	
        $sources=$this->sources;
	    $ret ='';
        $glue=array_shift($sources);

        /**
        * Check if there are methods with the same name as item $ source if true 
        * paste it returned by the method and if false just paste the item $ source.
        * 
        */
	    foreach($sources as $source){

            if($this->parent->hasMethod($source) ){
                if($this->parent->$source instanceof SD_Data){ 
                    $ret .= $this->parent->$source->showEmbededStrip().$glue;
                }else{
                    $ret .= $this->parent->$source().$glue;
                }
            }else{
                $ret .= $source.$glue;
            }
		}
        $ret = trim($ret,$glue);
		return  trim($ret);
	}

}