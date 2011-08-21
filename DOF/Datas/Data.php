<?php
/*
	Copyright © 2011 Rubén Schaffer Levine and Luca Lauretta <http://simplonphp.org/>
	
	This file is part of “SimplOn PHP”.
	
	“SimplOn PHP” is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation version 3 of the License.
	
	“SimplOn PHP” is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with “SimplOn PHP”.  If not, see <http://www.gnu.org/licenses/>.
*/
namespace DOF\Datas;
use \DOF\Main, \DOF\BaseObject;

/**
 * @todo Agregar posibilidad de especificar indices de b�squeda:
 * una posible soluci�n es la de usar el parametro $flags usando el metodo search()
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



abstract class Data extends BaseObject {
	
	protected
	/**
	 * Data value
	 *
	 * @access protected
	 * @var mixed
	 */
	$val,
	
	
	/**
	 * Default value
	 *
	 * @access protected
	 * @var mixed
	 */
	$default = NULL,
	
	
	/**
	 * Data name or label as it must be presented to human readers (in forms, html tables, etc)
	 *
	 * @access protected
	 * @var ing
	 */
	$label,
	
	
	/**
	 * indicates if the DOFdata must be used when generating the default HTML template
	 * @var boolean
	 */
	$view = true,
	
	/**
	 * indicates if the DOFdata must be used in the add(capture) from
	 * @var boolean
	 */
	$create = true,
	
	/**
	 * indicates if the DOFdata must be used in the update from
	 * @var boolean
	 */
	$update = true,
	
	/**
	 * indicates if the DOFdata must be used in the search from
	 * @var boolean
	 */
	$search = false,
	
	/**
	 * indicates if the DOFdata must be used when several items are listed on a html table or list
	 * @var boolean
	 */
	$list = false,
	
	/**
	 * indicates if the DOFdata must have a value in order to allow the storage of it's dataParent DOFelement
	 * @var boolean
	 */
	$required = false,
	
	
	/**
	 * indicates if the DOFdata must have a value in order to allow the storage of it's dataParent DOFelement
	 * @var boolean
	 */
	$fetch = true,
	
	$name,
	
	$filterCriteria = 'name == :name';
	
	
	
	/**
	 * search operands:
	 * >
	 * <
	 * >=
	 * <=
	 * ==
	 * !=
	 * ~=
	 * ^=
	 * $=
	 * 
	 * ***********
	 * 
	 * Complex operations are just simple operations 
	 * jointed with ANDs and ORs, so it'd be possible
	 * to create "Operators" objects that defines those
	 * complex operations.
	 * 
	 * (name > val1) OR (name < val2)
	 * 
	 * a <= x <= b
	 * a > x || x < b
	 */

	
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
	public function __construct($label=null, $flags=null, $val=null, $searchOp=null)
	{
		//check($label);
		$this->construct($label, $flags, $val, $searchOp);
		
		$this->val = $val;
		$this->label=$label;
		
		if($flags)
		{
			$this->dataFlags($flags);
		}
		
		
		/*@todo verificar que esto verdadaramente no se requeire*/
		//if($inputName){$this->inputName($inputName);}
	}

	public function construct($label=null, $flags=null, $val=null, $searchOp=null) {}

	public function fill() {}
	
	
	
	public function preRead() {}
	
	public function preCreate() {}
		
	public function preUpdate() {}

	public function preDelete() {}

	public function preSearch() {}


	//----

	public function doRead()
	{
		return ($this->fetch())
			? array(array($this->name(), $this->getClass()))
			: null;
	}
	
	public function doCreate()
	{
		return ($this->create())
			? array(array($this->name(), $this->getClass(), $this->val()))
			: null;
	}
		
	public function doUpdate()
	{
		return ($this->update())
			? array(array($this->name(),$this->getClass(),$this->val()))
			: null;
	}

	public function doSearch()
	{
		return ($this->search())
			? array(array($this->name(), $this->getClass(), $this->val(), $this->filterCriteria()))
			: null;		
	}

	public function doDelete()
	{}



	
	
	public function postRead()
	{}
	
	public function postCreate()
	{}
		
	public function postUpdate()
	{}

	public function postDelete()
	{}

	public function postSearch()
	{}
	
	
	
	public function filterCriteria($filterCriteria = null) {
		if(isset($filterCriteria)) $this->filterCriteria = $filterCriteria;
		
		return strtr($this->filterCriteria, array(
			'name' => $this->name(),
		));
	}


	
	/**
	 * Sets create, update, search, list, required and fetch flags according to the letters in $flags
	 *
	 *  @param ing $flags This ing indicates the DOFdata where it must be used by the DOFelement's to do so if sended the
	 * sting must contain the first letter of any the following "uses": view, create, update, search, list, required.
	 * if the letter is included (the order desn't matter) that use will be set to true if not to false.
	 * see the help avobe to see what each of this does.
	 */
	function dataFlags($flags = null) {
		// @todo: Optimizar esta parte
		// @todo: check conflict with required and create/update
		// Ej: $this->view( strpos($flags,'v')!==false );
		$a_flags = array(
			'v' => 'view',
			'c' => 'create',
			'u' => 'update',
			's' => 'search',
			'l' => 'list',
			'r' => 'required',
			'f' => 'fetch',
		);
		
		if(isset($flags)) {
			foreach(str_split($flags) as $flag)
				$this->{$a_flags[strtolower($flag)]} = ($flag != strtolower($flag));
		} else {
			$return = '';
			foreach($a_flags as $letter => $flag)
				$return.= $this->$flag ? strtoupper($letter) : strtolower($letter);
			return $return;
		}
	}
	
	
	public function getJS($method) {
		$class = end(explode('\\',$this->getClass()));
		$local_js = Main::$JS_FLAVOUR_BASE . "/Inits/$class.$method.js";
		return array($local_js);
	}
	
	
	
	/**
	 * Tells PHP how to render this object as a string
	 *
	 * @return ing
	 */
	public function __toString() {
		return $this->showView();
	}

	/**
	 * Gives the name of the field for the create or update forms (usualy an HTML form)
	 *
	 * @param $prefijoNombre =null valor que se puede usar para distinguir los diversos de dos elementos
	 * @return ing
	 */
	public function inputName(){
		return @$this->inputName ?: $this->name();
	}
	
	function showView($template = null){
		return $this->val();
	}
	
	abstract function showInput($fill);
	
	function showCreate(){
		return $this->showInput(false);
	}
	
	function showUpdate(){
		return $this->showInput(true);
	}
	
	function showSearch(){
		return $this->showInput(true);
	}
	
	/**
	 * Returns the label for the input
	 *
	 * @return ing
	 */
	public function label(){
		return $this->label ?: $this->getClass();
	}
}