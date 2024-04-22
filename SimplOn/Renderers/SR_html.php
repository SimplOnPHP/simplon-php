<?php 

class SR_html {

    public
        $SimplOn_path,
        $App_path,
        $App_web_root,
        $URL_METHOD_SEPARATOR ='!',
        $REMOTE_ROOT;

    static
        $outputtemplate,
        $csslinks,
        $jslinks = array();


    function addMethod($name, $method)
    {
        $this->{$name} = $method;
    }

    public function __call($name, $arguments)
    {
        return call_user_func($this->{$name}, $arguments);
    }

    function renderData( $Data, $method, $template = null, $messages = null, $action=null,$nextStep=null){
        $specialRendererPath = $this->SimplOn_path.DIRECTORY_SEPARATOR.'Renderers'.DIRECTORY_SEPARATOR.'htmls'.DIRECTORY_SEPARATOR.'specialRenderers'.DIRECTORY_SEPARATOR.'SR_'.$Data->getClass().'.php';
      
        //Fill the template
        if(!file_exists($specialRendererPath)){ //normal 

            if(!$template OR SC_Main::$Element_Layouts=='OverWrite'){
                $template = $this->getDataLayout($Data, $method); 
            }
             
            $ret = $this->fillDataDomWithVariables($Data, $template);
           
        }else{      //Special

            require_once($specialRendererPath);

            $className = get_class($Data);
            $SR_special_Get = $className . '_SR_special_Get';
            $SR_special_Check = $className . '_SR_special_Check';

            $specialCheck = $SR_special_Check($Data, $template, $method);
   
            if (!$template || SC_Main::$Element_Layouts === 'OverWrite') {
                $template = $SR_special_Get($Data, $method); 
            } elseif ($specialCheck !== 'Ok') {
                //TODO: Doing the same that above review what really ned to be done to consider the template and Update 
                $template = $SR_special_Get($Data, $method); 
            }
            $SR_special_Fill = $Data->getClass().'_SR_special_Fill';

            $ret = $SR_special_Fill($Data, $template);

            //$ret = $this->fillDataDomWithVariables($Data, $template);

        }
        
        return $ret;
    }    

    function render( $object, $method, $output = 'AE_fullPage', $template = null, $action=null,$nextStep=null,$noCancelButton=false){

        //Clean the Sysmessage so it's not added to the URLs
        $SystemMessage = SC_Main::$SystemMessage;
        SC_Main::$SystemMessage='';   
        
        //get (or make) the template
        if(!$template){ $template = $this->getElementTemplate($object, $method, $noCancelButton); }        
        //Fill the template
        
        $template = $this->fillDatasInElementTemplate($object,$template,$method);
        $template = $this->fillVariablesInElementTemplate($object,$template,$action);

        if($output){
            $output = new $output();
            $output->message($SystemMessage);
            $output->content($template->html());
            $outputtemplate = $this->directlayoutPath($output, 'showView');
            $outputtemplate = \phpQuery::newDocumentFileHTML($outputtemplate);
            $this->getJSandCSS($outputtemplate);
            $outputtemplate = $this->addCSSandJSLinksToTemplate($outputtemplate);

            return $this->fillDatasInElementTemplate($output,$outputtemplate,'showView');
        }else{
            //if the message has not been printed reset it
            SC_Main::$SystemMessage=$SystemMessage;
            return $template->html();
        }

    }

    function addCSSandJSLinksToTemplate($dom){
    if($dom["head"]->html()!=''){              
            $dom["head link[rel='stylesheet']"]->remove();

            foreach(self::$csslinks as $csslink){

                if(substr($csslink, 0, 4) == 'http' && substr($csslink, -4) == '.css'){
                    $dom["head"]->append('<link rel="stylesheet" href="'.$csslink.'"  //>');
                }else{
                    $dom["head"]->append('<link rel="stylesheet" href="'.$this->App_web_root.'/Layouts/css/'.basename($csslink).'" //>');
                }
            }
            $dom["head script"]->remove();
            foreach(self::$jslinks as $jslink){
                if(substr($jslink, 0, 4) == 'http' && substr($jslink, -3) == '.js'){
                    $dom["head"]->append('<script type="text/javascript" src="'.$jslink.'"> </script>'."\n");
                }else{
                    $dom["head"]->append('<script type="text/javascript" src="'.$this->App_web_root.'/Layouts/js/'.basename($jslink).'" /> </script>'."\n");  //NOTE :: space in -" /> </script>- weirdly required
                }
            }
        }
        return $dom;
    }

    function requiredText(SC_BaseObject $object){
        if($object->required()){ return 'required'; }else{ return ''; }
    }

    /* methods related with fix or generate specific HTML parts */
    function VCSLForPeople($object = null, string $VCSL=''){ 
        if(SC_Main::$VCRSL[$VCSL]){ return SC_Main::$VCRSL[$VCSL];}else{ return $VCSL;}
    }

    /* methods related with fix or generate specific HTML parts */
    function BackURL(){ 
        if(isset($_SERVER["HTTP_REFERER"])){
            $url = explode('!!',$_SERVER["HTTP_REFERER"]);
            return $url[0];
        }
    }

    function setOutputDom(string $output,phpQueryObject $content){

        if($output != 'partOnly' && !self::$mainDom){
            $appFile = $this->App_path.DIRECTORY_SEPARATOR.'Layouts'.DIRECTORY_SEPARATOR.$output.'.html';
            $simplonFile = $this->SimplOn_path.DIRECTORY_SEPARATOR.'Renderers'.DIRECTORY_SEPARATOR.'htmls'.DIRECTORY_SEPARATOR.$output.'.html';
            if(file_exists($appFile)){ self::$mainDom = \phpQuery::newDocumentFileHTML($appFile); }
            elseif(file_exists($simplonFile)){ self::$mainDom = \phpQuery::newDocumentFileHTML($simplonFile); }

        }elseif($output == 'partOnly'){
            if(!self::$mainDom){
                throw new SR_RendererException('There is not MainDom, there most be one element rendering to a full page to include the CSS and JS');
            } 
        }elseif(!self::$mainDom){
            throw new SR_RendererException('There is con only be one MainDom / Element Reendering to a full page');
        }
      
    }

    /* methods related with the JS and CSS */
    function getJSandCSS(phpQueryObject $dom){
        $this->getStylesAndScriptsLinks($dom);
        //TODO get other scripts and Styles???
    }
        
        function getStylesAndScriptsLinks(phpQueryObject $dom){ 
            // get all the CSS Links
            foreach($dom["head link[rel='stylesheet']"] as $link){
                $link = pq($link)->attr('href');
                if(substr($link, 0, 4) == 'http' && substr($link, -4) == '.css'){ self::$csslinks[$link]=$link; }
                elseif(substr($link, -4) == '.css'){self::$csslinks[basename($link)]=$link;}
            }

            // get all the JS Links
            foreach($dom["head script"] as $link){
                $link = pq($link)->attr('src');
                if(substr($link, 0, 4) == 'http' && substr($link, -3) == '.js'){ self::$jslinks[$link]=$link; }
                elseif(substr($link, -3) == '.js'){self::$jslinks[basename($link)]=$link;}
            }
        }

        function addStylesTagsToAutoCSS(SC_BaseObject $data, phpQueryObject $dom, string $method){ 
            global $cssTagsContent;
            // get the style tags of the method

            $cssTagsContent[$data->getClassName()]=array(
                'method'=>$method,
                'style'=>$dom['style']->html()
            );
            
            $minifyed = minify_css($cssTagsContent[$data->getClassName()]['style']);
            $minifyedWithMarks = "/* START_".$data->getClassName()." */\n $minifyed \n/* END_".$data->getClassName()." */";
    
            $file = $this->App_path.DIRECTORY_SEPARATOR.'Layouts'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'simplon-auto.css';
            $currentStylesFile = file_get_contents($file);

            $regEx = '/(\/\* START_'.$data->getClassName().' \*\/)(\\n.*\\n)(\/\* END_'.$data->getClassName().' \*\/)/';
            preg_match($regEx, $currentStylesFile, $currentStile);

            if(isset($currentStile[2]) && $currentStile[2] != $minifyed){
                $StylesForFile = preg_replace($regEx, $minifyedWithMarks, $currentStylesFile);
        
                if(!empty($currentStile[2]) AND $StylesForFile){
                    file_put_contents($file, $StylesForFile);
                }elseif(!empty(trim($minifyed))){
                    file_put_contents($file, $currentStylesFile."\n".$minifyedWithMarks);
                }
            }
        }   



    function getElementTemplate($object, $method, $noCancelButton=false){
        $directlayoutPath = $this->directlayoutPath($object);

        if(file_exists($directlayoutPath)){
           $check = $this->checkMethodLayout($object, $directlayoutPath, $method);
            if(is_array($check)){ 
                $changes = $check; 
                $check = 'Outdated'; 
            } 
        }
  
        if($object instanceof SE_Interface AND file_exists($directlayoutPath)){// if it's Interface ignore the OverWrite and use showView
            $dom = $this->createMethodLayout($object,'showView', null, $noCancelButton);
        }elseif( !file_exists($directlayoutPath)){
            // If there is no file create  the template
            $dom = $this->createMethodLayout($object,$method, null,$noCancelButton);
            $dom = "<section class='".$object->getClass()." $method'>". $dom->html()."</section>";

            $this->writeLayoutFile($dom,$directlayoutPath);

        }elseif( file_exists($directlayoutPath) && $check == 'None' ){ 
            // If there is template file but it has no section for the given method
            $dom = $this->createMethodLayout($object,$method, null, $noCancelButton);
            $dom = "<section class='".$object->getClass()." $method'>". $dom->html()."</section>";
            $this->appendMethodLayout($dom,$directlayoutPath);
        }elseif( file_exists($directlayoutPath) && $check == 'Empty' ){ 

            // If there is template file but it has no section for the given method
            $dom = $this->createMethodLayout($object,$method, null, $noCancelButton);
            $dom = "<section class='".$object->getClass()." $method'>". $dom->html()."</section>";
            $this->updateLayoutFile($dom,$method, $directlayoutPath);
        }elseif( SC_Main::$Element_Layouts == 'OverWrite' ){ 
            // If the section has to be overwriten
            if($check == 'Ok-NotVCRSL'){
                //Preserve de existiong NotVCRSL template
                $dom = \phpQuery::newDocumentFileHTML($directlayoutPath);
                $this->getStylesAndScriptsLinks($dom);
                $dom = $dom['.'.$method];
            }else{
                // Overwrite if VCRSL
                $dom = $this->createMethodLayout($object,$method, null, $noCancelButton);
                $dom = "<section class='".$object->getClass()." $method'>". $dom->html()."</section>";
                $this->updateLayoutFile($dom,$method,$directlayoutPath);
            }
        }elseif( SC_Main::$Element_Layouts == 'Update' AND !str_starts_with($check,'Ok') ){  
            // If Update and not in syc with Element or there is no section to render the method create the section
            $dom = $this->updateMethodLayout($object,$method,$changes, null, $noCancelButton);
            $dom = "<section class='".$object->getClass()." $method'>". $dom->html()."</section>";
            $this->updateLayoutFile($dom,$method, $directlayoutPath);
        }elseif(  
            SC_Main::$Element_Layouts == 'Preserve' 
            OR 
            (SC_Main::$Element_Layouts == 'Update' AND str_starts_with($check,'Ok') ) 
            ){

            // if Preserve or template in sync, render the Element with it.
            $dom = \phpQuery::newDocumentFileHTML($directlayoutPath);
            $this->getStylesAndScriptsLinks($dom);
            $dom = $dom['.'.$method];

        }
        //Returning the same $dom creates unusual flows I think there is trigger or something that alters the dom if the file is altered
        if($dom instanceof phpQueryObject){ $dom = $dom->htmlOuter(); }

        return \phpQuery::newDocumentHTML($dom);
    }

        function directlayoutPath($object){
            if(gettype($object) == 'object' && is_a($object,'SC_BaseObject')){

                if(is_a($object,'SD_Data')){ $dataPath=DIRECTORY_SEPARATOR.'Datas'; }
                if(is_a($object,'SID_Data')){ $dataPath=DIRECTORY_SEPARATOR.'InterfaceDatas'; }
                if(is_a($object,'SE_Element')){ $dataPath=''; }
                if(file_exists($this->SimplOn_path.DIRECTORY_SEPARATOR.'Renderers'.DIRECTORY_SEPARATOR.'htmls'.$dataPath.DIRECTORY_SEPARATOR.$object->getClass().'.html')){
                    $ret = $this->SimplOn_path.DIRECTORY_SEPARATOR.'Renderers'.DIRECTORY_SEPARATOR.'htmls'.$dataPath.DIRECTORY_SEPARATOR.$object->getClass().'.html';
                }else{
                    $ret = $this->App_path.DIRECTORY_SEPARATOR.'Layouts'.$dataPath.DIRECTORY_SEPARATOR.$object->getClass().'.html';
                }

            }elseif(gettype($object) == 'object' && !is_a($object,'SC_BaseObject')){  
                throw new SR_RendererException('This function can only get the path for Simplon  Datas and Elements');
            }elseif(is_string($object)){
                $ret = $this->App_path.DIRECTORY_SEPARATOR.'Layouts'.DIRECTORY_SEPARATOR.$object.'.html';
                if(!file_exists($ret)){
                    $ret = null;
                }
            }

            return $ret;
        }

        function layoutPath($object){
            if(gettype($object) == 'object' && is_a($object,'SC_BaseObject')){

                $ret = $this->App_path.DIRECTORY_SEPARATOR.'Layouts'.DIRECTORY_SEPARATOR.$object->getClass().'.html';

                if(is_a($object,'SD_Data')){ $dataPath=DIRECTORY_SEPARATOR.'Datas'; }

                if(is_a($object,'SID_Data') OR is_a($object,'SID_ComplexData')){ $dataPath=DIRECTORY_SEPARATOR.'InterfaceDatas'; }

                if(is_a($object,'SE_Element')){ $dataPath=''; }

                $ancestors = class_parents($object);
                array_splice($ancestors, -1);
                $ancestorClass = $object->getClass();
                while(
                        $ancestorClass 
                        && !file_exists($this->App_path.DIRECTORY_SEPARATOR.'Layouts'.$dataPath.DIRECTORY_SEPARATOR.$ancestorClass.'.html')
                        && !file_exists($this->SimplOn_path.DIRECTORY_SEPARATOR.'Renderers'.DIRECTORY_SEPARATOR.'htmls'.$dataPath.DIRECTORY_SEPARATOR.$ancestorClass.'.html')
                    ){
                    $ancestorClass = array_shift($ancestors);
                }
                if(file_exists($this->App_path.DIRECTORY_SEPARATOR.'Layouts'.$dataPath.DIRECTORY_SEPARATOR.$ancestorClass.'.html')){
                    $ret = $this->App_path.DIRECTORY_SEPARATOR.'Layouts'.$dataPath.DIRECTORY_SEPARATOR.$ancestorClass.'.html';
                }elseif(file_exists($this->SimplOn_path.DIRECTORY_SEPARATOR.'Renderers'.DIRECTORY_SEPARATOR.'htmls'.$dataPath.DIRECTORY_SEPARATOR.$ancestorClass.'.html')){
                    $ret = $this->SimplOn_path.DIRECTORY_SEPARATOR.'Renderers'.DIRECTORY_SEPARATOR.'htmls'.$dataPath.DIRECTORY_SEPARATOR.$ancestorClass.'.html';
                }
            }elseif(gettype($object) == 'object' && !is_a($object,'SC_BaseObject')){  
                throw new SR_RendererException('This function can only get the path for Simplon  Datas and Elements');
            }elseif(is_string($object)){
                $ret = $this->App_path.DIRECTORY_SEPARATOR.'Layouts'.DIRECTORY_SEPARATOR.$object.'.html';
                if(!file_exists($ret)){
                    $ret = $this->SimplOn_path.DIRECTORY_SEPARATOR.'Renderers'.DIRECTORY_SEPARATOR.'htmls'.$dataPath.DIRECTORY_SEPARATOR.$object.'.html';
                }
                if(!file_exists($ret)){
                    throw new SR_RendererException('Tehere is no template for '.$object);
                }
            }

            return $ret;
        }

        function checkMethodLayout(SE_Element $object, $dom, $method){
            if(is_string($dom)){$dom = \phpQuery::newDocumentFileHTML($dom);}
            $methodNode = $dom[".$method"];
            $showType = '';
            if(substr($method, 0 ,4) =='show'){$showType=strtolower(substr($method,4));}
            $objectDatasForMethod = $object->datasWith($showType);
      
            if(!$methodNode->htmlOuter()){ 
                return 'None';
            }elseif(!trim($methodNode->html())){ 
                return 'Empty';
            }elseif(!in_array(strtolower($showType), SC_Main::$VCRSLMethods)){
                return 'Ok-NotVCRSL';
            }elseif($methodNode->hasClass('direct')){
                return 'Ok-Direct';
            }else{
                $datasInLayout = preg_match_all_callback(
                    '/EA_[a-zA-Z0-9]+/',
                    $methodNode->html(),
                    function ($match){
                        return explode('_',$match[0])[1];
                    }
                );

                $ret = array();

                $ret['addToTemplate'] = array_diff(array_unique($objectDatasForMethod), array_unique((array)$datasInLayout));
                $ret['removeFromTemplate'] = array_diff(array_unique((array)$datasInLayout), array_unique($objectDatasForMethod));

                //-- Check for Correct Clasess
                foreach($objectDatasForMethod as $DataName){
                    $DataClass = $object->{'O'.$DataName}()->getClassName();
                    if(empty($methodNode['.'.$DataClass.'.EA_'.$DataName])){
                        $ret['removeFromTemplate'][] = $DataName;
                        $ret['addToTemplate'][] = $DataName;
                    }
                }
                $ret['addToTemplate'] = array_unique($ret['addToTemplate']);
                $ret['removeFromTemplate'] = array_unique($ret['removeFromTemplate']);


                if(empty($ret['addToTemplate']) and empty($ret['removeFromTemplate'])){
                    return 'Ok';
                }else{
                    return $ret;
                }
         
            }
        } 

        function updateMethodLayout(SE_Element $object,$method,$changes,$action=null,$noCancelButton=false){ 
            $dom = \phpQuery::newDocumentFileHTML($this->directlayoutPath($object));
            $methodNode = $dom[".$method"];

            foreach($changes['removeFromTemplate'] as $dataToRemove){
                $methodNode['.EA_'.$dataToRemove]->remove();
            }

            $objectDatasForMethod = $changes['addToTemplate'];

            if(is_array($objectDatasForMethod)){
                foreach($objectDatasForMethod as  $i=>$objectData) {
                    $dataTemplate = $this->getDataLayout($object->{'O'.$objectData}(),$method); //This has to be here to add the CSS and JS of all datas to the new updated template
            
                    if($i==0){
                        if( stripos($methodNode->html(),'<legend') ){
                            $methodNode["legend"]->after($dataTemplate);
                        }elseif(stripos($methodNode->html(),'<fieldset')){
                            $methodNode["fieldset"]->prepend($dataTemplate);
                        }elseif(stripos($methodNode->html(),'<form')){
                            $methodNode["form"]->prepend($dataTemplate);
                        }else{
                            $methodNode->prepend($dataTemplate);
                        }
                    }else{
                        if(isset($objectDatasForMethod[$i-1])){
                            $methodNode['.EA_'.$objectDatasForMethod[$i-1]]->after($dataTemplate);
                        }
                    }
                    
                }
                $html = $methodNode->html();
                $formTags = array('<input','<select','<textarea','<button','<fieldset', '<legend','<datalist','<output','<option','<optgroup'); 
                if( $this->contains($html,$formTags) && !stripos($html,'<form')) {
                    $enctype='';//todo change enctype if there is file type data
                    $VCSL=substr($method, 4);
    
                    if(SC_Main::$VCRSL[$VCSL]){$VCSLForPeople = SC_Main::$VCRSL[$VCSL];}else{$VCSLForPeople = $VCSL;};
                    if(empty($action)){$action='process'.$VCSL;}
                                
                    $html = '<form class="'.$object->htmlClasses($VCSL).' '.strtolower($VCSL) .'" '
                    . ' action="$action"'
                    . ' method="post" '
                    . @$enctype
                    . '><fieldset><legend>' . $VCSLForPeople . ' ' . $object->Name() . '</legend>'
                    . $html
                    . '<div class="buttons"><button type="submit">' . $VCSLForPeople . '</button>'
                    . (($noCancelButton) ? '<button onclick="location.href =\'$SRBackURL\';" class="SimplOn cancel-form">$SRVCSLForPeople_Cancel </button>' : '')
                    . '</div></fieldset></form>';
                    $methodNode->html($html);
                }
                return $methodNode;
            }elseif($objectDatasForMethod == 'NotVCRSL'){  
                $ret = $this->lookForMethodInElementsTree($object,$method);
                $ret['style']->html('');
            }



            // $datasInLayout = preg_match_all_callback(
            //     '/EA_[a-zA-Z0-9]+/',
            //     $methodNode->html(),
            //     function ($match){
            //         return explode('_',$match[0])[1];
            //     }
            // );
            // if(!$datasInLayout){ $datasInLayout = array(); }
            // $showType = '';
            // if(substr($method, 0 ,4) =='show'){$showType=strtolower(substr($method,4));}
            // $objectDatasForMethod = $object->datasWith($showType);
            // if(is_array($objectDatasForMethod) && $objectDatasForMethod != 'NotVCRSL'){
            //     for ($i = 0; $i < sizeof($objectDatasForMethod); $i++) {
            //         $objectData = $objectDatasForMethod[$i];
            //         $dataTemplate = $this->getDataLayout($object->{'O'.$objectData}(),$method); //This has to be here to add the CSS and JS of all datas to the new updated template
            //         if( !in_array($objectData,$datasInLayout) ){
            //             if($i==0){
            //                 if( stripos($methodNode->html(),'<legend') ){
            //                     $methodNode["legend"]->after($dataTemplate);
            //                 }elseif(stripos($methodNode->html(),'<fieldset')){
            //                     $methodNode["fieldset"]->prepend($dataTemplate);
            //                 }elseif(stripos($methodNode->html(),'<form')){
            //                     $methodNode["form"]->prepend($dataTemplate);
            //                 }else{
            //                     $methodNode->prepend($dataTemplate);
            //                 }
            //             }else{
            //                 $methodNode['.EA_'.$objectDatasForMethod[$i-1]]->after($dataTemplate);
            //             }
            //         }
            //     }
            //     $html = $methodNode->html();
            //     $formTags = array('<input','<select','<textarea','<button','<fieldset', '<legend','<datalist','<output','<option','<optgroup'); 
            //     if( $this->contains($html,$formTags) && !stripos($html,'<form')) {
            //         $enctype='';//todo change enctype if there is file type data
            //         $VCSL=substr($method, 4);
    
            //         if(SC_Main::$VCRSL[$VCSL]){$VCSLForPeople = SC_Main::$VCRSL[$VCSL];}else{$VCSLForPeople = $VCSL;};
            //         if(empty($action)){$action='process'.$VCSL;}
                                
            //         $html = '<form class="'.$object->htmlClasses($VCSL).' '.strtolower($VCSL) .'" '
            //         . ' action="$action"'
            //         . ' method="post" '
            //         . @$enctype
            //         . '><fieldset><legend>' . $VCSLForPeople . ' ' . $object->Name() . '</legend>'
            //         . $html
            //         . '<div class="buttons"><button type="submit">' . $VCSLForPeople . '</button>'
            //         . '<button onclick="location.href =\'$SRBackURL\';" class="SimplOn cancel-form">$SRVCSLForPeople_Cancel</button></div>'
            //         . '</div></fieldset></form>';
            //         $methodNode->html($html);
            //     }
            //     return $methodNode;
            // }elseif($objectDatasForMethod == 'NotVCRSL'){  
            //     $ret = $this->lookForMethodInElementsTree($object,$method);
            //     $ret['style']->html('');
            // }
        }


        function createMethodLayout(SE_Element $object,$method,$action=null,$noCancelButton=false){ 

            $showType = '';
            if(substr($method, 0 ,4) =='show'){$showType=strtolower(substr($method,4));}
            $DatasForMethod = $object->datasWith($showType);
            $ret='';
            if(is_array($DatasForMethod) && $DatasForMethod != 'NotVCRSL'){
                foreach($DatasForMethod as $Data){
                    $ret .= $this->getDataLayout($object->{'O'.$Data}(),$method);
                }
     
                $ret = \phpQuery::newDocumentHTML($ret);
                //$this->getStylesAndScriptsLinks($ret);
                $html=$ret->html();
                $enctype='';//todo change enctype if there is file type data
                $formTags = array('<input','<select','<textarea','<button','<fieldset', '<legend','<datalist','<output','<option','<optgroup'); 
                $VCSL=substr($method, 4);
        
                if(SC_Main::$VCRSL[$VCSL]){$VCSLForPeople = SC_Main::$VCRSL[$VCSL];}else{$VCSLForPeople = $VCSL;};
                if(empty($action)){$action='process'.$VCSL;}
                if( $this->contains($html,$formTags) ) { 
                    
                    $html = '<form class="'.$object->htmlClasses($VCSL).' '.strtolower($VCSL) .'" '
                    . ' action="$action"'
                    . ' method="post" '
                    . @$enctype
                    . '><fieldset><legend>' . $VCSLForPeople . ' ' . $object->Name() . '</legend>'
                    . $html
                    . '<div class="buttons"><button type="submit">' . $VCSLForPeople . '</button>'
                    . ((!$noCancelButton) ? '<button onclick="location.href =\'$SRBackURL\';" class="SimplOn cancel-form"> $SRVCSLForPeople_Cancel</button>' : '')
                    . '</div></div></fieldset></form>';
                    $ret->html($html);
                }
            }elseif($DatasForMethod == 'NotVCRSL'){  
                $ret = $this->lookForMethodInElementsTree($object,$method);
                $ret['style']->html('');
            }

            return $ret;
        }

        function getDataLayout(SD_Data $Data, string $method){ 

            $specialRendererPath = $this->SimplOn_path.DIRECTORY_SEPARATOR.'Renderers'.DIRECTORY_SEPARATOR.'htmls'.DIRECTORY_SEPARATOR.'specialRenderers'.DIRECTORY_SEPARATOR.'SR_'.$Data->getClass().'.php';


            if(file_exists($specialRendererPath)){
                require_once($specialRendererPath);

               
                $dom = ($Data->getClass().'_SR_special_Get')($Data, $method);
                $this->getStylesAndScriptsLinks($dom);
                //$this->addStylesTagsToAutoCSS($Data,$dom,$method);
                return $dom;

            }else{
             
                if($Data->fixedValue() && in_array(strtolower(substr($method, 4)),SC_Main::$VCRSLFormMethods) ){
                    $method = 'showFixedValue';
                }
                // $dataHTML = \phpQuery::newDocumentHTML($dataHTML);
                // $this->getStylesTagsContent($dataHTML,$object->{'O'.$Data}(),$method);
                $dom = \phpQuery::newDocumentFileHTML($this->layoutPath($Data));
                $dom[".$method>*:first-child"]->addClass($Data->getClassName())->html();
                $dom[".$method>*:first-child"]->addClass('EA_'.$Data->name())->html();
                $this->getStylesAndScriptsLinks($dom);
                $this->addStylesTagsToAutoCSS($Data,$dom,$method);

                return \phpQuery::newDocument( $dom[".$method"]->html() );
            }
        }

        function contains($str, array $arr){
            foreach($arr as $a) {
                if (stripos($str,$a) !== false) return true;
            }
            return false;
        }


        function appendMethodLayout($domOrfileContent, $pathOrObject){

            if($pathOrObject instanceof SE_Element){
                $filePath = $this->layoutPath($pathOrObject);
            }elseif(is_string($pathOrObject)){
                $filePath = $pathOrObject;
            }

            $dom = \phpQuery::newDocumentFileHTML($filePath);

            if(is_string($domOrfileContent)){
                $newContent = \phpQuery::newDocumentHTML($domOrfileContent);
            }elseif($domOrfileContent instanceof phpQueryObject){
                $newContent = $domOrfileContent;
            }


            $dom["body"]->append($newContent);

            // Get The CSS and JS of the base Tamplate
            $this->getStylesAndScriptsLinks($dom);

            // Add all the CSS and JS (from Datas -previously collected- and the base template)
           if($dom["head"]->html()!=''){              
                $dom["head link[rel='stylesheet']"]->remove();
    
                foreach(self::$csslinks as $csslink){

                    if(substr($csslink, 0, 4) == 'http' && substr($csslink, -4) == '.css'){
                        $dom["head"]->append('<link rel="stylesheet" href="'.$csslink.'"  //>');
                    }else{
                        $dom["head"]->append('<link rel="stylesheet" href="'.$this->App_web_root.'/Layouts/css/'.basename($csslink).'" //>');
                    }
                }
                $dom["head script"]->remove();

                foreach(self::$jslinks as $jslink){
                    if(substr($jslink, 0, 4) == 'http' && substr($jslink, -3) == '.js'){
                        $dom["head"]->append('<script type="text/javascript" src="'.$jslink.'"> </script>'."\n");
                    }else{
                        $dom["head"]->append('<script type="text/javascript" src="'.$this->App_web_root.'/Layouts/js/'.basename($jslink).'" /> </script>'."\n");  //NOTE :: space in -" /> </script>- weirdly required
                    }
                }
            }
  
            $fileContent=$dom->html();
            
            
            if(extension_loaded('tidy')){
                $tidy = new tidy;
                $config = array('indent'=> true,'output-xhtml' => false, 'output-html' => true,'wrap'=> 600);
                $tidy->parseString($fileContent, $config, 'utf8');
                $tidy->cleanRepair();
                $fileContent=$tidy.'';
            }
    
            $fileContent=str_replace('href="%24','href="$',$fileContent);
            $fileContent=str_replace('action="%24','action="$',$fileContent); //for some unkonown reason phpQuery changes the $ to %24 in action so this was required fix that

            file_put_contents($filePath, $fileContent);
        } 

        function updateLayoutFile($domOrfileContent, $method, $pathOrObject){

            if($pathOrObject instanceof SE_Element){
                $filePath = $this->layoutPath($pathOrObject);
            }elseif(is_string($pathOrObject)){
                $filePath = $pathOrObject;
            }

            $dom = \phpQuery::newDocumentFileHTML($filePath);

            if(is_string($domOrfileContent)){
                $newContent = \phpQuery::newDocumentHTML($domOrfileContent);
            }elseif($domOrfileContent instanceof phpQueryObject){
                $newContent = $domOrfileContent;
            }


            $dom[".$method"]->replaceWith($newContent);

            // Get The CSS and JS of the base Tamplate
            $this->getStylesAndScriptsLinks($dom);

            // Add all the CSS and JS (from Datas -previously collected- and the base template)
           if($dom["head"]->html()!=''){              
                $dom["head link[rel='stylesheet']"]->remove();
    
                foreach(self::$csslinks as $csslink){

                    if(substr($csslink, 0, 4) == 'http' && substr($csslink, -4) == '.css'){
                        $dom["head"]->append('<link rel="stylesheet" href="'.$csslink.'"  //>');
                    }else{
                        $dom["head"]->append('<link rel="stylesheet" href="'.$this->App_web_root.'/Layouts/css/'.basename($csslink).'" //>');
                    }
                }
                $dom["head script"]->remove();

                foreach(self::$jslinks as $jslink){
                    if(substr($jslink, 0, 4) == 'http' && substr($jslink, -3) == '.js'){
                        $dom["head"]->append('<script type="text/javascript" src="'.$jslink.'"> </script>'."\n");
                    }else{
                        $dom["head"]->append('<script type="text/javascript" src="'.$this->App_web_root.'/Layouts/js/'.basename($jslink).'" /> </script>'."\n");  //NOTE :: space in -" /> </script>- weirdly required
                    }
                }
            }
  
            $fileContent=$dom->html();
            
            
            if(extension_loaded('tidy')){
                $tidy = new tidy;
                $config = array('indent'=> true,'output-xhtml' => false, 'output-html' => true,'wrap'=> 600);
                $tidy->parseString($fileContent, $config, 'utf8');
                $tidy->cleanRepair();
                $fileContent=$tidy.'';
            }
    
            $fileContent=str_replace('href="%24','href="$',$fileContent);
            $fileContent=str_replace('action="%24','action="$',$fileContent); //for some unkonown reason phpQuery changes the $ to %24 in action so this was required fix that

            file_put_contents($filePath, $fileContent);
        } 

        function appendToLayoutFile($domOrfileContent, $pathOrObject){
            if($pathOrObject instanceof SE_Element){
                $filePath = $this->layoutPath($pathOrObject);
            }elseif(is_string($pathOrObject)){
                $filePath = $pathOrObject;
            }
            $dom = \phpQuery::newDocumentFileHTML($filePath);
        

            if(is_string($domOrfileContent)){
                $newContent = \phpQuery::newDocumentHTML($domOrfileContent);
            }elseif($domOrfileContent instanceof phpQueryObject){
                $newContent = $domOrfileContent;
            }

            if($dom["#content"]->html()!=''){
                $dom["#content"]->append($newContent);
            }else{
                $dom["body"]->append($newContent);
            }

            // Get The CSS and JS of the base Tamplate
            $this->getStylesAndScriptsLinks($dom);

            // Add all the CSS and JS (from Datas -previously collected- and the base template)
           if($dom["head"]->html()!=''){              
                $dom["head link[rel='stylesheet']"]->remove();
    
                foreach(self::$csslinks as $csslink){

                    if(substr($csslink, 0, 4) == 'http' && substr($csslink, -4) == '.css'){
                        $dom["head"]->append('<link rel="stylesheet" href="'.$csslink.'"  //>');
                    }else{
                        $dom["head"]->append('<link rel="stylesheet" href="'.$this->App_web_root.'/Layouts/css/'.basename($csslink).'" //>');
                    }
                }
                $dom["head script"]->remove();
                foreach(self::$jslinks as $jslink){
                    if(substr($jslink, 0, 4) == 'http' && substr($jslink, -3) == '.js'){
                        $dom["head"]->append('<script type="text/javascript" src="'.$jslink.'"> </script>'."\n");
                    }else{
                        $dom["head"]->append('<script type="text/javascript" src="'.$this->App_web_root.'/Layouts/js/'.basename($jslink).'" /> </script>'."\n");  //NOTE :: space in -" /> </script>- weirdly required
                    }
                }
            }
  
            $fileContent=$dom->html();
            
            
            if(extension_loaded('tidy')){
                $tidy = new tidy;
                $config = array('indent'=> true,'output-xhtml' => false, 'output-html' => true,'wrap'=> 600);
                $tidy->parseString($fileContent, $config, 'utf8');
                $tidy->cleanRepair();
                $fileContent=$tidy.'';
            }
   
            $fileContent=str_replace('href="%24','href="$',$fileContent);
            $fileContent=str_replace('action="%24','action="$',$fileContent); //for some unkonown reason phpQuery changes the $ to %24 in action so this was required fix that

            file_put_contents($filePath, $fileContent);
     
        } 

        function writeLayoutFile($domOrfileContent, $pathOrObject){
            if($pathOrObject instanceof SE_Element){
                $filePath = $this->layoutPath($pathOrObject);
            }elseif(is_string($pathOrObject)){
                $filePath = $pathOrObject;
            }
            


            if(is_string($domOrfileContent)){
                $dom = \phpQuery::newDocumentHTML($domOrfileContent);
            }elseif($domOrfileContent instanceof phpQueryObject){
                $dom = $domOrfileContent;
            }

            $baseTemplate = \phpQuery::newDocumentFileHTML($this->layoutPath('SE_Element'));
            $baseTemplate["body"]=$dom->html();
            // Get The CSS and JS of the base Tamplate
            $this->getStylesAndScriptsLinks($baseTemplate);

            
            // Add all the CSS and JS (from Datas -previously collected- and the base template)
           if($dom["head"]->html()!=''){              
                $dom["head link[rel='stylesheet']"]->remove();
    
                foreach(self::$csslinks as $csslink){

                    if(substr($csslink, 0, 4) == 'http' && substr($csslink, -4) == '.css'){
                        $dom["head"]->append('<link rel="stylesheet" href="'.$csslink.'"  //>');
                    }else{
                        $dom["head"]->append('<link rel="stylesheet" href="'.$this->App_web_root.'/Layouts/css/'.basename($csslink).'" //>');
                    }
                }
                $dom["head script"]->remove();
                foreach(self::$jslinks as $jslink){
                    if(substr($jslink, 0, 4) == 'http' && substr($jslink, -3) == '.js'){
                        $dom["head"]->append('<script type="text/javascript" src="'.$jslink.'"> </script>'."\n");
                    }else{
                        $dom["head"]->append('<script type="text/javascript" src="'.$this->App_web_root.'/Layouts/js/'.basename($jslink).'" /> </script>'."\n");  //NOTE :: space in -" /> </script>- weirdly required
                    }
                }
            }
  
            $fileContent=$baseTemplate->html();
    
            if(extension_loaded('tidy')){
                $tidy = new tidy;
                $config = array('indent'=> true,'output-xhtml' => false, 'output-html' => true,'wrap'=> 600);
                $tidy->parseString($fileContent, $config, 'utf8');
                $tidy->cleanRepair();
                $fileContent=$tidy.'';
            }
    
            $fileContent=str_replace('href="%24','href="$',$fileContent);
            $fileContent=str_replace('action="%24','action="$',$fileContent); //for some unkonown reason phpQuery changes the $ to %24 in action so this was required fix that

            file_put_contents($filePath, $fileContent);
        }       
 
    function fillDatasInElementTemplate(SE_Element $object, phpQueryObject $dom, string $method){
        $ret = $dom->html();
        $datasInLayout = preg_match_all_callback(
            '/EA_[a-z,_,0-9]+/i',
            $ret,
            function ($match){
                return explode('_',$match[0]);
            }
        );

        if($datasInLayout){
            //$datasInLayout = array_unique($datasInLayout);

            foreach($datasInLayout as $dataInLayout){
                $data = $object->{'O'.$dataInLayout[1]}();
                $dataMethodClass = '';
                if(isset($dataInLayout[2])){
                    $dataMethod=$dataInLayout[2];
                    $dataMethodClass=$dataInLayout[2];
                    $join='_';
                }else{
                    $dataMethod=$method;
                    $join='';
                };
                $dom[".EA_".$data->name().$join.$dataMethodClass]->replaceWith( $this->renderData( $data, $dataMethod, $dom[".EA_".$data->name().$join.$dataMethodClass] ));

            }
        }

        return $dom;
    }

    function fillDataDomWithVariables(SD_Data $Data, phpQueryObject $dom, $action=null){

        $repeatClasses = explode(' ',$dom['.repeat']->attr('class') );
        if($repeatClasses[0]){
            $items = $Data->{$repeatClasses[1]}();
            $itemHtml=$dom['.repeat .item:first']->htmlOuter();
            $filledItemHtml = '';
            $selectedItemHtml=$dom['.repeat .selectedItem:first']->htmlOuter();
            if(is_string(reset($items)) OR is_numeric(reset($items))){
                if(!$itemHtml){ $itemHtml = $dom['.repeat *:first']->htmlOuter(); }
                foreach($items as $key => $value){
                    if( $key === 0 ){ $key = '0'; }else
                    if( $key === ' ' ){ $key = ''; }
                    if($selectedItemHtml && (strval($Data->val()) === strval($key) OR strval($Data->val()) === strval($value))){
                        $filledItemHtml .= str_replace(['$key','$val'],[$key,$value],$selectedItemHtml);
                    }else{
                        $filledItemHtml .= str_replace(['$key','$val'],[$key,$value],$itemHtml);
                    }
                }
                $dom['.repeat']->html($filledItemHtml);
     
            }elseif(reset($items) instanceof SD_Data){
                //TODO
            }
        }


        // $ret = str_replace('%24','$',$dom->html()); 
        $ret = str_replace('="%24','="$',$dom->htmlOuter()); //for some unkonown reason phpQuery changes the $ to %24  so this was required fix that

       

        /** Quick and dirty replace of action */
        $ret = str_replace('$action',$action,$ret); //for some unkonown reason 

        /*
        Substitute the $variables on the Layout with the correspondant Data value.
        Substitute the $SRvariables with the correspondant Renderer Method
        */
        $ret = preg_replace_callback(
            '/(\$)([a-z,_,0-9]+)/i',
            function ($matches) use ($Data){
                $parameters = explode ('_',$matches[2]);
                $method = array_shift($parameters);
                $methodKey = substr($method, 0, 2);

                if($methodKey){
                    $rendermethod = substr($method, 2); 
                }

                // if($method == 'repeat' && (method_exists($Data,$parameters[0]) OR property_exists($Data, $parameters[0]))  && is_array($Data->{$parameters[0]}())  ){
                // }else 
                
                if($methodKey != 'SR' && (method_exists($Data,$method) OR property_exists($Data, $method)) ){ 
                    return call_user_func_array(array($Data, $method), $parameters);
                }elseif($methodKey == 'SR' && method_exists($this,$rendermethod)){

                    array_unshift($parameters,$Data);  
                    return call_user_func_array(array($this, $rendermethod), $parameters);
                }else{
                    if($Data->parent()){
                        return call_user_func_array(array($Data->parent(), $method), $parameters);
                    }
                }
            },
            $ret
        );

        /*
        Remove/fix the required atributes
        */
        $dom = \phpQuery::newDocumentHTML($ret);
        /** @var phpQueryObject $dom */
        $dom[ "*[required='']" ]->removeAttr('required');
        $this->getStylesAndScriptsLinks($dom);
        return $dom;
    }

    function fillVariablesInElementTemplate( SE_Element $element, phpQueryObject $dom, $action=null,$nextStep=null){   

        $ret=$dom->html();
        $fixedActionInDom = str_replace('action="%24','action="$',$dom->html());
        $fixedActionInDom = str_replace('href="%24','href="$',$fixedActionInDom); //for some unkonown reason phpQuery changes the $ to %24 in action so this was required fix that

        /** Quick and dirty replace of action */
        $fixedActionInDom = str_replace('$action',$action,$fixedActionInDom);  

        $ret = preg_replace_callback(
            '/(\$)([a-z,_,0-9]+)/i',
            function ($matches) use ($element,$nextStep){
                // return $matches[2];

                $parameters = explode ('_',$matches[2]);
                $method = array_shift($parameters);
                $methodKey = substr($method, 0, 2);
        
                if($methodKey){
                    $rendermethod = substr($method, 2); 
                }
                if($methodKey != 'SR' && method_exists($element,$method)){
                    return call_user_func_array(array($element, $method), $parameters);
                }elseif($methodKey == 'SR' && method_exists($this,$rendermethod)){
                    array_unshift($parameters,$element);
                    if($nextStep){
                        $parameters[]=$nextStep;
                    }
                    return call_user_func_array(array($this, $rendermethod), $parameters);
                }else{
                    return call_user_func_array(array($element, $method), $parameters);
                }
            },
            $fixedActionInDom 
        );
        $dom = \phpQuery::newDocumentHTML($ret);
        /** @var phpQueryObject $dom */
        $dom[ "*[required='']" ]->removeAttr('required');
        $this->getStylesAndScriptsLinks($dom);
        return $dom;
    }


	function setMessage($message='') {
		SC_Main::$SystemMessage = $message;
	}


	function encodeURL($class = null, $construct_params = null, $method = null, $method_params = null, $dataName = null) {
		$url = '';
		if(isset($class)) {
			// class
			$url.= $this->REMOTE_ROOT . '/' . $this->fixCode(strtr($class,'\\','-'));
			// construct params
			if(!empty($construct_params) && is_array($construct_params)) {
                // $tempArr=array_map(
                //     //['self', 'parameterEncoder'], 
                //     'SR_html::parameterEncoder()',
				// 	$construct_params
				// );
                $tempArr = array();
                foreach($construct_params as $param){
                    $tempArr[]=SR_html::parameterEncoder($param);
                }
			    $url.= '/' . implode('/',$tempArr);
			}
			
			if(isset($dataName) && isset($method)) {
				// Data name
				$url.= $this->URL_METHOD_SEPARATOR . $dataName;
			}
			
			if(isset($method)) {
				// method
				$url.= $this->URL_METHOD_SEPARATOR . $method;
				
				// method params
				if(!empty($method_params) && is_array($method_params)) {
                    $tempArr = array();
                    foreach($method_params as $param){
                        $tempArr[]=SR_html::parameterEncoder($param);
                    }
                    $url.= '/' . implode('/',$tempArr);
				}
			}
		}
        $qs = SC_Main::$URL_METHOD_SEPARATOR;

        if(!empty(SC_Main::$SystemMessage)){ $url.=$qs.$qs.SR_html::parameterEncoder(SC_Main::$SystemMessage); }
		return $url;
	}

	function decodeURL($e = '') {
		$string_delimiter = '\'';
        $qs = SC_Main::$URL_METHOD_SEPARATOR;
        
        if (isset($_SERVER['HTTP_REFERER'])) {
            $server_referal = explode($qs.$qs,$_SERVER['HTTP_REFERER']);
        } else {
            $server_referal = array();
            $server_referal[0] = '';
        }
        $GLOBALS['BackURL']=$server_referal[0];

        // Look if there is a double $qs and take whats at the end as mmesage
        $server_request = explode($qs.$qs,$_SERVER['REQUEST_URI']);
        if (isset($server_request[1])) {$this->setMessage(urldecode($server_request[1]));}
        $server_request = $server_request[0];
        //process the rest of the URL normally
		$server_request = urldecode(substr($server_request, strlen(SC_Main::$REMOTE_ROOT)));
		if(strpos($server_request, '/') !== 0) $server_request = '/' . $server_request;
        $qs = SC_Main::$URL_METHOD_SEPARATOR;
		$sd = $string_delimiter;
		$offset = 0;
		
		
		$parameterDecoder = function($what, $encapsulated = false) use($sd, $qs, $server_request, &$offset) {
			$regexes = array(
				'class' => '\/(?<raw>[^'.$sd.$qs.'\/]+)',
				'construct_params' => '(?:\/(?:(?<raw>[^'.$sd.$qs.'\/]+)|'.$sd.'(?<string>[^'.$sd.']*)'.$sd.'))',
				'dataName' => '\/?'.$qs.'(?<raw>[^'.$sd.$qs.'\/]+)(?='.$qs.')',
				'method' => '\/?'.$qs.'(?<raw>[^'.$sd.$qs.'\/]+)',
				'method_params' => '(?:\/(?:(?<raw>[^'.$sd.$qs.'\/]+)|'.$sd.'(?<string>[^'.$sd.']*)'.$sd.'))',
			);
			if(preg_match('/^'. $regexes[$what] .'/x', substr($server_request, $offset), $matches, PREG_OFFSET_CAPTURE)) {
				$offset+= $matches[0][1] + strlen($matches[0][0]);
				$raw = @$matches['raw'][0];
				$string = @$matches['string'][0];
				
				if(empty($raw) && empty($string)) {
					$return = '';
				} elseif(!empty($raw) && empty($string)) {
					if($raw == 'null') {
						$return = null;
					} elseif($raw == 'false') {
						$return = array(false);
					} elseif(is_numeric($raw)) {
						$return = floatval($raw) == intval($raw)
							? intval($raw)
							: floatval($raw);
					} else {
						$return = $raw;
					}
				} elseif(empty($raw) && !empty($string)) {
					$return = urldecode($this->fixCode($string, false));
				}
				return $encapsulated
					? array($return)
					: $return;
			} else {
				return false;
			}
		};
		
		SC_Main::$class = strtr($parameterDecoder('class'),'-','\\') ?: SC_Main::$DEFAULT_ELEMENT;
		//self::$class = SC_Main::$DEFAULT_ELEMENT; //debug
		SC_Main::$construct_params = array();
		while(($param = $parameterDecoder('construct_params', true)) !== false) {
			SC_Main::$construct_params[] = $param[0];
		}
		
		SC_Main::$dataName = $parameterDecoder('dataName');
		SC_Main::$method = $parameterDecoder('method') ?: SC_Main::$DEFAULT_METHOD;
		
		SC_Main::$method_params = array();
		while(($param = $parameterDecoder('method_params', true)) !== false) {
			SC_Main::$method_params[] = $param[0];
		}


	}

    static function fixCode($string, $encoding = true) {
		return $encoding  
			? strtr($string, array(
				'%2F' => '/',
				'%2527' => '%252527',
				'%27' => '%2527',
				'%255C' => '%25255C',
				'%5C' => '%255C',
			))
			: strtr($string, array(
				'%2527' => '%27',
				'%252527' => '%2527',
				'%255C' => '%5C',
				'%25255C' => '%255C',
			));
	}



    function lookForMethodInElementsTree(SE_Element $element, string $method){
        $Tree = class_parents($element);
        $Tree = array_merge(array($element->getClass()), array_values($Tree));
        $i = '0';
        do {   
            
                
            if($Tree[$i] != 'SE_Element' ){
                $path = $this->layoutsPath($element);
                if(file_exists($path)){
                    $Dom = \phpQuery::newDocumentFileHTML($path);
                }
            }elseif($Tree[$i] == 'SE_Element'){
                $Dom = $this->LoadDefaultLayoutFile();
            }
            $i++;
        } while ( $i<sizeof($Tree) AND (!$Dom OR empty ($Dom[".$method"]->html()) ) );

        $this->getStylesAndScriptsLinks($Dom);
        $this->addStylesTagsToAutoCSS($element, $Dom, $method);
        return $Dom[".$method"];     
    }


    function action(SC_BaseObject $object, string $action, $clean = null, $message = null){

        if($object instanceof SD_ElementContainer OR $object instanceof SD_ElementsContainerMM){
            $object = $object->element();
        }
        if($clean == 'id'){
            return $this->encodeURL($object->getClassName(),array(),$action);
            //return $this->encodeURL($object->getClassName(),array(),$action,array($nextStep));    
        }else{
            return $this->encodeURL($object->getClassName(),array($object->id()),$action);
            //return $this->encodeURL($object->getClassName(),array($object->id()),$action,array($nextStep));
        }
    }


    function layoutsPath($object){
        if(is_a($object,'SD_Data')){
            $dataPath=DIRECTORY_SEPARATOR.'Datas';
            $ancestors = class_parents($object);
            array_splice($ancestors, -1);
            $ancestorClass = $object->getClass();
            while(
                    $ancestorClass 
                    && !file_exists($this->App_path.DIRECTORY_SEPARATOR.'Layouts'.$dataPath.DIRECTORY_SEPARATOR.$ancestorClass.'.html')
                    && !file_exists($this->SimplOn_path.DIRECTORY_SEPARATOR.'Renderers'.DIRECTORY_SEPARATOR.'htmls'.$dataPath.DIRECTORY_SEPARATOR.$ancestorClass.'.html')
                ){
                $ancestorClass = array_shift($ancestors);
            }
            if(file_exists($this->App_path.DIRECTORY_SEPARATOR.'Layouts'.$dataPath.DIRECTORY_SEPARATOR.$ancestorClass.'.html')){
                $ret = $this->App_path.DIRECTORY_SEPARATOR.'Layouts'.$dataPath.DIRECTORY_SEPARATOR.$ancestorClass.'.html';
            }elseif(file_exists($this->SimplOn_path.DIRECTORY_SEPARATOR.'Renderers'.DIRECTORY_SEPARATOR.'htmls'.$dataPath.DIRECTORY_SEPARATOR.$ancestorClass.'.html')){
                $ret = $this->SimplOn_path.DIRECTORY_SEPARATOR.'Renderers'.DIRECTORY_SEPARATOR.'htmls'.$dataPath.DIRECTORY_SEPARATOR.$ancestorClass.'.html';
            }
        }elseif(!is_string($object) && is_a($object,'SE_Element')){
            $ret = $this->App_path.DIRECTORY_SEPARATOR.'Layouts'.DIRECTORY_SEPARATOR.$object->getClass().'.html';

        }elseif(is_string($object)){
            $ret = $this->App_path.DIRECTORY_SEPARATOR.'Layouts'.DIRECTORY_SEPARATOR.$object.'.html';
        }

        return $ret;
    }


    /* methods related with getting/generatting the Layouts */
    function  LoadDefaultLayoutFile(){
        $simplonBase = $this->SimplOn_path.DIRECTORY_SEPARATOR.'Renderers'.DIRECTORY_SEPARATOR.'htmls'.DIRECTORY_SEPARATOR.'SE_Element.html';
        $appBase = $this->App_path.DIRECTORY_SEPARATOR.'Layouts'.DIRECTORY_SEPARATOR.'SE_Element.html';
        if(file_exists($appBase)){ $dom = \phpQuery::newDocumentFileHTML($appBase); }
        else{  $dom = \phpQuery::newDocumentFileHTML($simplonBase);  }
        $this->getStylesAndScriptsLinks($dom);
        return $dom;
    }

	function link($content, $href, array $extra_attrs = array(), $auto_encode = true) {
		$extra = array();
		foreach($extra_attrs as $attr => $value) {
			if($auto_encode) $value = htmlentities($value, ENT_COMPAT, 'UTF-8');
			$extra[] = $attr.'="'.$value.'"';
		}
		if($auto_encode) {
			$href = htmlentities($href, ENT_COMPAT, 'UTF-8');
			//$content = htmlentities($content, ENT_COMPAT, 'UTF-8');
		}
		return '<a '.implode(' ',$extra).' href="'.$href.'">'.$content.'</a>';
    }



	static function parameterEncoder($p){
		if(is_string($p)) {
		    $string_delimiter = '\'';
			$p = self::fixCode(urlencode($p));
			return $string_delimiter. $p .$string_delimiter;
		} else {
			return urlencode($p);
		}
	}


}