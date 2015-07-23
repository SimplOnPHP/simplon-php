<?php
namespace SimplOn\Datas;
use SimplOn\Main;

class HTMLInclude extends ComplexData {
 
    public function val($fill = null){

        $sources=$this->sources;
        $ret='';
        foreach($sources as $file){
            $ret.=file_get_contents(Main::$GENERIC_TEMPLATES_PATH.'/Includes/'.$file);
        }
        return  trim($ret);
    }

}



?>
