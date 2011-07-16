<?php
namespace DOF\DataStorages;

abstract class SQL extends DataStorage
{
	/*@var db MySqlDataBase */
	public $db;
	
	static $typesMap = array(
		'Id'		=> 'int(11) auto_increment',
		'Integer'	=> 'int(11)',
		'Float'  	=> 'float',
		
		'String'	=> 'varchar(240)',
		'HTMLText'	=> 'text',
	);

	abstract function __construct($server,$dataBase,$user,$password);
	
	abstract function createDB($db_name);
	
	//@todo implement ORDER BY in a simple way
	public function getElementsData(&$element, $filters = null, $range = '0,500' ) {
		/*@var element Element*/
		
		if($element instanceof \DOF\Datas\Data) {
			$whatToGet = $this->getWhatFromElement($element);
			$fromWhere = $element->storage();
			if(!$filters){ $filters = $this->getFiltersFromElement($element); }else
			if($filters instanceof Filter){ $filters = $this->formatFilters($filters); }
			//if($range){ $range = $this->formatRange($range); }
			 
			return $this->db->query("Select ".$whatToGet." FROM ".$fromWhere." ".(($filters)?" WHERE ".$filters:'').' '.(($range)?" LIMIT ".$range:'') )->fetchAll();
			
		} else if(!is_string($where)) {
			throw new Exception(__CLASS__ . '->'. __METHOD__ .'() needs a valid DOF\Type\Element');
		}
	}
	
	public function formatFilters($filters)
	{
		if($filters instanceof DsEval) {
			foreach( $filters->operatorsOperadsArray() as $operator=>$operand ) {
				$ret[]= $DsEval->firstOperand().' '.$operator.' '.$operand;
			}
			
			return implode($DsEval->boolOperator(),$ret);
			
		} else if($filters instanceof DsBoolOp) {
			
			foreach($filters->operands() as $BoolOp)
			{
				$ret[] = $this->formatFilters($BoolOp);
			}
			return implode($filters->boolOperator(),$ret);
			
		} else {
			return $filters;
		}
	}
	

	public function saveElement(&$element) {
		if( $element->id() ) {
			$this->updateElement($element);
		} else {
			$this->createElement($element);
		}
	}
	
	public function createElement(&$element) {
		foreach($element->dataAttributes() as $dataKey)
		{
			$column = $element->{'F'.$dataKey}();
			
			$colums[] = $column;
			$values[':'.$column] = $element->$dataKey();
		}
		
		return $this->db->prepare('
			INSERT INTO '.$element->storage().' 
			('. implode(', ',$colums) .') 
			VALUES ('. implode(', ',array_keys($values)) .')
		')->execute($values);
	}
	
	public function updateElement(&$element) {
		foreach($element->dataAttributes() as $dataKey)
		{
			$column = $element->{'F'.$dataKey}();
			
			$sets[] = $column.'=:'.$column;
			$values[':'.$column] = $element->$dataKey();
		}
		$values[':'.$element->Fid()] = $element->id();
		
		return $this->db->prepare('
			UPDATE '.$element->storage().' 
			SET '. implode(', ',$sets) .'
			WHERE '. $element->Fid().'=:'.$element->Fid() .'
		')->execute($values);
	}
	
	public function deleteElement(&$element) {
		//check($this->deleteQuery($element, $element->Fid()."=".$element->id() ) );
		$this->db->query( $this->deleteQuery($element, $element->Fid()."=".$element->id() ) );
	}

	public function getElementData(&$element) {
		foreach($element->dataAttributes() as $dataKey)
		{
			$column = $element->{'F'.$dataKey}();
			
			$columns[] = $column;
		}
		$values[':'.$element->Fid()] = $element->id();
		
		$query = $this->db->prepare('
			SELECT '.implode(', ',$columns).' 
			FROM '.$element->storage().' 
			WHERE '. $element->Fid().'=:'.$element->Fid() .'
			LIMIT 1
		');
		$query->execute($values);
		return $query->fetch();
	}
	
	
	
	
	
//--------------SQL dependant
	public function processRange($range)
	{
		return 'LIMIT ' . $range;
	}
	
	public function processConditions($conditions)
	{
		return $conditions;
	}

	public function deleteQuery( &$element,$conditions=null )
	{
		if(!$conditions){
			foreach($element->dataAttributes() as $dataKey)
			{
				$value = $element->$dataKey();
				
				$objectKey = 'O'.$dataKey;
				
				//if( !($element->$objectKey() instanceof Id) ){
					if($this->evalQuotesUse($element->$objectKey()) && $value ){
						$value="'$value'";
						//@todo implement escape quotes to allow the use of single quotes in the string
						//@todo implement anti injection code
					}
					if( $value===0 ){ $value='0'; }else
					if( empty($value) ){ $value='NULL'; }
		
					$fieldKey = 'F'.$dataKey;
					
					$conditions.= ' AND '.$element->$fieldKey().'='.$value;
				//}
			}
		}
		
		return "DELETE FROM ".$element->storage().' '.(($conditions)?" WHERE ".$conditions:'') ;
	}
		
	
	
	public function evalQuotesUse(&$data)
	{
		if (  strpos($data->sqlType(),'INT' ) !== false  ){ return false; }else
		if (  strpos($data->sqlType(),'FLOAT' ) !== false  ){ return false; }else
		if (  strpos($data->sqlType(),'DECIMAL' ) !== false  ){ return false; }else
		if (  strpos($data->sqlType(),'DOUBLE' ) !== false  ){ return false; }else
		if (  strpos($data->sqlType(),'INT' ) !== false  ){ return false; }else
		{return true; }
	}
	
	public function isSetElementStorage(\DOF\Elements\Element &$element) {
		return in_array($element->storage(), $this->db->query('SHOW TABLES', \PDO::FETCH_COLUMN,0)->fetchAll());
	}
	
	public function alterTable(\DOF\Elements\Element &$element) {
		// Verify that we have the same Datas in the element and in the DB
		$dbColumns = array();
		$dbIndexes = array();
		$elementData = $this->getDataTypes($element);
		$elementDataKeys = array_keys($elementData);
		
		$alters = array();
		
		foreach($this->db->query('SHOW COLUMNS FROM '.$element->storage())->fetchAll() as $dbColumn){
			if(!in_array($dbColumn['Field'], $elementDataKeys)) {
				// DROP COLUMN
				$alters[] = 'DROP COLUMN `'.$dbColumn['Field'].'`';
			} else {
				$dbColumns[$dbColumn['Field']] = $dbColumn;
			}
		}
		
		foreach($this->db->query('SHOW INDEXES FROM '.$element->storage())->fetchAll() as $dbIndex){
			//if( $dbIndex['Column_name']!=$element->Fid() && !$element->{'O'.$dbIndex['Column_name']}()->search() ) {
				// DROP INDEX
				$alters[] = 'DROP INDEX `'.$dbIndex['Key_name'].'`';
				/*
			} else {
				$dbIndexes[$dbColumn['Column_name']] = $dbIndex;
			}*/
		}
		
		$dbColumnsKeys = array_keys($dbColumns);
		foreach($elementData as $dataName => $dataType) {
			$flags = $dataType.' '.($element->{'O'.$dataName}()->required() ? 'NOT NULL' : 'NULL');
						
			if(!in_array($dataName, $dbColumnsKeys)) {
				// ADD COLUMN
				$alters[] = 'ADD COLUMN `'.$dataName.'` '.$flags;
			} else {
				// ALTER COLUMN
				$alters[] = 'CHANGE COLUMN `'.$dataName.'` `'.$dataName.'` '.$flags;
			}
			
			//ADD INDEXES
			if($dataName!=$element->Fid() && $element->{'O'.$dataName}()->search() ) {
				$alters[] = 'ADD INDEX `Index'.$dataName.'` (`'.$dataName.'` ASC)';
			}
		}
		
		$alters[] = 'ADD PRIMARY KEY (`'.$element->Fid().'`)';
		
		$q = 'ALTER TABLE `'.$element->storage().'` '.implode(', ', $alters);
		var_dump($q);
		return $this->db->query($q);
	}
	
	public function isValidElementStorage(\DOF\Elements\Element &$element) {
		
		// Verify that we have the same Datas in the element and in the DB
		$dbColumns = $this->db->query('SHOW COLUMNS FROM '.$element->storage(), \PDO::FETCH_COLUMN,0)->fetchAll();
		$elementData = $this->getDataTypes($element);
		$return = true;
		
		if( count($dbColumns) == count($elementData) && !array_diff($dbColumns, array_keys($elementData) ) ) //verifys that both element and DB have the same Data
		{
			
			foreach($this->db->query('SHOW COLUMNS FROM '.$element->storage())->fetchAll() as $dbColumn){
				
				//Check Auto-increment
				if( (
						(stripos($dbColumn['Extra'], 'auto_increment') !== false)
						XOR
						(stripos($elementData[$dbColumn['Field']], 'auto_increment' ) !== false)
					)) {
					user_error(
						$dbColumn['Field'].' data Auto-Increment ('.$elementData[$dbColumn['Field']].') does not match Storage ('.$dbColumn['Extra'].')'
					,E_USER_WARNING);
					$return = false;
				}
				
				//Check Types
				if( strpos($elementData[$dbColumn['Field']], $dbColumn['Type']) === false ){
					user_error(
						$dbColumn['Field'].' data type ('.$elementData[$dbColumn['Field']].') does not match Storage data type ('.$dbColumn['Type'].')'
					,E_USER_WARNING);
					$return = false;
				}
				
				//Check Required fields
				if( $dbColumn['Null'] == 'NO' XOR $element->{'O'.$dbColumn['Field']}()->required() ){
					user_error(
						$dbColumn['Field'].' data has a Requirement inconsistency:
						'.$dbColumn['Field'].' -> '. ($element->{'O'.$dbColumn['Field']}()->required() ? 'required' : 'not required') .'
						Storage -> '. (($dbColumn['Null'] == 'YES') ? 'not required' : 'required')
					,E_USER_WARNING);
					$return = false;
				}
				  
				 //Check Indexes
				 if(
				 		($element->Fid()==$dbColumn['Field'] && $dbColumn['Key']!='PRI')
					 	OR
					 	($dbColumn['Key']=='PRI' && $element->Fid()!=$dbColumn['Field'])
					) {
				 	user_error(
						$dbColumn['Field'].' data has a Primary Key inconsistency:
						'.$dbColumn['Field'].' -> '. (($element->{'O'.$dbColumn['Field']}()->search() || $element->Fid() == $dbColumn['Field']) ? 'index' : 'not index') .'
						Storage -> '. $dbColumn['Key']
					,E_USER_WARNING);
					$return = false;
				 }
				 
				 if( ($dbColumn['Key'] XOR $element->{'O'.$dbColumn['Field']}()->search()) && ($dbColumn['Key']!='PRI') ){
					user_error(
						$dbColumn['Field'].' data has a Indexes inconsistency:
						'.$dbColumn['Field'].' -> '. (($element->{'O'.$dbColumn['Field']}()->search() || $element->Fid() == $dbColumn['Field']) ? 'index' : 'not index') .'
						Storage -> '. ($dbColumn['Key']?'index':'not index')
					,E_USER_WARNING);
					$return = false;
				 }

			}
			
			//user_error($return ? 'Valid Storage' : 'Invalid Storage');
			return $return;
		}

		user_error(
			$element->getClass().' structure mismatch with Data Storage:
			Element '.$element->getClass().' -> '.print_r(array_keys($elementData),1).'
			Storage '.$element->storage().' -> '.print_r($dbColumns,1)
		,E_USER_WARNING);
		return false;
	}

	public function createTable($element) {
		// $this->db->query('CREATE SCHEMA `'.$element->storage.'`');
		/*
		foreach($this->getDataTypes($element) as $data => $type) {
			$q_data_part[]= "`$data` $type";
		}
		foreach($element->dataAttributes() as $dataName) {
			if($element->{'O'.$dataName}()->search()) {
				$q_index_part[]= "`Index$dataName` (`$dataName` ASC)";
			}
		}
		if(isset($q_index_part)) {
			$q_index_part = ',INDEX '.implode(', ',$q_index_part);
		}
		/*/
		$dataType=$this->getDataTypes($element);
		$dataType=$dataType[$element->Fid()];
		
		$q_data_part[]= ' `'.$element->Fid().'` '.$dataType.' NOT NULL';
			 
		/**/
		$q = 'CREATE TABLE `'.$element->storage().'` (
			'.implode(', ',$q_data_part).',
			PRIMARY KEY (`'. $element->Fid() .'`)
			'.@$q_index_part.'
		)';
		
		if($this->db->query($q)) {
			return $this->alterTable($element);
		} else {
			return false;
		}
	}
	
	public function ensureElementStorage(\DOF\Elements\Element &$element) {
		if($element->storageChecked()) {
			$return = $element->storageChecked();
		} else {
			if(!$this->isSetElementStorage($element)) {
				// @todo: Create Table ONLY IN DEVELOPMENT MODE
				if(true) {
					$return = $this->createTable($element);
				} else {
					$return = false;
				}
			} else if(!$this->isValidElementStorage($element)) {
				// @todo: alter table ONLY IN DEVELOPMENT MODE
				if(true) {
					$return = $this->alterTable($element) && $this->isValidElementStorage($element);
				} else {
					$return = false;
				}
			} else {
				$return = true;
			}
			$element->storageChecked($return);
		}
		return $return;
	}

	public function getDataTypes(\DOF\Elements\Element &$element) {
		// @todo: check
		$result = array();
		$typesMap = array_reverse(self::$typesMap, true);
		foreach($typesMap as $class => $type)
		{
			if($array = $element->attributesTypes('\\DOF\\Datas\\'.$class)) {
				$result = array_merge($result, array_combine($array, array_fill(0, count($array), $type)));
			}
		}
		
		return $result;
	}
}