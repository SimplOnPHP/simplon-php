<?php
namespace DOF;

class Config {
	const DEFAULT_INI = 'dof.ini';
	
	static
		$LOCAL_ROOT,
		$REMOTE_ROOT,

		$DOF_PATH,
		$GENERIC_TEMPLATES_PATH,

		$CREATE_LAYOUT_TEMPLATES,
		$OVERWRITE_LAYOUT_TEMPLATES,
		$USE_LAYOUT_TEMPLATES,

		$CREATE_FORM_TEMPLATES,
		$OVERWRITE_FORM_TEMPLATES,
		$USE_FORM_TEMPLATES,

		$DATA_STORAGE;
	
	/**
	 * Loads all the parameters specific to a website and loads needed classes.
	 *
	 * @param mixed	$ini	Can be either the path to a ini file or an array with configuration parameters.
	 * @return unknown_type
	 */
	function __construct($ini = null) {
		self::setup($ini);
	}
	
	static function setup($ini = null) {
		
		if(file_exists(DEFAULT_INI))
			self::fromArray(parse_ini_file(DEFAULT_INI));
		
		if(isset($ini)) {
			if(is_array($ini)) {
				self::fromArray($ini);
			} else if(is_string($ini) && file_exists($ini)) {
				self::fromArray(parse_ini_file($ini));
			}
		}
		
		spl_autoload_register(__NAMESPACE__ .'\Config::load_obj');
	}
	
	static function fromArray(array $ini) {
		foreach($ini as $const => $value)
			self::$$const = $value;
	}
	
	/**
	 * Includes a (class)file looking for it in the following order 1.- Site directory, 2.- Site template directory, 3.- DOF directory
	 */
	static function load_obj($classToLoad) {
		$classToLoad = end(explode('\\', $classToLoad));
		
		$test = array(
			self::$LOCAL_ROOT => array('classes', 'data', 'elements'),
			self::$GENERIC_TEMPLATES_PATH => array('classes', 'data', 'elements'),
			self::$DOF_PATH => array('classes', 'data', 'elements', 'datastorages'),
		);
		
		foreach($test as $base => &$types) {
			foreach($types as &$type) {
				$file_to_load = $base.$type.DIRECTORY_SEPARATOR.$classToLoad.'.php';
				if( file_exists($file_to_load) ) {
					require_once $file_to_load;
					return;
				}
			}
		}
		throw new \Exception("Can't find the file: $classToLoad.php");
	}
	
}