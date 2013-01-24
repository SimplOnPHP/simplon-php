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
namespace SimplOn\DataStorages;

class MySql extends SQL
{
	/*@var db MySqlDataBase */
    
	public function __construct($host = 'localhost', $dataBase = 'sample_site', $user = 'root',$password = '') {
		try {
		
			$this->db = new \PDO(
				'mysql:dbname='.$dataBase.';host='.$host,
				$user,
				$password,
				array(
						\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
						\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
						\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
			));
			
		} catch (\PDOException $e) {
			
			if($e->getCode()==1049){
			
				try {
					$this->db = new \PDO("mysql:host=$host", $user, $password);
					
					$userhost = '\''.$user.'\'@\''.$host.'\'';
					
					if(!$this->db->exec('CREATE DATABASE `'.$dataBase.'`'))
						throw new \Exception('Could not create database "'.$dataBase.'": '.$this->db->errorInfo()[2]);
					// shouldn't be necessary to grant all, as if it could create it, the user already has sufficient priviledges
					$this->db->exec('GRANT ALL ON `'.$dataBase.'`.* TO '.$userhost);
					$this->db->exec('FLUSH PRIVILEDGES');
					$this->db->exec('USE `'.$dataBase.'`');

				} catch (\PDOException $e) {
					throw $e;
				}
				
			}else{
				throw $e;
			}
		}
	}
	
	function createDB($db_name) {
		// CREATES A MYSQL TABLE
	}
}