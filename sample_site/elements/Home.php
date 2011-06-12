<?php
class Home extends DOF\Elements\Abstract_
{
	protected $id;
	protected $cabeza;
	protected $contenido;
	
	public function __construct($id = null, &$specialDataStorage = null)
	{
	    $this->id = new DOF\Datas\Id('Id','id');
		$this->cabeza = new DOF\Datas\String('Cabeza','cabeza','vcusl');
		
		$this->contenido = new DOF\Datas\HTMLText('Contenido: (Para copiar texto de Word utilizar la herramienta "Paste from Word")','contenido');
		
		
		
		$this->repository('nota');
		
		parent::__construct($id, $specialDataStorage);
	}
	
	public function index() {
		return "Hello world!";
	}
	
	public function inox() {
		return "Inox world!";
	}
	
	
}