<?php

// check for a guided tutorial at https://simplonphp.org/tutorial
date_default_timezone_set('America/Mexico_City');
setlocale(LC_ALL, 'es_MX.UTF-8', 'es_ES.UTF-8');

require '../SimplOn/Main.php';
SimplOn\Main::run(array(
	'LOCAL_ROOT' => __DIR__,
	'REMOTE_ROOT' => dirname($_SERVER['PHP_SELF']),
	
	'SimplOn_PATH' => realpath('../SimplOn'),
	'GENERIC_TEMPLATES_PATH' => realpath('Templates'),
	'MASTER_TEMPLATE' => realpath('../SimplOn').'/Renderers/Html5.html',
		
	'DEFAULT_ELEMENT' => 'Persona',
	'DEFAULT_METHOD' => 'index',
	
	'DEFAULT_RENDERER' => new SimplOn\Renderers\Html5(),

	'CREATE_LAYOUT_TEMPLATES' => true,
	'OVERWRITE_LAYOUT_TEMPLATES' => true,
	'USE_LAYOUT_TEMPLATES' => true,
	
	'CREATE_FROM_TEMPLATES' => true,
	'OVERWRITE_FROM_TEMPLATES' => true,
	'USE_FROM_TEMPLATES' => true,

	'JS_FLAVOUR' => 'jQuery',
	
	'DEV_MODE' => true,
	// MySql('localhost','your database','your user','your password')
	'DATA_STORAGE' => new SimplOn\DataStorages\MySql('localhost','tutorial','root',''),
));
