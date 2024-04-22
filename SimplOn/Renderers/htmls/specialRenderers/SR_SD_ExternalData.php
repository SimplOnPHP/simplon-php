<?php
    /** This file is inteded to be included in SR_html to help render  SD_ExternalData Datas
     * Such datas have either an array of arrays, or an array of elements as "val"
     */

    function SD_ExternalData_SR_special_Fill(SD_ExternalData $Data, phpQueryObject $dom){
		$redender = $GLOBALS['redender'];
        return $redender->fillDataDomWithVariables($Data, $dom);
        //return $Data->showView();
    }

    function SD_ExternalData_SR_special_Check($Data, $template, $method){
        return 'OutDated';
    }

    function SD_ExternalData_SR_special_Get($Data, $method){
		$redender = $GLOBALS['redender'];
        $template = null;
        $element = $Data->element();
        //return $redender->renderData($element->{'O'.$Data->data}(),'showView',$template,1);
        return $redender->renderData($element->{'O'.$Data->data}(),$method,$template,1);
    }

?>