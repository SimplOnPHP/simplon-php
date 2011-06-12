<?php
namespace DOF;
		
spl_autoload_register( __NAMESPACE__ . '\\Main::load_obj');

class Main {
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
	
	static	
		$AUTOLOAD_DIRS = array(
			'.',
			'DataStorages',
			'Datas',
			'Elements',
			'Utilities',
		);
		
	static
		$class,
		$method,
		$params;
		
	
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
		
		if(file_exists(self::DEFAULT_INI))
			self::fromArray(parse_ini_file(self::DEFAULT_INI));
		
		if(isset($ini)) {
			if(is_array($ini)) {
				self::fromArray($ini);
			} else if(is_string($ini) && file_exists($ini)) {
				self::fromArray(parse_ini_file($ini));
			}
		}
		
		
		// Parses the URL
		$server_request = $_SERVER['REQUEST_URI'];
		if(strpos($server_request, '?') !== false)
			$server_request = substr($server_request, 0, strpos($server_request, '?'));
		$virtual_path = array_values(array_diff(
			explode('/',$server_request), 
			explode('/',self::$REMOTE_ROOT)
		));
		self::$class = @array_shift($virtual_path) ?: 'Home';
		self::$method =  @array_shift($virtual_path) ?: 'index';
		self::$params = array_values($virtual_path);
	}
	
	static function fromArray(array $ini) {
		foreach($ini as $const => $value)
			self::$$const = $value;
	}
	
	/**
	 * Includes a (class)file looking for it in the following order 1.- Site directory, 2.- Site template directory, 3.- DOF directory
	 */
	static function load_obj($classToLoad) {
		$pathExploded = explode('\\', $classToLoad);
		
		if(reset($pathExploded) == 'DOF') {
			$file_to_load = '../' . implode('/', $pathExploded) . '.php';
			include_once $file_to_load;
			return;
		} else {
			$classToLoad = end($pathExploded);
			
			$test = array(
				self::$LOCAL_ROOT,
				self::$GENERIC_TEMPLATES_PATH,
				self::$DOF_PATH,
			);
		
			foreach($test as $base) {
				foreach(self::$AUTOLOAD_DIRS as &$type) {
					$file_to_load = $base.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.$classToLoad.'.php';
					if( file_exists($file_to_load) ) {
						require_once $file_to_load;
						return;
					}
				}
			}
		}
		//throw new \Exception("Can't find the file: $classToLoad.php");
	}
	/**/
}