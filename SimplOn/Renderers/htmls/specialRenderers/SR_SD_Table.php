<?php
    /** This file is inteded to be included in SR_html to help render  SD_Table Datas
     * Such datas have either an array of arrays, or an array of elements as "val"
     * 
		$simplonCols = 'ignore', // show/ignore
		$colsTitles = true, // true/false
		$rowsTitles = false; // true/false
		$colsTitlesIn = firstRow, // keys/firstRow
		$rowsTitlesIn= keys; // keys/firstCol
     */

    /** Create an array of arrays ready to be printed with the Titles in the first row or colum if they are going to be shown and just the data that has to be shown */
    function SD_Table_adjustAcordingToParams(SD_Table $Data){
        $data = $Data->val();

        reset($data);
        $data0 = current($data);
     
        $newVal = [];
        if($data0 instanceof SE_Element){
            $data0 = SD_Table_addColumns($data0,$Data->columnsToAdd());
            $datasWithList=array_merge($data0->datasWith('list'), $Data->columnsToList() );
            if($Data->rowsTitles()){$rowVal[] = '&nbsp;';}
            if($Data->colsTitles()){
                foreach($datasWithList as $dataName){
                    if(empty($data0->{'O'.$dataName}()->label())){$temp='&nbsp;';}else{$temp=$data0->{'O'.$dataName}()->label();}
                    $rowVal[] = $temp;
                }
            }   
            $newVal[] = $rowVal;
            $Data->colsTitlesIn('firstRow');
            foreach($data as $element){
                $element = SD_Table_addColumns($element,$Data->columnsToAdd());
                $rowVal = [];
                if($Data->rowsTitles()){$rowVal[] = $element->Name();}
                foreach($datasWithList as $key => $dataName){
                    $rowVal[] = $element->{'O'.$dataName}()->showList();
                }
                $newVal[] = $rowVal;
            }
        }elseif(is_array($data0)){
            
            $data0 = SD_Table_addColumns($data0,$Data->columnsToAdd());
            if($Data->colsTitles() AND $Data->colsTitlesIn()=='keys'){
                if($Data->simplonCols()=='ignore'){ $temp=array_slice(array_keys($data0), 3); }else{ $temp = array_keys($data0);}
                if($Data->rowsTitles() AND $Data->rowsTitlesIn()=='keys'){ array_unshift($temp,'&nbsp;'); }
                if(!$Data->rowsTitles() AND $Data->rowsTitlesIn()=='firstCol'){ array_shift($temp); }
                $newVal[] = $temp;   
            }
            if(!$Data->colsTitles() AND $Data->colsTitlesIn()=='firstRow'){ array_shift($data); }
            foreach($data as $key=>$row){
                $row = SD_Table_addColumns($row,$Data->columnsToAdd());
                if($Data->simplonCols()=='ignore' AND array_keys($row)[0] == 'SimplOn_class' ){ $row=array_slice($row, 3); }
                if($Data->rowsTitles() AND $Data->rowsTitlesIn()=='keys'){ array_unshift($row,$key); }
                if(!$Data->rowsTitles() AND $Data->rowsTitlesIn()=='firstCol'){ array_shift($row); }
                //Leave only columns to show
                //Add columns that do calculus
                //Do calculs for cols
                $newVal[] = $row;
            }
            //Add calculus rows
        }
        $Data->val($newVal);
        return $Data;
    }

        function SD_Table_addColumns( $arrayOrElement, array $columnsToAdd){
            if($arrayOrElement instanceof SE_Element){
                foreach ($columnsToAdd as $key=>$val){
                    if(is_string($val)){
                        $arrayOrElement->{'O'.$val}()->dataFlags('L');
                    }else{
                        $val->dataFlags('L');
                        $arrayOrElement->addData($key,$val);
                    }
                }
            }elseif(is_array($arrayOrElement)){
                $tempElement = new SC_BaseObject();
                foreach ($arrayOrElement as $key => $val)
                {
                    $tempElement->$key = $val;
                }
            
                foreach ($columnsToAdd as $key=>$val){
                    $data = clone $val;
                    $data->parent($tempElement);
                    $arrayOrElement[$data->label()]=$data;
                }
            }
            return $arrayOrElement;
        }


    function SD_Table_SR_special_Fill(SD_Table $Data, phpQueryObject $dom){
        $data = $Data->val();  

        if(!empty($data)){
            $headerNewHtml = '';

            $header = \phpQuery::newDocument($dom['.SD_Table .rows .header:first']->htmlOuter());
            $headerCel = addslashes($dom['.SD_Table .rows .header>*:first']->htmlOuter());


            $header['.header']->empty();

            $rowTemp = \phpQuery::newDocument($dom['.SD_Table .rows .row:first']->htmlOuter());
            $celEval = addslashes($dom['.SD_Table .rows .row *:not(.header):first']->htmlOuter());
            $celHeaderEval = addslashes($dom['.SD_Table .rows .row .header:first']->htmlOuter());

            $dom['.SD_Table .rows']->empty();  

            if($Data->colsTitles()){
                $headerData = array_shift($data);
                foreach($headerData as $val){ 
                    eval ('$headerNewHtml .= "'.$headerCel.'";' );
                }
            }
            $header['.header']->html($headerNewHtml);

            $dom['.SD_Table .rows']->append($header->htmlOuter()); 
        
            $colsArray = array();
            foreach($data as $rowData){
                $rowTempHtml = '';

                if($Data->rowsTitles()){
                    $val = array_shift($rowData);
                    eval ('$rowTempHtml .= "'.$celHeaderEval.'";' ); 
                }
                reset($headerData);  
                
                foreach($rowData as $key=>$val){
                    if($Data->rowsToAdd()){
                        if($val instanceof SD_Data){  
                            $colsArray[$key][]=$val->val();
                        }else{
                            $possibleNum=trim(strip_tags($val));
                            if(is_numeric($possibleNum)){
                                $colsArray[$key][]=(double)$possibleNum;  
                            }else{
                                $colsArray[$headerData[$key]][]=$val;  
                            }
                                
                        }
                    }
                    if(!$Data->colsTitles()){ $key=''; }
                    elseif($Data->colsTitlesIn()=='firstRow'){
                        $key=current($headerData);
                        next($headerData);
                    }
                    eval ('$rowTempHtml .= "'.$celEval.'";' );
                }
                $rowTemp['.row']->html($rowTempHtml);
                $dom['.SD_Table .rows']->append($rowTemp['.row']->htmlOuter()); 
            }

            if($Data->rowsToAdd()){
                foreach($Data->rowsToAdd() as $rowParams){
                    $rowTempHtml = '';
                    if($Data->rowsTitles() ){ 
                        $key=$rowParams['title'];
                        $val=$key;
                        eval ('$rowTempHtml .= "'.$celHeaderEval.'";' );  
                    }

                    foreach($rowData as $key=>$val){
                        $function=$rowParams['function'];
                        if(is_callable($function) AND in_array($key,$rowParams['rows'])){ 
                            $val=$function($colsArray[$key]); 
                        } else {
                            $val='';
                        }
                        eval ('$rowTempHtml .= "'.$celEval.'";' );
                    }
                    $rowTemp['.row']->html($rowTempHtml);
                    $dom['.SD_Table .rows']->append($rowTemp['.row']->htmlOuter()); 
                }

            }

            return $dom['.SD_Table']->htmlOuter();
        }
    }


    function SD_Table_SR_special_Check($Data, $template, $method){
        return 'OutDated';
    }


    function SD_Table_SR_special_Get($Data, $method){
		$redender = $GLOBALS['redender'];

        $dom = \phpQuery::newDocumentFileHTML($redender->layoutPath($Data));
        $redender->getStylesAndScriptsLinks($dom);
        $redender->addStylesTagsToAutoCSS($Data,$dom,$method);
        $dom[".$method>*:first-child"]->addClass('EA_'.$Data->name())->html();


        return $dom[".$method"];
    }

    $Data = SD_Table_adjustAcordingToParams($Data);


    //  $mainDom['.repeat_rows>*:eq(0)']->html();

?>