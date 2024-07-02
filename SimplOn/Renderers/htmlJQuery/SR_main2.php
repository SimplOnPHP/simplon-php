<?php

class SR_main2 extends SC_BaseObject
{


    protected
        $SimplOn_path,
        $App_path,
        $App_web_root,

        $cssWebRoot,
        $jsWebRoot,
        $imgsWebRoot,

        $URL_METHOD_SEPARATOR = '!',
        $WEB_ROOT;

    static
        $layoutsCache,
        $csslinks,
        $jslinks;


    function __construct()
    {
        if (empty(self::$layoutsCache)) {
            self::$layoutsCache = [];
        }
        if (empty(self::$jslinks)) {
            self::$jslinks = [];
        }
        if (empty(self::$csslinks)) {
            self::$csslinks = [];
        }



    }

    public function App_web_root($App_web_root = null)
    {
        if($App_web_root){
            $this->App_web_root =   $App_web_root;
            $this->cssWebRoot =   $App_web_root.'/Layouts/css';
            $this->jsWebRoot =    $App_web_root.'/Layouts/js';
            $this->imgsWebRoot =  $App_web_root.'/Layouts/imgs';
        }else{
            return $this->App_web_root;
        }
    }

    function getDataLayout(SD_Data $Data, string $method)
    {
        $layoutName = $Data->getClass() . '::' . $method;
        if (!isset(self::$layoutsCache[$layoutName])) {
            $specialRendererPath = $this->SimplOn_path . DIRECTORY_SEPARATOR . 'Renderers' . DIRECTORY_SEPARATOR . $GLOBALS['redenderFlavor'] . DIRECTORY_SEPARATOR . 'htmls' . DIRECTORY_SEPARATOR . 'specialRenderers' . DIRECTORY_SEPARATOR . 'SR_' . $Data->getClass() . '.php';
            if (file_exists($specialRendererPath)) {
                require_once($specialRendererPath);
                $dom = ($Data->getClass() . '_SR_special_Get')($Data, $method);
                $this->getStylesAndScriptsLinks($dom);
                //$this->addStylesTagsToAutoCSS($Data,$dom,$method);
                self::$layoutsCache[$layoutName] = $dom;
                return \phpQuery::newDocument($dom[".$method"]->html()); // returns a copy to avoid overwring
            } else {
                if ($Data->fixedValue() && in_array($method, $Data->parent()::$formMethods)) {
                    $method = 'showFixedValue';
                }
                // $dataHTML = \phpQuery::newDocumentHTML($dataHTML);
                // $this->getStylesTagsContent($dataHTML,$element->{'O'.$Data}(),$method);
                $dom = \phpQuery::newDocumentFileHTML($this->layoutPath($Data));
                $dom[".$method>*:first-child"]->addClass($Data->getClass())->html();
                $this->getStylesAndScriptsLinks($dom);
                $this->addStylesTagsToAutoCSS($Data, $dom, $method);
                self::$layoutsCache[$layoutName] = \phpQuery::newDocument($dom[".$method"]->html());
                $dom[".$method>*:first-child"]->addClass('EA_' . $Data->name())->html();
                self::$layoutsCache[$layoutName] = $dom;
                return \phpQuery::newDocument(trim($dom[".$method"]->html(), "\r\n")); // returns a copy to avoid overwring
            }
        } else {
            $dom = self::$layoutsCache[$layoutName];
            $dom[".$method>*:first-child"]->addClass('EA_' . $Data->name())->html();
            return \phpQuery::newDocument($dom[".$method"]->html()); // returns a copy to avoid overwring
        }
    }

    function layoutPath($object)
    {
        if (gettype($object) == 'object' && is_a($object, 'SC_BaseObject')) {

            $ret = $this->App_path . DIRECTORY_SEPARATOR . 'Layouts' . DIRECTORY_SEPARATOR . $object->getClass() . '.html';

            if (is_a($object, 'SD_Data')) {
                $dataPath = DIRECTORY_SEPARATOR . 'Datas';
            }

            if (is_a($object, 'SID_Data') or is_a($object, 'SID_ComplexData')) {
                $dataPath = DIRECTORY_SEPARATOR . 'InterfaceDatas';
            }

            if (is_a($object, 'SC_Element')) {
                $dataPath = '';
            }

            $ancestors = class_parents($object);
            array_splice($ancestors, -1);
            $ancestorClass = $object->getClass();

            while (
                $ancestorClass
                && !file_exists($this->App_path . DIRECTORY_SEPARATOR . 'Layouts' . $dataPath . DIRECTORY_SEPARATOR . $ancestorClass . '.html')
                && !file_exists($this->SimplOn_path . DIRECTORY_SEPARATOR . 'Renderers' . DIRECTORY_SEPARATOR . $GLOBALS['redenderFlavor'] . DIRECTORY_SEPARATOR . 'htmls' . $dataPath . DIRECTORY_SEPARATOR . $ancestorClass . '.html')
            ) {
                $ancestorClass = array_shift($ancestors);
            }
            if (file_exists($this->App_path . DIRECTORY_SEPARATOR . 'Layouts' . $dataPath . DIRECTORY_SEPARATOR . $ancestorClass . '.html')) {
                $ret = $this->App_path . DIRECTORY_SEPARATOR . 'Layouts' . $dataPath . DIRECTORY_SEPARATOR . $ancestorClass . '.html';
            } elseif (file_exists($this->SimplOn_path . DIRECTORY_SEPARATOR . 'Renderers' . DIRECTORY_SEPARATOR . $GLOBALS['redenderFlavor'] . DIRECTORY_SEPARATOR . 'htmls' . $dataPath . DIRECTORY_SEPARATOR . $ancestorClass . '.html')) {
                $ret = $this->SimplOn_path . DIRECTORY_SEPARATOR . 'Renderers' . DIRECTORY_SEPARATOR . $GLOBALS['redenderFlavor'] . DIRECTORY_SEPARATOR . 'htmls' . $dataPath . DIRECTORY_SEPARATOR . $ancestorClass . '.html';
            }
        } elseif (gettype($object) == 'object' && !is_a($object, 'SC_BaseObject')) {
            throw new SR_RendererException('This function can only get the path for Simplon  Datas and Elements');
        } elseif (is_string($object)) {
            $ret = $this->App_path . DIRECTORY_SEPARATOR . 'Layouts' . DIRECTORY_SEPARATOR . $object . '.html';
            if (!file_exists($ret)) {
                $ret = $this->SimplOn_path . DIRECTORY_SEPARATOR . 'Renderers' . DIRECTORY_SEPARATOR . $GLOBALS['redenderFlavor'] . DIRECTORY_SEPARATOR . 'htmls' . $dataPath . DIRECTORY_SEPARATOR . $object . '.html';
            }
            if (!file_exists($ret)) {
                throw new SR_RendererException('Tehere is no template for ' . $object);
            }
        }
        return $ret;
    }

    function directlayoutPath($object)
    {
        if (gettype($object) == 'object' && is_a($object, 'SC_BaseObject')) {

            if (is_a($object, 'SD_Data')) {
                $dataPath = DIRECTORY_SEPARATOR . 'Datas';
            }
            if (is_a($object, 'SID_Data')) {
                $dataPath = DIRECTORY_SEPARATOR . 'InterfaceDatas';
            }
            if (is_a($object, 'SC_Element')) {
                $dataPath = '';
            }
            if (file_exists($this->SimplOn_path . DIRECTORY_SEPARATOR . 'Renderers' . DIRECTORY_SEPARATOR . $GLOBALS['redenderFlavor'] . DIRECTORY_SEPARATOR . 'htmls' . $dataPath . DIRECTORY_SEPARATOR . $object->getClass() . '.html')) {
                $ret = $this->SimplOn_path . DIRECTORY_SEPARATOR . 'Renderers' . DIRECTORY_SEPARATOR . $GLOBALS['redenderFlavor'] . DIRECTORY_SEPARATOR . 'htmls' . $dataPath . DIRECTORY_SEPARATOR . $object->getClass() . '.html';
            } else {
                $ret = $this->App_path . DIRECTORY_SEPARATOR . 'Layouts' . $dataPath . DIRECTORY_SEPARATOR . $object->getClass() . '.html';
            }
        } elseif (gettype($object) == 'object' && !is_a($object, 'SC_BaseObject')) {
            throw new SR_RendererException('This function can only get the path for Simplon  Datas and Elements');
        } elseif (is_string($object)) {
            $ret = $this->App_path . DIRECTORY_SEPARATOR . 'Layouts' . DIRECTORY_SEPARATOR . $object . '.html';
            if (!file_exists($ret)) {
                $ret = null;
            }
        }

        return $ret;
    }

    /* methods related with the JS and CSS */
    function getJSandCSS(phpQueryObject $dom)
    {
        $this->getStylesAndScriptsLinks($dom);
        //TODO get other scripts and Styles???
    }

    function getStylesAndScriptsLinks(phpQueryObject $dom)
    {
        // get all the CSS Links
        foreach ($dom["head link[rel='stylesheet']"] as $link) {
            $link = pq($link)->attr('href');
            if (substr($link, 0, 4) == 'http' && substr($link, -4) == '.css') {
                self::$csslinks[$link] = $link;
            } elseif (substr($link, -4) == '.css') {
                self::$csslinks[basename($link)] = $link;
            }
        }

        // get all the JS Links
        foreach ($dom["head script"] as $link) {
            $link = pq($link)->attr('src');
            if (substr($link, 0, 4) == 'http' && substr($link, -3) == '.js') {
                self::$jslinks[$link] = $link;
            } elseif (substr($link, -3) == '.js') {
                self::$jslinks[basename($link)] = $link;
            }
        }
    }

    function addStylesTagsToAutoCSS(SC_BaseObject $data, phpQueryObject $dom, string $method)
    {
        global $cssTagsContent;
        // get the style tags of the method

        $cssTagsContent[$data->getClass()] = array(
            'method' => $method,
            'style' => $dom['style']->html()
        );

        $minifyed = minify_css($cssTagsContent[$data->getClass()]['style']);
        $minifyedWithMarks = "/* START_" . $data->getClass() . " */\n $minifyed \n/* END_" . $data->getClass() . " */";

        $file = $this->App_path . DIRECTORY_SEPARATOR . 'Layouts' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'simplon-auto.css';
        $currentStylesFile = file_get_contents($file);

        $regEx = '/(\/\* START_' . $data->getClass() . ' \*\/)(\\n.*\\n)(\/\* END_' . $data->getClass() . ' \*\/)/';
        preg_match($regEx, $currentStylesFile, $currentStile);

        if (isset($currentStile[2]) && $currentStile[2] != $minifyed) {
            $StylesForFile = preg_replace($regEx, $minifyedWithMarks, $currentStylesFile);

            if (!empty($currentStile[2]) and $StylesForFile) {
                file_put_contents($file, $StylesForFile);
            } elseif (!empty(trim($minifyed))) {
                file_put_contents($file, $currentStylesFile . "\n" . $minifyedWithMarks);
            }
        }
    }


    function addCSSandJSLinksToTemplate($dom)
    {
        if ($dom["head"]->html() != '') {
            $dom["head link[rel='stylesheet']"]->remove();

            foreach (self::$csslinks as $csslink) {

                if (substr($csslink, 0, 4) == 'http' && substr($csslink, -4) == '.css') {
                    $dom["head"]->append('<link rel="stylesheet" href="' . $csslink . '"  //>');
                } else {
                    $dom["head"]->append('<link rel="stylesheet" href="' . $this->cssWebRoot . '/' . basename($csslink) . '" //>');
                }
            }
            $dom["head script"]->remove();
            foreach (self::$jslinks as $jslink) {
                if (substr($jslink, 0, 4) == 'http' && substr($jslink, -3) == '.js') {
                    $dom["head"]->append('<script type="text/javascript" src="' . $jslink . '"> </script>' . "\n");
                } else {
                    $dom["head"]->append('<script type="text/javascript" src="' . $this->jsWebRoot . '/' . basename($jslink) . '" /> </script>' . "\n");  //NOTE :: space in -" /> </script>- weirdly required
                }
            }
        }
        return $dom;
    }

    function fillDatasInElementLayout(SC_Element $element, string $method, string $ret)
    {

        //$ret=$dom->html();
        $dom = \phpQuery::newDocument($ret);

        $datasInLayout = preg_match_all_callback(
            '/EA_[a-z,_,0-9]+/i',
            $ret,
            function ($match) {
                return explode('_', $match[0]);
            }
        );

        if ($datasInLayout) {
            //$datasInLayout = array_unique($datasInLayout);

            foreach ($datasInLayout as $dataInLayout) {

                $data = $element->{'O' . $dataInLayout[1]}();
                $dataMethodClass = '';
                if (isset($dataInLayout[2])) {
                    $dataMethod = $dataInLayout[2];
                    $dataMethodClass = $dataInLayout[2];
                    $join = '_';
                } else {
                    $dataMethod = $method;
                    $join = '';
                };

                $dom[".EA_" . $data->name() . $join . $dataMethodClass]->replaceWith($this->fillDataDomWithVariables($data,  $dom[".EA_" . $data->name() . $join . $dataMethodClass]));
            }
        }

        return $dom;
    }

    function fillVariablesInElementLayout(SC_Element $element, phpQueryObject $dom)
    {

        $ret = $dom->html();
        $ret = $dom;
        $fixedActionInDom = str_replace('"%24', '"$', $dom->html());
        $fixedActionInDom = str_replace('/%24', '/$', $fixedActionInDom);

        $ret = preg_replace_callback(
            '/(\$)([a-z,:,_,0-9]+)/i',
            function ($matches) use ($element) {
                // return $matches[2];
                $parameters = explode('_', $matches[2]);
                $method = array_shift($parameters);
                $methodKey = substr($method, 0, 2);
                $rendermethod = substr($method, 2);


                if ($methodKey != 'SR' && method_exists($element, $method)) {
                    return call_user_func_array(array($element, $method), $parameters);
                } elseif ($methodKey == 'SR' &&  (method_exists($this, $method) or property_exists($this, $method))) {
                    array_unshift($parameters, $element);
                    return call_user_func_array(array($this, $rendermethod), $parameters);
                } else {
                    return call_user_func_array(array($element, $method), $parameters);
                }
            },
            $fixedActionInDom
        );
        $dom = \phpQuery::newDocumentHTML($ret);
        /** @var phpQueryObject $dom */
        $dom["*[required='']"]->removeAttr('required');
        $this->getStylesAndScriptsLinks($dom);
        return $dom;
    }

    function renderData($Data, $method, $template = null, $messages = null, $action = null, $nextStep = null)
    {
        $specialRendererPath = $this->SimplOn_path . DIRECTORY_SEPARATOR . 'Renderers' . DIRECTORY_SEPARATOR . $GLOBALS['redenderFlavor'] . DIRECTORY_SEPARATOR . 'htmls' . DIRECTORY_SEPARATOR . 'specialRenderers' . DIRECTORY_SEPARATOR . 'SR_' . $Data->getClass() . '.php';

        //Fill the template
        if (!file_exists($specialRendererPath)) { //normal 

            if (!$template or SC_Main::$Layouts_Processing == 'OverWrite' or SC_Main::$Layouts_Processing == 'OnTheFly') {
                $template = $this->getDataLayout($Data, $method);
            }
            $ret = $this->fillDataDomWithVariables($Data, $template);
        } else {      //Special

            require_once($specialRendererPath);

            $className = get_class($Data);
            $SR_special_Get = $className . '_SR_special_Get';
            $SR_special_Check = $className . '_SR_special_Check';

            $specialCheck = $SR_special_Check($Data, $template, $method);

            if (!$template or SC_Main::$Layouts_Processing === 'OverWrite' or SC_Main::$Layouts_Processing == 'OnTheFly') {
                $template = $SR_special_Get($Data, $method);
            } elseif ($specialCheck !== 'Ok') {
                //TODO: Doing the same that above review what really ned to be done to consider the template and Update 
                $template = $SR_special_Get($Data, $method);
            }
            $SR_special_Fill = $Data->getClass() . '_SR_special_Fill';

            $ret = $SR_special_Fill($Data, $template);
        }

        return $ret;
    }

    function renderElement(SC_Element $element, string $method, $output = null, $Layout = null)
    {
        //$output = 'AE_fullPage' 
        //Clean the Sysmessage so it's not added to the URLs
        $SystemMessage = SC_Main::$SystemMessage;
        SC_Main::$SystemMessage = '';

        // get (or make) the template
        if (!$Layout) {
            $Layout = $this->getElementLayout($element, $method);
        }

        //Fill the template 
        $Layout = $this->fillDatasInElementLayout($element, $method, $Layout);
        $Layout = $this->fillVariablesInElementLayout($element, $Layout);

        if ($output) {
            $output = new $output();
            $output->message($SystemMessage);
            $output->content($Layout->html());
            $outputtemplate = $this->directlayoutPath($output, 'showView');
            $outputtemplate = \phpQuery::newDocumentFileHTML($outputtemplate);

            $this->getJSandCSS($outputtemplate);
            $outputtemplate = $this->addCSSandJSLinksToTemplate($outputtemplate);
            return $this->fillDatasInElementLayout($output, 'showView', $outputtemplate);
        } else {
            //if the message has not been printed reset it
            SC_Main::$SystemMessage = $SystemMessage;
            return $Layout->html();
        }
    }

    
    function renderInterface(SR_interfaceItem $interface, string $method, $output = null, $Layout = null)
    {
        // get (or make) the template
        if (!$Layout) {
            $Layout = $interface->getLayout($method);
        }

        //Fill the template 
        $Layout = $interface->fillLayout($Layout);

        if ($output) {
            $output = new $output();
            $output->message($SystemMessage);
            $output->content($Layout->html());
            $outputtemplate = $this->directlayoutPath($output, 'showView');
            $outputtemplate = \phpQuery::newDocumentFileHTML($outputtemplate);

            $this->getJSandCSS($outputtemplate);
            $outputtemplate = $this->addCSSandJSLinksToTemplate($outputtemplate);
            return $this->fillDatasInElementLayout($output, 'showView', $outputtemplate);
        } else {
            //if the message has not been printed reset it
            SC_Main::$SystemMessage = $SystemMessage;
            return $Layout->html();
        }
    }

    function createMethodLayout(SC_Element $object,$method,$action=null,$noCancelButton=false){ 

        $layoutName = $object->getClass().'::'.$method;
        if(!isset(self::$layoutsCache[$layoutName])){
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
            self::$layoutsCache[$layoutName] = \phpQuery::newDocument( $ret );
            return \phpQuery::newDocument( $ret ); // returns a copy to avoid overwring
        }else{ 
            $ret = self::$layoutsCache[$layoutName];
            return \phpQuery::newDocument( $ret[".$method"]->html() ); // returns a copy to avoid overwring
        }
    }

    function getElementLayout($object, $method, $noCancelButton=false){

        $layoutName = $object->getClass().'::'.$method;
        if(!isset(self::$layoutsCache[$layoutName])){
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
            }elseif( !file_exists($directlayoutPath) OR (SC_Main::$Layouts_Processing =='OnTheFly') ){

                // If there is no file create  the template
                $dom = $this->createMethodLayout($object,$method, null,$noCancelButton);
                $dom = "<section class='".$object->getClass()." $method'>". $dom->html()."</section>";

                if( SC_Main::$Layouts_Processing !='OnTheFly'){
                    $this->writeLayoutFile($dom,$directlayoutPath);
                }
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
            }elseif( SC_Main::$Layouts_Processing == 'OverWrite' ){ 
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
            }elseif( SC_Main::$Layouts_Processing == 'Update' AND !str_starts_with($check,'Ok') ){  
                // If Update and not in syc with Element or there is no section to render the method create the section
                $dom = $this->updateMethodLayout($object,$method,$changes, null, $noCancelButton);
                $dom = "<section class='".$object->getClass()." $method'>". $dom->html()."</section>";
                $this->updateLayoutFile($dom,$method, $directlayoutPath);
            }elseif(  
                SC_Main::$Layouts_Processing == 'Preserve' 
                OR 
                (SC_Main::$Layouts_Processing == 'Update' AND str_starts_with($check,'Ok') ) 
                ){

                // if Preserve or template in sync, render the Element with it.
                $dom = \phpQuery::newDocumentFileHTML($directlayoutPath);
                $this->getStylesAndScriptsLinks($dom);
                $dom = $dom['.'.$method];

            }
            //Returning the same $dom creates unusual flows I think there is trigger or something that alters the dom if the file is altered
            if($dom instanceof phpQueryObject){ $dom = $dom->htmlOuter(); }

            self::$layoutsCache[$layoutName] = \phpQuery::newDocumentHTML($dom); // save a copy to avoid overwring
            return \phpQuery::newDocumentHTML($dom); // returns a copy to avoid overwring
        }else{ 
            $dom = self::$layoutsCache[$layoutName];
            return \phpQuery::newDocument( $dom[".$method"]->html() ); // returns a copy to avoid overwring
        }
    }


    function fillDataDomWithVariables(SD_Data $Data, phpQueryObject $dom, $action = null)
    {

        $repeatClasses = explode(' ', $dom['.repeat']->attr('class'));

        if ($repeatClasses[0]) {
            $items = $Data->{$repeatClasses[1]}();
            $itemHtml = $dom['.repeat .item:first']->htmlOuter();
            $filledItemHtml = '';
            $selectedItemHtml = $dom['.repeat .selectedItem:first']->htmlOuter();
            if (is_string(reset($items)) or is_numeric(reset($items))) {
                if (!$itemHtml) {
                    $itemHtml = $dom['.repeat *:first']->htmlOuter();
                }
                foreach ($items as $key => $value) {
                    if ($key === 0) {
                        $key = '0';
                    } else
                    if ($key === ' ') {
                        $key = '';
                    }
                    if ($selectedItemHtml && (strval($Data->val()) === strval($key) or strval($Data->val()) === strval($value))) {
                        $filledItemHtml .= str_replace(['$key', '$val'], [$key, $value], $selectedItemHtml);
                    } else {
                        $filledItemHtml .= str_replace(['$key', '$val'], [$key, $value], $itemHtml);
                    }
                }
                $dom['.repeat']->html($filledItemHtml);
            } elseif (reset($items) instanceof SD_Data) {
                //TODO
            }
        }


        // $ret = str_replace('%24','$',$dom->html()); 
        $ret = str_replace('="%24', '="$', $dom->htmlOuter());
        $ret = str_replace('/%24', '/$', $ret);  //for some unkonown reason phpQuery changes the $ to %24  so this was required fix that



        /** Quick and dirty replace of action */
        $ret = str_replace('$action', $action, $ret); //for some unkonown reason 

        /*
        Substitute the $variables on the Layout with the correspondant Data value.
        Substitute the $SRvariables with the correspondant Renderer Method
        */
        $ret = preg_replace_callback(
            '/(\$)([a-z,_,0-9]+)/i',
            function ($matches) use ($Data) {
                $parameters = explode('_', $matches[2]);
                $method = array_shift($parameters);
                $methodKey = substr($method, 0, 2);
                $rendermethod = substr($method, 2);

                if ($methodKey != 'SR' && (method_exists($Data, $method) or property_exists($Data, $method))) {
                    return call_user_func_array(array($Data, $method), $parameters);
                } elseif ($methodKey == 'SR' && (method_exists($this, $rendermethod) or property_exists($this, $rendermethod))) {
                    if (method_exists($this, $rendermethod)) {
                        array_unshift($parameters, $Data);
                    }
                    return call_user_func_array(array($this, $rendermethod), $parameters);
                } else {
                    if ($Data->parent()) {
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
        $dom["*[required='']"]->removeAttr('required');
        $this->getStylesAndScriptsLinks($dom);
        return $dom;
    }

    function requiredText(SC_BaseObject $object)
    {
        if ($object->required()) {
            return 'required';
        } else {
            return '';
        }
    }

    function action(SC_BaseObject $object, string $action, $clean = null)
    {

        if ($object instanceof SD_ElementContainer or $object instanceof SD_ElementsContainerMM) {
            $object = $object->element();
        }
        if ($clean == 'id') {
            return $this->encodeURL($object->getClass(), array(), $action);
            //return $this->encodeURL($object->getClass(),array(),$action,array($nextStep));    
        } else {
            return $this->encodeURL($object->getClass(), array($object->getId()), $action);
            //return $this->encodeURL($object->getClass(),array($object->id()),$action,array($nextStep));
        }
    }

    function encodeURL($class = null, $construct_params = null, $method = null, $method_params = null, $dataName = null)
    {
        $url = '';
        if (isset($class)) {
            // class
            $url .= $this->App_web_root . '/' . $this->fixCode(strtr($class, '\\', '-'));
            // construct params
            if (!empty($construct_params) && is_array($construct_params)) {
                // $tempArr=array_map(
                //     //['self', 'parameterEncoder'], 
                //     'SR_main::parameterEncoder()',
                // 	$construct_params
                // );
                $tempArr = array();
                foreach ($construct_params as $param) {
                    $tempArr[] = SR_main::parameterEncoder($param);
                }
                $url .= '/' . implode('/', $tempArr);
            }

            if (isset($dataName) && isset($method)) {
                // Data name
                $url .= $this->URL_METHOD_SEPARATOR . $dataName;
            }

            if (isset($method)) {
                // method
                $url .= $this->URL_METHOD_SEPARATOR . $method;

                // method params
                if (!empty($method_params) && is_array($method_params)) {
                    $tempArr = array();
                    foreach ($method_params as $param) {
                        $tempArr[] = SR_main::parameterEncoder($param);
                    }
                    $url .= '/' . implode('/', $tempArr);
                }
            }
        }
        $qs = SC_Main::$URL_METHOD_SEPARATOR;

        if (!empty(SC_Main::$SystemMessage)) {
            $url .= $qs . $qs . SR_main::parameterEncoder(SC_Main::$SystemMessage);
        }
        return $url;
    }

    static function fixCode($string, $encoding = true)
    {
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
}
