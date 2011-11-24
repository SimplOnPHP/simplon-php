<?php
use \DOF\Elements\Element, \DOF\Datas;

class Fer2 extends Element
{
	public function construct($id = null, &$specialDataStorage = null) {
	    $this->id = new Datas\Id('Id');
		$this->cabeza = new Datas\String('¿Cuando tendrás Fer?', 'S', 'aaaa');
		$this->contenido = new Datas\String('Contenido', 'S');
		$this->home = new Datas\ElementContainer(new Home(), 'Home');
		//$this->homes = new Datas\ElementsContainer(array(new Home()), 'Homes');
		
		$this->concat = new Datas\Concat("Concat test", array(' ','Cabeza:','cabeza',' contenido','contenido'),"L");
        $this->compose = new Datas\Compose("Compose test", array('%s concat %s','cabeza','concat'),"L");
	}
    
	/*
	public function index(){

		
		echo 'weee';
		var_dump($this->filterCriteria()) ;
		
	}
    */
}