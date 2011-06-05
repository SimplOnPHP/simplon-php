<?php
namespace DOF;

/**
 *
 * @author Administrador
 *
 *


$db=new BaseDeDatos($host,$usr,$clave,$dbname);

$db->query($query);

$db->queryArreglo($query);

$db->queryArregloUni($query);

*$db->notNulls($query);

*/

class MySqlDataBase
{
	public $conexion;
	
	public function __construct($host,$usr,$pwd,$db)
	{
		if(!$this->conexion=mysql_connect($host,$usr,$pwd)){ throw new \Exception("Error wile connecting to server:  <br />".mysql_error()); }
		if(!mysql_select_db($db,$this->conexion) ){ throw new \Exception("Error selecting the data base: $db <br />".mysql_error()); }
			
	}
	
	function query($query)
	{
		/*?>alert--x--<?php check( $query ) ?>-x-x-<?*/
		if( trim($query) )
		{
			$result = mysql_query($query,$this->conexion);
	
			if( mysql_error() )
			{
				throw new \Exception("Can't excute the query:<br>\n$query<br>\n". mysql_error());
			}
	
			return $result;
		}
	}

	function queryAsArray($query)
	{
		$rowtotal = array();
		$resultado = $this->query($query);
		while ($row = mysql_fetch_assoc($resultado))
		{
			//foreach($row as &$cell)
			//{$cell=utf8_decode($cell);}
			$rowtotal[] = $row;
		}
		return ($rowtotal);
	}
	
	/**
	 * IMP Se puede hacer que si la matriz tiene una sola columna tambien la regrese como arrreglo unidimencionar y/o que si tiene varios renglos o columnas regrese el primer renglo o la primer columna
	 *
	 * @param $query
	 * @return unknown_type
	 */
	function queryAsUniArray($query)
	{
		$ret=$this->queryAsArray($query);
		
		if(sizeof($ret)==1)
		{
			return $ret[0];
		}else{
			
			foreach($ret as $row)
			{
				$ret2[]=array_shift($row);
			}
		}
		
		 return $ret2;
	}

	function notNulls($tabla)
	{
		$temp=$this->queryAsArray("SHOW COLUMNS FROM $tabla");
		foreach($temp as $row)
		{
			if($row['Null']=='NO')
			{
				$ret[]=$row['Field'];
			}
		}
		return $ret;
	}
		
	function  __destruct()
	{
		mysql_close($this->conexion);
	}
	
	
}