<?php

class SID_MenuLink extends SID_ComplexData {	
    public
        $content,
        $href;

	public function __construct($url, $content=null){
        $this->content = $content;
        if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
            $this->href = $href;
        } else if(is_array($url)){
           // if(is_callable($url)){
                $redender = $GLOBALS['redender'];
                $this->href = $redender->encodeURL($url[0], null, $url[1], null);
            //}else{
            //    throw new SC_DataValidationException($url[1]." is not a callable method of ".$url[0]);
            //}
        } else {
            throw new SC_DataValidationException("$url is not a valid URL");
        }
	}
}