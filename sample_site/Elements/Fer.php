<?php
use \DOF\Elements\Element, \DOF\Datas;

class Fer extends Element
{
	public function construct($id = null, $storage=null, &$specialDataStorage = null) {
	    $this->id = new \DOF\Datas\NumericId('Id');
		$this->cabeza = new Datas\String('¿Cuando tendrás Fer?', 'S');
		$this->contenido = new Datas\String('Contenido', 'S');
		$this->home = new Datas\ElementContainer(new Home(), 'Home');
		//$this->homes = new Datas\ElementsContainer(array(new Home()), 'Homes');
		
		$this->concat = new Datas\Concat("Concat test", array(' ','Cabeza:','cabeza',' contenido','contenido'),"L");
        $this->compose = new Datas\Compose("Compose test", array('%s concat %s','cabeza','concat'),"L");
	}
	
	public function test(){

		$this->fillFromDSById();
		
		//echo $this->cabeza();
		
		//echo $this->concat->showView().'<br>';
		
		//$this->cabeza('cabezon');
		
		echo $this->updateAction->showView();
		
	}
}