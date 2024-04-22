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

spl_autoload_register( __NAMESPACE__ . '\\SC_Main::load_obj');



class SC_Main {
	const DEFAULT_INI = 'dof.ini';

	static
		$LOCAL_ROOT,
		$REMOTE_ROOT,
		$VCRSL,
		$VCRSLMethods = array('view','create','update','required','search','list','embeded'),
		$VCRSLFormMethods = array('create','update','search'),
		$SimplOn_PATH,
		$App_PATH,
		$App_web_root,
		$App_Name,
		$Element_Layouts,
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
		$LOAD_ROLE_CLASS = false,
        $DEFAULT_PERMISSIONS,
	
		$DEV_MODE = false,

		$DATA_STORAGE,
			
		$QUICK_DELETE = false,
        
		$LIMIT_ELEMENTS,
        //super array to alter classes atributes on the fly must be in the format "class" -> array("data1name"=>$data1)
		$SystemMessage = '',
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
		//self::$PERMISSIONS = new \ReflectionClass(class_exists(self::$PERMISSIONS) ? self::$PERMISSIONS : '\\SimplOn\\Elements\\'.self::$PERMISSIONS);
		//self::$PERMISSIONS = self::$PERMISSIONS->newInstanceArgs(array($_SESSION["permissionID"]));
		$permissionsClass = self::$PERMISSIONS; // Get the class name from the static property
		self::$PERMISSIONS = new $permissionsClass(); //set the object
	
		if(isset($_SESSION["permissionID"])){
			self::$PERMISSIONS->fillFromDSById($_SESSION["permissionID"]); // Instantiate the class
			$role=self::$PERMISSIONS->OuserRole()->viewVal();
			$permissionsClass = 'AE_'.ucfirst($role);
			self::$PERMISSIONS = new $permissionsClass($_SESSION["permissionID"]); //set the object
		}
		
		$results = self::$DATA_STORAGE->readElements(self::$PERMISSIONS);

		//if there is no user in the database allow a temporary admin that can create users
		if(!$results){
			self::$PERMISSIONS = new AE_EmptyAdmin(); 
			self::$PERMISSIONS->userName('emptyAdmin');
		}

		if(class_exists(self::$class) || class_exists('\\SimplOn\\Elements\\'.self::$class)) {
			//$cp = self::$construct_params;


			//Load the sub class of the User (or permisssions class) if it exisists
			if(self::$LOAD_ROLE_CLASS && isset($role)){
				$class = 'AE_'.ucfirst($role); 
				if(file_exists(SC_Main::$App_PATH.'\\'.$class.'.php')){
					$newPermissionsObject = new $class();
					$newPermissionsObject->fillFromDSById($_SESSION["permissionID"]);
					SC_Main::$PERMISSIONS = $newPermissionsObject;
				}
			}


			$rc = new \ReflectionClass(class_exists(self::$class) ? self::$class : '\\SimplOn\\Elements\\'.self::$class);
			if( 
				isset(self::$method)
				&&
				($obj = $rc->newInstanceArgs(self::$construct_params))
				&&
				($obj instanceof SE_Element)
			){
				if(is_object(self::$PERMISSIONS) && (self::$class !='JS' && self::$class!='CSS')   ){
					$mode='';
					if(isset($obj->methodsFamilies()[self::$method])){$mode = $obj->methodsFamilies()[self::$method];}
					
					// If there is a user that can enter
					if( self::$PERMISSIONS->canEnter($obj,$mode) ){	
						
						//if there is a set of values for the Element Datas set them
						if($mode){
							self::$PERMISSIONS->setValuesByPermissions($obj, $mode);
						}

						echo call_user_func_array(array($obj, self::$method), self::$method_params);
					//if there is a user that can't enter
					}elseif(self::$PERMISSIONS->logedIn()){
						self::$SystemMessage='You can\'t access that page ';
						echo call_user_func_array(array(self::$PERMISSIONS, 'default'), self::$method_params);
					//if there is no user or else
					}else{
						echo call_user_func_array(array(self::$PERMISSIONS, 'showLogin'), self::$method_params);
					}	
				}else{
					if(self::$dataName) {$obj = $obj->{self::$dataName};}
					echo call_user_func_array(array($obj, self::$method), self::$method_params);
				}
			}

            //     if(self::$PERMISSIONS && (self::$class !='JS' && self::$class!='CSS')   ){
					
			// 		if(!@$_SESSION['simplonUser'] && !(self::$class == self::$PERMISSIONS && self::$method =='processValidation')  ){
			// 			//ask for credentials
			// 			$class = '\\'.self::$PERMISSIONS;
			// 			$validator = new $class(); //$user is the default validator class
			// 			$_SESSION['url']=$_SERVER['REQUEST_URI'];
			// 			echo $validator->showValidation();
			// 		}else{
			// 			if (isset($_SERVER['HTTP_REFERER'])){ $_SESSION['url']=$_SERVER['HTTP_REFERER']; }
			// 					//Validate user's permissions
			// 			if( $obj->allow(@$_SESSION['simplonUser'],self::$method) ) {
			// 				if(self::$dataName) {
			// 					$obj = $obj->{self::$dataName};
			// 				}
			// 				echo call_user_func_array(array($obj, self::$method), self::$method_params);
			// 			}else{
			// 				//header('HTTP/1.1 403 Access forbidden');
			// 				//header('SimplOn: You don\'t have permissions to see this page.');
            //                 echo '<h1>Access forbidden</h1>';
			// 				return;
			// 			}
			// 		}
			// 	}else{
			// 		if(self::$dataName) {
            //             $obj = $obj->{self::$dataName};
			// 		}
			// 		echo call_user_func_array(array($obj, self::$method), self::$method_params);
			// 	}
			// } elseif( $obj->hasMethod(self::$method) ){ //Not Element but object to do whatever you want

			// 	echo call_user_func_array(array($obj, self::$method), self::$method_params);
	
			// }else{
			// 	header('HTTP/1.1 403 Access forbidden');
			// 	echo 'SimplOn: '.self::$class.' is not an SE_Element class.';
			// 	return;
			// }
		} else {
			header('HTTP/1.1 404 File not found');
			trigger_error('SimplOn: '.self::$class.' is not a valid class name.', E_USER_ERROR);
			return;
		}
	}

    
	static function addData($class,$attributeName,$attribute) {
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
					case 'DEFAULT_RENDERER': self::$DEFAULT_RENDERER = new SR_Html5(); break;
				}
			}
		}*/
		
		if(self::$DEV_MODE) {
			error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_DEPRECATED);
			ini_set('display_errors', true);
		} else {
			error_reporting(0);
			ini_set('display_errors', false);
		}
		
		$redender = $GLOBALS['redender'];
		$redender->decodeURL();
	}
	
	static function dataStorage() {
		if(is_array(self::$DATA_STORAGE)) {
			$d = self::$DATA_STORAGE;
			self::$DATA_STORAGE = new $d['driver'](@$d['host'], @$d['db'], @$d['user'], @$d['pass']); 
		}
		return self::$DATA_STORAGE;
	}
	
	// static function parameterEncoder($p) {
	// 	if(is_string($p)) {
	// 		$string_delimiter = '"';
	// 		$p = self::fixCode(urlencode($p));
	// 		return $string_delimiter. $p .$string_delimiter;
	// 	} else {
	// 		return urlencode($p);
	// 	}
	// }
	
	// static function encodeURL($class = null, $construct_params = null, $method = null, $method_params = null, $dataName = null) {
	// 	$url = '';
	// 	if(isset($class)) {
	// 		// class
	// 		$url.= self::$REMOTE_ROOT . '/' . self::fixCode(strtr($class,'\\','-'));

	// 		// construct params
	// 		if(!empty($construct_params) && is_array($construct_params)) {
	// 			$url.= '/' . implode('/',array_map(
	// 				array('self','parameterEncoder'), 
	// 				$construct_params
	// 			));
	// 		}
			
	// 		if(isset($dataName) && isset($method)) {
	// 			// Data name
	// 			$url.= self::$URL_METHOD_SEPARATOR . $dataName;
	// 		}
			
	// 		if(isset($method)) {
	// 			// method
	// 			$url.= self::$URL_METHOD_SEPARATOR . $method;
				
	// 			// method params
	// 			if(!empty($method_params) && is_array($method_params)) {
	// 				$url.= '/' . implode('/',array_map(
	// 					array('self','parameterEncoder'), 
	// 					$method_params
	// 				));
	// 			}
	// 		}
	// 	}
		
	// 	return $url;


	// }
	
	// static function decodeURL() {
	// 	$string_delimiter = '\'';
	// 	$server_request = urldecode(substr($_SERVER['REQUEST_URI'], strlen(self::$REMOTE_ROOT)));
	// 	if(strpos($server_request, '/') !== 0) $server_request = '/' . $server_request;
    //     $qs = self::$URL_METHOD_SEPARATOR;
	// 	$sd = $string_delimiter;
	// 	$offset = 0;
		
		
	// 	$parameterDecoder = function($what, $encapsulated = false) use($sd, $qs, $server_request, &$offset) {
	// 		$regexes = array(
	// 			'class' => '\/(?<raw>[^'.$sd.$qs.'\/]+)',
	// 			'construct_params' => '(?:\/(?:(?<raw>[^'.$sd.$qs.'\/]+)|'.$sd.'(?<string>[^'.$sd.']*)'.$sd.'))',
	// 			'dataName' => '\/?'.$qs.'(?<raw>[^'.$sd.$qs.'\/]+)(?='.$qs.')',
	// 			'method' => '\/?'.$qs.'(?<raw>[^'.$sd.$qs.'\/]+)',
	// 			'method_params' => '(?:\/(?:(?<raw>[^'.$sd.$qs.'\/]+)|'.$sd.'(?<string>[^'.$sd.']*)'.$sd.'))',
	// 		);
	// 		if(preg_match('/^'. $regexes[$what] .'/x', substr($server_request, $offset), $matches, PREG_OFFSET_CAPTURE)) {
	// 			$offset+= $matches[0][1] + strlen($matches[0][0]);
	// 			$raw = @$matches['raw'][0];
	// 			$string = @$matches['string'][0];
				
	// 			if(empty($raw) && empty($string)) {
	// 				$return = '';
	// 			} else if(!empty($raw) && empty($string)) {
	// 				if($raw == 'null') {
	// 					$return = null;
	// 				} else if($raw == 'false') {
	// 					$return = array(false);
	// 				} else if(is_numeric($raw)) {
	// 					$return = floatval($raw) == intval($raw)
	// 						? intval($raw)
	// 						: floatval($raw);
	// 				} else {
	// 					$return = $raw;
	// 				}
	// 			} else if(empty($raw) && !empty($string)) {
	// 				$return = urldecode(SC_Main::fixCode($string, false));
	// 			}
	// 			return $encapsulated
	// 				? array($return)
	// 				: $return;
	// 		} else {
	// 			return false;
	// 		}
	// 	};
		
	// 	self::$class = strtr($parameterDecoder('class'),'-','\\') ?: SC_Main::$DEFAULT_ELEMENT;
	// 	//self::$class = SC_Main::$DEFAULT_ELEMENT; //debug
	// 	self::$construct_params = array();
	// 	while(($param = $parameterDecoder('construct_params', true)) !== false) {
	// 		self::$construct_params[] = $param[0];
	// 	}
		
	// 	self::$dataName = $parameterDecoder('dataName');
	// 	self::$method = $parameterDecoder('method') ?: SC_Main::$DEFAULT_METHOD;
		
	// 	self::$method_params = array();
	// 	while(($param = $parameterDecoder('method_params', true)) !== false) {
	// 		self::$method_params[] = $param[0];
	// 	}
	// }
	
	// static function fixCode($string, $encoding = true) {

	// 	return $encoding  
	// 		? strtr($string, array(
	// 			'%2F' => '/',
	// 			'%2522' => '%252522',
	// 			'%22' => '%2522',
	// 			'%255C' => '%25255C',
	// 			'%5C' => '%255C',
	// 		))
	// 		: strtr($string, array(
	// 			'%2522' => '%22',
	// 			'%252522' => '%2522',
	// 			'%255C' => '%5C',
	// 			'%25255C' => '%255C',
	// 		));
	// }
	
	
	static function fromArray(array $ini) {
		foreach($ini as $const => $value)
			self::$$const = $value;
	}
	
	static function createFile($file_path, $data = null, $flags = null) {
		// @todo: implement with RecursiveDirectoryIterator
		//echo "$file_path<br><br>";

		$exploded_path = explode('/', $file_path);
		$file = array_pop($exploded_path);
		$current_path = '.';
		foreach ($exploded_path as $dir) {
			$current_path.= $dir.'/';
			if($dir && !is_dir($current_path)) {
				if(!mkdir($current_path))
					throw new SC_Exception('Cannot create the following directory: '. $current_path);
			}
		}
				
		///rsl2022 quick and dirty fix to remove .// in paths
		$file_path = str_replace('.//','./',$file_path);
		

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
		$ret=str_replace(array(SC_Main::$LOCAL_ROOT, SC_Main::$SimplOn_PATH), SC_Main::$REMOTE_ROOT, $file_path);
		
		///rsl2022 quick and dirty fix to .\\ in file paths
		$ret = str_replace('.\\','./',$ret);

		return $ret;
	}
	
	/**
	 * Includes a (class)file looking for it in the following order 1.- Site directory, 2.- Site template directory, 3.- SimplOn directory
	 */	
	
	 static function load_obj( $classToLoad ){
		global $simplon_root;
		global $app_root;
		$redenderSubDir = $GLOBALS['redenderSubDir'];


		$ClassKind = explode('_',$classToLoad)[0];
	 
		if($ClassKind == 'SC'){ 								//Simplon Core
			require_once($simplon_root.'/'.$classToLoad.'.php');
		}elseif ($ClassKind == 'SE') {						//Simplon Elements
			require_once($simplon_root.'/Elements/'.$classToLoad.'.php');
		}elseif ($ClassKind == 'SD') {						//Simplon Datas
			require_once($simplon_root.'/Datas/'.$classToLoad.'.php');
		}elseif ($ClassKind == 'SDS') {						//Simplon DataStorage
			require_once($simplon_root.'/DataStorages/'.$classToLoad.'.php');
		}elseif ($ClassKind == 'SR') {						//Simplon Render
			require_once($simplon_root.'/Renderers/'.$classToLoad.'.php');
		}elseif ($ClassKind == 'SID') {						//Simplon Render
			require_once($simplon_root.'/InterfaceDatas/'.$classToLoad.'.php');
		}elseif ($ClassKind == 'AE') {						//App Element
			require_once($app_root.'/'.$classToLoad.'.php');
		}elseif ($ClassKind == 'AD') {						//App Datas
			require_once($app_root.'/Datas/'.$classToLoad.'.php');
		}elseif ($ClassKind == 'ARID') {						//Simplon Render
			require_once($app_root.'/InterfaceDatas/'.$classToLoad.'.php');
		}
	 
	}

    
    static function loadDom($template) {
        return is_file($template) ? \phpQuery::newDocumentFile($template) : \phpQuery::newDocument($template);
    }
    
    static function hasNoHtmlTags($string) {
        return strpos($string, '<') === false;
    }
}
