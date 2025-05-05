<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/
class SE_User extends SC_Element
{	
	public static $storageChecked;

	protected 
		$defaultClass,
		$defaultMethod;

	static 
		$permissions,
		$menu,
		$cantAccessMsg;
	
	protected
		$asesorCreate,
		$validationAceptedMessage = 'Welcome:',
		$validationRejectedMessage = "User and Password don't match Please try again";
		
	static
		$CantAccessMsg,
		$CantAccessHomeLinkMsg;	

	public function construct($id = null, $storage = null)
	{
		if($storage){
			$this->$storage($storage);
		}
		
		static::$CantAccessMsg = SC_Main::L('We\'re sorry, but it looks like you don’t have permission to access this page.
		If you believe this is an error, please check with your administrator or support team for further assistance.
		Thank you for your understanding!');
		static::$CantAccessHomeLinkMsg = SC_Main::L('Click here to go to your home page');

		self::$permissions = array(
			'admin' => array('*'=>'allow'),
			'user' => array(
				'Admin'=>'deny',
				'View'=>array(
					'updateAction'=>'viwableWhen_id_=_CurrentUserId',
					'createAction'=>'hide',
					'deleteAction'=>'hide',
				),
				'Search'=>'deny',
				'Update'=>array(
					'id'=>'fixed_CurrentUserId',
					'userRole'=>'fixed_CurrentUserRole',
					'userName'=>'fixed_CurrentUserName',
				),
				'Create'=>'deny',
				'Delete'=>'deny',
				'*'=>'deny',
			),
		);

        $this->id = new SD_AutoIncrementId(null); 
		$this->userName = new SD_String(SC_Main::L('User'),'VCUSlRe');
		$this->password = new SD_Password(SC_Main::L('Password'));
		$this->fullName = new SD_String(SC_Main::L('Full name'),'SL');

		//$this->role = new SD_Hidden('admin','vcuslerf',$_SERVER['REQUEST_URI']);
		$this->sourceURL = new SD_Hidden(null,'vcuslerf',$_SERVER['REQUEST_URI']);
		

		//We need to ensure there is an admin role in the DB
		$role = new SE_Role();

		///////////$role->asureRole('admin');

		$this->userRole = new SD_ElementContainer($role,SC_Main::L('Role'),null,'RSL');
		$this->userRole->layout(new SI_Select());
		//Needed  to load the options before SE_User is created 
		//$this->userRole->options();

///$this->processLoginLink = $this->renderer()->action($this,'processLogin');
		//$this->logoutLink = $this->renderer()->action($this,'logout');

		// self::$formMethods = parent::$formMethods ;
		// self::$formMethods[] = 'login';

        $this->menu = new SI_SystemMenu([]);

	}

	public function processLogin(){
		//Get the info from the form

		// To read with out error the form we must set the role as not required since the login form is just user and password
		$tempRoleReq = $this->userRole->required();
		$this->userRole->required(false);
		try {

			//Get the info from the form
			$this->fillFromRequest();
			//Change the way the userName compared in the DS switching to that contains the stirng  used by default and useful in search to equal needed to find the proper user
			$this->userName->filterCriteria('name == :name');
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
		
		try{$this->fillFromArray($results[0]);}catch(SC_ElementValidationException $ev){}

		if($this->autenticate($recibedPassword)){
			$this->dataStorage = null;
			$_SESSION["permissionID"] = $this->id();
			$_SESSION["userName"] = $this->userName();
			$_SESSION["userRole"] = $this->userRole->viewVal();	

			if(isset($_REQUEST['sourceURL'])){
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
						'args' => array(static::$cantAccessMsg)
					),
				)
			);
		}
		echo json_encode($ret);
	}

	public function fillFromRequest() {
		if ($_REQUEST) {
			try{$this->fillFromArray($_REQUEST);}catch(SC_ElementValidationException $ev){}
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
	
	function showLogin(){

		$content = new SI_VContainer(null,'c c c');
		$content->addItem(new SI_Spacer('3rem','3rem'));
			$picframe = new SI_HContainer([
				'&nbsp;',
				new SI_Image('Logo.webp', SC_Main::$App_Name.' logo','300rem'),
				'&nbsp;']);

		$content->addItem($picframe);


		$action = $this->renderer->action($this,'processLogin');
		if($_SERVER['REQUEST_URI']){
			$this->addData('sourceURL',new SD_Hidden(null,null,$_SERVER['REQUEST_URI']));
        	$form = new SI_Form([$this->userName->showCreate(), $this->password->showCreate(), $this->sourceURL->showCreate()], $action);
		}else{
        	$form = new SI_Form([$this->userName, $this->password], $action, true, 'showCreate');
		}
		$form->addItem(new SI_Submit(SC_MAIN::L('Login')));

		
		$content->addItem(new SI_HContainer([' ',$form,' ']));
        //return $this->renderer->renderFullPage($content, 'showView', $this, 'showLogin');
		
		$page = new SI_systemScreen( $content,'',static::$AdminMsg );
		return $page;

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

	public function canEnter( $element, string $method = null){

		$premissions = $this->getPermissions($element, $this->userRole->viewVal(), $method);

		if(
			(($method=='showLogin' || $method=='processLogin' || $method=='logout') AND ($element instanceof $this))
			OR
			$this->getClass() == 'AE_EmptyAdmin'
		){
			return true;
		}elseif($premissions){
				
			if(isset($premissions[''])){

				$value = explode("_", $premissions['']);
				$treatment = array_shift($value);

				if($treatment == 'accessibleWhen'){
					if($value[0] == 'CurrentUserId'){
						$value[0] = $this->id();
					}elseif($value[0] == 'CurrentUserName'){
						$value[0] = $this->userName();
					}elseif($value[0] == 'CurrentUserRole'){
						$value[0] = $this->userRole->viewVal();
					}else{
						$value[0] = $element->{$value[0]}();
					}
					if($value[2] == 'CurrentUserId'){
						$value[2] = $this->id();
					}elseif($value[2] == 'CurrentUserName'){
						$value[2] = $this->userName();
					}elseif($value[2] == 'CurrentUserRole'){
						$value[2] = $this->userRole->viewVal();
					}else{
						$value[2] = $element->{$value[2]}();
					}
					
					switch ($value[1]) {
						case '=':
						case '==':
							return $value[0] == $value[2];
							break;
						case '!=':
							return $value[0] != $value[2];
							break;
						case '>':
							return $value[0] > $value[2];
							break;
						case '<':
							return $value[0] < $value[2];
							break;
						case '>=':
							return $value[0] >= $value[2];
							break;
						case '<=':
							return $value[0] <= $value[2];
							break;
						default:
							throw new SC_Exception("Invalid element access operator: " . $value[1]);
					}

				}else{
					throw new SC_Exception('Command '.$treatment.' is not valid for element access control');
				}
			}else{
				return true;
			}
			
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
			foreach($element->permissions()[$this->userRole->viewVal()][$mode] as $data=>$rule){
				//If the rules are for the element itself
				if(empty($data)){
					// just ignore because this is checked in the canEnter function
				//if the rules are for the element's datas
				}elseif(property_exists($element, $data)){
					$this->setCheckDataRule($element,$data,$rule);
				}
			}
		}	
	}

	public function setCheckDataRule(SC_Element $element, $data, string $rule){

		$result = null;
		$rule = explode("_", $rule);

		$treatment = array_shift($rule);
		if($treatment == 'fixed'){// can't change the value
			if($rule[0] == 'CurrentUserId'){
				$element->{'F'.$data}($this->id());
			}elseif($rule[0] == 'CurrentUserName'){
				$element->{'F'.$data}($this->userName());
			}elseif($rule[0] == 'CurrentUserRole'){
				$element->{'F'.$data}($this->userRole());
			}elseif(property_exists($element, $rule[0])){ //other attribute
				$element->{'F'.$data}($element->{$rule[0]}());
			}else{ // constant TODO: how to use a constant that is equal to an attribute name
				$element->{'F'.$data}($rule[0]);
			}
		}elseif($treatment == 'load'){// set the value but can be changed
			if($rule[0] == 'CurrentUserId'){
				//$element->{'O'.$data}($this->id());
				$element->{$data}($this->id());
			}elseif($rule[0] == 'CurrentUserName'){
				$element->{$data}($this->userName());
			}elseif($rule[0] == 'CurrentUserRole'){
				$element->{$data}($this->userRole());
			}elseif(property_exists($element, $rule[0])){ //other attribute
				$element->{$data}($element->{$rule[0]}());
			}else{ // constant TODO: how to use a constant that is equal to an attribute name
				$element->{$data}($rule[0]);
			}
		}elseif($treatment == 'viwableWhen'){ // Show the value when conditions meet
			if($rule[0] == 'CurrentUserId'){
				$rule[0] = $this->id();
			}elseif($rule[0] == 'CurrentUserName'){
				$rule[0] = $this->userName();
			}elseif($rule[0] == 'CurrentUserRole'){
				$rule[0] = $this->userRole->viewVal();
			}else{
				$rule[0] = $element->{$rule[0]}();
			}
			if($rule[2] == 'CurrentUserId'){
				$rule[2] = $this->id();
			}elseif($rule[2] == 'CurrentUserName'){
				$rule[2] = $this->userName();
			}elseif($rule[2] == 'CurrentUserRole'){
				$rule[2] = $this->userRole->viewVal();
			}else{
				$rule[2] = $element->{$rule[2]}();
			}

			$rule[0] = (string)$rule[0];
			$rule[2] = (string)$rule[2];
			switch ($rule[1]) {
			case '=':
			case '==':				
				$result = $rule[0] == $rule[2];
				break;
			case '!=':
				$result = $rule[0] != $rule[2];
				break;
			case '<':
				$result = $rule[0] < $rule[2];
				break;
			case '>':
				$result = $rule[0] > $rule[2];
				break;
			case '<=':
				$result = $rule[0] <= $rule[2];
				break;
			case '>=':
				$result = $rule[0] >= $rule[2];
				break;
			}

			// if the comparion is false hide the data
			if($result === false){
				$data->renderOverride('showEmpty');
			}else{


				// if the data has no name it's most likely added dinamically and might be reused to save ram in several instances of  the element, thus the renderOverride might be cleared

				// if(is_string($data)){$data = $element->{'O'.$data}();}//SD_Actions are sent as objects when add dinamically like in table bacause they maight 

				// if(empty($data->name())){
				// 	$element->{'O'.$data}()->clear('renderOverride');
				// }
			}
			
		
		}elseif($treatment == 'hide'){//  Do not show the value
			$data->renderOverride('showEmpty');
		}
		return $result;
	}




/**
self::$permissions = array(
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
		),
	'*' => array('showView'=>'allow','*'=>'deny')
 */


	/**
	 * @param SC_Element|SD_ElementContainer $element
	 * @param string|null $userRole
	 * @param string|null $method
	 */
	public function getPermissions( $element, $userRole=null,  $method = null){
		
		$ret='';
		$elementPermissions = $element->permissions();
		$methodsFamilies = $element::$methodsFamilies;
		if(!$method){$method = SC_Main::$method;}
		if(array_key_exists($method, $methodsFamilies)) { $method = $methodsFamilies[$method];}
		
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