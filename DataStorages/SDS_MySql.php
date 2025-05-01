<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/


class SDS_MySql extends SDS_SQL
{
	/*@var db MySqlDataBase */
    
	public function __construct($host = 'localhost', $dataBase = 'sample_site', $user = 'root', $password = '') {

        try {    
            $this->db = new \PDO(
            'mysql:host='.$host.';dbname='.$dataBase.';charset=utf8mb4',
            $user,
            $password,
            array(
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ));
        } catch (\PDOException $e) {
            
            if($e->getCode()==1049){
            
                try {
                    $this->db = new \PDO("mysql:host=$host", $user, $password);

                    $this->db->exec("CREATE DATABASE `$dataBase`;
                            CREATE USER '$user'@'localhost' IDENTIFIED BY '$password';
                            GRANT ALL ON `$dataBase`.* TO '$user'@'localhost';
                            FLUSH PRIVILEGES;")
                    or die(print_r($this->db->errorInfo(), true));
                    
                    $this->db->exec("use $dataBase")
                    or die(print_r($this->db->errorInfo(), true));

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