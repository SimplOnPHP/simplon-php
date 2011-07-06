<?php
namespace DOF\DataStorages;

class MySql extends DataStorage
{
	/*@var db MySqlDataBase */
	public $db;
	
	static $typesMap = array(
		'Id'		=> 'INT NOT NULL AUTO_INCREMENT',
		'Integer'	=> 'int',
		'Float'  	=> 'float',
		
		'String'	=> 'varchar(240)',
		'HTMLText'	=> 'text',
	);

	public function __construct($server,$user,$password,$dataBase) {
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
	
	public function isValidElementStorage(\DOF\Elements\Element &$element) {
		
		// Verify that we have the same Datas in the element and in the DB
		$dbColumns = $this->db->query('SHOW COLUMNS FROM '.$element->storage(), \PDO::FETCH_COLUMN,0)->fetchAll();
		$elementData = $this->getDataTypes($element);
		
		if( !array_diff($dbColumns, array_keys($elementData) ) && !array_diff(array_keys($elementData), $dbColumns) ) //verifys that both element and DB have the same Data
		{
			foreach($this->db->query('SHOW COLUMNS FROM '.$element->storage())->fetchAll() as $dbColumn){
				
				//CheckType
				if(  $elementData[$dbColumn['Field']] != $dbColumn['Type'] ){ return false; }
				
				//CheckNull
				if(  
					(  $dbColumn['Null'] == 'YES' && $element->{'O'.$dbColumn['Field']()->required()}  )  
					OR
					(  $dbColumn['Null'] == 'NO' && !$element->{'O'.$dbColumn['Field']()->required()}   )
					
				  ){ return false; }
				  
				 //key @todo verify and improve
				 if( 
				 	(  $dbColumn['Key'] && $dbColumn['Key'] !='PRI' && !$element->{'O'.$dbColumn['Field']()->search()}  ) 
					OR
					(  $dbColumn['Key'] =='PRI' && $element->Fid() != $dbColumn['Field']   ) 
				   ){ return false; } 
				
			}
			
			return true;
		}
		return false;
	}
	
	public function ensureElementStorage(\DOF\Elements\Element &$element) {
		if($element->storageChecked()) {
			$return = $element->storageChecked();
		} else {
			if(!$this->isSetElementStorage($element)) {
				// @todo: Create Table ONLY IN DEVELOPMENT MODE
				
				// $this->db->query('CREATE SCHEMA `'.$element->storage.'`');
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
				
				$q = 'CREATE TABLE `'.$element->storage().'` (
					'.implode(', ',$q_data_part).',
					PRIMARY KEY (`'. $element->Fid() .'`)
					'.$q_index_part.'
				)';
				
				$return = $this->db->query($q);
			} else if(!$this->isValidElementStorage($element)) {
				// @todo: alter table ONLY IN DEVELOPMENT MODE
				$return = false;
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
		foreach(self::$typesMap as $class => $type)
		{
			if($array = $element->attributesTypes('\\DOF\\Datas\\'.$class)) {
				$result = array_merge($result, array_combine($array, array_fill(0, count($array), $type)));
			}
		}
		
		return $result;
	}
}