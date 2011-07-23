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
		$MASTER_TEMPLATE,

		$CREATE_LAYOUT_TEMPLATES,
		$OVERWRITE_LAYOUT_TEMPLATES,
		$USE_LAYOUT_TEMPLATES,

		$CREATE_FROM_TEMPLATES,
		$OVERWRITE_FROM_TEMPLATES,
		$USE_FROM_TEMPLATES,
		
		$JS_FLAVOUR = 'jQuery',
		$JS_FLAVOUR_BASE,
	
		$DEV_MODE = false,

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
		$construct_params,
		$method_params;
	
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
		
		if(!self::$JS_FLAVOUR_BASE)
			self::$JS_FLAVOUR_BASE = self::$LOCAL_ROOT . '/JS/' . self::$JS_FLAVOUR;
		
		include(self::$DOF_PATH.'/Utilities/check.php');
		
		self::decodeURL();
	}
	
	static function decodeURL() {
			
		/*
		$basePath = substr($_SERVER['PHP_SELF'], 0, -9);
		$parts = explode('?',$_SERVER['REQUEST_URI']);
		
		$construct_params = explode('/', substr($parts[0], strlen($basePath)) );
		$class = array_shift($construct_params);
		
		$method_params = explode('/',$parts[1]);
		$method = array_shift($method_params);
		*/		
		
		// Parses the URL
		$server_request = $_SERVER['REQUEST_URI'];
		if(strpos($server_request, '?') !== false)
			$server_request = substr($server_request, 0, strpos($server_request, '?'));
		$virtual_path = array_values(array_diff(
			explode('/',$server_request), 
			explode('/',self::$REMOTE_ROOT)
		));
		self::$class = @array_shift($virtual_path) ?: 'Home';
		self::$construct_params = @array_values($virtual_path) ?: array();
		
		//var_dump($_SERVER);exit();
		
		$GET_virtual_path = array_values(explode('/',@$_SERVER['QUERY_STRING']));
		self::$method = @array_shift($GET_virtual_path) ?: 'index';
		self::$method_params = @$GET_virtual_path ?: array();
	}
	
	static function encodeURL($class, array $construct_params, $method, array $method_params = array()) {
		return self::$REMOTE_ROOT . '/'
			. $class . (@$construct_params ? '/' . implode('/',$construct_params) : '') 
			. '?' 
			. $method . (@$method_params ? '/' . implode('/',$method_params) : '');
	}
	
	static function fromArray(array $ini) {
		foreach($ini as $const => $value)
			self::$$const = $value;
	}
	
	static function createFile($file_path) {
		// @todo: implement
		return true;
	}
	
	/**
	 * Includes a (class)file looking for it in the following order 1.- Site directory, 2.- Site template directory, 3.- DOF directory
	 */
	static function load_obj($classToLoad) {
		$pathExploded = explode('\\', $classToLoad);
		
		if(reset($pathExploded) == 'DOF') {
			$file_to_load = '../' . str_replace('\\', '/', $classToLoad) . '.php';
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