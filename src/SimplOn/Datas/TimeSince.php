<?php
/*
	Copyright © 2015 Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
	
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

use SimplOn\Exception;
use \SimplOn\Main;

/**
 * TimeSince data type
 *
 * This is a simplon link data type, this kind of data allows create a direct link to
 * an specific method or to an specific class from another element.
 *
 * @author Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
 * @copyright (c) 2011, Rubén Schaffer Levine and Luca Lauretta
 * @category Data
 */
class TimeSince extends ComplexData {

    /**
     * @param null|string $label
     * @param Date $source
     * @param string $viewFormat Format as http://php.net/manual/en/dateinterval.format.php
     * @param null|string $flags
     * @param null|string $searchOp
     * @throws Exception
     */
    public function __construct($label, $source, $viewFormat = '%d days, %m months, %y years', $flags=null, $searchOp=null){
        $this->view_format = $viewFormat;

        if($source instanceof Date) {
            parent::__construct($label, array($source), $flags, $searchOp);
        } else {
            throw new Exception('Source must be of type \\SimplOn\\Data\\Date.');
        }

    }

    /**
     * @param Date $sources
     * @return string
     */
    public function val($sources = null){
        /** @var Date $date_from */
        $date_from = $this->sources[0];
        $date_time = \DateTime::createFromFormat($date_from->dbFormat, $date_from->val());

        if($date_time instanceof \DateTimeInterface) {
            $time_now = new \DateTime();
            $time_since = $time_now->diff($date_time);
            return $time_since->format( $this->view_format );
        }

        return '';
    }

}