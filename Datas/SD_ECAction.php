<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

/**
 * Integer data type  
 * 
 * This is an integer data type which allow you show an input to introduce a integer number.
 * 
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */
class SD_ECAction extends SD_ComplexData {

	protected $methodToCall, $action, $ElementContainer;

	function __construct($label = null, $ElementContainer, $methodToCall, $text, $icon = null, $flags = null, $val = null, $filterCriteria = null){

		$dataPrepare = null;
		$this->methodToCall = $methodToCall;

        $this->ElementContainer = $ElementContainer;
		$layout = new SI_AjaxLink([$this,'action'], $text,$icon);

		parent::__construct($label, $dataPrepare, $layout, $flags, $val, $filterCriteria);

	}

	function action($action = null){
		if($action){$this->action = $action;}
		elseif(!$this->action){ 
            
            $elementId = $this->parent()->getId() ? $this->parent()->getId() : null;

			if($this->ElementContainer->tagId()){ @$methodVars[] = $this->ElementContainer->tagId();}
			if($this->ElementContainer->name()){ @$methodVars[] = $this->ElementContainer->name();}

            return
            SC_Main::$RENDERER->encodeURL(
                $this->ElementContainer->getClass(),
                [$this->ElementContainer->element()->getClass(),$elementId],
                $this->methodToCall,
                @$methodVars
            );
        }else{return $this->action;}
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
