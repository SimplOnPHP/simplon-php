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

class JS extends Element {
	const
		mime = 'text/javascript', 
		type = 'JS', 
		default_dir = 'Inits';
	
	var $flavour, $directory, $file;
	
	static $files_exists = array();
	
	function __construct($flavour, $directory, $file) {
		$this->flavour = $flavour;
		$this->directory = $directory;
		$this->file = $file;
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
				if(file_exists($filepath)) {
					self::$files_exists[$token_id] = $filepath;
					return $filepath;
				}
			}
		}
		return self::$files_exists[$token_id];
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
		include(\SimplOn\Main::$SimplOn_PATH.'/'. self::type .'/'.$this->flavour.'/'.$this->directory.'/'.$this->file);
	}
}