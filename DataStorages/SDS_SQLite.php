<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
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