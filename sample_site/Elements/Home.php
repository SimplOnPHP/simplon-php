<?php
class Home extends DOF\Elements\Element
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
	
	public static function index() {
		return "Hello world!";
	}
	
	public static function inox() {
		return "Inox world!";
	}
	
	
}