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


class SDS_SQLite extends SQL
{
	/*@var db MySqlDataBase */
	
	static $typesMap = array(
		'Id'		=> 'int(11) not null auto_increment',
		'Integer'	=> 'int(11)',
		'Float'  	=> 'float',
		
		'String'	=> 'varchar(240)',
		'HTMLText'	=> 'text',
	);

	public function __construct($server, $db_name, $user = 'root', $password = '') {
		if(!file_exists($db_name))
			$this->createDB($db_name); 
		
		$this->db = new \PDO(
			'sqlite:'.$db_name,
			$user, 
			$password,
			array(
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		));
	}
	
	function createDB($db_name) {
		$error = '';
		try {
			//create or open the database
			return new \SQLiteDatabase($db_name, 0666, $error);
		} catch(\Exception $e) {
			user_error($error, E_USER_ERROR);
		}
	}
}