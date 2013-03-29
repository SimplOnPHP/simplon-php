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
namespace SimplOn;

/**
 * Object to add Geters, Seters and other desired functionality to all SimplOn objects
 *
 * @author RSL
 */
class BaseObject
{
	/**
	 * Returns the object class
	 *
	 * @return string Class of the object
	 */
	public function getClass($fssafe = '') {
		return $fssafe
			? strtr(get_class($this), '\\', $fssafe)
			: get_class($this);
	}
	
	/**
	 * Returns the object class
	 *
	 * @return string Class of the object
	 */
	public function getClassName()
	{
            $a = explode('\\',get_class($this));
            return end($a);
	}
	
	/**
	 * Returns the Methods of the Object
	 *
	 * @return array[int]string
	 */
	public function getMethods()
	{
		$class = get_class($this);
		return array_merge(get_class_methods($class) , $this->getAttributeKeys());
	}

	
	/**
	 * Returns the Keys of the Object attributes.
	 *
	 * @param string $class Filter only attributes instance of $class.
	 *
	 * @return array(int => string)
	 */
	public function getAttributeKeys($class = null) {
		foreach($this as $key => $data) {
			if($class) {
				if($data instanceof $class)
					$ret[] = $key;
			} else {
				$ret[] = $key;
			}
		}
		return $ret;
	}
	
	/**
	 * Checks if a an object has a specific method
	 *
	 * @return boolean
	 */
	public function hasMethod($method) {
		return in_array($method, $this->getMethods());
	}

	
	
	/**
	 * Auto Makes the Setters and Getters
	 *
	 * @param $name
	 * @param $arguments
	 */
    public function __call($name, $arguments) {
    	//check($name.$arguments[0]);
    	
    	//Get and Set
    	if($this->hasMethod($name)) {
        	if($arguments) {
        		$this->$name = $arguments[0];
        		return $this;
			} else {
				return $this->$name;
			}
        } else {
    	  	//Adds the ability to automaticaly print any 'str' method of a SimplOn's object by just calling it changing the 'str' by a 'prt'
        	$function = substr($name, 0, 1);
    		$stringAttrib = substr($name, 1);
    		if($function == 'P'){
    			echo call_user_func_array( array($this,$stringAttrib),$arguments );
				return;
    		}
        }
		
		throw new Exception('The method: '.$name.' is not defined in the object: ' . get_class($this));
    }
	
	/*
	function __get($property) {
		echo 'get '.$property.'; ';
		// @todo: optimize this
		return $this->__call($property, array());
	}
	
	function __set($property, $value) {
		echo 'set '.$property.'='.$value .'; ';
		// @todo: optimize this
		return $this->$property($value);
	}
	*/
	
	public function instanceId() {
		return spl_object_hash($this);
	}
}