<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/
class SI_Link extends SI_Item {
	protected $href, $text, $onclick = '';
	
function __construct($href, $content, $icon = null, $Iconheight = '1.5em', $Iconwidth = '1.5em') {
		if($href){$this->addAttribute('href', $href);}
		$this->addClass('SI_Link');
		$this->content = $content;
		$this->icon = $icon;
		$this->addStylesToAutoCSS('.SI_Link .icon{
				height: '.$Iconheight.';
				width: '.$Iconwidth.';
		}');
	}

	function setTagsVals($renderVals = null) {
		if($renderVals['icon'] AND is_string($renderVals['content'])){
			$image = new SI_Image($renderVals['icon'],$renderVals['content']);
			$image->addClass('icon');
			$renderVals['content'] = $image;
		}
		$this->start = '<a '.$this->attributesString().'>';
		$this->end = "</a>\n";
	}
}


/**
 * Integer data type  
 * 
 * This is an integer data type which allow you show an input to introduce a integer number.
 * 
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */
class SD_Action extends SD_ComplexData {

	protected $methodToCall, $action;

	function __construct($label = null, $methodToCall, $text, $icon = null, $flags = null, $val = null, $filterCriteria = null){

		$dataPrepare = null;
		$this->methodToCall = $methodToCall;

		$layout = new SI_Link([$this,'action'], $text,$icon);
		
		parent::__construct($label, $dataPrepare, $layout, $flags, $val, $filterCriteria);

	}

	function action($action = null){
		if($action){$this->action = $action;}
		elseif(!$this->action){return SC_Main::$RENDERER->action($this->parent(),$this->methodToCall);}
		else{return $this->action;}
	}

    // function getLayout($method)
    // {
	// 	if(SC_Main::$PERMISSIONS instanceof SE_User){

	// 		$permissions = SC_Main::$PERMISSIONS->getPermissions($this->parent());

	// 		if($permissions == 'allow'){
	// 			//keep the same method for bellow;
	// 		}elseif($permissions == 'deny'){
	// 			$method = 'showEmpty';
	// 		}elseif(is_array($permissions)){
	// 			$actionMethod = strtolower(str_replace("show", "", $this->method)).'Action';
	// 			if(isset($permissions[$actionMethod])){
	// 				SC_Main::$PERMISSIONS->setCheckDataRule($this->parent(), $this, $permissions[$actionMethod]);
	// 			}
	// 		}
	// 	}
	// 	if(!empty($this->renderOverride) ){$method = $this->renderOverride;}
	// 	if(empty($this->icon)){
	// 		return $this->renderer()->getDataLayoutFromFile($this,$method);
	// 	}else{
	// 		return $this->renderer()->getDataLayoutFromFile($this,$method.'Icon');
	// 	}
    // }
}
