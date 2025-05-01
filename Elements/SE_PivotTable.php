<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

class SE_PivotTable extends SC_Element {
	static $permissions;
	public function construct($id = null, &$specialDataStorage = null) {
	    $this->id = new SD_AutoIncrementId();
	    $this->parentId = new SD_String('','S');
        $this->childId = new SD_String('','S');
        $this->childClass = new SD_String('','S');
	}

	public function deleteCriteria($deleteCriteria = null) {
		if (isset($deleteCriteria))
			$this->deleteCriteria = $deleteCriteria;
		else {

			//REMOVED so it adapts on every run if necesary
			if (!isset($this->deleteCriteria))
			$this->deleteCriteria = $this->defaultDeleteCriteria();

			//$filterCriteria = $this->filterCriteria;

			$patterns = array();
			$subs = array();
			foreach ($this->dataAttributes() as $dataName) {
				// Regexp thanks to Jens: http://stackoverflow.com/questions/6462578/alternative-to-regex-match-all-instances-not-inside-quotes/6464500#6464500
				
				if( !$dataName OR ($dataName === $this->fieldId() AND empty($this->getId())  )   ){
					$fc = null;
				}else{
					$fc = $this->{'O' . $dataName}()->filterCriteria();
				}
				if (!empty($fc)) {
					$patterns[] = '/(\.' . $dataName . ')(?=([^"\\\\]*(\\\\.|"([^"\\\\]*\\\\.)*[^"\\\\]*"))*[^"]*$)/';
					$subs[] = $fc;
				}
			}
			
			//$ret = preg_replace($patterns, $subs, $filterCriteria);
			return preg_replace($patterns, $subs, $this->deleteCriteria);
		}
	}

}