<?php
namespace DOF\DataStorages;

/**
 *
 * @author Rub�n Schaffer Levine
 */
abstract class DataStorage {
	public $db;

	/**
	 *
	 * @param $server
	 * @param $user
	 * @param $password
	 * @param $dataBase
	 * @return unknown_type
	 */
	abstract public function __construct($server, $user, $password, $dataBase);
	
	//@todo implement ORDER BY in a simple way
	abstract public function getElementsData(&$element, $filters=null, $range='0,500');
	
	abstract public function formatFilters($filters);
	
	abstract public function isValidElementRepository(&$element);
	
	abstract public function getQuery(&$element, $conditions=null, $range=array(0,100));
	
	abstract public function createQuery(&$element, $conditions=null);
	
	abstract public function processRange($range);
	
	abstract public function processConditions($conditions);
	
	abstract public function updateQuery(&$element, $conditions=null);
	
	abstract public function deleteQuery(&$element, $conditions=null);

	public function saveElement(&$element) {
		if( $element->id() ){
			$this->updateElement($element);
		}else{
			$this->createElement($element);
		}
	}
	
	public function createElement(&$element) {
		//check( $this->createQuery($element) );
		$this->db->query( $this->createQuery($element) );
	}
	
	public function updateElement(&$element) {
		//check($this->updateQuery($element, $element->Fid()."=".$element->id() ) );
		$this->db->query( $this->updateQuery($element, $element->Fid()."=".$element->id()) );
	}
	
	public function deleteElement(&$element) {
		//check($this->deleteQuery($element, $element->Fid()."=".$element->id() ) );
		$this->db->query( $this->deleteQuery($element, $element->Fid()."=".$element->id() ) );
	}

	public function getElementData(&$element) {
		return $this->db->queryAsUniArray( ($this->getQuery($element, $element->Fid()."=".$element->id() ) )  );
	}
	
}


?>