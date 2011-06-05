<?php
/**
* Dubrox_PhpDebugger allows to show debugs informations in a better looking way
* without being intrusive for the HTML result, allowing some other useful operations.
* 
* @version	1.12.2
* @author	Luca Lauretta (aka. dubrox)
* @license	http://www.gnu.org/copyleft/gpl.html GNU General Public License
* @todo		visit: https://sourceforge.net/apps/trac/dubroxphpdebug/roadmap
*/
class Dubrox_PhpDebugger {
	const version	 = '1.12.2';
	const site		 = 'http://dubroxphpdebug.sourceforge.net/';
	
	private $active				 = false;
	private $level;
	private $equivalentLevel;
	private $scripts_to_debug	 = array();
	private $discrete			 = 'any';
	private $halt_on_error		 = false;
	
	private $collected			 = array();
	private $n_bugs				 = 0;
	private $debugging_time;
	
	private $tools_dir;
	private $name;
	private $session_name;
	private $commands			 = array();
	private $commands_presets;
	
	private $error_reporting	 = 'E_ALL|E_STRICT';
	private $ex_error_handler;
	// private $ex_error_reporting;
	
	var $log_dir;
	var	$log_everything;
	var	$one_log;
	
	/**
	 * Initializes the object.
	 * 
	 * @param	array	$options
	 * @return	void
	 */
	function __construct($options = array()) {
		// allowed options and its default values
		$options_defaults = array(
			'name' => 'dpd',
			'tools_dir' => '/dpd/plugins/',
			'log_dir' => '',
			'log_everything' => false,
			'one_log' => false,
			'commands_presets' => array(),
		);
		
		foreach($options_defaults as $option => $default) {
			$this->$option = (isset($options[$option])) ? $options[$option] : $default;
		}
		
		// sets the session name
		$this->session_name = preg_replace('/[^a-z0-9]+/i', '', $this->name);
		
		// sets default debug level to maximum
		$this->debugLevel('max');
		
		// allows to manage CLI scripts
		global $argv;
		
		// selects where to get infos about debugger commands
		if( isset($_GET[$this->name]) ) {
			$commands = $_GET[$this->name];
			$this->active = true;
		} elseif( isset($_POST[$this->name]) ) {
			$commands = $_POST[$this->name];
			$this->active = true;
		} elseif( isset($argv[$this->name]) ) {
			$commands = 'level:max';
			$this->active = true;
		}
		
		if($this->active) {
			// parses the commands to array
			$r_commands = $this->parseCommandsString($commands);
		}
		
		$persistent = $this->persistentMode();
		if($persistent) {
			if(isset($r_commands)) {
				foreach($persistent as $command => $value) {
					if($command != 'persistent') { 
						$r_commands[$command] = $value;
					}
				}
			} else {
				$r_commands = $persistent;
			}
			$this->active = true;
		}
		
		// parses the commands and starts the debugger
		if($this->active) {
			$this->commands = $r_commands;
			$this->startDebugger();
		}
	}
	
	/**
	 * Parses a string of commands to the respective array.
	 * 
	 * @param	string	$commands
	 * @return	array
	 */
	private function parseCommandsString($commands) {
		$r_commands = array();
		
		if(is_string($commands) && $commands != '') {
			$a_commands = explode(',',$commands);
			foreach($a_commands as $command_and_value) {
				list($command, $value) = explode(':',$command_and_value);
				$r_commands[$command] = $value;
			}
		}
		
		return $r_commands;
	}
	
	/**
	 * Applies commands in the debugger environment.
	 * 
	 * @param	array|string	$commands
	 * @return	void
	 */
	private function applyCommands($commands) {
		if(!is_array($commands)) $commands = $this->parseCommandsString($commands);
		
		foreach($commands as $command => $value) {
			switch($command) {
				case 'c':
				case 'my':
				case 'preset':
					if(isset($this->commands_presets[$value])) {
						$this->applyCommands($this->commands_presets[$value]);
					} else {
						throw new Exception('"'.$value.'" is not a valid Preset command!');
					}
					break;
				case 'persistent':
					$this->persistentMode($value);
					break;	
				case 'discrete':
					$this->discreteMode($value);
					break;
				case 'halt':
				case 'halt on error':
					$this->haltOnError($value);
					break;
				case 'debug level':
				case 'level':
					$this->debugLevel($value);
					break;
				case 'error_reporting':
					$this->error_reporting = $value;
					break;
				case 'script':
				case 'scripts':
					$this->scriptsToDebug($value);
					break;
				default:
					throw new Exception('"'.$command.'" is not an allowed command.');
			}
		}	
	}
	
	/**
	 * Pauses debugging session.
	 * 
	 * @return void
	 */
	function pauseDebugger() {
		// error_reporting($this->ex_error_reporting);
		set_error_handler($this->ex_error_handler);
	}
	
	/**
	 * Starts debugging session.
	 * 
	 * @param	array	$r_commands
	 * @return	void
	 */
	function startDebugger($r_commands = array()) {
		$this->commands = array_merge($this->commands, $r_commands);
		
		// parses command array to set debugger variables
		$this->applyCommands($this->commands);

		// if debugger level is superior to 0
		// custom error handler is activated to collect errors
		if($this->debugLevel()) {
			// $this->ex_error_reporting = error_reporting(eval('return '.$this->error_reporting.';'));
			// ob_start('before_ending') or trigger_error('Debug buffering failed!',E_USER_ERROR); // to test
			if($this->ex_error_handler = set_error_handler(array($this, 'errorsCollector'), eval('return '.$this->error_reporting.';'))) {
				trigger_error('Another error handler, named "'.$this->ex_error_handler.'", has been detected and substituted!', E_USER_NOTICE);
			}
		}
		
		// benchmarking
		$this->debugging_time = microtime(true);
	}
	
	/**
	 * Allows association within PHP Error numbers 
	 * and DPD error levels
	 * 
	 * @param	int	$errno
	 * @return	string
	 */
	function equivalentErrorLevel($errno) {
		switch($errno) {
			case 0:
				$equivalent = 'none';
				break;
				
			case E_PARSE:
			case E_ERROR:
			case 4096:
			case E_USER_ERROR:
				$equivalent = 'error';
				break;
				
			case E_WARNING:
			case 2048:
			case E_USER_WARNING:
				$equivalent = 'warning';
				break;
				
			case 8192:
			case 16384:
			case E_NOTICE:
			case E_USER_NOTICE:
				$equivalent = 'notice';
				break;
				
			default:
				$equivalent = 'any';
				break;
		}
		return $equivalent;
	}
	
	/**
	 * Translates the equivalent level from string to int and vice versa.
	 * @param	string|int	$level
	 * @param	bool|string	$force_format	Forces the output to a specified format ('int' or 'string').
	 * @return	int|string
	 */
	private function translateEquivalentLevel($level, $force_format = false) {
		switch("$level") {
			case '0': case 'none':
				$int = 0;
				$string = 'none';
				break;
			case '1': case 'error': case 'errors':
				$int = 1;
				$string = 'error';
				break;
			case '2': case 'warning': case 'warnings':
				$int = 2;
				$string = 'warning';
				break;
			case '3': case 'notice': case 'notices':
				$int = 3;
				$string = 'notice';
				break;
			case '4': case 'any':
				$int = 4;
				$string = 'any';
				break;
			default:
				$int = 4;
				$string = 'any';
				throw new Exception('"'.$level.'" is not a valid error level!');
		}
		if($force_format === false) {
			if(is_string($level)) {
				return $int;
			} else {
				return $string;
			}
		} else {
			return $$force_format;
		}
	}
	
	/**
	 * Sets and gets the Debug level.
	 * If a Debug level is set, then will be displayed all message of a level 
	 * equal or superior to the specified one.
	 * 
	 * @param	int|string	$set
	 * @return	int
	 */
	function debugLevel($set = NULL) {
		if(isset($set)) {
			switch("$set") {
				case '0': case 'none':
					$this->level = 0;
					$this->equivalentLevel = 'none';
					break;
				case '1': case 'error': case 'errors': 
					$this->level = 1;
					$this->equivalentLevel = 'error';
					break;
				case '2': case 'warning': case 'warnings': 
					$this->level = 2;
					$this->equivalentLevel = 'warning';
					break;
				case '3': case 'system notice': case 'system notices': 
					$this->level = 3;
					$this->equivalentLevel = 'notice';
					break;
				case '4': case 'notice':  case 'notices':
					$this->level = 4;
					$this->equivalentLevel = 'notice';
					break;
				case '5': case 'any': case 'max':
					$this->level = 5;
					$this->equivalentLevel = 'any';
					break;
				default:
					throw new Exception('"'.$set.'" is not a valid debug level, "'.$this->level.'" will be kept.');
			}
		}
		return $this->level;
	}
	
	/**
	 * Sets and gets the Persistent mode.
	 * Persistent mode allows to set debugging on of off at session-level.
	 * 
	 * @param	bool	$set
	 * @return	bool
	 */
	function persistentMode($set = NULL) {
		// In case session is closed, opens it
		if(isset($set)) {
			session_write_close();
			$ex_session_name = session_name($this->session_name);
			session_regenerate_id();
			session_start();
		}
		
		// in case session variable is still unset, sets to default false
		if(!isset($_SESSION[$this->name])) $set = false;
		
		// in case a set parameter is passed, sets session variable
		if(isset($set)) {
			switch("$set") {
				case '1':
				case 'on':
				case 'true':
					$_SESSION[$this->name] = $this->commands;
					break;
				default:
					if(isset($_SESSION[$this->name])) {
						$_SESSION[$this->name] = false;
						unset($_SESSION[$this->name]);
					}
					return array();
					break;
			}
		}
		
		// returns session status
		$return = $_SESSION[$this->name];
//		echo (
//			'<br>
//			$_SESSION[debug]: "' .print_r($_SESSION[$this->name],true). '" 
//			$set: "'. $set .'"
//			<br>'
//		);
		session_write_close();
		session_name($ex_session_name);
		
		return $return ;
	}
	
	/**
	 * Sets and gets the Discrete mode.
	 * If Discrete mode is set, debug information will be displayed only if
	 * a message of a level equal or superior to a specified one is catched.
	 * (default-level: any)
	 * 
	 * @param	string	$set
	 * @return	string
	 */
	function discreteMode($set = NULL) {
		if(isset($set)) {
			$set = $this->translateEquivalentLevel($set, 'string');
			switch($set) {
				case 'error':
				case 'warning':
				case 'notice':
				case 'any':
					$this->discrete = $set;
					break;
				default:
					throw new Exception('"'.$set.'" is not a valid error level.');
			}
		}
		return $this->discrete;
	}
	
	/**
	 * Sets and gets the Halt On Error mode.
	 * If Halt On Error mode is set, script will halt if
	 * a message of a level equal or superior to a specified one is catched.
	 * (default-level: warnings)
	 * 
	 * @param	string	$set
	 * @return	string
	 */
	function haltOnError($set = NULL) {
		if(isset($set)) {
			$set = $this->translateEquivalentLevel($set, 'string');
			switch($set) {
				case false:
				case 'error':
				case 'warning':
				case 'notice':
				case 'any':
					$this->halt_on_error = $set;
					break;
				default:
					throw new Exception('"'.$set.'" is not a valid error level.');
			}
		}
		return $this->halt_on_error;
	}
	
	/**
	 * Sets and gets an array the scripts to debug.
	 * If Scripts To Debug is set, the only debug messages displayed will
	 * be those generated by the list of scripts specified. Otherwise all
	 * messages will be displayed.
	 * 
	 * @param	array|string	$set
	 * @return	array
	 */
	function scriptsToDebug($set = NULL) {
		if(isset($set)) {
			if(is_array($set)) {
				$this->scripts_to_debug = $set;
			} else {
				$this->scripts_to_debug = explode('|', $set);
			}
		}
		return $this->scripts_to_debug;
	}
	
	// // to test
	// function __destruct() {
		// if($printed_pretty_errors == 0) {
			// $close_body_tag = '</body>';
			// ob_end_clean();
			// $html_page = ob_get_clean();
			// $pretty_errors = pretty_errors();
			// echo str_replace($close_body_tag, $pretty_errors.$close_body_tag ,$html_page);
		// } else {
			// ob_end_flush();
		// }
	// }
	
	/**
	 * Verifies if the error number has to be handled
	 * by the error handler, based on the current
	 * Debug level.
	 * 
	 * @param	int	$errno
	 * @return	bool
	 */
	private function allowedError($errno) {
		$allowed = false;
		switch($errno) {
			case E_PARSE:
			case E_ERROR:
			case E_USER_ERROR:
				$allowed = true;
				break;
			case E_WARNING:
			case E_USER_WARNING:
				switch($this->debugLevel()) {
					case 2:
					case 3:
					case 4:
					case 5:
						$allowed = true;
				}
				break;
			case E_NOTICE:
				switch($this->debugLevel()) {
					case 3:
					case 4:
					case 5:
						$allowed = true;
				}
			case E_USER_NOTICE:
				switch($this->debugLevel()) {
					case 4:
					case 5:
						$allowed = true;
				}
				break;
			default:
				switch($this->debugLevel()) {
					case 5:
						$allowed = true;
				}
				break;
		}
		return $allowed;
	}
	
	/**
	 * Custom error handler.
	 * Simply collects all errors and stores them in an internal variable
	 * without printing them.
	 * 
	 * @param int		$errno
	 * @param string	$errstr
	 * @param string	$errfile
	 * @param int		$errline
	 * @return bool
	 */
	function errorsCollector($errno, $errstr, $errfile, $errline, $errcontext) {
		
		// makes it easier to distinguish within error levels
		switch($errno) {
			case E_ERROR:		$color='#FF0000'; $err_lvl='E_ERROR'; break;
			case E_USER_ERROR:	$color='#FF0000'; $err_lvl='E_USER_ERROR'; break;
			case 4096:			$color='#FF0000'; $err_lvl='E_RECOVERABLE_ERROR'; break;
			
			case E_WARNING:		$color='#FF9966'; $err_lvl='E_WARNING'; break;
			case E_USER_WARNING:$color='#FF9966'; $err_lvl='E_USER_WARNING'; break;
			
			case E_NOTICE:		$color='#FFFF00'; $err_lvl='E_NOTICE'; break;
			case 2048:			$color='#FFFF00'; $err_lvl='E_STRICT'; break;
			case 8192:			$color='#FFFF00'; $err_lvl='E_DEPRECATED'; break;
			case 16384:			$color='#FFFF00'; $err_lvl='E_USER_DEPRECATED'; break;
			
			case E_USER_NOTICE:	$color='#888888'; $err_lvl='E_USER_NOTICE'; break;
			
			default:			$color='#ffffff'; $err_lvl='unrecognized'; break;
		}
		$err_lvl.= ' ['.$errno.']'; 
		
		// stores error in an array format
		if( ((count($this->scripts_to_debug) == 0) || (in_array(basename($errfile), $this->scripts_to_debug))) && $this->allowedError($errno)) {
		
			// counts those errors that should be considered discrete
			if(
				$this->translateEquivalentLevel($this->equivalentErrorLevel($errno),'int') 
				<=
				$this->translateEquivalentLevel($this->discreteMode(),'int') 
				) {
				$this->n_bugs++;
			}
			
//			$avoid_k = array('GLOBALS', '_POST', '_GET', '_COOKIE', '_SESSION', 'argv', '_FILES');
//			foreach($errcontext as $k => $val)
//				if(!in_array($k, $avoid_k) && !is_a($val, 'Dubrox_PhpDebugger'))
//					$filtered_errcontext[$k] = $val;
				
			$this->collected[] = array(
				'errno' => $errno,
				'errlvl' => $err_lvl,
				'errstr' => $errstr,
				'errfile' => $errfile,
				'errline' => $errline,
				'errcolor' => $color,
//				'errcontext' => $filtered_errcontext,
			);
		}
		
		// halts script execution on error (if the corresponding flag has been set)
		if($this->haltOnError() && ($this->translateEquivalentLevel($this->equivalentErrorLevel($errno),'int') >= $this->translateEquivalentLevel('warning','int'))) {
			exit(
				$this->toHtml() .
				'HALT_ON_ERROR TRIGGERED: ['.$err_lvl.'] '.$errstr.' (on file '.$errfile.' line #'.$errline.')'
			);
		}
		
		return true;
	}
	
	/**
	 * Clears all logs about the specified filename.
	 * @param	string	$filename
	 * @return	void
	 */
	function clearLogs($filename) {
		exec('rm '. $this->log_dir . $filename .'*.html');
	}
	
	/**
	 * Clears all data about collected errors.
	 * 
	 * @return void
	 */
	function clearCollected() {
		$this->n_bugs = 0;
		$this->collected = array();
	}
	
	/**
	 * Writes a log text to a file.
	 * 
	 * @param string	$text
	 * @param string	$filename
	 * @return int Number of characters written
	 */
	function writeToLog($text, $filename) {
		$timestamp = ($this->one_log) ? '' : '-'.time();
		$output_file = $this->log_dir . $filename . '-' . $timestamp . '.html';
		return file_put_contents($output_file , $text);
	}
	
	/**
	 * Detects if it is necessary to output anything.
	 * 
	 * @return bool
	 */
	private function allowOutput() {
		return (
			$this->active && ( 
				($this->discreteMode() == 'any')
				|| 
				($this->n_bugs > 0) 
			) 
		);
	}
	
	/**
	 * Gives out the errors handled in a PHP array format.
	 * 
	 * @return string
	 */
	function toArray() {
		$output = array();
		if($this->allowOutput()) {
			$output = $this->collected;
		}
		return $output;
	}
	
	/**
	 * Gives out the errors handled in a JSON format.
	 * 
	 * @return string
	 */
	function toJson() {
		$output = '';
		if($this->allowOutput()) {
			$output = json_encode($this->collected);
		}
		return $output;
	}
	
	/**
	 * Gives out the errors handled in a HTML format.
	 * 
	 * @param $options
	 * @return string
	 */
	function toHtml($options = array()) {
		$output = '';
		if($this->allowOutput()) {
			$timestamp = time();
			
			ob_start();
			
			// includes CSS plugins
			$css_dir = $this->tools_dir.'css/';
			if(is_dir($css_dir)) {
				if ($dh = opendir($css_dir)) {
					while (($file = readdir($dh)) !== false) {
						if($file[0] == '.') continue;
						?> 
						<link type="text/css" rel="stylesheet" href="<?php echo $css_dir.$file; ?>" />
						<?php
					}
					closedir($dh);
				}
			}
			
			// includes Debugger JS
			?>
			<script type="text/javascript">
				var debug = <?php echo $this->debugLevel()?>;
			</script> <?php
			$js_dir = $this->tools_dir.'js/';
			if(is_dir($js_dir)) {
				if ($dh = opendir($js_dir)) {
					while (($file = readdir($dh)) !== false) {
						if($file[0] == '.') continue;
						?> 
						<script type="text/javascript" src="<?php echo $js_dir.$file; ?>"></script>
						<?php
					}
					closedir($dh);
				}
			}
			
			?>
			<div id="DubroxPhpDebuggerErrorsBox" name="DubroxPhpDebuggerErrorsBox" style="font-family:sans-serif; width:90%; background-color: #FFE4E1; border: 5px solid #BB3030; margin: 30px auto 100px; text-align:left; font-size:11px; overflow:auto; padding:2px;">
				<h1 style="font-size:20px; text-align:center;">Dubrox's PHP debugger <sup><a style="font-size:x-small; color:#aaa; text-decoration:none;" href="<?php echo Dubrox_PhpDebugger::site; ?>">v<?php echo Dubrox_PhpDebugger::version; ?></a></sup></h1>
				<p style="text-align:center; font-weight:bold;">
					time stamp: <?php echo $timestamp; ?> | 
					execution time (seconds): <?php echo microtime(true) - $this->debugging_time; ?></p>
				<p style="text-align:center;">
					# of bugs collected: <strong><?php echo $this->n_bugs; ?></strong> | 
					
					Debug level: <strong><?php echo $this->equivalentLevel; ?></strong> | 

					Error reporting: <strong><?php echo $this->error_reporting . ' ('. eval('return '.$this->error_reporting.';') .')'; ?></strong> | 
					
					Files to debug: <strong><?php echo (count($this->scripts_to_debug)) ? implode(', ',$this->scripts_to_debug) : 'all'; ?></strong> | 
					
					Persistent mode: <strong><?php echo ($this->persistentMode()) ? 'on' : 'off'; ?></strong> | 
					
					Halt on error: <strong><?php echo ($this->haltOnError()) ? $this->haltOnError() : 'off'; ?></strong> | 
					
					Discrete mode: <strong><?php echo $this->discreteMode(); ?></strong>
				</p>
				<?php
				foreach($this->collected as $r_error) { ?>
					<div style="border: 2px solid <?php echo $r_error['errcolor'] ?>;padding:2px;margin:5px;" title="Error level: <?php echo $r_error['errlvl'] ?>">
						<p>
							File: <?php echo $r_error['errfile'] ?> 
							(at line #<?php echo $r_error['errline'] ?>)
						</p> 
						<div class="error_message" style="max-height: 400px; overflow: auto;">
							<?php
							if( stristr($r_error['errstr'],'</') !== false ){
								echo $r_error['errstr'];
							} else { ?>
								<pre style="white-space: pre-wrap;"><?php echo htmlentities($r_error['errstr'], ENT_QUOTES, 'UTF-8'); ?></pre> <?php
							} ?>
						</div>
					</div>
					<?php
				}
				?>
			</div> 
			<?php
			$output = ob_get_clean();
			
			// eventually writes to a log file
			if($this->log_everything == true || isset($options['log_file'])) {
				$this->writeToLog($output, isset($options['log_file']) ? $options['log_file'] : 'log');
			}
		}
		
		return $output;
	}
}