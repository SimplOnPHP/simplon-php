<?php

require_once '../DOF/Utilities/Acl/Acl.php';
require_once '../DOF/Utilities/Acl/Role.php';
require_once '../DOF/Utilities/Acl/Resource.php';
require_once '../DOF/Utilities/Acl/Role/GenericRole.php';
require_once '../DOF/Utilities/Acl/Resource/GenericResource.php';
require_once '../DOF/Utilities/Acl/Role/Registry.php';

$acl = new Zend\Acl\Acl();
 
$acl->addRole(new Zend\Acl\Role\GenericRole('guest'))
    ->addRole(new Zend\Acl\Role\GenericRole('member'))
    ->addRole(new Zend\Acl\Role\GenericRole('admin'));
 
$parents = array('guest', 'member', 'admin');
$acl->addRole(new Zend\Acl\Role\GenericRole('someUser'), $parents);
 
$acl->addResource(new Zend\Acl\Resource\GenericResource('someResource'));
 
$acl->deny('guest', 'someResource');
$acl->allow('member', 'someResource');
 
echo $acl->isAllowed('someUser', 'someResource') ? 'allowed' : 'denied';



