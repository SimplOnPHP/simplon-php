<?php


class SID_Content extends SID_Data{

    public
        $staticRender = false;

	
    public function __construct($val=null){
        parent::__construct(null,null,null,null);
        $this->val($val);
    }

    public function __call($name, $arguments) {
        if(substr($name, 0, 4) === "show"){
            return $this->val();
        }else{
            return parent::__call($name, $arguments);
        }
    }
    
}