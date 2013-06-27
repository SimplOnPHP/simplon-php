<?php
namespace SimplOn\Elements;
use \SimplOn\Datas;

class User extends Element
{
	protected
		$validationAceptedMessage = 'Welcome:',
		$validationRejectedMessage = "User and Password don't match Please try again";
	
	public function construct($id = null, &$specialDataStorage = null)
	{
		$this->user = new Datas\StringId('User','VCUSL');
		$this->password = new Datas\Password('Password');
		$this->role = new Datas\ElementsContainer(array(new Role()) ,'Role');
		//$this->filterCriteria('.cabeza OR contenido == "igual" OR contenido ^= "empieza" OR contenido $= "acaba" OR contenido ~= "papas a \"la\" .contenido francesa"');
                $this->permissions = array('Administrator' => array ('Allow' => array('*')),
                                        'User' => array('Deny' => array('*')),
                                        'Guest' => array('Deny' => array('*')));
		
	}
	
	public function autenticate($givenPassword){
		if(md5($givenPassword)==$this->password){
			
		}
	}
	
	public function showValidation(){
		$this->password->search(true);
		//$ret = $this->showSearch(\SimplOn\Main::$GENERIC_TEMPLATES_PATH.'/validation/userValidation.html', $this->encodeURL(array(), 'processValidation'));
		$ret = $this->obtainHtml('showSearch', \SimplOn\Main::$GENERIC_TEMPLATES_PATH.'/validation/userValidation.html', $this->encodeURL(array(), 'processValidation'));
		$this->password->search(false);
		return $ret;
	}	

	public function processValidation(){
		if( @$_REQUEST[$this->user->inputName()] ){
			$this->fillFromDSById( $_REQUEST[$this->user->inputName()] );
			if($this->password() == trim(md5($_REQUEST[$this->password->inputName()]))){
				$this->sonMessage($this->validationAceptedMessage.$this->user);
				$_SESSION['simplonUser'] = $this->user();
				return header('Location: '.$_SESSION['url']);
			}else{
				$this->sonMessage($this->validationRejectedMessage);
				return $this->showValidation();
			}
		}
	}	
}