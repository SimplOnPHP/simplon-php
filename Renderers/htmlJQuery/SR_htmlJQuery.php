<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/
//use voku\helper\HtmlDomParser;

class SR_htmlJQuery extends SC_BaseObject {

    protected
        $SimplOn_path,
        $App_path,
        $App_web_root,
        $Renderer_path,

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
        $InputBox_type = 'SI_HInputBox',
        $jslinks = array();

        function render(SI_Item $item) {
            // $config = array(
            //     'indent' => true,
            //     'output-xhtml' => true,
            //     'wrap' => 200
            // );
            
            // $tidy = new tidy();
            // $tidy->parseString($item->html(), $config, 'utf8');
            // $tidy->cleanRepair();
            
            echo $item->html();
        }

        //--------------------------------
        function action($object, string $method, $clean = null, $message = null){

            // if ($object instanceof SD_ElementContainer or $object instanceof SD_ElementsContainerMM) {
            //     $object = $object->element();
            // }

            if ($object instanceof SC_BaseObject) {
                $class = $object->getClass();
            }elseif(is_string($object)){
                $class = $object;
                $clean = 'id';
            }else{
 
            }

            if(!empty($message)){ $message = SC_Main::$URL_METHOD_SEPARATOR.SC_Main::$URL_METHOD_SEPARATOR.urlencode($message); }

            if ($clean == 'id') {
                return $this->encodeURL($class, array(), $method).$message;
                //return $this->encodeURL($object->getClass(),array(),$method,array($nextStep));    
            } else {
                return $this->encodeURL($class, array($object->getId()), $method).$message;
                //return $this->encodeURL($object->getClass(),array($object->id()),$method,array($nextStep));
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
                    //     'SC_Main::$RENDERER::parameterEncoder()',
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
            
            return $url;
        }


        static function parameterEncoder($p) {
            if(is_string($p)) {
                $string_delimiter = '"';
                $p = self::fixCode(urlencode($p));
                //return $string_delimiter. $p .$string_delimiter;
                return  $p ;
            } else {
                return urlencode($p);
            }
        }
        
        function setMessage($message='') {
            SC_Main::$SystemMessage = $message;
        }
        //--------------------------------

         
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
}