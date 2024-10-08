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

abstract class SDS_SQL extends SDS_DataStorage
{
	/*@var db MySqlDataBase */
	public $db;
	
	static $typesMap = array(
		'SD_NumericId'		=> 'int(11) auto_increment',
		'SD_StringId'    	=> 'varchar(240)',
        
		'SD_Integer'   	=> 'int(11)',
		'SD_Float'         => 'float',
		
		'SD_TimeStamp'     => 'timestamp',
		'SD_Date'          => 'date',
		'SD_DateTime'      => 'timestamp',
		
		'SD_String'    	=> 'varchar(240)',
				
		'SD_Text'      => 'text',
		'SD_ElementContainer' => '_ForeignKey_',
	);
	
	static $operandsMap = array(
		'==' => '=',
	);

	abstract function __construct($server,$dataBase,$user,$password);
	
	abstract function createDB($db_name);
	
	//@todo implement ORDER BY in a simple way
	public function getElementsData(&$element, $filters = null, $range = '0,500' ) {
		/*@var element Element*/
		
		if($element instanceof SD_Data) {
			$whatToGet = $this->getWhatFromElement($element);
			$fromWhere = $element->storage();
			if(!$filters){ $filters = $this->getFiltersFromElement($element); }else
			if($filters instanceof Filter){ $filters = $this->formatFilters($filters); }
			//if($range){ $range = $this->formatRange($range); }
			 
			return $this->db->query("Select ".$whatToGet." FROM ".$fromWhere." ".(($filters)?" WHERE ".$filters:'').' '.(($range)?" LIMIT ".$range:'') )->fetchAll();
			
		} else if(!is_string($where)) {
			throw new \Exception(__CLASS__ . '->'. __FUNCTION__ .'() needs a valid SimplOn\Type\Element');
		}
	}

	public function readElement(&$element) {
		$values[':'.$element->fieldId()] = $element->{$element->fieldId()}();
		
		$element->processData('preRead');
		
		foreach($element->processData('doRead') as $dataInfo){
			foreach($dataInfo as $fieldInfo){
				$fields[] = $fieldInfo[0];
			}
		}
		
		$query = $this->db->prepare('
			SELECT `'.implode('`, `',$fields).'` 
			FROM '.$element->storage().' 
			WHERE '. $element->fieldId().'=:'.$element->fieldId() .'
			LIMIT 1
		');
		$query->execute($values);
		$return = $query->fetch();
		
		$element->processData('postRead');
		
		return $return;
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
	
	public function isSetElementStorage(SC_Element &$element) {
		return in_array(strtolower($element->storage()), array_map('strtolower', $this->db->query('SHOW TABLES', \PDO::FETCH_COLUMN,0)->fetchAll()));
	}
	
	public function alterTable(SC_Element $element) {
		// Verify that we have the same Datas in the element and in the DB
		$dbColumns = array();
		$dbIndexes = array();
		$elementData = $this->getDataTypes($element);
		$elementDataKeys = array_keys($elementData);
		
		$alters = array();
		
		foreach($this->db->query('SHOW COLUMNS FROM `'.$element->storage().'`')->fetchAll() as $dbColumn){
			if(!in_array($dbColumn['Field'], $elementDataKeys)) {
				// DROP COLUMN
				$alters[] = 'DROP COLUMN `'.$dbColumn['Field'].'`';
			} else {
				$dbColumns[$dbColumn['Field']] = $dbColumn;
			}
		}

		foreach($this->db->query('SHOW INDEXES FROM `'.$element->storage().'`')->fetchAll() as $dbIndex){
			//if( $dbIndex['Column_name']!=$element->fieldId() && !$element->{'O'.$dbIndex['Column_name']}()->search() ) {
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
			if($dataName!=$element->fieldId() && $element->{'O'.$dataName}()->search() ) {
				$alters[] = 'ADD INDEX `Index'.$dataName.'` ('.(($dataType==='text') ? $dataName .= '(200)' : $dataName).' ASC)';
			}
		}
		$alters[] = 'ADD INDEX `Index'.$dataName.'` ('.(($dataType==='text') ? $dataName .= '(200)' : $dataName).' ASC)';
		$alters[] = 'ADD PRIMARY KEY (`'.$element->fieldId().'`)';
		
		$q = 'ALTER TABLE `'.$element->storage().'` '.implode(', ', $alters);

		return $this->db->query($q);
	}
	
	//@todo: in  arrays format
	public function isValidElementStorage(SC_Element &$element) {
        
		// Verify that we have the same Datas in the element and in the DB
		$dbColumns = $this->db->query('SHOW COLUMNS FROM `'.$element->storage().'`', \PDO::FETCH_COLUMN,0)->fetchAll();
		$elementData = $this->getDataTypes($element);
		$return = true;
		
		//verifys that both element and DB have the same Data
		if( count($dbColumns) == count($elementData) && !array_diff($dbColumns, array_keys($elementData)) )
		{
			
			foreach($this->db->query('SHOW COLUMNS FROM `'.$element->storage().'`')->fetchAll() as $dbColumn){
				
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
				 		($element->fieldId()==$dbColumn['Field'] && $dbColumn['Key']!='PRI')
					 	OR
					 	($dbColumn['Key']=='PRI' && $element->fieldId()!=$dbColumn['Field'])
					) {
				 	user_error(
						$dbColumn['Field'].' data has a Primary Key inconsistency:
						'.$dbColumn['Field'].' -> '. (($element->{'O'.$dbColumn['Field']}()->search() || $element->fieldId() == $dbColumn['Field']) ? 'index' : 'not index') .'
						Storage -> '. $dbColumn['Key']
					,E_USER_WARNING);
					$return = false;
				 }
				 
				 if( ($dbColumn['Key'] XOR $element->{'O'.$dbColumn['Field']}()->search()) && ($dbColumn['Key']!='PRI') ){
					user_error(
						$dbColumn['Field'].' data has a Indexes inconsistency:
						'.$dbColumn['Field'].' -> '. (($element->{'O'.$dbColumn['Field']}()->search() || $element->fieldId() == $dbColumn['Field']) ? 'index' : 'not index') .'
						Storage -> '. ($dbColumn['Key']?'index':'not index')
					,E_USER_WARNING);
					$return = false;
				 }

			}
			
			//@todo: validate with Data\Date
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


	/**
	 * @param SC_Element $element
	 * 
	 * @return [type]
	 */
	public function createTable( SC_Element $element) {
		
        /**
         * At this point the table is created with the minimum columns (primary keys)
         * and the rest of the columns are added later with alterTable method
         */
        
		$dataType=$this->getDataTypes($element);

		$dataType=$dataType[$element->fieldId()];
		
		$q_data_part[]= ' `'.$element->fieldId().'` '.$dataType.' NOT NULL';
		
		$q = 'CREATE TABLE `'.$element->storage().'` (
			'.implode(', ',$q_data_part).',
			PRIMARY KEY (`'. $element->fieldId() .'`)
			'.@$q_index_part.'
		)';
		if($this->db->query($q)) {
			return $this->alterTable($element);
		} else {
			return false;
		}
	}
	
	/**
	 * @param SC_Element $element
	 * 
	 * @return [type]
	 */
	public function ensureElementStorage( SC_Element &$element) {
		if($element->getClass()::$storageChecked) {
			return $element->getClass()::$storageChecked;
		} else {			
			if(!$this->isSetElementStorage($element)) {
				if(SC_Main::$DEV_MODE) {
					$return = $this->createTable($element);
				} else {
					$return = false;
				}
			} else if(!$this->isValidElementStorage($element)) {
				if(SC_Main::$DEV_MODE) {
					$return = $this->alterTable($element) && $this->isValidElementStorage($element);
				} else {
					$return = false;
				}
			} else {
				$return = true;
			}
			$element->getClass()::$storageChecked = $return;
			return $return;
		}
	}

	public function getDataTypes(SC_Element &$element) {
		// @todo: check
		$result = array();
		$typesMap = array_reverse(self::$typesMap, true);
		foreach($typesMap as $class => $type)
		{
			if($attr_types = $element->attributesTypesWith($class)) {
			//if($attr_types = $element->processData('doRead')) {
			// @todo: continue from here! (new instance_of :)				
				if($type == '_ForeignKey_') {
					foreach($attr_types as $attr){
						$encapsuledElement = $element->{'O'.$attr}()->element();
						$encapsuledElementDatasTypes = $this->getDataTypes($encapsuledElement);
						$result[$attr] = str_replace('auto_increment', '', $encapsuledElementDatasTypes[$encapsuledElement->fieldId()]);
					}
				} else {
						$result = array_merge($result, array_combine($attr_types, array_fill(0, count($attr_types), $type)));
				}
			}
		}
		
		return $result;
	}
	
	public function delete(SC_Element &$element) {
        $query_string = 'DELETE FROM '.$element->storage().' WHERE '.$this->filterCriteria($element->deleteCriteria());
		return $this->db->prepare($query_string)->execute($this->obtainValues($element, $query_string));
	}
	
	public function deleteElement(SC_Element &$element) {
		$fieldId = $element->fieldId();
		return $this->db->prepare('
			DELETE FROM '.$element->storage().' WHERE '.$fieldId.' = :'.$fieldId.'
		')->execute(array(
			':'.$fieldId => $element->$fieldId()
		));
	}
	
	function createElement (SC_Element &$element) {
		$datas = $element->processData('doCreate');

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
				$columns[] = '`'.$column.'`';
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
		$id = $element->getId() ?: $this->db->lastInsertId();
		
		
		if($this->db->commit()) {
			return $id;
		} else {
			return false;
		}
	}
	
	
	function updateElement(SC_Element &$element) {
		// @todo: evaluate if it's more convenient to merge the array
		$fieldId = $element->fieldId();;
		foreach($element->processData('doUpdate') as $datas){
			if($datas){
				foreach($datas as $data){
					if($data[0] != $fieldId) {
						$sets[] = '`'.$data[0].'`=:'.$data[0];
					}
					$values[':'.$data[0]] = $data[2];
				}
			}
		}

		return $this->db->prepare('
			UPDATE '.$element->storage().' 
			SET '. implode(', ',$sets) .'
			WHERE '. $fieldId.'=:'.$fieldId .'
		')->execute($values);
	}

	public function readElements(SC_Element &$element, $returnAs = 'array', $position = null, $limit = null, $group = null){
		
		if(isset($group)){
			$group = implode(',', $group);
		}
        /*
         * Reads the storage "table" for each class, usually it's the same 
         * as the class name but could be otherwise (any class may use 
         * any storage if wanted)
         */
        $storages = is_array($element->storage())
                        ? $element->storage() 
                        : array($element->getClass() => $element->storage());
		
		/*
         * Select only the readable elements
         */
		foreach($element->processData('doRead') as $dataInfo){
			foreach($dataInfo as $fieldInfo){
				$fields[] = $fieldInfo[0];
			}
		}

        /*
         * 
         */
		foreach($storages as $class => $storage) {
			$storage_fields = $fields; // id as id, 'id' as fieldId, 'fe' as storage
			$addFields =
				'"'.strtr($class,array('\\'=>'\\\\')).'" as `SimplOn_class`, '.
				'"'.$element->fieldId().'" as `SimplOn_fieldId`, '.
				'"'.$element->fieldId().'" as `SimplOn_id`, '. // mandatory (ej. to make it possible to order on a UNION)
				//'"'.$element->fieldId().'", ';
				'';
				///RSL2022
			$where = $this->filterCriteria($element);
			$selects[] = '(SELECT '.$addFields.implode(', ', $storage_fields).' FROM '.$storage.' '. ($where ? 'WHERE '.$where : '').' '.($group? 'GROUP BY '.$group:'').' '.((isset($limit)) ? " LIMIT $position,$limit" : '').')';
		
		}
		
		// @todo: where and order by (and limit)
		$query_string = implode("\n".' UNION ', $selects).'
			ORDER BY '.$element->OrderCriteria().'
		';

		$query = $this->db->prepare($query_string);

		// Obtains values
		$values = $this->obtainValues($element, $query_string);
		///RSL 2022
		

		$query->execute($values);
			/**
			 * Example:
			 * $array_of_datas = array(
			 *      array(
			 *          'SimplOn_class' => 'Home',
			 *          'SimplOn_fieldId' => 'id',
			 *          'SimplOn_id' => 1,
			 *          'id' => 1,
			 *          ...
			 *      ),
			 *      ...
			 * );
			 */
			$array_of_datas = $query->fetchAll();
			switch($returnAs) {
				case 'array':
					$return = $array_of_datas;
					break;
				case 'Elements':
					$return = array();
					foreach($array_of_datas as $datas) {
						if($element->datasMode()){
							$tempElem = new $datas['SimplOn_class']($datas);
							SC_Main::$PERMISSIONS->setValuesByPermissions($tempElem, $element->datasMode());
							$return[] = $tempElem;
						}else{
							$return[] = new $datas['SimplOn_class']($datas);
						}
					}
					break;
					
				default:
					//trigger error
			}
		return $return;
	}

	public function filterCriteria($element_or_criteria){
        $filterCriteria = is_string($element_or_criteria)
            ? $element_or_criteria
            : $element_or_criteria->filterCriteria();
		$patterns = array();
		$subs = array();
		foreach(self::$operandsMap as $op => $sqlOp){
			// Regexp thanks to Jens: http://stackoverflow.com/questions/6462578/alternative-to-regex-match-all-instances-not-inside-quotes/6464500#6464500
			$patterns[] = '/('.$op.')(?=([^"\\\\]*(\\\\.|"([^"\\\\]*\\\\.)*[^"\\\\]*"))*[^"]*$)/';
			$subs[] = $sqlOp;
		}
		
		/**
			TODO :: FIX REGEXS SO THAT THERE CAN BE TWO ATTRIBUTES WITH A SHARED SUB STRING EX: cuenta and cuentaHabiente
		*/

		// Specials (for LIKE statement)
		$patterns[] = '/~= *:([a-zA-Z0-9]+)/';
		$subs[] = 'LIKE :RLLIKE__$1';
		$patterns[] = '/~= *("(([^"\\\\]*\\\\.)*[^"\\\\]*)")/';
		$subs[] = 'LIKE "%$2%"';
		
		$patterns[] = '/\^= *:([a-zA-Z0-9]+)/';
		$subs[] = 'LIKE :RLIKE__$1';
		$patterns[] = '/\^= *("(([^"\\\\]*\\\\.)*[^"\\\\]*)")/';
		$subs[] = 'LIKE "$2%"';
		
		$patterns[] = '/\$= *(:[a-zA-Z0-9]+)/';
		$subs[] = 'LIKE :LLIKE__$1';
		$patterns[] = '/\$= *("(([^"\\\\]*\\\\.)*[^"\\\\]*)")/';
		$subs[] = 'LIKE "%$2"';
		
		return preg_replace($patterns, $subs, $filterCriteria);
	}

    function obtainValues(SC_Element &$element, $query_string) {
        $values = array();
		foreach($element->processData('doSearch') as $dataInfo){
			foreach($dataInfo as $fieldInfo){
				$bindable_values = array(
					':'.$fieldInfo[0]		 => $fieldInfo[2],
					':RLLIKE__'.$fieldInfo[0]	 => '%'.$fieldInfo[2].'%',
					':LLIKE__'.$fieldInfo[0]	 => '%'.$fieldInfo[2],
					':RLIKE__'.$fieldInfo[0]	 => $fieldInfo[2].'%',
				);
				foreach($bindable_values as $label => $value) {
					if(strpos($query_string, $label) !== false)
						$values[$label] = $value;
				}
			}
		}
        return $values;
    }

	function countElements(SC_Element &$element, $group=null){
		if (isset($group)) {
			$group = implode(',', $group);
		}
		$storages = is_array($element->storage())
                        ? $element->storage() 
                        : array($element->getClass() => $element->storage());
		foreach ($element->processData('doRead') as $dataInfo) {
			foreach ($dataInfo as $fieldInfo) {
				$fields[] = $fieldInfo[0];
			}
		}
		foreach ($storages as $class => $storage) {
			$storage_fields = $fields; 
			$addFields =
				'"'.strtr($class,array('\\'=>'\\\\')).'" as `SimplOn_class`, '.
				'"'.$element->fieldId().'" as `SimplOn_fieldId`, '.
				'"'.$element->fieldId().'" as `SimplOn_id`, '. 
				'"'.$element->fieldId().'", ';
			$where = $this->filterCriteria($element);

			$selects[] = ' (SELECT '.$addFields.implode(', ', $storage_fields).' FROM '.$storage.' '. ($where ? 'WHERE '.$where : '').' '.($group? 'GROUP BY '.$group:'').') ';
		}
		$query_string = implode("\n".' UNION ', $selects).' ORDER BY SimplOn_id desc';
		$query = $this->db->prepare($query_string);
		$values = $this->obtainValues($element, $query_string);
		$query->execute($values);
		$results = $query->fetchAll();
        $totalRecords = sizeof($results);
		return $totalRecords;
	}

}
