<?php

class SE_Role extends SC_Element
{	
	static
		$ReturnBtnMsg,
		$CancelBtnMsg,

		$SearchBtnMsg,
		$SearchMsg,

		$CreateBtnMsg,
		$CreatedMsg,
		$CreateMsg,
		$CreateError,

		$UpdateBtnMsg,
		$UpdatedMsg,
		$UpdateMsg,
		$UpdateError,

		$DeleteBtnMsg,
		$DeletedMsg,
		$DeleteMsg,
		$DeleteError;
		
	public static $storageChecked;

	static $permissions = array(
		'admin' => array('*'=>'allow'),
		'*' => array('*'=>'deny')
	);

	public function construct($id = null, &$specialDataStorage = null){
        $this->id = new SD_NumericId(); 
		$this->roleName = new SD_String('Rol','S');
	}

	//Makes sure there is the Role admin in the DB
	public function asureRole($role){
		$temp = $this->roleName();
		$this->roleName($role);
		$results = $this->dataStorage->readElements($this);
		if(count($results) == 0){
			$this->save();
			$this->roleName->clearValue();
			$this->roleName->clearValue($temp);
			return;
		}else{
			$this->roleName->clearValue();
			$this->roleName->clearValue($temp);
			return $results[0]['id'];
		}
	}	
 
}