<?php
/* This is a configuration file that initializes the debugger
 * with usually useful parameters.
 * You can change it to your pleasure.
 */
require_once('DubroxPhpDebugger.class.php');
$debugger = new Dubrox_PhpDebugger(array(
	// Client-side directory location
	// where Dubrox's PHP Debugger is located
	// used to locate JS and CSS plug-ins
	'tools_dir' => 'plugins/',

	// Variable name of the GET or POST or REQUEST
	// used to activate the debugger and pass flags to it
	'name' => 'debug',
	
	// Directory where to store logs of the detected bugs.
	// You can use both relative or absolute path.
	'log_dir' => 'debug_logs/',

	// Sets some personal commands sets
	'commands_presets' => array(
		'allp' => 'persistent:on,error_reporting:E_ALL',
	),
));