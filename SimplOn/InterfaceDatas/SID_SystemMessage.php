<?php

class SID_SystemMessage extends SID_Data{
	public function __construct($val,$flags=null,$searchOp=null){
		parent::__construct(null,'V',$val,null,$searchOp);
	}
}