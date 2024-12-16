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

class SD_File extends SD_String {

	protected
		$path = null,
		$fileName = null;


		function val($val = null) {
			if(!empty($val)){
				// Check if it's an array, meaning it's likely from $_FILES (an upload)
				// name: 'graph.png'
				// full_path: 'graph.png'
				// type: 'image/png'
				// tmp_name: '/tmp/phpX78t0xM'
				// error: 0
				// size: 751519
				if (is_array($val)) {
					// Check if the file was uploaded and a destination path is set
					if (!empty($val['tmp_name']) && !empty($this->path())) {					
						if (!move_uploaded_file($val['tmp_name'], $this->path() . DIRECTORY_SEPARATOR . basename($val['name']))) {
							throw new SC_DataValidationException('The file could not be copied');
						}else{
							$this->val($this->path() . DIRECTORY_SEPARATOR . basename($val['name']));
							$this->fileName(basename($val['name']));
						}
					}elseif(empty($val['tmp_name'])){
						throw new SC_DataValidationException('The file could not be uploaded');
					}else{
						throw new SC_DataValidationException('Wrong asignation');
					}
				}else{
					$this->val = $val;
					$fileName = end(explode(DIRECTORY_SEPARATOR,$val));
					$this->fileName($fileName);
				}
			} else {
				return $this->val;
			}
		}

		// function downloadVal(){
		// 	return $this->downloadPath().DIRECTORY_SEPARATOR.$this->fileName();
		// }

		function path($path = null) {
			if (!empty($path)) {
				// Check if the directory exists, if not, try to create it
				if (!is_dir($path)) {
					// Attempt to create the directory, set permissions to 0755 by default
					if (mkdir($path, 0755, true)) {
						$this->path = $path;  // Set the path if directory creation succeeds
					} else {
						throw new SC_DataValidationException('The file path cloud not be created '.$path);
					}
				} else {
					// If the directory exists, set the path
					$this->path = $path;
				}
			} else {
				// Return the current path if no argument is passed
				return $this->path;
			}
		}

}

