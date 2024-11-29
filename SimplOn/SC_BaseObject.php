<?php
/*
	Copyright © 2024 Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
	
	This file is part of “SimplOn PHP” a Data Oriented Aproach free software development framework: you can redistribute it and/or modify it under the terms of the MIT License.
*/


/**
 * Object to add Geters, Seters and other desired functionality to all SimplOn objects
 *
 * @author RSL
 */
class SC_BaseObject
{
	/**
	 * Returns the object's class
	 *
	 * @return string Class of the object
	 */
	public function getClass()
	{
		$class = explode('\\',get_class($this));
		return end($class);
	}
	
	
	/**
	 * Returns the object's class without the prefixes AE_, SC_, SE_, SD_, AD_, etc.
	 *
	 * @return string Class of the object without the prefixes AE_, SC_, SE_, SD_, AD_, etc.
	 */
	public function getClassWords($getPrefix = false)
	{
		$words = get_class($this);
		if(!$getPrefix){$words = substr($words,3);} 
		$words = preg_replace('/(?<!\ )[A-Z]/', ' $0', $words);
		return $words;
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
		$ret = array();
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
	public function hasAttribute($attribute) {
		return property_exists($this,$attribute);
	}

	function hasProperty(string $propName): bool {
		// Get the class name if an object is passed
		$className =  get_class($this);
		
		// Check if the class exists
		if (!class_exists($className)) {
			return false;
		}
		
		try {
			$reflection = new ReflectionClass($className);
			
			// Get all static properties
			$staticProps = $reflection->getStaticProperties();
			
			// Check if the property exists and is static
			return array_key_exists($propName, $staticProps);
		} catch (ReflectionException $e) {
			return false;
		}
	}
	
	/**
	 * Auto Makes the Setters and Getters
	 *
	 * @param $name
	 * @param $arguments
	 */
    public function __call($name, $arguments) {
    	//Get and Set
    	if($this->hasProperty($name)) {
        	if($arguments) {
        		get_class($this)::$$name = $arguments[0];
        		return $this;
			} else {
				return get_class($this)::$$name;
			}
        }elseif($this->hasAttribute($name)) {
        	if($arguments) {
        		$this->$name = $arguments[0];
        		return $this;
			} else {
				return $this->$name;
			}
        }else{
    	  	//Adds the ability to automaticaly print any 'str' method of a SimplOn's object by just calling it changing the 'str' by a 'prt'
        	$function = substr($name, 0, 1);
    		$stringAttrib = substr($name, 1);
    		if($function == 'P'){
    			echo call_user_func_array( array($this,$stringAttrib),$arguments );
				return;
    		}
        }
		
		throw new SC_Exception('The method: '.$name.' is not defined in the object: ' . get_class($this));
    }

	public function clear(string $name) {
		$this->$name = null;
	}

	/**
	 * Run time id of the object used to creat ids to link things in the interface like Labels and Inputs
	 */
	public function instanceId() {
		return 'objID-'.spl_object_id($this);
	}
	
	/**
	 * Class and instance id
	 */
	public function ObjectId() {
		return $this->getClass() . '-' . $this->instanceId();
	}
}