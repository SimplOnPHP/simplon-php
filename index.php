<?php
/**/error_reporting(E_ALL); ini_set('display_errors',true);/*/
error_reporting(0); ini_set('display_errors',false);/**/
date_default_timezone_set('America/Mexico_City');
setlocale(LC_ALL, 'es_MX.UTF-8', 'es_ES.UTF-8');

require 'DOF/plugins/dpd/DubroxPhpDebugger.class.php';
$debugger = new Dubrox_PhpDebugger(array(
	// Client-side directory location
	// where Dubrox's PHP Debugger is located
	// used to locate JS and CSS plug-ins
	'tools_dir' => 'DOF/plugins/dpd/plugins/',

	// Variable name of the GET or POST or REQUEST
	// used to activate the debugger and pass flags to it
	'name' => 'debug',
	
	// Directory where to store logs of the detected bugs.
	// You can use both relative or absolute path.
	'log_dir' => 'dpd_logs/',

	// Sets some personal commands sets
	'commands_presets' => array(
		'allp' => 'persistent:on,error_reporting:E_ALL',
	),
));

require 'DOF/Configurator.php';
DOF\Config::setup(array(
	'LOCAL_ROOT' => __DIR__,
	'REMOTE_ROOT' => dirname($_SERVER['PHP_SELF']),
	
	'DOF_PATH' => realpath('./DOF'),
	'GENERIC_TEMPLATES_PATH' => realpath('./templatesitios'),

	'CREATE_LAYOUT_TEMPLATES' => true,
	'OVERWRITE_LAYOUT_TEMPLATES' => true,
	'USE_LAYOUT_TEMPLATES' => true,
	
	'CREATE_FORM_TEMPLATES' => true,
	'OVERWRITE_FORM_TEMPLATES' => true,
	'USE_FORM_TEMPLATES' => true,
	
	'DATA_STORAGE' => new DOF\MySqlDS('localhost','root','','sitio1'),
));

// Parses the URL
$server_request = $_SERVER['REQUEST_URI'];
if(strpos($server_request, '?') !== false)
	$server_request = substr($server_request, 0, strpos($server_request, '?'));
$virtual_path = array_values(array_diff(
	explode('/',$server_request), 
	explode('/',DOF\Config::REMOTE_ROOT)
));
$class = @array_shift($virtual_path) ?: 'home';
$method =  @array_shift($virtual_path) ?: 'landing';
$parameters = array_values($virtual_path);

//TODO: stuff

$debugger->toHtml();