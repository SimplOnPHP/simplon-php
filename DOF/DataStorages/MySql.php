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
namespace DOF\DataStorages;

class MySql extends SQL
{
	/*@var db MySqlDataBase */

	public function __construct($host = 'localhost', $dataBase = 'sample_site', $user = 'root',$password = '') {
		$this->db = new \PDO(
			'mysql:dbname='.$dataBase.';host='.$host,
			$user,
			$password,
			array(
				\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		));
	}
	
	function createDB($db_name) {
		// CREATES A MYSQL TABLE
	}
}