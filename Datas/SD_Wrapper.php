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

/**
 * Integer data type  
 * 
 * This is an integer data type which allow you show an input to introduce a integer number.
 * 
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */
use voku\helper\HtmlDomParser;

class SD_Wrapper extends SD_Data {

	protected 
		$object,	
		$view = true,
		$create = false,
		$update = false,
		$list = false,
		$fetch = false,
		$required = false,
		$search = false,
		$sources = array(),
		/** @var SC_Element $parent  */
		$parent = null;

	public function  __construct($object, $label=null, $flags=null)
	{
		$this->object = $object;
		parent::__construct($label, $flags);
	}


	public function parent($parent = null)
	{
		if($parent){
			if (property_exists($this->object, 'parent') OR is_callable([$item,'parent'])) {
				$this->object->parent($parent);
			}else{
				$this->parent = $parent;
			}
		}else{
			if (property_exists($this->object, 'parent')) {
				return $this->object->parent();
			}else{
				return $this->parent;
			}
		}
	}

    public function __call($name, $arguments)
    {
		$parentMethod = [get_parent_class($this), $name];
		$objectMethod = [$this->object, $name];

		if($this->object instanceof SI_Container){
			try{
				return call_user_func_array($objectMethod, $arguments);
			}catch(Exception $e){
				return call_user_func_array($parentMethod, $arguments);
			}
		}elseif($this->object instanceof SD_Data){
			if (str_starts_with($name, 'show')) { 
				//TODO check  What other methods should give priority to the object method
				// Objects method has priority over parent method
				try{
					return call_user_func_array($objectMethod, $arguments);
				}catch(Exception $e){
					return call_user_func_array($parentMethod, $arguments);
				}
			} else {
				// Wraper method has priority over parent method
				try{
					return call_user_func_array($parentMethod, $arguments);
				}catch(Exception $e){
					return call_user_func_array($objectMethod, $arguments);
				}
			}
		}
    }

	function getLayout($method){

		if($this->object instanceof SD_Data){
			$this->object->parent = $this->parent;
			return $this->renderer()->getDataLayout($this->object, $method);
		}elseif($this->object instanceof SI_Item){
			return $this->renderer()->getItemLayout($this->object);
		}elseif($this->object instanceof SI_Container){
			return $this->renderer()->getContainerLayout($this->object, $method);
		}
	}

	function fillLayout($dom){
		return $this->renderer()->fillDomWithObject($this->object,$dom->html());
	}


}
