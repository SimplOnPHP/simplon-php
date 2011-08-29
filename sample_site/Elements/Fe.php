<?php
use \DOF\Elements\Element, \DOF\Datas;

class Fe extends Element
{
	public function construct($id = null, &$specialDataStorage = null) {
	    $this->id = new Datas\Id('Id');
		$this->cabeza = new Datas\String('¿Cuando tendrás Fe?', 'S');
		$this->contenido = new Datas\String('Contenido', 'S');
		$this->home = new Datas\ElementContainer(new Home(), 'Home');
		
		$this->concat = new Datas\Concat("concat---", array('cabeza','contenido'));
		$this->updateAction = new Datas\UpdateAction("E-Link", array('Jaja %s jojo %s','cabeza','contenido'));
	}
	
	public function test(){

		$this->fillFromDSById();
		
		//echo $this->cabeza();
		
		//echo $this->concat->showView().'<br>';
		
		//$this->cabeza('cabezon');
		
		echo $this->updateAction->showView();
		
	}
}