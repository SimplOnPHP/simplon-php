<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/
class SE_EmptyAdmin extends AE_User
{	
	protected static $AdminRoleID;
	protected 
	 	$defaultClass = 'AE_Admin',
	 	$defaultMethod = 'showCreate';

	static
		$menu,
		$SearchBtnMsg;

	public function construct($id = null, $storage = 'AE_User'){
		parent::construct($id, $storage);
		$this->storage('AE_User');

		//We need to ensure there is an Admmin role in the DB and set it as a fixed value for this class
		if(!self::$AdminRoleID){
			$role = new SE_Role();
			$role->roleName('admin');
			$search = new SE_Search(array('SE_Role'));
			$result = @$search->getResults($role->toArray(), ['id'], 0, 1)[0];
			if ($result && $result->id()) {
				self::$AdminRoleID = $result->id();
			} else {
				self::$AdminRoleID = $role->create();
			}
			$this->userRole(self::$AdminRoleID);
		}
		SC_Main::$method == 'showAdmin';
		$Links[] = new SI_Link(SC_Main::$RENDERER->action('AE_User','showAdmin'), SC_MAIN::L('Users'));
		$Links[] = new SI_AjaxLink(SC_Main::$RENDERER->action($this->getClass(),'logout'), SC_MAIN::L('Logout'), 'logout.svg');

		self::$menu =  new SI_SystemMenu($Links);
    }

}