<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

class SD_File extends SD_String {

	protected
		$path = null,
		$fileName = null;

		public function __construct($path = './files', $label=null, $flags=null, $val=null, $filterCriteria=null)
		{
			$this->path($path);
			parent::__construct($label, $flags, $val, $filterCriteria);
		}

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

		public function fileName($fileName = null) {
			if ($fileName !== null) {
				$this->fileName = $fileName;
			} else {
				if ($this->fileName === null && !empty($this->val)) {
					// Extract filename from the path stored in val
					$pathParts = explode(DIRECTORY_SEPARATOR, $this->val);
					$this->fileName = end($pathParts);
				}
				return $this->fileName;
			}
		}	

		public function doUpdate(){
			return ($this->update())
				? array(array($this->name(),$this->getClass(),$this->val()))
				: null;
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

		public function showCreate() {
			if($this->renderOverride()=='showEmpty'){return '';}elseif($this->fixedValue()) {$input =  new SI_FixedValue($this->name(), $this->viewVal());
			}else{
				$input = new SI_FileBox($this->name(), '',  $this->label(), $this->required(), $this->ObjectId());	
			}
			$inputBox = new (SC_Main::$RENDERER::$InputBox_type)($input, $this->label());
			return $inputBox;
		}
			
		public function showUpdate() {
			if($this->renderOverride()=='showEmpty'){return '';}elseif($this->fixedValue()) {$input =  new SI_FixedValue($this->name(), $this->viewVal());
			}elseif($this->val()){
				$input = $this->viewVal();

				$input = new class($this->name(), $this->viewVal(), 'file', $this->label(), $this->required(), $this->ObjectId()) extends SI_Input {	
					function setTagsVals($renderVals = null) {
						$href = $this->getAttribute('value');
						$name = basename($href);
						$link = new SI_Link($href, $name);
						$classTag= substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
						$link->addClass($classTag);

						$showInputLink = new SI_Link('#', 'Show', 'removeIcon.svg');
						$showInputLink->addClass($classTag);

						$showInputLink->addAttribute('onclick',"$('.$classTag').hide(); $('.".$classTag."2').show();");


						$input = new SI_FileBox($this->name(), $this->getAttribute('value'), $this->placeHolder(), $this->required(), $this->ObjectId());
						$input->addClass('hidden');
						$input->addClass($classTag.'2');
						$hideInputLink = new SI_Link('#', 'return', 'return.svg');
						$hideInputLink->addClass('hidden');
						$hideInputLink->addClass($classTag.'2');
						$hideInputLink->addAttribute('onclick',"$('.$classTag').show(); $('.".$classTag."2').hide();");


						$this->start = $link.$showInputLink.$input.$hideInputLink;
					}
				};
			}else{
				$input = new SI_FileBox($this->name(), $this->val(), $this->label(), $this->required(), $this->ObjectId());
			}

			$inputBox = new (SC_Main::$RENDERER::$InputBox_type)($input, $this->label());
			return $inputBox;
		}
	
		public function showDelete() {}
	
		public function showSearch() {
			if($this->renderOverride()=='showEmpty'){return '';}elseif($this->fixedValue()) {$input =  new SI_FixedValue($this->name(), $this->viewVal());
			}else{	
				$input = new SI_Input($this->name(), $this->val(), null, $this->label(), null, $this->ObjectId());
			}
			$inputBox = new (SC_Main::$RENDERER::$InputBox_type)($input, $this->label());
			return $inputBox;
		}
}

