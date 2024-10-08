<?php

class SR_formContainer extends SR_interfaceContainer
{
    function __construct($name = ''){
        $this->name=$name;
        $this->extra='';

        $this->parentWrap['end']='</form>';
    }


    function getLayout() {

        global $rendr2;

        $ret = '';
        $i=0;

        foreach($this->items as $item){
            $i++;
            if($item instanceof SD_File){ $this->extra = ' enctype="multipart/form-data" ';}
            if(is_object($item[0])){$class = $item[0]->getClass();}
            if( is_string($item[0]) ){
                $dom = pq($item[0]);
                $class = 'string';
            }elseif( $item[0] instanceof SC_Element){
                $dom = $rendr2->getElementLayout($item[0], $item[1]);
            }elseif( $item[0] instanceof SD_Data){
                $dom = $rendr2->getDataLayout($item[0], $item[1]);
            }elseif( $item[0] instanceof SR_interfaceItem){
                $dom = $item[0]->getLayout();
            }
            if(!empty($this->childWrap['start'])){
                $childTag = pq($this->childWrap['start'].$this->childWrap['end']);
                $childTag->addClass('item'.$i);
                if (!$childTag->hasClass($class)){ $childTag->addClass($class); }
                $dom->wrap($childTag);
            }else{
                if($class == 'string'){ 
                    $dom = pq($this->childWrap['start'].$item[0].$this->childWrap['end']);
                    $dom->addClass('item'.$i); 
                }else{ 
                    $dom = pq($this->childWrap['start'].$dom.$this->childWrap['end']);
                    $dom->addClass('item'.$i);
                    if(!$dom->hasClass($class)){ $dom->addClass($class); }
                }
            }
            $ret .=$dom;
        }
        $ret .= $this->parentWrap['end']."\r\n";
        $ret ='<form name="'.$this->name.'" method="post" action="$action" class="'.$this->name.'" '.$this->extra.'>'."\r\n".$ret;
        return pq($ret);
    }


}