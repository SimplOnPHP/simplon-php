<?php
date_default_timezone_set('America/Mexico_City');
setlocale(LC_ALL, 'es_MX.UTF-8', 'es_ES.UTF-8');

require '../SimplOn/Main.php';
SimplOn\Main::run(array(
	'LOCAL_ROOT' => __DIR__,
	'REMOTE_ROOT' => dirname($_SERVER['PHP_SELF']),
	
	'SimplOn_PATH' => realpath('../SimplOn'),
	
	'DEFAULT_ELEMENT' => 'Fe',
	'DEFAULT_METHOD' => 'index',
	
	'GENERIC_TEMPLATES_PATH' => realpath('Templates'),
	'MASTER_TEMPLATE' => realpath('../SimplOn').'/Renderers/Html5.html',
	'DEFAULT_RENDERER' => new SimplOn\Renderers\Html5(),

	'CREATE_LAYOUT_TEMPLATES' => true,
	'OVERWRITE_LAYOUT_TEMPLATES' => 1,
	'USE_LAYOUT_TEMPLATES' => true,
	
	'CREATE_FROM_TEMPLATES' => true,
	'OVERWRITE_FROM_TEMPLATES' => true,
	'USE_FROM_TEMPLATES' => true,

	'JS_FLAVOUR' => 'jQuery',
	
	//'PERMISSIONS' => 'SimplOn\\Elements\\User',
	'PERMISSIONS' => false,
	
	'DEV_MODE' => true,
	
	'DATA_STORAGE' => array(
		'driver' => 'SimplOn\\DataStorages\\MySql',
		'host' => 'localhost',
		'db' => 'sample_site',
		'user' => 'root',
		'pass' => ''
	),
	
	'QUICK_DELETE' => true,
));

/*echo __DIR__.'<br>';

echo realpath('../SimplOn').'<br>';

echo realpath('Templates');*/
