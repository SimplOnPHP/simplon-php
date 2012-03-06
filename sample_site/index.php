<?php
date_default_timezone_set('America/Mexico_City');
setlocale(LC_ALL, 'es_MX.UTF-8', 'es_ES.UTF-8');

require '../DOF/Main.php';
DOF\Main::run(array(
	'LOCAL_ROOT' => __DIR__,
	'REMOTE_ROOT' => dirname($_SERVER['PHP_SELF']),
	
	'DOF_PATH' => realpath('../DOF'),
	
	'DEFAULT_ELEMENT' => 'Fe',
	'DEFAULT_METHOD' => 'index',
	
	'GENERIC_TEMPLATES_PATH' => realpath('Templates'),
	'MASTER_TEMPLATE' => realpath('../DOF').'/Renderers/Html5.html',
	'DEFAULT_RENDERER' => new DOF\Renderers\Html5(),

	'CREATE_LAYOUT_TEMPLATES' => true,
	'OVERWRITE_LAYOUT_TEMPLATES' => 1,
	'USE_LAYOUT_TEMPLATES' => true,
	
	'CREATE_FROM_TEMPLATES' => true,
	'OVERWRITE_FROM_TEMPLATES' => true,
	'USE_FROM_TEMPLATES' => true,

	'JS_FLAVOUR' => 'jQuery',
	
	'DEV_MODE' => true,
	
	'DATA_STORAGE' => array(
		'driver' => 'DOF\\DataStorages\\MySql',
		'host' => 'localhost',
		'db' => 'sample_site',
		'user' => 'root',
		'pass' => ''
	),
	
	'QUICK_DELETE' => true,
));
