<?php

use \SimplOn\Elements\Element, \SimplOn\Datas;

/**
 * Tutorial ejercicio 1.1 HolaMundo
 * 
 * echo "Abriendo Hola Mundo";*/               

class HolaMundo extends Element {

    public $opciones=array(
        'uno'=>'one',
        'dos'=> 'two',
        'tres'=> 'three');
        
    public function construct($id = null, &$specialDataStorage = null) {
        $this->id = new Datas\NumericId('Id');
        $this->nombre = new Datas\String('Nombre','vUR','Mario Saenz');
        $this->frase = new Datas\String('Frase','U','estoy usando Simplon');
        $this->radio = new Datas\RadioButtonText('Number',$this->opciones,'sU');
        $this->correo = new Datas\Email('Correo','sUR');
        $this->fecha = new Datas\Date('Fecha','SUR');
        $this->editor1 = new Datas\HTMLText('Mensaje','sU');
        $this->pegar = new Datas\Concat('',array('nombre','frase','correo'));
        $this->enlace = new Datas\ElementLink('Crear',array('showCreate'),'showCreate');
        //$this->editor2 = new Datas\HTMLText('Mensaje2','sU');
        //$this->editor3 = new Datas\Text('Mensaje3','sU');
    }   
    
    function saluda(){
       echo "Hola Mundo, ".$this->nombre().' '.$this->frase()." este es mi correo: ".$this->pegar().' enlace: '.$this->enlace();
    }

    function otra(){
	echo "Esto es otra funcion";
    }

}

