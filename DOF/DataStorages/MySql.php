<?php
namespace DOF\DataStorages;

class MySql extends SQL
{
	/*@var db MySqlDataBase */

	public function __construct($server,$dataBase,$user = 'root',$password = '') {
		$this->db = new \PDO(
			'mysql:dbname='.$dataBase.';host='.$server,
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