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
* __toString() 														Define como se parsea el objeto como string
* strQueryField()													Regresa el campo que se debe usar para Querys a la Fuente de datos
* strQueryFieldSelect()												Regresa el campo que se debe usar para generar consultas a la Fuente de datos - En SQL puede permite (SELECTS) complejos usando la asigancion " AS "
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



abstract class Abstract_ extends \DOF\BaseObject {
	/**
	 * Data value
	 *
	 * @access protected
	 * @var unknown_type
	 */
	protected $val;

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
	 * Data name or field as it must be presented to DOFstorage objetcs and/or storage systems (like Data Dases)
	 *
	 * @access protected
	 * @var ing
	 */
	protected $field;

	/**
	 * @todo remove this from here and integrate it some how to the DB storage Item, or at least stablisha a naming convention for this "misplaced" functionalities
	 * Special to allow data field aliasing in SQL
	 *
	 * @access protected
	 * @var ing
	 */
	protected $asField;
	
	
	/**
	 * indicates if the DOFdata must be used when generating the default HTML template
	 * @var boolean
	 */
	protected $view;
	
	/**
	 * indicates if the DOFdata must be used in the add(capture) from
	 * @var boolean
	 */
	protected $create;
	
	/**
	 * indicates if the DOFdata must be used in the update from
	 * @var boolean
	 */
	protected $update;
	
	/**
	 * indicates if the DOFdata must be used in the search from
	 * @var boolean
	 */
	protected $search;
	
	/**
	 * indicates if the DOFdata must be used when several items are listed on a html table or list
	 * @var boolean
	 */
	protected $list;
	
	/**
	 * indicates if the DOFdata must have a value in order to allow the storage of it's dataParent DOFelement
	 * @var boolean
	 */
	protected $required;
	
	protected $externalJS;
	protected $externalCSS;

	protected $internalJS;
	protected $internalCSS;
	

	
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
	public function __construct($label=null,$field=null,$vcuslr=null,$val=null)
	{
		//check($label);
		
		if( $val ){$this->val=$val;}
		if($label){$this->label=$label;}
		if($field){$this->field=$field;}
		
		if($vcuslr)
		{
			$this->setVCUSLR($vcuslr);
		}else{
			$this->setDefaultsetVCUSLR();
		}
		
		
		/*@todo verificar que esto verdadaramente no se requeire*/
		//if($inputName){$this->inputName($inputName);}
	}
	
	/**
	 * @todo modify this to allow to have + and or - operatos to modify a specif set without having to altern-nor check the others
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
		if(strpos($vcuslr,'v')!==false){ $this->view=true; }else{ $this->view=false; }
		if(strpos($vcuslr,'c')!==false){ $this->create=true; }else{ $this->create=false; }
		if(strpos($vcuslr,'u')!==false){ $this->update=true; }else{ $this->update=false; }
		if(strpos($vcuslr,'s')!==false){ $this->search=true; }else{ $this->search=false; }
		if(strpos($vcuslr,'l')!==false){ $this->list=true; }else{ $this->list=false; }
		if(strpos($vcuslr,'r')!==false){ $this->required=true; }else{ $this->required=false; }
	}

	function setDefaultsetVCUSLR()
	{
		$this->view(true);
		$this->create(true);
		$this->update(true);
		$this->search(false);
		$this->list(false);
		$this->required(false);
	}
	
	/**
	 * Tells PHP how to render this object as a string
	 *
	 * @return ing
	 */
	public function __toString()
	{
		return $this->HTML();
	}
	
	/**
	 * provides the tha name of the field that must be used to create, update or retrive this data from the data source.
	 *
	 * @return ing
	 */
	public function QueryField()
	{
		if( $this->field ){  return $this->field; }
		else { return $this->Class(); }
	}

	/**
	 * Gives the name of the field for the create or update forms (usualy an HTML form)
	 *
	 * @param $prefijoNombre =null valor que se puede usar para distinguir los diversos de dos elementos
	 * @return ing
	 */
	public function inputName()
	{
		if($this->inputName ){ return $this->inputName; }
		else{ return $this->QueryField(); }
	}

	/**
	 * Gets the DOFdata value from an asociative array that may have many other values
	 *
	 * @param $arreglo
	 * @return ing
	 */
	public function fillFromArray(&$array)
	{
		//chek($this->QueryField().'---');
		//chek($arreglo[$this->QueryField()]);
		$this->val($array[$this->QueryField()]);
		//chek($this->val());
	}
	
	/**
	 * Gets the DOFdata value from the REQUEST global array
	 *
	 * @param $arreglo =$_REQUEST	Arreglo desde el qeu se obtendrá el dato
	 * @param $prefijo =$null		Prefijo con el qeu se diferecio el input
	 */
	public function fillFromRequest()
	{
		global $_REQUEST;
		$this->fillFromArray($_REQUEST);
	}
	
	/**
	 * Returns the DOFdata value as it must be rendered in HTML
	 *
	 * @return ing
	 */
	public function HTML()
	{
		if($this->val()){ return $this->val(); }
		else{ return ' '; }
	}

	/**
	 * Returns the DOFdata value as it must when show on a DOF "admin" list (table)
	 *
	 * @return ing
	 */
	public function TableHTML()
	{
		return $this->HTML();
	}
	
	/**
	 * Returns the DOFdata value as it must when show in WML
	 * @return ing
	 */
	public function WML()
	{
		return $this->HTML();
	}

	
	/**
	 * Returns the DOFdata value as it must when show in WML
	 * @return ing
	 */
	public function XML()
	{
		return $this->HTML();
	}
	
	/**
	 * Returns the DOFdata value as it must when show in WML
	 * @return ing
	 */
	public function JSON()
	{
		check( $this->getClass().' does not have properly implmented the JSON method (try defining it at Data object first)' );
	}
	
	/**
	 * Returns the input field for HTML forms ment to create a new element
	 *
	 * @return string
	 *
	 */
	public function createInput()
	{
		return $this->updateInput(false);
	}

	/**
	 * Returns the input field for HTML forms ment to update an existing element
	 *
	 * @param $printval specifies if the curren data value should be displayes as the predefined value of the input
	 *
	 * @return string
	 *
	 */
	public function updateInput($printval=true)
	{
		//chek($this);
		return "<input class='I".$this->getClass()."' size='45' name='".$this->inputName($prefijoNombre)."'".(($printval)?" value='".$this->val()."'":"")." type='text' />";
	}

	/**
	 * Returns the input field for HTML forms ment to allow searches over the stored DOF elments that contain the DOF data at the specific moment
	 *
 	 * @param $printval specifies if the curren data value should be displayes as the predefined value of the input
	 * @return ing
	 */
	public function searchInput($printval=true)
	{
		return $this->updateInput($printval);
	}
	
	/**
	 * Regresa la  para poner como etiqueta antes de los inputs.
	 *
	 * @return ing
	 */
	public function label()
	{
		//return "<span>get_class($this)</span>";
		if($this->label){ return $this->label; }
		else { return get_class($this); }
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