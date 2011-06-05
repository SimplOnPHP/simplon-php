<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
include '../../DOF/DOFstuff.php';
DOF\Includer::setup(array(
	'DOF_PATH' => realpath('../../DOF'),
	'GENERIC_TEMPLATES_PATH' => realpath('../../templatesitios'),

	'ADMIN_PATH' => __DIR__,
	'ADMIN_URL' => 'http://localhost:8888/proyectos/FWR/MultiSite/sitio1/internal',

	'PUBLIC_PATH' => realpath('../external'),
	'PUBLIC_URL' => 'http://localhost:8888/proyectos/FWR/MultiSite/sitio1/external',

	'CREATE_LAYOUT_TEMPLATES' => true,
	'OVERWRITE_LAYOUT_TEMPLATES' => true,
	'USE_LAYOUT_TEMPLATES' => true,
	
	'CREATE_FORM_TEMPLATES' => true,
	'OVERWRITE_FORM_TEMPLATES' => true,
	'USE_FORM_TEMPLATES' => true,
));

DOF\Includer::fromArray(array(
	'DATA_STORAGE' => new DOF\MySqlDS('localhost','root','','sitio1'),
));