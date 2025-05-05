<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

/**
 * Provides base functionality for all SimplOn classes.
 *
 * This class offers common utilities like:
 * - Retrieving class name information (full name, prefix, words).
 * - Introspecting methods and attributes.
 * - Automatic getter and setter generation via the magic __call method for both
 *   instance and static properties.
 * - Checking for the existence of attributes and properties.
 * - Clearing property values.
 * - Generating unique instance identifiers for UI linking.
 *
 * @version 1b.1.0
 * @package SimplOn\Core
 * @author RSL
 */
class SC_BaseObject
{
	/**
	 * Returns the object's class
	 *
	 * @return string The short class name of the object instance.
	 */
	public function getClass()
	{
		$class = explode('\\',get_class($this));
		return end($class);
	}
	
	
	/**
	 * Returns the object's class name as space-separated words.
	 * By default, it removes common SimplOn prefixes (AE_, SC_, SE_, SD_, AD_, etc.).
	 * For example, 'SC_MyExampleClass' becomes 'My Example Class'. Usefull to get human readable names.
	 *
	 * @param bool $getPrefix Optional. If true, the prefix is included in the output (e.g., 'SC_ My Example Class'). Defaults to false.
	 * @return string The class name formatted as space-separated words, potentially including the prefix.
	 */
	public function getClassWords($getPrefix = false)
	{
		$words = get_class($this);
		if(!$getPrefix){$words = substr($words,3);} 
		$words = preg_replace('/(?<!\ )[A-Z]/', ' $0', $words);
		return $words;
	}

	/**
	 * Returns the object's class name without the first two characters.
	 *
	 * @return string The class name with the first two characters removed.
	 */
	public function getClassPrefix()
	{
		return substr( get_class($this),2);
	}

	/**
	 * Returns an array containing the names of the object's methods
	 * and the keys of its attributes.
	 *
	 * @return string[] An array containing both method names and attribute keys.
	 */
	public function getMethods()
	{
		$class = get_class($this);
		return array_merge(get_class_methods($class) , $this->getAttributeKeys());
	}

	
	/**
	 * Returns the names (keys) of the object's attributes (properties).
	 * Optionally filters attributes to include only those whose value is an instance
	 * of a specific class.
	 *
	 * @param string|null $class Optional. The fully qualified class name to filter by. If null, all attribute keys are returned.
	 * @return string[] An array containing the names of the matching attributes.
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