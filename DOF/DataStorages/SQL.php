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
		'ElementContainer' => '_ForeignKey_',
	);
	
	static $operandsMap = array(
		'==' => '=',
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
			throw new Exception(__CLASS__ . '->'. __FUNCTION__ .'() needs a valid DOF\Type\Element');
		}
	}

	public function readElement(&$element) {
		$values[':'.$element->field_id()] = $element->{$element->field_id()}();
		
		foreach($element->processData('doRead') as $dataInfo){
			foreach($dataInfo as $fieldInfo){
				$fields[] = $fieldInfo[0];
			}
		}
		
		$query = $this->db->prepare('
			SELECT '.implode(', ',$fields).' 
			FROM '.$element->storage().' 
			WHERE '. $element->field_id().'=:'.$element->field_id() .'
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
	
	public function isSetElementStorage(\DOF\Elements\Element &$element) {
		return in_array(strtolower($element->storage()), array_map('strtolower', $this->db->query('SHOW TABLES', \PDO::FETCH_COLUMN,0)->fetchAll()));
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
			//if( $dbIndex['Column_name']!=$element->field_id() && !$element->{'O'.$dbIndex['Column_name']}()->search() ) {
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
			if($dataName!=$element->field_id() && $element->{'O'.$dataName}()->search() ) {
				$alters[] = 'ADD INDEX `Index'.$dataName.'` (`'.$dataName.'` ASC)';
			}
		}
		
		$alters[] = 'ADD PRIMARY KEY (`'.$element->field_id().'`)';
		
		$q = 'ALTER TABLE `'.$element->storage().'` '.implode(', ', $alters);
		// var_dump($q);
		return $this->db->query($q);
	}
	
	//@todo: in  arrays format
	public function isValidElementStorage(\DOF\Elements\Element &$element) {
		
		// Verify that we have the same Datas in the element and in the DB
		$dbColumns = $this->db->query('SHOW COLUMNS FROM '.$element->storage(), \PDO::FETCH_COLUMN,0)->fetchAll();
		$elementData = $this->getDataTypes($element);
		$return = true;
		
		//verifys that both element and DB have the same Data
		if( count($dbColumns) == count($elementData) && !array_diff($dbColumns, array_keys($elementData)) )
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
				 		($element->field_id()==$dbColumn['Field'] && $dbColumn['Key']!='PRI')
					 	OR
					 	($dbColumn['Key']=='PRI' && $element->field_id()!=$dbColumn['Field'])
					) {
				 	user_error(
						$dbColumn['Field'].' data has a Primary Key inconsistency:
						'.$dbColumn['Field'].' -> '. (($element->{'O'.$dbColumn['Field']}()->search() || $element->field_id() == $dbColumn['Field']) ? 'index' : 'not index') .'
						Storage -> '. $dbColumn['Key']
					,E_USER_WARNING);
					$return = false;
				 }
				 
				 if( ($dbColumn['Key'] XOR $element->{'O'.$dbColumn['Field']}()->search()) && ($dbColumn['Key']!='PRI') ){
					user_error(
						$dbColumn['Field'].' data has a Indexes inconsistency:
						'.$dbColumn['Field'].' -> '. (($element->{'O'.$dbColumn['Field']}()->search() || $element->field_id() == $dbColumn['Field']) ? 'index' : 'not index') .'
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
		// $this->db->query('CREATE SCHEMA `'.$element->storage().'`');
		/* *
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
		$dataType=$dataType[$element->field_id()];
		
		$q_data_part[]= ' `'.$element->field_id().'` '.$dataType.' NOT NULL';
			 
		/* */
		$q = 'CREATE TABLE `'.$element->storage().'` (
			'.implode(', ',$q_data_part).',
			PRIMARY KEY (`'. $element->field_id() .'`)
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
			if($attr_types = $element->attributesTypes('\\DOF\\Datas\\'.$class)) {
			//if($attr_types = $element->processData('doRead')) {
				// @todo: continue from here! (new instance_of :)			
				if($type == '_ForeignKey_') {
					foreach($attr_types as $attr){
						$encapsuledElement = $element->{'O'.$attr}()->element();
						$encapsuledElementDatasTypes = $this->getDataTypes($encapsuledElement);
						$result[$attr] = str_replace('auto_increment', '', $encapsuledElementDatasTypes[$encapsuledElement->field_id()]);
					}
				} else {
					$result = array_merge($result, array_combine($attr_types, array_fill(0, count($attr_types), $type)));
				}
			}
		}
		
		return $result;
	}
	
	public function deleteElement(\DOF\Elements\Element &$element) {
		$field_id = $element->field_id();
		return $this->db->prepare('
			DELETE FROM '.$element->storage().' WHERE '.$field_id.' = :'.$field_id.'
		')->execute(array(
			':'.$field_id => $element->$field_id()
		));
	}
	
	function createElement (\DOF\Elements\Element &$element) {
		$datas=$element->processData('doCreate');
		
		
		$new_datas = array();
		foreach($datas as $data) {
			$new_datas = array_merge($new_datas, $data);
		}
		$datas = $new_datas;
		
		$columns = array();
		foreach($datas as $data)
		{
			list($column, $class, $value) = $data;
			if(!in_array($column, $columns)) {
				$columns[] = $column;
				$values[':'.$column] = $value;
			}
		}
		
		$prepared = $this->db->prepare('
			INSERT INTO '.$element->storage().' 
			('. implode(', ',$columns) .') 
			VALUES ('. implode(', ',array_keys($values)) .')
		');
		
		$this->db->beginTransaction();
		$prepared->execute($values);
		
		// @todo: place an alternative for MSSQL
		$id = $this->db->lastInsertId();
			
		if($this->db->commit()) {
			return $id;
		} else {
			return false;
		}
	}
	
	
	function updateElement(\DOF\Elements\Element &$element) {
		// @todo: evaluate if it's more convenient to merge the array
		$field_id = $element->field_id();
		
		foreach($element->processData('doUpdate') as $datas){
			foreach($datas as $data){
				if($data[0] != $field_id) {
					$sets[] = $data[0].'=:'.$data[0];
				}
				$values[':'.$data[0]] = $data[2];
			}
		}
		
		return $this->db->prepare('
			UPDATE '.$element->storage().' 
			SET '. implode(', ',$sets) .'
			WHERE '. $field_id.'=:'.$field_id .'
		')->execute($values);
	}

	public function readElements(\DOF\Elements\Element &$element){
		$storages = is_array($element->storage()) ? $element->storage() : array($element->storage());
		
		foreach($element->processData('doRead') as $dataInfo){
			foreach($dataInfo as $fieldInfo){
				$fields[] = $fieldInfo[0];
			}
		}
		
		foreach($storages as $storage) {
			$storage_fields = $fields; // id as id, 'id' as field_id, 'fe' as storage
			array_unshift($storage_fields,
				'"'.$storage.'" as DOF_storage', 
				'"'.$element->field_id().'" as DOF_field_id', 
				$element->field_id().' as DOF_id');
			$where = $this->filterCriteria($element);
			$selects[] = '(SELECT '.implode(', ', $storage_fields).' FROM '.$storage.' '. ($where ? 'WHERE '.$where : '') .')';
		}
		
		
		
		// @todo: where and order by (and limit)
		$query = implode(' UNION ', $selects).'
			ORDER BY DOF_id desc
		';
		var_dump($query);
		$query = $this->db->prepare($query);
		
		
		foreach($element->processData('doSearch') as $dataInfo){
			foreach($dataInfo as $fieldInfo){
				$values[':'.$fieldInfo[0]] = $fieldInfo[2];
			}
		}
		$query->execute($values);
		var_dump($values);
		return $query->fetch();
	}

	public function filterCriteria(\DOF\Elements\Element &$element){
		$filterCriteria = $element->filterCriteria();
		
		$patterns = array();
		$subs = array();
		foreach(self::$operandsMap as $op => $sqlOp){
			// Regexp thanks to Jens: http://stackoverflow.com/questions/6462578/alternative-to-regex-match-all-instances-not-inside-quotes/6464500#6464500
			$patterns[] = '/('.$op.')(?=([^"\\\\]*(\\\\.|"([^"\\\\]*\\\\.)*[^"\\\\]*"))*[^"]*$)/';
			$subs[] = $sqlOp;
		}
		
		// Specials (for LIKE statement)
		$patterns[] = '/~= *(:[a-zA-Z0-9]+)/';
		$subs[] = 'LIKE "%$1%"';
		$patterns[] = '/~= *("(([^"\\\\]*\\\\.)*[^"\\\\]*)")/';
		$subs[] = 'LIKE "%$2%"';
		
		$patterns[] = '/\^= *(:[a-zA-Z0-9]+)/';
		$subs[] = 'LIKE "$1%"';
		$patterns[] = '/\^= *("(([^"\\\\]*\\\\.)*[^"\\\\]*)")/';
		$subs[] = 'LIKE "$2%"';
		
		$patterns[] = '/\$= *(:[a-zA-Z0-9]+)/';
		$subs[] = 'LIKE "%$1"';
		$patterns[] = '/\$= *("(([^"\\\\]*\\\\.)*[^"\\\\]*)")/';
		$subs[] = 'LIKE "%$2"';
		
		// @todo: this thing should return an array with the query and the corresponding values
		return preg_replace($patterns, $subs, $filterCriteria);
	}










}