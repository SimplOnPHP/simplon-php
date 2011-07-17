<?php
namespace DOF\Datas;
/**
 * @todo Agregar posibilidad de especificar indices de b�squeda:
 * una posible soluci�n es la de usar el parametro $vcuslr usando el metodo search()
 * de ahi entender si un dato se va a usar para la b�squeda
 * y tambi�n dar la posibilidad de un "override" para especificar
 * de modo explicito, si se quiere o no un indice en dicho dato.
 * Basandose sobre el tipo de dato, elegir el tipo de indice
 * o basarse sobr un atributo del dato.
 */


/**
* __construct($label=null,$campo=null,$inputName=null,$val=null)	Si los parametros estan vacios causará que se use la clase
* __toString()										Regresa el campo que se debe usar para generar consultas a la Fuente de datos - En SQL puede permite (SELECTS) complejos usando la asigancion " AS "
* inputName($prefijo=null)											Regresa la str del name del input para los formularios
* fillFromArray($arreglo)											Asigna el valor del dato a partir de un arreglo con todos los datos de un mismo elemento o consulta (esto es porque algunos datos puedne depender de otros)
* fillFromRequest($prefijo=null, &$arreglo=null)					Asigna el valor del dato a partir de un arreglo con llaves como los nombres de los inputs generalmente REQUEST
* strValorQuery()													Valor como se debe usar en los Querys SQL  (es decir con comillas en el caso de los strings)
* strQueryValueColum()												Da la asiganacion de valor para los Updates de SQL
* HTML()															Regresa el valor como debe imprimirse en HTML
* createInput($prefijo=null,$printval=false)						Input a desplegarse cuando se desea moar un formaulario de captura del dato
* updateInput($prefijo=null,$printval=true)							Input a desplegarse cuando se desea moar un formaulario de edicion del dato
* InputBuscador($prefijo)											 para de los campos de busqueda del buscador
* Where()															 para armar las consultas la seccion Where de las consultas SQL
* Label()															Regresa la  para poner como etiqueta antes de los inputs
* Header()															Regresa los necesariso para inlcuir librerias JA
**/



/**
* Componente para basico definir campos de los elementos
*
* @version	1.0
* @author	Ruben Schaffer
* @todo		-Poner funciones UTF8 en setval
* 			-Agregar excepciones: al asignar valor(si el valor es viable), al llenar desde fuente de datoso y/o REQUEST (validad si el dato esta en el arreglo)
* 			-Funciones set de -labael campo campoAS e imputname- que verifique que tiene estan en l formato correcto
*/



/**
 *
 *$campo field
 *$asField asField
 */



abstract class Data extends \DOF\BaseObject {
	
	/**
	 * Data value
	 *
	 * @access protected
	 * @var mixed
	 */
	protected $val;
	
	
	/**
	 * Default value
	 *
	 * @access protected
	 * @var mixed
	 */
	protected $default = NULL;

	/**
	 * DOF type, will be translated to the data type of the used db.
	 *
	 * @access protected
	 * @var string
	 */
	protected $type;
	
	
	/**
	 * Data name or label as it must be presented to human readers (in forms, html tables, etc)
	 *
	 * @access protected
	 * @var ing
	 */
	protected $label;
	
	
	/**
	 * indicates if the DOFdata must be used when generating the default HTML template
	 * @var boolean
	 */
	protected $view = true;
	
	/**
	 * indicates if the DOFdata must be used in the add(capture) from
	 * @var boolean
	 */
	protected $create = true;
	
	/**
	 * indicates if the DOFdata must be used in the update from
	 * @var boolean
	 */
	protected $update = true;
	
	/**
	 * indicates if the DOFdata must be used in the search from
	 * @var boolean
	 */
	protected $search = false;
	
	/**
	 * indicates if the DOFdata must be used when several items are listed on a html table or list
	 * @var boolean
	 */
	protected $list = false;
	
	/**
	 * indicates if the DOFdata must have a value in order to allow the storage of it's dataParent DOFelement
	 * @var boolean
	 */
	protected $required = false;
	
	protected $name;

	
	/**
	 *
	 *
	 *
	 * @param ing $label
	 * @param ing $field
	 * @param unknown_type $val
	 * @param ing $acslr This ing indicates the DOFdata where it must be used by the DOFelement's to do so if sended the
	 * sting must contain the first letter of any the following "uses": create, update, search, list, required.
	 * if the letter is included (the order desn't matter) that use will be set to true if not to false.
	 * see the help avobe to see what each of this does.
	 */
	public function __construct($label=null,$vcuslr=null,$val=null)
	{
		//check($label);
		
		$this->val = $val;
		$this->label=$label;
		
		if($vcuslr)
		{
			$this->setVCUSLR($vcuslr);
		}
		
		
		/*@todo verificar que esto verdadaramente no se requeire*/
		//if($inputName){$this->inputName($inputName);}
	}
	
	/**
	 * @todo handle UPPER or lower letter to ADD or remove attributes
	 *
	 * Sets create, update, search, list, required acording to the letters in $acslr
	 *
	 *  @param ing $acslr This ing indicates the DOFdata where it must be used by the DOFelement's to do so if sended the
	 * sting must contain the first letter of any the following "uses": view, create, update, search, list, required.
	 * if the letter is included (the order desn't matter) that use will be set to true if not to false.
	 * see the help avobe to see what each of this does.
	 */
	function setVCUSLR($vcuslr)
	{
		// @todo: Optimizar esta parte
		// Ej: $this->view( strpos($vcuslr,'v')!==false );
		
		if(strpos($vcuslr,'v')!==false){ $this->view=true; }else{ $this->view=false; }
		if(strpos($vcuslr,'c')!==false){ $this->create=true; }else{ $this->create=false; }
		if(strpos($vcuslr,'u')!==false){ $this->update=true; }else{ $this->update=false; }
		if(strpos($vcuslr,'s')!==false){ $this->search=true; }else{ $this->search=false; }
		if(strpos($vcuslr,'l')!==false){ $this->list=true; }else{ $this->list=false; }
		if(strpos($vcuslr,'r')!==false){ $this->required=true; }else{ $this->required=false; }
	}
	
	/**
	 * Tells PHP how to render this object as a string
	 *
	 * @return ing
	 */
	public function __toString()
	{
		return $this->showView();
	}

	/**
	 * Gives the name of the field for the create or update forms (usualy an HTML form)
	 *
	 * @param $prefijoNombre =null valor que se puede usar para distinguir los diversos de dos elementos
	 * @return ing
	 */
	public function inputName()
	{
		return @$this->inputName ?: $this->name();
	}
	
	/**
	 * Gets the DOFdata value from the REQUEST global array
	 *
	 * @param $arreglo =$_REQUEST	Arreglo desde el qeu se obtendrá el dato
	 * @param $prefijo =$null		Prefijo con el qeu se diferecio el input
	 */
	public function fillFromRequest()
	{
		$this->fillFromArray($_REQUEST);
	}
	
	
	function showView($template = null)
	{
		return $this->val();
	}
	
	abstract function showInput($fill);
	
	function showCreate()
	{
		return $this->showInput(false);
	}
	
	function showUpdate()
	{
		return $this->showInput(true);
	}
	
	function showSearch()
	{
		return $this->showInput(true);
	}
	
	/**
	 * Returns the label for the input
	 *
	 * @return ing
	 */
	public function label()
	{
		return $this->label ?: $this->getClass();
	}

	
	/** @todo Implementar.
	 * @return unknown_type
	 */
	public function afterUpdateQuery()
	{
	}

	
	/**
	 * @todo Implementar.
	 * @return unknown_type
	 */
	public function afterInsertQuery()
	{
	}
	
	/**
	 * @todo Implementar.
	 * @return unknown_type
	 */
	public function afterSelectQuery()
	{
	}
	
	
	/**
	 * @todo Implemetar.
	 * @return unknown_type
	 */
	public function afterDeleteQuery()
	{
	}	
}