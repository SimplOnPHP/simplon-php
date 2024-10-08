<?php


class SR_interfaceContainer extends SR_interfaceItem
{
    protected
    $parentWrap = ['start'=>'','end'=>''],
    $childWrap = ['start'=>'','end'=>''],
    $name,
    $items = [];

    function __construct($name = ''){
        $this->name=$name;
    }

    function addItem($item,$method = 'showView'){
        $this->items[]=[$item,$method];
    }

    function addItems($items,$method = 'showView'){
        
        $rendr2 = $GLOBALS['rendr2'];

        foreach ($items as $item) {
            $this->items[]=[$item,$method];
        }
    }
    
    function prependItem($item,$method = null) {
        array_unshift($this->items, [$item,$method]);
    }



    function getLayout() {

        $rendr2 = $GLOBALS['rendr2'];

        $ret = '';
        $i=0;

        $ret = $this->parentWrap['start']."\r\n";
        
        foreach($this->items as $item){
            $i++;
            if(is_object($item[0])){$class = $item[0]->getClass();}
            if( is_string($item[0]) ){
                $class = 'string';
            }elseif( $item[0] instanceof SC_Element){
                $dom = $rendr2->getElementLayout($item[0], $item[1]);
            }elseif( $item[0] instanceof SD_Data){
                $dom = $rendr2->getDataLayout($item[0], $item[1]);
            }elseif( $item[0] instanceof SR_interfaceItem){
                $dom = $item[0]->getLayout();
            }
            if(!empty($this->childWrap['start'])){
                if($class == 'string'){ 
                    $dom = pq($this->childWrap['start'].$item[0].$this->childWrap['end']);
                    $dom->addClass('item'.$i); 
                }else{ 
                    $dom = pq($this->childWrap['start'].$dom.$this->childWrap['end']);
                    $dom->addClass('item'.$i);
                    if(!$dom->hasClass($class)){ $dom->addClass($class); }
                }
            }else{
                $dom->addClass('item'.$i);
            }
            $ret .=$dom;
        }
        $ret .= $this->parentWrap['end']."\r\n";
        
        return pq($ret);
    }

    function fillLayout($Layout) {
        
        $rendr2 = $GLOBALS['rendr2'];

        $ret = '';
        $i=0;

        foreach($this->items as $item){ 
            $i++;

            if( is_string($item[0]) ){
                $ret .= $Layout['> .item'.$i]->html($item[0]);
            }elseif( $item[0] instanceof SC_Element){
                $Layout = $rendr2->fillDatasInElementLayout($item[0], $Layout['> .item'.$i]);
                $ret .= $rendr2->fillVariablesInElementLayout($item[0],$Layout['> .item'.$i]);
            }elseif( $item[0] instanceof SD_Data){
                $ret .= $rendr2->fillDataDomWithVariables($item[0],$Layout['> .item'.$i]);
            }elseif( $item[0] instanceof SR_interfaceItem){
                $ret .= $item[0]->fillLayout($Layout['> .item'.$i]);
            }
        }
        $Layout->html($ret);
        return $Layout;
    }
}