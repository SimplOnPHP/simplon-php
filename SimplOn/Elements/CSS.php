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
namespace SimplOn\Elements;
use \SimplOn\Main,
\SimplOn\Elements\Element;

class CSS extends Element {
	const
	mime = 'text/css',
	type = 'CSS',
	default_dir = 'PerMethod';

	var $flavour, $directory, $file;

	static $files_exists = array();

	function __construct($flavour, $directory, $file) {
		$args = func_get_args();
		$this->flavour = array_shift($args);
		$this->directory = array_shift($args);
		$this->file = implode('/',$args);
	}

	static function compress() {

	}

	static function getPath($file, $flavour = null, $directory = null) {


		$token_id = $file.'|'.$flavour.'|'.$directory;
		if(!isset(self::$files_exists[$token_id])) {
			self::$files_exists[$token_id] = false;
			if(!$flavour) $flavour = Main::${ self::type .'_FLAVOUR'};
			if(!$directory) $directory = self::default_dir;

			$bases = array(
			'SimplOn' => Main::$SimplOn_PATH,
			'Site' => Main::$LOCAL_ROOT,
			);
			foreach($bases as $name => $base) {

				$filepath = $base . '/'. self::type .'/' . $flavour . '/' . $directory . '/' .$file;
				//echo "$filepath<br>";
				if(file_exists($filepath)) {
					self::$files_exists[$token_id] = $filepath;
					return $filepath;
				}else{
					list($uno,$dos,$tres) = explode('.', $file);
					if ($dos === 'showUpdate' || 'showAdmin' || 'showCreate' || 'showSearch') {
						$dos = 'showCreate';
						$file = implode('.', array($uno,$dos,$tres));
						$filepath = $base . '/'. self::type .'/' . $flavour . '/' . $directory . '/' .$file;
						if(file_exists($filepath)){
							self::$files_exists[$token_id] = $filepath;
							return $filepath;
						}
					}

				}



			}
		}

		return self::$files_exists[$token_id];
		var_dump(self::$files_exists[$token_id]);

	}

	static function getLibs($flavour = null) {
		if(!$flavour) $flavour = Main::${ self::type .'_FLAVOUR'};
		$bases = array(
		'SimplOn' => Main::$SimplOn_PATH,
		'Site' => Main::$LOCAL_ROOT,
		);
		$libs = array();
		foreach($bases as $name => $base) {
			$base_libs = glob($base . '/'. self::type .'/' . $flavour . '/Libs/*.'.strtolower(self::type));
			$libs = array_merge($libs, $base_libs);
		}
		$libs = array_unique($libs);
		sort($libs);
		return $libs;
	}

	function index() {
		header('Content-type: '. self::mime);
		readfile(\SimplOn\Main::$SimplOn_PATH.'/'. self::type .'/'.$this->flavour.'/'.$this->directory.'/'.$this->file);
	}
}