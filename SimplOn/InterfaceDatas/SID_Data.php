<?php


class SID_Data extends SD_Data{

    public
        $staticRender = true;


    public function __call($name, $arguments) {
        if(substr($name, 0, 4) === "show"){
            $redender = $GLOBALS['redender2'];
            //array_unshift($arguments,$name);
            array_unshift($arguments,'showView');
            array_unshift($arguments,$this);
            return call_user_func_array(array($redender, 'renderData'), $arguments);
        }else{
            return parent::__call($name, $arguments);
        }
    }


    // function showView($template = null,$sources = NULL){

    //     // \phpQuery::newDocumentFileHTML(realpath($template_file))
    //     $redender = $GLOBALS['redender2'];

    //     return $redender->renderData($this,'showView');
    // }

}