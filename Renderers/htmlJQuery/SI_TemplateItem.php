<?php

/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

use \SC_Main as SC_Main;

use voku\helper\HtmlDomParser;

/**
 * Los Items de interfaz SI_Item (Simplon Interface Item) son objetos que representan elementos de la interfaz de usuario.
 * Siempre deben recibir el Dato o elemento ($doe) del queu forman parte
 * Estos elementos deben ser redefinidos para cada Renderer ya que dependen de este.
 * 
 * En el Renderer htmlJQuery deben tener solo el atributo $doe que guarda a el Dato o Elemento del que forma parte el item de interfaz y atributos sencillos de valor de string para guardar los valores de lo que debe sustiturse en pantalla
 * 
 */
abstract class SI_TemplateItem extends SI_Item {

    protected
        $template = null;

    function __construct($content)
    { 
        $this->template = HtmlDomParser::file_get_html($this->getTemplatePath()); 
        $this->content = $content;
        $this->getStylesAndScriptsLinks();
        $this->addCSSandJSLinksToTemplate();
    }

    function html() {
        $this->setRenderVals();
        $html = preg_replace_callback(
            '/(\$)([a-z,_,0-9]+)/i',
            function ($matches) {                  
                $parameters = explode ('_',$matches[2]);
                $method = array_shift($parameters);
                $ret = call_user_func_array(array($this, $method), $parameters);
                if($ret instanceof SI_Item ){ $ret = $ret->html(); }
                if(is_array($ret) ){ 
                    $temp = '';
                    foreach ($ret as $arrayItem) {
                        if( $arrayItem instanceof SI_Item ){$temp .= $arrayItem->html();}
                        elseif(is_string($arrayItem)){$temp .= $arrayItem;}
                    }
                    $ret = $temp; 
                }
                return $ret;
            },
            str_replace('&#13;', '', $this->template->html())
            
        );

        $this->addStylesTagsToAutoCSS();
        $this->getStylesAndScriptsLinks();

        
        return $html;
    }

    function setRenderVals(){
        foreach($this as $atribute => $value){
            if(is_array($value) AND sizeof($value) == 2 AND  $value[0] instanceof SC_BaseObject AND is_string( $value[1] )){
                if($this->object instanceof SC_BaseObject){ $value[0] = $this->object; }
                $this->$atribute = $value();
            }
        }
    }
    
    function addCSSandJSLinksToTemplate($dom = null){
        if(!$dom){$dom = $this->template;}
        $head = $dom->findOne("head");
        if ($dom->findOne("head")->html() != '') {
                                            
            foreach ($dom->find('head link[rel="stylesheet"]') as $element) {
                $element->outertext = '';
            }

            $headInnerHtml='';
            // Add new stylesheet links
             $headInnerHtml .= "\n";
            foreach (static::$cssfiles as $cssfile) {
                
                if (substr($cssfile, 0, 4) == 'http' && substr($cssfile, -4) == '.css') {
                    $headInnerHtml .= '<link rel="stylesheet" href="' . $cssfile . '" />'. "\n";
                } else {
                    $headInnerHtml .= '<link rel="stylesheet" href="' . SC_Main::$App_web_root.DIRECTORY_SEPARATOR.SC_Main::$RENDERER_FLAVOR.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR. basename($cssfile) . '" />'. "\n";
                }
            }

            foreach ($dom->find("head script") as $element) {
                $element->outertext = '';
            }
            // Add new script tags
            
            ksort(static::$jsfiles);
            static::$jsfiles = array_unique(static::$jsfiles);
            foreach (static::$jsfiles as $jsfile) {
                if (substr($jsfile, 0, 4) == 'http' && substr($jsfile, -3) == '.js') {
                    $headInnerHtml .= '<script type="text/javascript" src="' . $jsfile . '"></script>' . "\n";
                } else {
                    $headInnerHtml .= '<script type="text/javascript" src="' . SC_Main::$App_web_root.DIRECTORY_SEPARATOR.SC_Main::$RENDERER_FLAVOR.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR. basename($jsfile) . '"></script>'. "\n" ;
                }
            }

        }
        $head->innerHtml .= $headInnerHtml;
        $this->template = $dom;
    }

    function getStylesAndScriptsLinks($dom = null){

        if(!$dom){$dom = $this->template;}

        // get head the CSS Links
        foreach ($dom->find('head link[rel="stylesheet"]') as $domLink) {    
            // Get the href attribute of each link
            $link = $domLink->href;
            if(substr($link, 0, 4) == 'http' && substr($link, -4) == '.css'){ SI_Item::$cssfiles[$link]=$link; }
            elseif(substr($link, -4) == '.css'){SI_Item::$cssfiles[basename($link)]=$link;}
        }
        // get head the JS Links
        foreach ($dom->find('head script') as $domLink) {
           $link = $domLink->src;
           if(substr($link, 0, 4) == 'http' && substr($link, -3) == '.js'){ SI_Item::$jsfiles[$link]=$link; }
           elseif(substr($link, -3) == '.js'){SI_Item::$jsfiles[basename($link)]=$link;}
        }        
    }

    function addStylesTagsToAutoCSS($dom = null){
        if(!$dom){$dom = $this->template;}
        global $cssTagsContent;
     
       
        // get the style tags of the method           
        $cssTagsContent[$this->getClass()] = $dom->find("style", 0)->plaintext;
          
        $minifyed = minify_css($cssTagsContent[$this->getClass()]);
        $minifyedWithMarks = "/* START_".$this->getClass()." */\n $minifyed \n/* END_".$this->getClass()." */";
        
        $file = SC_Main::$App_PATH.DIRECTORY_SEPARATOR.SC_Main::$RENDERER_FLAVOR.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'simplon-auto.css';
        $currentStylesFile = file_get_contents($file);

        $regEx = '/(\/\* START_'.$this->getClass().' \*\/)(\\n.*\\n)(\/\* END_'.$this->getClass().' \*\/)/';
        
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
            $regEx = '/\/\* START_'.$this->getClass().'\*\/\s*(.+?)\s*\/\* END_'.$this->getClass().'\*\//s';
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

    function getTemplatePath(){
        if(file_exists(SC_Main::$App_PATH.DIRECTORY_SEPARATOR.SC_Main::$RENDERER_FLAVOR.DIRECTORY_SEPARATOR.$this->getClass().'.html')){
            return SC_Main::$App_PATH.DIRECTORY_SEPARATOR.SC_Main::$RENDERER_FLAVOR.DIRECTORY_SEPARATOR.$this->getClass().'.html';
        }else{
            return SC_Main::$SimplOn_PATH.DIRECTORY_SEPARATOR.'Renderers'.DIRECTORY_SEPARATOR.$GLOBALS['redenderFlavor'].DIRECTORY_SEPARATOR.$this->getClass().'.html';
        }
    }

}

