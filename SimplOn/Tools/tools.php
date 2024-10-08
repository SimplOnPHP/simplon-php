<?php

// $whoops = new \Whoops\Run;
// $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
// $whoops->register();


if (version_compare(PHP_VERSION, '8.0.0', '<')) {
   function str_starts_with($haystack, $needle) {
       return substr($haystack, 0, strlen($needle)) === $needle;
   }
}



function htmlCleanAndTidy($html){
   $html = str_replace('&#13;', '', $html);
    
   $tidy = new tidy;
   $config = array('indent'=> true,'output-xhtml' => false, 'output-html' => true,'wrap'=> 600);
   $tidy->parseString($html, $config, 'utf8');
   $tidy->cleanRepair();
   return $tidy.'';
}


function sd($v, $name = '' ){
   
   if(is_array($v) AND 
         isset($v[0]) AND
         (
         $v[0] instanceof SC_BaseObject
         OR ($v[0] instanceof \voku\helper\SimpleHtmlDomBlank)
         OR ($v[0] instanceof \voku\helper\HtmlDomParser)
         OR ($v[0] instanceof \voku\helper\SimpleHtmlDom)
         OR ($v[0] instanceof \voku\helper\SimpleHtmlDomNode)
         ) 
      ){
         sd('- - * - - ');
         foreach($v as $i){
            sd($i);
         }
         sd('- - * - - ');
   }elseif($v instanceof SC_BaseObject){
      if(!$name){$name = $v->getClass ();}
	   echo " \r\n<pre>".' --SC_BaseObject-- '.$name.' - '.htmlentities($v->getClass()).'</pre><br />';
   }else if($v instanceof voku\helper\SimpleHtmlDomBlank){
	   echo " \r\n<pre>".' --HtmlDomParser-- '.$name.' - '.htmlentities($v->outerHtml).'</pre><br />';
   }else if($v instanceof voku\helper\HtmlDomParser){
	   echo " \r\n<pre>".' --HtmlDomParser-- '.$name.' - '.htmlentities($v->outerHtml).'</pre><br />';
   }else if($v instanceof voku\helper\SimpleHtmlDom){
	   echo " \r\n<pre>".' --SimpleHtmlDom-- '.$name.' - '.htmlentities($v->outerHtml).'</pre><br />';
   }else if(is_string($v) AND isHTML($v)){
	   echo " \r\n<pre>".$name.' ---- '.htmlentities($v).'</pre><br />';
   }else if(is_array($v)){
      
	   $echo =  " \r\n<pre>".$name;
      
	   foreach($v as $i){ 
	      $echo .=  "\r\n".var_export($i, true);
      }
      
      $echo .= '</pre><br />';
      echo $echo;
   }else{ 
	   echo " \r\n<pre>".$name.' ---- '.var_export($v, true).'</pre><br />';
   }
}

function isHTML($string){
   return $string != strip_tags($string) ? true:false;
}


function wrap_implode( $array, $before = '', $after = '', $separator = '' ){
   if( ! $array )  return '';
   return $before . implode("{$after}{$separator}{$before}", $array ) . $after;
 }
 
 /**
  * from https://www.geekality.net/2017/02/08/php-preg_match_all_callback/
  */
  function preg_match_all_callback(
   string $pattern,
   string $subject,
   callable $callback) {
      preg_match_all($pattern, $subject, $matches, PREG_SET_ORDER);
      $r = array();
      foreach($matches ?? [] as $match)
         $r[] = $callback($match);
      return $r;
   }

/**
 * Process the script tags in a QueryPath object to prevent encoding of their content.
 *
 * @param DOMQuery $qp The QueryPath object to process.
 * @return DOMQuery The modified QueryPath object with unencoded script tags.
 */
function preserveScriptContent(QueryPath\DOMQuery $qp): QueryPath\DOMQuery {
   // Find all <script> elements and handle them
   $qp->find('script')->each(function (QueryPath\DOMQuery $script) {
       // Get the raw script content
       $scriptContent = $script->text();

       // Clear the script's existing content
       $script->empty();

       // Append raw content back without encoding
       $script->append($scriptContent);
   });

   // Return the modified QueryPath object
   return $qp;
}




require_once( $simplon_root.DIRECTORY_SEPARATOR.'Tools'.DIRECTORY_SEPARATOR.'minify.php');

?>