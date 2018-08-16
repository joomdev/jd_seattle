<?php

class N2Acl extends N2AclAbstract
{

    private $user = null;

    public function __construct() {
        $this->user = JFactory::getUser();
    }

    public function authorise($action, $info) {
        if($action == $info->getName()){
            $action = 'core.manage';
        }        
        return $this->user->authorise(str_replace('_', '.', $action), $info->getAcl());
    }
}