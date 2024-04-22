<?php

class SE_User extends SE_Element
{
	protected
		$permissions,
		$asesorCreate,
		$validationAceptedMessage = 'Welcome:',
		$validationRejectedMessage = "User and Password don't match Please try again";		

	public function construct($id = null, &$specialDataStorage = null)
	{
		$this->permissions = array(
			'admin' => array('*'=>'allow'),
			'Asesor' => array(
				'View'=>array(
					'updateAction'=>'viwableWhen_id_=_CurrentUserId',
					'createAction'=>'hide',
					'deleteAction'=>'hide',
				),
				'Search'=>'allow',
				'Update'=>array(
					'id'=>'fixed_CurrentUserId',
					'userRole'=>'fixed_CurrentUserRole',
				),
				'Create'=>'deny',
				'Delete'=>'deny',
				'showDashboard'=>'allow',
			),
		);

        $this->id = new SD_NumericId(null); 
		$this->userName = new SD_String('User','VCUSLR');
		$this->password = new SD_Password('Password');
		$this->sourceURL = new SD_Hidden(null,'vcuslerf',$_SERVER['REQUEST_URI']);

		//We need to ensure there is an admin role in the DB
		$role = new SE_Role();
		$role->asureRole('admin');

		$this->userRole = new SD_ElementContainerDropBox($role,'Rol',null,'RS');
		//Needed  to load the options before SE_User is created 
		$this->userRole->options();

		$redender = $GLOBALS['redender'];
		$this->processLoginLink = $redender->action($this,'processLogin');
		//$this->logoutLink = $redender->action($this,'logout');

		$this->formMethods[] = 'login';

        $this->menu = new SID_Menu([]);

	}
	
	public function processLogin(){
		//Get the info from the form

		// To read with out error the form we must set the role as not required since the login form is just user and password
		$tempRoleReq = $this->userRole->required();
		$this->userRole->required(false);
		try {

			//Get the info from the form
			$this->fillFromRequest();
			$this->validateForDB();	
			$this->userRole->required($tempRoleReq);
		} catch (SC_ElementValidationException $ev) {

			//If something is missing send the message
			$data = array();
			foreach ($ev->datasValidationMessages() as $key => $value) {
				$data[] = array(
				'func' => 'showValidationMessages',
				'args' => array($key, $value[0])
				);
			}
			$return = array(
			'status' => true,
			'type' => 'commands',
			'data' => $data
			);
			$return = json_encode($return);
			$this->userRole->required(true);
			return $return;
		}
	
		//Get the recived password
		$recibedPassword = $this->password();
		$this->password('');

		//Get the first user with the same name
		$results = $this->dataStorage->readElements($this);
		$this->fillFromArray($results[0]);

		if($this->autenticate($recibedPassword)){
			$this->dataStorage = null;
			$_SESSION["permissionID"] = $this->id();
			$_SESSION["userName"] = $this->userName();
			$_SESSION["userRole"] = $this->userRole->viewVal();	

			if($_REQUEST['sourceURL']){
				$nextStep = $_REQUEST['sourceURL'];
			}else{
				$nextStep = '';
			};
			$ret = array(
				'status' => true,
				'type' => 'commands',
				'data' => array(
					array(
						'func' => 'redirectNextStep',
						'args' => array($nextStep)
					),
				)
			);
		}else{
			$ret = array(
				'status' => true,
				'type' => 'commands',
				'data' => array(
					array(
						'func' => 'alert',
						'args' => array('Nel chavo')
					),
				)
			);
		}
		echo json_encode($ret);
	}

	public function fillFromRequest() {
		if ($_REQUEST) {
			$this->fillFromArray($_REQUEST);
			$this->password->encriptedFlag(False);
			return;
		} else {
			return false;
		}
	}

	public function logout(){
		session_unset();
		$ret = array(
			'status' => true,
			'type' => 'commands',
			'data' => array(
				array(
					'func' => 'redirectNextStep',
					'args' => array('')
				),
			)
		);
		echo json_encode($ret);
	}

	public function autenticate($givenPassword){
		if(md5($givenPassword)==$this->password()){
			return true;
		}else{
			return false;
		}
	}
	

	function processCreate(){
		if($this->userName() == 'emptyAdmin'){
			throw new SC_Exception('You can not create a user with the name emptyAdmin');
		}
		return parent::processCreate();
	}

	function processUpdate()
	{
		if($this->userName() == 'emptyAdmin'){
			throw new SC_Exception('You can not create a user with the name emptyAdmin');
		}
		return parent::processUpdate();
	}

	public function canEnter(SE_Element $element, string $method = null){

		$premissions = $this->checkPermissions($element->permissions(), $this->userRole->viewVal(), $method);

		if(
			$premissions
			OR
			(($method=='showLogin' || $method=='processLogin' || $method=='logout') AND ($element instanceof $this))
			OR
			$this->getClassName() == 'AE_EmptyAdmin'
		){
			return true;
		}else{
			return false;
		}
	}

	public function setValuesByPermissions(&$element, $mode){
		$element->datasMode($mode);
		if(
			isset($element->permissions()[$this->userRole->viewVal()]) &&
			isset($element->permissions()[$this->userRole->viewVal()][$mode]) &&
			is_array($element->permissions()[$this->userRole->viewVal()][$mode])
		){
			foreach($element->permissions()[$this->userRole->viewVal()][$mode] as $data=>$value){

				$value = explode("_", $value);
				$treatment = array_shift($value);
				if($treatment == 'fixed'){// can't change the value
					if($value[0] == 'CurrentUserId'){
						$element->{'F'.$data}($this->id());
					}elseif($value[0] == 'CurrentUserName'){
						$element->{'F'.$data}($this->userName());
					}elseif($value[0] == 'CurrentUserRole'){
						$element->{'F'.$data}($this->userRole());
					}elseif(property_exists($element, $value[0])){ //other attribute
						$element->{'F'.$data}($element->{$value[0]}());
					}else{ // constant TODO: how to use a constant that is equal to an attribute name
						$element->{'F'.$data}($value[0]);
					}
				}elseif($treatment == 'load'){// set the value but can be changed
					if($value[0] == 'CurrentUserId'){
						$element->{$data}($this->id());
					}elseif($value[0] == 'CurrentUserName'){
						$element->{$data}($this->userName());
					}elseif($value[0] == 'CurrentUserRole'){
						$element->{$data}($this->userRole());
					}elseif(property_exists($element, $value[0])){ //other attribute
						$element->{$data}($element->{$value[0]}());
					}else{ // constant TODO: how to use a constant that is equal to an attribute name
						$element->{$data}($value[0]);
					}
				}elseif($treatment == 'viwableWhen'){ // Show the value when conditions meet
					if($value[2] == 'CurrentUserId'){
						$tmpCompare = $this->id();
					}elseif($value[2] == 'CurrentUserName'){
						$tmpCompare = $this->userName();
					}elseif($value[2] == 'CurrentUserRole'){
						$tmpCompare = $this->userRole();
					}elseif(property_exists($element, value[2])){ //other attribute
						$tmpCompare = $element->{$value[2]}();
					}else{ // constant TODO: how to use a constant that is equal to an attribute name
						$tmpCompare = $value[2];
					}
					switch ($value[1]) {
					case '=':
					case '==':
						$result = $element->{$value[0]}() == $tmpCompare;
						break;
					case '!=':
						$result = $element->{$value[0]}() != $tmpCompare;
						break;
					case '<':
						$result = $element->{$value[0]}() < $tmpCompare;
						break;
					case '>':
						$result = $element->{$value[0]}() > $tmpCompare;
						break;
					case '<=':
						$result = $element->{$value[0]}() <= $tmpCompare;
						break;
					case '>=':
						$result = $element->{$value[0]}() >= $tmpCompare;
						break;
					}
					if(!$result){// TODO: Change to emptyData
						$element->{'O'.$data}(new SD_emptyAction('', array('')));
					}
				
				}elseif($treatment == 'hide'){//  Do not show the value
					$element->{'O'.$data}(new SD_emptyAction('', array('')));
				}
			}
		}	
	}
/**
$this->permissions = array(
	'admin' => array('*'=>'allow'),
	'Asesor' => array(
		'View'=>array(
			'updateAction'=>'viwableWhen_id_=_CurrentUserRole',
			'createAction'=>'hide',
			'deleteAction'=>'hide',
		),
		'Search'=>'allow',
		'Update'=>array(
			'id'=>'fixed_CurrentUserId',
			'userRole'=>'fixed_CurrentUserRole',
		),
		'Create'=>'deny',
		'Delete'=>'deny',
		),
	'*' => array('showView'=>'allow','*'=>'deny')
 */



	public function checkPermissions($elementPermissions, $userRole=null,  $method = null){
		$ret='';
		if(!$method){$method = SC_Main::$method;}
		if(!$userRole){$userRole = SC_Main::$PERMISSIONS->OuserRole()->viewVal();}
		if(isset($elementPermissions[$userRole])){
			if(isset($elementPermissions[$userRole][$method])){
				$ret = $elementPermissions[$userRole][$method];
			}elseif(isset($elementPermissions[$userRole]['*'])){
				$ret = $elementPermissions[$userRole]['*'];
			}
		}else{
			if(isset($elementPermissions['*'][$method])){
				$ret = $elementPermissions['*'][$method];
			}elseif(isset($elementPermissions['*']['*'])){
				$ret = $elementPermissions['*']['*'];
			}
		}

		return ($ret !== 'deny')? $ret:false;
	}


	public function activeUser(){
		if(isset($_SESSION["userName"])){return $_SESSION["userName"];}
	}

	public function logedIn(){
		return isset($_SESSION["userName"]);
	}

	public function acctiveRole(){
		return $_SESSION["userRole"];
	}

	public function default(){
		return $this->showDashboard();
	}

	


}