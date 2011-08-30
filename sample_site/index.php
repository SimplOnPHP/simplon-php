<?php
/* */
error_reporting(E_ALL); ini_set('display_errors',true);
/*/
error_reporting(0); ini_set('display_errors',false);
/* */
date_default_timezone_set('America/Mexico_City');
setlocale(LC_ALL, 'es_MX.UTF-8', 'es_ES.UTF-8');

/* *
echo '$_SERVER';var_dump($_SERVER);
echo '$_GET';var_dump($_GET);
phpinfo();
exit();
/* */

/*
$basePath = substr($_SERVER['PHP_SELF'], 0, -9);
$parts = explode('?',$_SERVER['REQUEST_URI']);

$construct_params = explode('/', substr($parts[0], strlen($basePath)) );
$class = array_shift($construct_params);

$method_params = explode('/',$parts[1]);
$method = array_shift($method_params);
*/

//var_dump($class);
//var_dump($construct_params);

//var_dump($method);
//var_dump($method_params);




require '../DOF/Main.php';
DOF\Main::setup(array(
	'LOCAL_ROOT' => __DIR__,
	'REMOTE_ROOT' => dirname($_SERVER['PHP_SELF']),
	
	'DOF_PATH' => realpath('../DOF'),
	'GENERIC_TEMPLATES_PATH' => realpath('Templates'),
	'MASTER_TEMPLATE' => realpath('Templates') . '/Master.html',
	
	'DEFAULT_ELEMENT' => 'Fe',
	'DEFAULT_METHOD' => 'index',
	
	'DEFAULT_RENDERER' => new DOF\Renderers\Html4(),

	'CREATE_LAYOUT_TEMPLATES' => true,
	'OVERWRITE_LAYOUT_TEMPLATES' => true,
	'USE_LAYOUT_TEMPLATES' => true,
	
	'CREATE_FROM_TEMPLATES' => true,
	'OVERWRITE_FROM_TEMPLATES' => true,
	'USE_FROM_TEMPLATES' => true,

	'JS_FLAVOUR' => 'jQuery',
	
	'DEV_MODE' => true,
	
	'DATA_STORAGE' => new DOF\DataStorages\MySql('localhost','sample_site','root',''),
	//'DATA_STORAGE' => new DOF\DataStorages\SQLite('localhost',__DIR__.'/sample_site.sqlite'),
));

require DOF\Main::$DOF_PATH . '/PlugIns/dpd/DubroxPhpDebugger.class.php';
$debugger = new Dubrox_PhpDebugger(array(
	// Client-side directory location
	// where Dubrox's PHP Debugger is located
	// used to locate JS and CSS plug-ins
	'tools_dir' => DOF\Main::$DOF_PATH . '/PlugIns/dpd/plugins/',

	// Variable name of the GET or POST or REQUEST
	// used to activate the debugger and pass flags to it
	'name' => 'debug',
	
	// Directory where to store logs of the detected bugs.
	// You can use both relative or absolute path.
	'log_dir' => DOF\Main::$LOCAL_ROOT . 'dpd_logs/',

	// Sets some personal commands sets
	'commands_presets' => array(
		'allp' => 'persistent:on,error_reporting:E_ALL',
	),
));



/**
 * @TODO: allow debugging of this fragment of code.
 */
//if(class_exists(DOF\Main::$class)) {
if(class_exists(DOF\Main::$class)) {
	$rc = new ReflectionClass(DOF\Main::$class);
	if( 
		isset(DOF\Main::$method)
		&&
		($obj = $rc->newInstanceArgs(DOF\Main::$construct_params))
		&&
		($obj instanceof DOF\Elements\Element)
	){
		echo call_user_func_array(array($obj, DOF\Main::$method), DOF\Main::$method_params);
	} else {
		var_dump(DOF\Main::$method);
		unset($obj);
		header('HTTP/1.1 403 Access forbidden');
		return;
	}
} else {
	//header('HTTP/1.1 404 File not found');
	echo 'Class '.DOF\Main::$class.' not found';
	return;
}

echo $debugger->toHtml();