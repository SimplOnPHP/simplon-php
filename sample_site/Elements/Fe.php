<?php
use \DOF\Elements\Element, \DOF\Datas;

class Fe extends Element
{
	protected 
		$id,
		$cabeza,
		$condenado;
		//$ap_pater;
	
	// @todo: use an alias instead of __construct;
	public function __construct($id = null, &$specialDataStorage = null)
	{
	    $this->id = new Datas\Id('Id');
		$this->cabeza = new Datas\String('¿Cuando tendrás Fe?', 'S');
		$this->contenido = new Datas\String('Contenido', 'S');
		//$this->home = new Datas\ElementContainer(new Home(), 'Home');
		//$this->ap_pater = new Datas\Date('Mi nombre en formato fecha', 'SR');
		
		
		parent::__construct($id, $specialDataStorage);
	}
}