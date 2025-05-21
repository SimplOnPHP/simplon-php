<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/


class SDS_SQLite extends SDS_SQL
{
	/*@var db MySqlDataBase */

	static $typesMap = array(
		'SD_AutoIncrementId'    => 'INTEGER', // Base type for SQLite autoincrement
		'SD_StringId'           => 'TEXT',
		'SD_Integer'            => 'INTEGER',
		'SD_Float'              => 'REAL',
		'SD_TimeStamp'          => 'TEXT',    // SQLite stores timestamps often as TEXT (ISO8601) or INTEGER (Unix epoch)
		'SD_Date'               => 'TEXT',    // SQLite stores dates as TEXT (YYYY-MM-DD)
		'SD_DateTime'           => 'TEXT',    // SQLite stores datetimes as TEXT
		'SD_String'             => 'TEXT',
		'SD_Text'               => 'TEXT',
		'SD_DArray'             => 'TEXT',    // Assuming serialized storage
		'SD_Array'              => 'TEXT',    // Assuming serialized storage
		'SD_Password'           => 'TEXT',    // Storing hashed passwords
		'SD_File'               => 'TEXT',    // Storing file path
		'SD_Image'              => 'TEXT',    // Storing image path
		'SD_ElementContainer'   => '_ForeignKey_', // Placeholder, actual type depends on referenced PK
		'SD_ElementsContainer'  => '_ForeignKey_', // Placeholder, might not be directly storable
	);

	public function __construct( $server, $db_name, $user = 'root', $password = '') {
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
			return new \SQLite3 ($db_name, 0666, $error);
		} catch(\Exception $e) {
			user_error($error, E_USER_ERROR);
		}
	}

	public function isSetElementStorage(SC_Element &$element) {
		// SQLite uses sqlite_master table to list tables
		$query = $this->db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
		$tables = $query->fetchAll(\PDO::FETCH_COLUMN);
		
		// The original code uses strtolower for comparison, so we maintain that.
		if (!is_array($tables)) {
			$tables = []; // Ensure $tables is an array if query fails or returns no results
		}
		return in_array(strtolower($element->storage()), array_map('strtolower', $tables));
	}

	public function getDataTypes(SC_Element &$element) {
		$result = array();
		// Use static::$typesMap to ensure LSB picks up SDS_SQLite::$typesMap
		$typesMap = array_reverse(static::$typesMap, true); 
		
		foreach($typesMap as $class => $type) {
			if($attr_types = $element->attributesTypesWith($class)) {
				if($type == '_ForeignKey_') {
					foreach($attr_types as $attr){
						$encapsuledElement = $element->{'O'.$attr}()->element();
						// Recursive call, will correctly use overridden getDataTypes if $this is SDS_SQLite
						$encapsuledElementDatasTypes = $this->getDataTypes($encapsuledElement); 
						$pkFieldOfEncapsuled = $encapsuledElement->fieldId();
						
						if (isset($encapsuledElementDatasTypes[$pkFieldOfEncapsuled])) {
							$fk_type = $encapsuledElementDatasTypes[$pkFieldOfEncapsuled];
							// Cleanup MySQL/SQLite specific keywords if they are part of the base type string
							$fk_type = str_ireplace('auto_increment', '', $fk_type);
							$fk_type = str_ireplace('autoincrement', '', $fk_type);
							$fk_type = trim(preg_replace('/\s*PRIMARY KEY/i', '', $fk_type));
							$fk_type = trim(preg_replace('/\s*NOT NULL/i', '', $fk_type));
							$result[$attr] = trim($fk_type);
						} else {
							// Fallback or error handling if PK type of encapsulated element is not found
							$result[$attr] = 'INTEGER'; // A sensible default for FKs if type unknown
						}
					}
				} else {
					$result = array_merge($result, array_combine($attr_types, array_fill(0, count($attr_types), $type)));
				}
			}
		}
		return $result;
	}

	public function createTable(SC_Element $element) {
		$fieldId = $element->fieldId();
		// This will call the overridden getDataTypes in SDS_SQLite
		$elementDataTypes = $this->getDataTypes($element); 
		
		if (!isset($elementDataTypes[$fieldId])) {
			throw new \Exception("Primary key field '{$fieldId}' not found in element's data types for table creation for element " . $element->getClass());
		}
		$pkBaseDataType = $elementDataTypes[$fieldId]; // e.g., INTEGER from SDS_SQLite::$typesMap
	
		$columnDefinition = '"' . $fieldId . '" ' . $pkBaseDataType;
	
		$fieldIdDataAttribute = $element->{'O'.$fieldId}();
		if ($fieldIdDataAttribute instanceof SD_AutoIncrementId) {
			// SQLite specific syntax for auto-incrementing primary key
			$columnDefinition .= ' PRIMARY KEY AUTOINCREMENT NOT NULL';
		} else {
			// For other types of IDs (e.g., SD_StringId)
			$columnDefinition .= ' PRIMARY KEY NOT NULL';
		}
		
		$tableName = $element->storage();
	
		$q = 'CREATE TABLE "' . $tableName . '" (' . $columnDefinition . ')';
	
		if ($this->db->exec($q) !== false) { // PDO::exec returns number of affected rows or false on error
			return $this->alterTable($element); 
		} else {
			return false;
		}
	}

	public function isValidElementStorage(SC_Element &$element) {
		$tableName = $element->storage();
		// This will call the overridden getDataTypes in SDS_SQLite
		$elementData = $this->getDataTypes($element); 
		$return = true;

		// Fetch column information from SQLite
		try {
			$stmt = $this->db->query("PRAGMA table_info(\"$tableName\")");
			$dbColumnInfos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		} catch (\PDOException $e) {
			user_error("Error fetching table info for $tableName: " . $e->getMessage(), E_USER_WARNING);
			return false; // Cannot validate if table info cannot be fetched
		}

		if (empty($dbColumnInfos) && !empty($elementData)) {
			user_error(
				$element->getClass().' structure mismatch with Data Storage: Table '.$tableName.' appears to have no columns, but element expects some.',
				E_USER_WARNING
			);
			return false;
		}
        if (empty($dbColumnInfos) && empty($elementData)) {
            return true; // No columns expected, no columns exist. Valid.
        }
		
		$dbColumnNames = array_map('strtolower', array_column($dbColumnInfos, 'name'));
		$elementDataKeysLower = array_map('strtolower', array_keys($elementData));

		// Verify that both element and DB have the same Data (column names and count)
		if (count($dbColumnNames) != count($elementDataKeysLower) || 
			!empty(array_diff($dbColumnNames, $elementDataKeysLower)) || 
			!empty(array_diff($elementDataKeysLower, $dbColumnNames))) {
			user_error(
				$element->getClass().' structure mismatch with Data Storage: Column names or count differ.
				Element '.$element->getClass().' expects columns -> '.print_r(array_keys($elementData),1).'
				Storage '.$tableName.' has columns -> '.print_r(array_column($dbColumnInfos, 'name'),1),
				E_USER_WARNING
			);
			return false; 
		}

		// Create a map of DB column info for easy lookup (case-insensitive keys)
		$dbColumnsMap = [];
		foreach ($dbColumnInfos as $colInfo) {
			$dbColumnsMap[strtolower($colInfo['name'])] = $colInfo;
		}

		foreach ($elementData as $elementFieldName => $elementFieldType) {
			$elementFieldNameLower = strtolower($elementFieldName);
			$dbColumnInfo = $dbColumnsMap[$elementFieldNameLower];
			$elementFieldObject = $element->{'O'.$elementFieldName}();

			// Check Auto-increment
			$isElementAutoIncrement = ($elementFieldObject instanceof SD_AutoIncrementId);
			$isDbPotentialAutoIncrement = (
				strtoupper($dbColumnInfo['type']) === 'INTEGER' &&
				$dbColumnInfo['pk'] == 1
			);

			if ($isElementAutoIncrement && !$isDbPotentialAutoIncrement) {
				 user_error(
					$elementFieldName.' data Auto-Increment inconsistency: Element expects auto-increment, but DB column is not INTEGER PRIMARY KEY. '.
					'DB Type: '.$dbColumnInfo['type'].', DB PK: '.$dbColumnInfo['pk'],
					E_USER_WARNING
				);
				$return = false;
			}

			// Check Types
			$dbColTypeNormalized = strtoupper($dbColumnInfo['type']);
			$elementColTypeNormalized = strtoupper($elementFieldType);
			$typeCompatible = false;

			if ($elementColTypeNormalized == $dbColTypeNormalized) {
				$typeCompatible = true;
			} else {
				// SQLite Type Affinities
				if ($elementColTypeNormalized == 'INTEGER' && in_array($dbColTypeNormalized, ['INT', 'INTEGER', 'TINYINT', 'SMALLINT', 'MEDIUMINT', 'BIGINT', 'UNSIGNED BIG INT', 'INT2', 'INT8'])) {
					$typeCompatible = true;
				} elseif ($elementColTypeNormalized == 'TEXT' && in_array($dbColTypeNormalized, ['TEXT', 'CHARACTER', 'VARCHAR', 'VARYING CHARACTER', 'NCHAR', 'NATIVE CHARACTER', 'NVARCHAR', 'CLOB'])) {
					$typeCompatible = true;
				} elseif ($elementColTypeNormalized == 'REAL' && in_array($dbColTypeNormalized, ['REAL', 'DOUBLE', 'DOUBLE PRECISION', 'FLOAT'])) {
					$typeCompatible = true;
				} elseif ($elementColTypeNormalized == 'BLOB' && $dbColTypeNormalized == 'BLOB') { 
					$typeCompatible = true;
				}
			}

			if (!$typeCompatible) {
				user_error(
					$elementFieldName.' data type (Element: '.$elementColTypeNormalized.') does not match Storage data type (DB: '.$dbColTypeNormalized.') for table '.$tableName,
					E_USER_WARNING
				);
				$return = false;
			}
			
			// Check Required fields
			$dbIsNotNull = ($dbColumnInfo['notnull'] == 1);
			$elementIsRequired = $elementFieldObject->required();

			if ($dbIsNotNull XOR $elementIsRequired) {
				user_error(
					$elementFieldName.' data has a Requirement inconsistency:
					'.$elementFieldName.' -> '. ($elementIsRequired ? 'required' : 'not required') .'
					Storage -> '. ($dbIsNotNull ? 'required (NOT NULL)' : 'not required (NULLABLE)')
				,E_USER_WARNING);
				$return = false;
			}
			  
			// Check Primary Key
			$isElementPK = (strtolower($element->fieldId()) == $elementFieldNameLower);
			$isDbPK = ($dbColumnInfo['pk'] == 1);

			if ($isElementPK XOR $isDbPK) {
				user_error(
					$elementFieldName.' data has a Primary Key inconsistency:
					'.$elementFieldName.' -> '. ($isElementPK ? 'is PK' : 'is not PK') .'
					Storage -> '. ($isDbPK ? 'is PK' : 'is not PK')
				,E_USER_WARNING);
				$return = false;
			}
			 
			// Check Indexes for searchable fields (excluding PK)
			if (!$isDbPK && $elementFieldObject->search()) {
				$indexExistsOnColumn = false;
				$stmt_idx_list = $this->db->query("PRAGMA index_list(\"$tableName\")");
				$indexes = $stmt_idx_list->fetchAll(\PDO::FETCH_ASSOC);
				foreach ($indexes as $index) {
					if ($index['origin'] == 'pk') continue; // Skip PK index

					$stmt_idx_info = $this->db->query("PRAGMA index_info(\"{$index['name']}\")");
					$idx_cols = $stmt_idx_info->fetchAll(\PDO::FETCH_ASSOC);
					// Check if the current field is the first column in this index (simplification)
					if (!empty($idx_cols) && strtolower($idx_cols[0]['name']) == $elementFieldNameLower) {
						$indexExistsOnColumn = true;
						break;
					}
				}
				if (!$indexExistsOnColumn) {
					user_error(
						$elementFieldName.' data has an Index inconsistency: Element expects field to be searchable, but no suitable index found in Storage.', E_USER_WARNING);
					$return = false;
				}
			}
		}
		return $return;
	}

	public function alterTable(SC_Element $element) {
		$tableName = $element->storage();
		// This will call the overridden getDataTypes in SDS_SQLite
		$elementData = $this->getDataTypes($element); 

		// 1. Get current DB schema (columns)
		try {
			$stmt = $this->db->query("PRAGMA table_info(\"$tableName\")");
			$dbColumnInfos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		} catch (\PDOException $e) {
			user_error("Failed to get table info for $tableName during alterTable: " . $e->getMessage(), E_USER_ERROR);
			return false;
		}
		
		$currentDbColumns = [];
		foreach ($dbColumnInfos as $colInfo) {
			$currentDbColumns[strtolower($colInfo['name'])] = $colInfo;
		}

		// --- Column Operations ---
		// 2. Add missing columns
		foreach ($elementData as $dataName => $dataTypeFromElement) {
			if (!isset($currentDbColumns[strtolower($dataName)])) {
				$columnDefinition = "\"" . SQLite3::escapeString($dataName) . "\" $dataTypeFromElement";
				if ($element->{'O'.$dataName}()->required()) {
					// For new columns being added with NOT NULL, SQLite requires a DEFAULT value
					// if the table is not empty. For simplicity, we'll add NOT NULL.
					// If this causes issues on non-empty tables, a DEFAULT clause would be needed.
					// Example: DEFAULT '' for TEXT, DEFAULT 0 for INTEGER.
					// However, SD_Data types usually handle their default values internally.
					$columnDefinition .= ' NOT NULL';
					// Consider adding a generic default based on type if issues arise with existing data:
					// $defaultVal = "DEFAULT ''"; // for TEXT
					// if (strpos(strtoupper($dataTypeFromElement), 'INT') !== false) $defaultVal = "DEFAULT 0";
					// if (strpos(strtoupper($dataTypeFromElement), 'REAL') !== false) $defaultVal = "DEFAULT 0.0";
					// $columnDefinition .= ' ' . $defaultVal;
				}
				try {
					$this->db->exec("ALTER TABLE \"$tableName\" ADD COLUMN $columnDefinition;");
				} catch (\PDOException $e) {
					user_error("Failed to add column $dataName to $tableName: " . $e->getMessage(), E_USER_WARNING);
					// Decide if one failure should stop all alterations
				}
			}
			// Note: Modifying existing column types/constraints (like MySQL's CHANGE COLUMN)
			// is complex in SQLite and often requires table recreation.
			// This implementation focuses on adding missing columns and managing indexes.
		}

		// 3. Drop extra columns (Requires SQLite 3.35.0+)
		// Refresh column list after additions if we were to use it for drops.
		// For now, let's use the initially fetched list for identifying columns to drop.
		foreach ($currentDbColumns as $dbColNameLower => $dbColInfo) {
			$originalDbColName = $dbColInfo['name'];
			$foundInElementData = false;
			foreach (array_keys($elementData) as $elementKey) {
				if (strtolower($elementKey) === $dbColNameLower) {
					$foundInElementData = true;
					break;
				}
			}
			if (!$foundInElementData) {
				// Do not attempt to drop the primary key column
				if ($dbColInfo['pk'] != 1) {
					 try {
						// This requires SQLite 3.35.0+
						$this->db->exec("ALTER TABLE \"$tableName\" DROP COLUMN \"$originalDbColName\";");
					} catch (\PDOException $e) {
						// This might fail on older SQLite or if column has constraints/indexes.
						user_error("Notice: Failed to drop column $originalDbColName from $tableName (possibly unsupported SQLite version, or column is part of an index/constraint): " . $e->getMessage(), E_USER_NOTICE);
					}
				}
			}
		}
		
		// --- Index Operations ---
		// 4. Get current DB indexes
		$stmt_idx_list = $this->db->query("PRAGMA index_list(\"$tableName\")");
		$dbIndexes = $stmt_idx_list->fetchAll(\PDO::FETCH_ASSOC);

		// 5. Drop existing custom-created indexes (ones not for PK or UNIQUE constraints)
		foreach ($dbIndexes as $index) {
			if ($index['origin'] === 'c' && strpos($index['name'], 'sqlite_autoindex_') === false) {
				try {
					$this->db->exec("DROP INDEX IF EXISTS \"{$index['name']}\";");
				} catch (\PDOException $e) {
					user_error("Failed to drop index {$index['name']} for $tableName: " . $e->getMessage(), E_USER_WARNING);
				}
			}
		}

		// 6. Add required indexes (for searchable fields, not PK)
		$fieldIdLower = strtolower($element->fieldId());
		foreach ($elementData as $dataName => $dataTypeFromElement) {
			if (strtolower($dataName) != $fieldIdLower && $element->{'O'.$dataName}()->search()) {
				$indexName = "idx_{$tableName}_" . SQLite3::escapeString($dataName);
				$this->db->exec("CREATE INDEX IF NOT EXISTS \"$indexName\" ON \"$tableName\" (\"" . SQLite3::escapeString($dataName) . "\");");
			}
		}
		
		return true; // Indicate that alterations were attempted.
	}
}