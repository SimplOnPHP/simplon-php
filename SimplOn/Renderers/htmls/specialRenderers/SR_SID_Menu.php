<?php

function SID_Menu_SR_special_Fill(SD_Data $Data, phpQueryObject $template){
     
    $redender = $GLOBALS['redender'];
    ////// AQUI me quede hay que arreglar el create y el update de plantillas
    foreach($Data->sources() as $item){
        if($template['.item.'.$item]){
            $itemTemplate = \phpQuery::newDocument($template['.item.'.$item]->htmlOuter());
            $template['.item.'.$item]->replaceWith($redender->fillDataDomWithVariables($Data->parent()->{'O'.$item}(), $itemTemplate));
        }
    }

    return $template;
};


function SID_Menu_SR_special_Check($Data, $template, $method){
    if(!$template){ return 'OutDated'; }
    $redender = $GLOBALS['redender'];
    
    $DataNode=$template['.'.$method.' .EA_'.$Data->name()];
    if(!$DataNode){ $DataNode = $template; }
    $datasInLayout = preg_match_all_callback(
        '/EA_[a-zA-Z0-9]+/',
        $DataNode->html(),
        function ($match){
            return explode('_',$match[0])[1];
        }
    );

    if($datasInLayout) {$datasInLayout = array_unique($datasInLayout);}else{$datasInLayout = array();}
    $sources = array_unique($Data->sources());
    if(!$datasInLayout){$datasInLayout = array();}
    $TemplateHasAllDatas = !array_diff($sources, $datasInLayout);
    $TemplateHasMoreDatas = (sizeof($sources)<sizeof($datasInLayout));

    if($TemplateHasAllDatas && !$TemplateHasMoreDatas){
        return 'Ok';
    }else{
        return 'OutDated';
    } 

};




function SID_Menu_SR_special_Get($Data, $method){
    $redender = $GLOBALS['redender'];

    $dataDom = \phpQuery::newDocumentFileHTML($redender->layoutPath($Data));
    $ret='';
    foreach($Data->sources() as $source){
        if(is_string($source)){$source=$Data->parent()->{'O'.$source}();}
        $sourceDom = \phpQuery::newDocumentFileHTML($redender->layoutPath($source));
        $redender->getStylesAndScriptsLinks($sourceDom);
        $redender->addStylesTagsToAutoCSS($source,$sourceDom,$method);
        $sourceDom[".$method>*:first-child"]->addClass(' item '.$source->name());
        $ret.=$sourceDom[".$method"]->html();
    }
    $dataDom[".$method>*:first-child"]->addClass('EA_'.$Data->name());
    $dataDom[".$method .links"]->html($ret);

    $redender->getStylesAndScriptsLinks($dataDom);
    $redender->addStylesTagsToAutoCSS($Data,$dataDom,$method);
    
    return $dataDom[".$method>*:first-child"];
};