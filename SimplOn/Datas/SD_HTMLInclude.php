<?php


class SD_HTMLInclude extends SD_ComplexData {
 
    public function val($fill = null){

        $sources=$this->sources;
        $ret='';
        foreach($sources as $file){
            $ret.=file_get_contents(SC_Main::$GENERIC_TEMPLATES_PATH.'/Includes/'.$file);
        }
        return  trim($ret);
    }

}



?>
