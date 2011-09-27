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
namespace DOF;
use \DOF\Exception;

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
		
		$DEFAULT_RENDERER,
		
		$DEFAULT_ELEMENT,
		$DEFAULT_METHOD = 'index',

		$CREATE_FROM_TEMPLATES,
		$OVERWRITE_FROM_TEMPLATES,
		$USE_FROM_TEMPLATES,
		
		$JS_FLAVOUR = 'jQuery',
		$JS_FLAVOUR_BASE,
	
		$CSS_FLAVOUR = 'jQuery',
		$CSS_FLAVOUR_BASE,
	
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
		if(!self::$CSS_FLAVOUR_BASE)
			self::$CSS_FLAVOUR_BASE = self::$LOCAL_ROOT . '/CSS/' . self::$CSS_FLAVOUR;
		
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
		$f_decode_param = function($p) {
			return json_decode(urldecode($p));
		};
		
		$server_request = $_SERVER['REDIRECT_URL'];
		$query_string = '';
		if(strpos($server_request, '|') !== false) {
			$query_string = substr($server_request, strpos($server_request, '|')+1);
			$server_request = substr($server_request, 0, strpos($server_request, '|'));
		}
		$virtual_path = array_values(array_diff(
			explode('/',$server_request), 
			explode('/',self::$REMOTE_ROOT)
		));
		
		self::$class = @array_shift($virtual_path) ?: self::$DEFAULT_ELEMENT;
		
		//var_dump(@array_values($virtual_path));
		self::$construct_params = array_map($f_decode_param, @array_values($virtual_path) ?: array());
		
		//var_dump($_SERVER);exit();
		
		$GET_virtual_path = array_values(explode('/',$query_string));
		self::$method = @array_shift($GET_virtual_path) ?: self::$DEFAULT_METHOD;
		
		//var_dump(@$GET_virtual_path);
		self::$method_params = array_map($f_decode_param, @$GET_virtual_path ?: array());
		
		//var_dump(self::$construct_params);
		//var_dump(self::$method_params);
	}
	
	static function encodeURL($class, array $construct_params, $method, array $method_params = array()) {
		$fencoder = function ($p) {
			return urlencode(json_encode($p));
		};
		
		$construct_params = array_map($fencoder, $construct_params);
		$method_params = array_map($fencoder, $method_params);
		
		return self::$REMOTE_ROOT . '/'
			. $class . (@$construct_params ? '/' . implode('/',$construct_params) : '/')
			. '|' 
			. $method . (@$method_params ? '/' . implode('/',$method_params) : '');
	}
	
	static function fromArray(array $ini) {
		foreach($ini as $const => $value)
			self::$$const = $value;
	}
	
	static function createFile($file_path, $data = null, $flags = null) {
		// @todo: implement with RecursiveDirectoryIterator
		
		$exploded_path = explode('/', $file_path);
		$file = array_pop($exploded_path);
		$current_path = '';
		foreach ($exploded_path as $dir) {
			$current_path.= $dir.'/';
			if($dir && !is_dir($current_path)) {
				if(!mkdir($current_path))
					throw new Exception('Cannot create the following directory: '. $current_path);
			}
		}
		
		if(isset($data))
			return file_put_contents($file_path, $data, $flags);
		
		return true;
	}
	
	/**
	 * Credits to Jennifer: http://www.php.net/manual/en/language.operators.type.php#103205
	 */
	public function instance_of($object, $class){
	    if(is_object($object)) return $object instanceof $class;
	    if(is_string($object)){
	        if(is_object($class)) $class=get_class($class);
	
	        if(class_exists($class)) return is_subclass_of($object, $class) || $object==$class;
	        if(interface_exists($class)) {
	            $reflect = new ReflectionClass($object);
	            return !$reflect->implementsInterface($class);
	        }
	
	    }
	    return false;
	}
	
	/**
	 * Includes a (class)file looking for it in the following order 1.- Site directory, 2.- Site template directory, 3.- DOF directory
	 */
	static function load_obj($classToLoad) {
		$pathExploded = explode('\\', $classToLoad);
		
		if(reset($pathExploded) == 'DOF') {
			$file_to_load = '../' . str_replace('\\', '/', $classToLoad) . '.php';
			// echo 'DOF -> '.$file_to_load.'<br>';
			require_once $file_to_load;
			return true;
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
						//echo $file_to_load.'<br>';
						require_once $file_to_load;
						return true;
					}
				}
			}
		}
		return false;
		//throw new \Exception("Can't find the file: $classToLoad.php");
	}
	/* */
}