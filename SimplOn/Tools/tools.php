<?php

if (version_compare(PHP_VERSION, '8.0.0', '<')) {
   function str_starts_with($haystack, $needle) {
       return substr($haystack, 0, strlen($needle)) === $needle;
   }
}

function sd($v, $name = '' ){
   if($v instanceof SC_BaseObject){
      if(!$name){$name = $v->getClass ();}
	   echo " \r\n<pre>".' --SC_BaseObject-- '.$name.' - '.htmlentities($v->getClass()).'</pre><br />';
   }else if($v instanceof phpQueryObject){
	   echo " \r\n<pre>".' --phpQueryObject-- '.$name.' - '.htmlentities($v->htmlOuter()).'</pre><br />';
   }else if(is_string($v) AND isHTML($v)){
	   echo " \r\n<pre>".$name.' ---- '.htmlentities($v).'</pre><br />';
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

require_once( $simplon_root.DIRECTORY_SEPARATOR.'Tools'.DIRECTORY_SEPARATOR.'minify.php');


?>