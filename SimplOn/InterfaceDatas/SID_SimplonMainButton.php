<?php

class SID_SimplonMainButton extends SID_ComplexData {
	
    public
        $class,
        $method,
        $content,
        $img,
        $href;

	public function __construct($class,$method, $content=null, $img=null){
                $this->method = $method;
                $this->content = $content;
                /** @var SR_html $redender */
                $redender = $GLOBALS['redender'];
                $this->href = $redender->encodeURL($class, null, $this->method, null);
                if($img){$this->img=$img;}
                //($label, $sources, $flags=null)
	}

	public function src(){
                if(!$this->img){$this->img=$this->name;}
                return '/Imgs/'.$this->img.'.svg';
	}

}