<?php
class Home extends \DOF\Elements\Element
{
	protected $id;
	protected $cabeza;
	protected $contenido;
	
	public function __construct($id = null, &$specialDataStorage = null)
	{
	    $this->id = new \DOF\Datas\Id('Id');
		$this->cabeza = new \DOF\Datas\String('Cabeza: ','vcusl');
		
		$this->contenido = new \DOF\Datas\HTMLText('Contenido: ');
				
		$this->storage('home');
		
		parent::__construct($id, $specialDataStorage);
	}
}