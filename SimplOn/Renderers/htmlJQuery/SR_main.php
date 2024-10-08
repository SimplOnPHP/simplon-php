<?php

use voku\helper\HtmlDomParser;

class SR_main extends SC_BaseObject {

    protected
        $SimplOn_path,
        $App_path,
        $App_web_root,

        $cssWebRoot,
        $jsWebRoot, 
        $imgsWebRoot,
        $imgsPath,

        $URL_METHOD_SEPARATOR ='!',
        $WEB_ROOT;

    static
        $layoutsCache,
        $outputtemplate,
        $csslinks,
        $jslinks = array();

        function render($object, $method = null, $forTemplateOf = null, $template = null){

            if($object instanceof SD_Data){
                if(method_exists($object, $method)){
                    $object->{$method}();
                } else { $dom = $this->getDataLayout($object, $method); }
            }elseif ($object instanceof SI_Item) {  
                if(method_exists($object, $method)){
                    $object->{$method}();
                } else { 
                    $object->name($object->getClass().'_'.$method); 
                    $dom = $this->getItemLayout($object);     
                }
            }elseif ($object instanceof SI_Container) {       
                $dom = $this->getContainerLayout($object, $method, $forTemplateOf); 
            }elseif ($object instanceof SI_Page) {
                $dom = $this->getPageLayout($object);
                $dom = $this->addCSSandJSLinksToTemplate($dom); 
            }

            return $this->fillDomWithObject($object, $dom->html());
        }

        function renderFullPage($object, $method = null, $forTemplateOf = null, $template = null){
            $content = $this->render($object, $method, $forTemplateOf, $template );
            $page = new SI_FullPage($content,SC_Main::$App_Name);
            return $this->render($page);
        }

        function renderBasicPage($object, $method = null, $forTemplateOf = null, $template = null){
            $content = $this->render($object, $method, $forTemplateOf, $template );
      
            $page = new SI_BasicPage($content,SC_Main::$App_Name);
            return $this->render($page);
        }


        //--------------------------------
        function action($object, string $action, $clean = null, $message = null){
    

            if ($object instanceof SD_ElementContainer or $object instanceof SD_ElementsContainerMM) {
                $object = $object->element();
            }

            if ($object instanceof SC_BaseObject) {
                $class = $object->getClass();
            }elseif(is_string($object)){
                $class = $object;
                $clean = 'id';
            }


            if(!empty($message)){ $message = SC_Main::$URL_METHOD_SEPARATOR.SC_Main::$URL_METHOD_SEPARATOR.urlencode($message); }

            if ($clean == 'id') {
                return $this->encodeURL($class, array(), $action).$message;
                //return $this->encodeURL($object->getClass(),array(),$action,array($nextStep));    
            } else {
                return $this->encodeURL($class, array($object->getId()), $action).$message;
                //return $this->encodeURL($object->getClass(),array($object->id()),$action,array($nextStep));
            }
        }
 
        function encodeURL($class = null, $construct_params = null, $method = null, $method_params = null, $dataName = null) {
            $url = '';
            if(isset($class)) {
                // class
                $url.= $this->App_web_root . '/' . $this->fixCode(strtr($class,'\\','-'));
                // construct params
                if(!empty($construct_params) && is_array($construct_params)) {
                    // $tempArr=array_map(
                    //     //['self', 'parameterEncoder'], 
                    //     'SR_main::parameterEncoder()',
                    // 	$construct_params
                    // );
                    $tempArr = array();
                    foreach($construct_params as $param){
                        $tempArr[]=$this->parameterEncoder($param);
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
                            $tempArr[]=$this->parameterEncoder($param);
                        }
                        $url.= '/' . implode('/',$tempArr);
                    }
                }
            }
            $qs = SC_Main::$URL_METHOD_SEPARATOR;
    
            return $url;
        }
 
        static function fixCode($string, $encoding = true){
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

        static function parameterEncoder($p) {
            if(is_string($p)) {
                $string_delimiter = '"';
                $p = self::fixCode(urlencode($p));
                return $string_delimiter. $p .$string_delimiter;
            } else {
                return urlencode($p);
            }
        }
        
        function setMessage($message='') {
            SC_Main::$SystemMessage = $message;
        }
        //--------------------------------



        function layoutPath($object){
            if(gettype($object) == 'object' && is_a($object,'SC_BaseObject')){

                //$ret = $this->App_path.DIRECTORY_SEPARATOR.'Layouts'.DIRECTORY_SEPARATOR.$object->getClass().'.html';

                if(is_a($object,'SD_Data')){ $dataPath=DIRECTORY_SEPARATOR.'Datas'; }
                else if(is_a($object,'SI_Item')){ $dataPath=DIRECTORY_SEPARATOR.'InterfaceItems'; }
                else if(is_a($object,'SI_Page')){ $dataPath=DIRECTORY_SEPARATOR.'InterfaceItems'; }
                else if(is_a($object,'SI_Container')){ $dataPath=DIRECTORY_SEPARATOR.'InterfaceItems'; }
                else if(is_a($object,'SC_Element')){ $dataPath=''; }

                $ancestors = class_parents($object);
                array_splice($ancestors, -1);
                $ancestorClass = $object->getClass();
                while(
                        $ancestorClass 
                        && !file_exists($this->App_path.DIRECTORY_SEPARATOR.'Layouts'.$dataPath.DIRECTORY_SEPARATOR.$ancestorClass.'.html')
                        && !file_exists($this->SimplOn_path.DIRECTORY_SEPARATOR.'Renderers'.DIRECTORY_SEPARATOR.$GLOBALS['redenderFlavor'].DIRECTORY_SEPARATOR.'htmls'.$dataPath.DIRECTORY_SEPARATOR.$ancestorClass.'.html')
                    ){
                    $ancestorClass = array_shift($ancestors);
                }
               
                if(file_exists($this->App_path.DIRECTORY_SEPARATOR.'Layouts'.$dataPath.DIRECTORY_SEPARATOR.$ancestorClass.'.html')){
                    $ret = $this->App_path.DIRECTORY_SEPARATOR.'Layouts'.$dataPath.DIRECTORY_SEPARATOR.$ancestorClass.'.html';
                }elseif(file_exists($this->SimplOn_path.DIRECTORY_SEPARATOR.'Renderers'.DIRECTORY_SEPARATOR.$GLOBALS['redenderFlavor'].DIRECTORY_SEPARATOR.'htmls'.$dataPath.DIRECTORY_SEPARATOR.$ancestorClass.'.html')){
                    $ret = $this->SimplOn_path.DIRECTORY_SEPARATOR.'Renderers'.DIRECTORY_SEPARATOR.$GLOBALS['redenderFlavor'].DIRECTORY_SEPARATOR.'htmls'.$dataPath.DIRECTORY_SEPARATOR.$ancestorClass.'.html';
                }
            }elseif(gettype($object) == 'object' && !is_a($object,'SC_BaseObject')){  
                throw new SR_RendererException('This function can only get the path for Simplon  Datas and Elements');
            }elseif(is_string($object)){
                $ret = $this->App_path.DIRECTORY_SEPARATOR.'Layouts'.DIRECTORY_SEPARATOR.$object.'.html';
                if(!file_exists($ret)){
                    $ret = $this->SimplOn_path.DIRECTORY_SEPARATOR.'Renderers'.DIRECTORY_SEPARATOR.$GLOBALS['redenderFlavor'].DIRECTORY_SEPARATOR.'htmls'.$dataPath.DIRECTORY_SEPARATOR.$object.'.html';
                }
                if(!file_exists($ret)){
                    throw new SR_RendererException('Tehere is no template for '.$object);
                }
            }

            return $ret;
        }

        function directlayoutPath($object){
            if(gettype($object) == 'object' && is_a($object,'SC_BaseObject')){

                if(is_a($object,'SD_Data')){ $dataPath=DIRECTORY_SEPARATOR.'Datas'; }
                if(is_a($object,'SID_Data')){ $dataPath=DIRECTORY_SEPARATOR.'InterfaceDatas'; }
                if(is_a($object,'SC_Element')){ $dataPath=''; }
                if(file_exists($this->SimplOn_path.DIRECTORY_SEPARATOR.'Renderers'.DIRECTORY_SEPARATOR.$GLOBALS['redenderFlavor'].DIRECTORY_SEPARATOR.'htmls'.$dataPath.DIRECTORY_SEPARATOR.$object->getClass().'.html')){
                    $ret = $this->SimplOn_path.DIRECTORY_SEPARATOR.'Renderers'.DIRECTORY_SEPARATOR.$GLOBALS['redenderFlavor'].DIRECTORY_SEPARATOR.'htmls'.$dataPath.DIRECTORY_SEPARATOR.$object->getClass().'.html';
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

        function fillDomWithObject($object, $htmlDomString){

            $filledItemHtml='';
            
            $dom = HtmlDomParser::str_get_html($htmlDomString);

            if (method_exists($object, 'fillLayout')) {
                $ret = $object->fillLayout($dom);
            }else{
                if($object instanceof SI_Container){
    
                    $ICNode = $dom->findOne('.IC');
     
                    if (strpos($ICNode->class, 'items') == false) {
                         $ICNode = $dom->findOne('.IC .items');
                    }

                    $tmpID = $ICNode->id;
                    $ICNode->id='tmpID';
      
                    $IINodes = $ICNode->find('#tmpID > .II');

                    if(count($IINodes) === 0){
                        $IINodes = $ICNode->find('#tmpID > .itemWarp > .II');
                    }

                    $i=0;
                    $filledItemsHtml='';

                    foreach($object->items() as $item){
                        $item = $item["item"];
                        if($item instanceof SC_BaseObject){
                            $filledItemsHtml.=$this->fillDomWithObject($item, $IINodes[$i]->html() )."\n\n";
                        }elseif( is_string($item) OR is_numeric($item)){
                            if($item == 0){$item = (string)$item;}
                            $filledItemsHtml .=  str_replace('$val', $item, $IINodes[$i]->html());
                        }
                        $i++;
                    }        
                    $ICNode->id=$tmpID;    
                    $ICNode->innerhtml = $filledItemsHtml; 
                }elseif( !($object instanceof SI_Container) ){
                    $repeatNodes = $dom->find(".repeat");
                    foreach($repeatNodes as $repeatNode){
                        $repeatClasses = explode(' ', $repeatNode->class);
                        $items = $object->{$repeatClasses[1]}();
                        $itemHtml = $repeatNode->find('.repeat .item',0)->outerHtml;
                        $selectedItemHtml = $repeatNode->find('.repeat .selectedItem',0)->outerHtml;
        
                        $i=0;
                        foreach($items as $key => $value){
                            
                            if (is_object($value)) {
                                if( $selectedItemHtml
                                    AND (method_exists($object, 'selected') OR property_exists($object, 'selected'))
                                    AND (
                                        ($value instanceof \SD_Data AND $object->selected($value->val())) 
                                        OR 
                                        ($value instanceof \SC_Element AND $object->selected($value->id()))
                                    )
                                ){
                                    $filledItemHtml .= $this->fillDomWithObject($value, $selectedItemHtml);
                                }else{
                                    $filledItemHtml .= $this->fillDomWithObject($value, $itemHtml);
                                }
                            }else{
                                if( $key === 0 ){ $key = '0'; }else
                                if( $key === ' ' ){ $key = ''; }
            
                                if( $selectedItemHtml
                                    AND (method_exists($object, 'selected') OR property_exists($object, 'selected'))
                                    AND $object->selected($key)
                                ){
                                    $filledItemHtml .= "\n".str_replace(['$key','$val'],[$key,$value],$selectedItemHtml);
                                }else{
                                    $filledItemHtml .= "\n".str_replace(['$key','$val'],[$key,$value],$itemHtml);
                                }
        
                            }
                        }
                        $dom->find(".repeat",$i)->innerHtml = "\n".$filledItemHtml."\n";
                        $i++;
                    }
                }

                
                //TODO optimize so this does not replace nodes that will/have disaper when the parents node content has been also replaced
                //TODO modify so it detects nodes with any class that starts with 'OA_' not only the first class
                $OAs = $dom->find('[class^="OA_"]');
                $sssj=false;

                foreach($OAs as $OANode){
                    $sssj=true;
                    $class = explode(' ',$OANode->class)[0];
                    $class = explode('_',$class);
                    $oa = $object->{$class[1]}();
                    if(is_string($oa)){
                        $OANode->innerhtml = $oa;
                    }elseif( (
                            $oa instanceof SD_Data
                            OR
                            $oa instanceof SI_Item
                            OR
                            $oa instanceof SI_Container
                            OR
                            $oa instanceof SI_Page
                            OR
                            $oa instanceof SC_Element
                        ) 
                            AND empty($class[2]) 
                    ){
                        $OANode->innerhtml = $this->render($oa,'showView');
                    }
                }
                

                $ret = preg_replace_callback(
                    '/(\$)([a-z,_,0-9]+)/i',
                    function ($matches) use ($object){                  
                        $parameters = explode ('_',$matches[2]);
                        $method = array_shift($parameters);
                        $methodKey = substr($method, 0, 2);
                        $rendermethod = substr($method, 2); 

        
                        if($methodKey != 'SR' && (method_exists($object,$method) OR property_exists($object, $method)) ){ 
                            // if the method is for the data call it
                            return call_user_func_array(array($object, $method), $parameters);
                        }elseif($methodKey == 'SR' && (method_exists($this,$rendermethod) OR property_exists($this, $rendermethod)) ){
                            // if the method is for the renderer call it and use data as a parameter
                            if(method_exists($this,$rendermethod)){
                                array_unshift($parameters,$object);
                            }
                            return call_user_func_array(array($this, $rendermethod), $parameters);
                        }elseif((method_exists($object, 'parent') OR property_exists($object, 'parent') ) && $object->parent()){
                            return call_user_func_array(array($object->parent(), $method), $parameters);
                        }
                    },
                    $dom
                );
            }

            return str_replace('&#13;', '', $ret);
        }

        function getStylesAndScriptsLinks($dom){ 
            // get head the CSS Links
            foreach ($dom->find('head link[rel="stylesheet"]') as $domLink) {
                // Get the href attribute of each link
                $link = $domLink->href;
                if(substr($link, 0, 4) == 'http' && substr($link, -4) == '.css'){ self::$csslinks[$link]=$link; }
                elseif(substr($link, -4) == '.css'){self::$csslinks[basename($link)]=$link;}
            }

            // get head the JS Links
            foreach ($dom->find('head script') as $domLink) {
               $link = $domLink->src;
               if(substr($link, 0, 4) == 'http' && substr($link, -3) == '.js'){ self::$jslinks[$link]=$link; }
               elseif(substr($link, -3) == '.js'){self::$jslinks[basename($link)]=$link;}
            }
        }

        function addStylesTagsToAutoCSS(SC_BaseObject $object, $dom){ 
            global $cssTagsContent;
         
           
            // get the style tags of the method           
            $cssTagsContent[$object->getClass()] = $dom->find("style", 0)->plaintext;
              
            $minifyed = minify_css($cssTagsContent[$object->getClass()]);
            $minifyedWithMarks = "/* START_".$object->getClass()." */\n $minifyed \n/* END_".$object->getClass()." */";
            
            $file = $this->App_path.DIRECTORY_SEPARATOR.'Layouts'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'simplon-auto.css';
            $currentStylesFile = file_get_contents($file);

            $regEx = '/(\/\* START_'.$object->getClass().' \*\/)(\\n.*\\n)(\/\* END_'.$object->getClass().' \*\/)/';
            
            //Put in $currentStile[2] whats now in the simplon-auto.css file 
            preg_match($regEx, $currentStylesFile, $currentStile);
            if(
                array_key_exists(2, $currentStile) 
                && 
                !empty(trim($minifyed))
                &&
                (  trim($currentStile[2]) != trim($minifyed)  ) 
            ){
                //$StylesForFile = preg_replace($regEx, $minifyedWithMarks, $currentStylesFile);
                $StylesForFile = str_replace($currentStile[2], "\n".$minifyed."\n", $currentStylesFile); 
                if(!empty($currentStile[2]) AND $StylesForFile){
                   file_put_contents($file, trim($StylesForFile));
                }
            }elseif(!array_key_exists(2, $currentStile)  && !empty(trim($minifyed))){      
                $regEx = '/\/\* START_'.$object->getClass().'\*\/\s*(.+?)\s*\/\* END_'.$object->getClass().'\*\//s';
                preg_match($regEx, $currentStylesFile, $currentStile);
                if(array_key_exists(0, $currentStile)){
                    $StylesForFile = str_replace($currentStile[0], $minifyedWithMarks."\n", $currentStylesFile); 
                    file_put_contents($file, trim($StylesForFile));
                }else{
                    file_put_contents($file, trim($currentStylesFile)."\n".trim($minifyedWithMarks));
                }              
            }elseif(empty(trim($minifyed))){
                $StylesForFile = preg_replace($regEx, '', $currentStylesFile);
                file_put_contents($file, trim($StylesForFile));
            }
        } 

        function addCSSandJSLinksToTemplate($dom)
        {
            $head = $dom->findOne("head");
            if ($dom->findOne("head")->html() != '') {
                                                
                foreach ($dom->find('head link[rel="stylesheet"]') as $element) {
                    $element->outertext = '';
                }
                
                // Add new stylesheet links
                foreach (self::$csslinks as $csslink) {
                    if (substr($csslink, 0, 4) == 'http' && substr($csslink, -4) == '.css') {
                        $head->innerHtml .= '<link rel="stylesheet" href="' . $csslink . '" />';
                    } else {
                        $head->innerHtml .= '<link rel="stylesheet" href="' . $this->cssWebRoot . '/' . basename($csslink) . '" />';
                    }
                }


                foreach ($dom->find("head script") as $element) {
                    $element->outertext = '';
                }
                // Add new script tags
                foreach (self::$jslinks as $jslink) {
                    if (substr($jslink, 0, 4) == 'http' && substr($jslink, -3) == '.js') {
                        $head->innerHtml .= '<script type="text/javascript" src="' . $jslink . '"></script>' . "\n";
                    } else {
                        $head->innerHtml .= "\n".'<script type="text/javascript" src="' . $this->jsWebRoot . '/' . basename($jslink) . '"></script>' ;
                    }
                }
  
            }
            return $dom;
        }

        function getDataLayout(SD_Data $Data, string $method){ 

            if (method_exists($Data, 'getLayout')) {
                $methodDom = $Data->getLayout($method);
                $this->layoutsCache[$Data->getClass().'_'.$method] = $methodDom;
            }else{
                if( !isset($this->layoutsCache[$Data->getClass().'_'.$method]) ){           
                    $methodDom = $this->getDataLayoutFromFile($Data,$method);
                    $this->layoutsCache[$Data->getClass().'_'.$method] = $methodDom;
                }
            }
  
            return $this->layoutsCache[$Data->getClass().'_'.$method];
        }

        function getDataLayoutFromFile(SD_Data $Data, string $method){ 

            if( !isset($this->layoutsCache[$Data->getClass().'_'.$method]) ){
                $dom = HtmlDomParser::file_get_html($this->layoutPath($Data));
                $this->getStylesAndScriptsLinks($dom);     
                $this->addStylesTagsToAutoCSS($Data,$dom);
                $methodDom = $dom->find(".$method > *", 0);
                $methodDom->class = $methodDom->class.' '.'II II_'.$Data->name().'_'.$Data->getClass().'_'.$method;
            }
            return $methodDom;
        }

        function getItemLayout(SI_Item $Item){ 

            if( !isset($this->layoutsCache[$Item->getClass()]) ){
                
                if (method_exists($Item, 'getLayout')) {
                    $this->layoutsCache[$Item->getClass()] = $Item->getLayout();
                }else{
                    $dom = HtmlDomParser::file_get_html($this->layoutPath($Item));
                    $this->getStylesAndScriptsLinks($dom);     
                    $this->addStylesTagsToAutoCSS($Item,$dom);
                    $sectionDom = $dom->find("section > *", 0);
                    $sectionDom->class = $sectionDom->class.' '.'II II_'.$Item->name().'_'.$Item->getClass();
                    $this->layoutsCache[$Item->getClass()] = $sectionDom;
                }
            }

            return $this->layoutsCache[$Item->getClass()];
        }

        function getContainerLayout($container, $method = null, $forTemplateOf = null){

            if($forTemplateOf){$container->name($forTemplateOf->getClass().'_'.$method);}

            if($forTemplateOf && !isset($this->layoutsCache[$container->name()])){

                if(SC_Main::$Layouts_Processing == 'OnTheFly'){                   

                    $content = '<section class="'.$method.'">'.$this->makeContainerLayout($container).'</section>';
                    $dom = HtmlDomParser::str_get_html($content);
                    $containerdom = $dom->findOne("section.".$method);
                }else{
                    $path = $this->directlayoutPath($forTemplateOf);
                    if(file_exists($path)){
                        $dom = HtmlDomParser::file_get_html($path); 
                        $this->getStylesAndScriptsLinks($dom);     
                        $this->addStylesTagsToAutoCSS($container,$dom);
                    }else{
                        //CREATE FILE
                        $content = '<section class="'.$method.'">'.$this->makeContainerLayout($container).'</section>';
                        $dom = $this->render(new SI_basicPage($content));
                        file_put_contents($path, htmlCleanAndTidy($dom));
                        $dom = HtmlDomParser::str_get_html($dom);
                    }

    
                    $containerdom = $dom->findOne("section.".$method);
                    $bodyDom = $dom->findOne("body");
                }

                if(SC_Main::$Layouts_Processing == 'Preserve'){ 
                    $containerdom = $dom->findOne("section.".$method);
                    if(!$containerdom->html()){
                        throw new SC_Exception('There is no '.$method.' in the file: '.$path.' Layouts Processing: Preserve');
                    }
                }elseif(SC_Main::$Layouts_Processing == 'OverWrite'){
                    $methodDom = $dom->findOne("section.".$method);
                    if(empty($methodDom->html())){
                        $bodyDom->innerhtml = $bodyDom->innerhtml.'<section class="'.$method.'">'."\n".
                        $this->makeContainerLayout($container).
                        "\n".'</section>';
                        $containerdom = $dom->findOne("section.".$method);
                    }else{
                        $containerdom->innerhtml  = $this->makeContainerLayout($container)->html();
                    }
                    file_put_contents($path, htmlCleanAndTidy($dom->html()));
                }elseif(SC_Main::$Layouts_Processing == 'Update'){
                    //TODO make it so that it just updates the Datas that are missing or have changed
                    $methodDom = $dom->findOne("section.".$method);
                    $check = $this->checkContainerLayout($container,$methodDom);
                    if($check[0] == 'Empty'){ 
                        $bodyDom->innerhtml = $bodyDom->innerhtml.'<section class="'.$method.'">'."\n".
                        $this->makeContainerLayout($container).
                        "\n".'</section>';
                        $containerdom = $dom->findOne("section.".$method);
                    }
                }
            }elseif($forTemplateOf && !empty($this->layoutsCache[$container->name()])){
                return $this->layoutsCache[$container->name()];
            }else{
                $containerdom = $this->makeContainerLayout($container);
            }
            return $containerdom;
        }

        function checkContainerLayout($container, $dom){ 

            $containerDom = $dom->findOne("section > .II");

            foreach ($containerDom->children() as $child) {
                if(!empty($child->html())){
                    if (preg_match('/\bII_\w+/', $child->getAttribute('class'), $matches)) {
                        $iiClass = $matches[0];
                    }
                    $containerDomChilds[] = $child;
                }
            }

            if(empty($dom->html())){
                return ['Empty'];
            }else{
                foreach($container->items() as $item){
                    $method = $item['method'];
                    $item = $item['item'];
      
                        // if($item instanceof SI_Container){
                        //     //$this->checkContainerLayout($item['item'],$dom);
                        // }
        
                    //$itemDom = $dom->findOne("section.".$item->getClass());
                    //if(empty($itemDom->html())){
                    //    $itemDom = $this->getItemLayout($item);
                    //    $dom->findOne("section.".$item->getClass())->innerhtml = $itemDom->html();
                    //}
                }

                
            }
            return ['Empty'];
        }

        function getPageLayout($page){ 

            if( $page->name() && !isset($this->layoutsCache[$page->name()]) ){          
                $dom = HtmlDomParser::file_get_html($this->layoutPath($page));
                $this->getStylesAndScriptsLinks($dom);     
                $this->addStylesTagsToAutoCSS($page,$dom);
                $this->layoutsCache[$page->name()] = $dom;
            }
            
            if($page->name()){
                return $this->layoutsCache[$page->name()];
            }else{
                $dom = HtmlDomParser::file_get_html($this->layoutPath($page));
                $this->getStylesAndScriptsLinks($dom);     
                $this->addStylesTagsToAutoCSS($page,$dom);
                return $dom;
            }
        }

        function makeContainerLayout(SI_Container $container){ 

            if (method_exists($container, 'makeLayout')) {
                $containerDom = $container->makeLayout();
            }else{
                $dom = HtmlDomParser::file_get_html($this->layoutPath($container));
                $this->getStylesAndScriptsLinks($dom);
                $this->addStylesTagsToAutoCSS($container,$dom);
                $containerDom = $dom->findOne("section > *");                
                $itemsDom = $dom->find("section .items", 0);
        

                $containerDom->class = 'IC II IC_'.$container->name().'_'.$container->getClass().' '.$container->getClass().' '.$containerDom->class;
                $itemDom = $containerDom->findOne(".itemWarp"); 
                if ($itemDom) {
                    $currentClasses = $itemDom->getAttribute('class');
                    if (strpos($currentClasses, 'II') === false) {
                        $itemDom->setAttribute('class', trim($currentClasses . ' II'));
                    }
                }
                //if($itemDom){$originalItemDom = HtmlDomParser::str_get_html($itemDom->outerhtml);}


                $itemDom->innerhtml  = '';

                $innerHtml = '';
 
                foreach($container->items() as $containerItem){  

                    $method = $containerItem['method'];
                    $containerItem = $containerItem['item'];


                    if($containerItem instanceof SD_Data){            
                        $tempHtml = $this->getDataLayout($containerItem,$method)->outerhtml."\n\n"; 
                    }elseif($containerItem instanceof SI_Item){
                        $tempHtml = $this->getItemLayout($containerItem)->outerhtml."\n\n";
                    }elseif($containerItem instanceof SI_Container){
                        $tempHtml = $this->getContainerLayout($containerItem)->outerhtml."\n\n";
                    }elseif($containerItem instanceof SC_Element){
                        $tempHtml = $containerItem->{$method}(); 
                    }elseif( is_string($containerItem) OR is_numeric($containerItem)){
                        if($itemDom->html()){
                            $itemDom->setAttribute('class', trim($currentClasses . ' II'));
                            $tempHtml =  '$val' ;
                        }
                        else{$tempHtml =  '<span class="II item">$val</span>';}
                    }
                
                    if($itemDom->html()){
                        $itemDom->innerhtml = $tempHtml;
                        $innerHtml .= $itemDom->outerhtml; 
                    }else{ 
                        $innerHtml .= $tempHtml; 
                    }  
                } 
                if(empty($itemsDom->html())){
                    $containerDom->innerhtml=$innerHtml;
                }else{
                    $itemsDom->innerhtml=$innerHtml; 
                }
            }
            return $containerDom;
        }

        function requiredText(SC_BaseObject $object){
            if($object->required()){ return 'required'; }else{ return ''; }
        }
    
}