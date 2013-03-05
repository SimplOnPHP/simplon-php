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
namespace SimplOn\Datas;
/**
 * Message data type
 * 
 * This is a Message data type which allow displays a mensagge in the templates.  
 * 
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */
class Message extends Data
{
     /**
     *
     * Muestra
     * 
     * @var boolean $view, $create, $update, $search, $list, $required, 
     * $fetch - These variables are flags to indicate if this input will be
     * displayed in the different templates.
     * 
     * 
     */
	protected
	
		$view = true,

		$create = true,

		$update = true,

		$search = true,

		$list = false,

		$required = false,

		$fetch = false;
	
	
     /** function showInput - This function show the view.
     *  
     * @param boolean $fill
     * @return String
     */
	
	function showInput($fill)
	{ return $this->showView(); }
	
     /**
     *
     *  The functions doRead,doCreate,doUpdate,doSearch - Show values ​​obtained 
     *  from the database
     * 
     * @return string
     * 
     */         
	public function doRead()
	{}
	
	public function doCreate()
	{}
		
	public function doUpdate()
	{}

	public function doSearch()
	{}	
		
}
