<?php



use voku\helper\HtmlDomParser;

/**
 * Los Items de interfaz SI_Item (Simplon Interface Item) son objetos que representan elementos de la interfaz de usuario.
 * Siempre deben recibir el Dato o elemento ($doe) del queu forman parte
 * Estos elementos deben ser redefinidos para cada Renderer ya que dependen de este.
 * 
 * En el Renderer htmlJQuery deben tener solo el atributo $doe que guarda a el Dato o Elemento del que forma parte el item de interfaz y atributos sencillos de valor de string para guardar los valores de lo que debe sustiturse en pantalla
 * 
 */
class SI_Item extends SC_BaseObject {

    protected
        // $doe, //Datta or Element
        $start = null,
        $end = null,
        $itemStart = null,
        $itemEnd = null,
        $styles = null,
        $object = null,
        $class = null,
        $content = null; 
        
    static
        $cssfiles = [],
        $jsfiles = [];

    function setTagsVals($renderVals = null){
        if($renderVals['class']){ $class = 'class="'.$renderVals['class'].'"'; }else{ $class=''; }  
        $this->start = "<div $class >";
        $this->end = "</div>\n";
        // $this->itemStart = ;
        // $this->itemEnd = ;
        // $this->styles = ;
        // $this->object = ;
    }

    function __construct($content, $class = null){
        $this->content = $content; 
        $this->class = $class;
    }


    function __toString()
    {
        return $this->html();
    }

    function object($object = null, bool $pasToChilds = false){
        if($object){
            $this->object = $object;
            if($pasToChilds AND is_array($this->content)){
                foreach($this->content as $item){
                    if($item instanceof SI_Item){
                        $item->object($object, true);
                    }
                }
            }elseif($this->content instanceof SI_Item){
                $this->content->object($object, $pasToChilds);
            }
        }else{
            return $this->object;
        }
    }

    function html() {        
        $vals = $this->getRenderVals();
        $innerHTML = '';
        if(is_array($vals['content'])){
            foreach($vals['content'] as $item){
                if(is_string($item)){
                    $innerHTML .= $this->itemStart.$item.$this->itemEnd;
                }elseif($item instanceof SI_Item){
                    $innerHTML .= $this->itemStart.$item->html().$this->itemEnd;
                }
            }
        }else{ $innerHTML = $vals['content']; }
        return $this->start.$innerHTML.$this->end;
    }

    function addItem($item){
        if(is_array($this->content)){
            $this->content[] = $item;
        }else{
            $this->content = [$this->content, $item];
        }
    }

    function getRenderVals(){
        $ret = [];
        foreach($this as $atribute => $value){
            if(is_array($value) AND sizeof($value) == 2 AND  $value[0] instanceof SC_BaseObject AND is_string( $value[1] )){
                if($this->object instanceof SC_BaseObject){ $value[0] = $this->object; }
                $ret[$atribute] = $value();
            }else{
                $ret[$atribute] = &$this->$atribute;
            }
        }
        $this->setTagsVals($ret);
        return $ret;
    }


    function addStylesToAutoCSS(){ 
        global $cssTagsContent;
     
       if($this->styles AND SC_Main::$debug_mode){
                     
            $cssTagsContent[$this->getClass()] = $this->styles;
            
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
    } 



}

