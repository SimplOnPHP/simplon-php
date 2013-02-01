<?php
use \SimplOn\Elements\Element, \SimplOn\Datas;

class HolaMundo extends Element {
   
    public function construct($id = null, &$specialDataStorage = null) {
           $this->id = new Datas\NumericId('Id');                   
           $this->nombre = new Datas\String('Nombre:',null,'yo');
           $this->frase = new Datas\String('Frase:');
           }   
   
    function saluda(){
      echo "Hola Mundo, ".$this->nombre().' '.$this->frase();
    }
}
?>
