<?php


class SID_Container extends SID_ComplexData{
	
    public
        $class,
        $method,
        $content,
        $img,
        $type,
        $href;

	public function __construct($sources, $content=null, $img=null, $type = 'horizontalFill'){
                $this->sources = $sources;
                $this->content = $content;
                $this->type = $type;
	}

    public function appendSource($source){
        $this->sources[] = $source;
    }

    public function prependSource($source){
        array_unshift($this->sources, $source);
    }

}