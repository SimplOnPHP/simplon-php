<?php
/*
	Copyright © 2024 Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
	
	This file is part of “SimplOn PHP” a Data Oriented Aproach free software development framework: you can redistribute it and/or modify it under the terms of the MIT License.
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

/**
 *  SC_Main Is the calss to initialize everithing deccode de URL and execute the software.
 */
class SC_Main {
	const DEFAULT_INI = 'dof.ini';

	static
	//Things likely to be defined for each App

		$App_Name,

		$LOCAL_ROOT,		//Servers path to main public dir
		$WEB_ROOT,			//Web URL to main web dir
		$SimplOn_PATH,		//Servers path to simplon dir
		$App_PATH,			//Servers path to Application dir
		$App_web_root,		//Web URL to Application web dir

		$Layouts_Processing,	//Specifies what to do with Elements Layouts options are: (Always)OverWrite Update(just outdated parts)  Preserve(do not change anything)

		$DEFAULT_ELEMENT,
		$DEFAULT_METHOD = 'showAdmin',

		$DEV_MODE = false,		
		$PERMISSIONS = false,		//The class that will haldel the permisions for example SE_User
		$LOAD_ROLE_CLASS = false,	//Wether or not to load the subclass of the premissions element usually a user's role

		
		/** @var SDS_DataStorage */
		$DATA_STORAGE,

		/** @var SR_main */
		$RENDERER,
	
	//Things rarely to be redefined

		$VCRSL,
		$VCRSLMethods = array('view','create','update','required','search','list','embeded'),


		$URL_METHOD_SEPARATOR = '!',	

        $DEFAULT_PERMISSIONS,
	
			
		$QUICK_DELETE = false,
        
		$LIMIT_ELEMENTS = 20,
	
	//Working stuff
        //super array to alter classes atributes on the fly must be in the format "class" -> array("data1name"=>$data1)
		$SystemMessage = '';
	
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
	static function setup($ini = null) {
		if(file_exists(self::DEFAULT_INI))
			self::fillFromArray(parse_ini_file(self::DEFAULT_INI));

		if(isset($ini)) {
			if(is_array($ini)) {
				self::fillFromArray($ini);
			} else if(is_string($ini) && file_exists($ini)) {
				self::fillFromArray(parse_ini_file($ini));
			}
		}
			
		if(self::$DEV_MODE) {
			error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_DEPRECATED);
			ini_set('display_errors', true);
		} else {
			error_reporting(0);
			ini_set('display_errors', false);
		}

		self::decodeURL();
	}
	/**
	 * Gets URL and breaks it into the class andd method that needs to be executed as well as the message that has to be displayed
	 * 
	 */
	static function decodeURL($e = '') {

		$string_delimiter = '\'';

        $qs = self::$URL_METHOD_SEPARATOR;

		
  
        //If there is previos URL store it to be able to do Back
		if (isset($_SERVER['HTTP_REFERER'])) {
            $server_referal = explode($qs.$qs,$_SERVER['HTTP_REFERER']);
        } else {
            $server_referal = array();
            $server_referal[0] = '';
        }
        $GLOBALS['BackURL']=$server_referal[0];

		$server_URI = substr($_SERVER['REQUEST_URI'], strlen(self::$App_web_root));

        // Look if there is a double $qs and take whats at the end as mmesag

        $server_request = explode($qs.$qs,$server_URI);

        if (isset($server_request[1])) { 
			self::$SystemMessage = urldecode($server_request[1]);		
		 }else {
			self::$SystemMessage = '';
		}

        $server_request = $server_request[0];

        //process the rest of the URL to extract The calss and method
		$server_request = urldecode(substr($server_request, strlen(self::$WEB_ROOT)));
		if(strpos($server_request, '/') !== 0) $server_request = '/' . $server_request;
        $qs = self::$URL_METHOD_SEPARATOR;
		$sd = $string_delimiter;
		$offset = 0;
		
		$parameterDecoder = function($what, $encapsulated = false) use($sd, $qs, $server_request, &$offset) {
			$regexes = array(
				'class' => '\/(?<raw>[^'.$sd.$qs.'\/]+)',
				'construct_params' => '(?:\/(?:(?<raw>[^'.$sd.$qs.'\/]+)|'.$sd.'(?<string>[^'.$sd.']*)'.$sd.'))',
				'dataName' => '\/?'.$qs.'(?<raw>[^'.$sd.$qs.'\/]+)(?='.$qs.')',
				'method' => '\/?'.$qs.'(?<raw>[^'.$sd.$qs.'\/]+)',
				'method_params' => '(?:\/(?:(?<raw>[^'.$sd.$qs.'\/]+)|'.$sd.'(?<string>[^'.$sd.']*)'.$sd.'))',
			);
			if(preg_match('/^'. $regexes[$what] .'/x', substr($server_request, $offset), $matches, PREG_OFFSET_CAPTURE)) {
				$offset+= $matches[0][1] + strlen($matches[0][0]);
				$raw = @$matches['raw'][0];
				$string = @$matches['string'][0];
				
				if(empty($raw) && empty($string)) {
					$return = '';
				} elseif(!empty($raw) && empty($string)) {
					if($raw == 'null') {
						$return = null;
					} elseif($raw == 'false') {
						$return = array(false);
					} elseif(is_numeric($raw)) {
						$return = floatval($raw) == intval($raw)
							? intval($raw)
							: floatval($raw);
					} else {
						$return = $raw;
					}
				} elseif(empty($raw) && !empty($string)) {
					$return = urldecode($this->fixCode($string, false));
				}
				return $encapsulated
					? array($return)
					: $return;
			} else {
				return false;
			}
		};
		
		self::$class = strtr($parameterDecoder('class'),'-','\\') ?: self::$DEFAULT_ELEMENT;
		//self::$class = self::$DEFAULT_ELEMENT; //debug
		self::$construct_params = array();
		while(($param = $parameterDecoder('construct_params', true)) !== false) {
			self::$construct_params[] = $param[0];
		}
		
		self::$dataName = $parameterDecoder('dataName');
		self::$method = $parameterDecoder('method') ?: self::$DEFAULT_METHOD;
		
		self::$method_params = array();
		while(($param = $parameterDecoder('method_params', true)) !== false) {
			self::$method_params[] = $param[0];
		}
	}
		
	/**
	 * Loads de Ini parameters
	 * Instanciates the class and runs the method
	 */
	static function run($ini = null) {

        if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
		self::setup($ini);

		if(self::$PERMISSIONS){
			$permissionsClass = self::$PERMISSIONS; // Get the class name from the static property		
			self::$PERMISSIONS = new $permissionsClass(); //set the object

			self::$DATA_STORAGE->ensureElementStorage(self::$PERMISSIONS);

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
				($obj instanceof SC_Element)
			){
				if(is_object(self::$PERMISSIONS) && (self::$class !='JS' && self::$class!='CSS')   ){
					$mode='';
					if(isset($obj::$methodsFamilies[self::$method])){$mode = $obj::$methodsFamilies[self::$method];}
					
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
		} else {
			header('HTTP/1.1 404 File not found');
			trigger_error('SimplOn: '.self::$class.' is not a valid class name.', E_USER_ERROR);
			return;
		}
	}

	/**
	 * Returns the Data Storage
	 * 
	 * Since self::$DATA_STORAGE can be either the proper Data Storage objecct or an array with the connection type and dat if it's the later it istanciates a proper Data Storage object
	 */
	static function dataStorage() {
		if(is_array(self::$DATA_STORAGE)) {
			$d = self::$DATA_STORAGE;
			self::$DATA_STORAGE = new $d['driver'](@$d['host'], @$d['db'], @$d['user'], @$d['pass']); 
		}
		return self::$DATA_STORAGE;
	}
	
	/**
	 * Loads all the staic constants from ana array
	 */
	static function fillFromArray(array $ini) {
		foreach($ini as $const => $value)
			self::$$const = $value;
	}
	
	/**
	 * Loads objects looking for the file by looking into the first letters of the class name
	 */		
	 static function load_obj( $classToLoad ){
		global $simplon_root;
		global $app_root;

		$ClassKind = explode('_',$classToLoad)[0];

		if($ClassKind == 'SC'){ 							//Simplon Core
			require_once($simplon_root.'/'.$classToLoad.'.php');
		}elseif ($ClassKind == 'SE') {						//Simplon Elements
			require_once($simplon_root.'/Elements/'.$classToLoad.'.php');
		}elseif ($ClassKind == 'SD') {						//Simplon Datas
			require_once($simplon_root.'/Datas/'.$classToLoad.'.php');
		}elseif ($ClassKind == 'SDS') {						//Simplon DataStorage
			require_once($simplon_root.'/DataStorages/'.$classToLoad.'.php');
		}elseif ($ClassKind == 'SR') {						//Simplon Render
			require_once($simplon_root.'/Renderers/'.$GLOBALS['redenderFlavor'].'/'.$classToLoad.'.php');
		}elseif ($ClassKind == 'SI') {						//Simplon Interface Data					//Simplon Render
			require_once($simplon_root.'/Renderers/'.$GLOBALS['redenderFlavor'].'/InterfaceItems/'.$classToLoad.'.php');
		}elseif ($ClassKind == 'AE') {						//App Element
			require_once($app_root.'/'.$classToLoad.'.php');
		}elseif ($ClassKind == 'AD') {						//App Datas
			require_once($app_root.'/Datas/'.$classToLoad.'.php');
		}elseif ($ClassKind == 'AI') {						//App Interface Data
			require_once($app_root.'/InterfaceItems/'.$classToLoad.'.php');
		}
	}

}
