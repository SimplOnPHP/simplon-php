<?php


class SI_Link extends SI_Item {

    protected
        $text = '',
        $icon = '',
        $href = '';

    public function __construct($text, $href, $icon = ''){

        parent::__construct();
        $this->text = $text;
        $this->icon = $icon;
        if(!empty($icon)){$this->iconURL = $this->renderer->imgsWebRoot().DIRECTORY_SEPARATOR.'icons'.DIRECTORY_SEPARATOR.$icon;}

        if(is_string($href)){
            $this->href = $href;
        }elseif(is_array($href)){
		    $this->href = $this->renderer()->action($href[0], $href[1]);
        }
        
    }


    function getLayout()
    {
        if(empty($this->icon)){
            return $this->renderer->getItemLayoutFromTemplate($this);
        }else{
            return $this->renderer->getItemLayoutFromTemplate($this,'icon');
        }
    }   
}