<?php
use \SimplOn\Elements\Element, \SimplOn\Datas;

class Fe extends Element
{
	public function construct($id = null, &$specialDataStorage = null) {
	    $this->id = new \SimplOn\Datas\NumericId('Id');
		$this->cabeza = new Datas\String('¿Cuando tendrás Fe?', 'S');
		$this->contenido = new Datas\String('Contenido', 'S');
		$this->home = new Datas\ElementContainer(new Home(), 'Home');
        //$this->home2 = new Datas\ElementContainer(new Home(), 'Home2');
		$this->things = new Datas\ElementsContainer(array(new Home(), new Person()), 'Things');
		
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