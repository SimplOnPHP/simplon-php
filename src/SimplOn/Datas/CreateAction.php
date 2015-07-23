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
 * CreateAction data type  
 * 
 * This is a create action data type which allow you show a direct link to showCreate.
 * 
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */
class CreateAction extends ElementLink {
    /**
     * 
     * function __construct - this construct just especify the method "showCreate"
     * to create the correct link to method 
     * 
     * @param string $label
     * @param array $sources
     * @param string $flags
     * @param null $searchOp
     */
    
    public function __construct($label, array $sources, $flags=null, $searchOp=null){
    	parent::__construct($label, $sources, 'showCreate', array(), $flags,null,$searchOp);
    }
    
    public function val($sources = null){
            $this->parent->clearId();
            $link = parent::val($sources);
            return $link;
    }
}
