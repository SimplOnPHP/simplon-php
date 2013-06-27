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
use \SimplOn\Exception;

/*! \mainpage SimplOn PHP
 *
 * \section intro_sec What is SimplOn?
 *
 * SimplOn is a web framework based on the concept of embedding meta-data 
 * into PHP objects to automate CRUD tasks and rendering them 
 * (ie. in HTML format).
 * 
 * \section core_obj_sec SimplOn uses two core objects
 *
 * \subsection datas_subsec Datas
 * 
 * Replaces regular object's attributes, contains informations 
 * about value's type and where to display it.
 *  
 * \subsection elements_subsec Elements
 * 
 * Replaces regular objects, using Datas as attributes and offers 
 * a standard interface to Data Storages (MySQL, MongoDB, ...) 
 * and Renderers (HTML, XML, JSON, ...).
 * 
 * \see http://tinyurl.com/SimplON-chart
 * 
 * \section http_api Simple HTTP API
 * 
 * SimplOn uses a HTTP API simple to read and write.
 * It is structured in the following way:
 * \code
 * /Foo/cp1/cp2/cpN!Moo/mp1/mp2/mpN
 * \endcode
 * Where \c cp are construct parameters for the element \c Foo, 
 * while \c mp are parameters for the element's method \c Moo.
 * 
 * Since Datas have methods too, the way to call those methods is:
 * \code
 * /Foo/cp1/cp2/cpN!Doo!Moo/mp1/mp2/mpN
 * \endcode
 * Where \c Doo is a \c Foo's Data and \c Moo is \c Doo's method.
 * 
 * To create or update an Element, a HTTP POST must be sent
 * containing an array with keys corresponding to Element's 
 * Data names and their corresponding values.
 */

spl_autoload_register( __NAMESPACE__ . '\\Main::load_obj');

class Main {
	const DEFAULT_INI = 'dof.ini';
	
	static
		$LOCAL_ROOT,
		$REMOTE_ROOT,

		$SimplOn_PATH,
		$GENERIC_TEMPLATES_PATH,
		$MASTER_TEMPLATE,

		$CREATE_LAYOUT_TEMPLATES,
		$OVERWRITE_LAYOUT_TEMPLATES,
		$USE_LAYOUT_TEMPLATES,
		
		$DEFAULT_RENDERER,
		
		$DEFAULT_ELEMENT,
		$DEFAULT_METHOD = 'index',
		$URL_METHOD_SEPARATOR = '!',

		$CREATE_FROM_TEMPLATES,
		$OVERWRITE_FROM_TEMPLATES,
		$USE_FROM_TEMPLATES,
		
		$JS_FLAVOUR = 'jQuery',
		$CSS_FLAVOUR = 'jQuery',
			
		$PERMISSIONS = false,
                $DEFAULT_PERMISSIONS,
	
		$DEV_MODE = false,

		$DATA_STORAGE,
			
		$QUICK_DELETE = false,
        
		$LIMIT_ELEMENTS,
        //super array to alter classes atributes on the fly must be in the format "class" -> array("data1name"=>$data1)
        $onTheFlyAttributes = array();
	
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
		$dataName,
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
	
	
	static function run($ini = null) {
                if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
		self::setup($ini);
		/**		echo '<br>self::$class ';var_dump(self::$class);
		echo '<br>self::$construct_params ';var_dump(self::$construct_params);
		echo '<br>self::$dataName ';var_dump(self::$dataName);
		echo '<br>self::$method ';var_dump(self::$method);
		echo '<br>self::$method_params ';var_dump(self::$method_params);
		echo '<br>$_SERVER: ';var_dump($_SERVER,true);
		return;
		/**/
		
		if(class_exists(self::$class) || class_exists('\\SimplOn\\Elements\\'.self::$class)) {
            $cp = self::$construct_params;
			$rc = new \ReflectionClass(class_exists(self::$class) ? self::$class : '\\SimplOn\\Elements\\'.self::$class);
			if( 
				isset(self::$method)
				&&
				($obj = $rc->newInstanceArgs(self::$construct_params))
				&&
				($obj instanceof Elements\Element)
			){
                                if(self::$PERMISSIONS && (self::$class !='JS' && self::$class!='CSS')   ){
					if(!@$_SESSION['simplonUser'] && !(self::$class == self::$PERMISSIONS && self::$method =='processValidation')  ){
						//ask for credentials
						$class = '\\'.self::$PERMISSIONS;
						$validator = new $class(); //$user is the default validator class
						$_SESSION['url']=$_SERVER['REQUEST_URI'];
						echo $validator->showValidation();
					}else{
						if (isset($_SERVER['HTTP_REFERER'])){ $_SESSION['url']=$_SERVER['HTTP_REFERER']; }
								//Validate user's permissions
						if( $obj->allow(@$_SESSION['simplonUser'],self::$method) ) {
							if(self::$dataName) {
								$obj = $obj->{self::$dataName};
							}
							echo call_user_func_array(array($obj, self::$method), self::$method_params);
						}else{
							//header('HTTP/1.1 403 Access forbidden');
							//header('SimplOn: You don\'t have permissions to see this page.');
                                                        echo '<h1>Access forbidden</h1>';
							return;
						}
					}
				}else{			
                                    if(self::$dataName) {
                                            $obj = $obj->{self::$dataName};
					}
                                    echo call_user_func_array(array($obj, self::$method), self::$method_params);
				}
			} else {
				header('HTTP/1.1 403 Access forbidden');
				header('SimplOn: '.self::$class.' is not an Element class.');
				return;
			}
		} else {
			header('HTTP/1.1 404 File not found');
			header('SimplOn: '.self::$class.' is not a valid class name.');
			return;
		}
	}

    
	static function addOnTheFlyAttribute($class,$attributeName,$attribute) {
		self::$onTheFlyAttributes[$class][$attributeName]=$attribute;
	}
    
    static function getOnTheFlyAttributes($class) {
		return @self::$onTheFlyAttributes[$class] ?: array();
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
		
		/* sets defaults 
		foreach(self as $var => $val) {
			if(!isset($$var)) {
				switch($var) {
					case 'DEFAULT_RENDERER': self::$DEFAULT_RENDERER = new SimplOn\Renderers\Html5(); break;
				}
			}
		}*/
		
		if(self::$DEV_MODE) {
			error_reporting(E_ALL);
			ini_set('display_errors', true);
		} else {
			error_reporting(0);
			ini_set('display_errors', false);
		}
		
		self::decodeURL();
	}
	
	static function dataStorage() {
		if(is_array(self::$DATA_STORAGE)) {
			$d = self::$DATA_STORAGE;
			self::$DATA_STORAGE = new $d['driver'](@$d['host'], @$d['db'], @$d['user'], @$d['pass']); 
		}
		return self::$DATA_STORAGE;
	}
	
	static function parameterEncoder($p) {
		if(is_string($p)) {
			$string_delimiter = '"';
			$p = self::fixCode(urlencode($p));
			return $string_delimiter. $p .$string_delimiter;
		} else {
			return urlencode($p);
		}
	}
	
	static function encodeURL($class = null, $construct_params = null, $method = null, $method_params = null, $dataName = null) {
		$url = '';
		if(isset($class)) {
			// class
			$url.= self::$REMOTE_ROOT . '/' . self::fixCode(strtr($class,'\\','-'));
			
			// construct params
			if(!empty($construct_params) && is_array($construct_params)) {
				$url.= '/' . implode('/',array_map(
					array('self','parameterEncoder'), 
					$construct_params
				));
			}
			
			if(isset($dataName) && isset($method)) {
				// Data name
				$url.= self::$URL_METHOD_SEPARATOR . $dataName;
			}
			
			if(isset($method)) {
				// method
				$url.= self::$URL_METHOD_SEPARATOR . $method;
				
				// method params
				if(!empty($method_params) && is_array($method_params)) {
					$url.= '/' . implode('/',array_map(
						array('self','parameterEncoder'), 
						$method_params
					));
				}
			}
		}
		
		return $url;


	}
	
	static function decodeURL() {
		$string_delimiter = '"';
		$server_request = urldecode(substr($_SERVER['REQUEST_URI'], strlen(self::$REMOTE_ROOT)));
        $qs = self::$URL_METHOD_SEPARATOR;
		$sd = $string_delimiter;
		$offset = 0;
		
		
		$parameterDecoder = function($what, $encapsulated = false) use($sd, $qs, $server_request, &$offset) {
			$regexes = array(
				'class' => '\/ (?<raw>[^'.$sd.$qs.'\/]+) ',
				'construct_params' => '(?:\/(?: (?<raw>[^'.$sd.$qs.'\/]+) | '.$sd.'(?<string>[^'.$sd.']*)'.$sd.' ))',
				'dataName' => '\/?'.$qs.' (?<raw>[^'.$sd.$qs.'\/]+) (?='.$qs.')',
				'method' => '\/?'.$qs.' (?<raw>[^'.$sd.$qs.'\/]+) ',
				'method_params' => '(?:\/(?: (?<raw>[^'.$sd.$qs.'\/]+) | '.$sd.'(?<string>[^'.$sd.']*)'.$sd.' ))',
			);
			if(preg_match('/^'. $regexes[$what] .'/x', substr($server_request, $offset), $matches, PREG_OFFSET_CAPTURE)) {
				$offset+= $matches[0][1] + strlen($matches[0][0]);
				$raw = @$matches['raw'][0];
				$string = @$matches['string'][0];
				
				if(empty($raw) && empty($string)) {
					$return = '';
				} else if(!empty($raw) && empty($string)) {
					if($raw == 'null') {
						$return = null;
					} else if($raw == 'false') {
						$return = array(false);
					} else if(is_numeric($raw)) {
						$return = floatval($raw) == intval($raw)
							? intval($raw)
							: floatval($raw);
					} else {
						$return = $raw;
					}
				} else if(empty($raw) && !empty($string)) {
					$return = urldecode(\SimplOn\Main::fixCode($string, false));
				}
				return $encapsulated
					? array($return)
					: $return;
			} else {
				return false;
			}
		};
		
		self::$class = strtr($parameterDecoder('class'),'-','\\') ?: Main::$DEFAULT_ELEMENT;
		
		self::$construct_params = array();
		while(($param = $parameterDecoder('construct_params', true)) !== false) {
			self::$construct_params[] = $param[0];
		}
		
		self::$dataName = $parameterDecoder('dataName');
		self::$method = $parameterDecoder('method') ?: Main::$DEFAULT_METHOD;
		
		self::$method_params = array();
		while(($param = $parameterDecoder('method_params', true)) !== false) {
			self::$method_params[] = $param[0];
		}
	}
	
	static function fixCode($string, $encoding = true) {
		return $encoding  
			? strtr($string, array(
				'%2F' => '/',
				'%2522' => '%252522',
				'%22' => '%2522',
				'%255C' => '%25255C',
				'%5C' => '%255C',
			))
			: strtr($string, array(
				'%2522' => '%22',
				'%252522' => '%2522',
				'%255C' => '%5C',
				'%25255C' => '%255C',
			));
	}
	
	
	static function fromArray(array $ini) {
		foreach($ini as $const => $value)
			self::$$const = $value;
	}
	
	static function createFile($file_path, $data = null, $flags = null) {
		// @todo: implement with RecursiveDirectoryIterator
		//echo "$file_path<br><br>";
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
	
	static function localToRemotePath($file_path) {
		return str_replace(array(Main::$LOCAL_ROOT, Main::$SimplOn_PATH), Main::$REMOTE_ROOT, $file_path);
	}
	
	/**
	 * Includes a (class)file looking for it in the following order 1.- Site directory, 2.- Site template directory, 3.- SimplOn directory
	 */
	static function load_obj($classToLoad) {
		$pathExploded = explode('\\', $classToLoad);
		
		if(reset($pathExploded) == 'SimplOn') {
			$file_to_load = '../' . str_replace('\\', '/', $classToLoad) . '.php';
			if(file_exists($file_to_load)) {
				require $file_to_load;
				return true;
			}
		} else {
			$classToLoad = end($pathExploded);
			
			$test = array(
				self::$LOCAL_ROOT,
				self::$GENERIC_TEMPLATES_PATH,
				self::$SimplOn_PATH,
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
    
    static function loadDom($template) {
        return is_file($template) ? \phpQuery::newDocumentFile($template) : \phpQuery::newDocument($template);
    }
    
    static function hasNoHtmlTags($string) {
        return strpos($string, '<') === false;
    }
}
