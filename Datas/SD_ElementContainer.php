<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/



/**
 * 
 * Encapsulates an Element so it acts as a Data. 
 * 
 * @author Ruben Schaffer
 */
class SD_ElementContainer extends SC_Data {

	protected
		$layout,	
		$tagId,

		$AdminMsg,
		$ReturnBtnMsg,
		$CancelBtnMsg,

		$SearchBtnMsg,
		$SearchMsg,

		$ViewBtnMsg,
		$ViewMsg,

		$CreateBtnMsg,
		$CreatedMsg,
		$CreateMsg,
		$CreateError,

		$UpdateBtnMsg,
		$UpdatedMsg,
		$UpdateMsg,
		$UpdateError,

		$DeleteBtnMsg,
		$DeletedMsg,
		$DeleteMsg,
		$DeleteError;


	protected 
		/**
		 * Logic parent
		 * @var SC_Element
		 */
		$parent, 
            
		/**
		 * Encapsulated element
		 * @var SC_Element
		 */
		$element,
		
		/*
		 * Column's name where values will be extracted
		 * @var String
		 */
		$column,
		

		$allowCreateButton = true;

		static $methodsFamilies;
		static $permissions;


	public function __construct( $element, $labelORid=null, $column = null, $flags=null, $element_id=null) {

		if(empty(self::$methodsFamilies)){
			self::$methodsFamilies = array_merge(
				SC_Element::$methodsFamilies,
				[
					'showElementCreate' => 'Create',
					'processElementCreate' => 'Create',
					'showElementSelect' => 'View', 
					'refreshElementSelect' => 'View',
					'makeChangeSelection' => 'View'
				]
			);
		}


		if (is_string($element) AND class_exists($element)) {
			$element = new $element($labelORid);
			$label = 'label';
		}else{
			$label = $labelORid;
		}

		$this->column = $column;

		if($element instanceof SC_Element){
			if($element_id){$element->setId($element_id);}
		}else{

			throw new SC_Exception('Error: '.$element.' in Element Container is not a valid element');
			// //To avoid ciclic Calls Use a elements stack and do no create an element that depends on the elment thats calling it
			// $GLOBALS['callersStack'][] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['class'];

			// if(in_array($element,$GLOBALS['callersStack'])){

			// 	$element = new SC_ElementContainerIDPlaceHolder();
			// 	//$this->element($element);
			// }else{

			// 	$element = new $element();
			// 	//$this->element($element);
			// }
			// array_pop($GLOBALS['callersStack']);
		}
		
		$this->element($element);
		parent::__construct($label,$flags,$element_id);


		$this->AdminMsg = $element->NamePlural().' '.SC_MAIN::L('Manager');

		$this->ReturnBtnMsg = SC_MAIN::L('Return');
		$this->CancelBtnMsg = SC_MAIN::L('Cancel');

		$this->SearchBtnMsg = SC_MAIN::L('Search');
		$this->SearchMsg = SC_MAIN::L('Search').' '.$element->NamePlural();
 
		$this->ViewBtnMsg = SC_MAIN::L('View');
		$this->ViewMsg = SC_MAIN::L('View of '.$element->Name());

		$this->selectBtnMsg = SC_MAIN::L('Select');
		$this->selectMsg = SC_MAIN::L('Select a '.$element->Name());

		$this->CreateBtnMsg = SC_MAIN::L('Create');
		$this->CreateMsg = SC_MAIN::L('Create a '.$element->Name());
		$this->CreatedMsg = SC_MAIN::L('A '.$element->Name().' has been created');
		$this->CreateError = SC_MAIN::L('A '.$element->Name().' can\'t be created');

		$this->UpdateBtnMsg = SC_MAIN::L('Update');
		$this->UpdateMsg = SC_MAIN::L('Update a '.$element->Name());
		$this->UpdatedMsg = SC_MAIN::L('The '.$element->Name().' has been updated');
		$this->UpdateError = SC_MAIN::L('The '.$element->Name().' can\'t be updated');

		$this->DeleteBtnMsg = SC_MAIN::L('Delete');
		$this->DeleteMsg = SC_MAIN::L('Delete a '.$element->Name());
		$this->DeletedMsg = SC_MAIN::L('A '.$element->Name().' has been deleted');
		$this->DeleteError = SC_MAIN::L('The '.$element->Name().' has been deleted');

	}


	public function layout($layout = null) {
		if($layout){
			$this->layout = $layout;
			if(empty($this->layout->getAttribute('name'))){$this->layout->addAttribute('name',$this->name());}
		}else{
			return $this->layout;
		}
	}

	public function name($name = null) {
        if($name){
			$this->name = $name;
            $this->element->nameInParent($name);
			if($this->layout AND empty($this->layout->getAttribute('name'))){$this->layout->addAttribute('name',$this->name());}
		}else{
			return $this->name;
		}
	}
	
	/**
	 * function getJS - this function modifies the url of the encapsulated element to 
	 * don't display a incorrect url
	 * 
	 * @param string $method
	 * @return array
	 */
	public function getJS($method) {
		return array_map(
			function($fp) {
				return str_replace(SC_Main::$WEB_ROOT, SC_Main::$LOCAL_ROOT, $fp);
			},
			$this->element->getJS($method)
		);
	}

    /**
     * function parent -in this function you can designate a specific parent to 
     * use in this data, if you don't to use another parent, this function return 
     * the origal logic parent else change the logic parent for the new parent and
     * execute this function in the element to change the parent of the element.
     * 
     * @param object $parent
     * @return object
     */
    function parent(&$parent = null){
        if(!$parent){
            return $this->parent;
        } else {
            $this->parent = $parent;
            $this->element->parent($parent);
        }
    }

	function showList(){
		return $this->showEmbededStrip();
	}

	function showSearch(){
		$this->allowCreateButton = false;
		return $this->showUpdate();
	}

	function hiddenInput(){
		if($this->renderOverride()=='showEmpty'){
			return '';
		}elseif( $this->fixedValue() ){
			$input =  new SI_FixedValue($this->name(), $this->viewVal());
		}else{
			$input = new SI_Input($this->name(), $this->val(),'hidden');
		}
		return $input;
	}

	function options()
	{
		if(empty($this->options)){
			$search = new SE_Search(array($this->element->getClass()));

			$search->getResults(array());//need to send an empty array to avoid fillFrom Request to be called and thus allways properly read all the options	
			$options = array();
			$options[' '] = '';

			foreach($search->searchResults() as $elementIndex => $element) {
				$options[$element->getId()] = $element->showEmbededStrip();	
			}
			$this->options = $options;
		}
		return $this->options;
	}

	function showCreate(){
		if($this->renderOverride()=='showEmpty'){
			return '';
		}elseif( $this->fixedValue() ){
			$input =  new SI_FixedValue($this->name(), $this->viewVal());
		}elseif(empty($this->layout) OR $this->layout == 'default'){
			$input = new SI_ECInput($this);
		}elseif($this->layout instanceof SI_Options){
			$input = $this->layout;
			$input->name($this->name());
			$input->options($this->options());
			$input->required($this->required());
		}
		$inputBox = new (SC_Main::$RENDERER::$InputBox_type)($input, $this->label());
		return $inputBox;
	}

	function showUpdate(){
		if($this->renderOverride()=='showEmpty'){
			return '';
		}elseif( $this->fixedValue() ){
			$input =  new SI_FixedValue($this->name(), $this->viewVal());
		}elseif(empty($this->layout) OR $this->layout == 'default'){
			$input = new SI_ECInput($this);
		}elseif($this->layout instanceof SI_Options){
			$input = $this->layout;
			$input->options($this->options());
			$input->required($this->required());
			$input->name($this->name());
			$input->selected($this->val());
		}
		$inputBox = new (SC_Main::$RENDERER::$InputBox_type)($input, $this->label());
		return $inputBox;
	}

	function allowCreateButton($allowCreateButton = null){
		if($allowCreateButton !== null){
			$this->allowCreateButton = $allowCreateButton;
		}else{
			if( $this->checkPermissions($this->element,'showCreate') AND $this->allowCreateButton ){	
				return true;
			}else{
				return false;
			}
		}
	}


	function showElementCreate($tagId){
		$content = new SI_VContainer();
		$content->addItem(new SI_Title($this->element::$CreateMsg,4));
		
        $action = SC_Main::$RENDERER->encodeURL($this->getClass(), [$this->element()->getClass()],'processElementCreate');	
		$form = new SI_Form($this->element->datasWith('create','show'), $action);

		//$form->addItem( new SI_HContainer([  new SI_Submit(SC_Main::L('Create')), new SI_CancelButton()  ]) );
		$form->addItem( new SI_Input('tagId', $tagId, 'hidden') );
		$form->addItem( new SI_Submit(SC_Main::L('Create')) );
		$form->addItem( new SI_CancelButton() );
		
		$content->addItem($form);

		return $content;
	}

	function showElementSelect($tagId){
		$this->element->fillFromRequest();
		$this->tagId = $tagId;

		$content = new SI_VContainer();

		$content->addItem(new SI_Divider(new SI_Title($this->element::$SearchMsg,5)));

			$form = new SI_Form($this->element->datasWith('search','show'), SC_Main::$RENDERER->encodeURL($this->getClass(), [$this->element()->getClass()],'refreshElementSelect',[$tagId]));
			//$form->ajax = false;
			$form->addItem( new SI_Submit(SC_Main::L('Search')) );
			$form->addItem( new SI_CancelButton() );
		$content->addItem($form);

			$elements = $this->element->dataStorage()->readElements($this->element, 'Elements');

			$selectAction = new SD_ECAction(SC_Main::L('Select'), $this,'makeChangeSelection',SC_Main::L('Select'),'selectIcon.svg');

			$table = new SI_Table($elements, [SC_Main::L('Select: ') => $selectAction], 'list' );
			$table->addAttribute('id',$tagId.'t');
		$content->addItem($table);

		return $content;
	}

	function refreshElementSelect($tagId){
		
		$this->element->fillFromRequest();
		$this->tagId = $tagId;

		$elements = $this->element->dataStorage()->readElements($this->element, 'Elements');
		$selectAction = new SD_ECAction(SC_Main::L('Select'), $this,'makeChangeSelection',SC_Main::L('Select'),'selectIcon.svg');
		$table = new SI_Table($elements, [SC_Main::L('Select: ') => $selectAction], 'list' );
		$table->addAttribute('id',$tagId.'t');

		$return = array(
			'status' => true,
			'type' => 'commands',
			'data' => array(
				array(
					'func' => 'changeTag',
					'args' => array('#'.$tagId.'t',$table->html() )
				)
			)
		);
		header('Content-type: application/json');
		echo json_encode($return);
	}

	function checkPermissions($element, $method){				
		$mode='';
		if(isset($element::$methodsFamilies[$method])){$mode = $element::$methodsFamilies[$method];}
		
		// If there is a user that can enter

		if(empty(SC_MAIN::$PERMISSIONS)){
			return true;
		}elseif( SC_MAIN::$PERMISSIONS->canEnter($element,$mode) ){	
			//if there is a set of values for the Element Datas set them
			if($mode){
				SC_MAIN::$PERMISSIONS->setValuesByPermissions($element, $mode);
			}
			//echo call_user_func_array(array($element, $method), self::$method_params);
			return true;
		//if there is a user that can't enter
		}elseif(SC_MAIN::$PERMISSIONS->logedIn()){
			//SC_MAIN::$SystemMessage='You can\'t access that page ';
			return false;
		//if there is no user or else
		}else{
			return false;
		}
	}

	function permissions(){
		return $this->element::$permissions;
	}

	function processElementCreate(){
		try {
			$this->element->fillFromRequest();
			$this->checkPermissions($this->element,'processCreate');
			$this->element->validateForDB();
		} catch (SC_ElementValidationException $ev) {
			$data = array();
			foreach ($ev->datasValidationMessages() as $key => $value) {
				$data[] = array(
				'func' => 'showValidationMessages',
				'args' => array($key, $value[0])
				);
			}
			$return = array(
			'status' => true,
			'type' => 'commands',
			'data' => $data
			);
			$return = json_encode($return);
			return $return;
		}
		try {
			$this->element->create();
			$this->val($this->element->getId());
			if ($this->val()) {
					
					return $this->makeChangeSelection($_REQUEST['tagId']);
			} else {
				// @todo: error handling
				user_error($this->CreateError, E_USER_ERROR);
			}
		} catch (\PDOException $ev) {
			//user_error($ev->errorInfo[1]);
			//@todo handdle the exising ID (stirngID) in the DS
			user_error($ev);
		}

	}
	
	function makeChangeSelection($tagId){
		$this->val($this->element->getId());
		$this->element->fillFromDSById($this->val());
		$this->checkPermissions($this->element,'showView');

		$remove = new SI_AjaxLink('#', 'Deselect', 'removeIcon.svg');
		$remove->addAttribute('onclick',"$('#$tagId').find('.value').val('');$('#$tagId > :nth-child(3)').html('');return false;");

		$return = array(
			'status' => true,
			'type' => 'commands',
			'data' => array(
				array(
					'func' => 'changeContent',
					'args' => array('#'.$tagId.' > :nth-child(3)',$this->showEmbeded().$remove )
				),
				array(
					'func' => 'changeValue',
					'args' => array('#'.$tagId.' .value',$this->val() )
				),
				array(
					'func' => 'closeModal'
				),
			)
		);
		header('Content-type: application/json');
		echo json_encode($return);
	}


	public function viewVal() {
		if( $this->element->getId() != $this->val() ){
			$this->element->fillFromDSById($this->val());
			$this->checkPermissions($this->element,'showView');
		}
		return $this->element->showEmbededStrip();
	}
	
	function showEmbeded($template = null)
	{	
		if($this->val()){
			$this->element->setId( $this->val() );
			$this->element->fillFromDSById();
			$this->checkPermissions($this->element,'showView');
			return $this->element->showEmbeded();
		}
	}

	function showEmbededStrip($template = null){
		if($this->val()){
			$this->element->setId( $this->val() );
			$this->checkPermissions($this->element,'showView');
			return $this->element->showEmbededStrip();
		}
	}
	


	/**
         * 
         * function val - this function introduces the values from database of son element 
         * to database of parent element without modify the original values.
         * @param type $val
         * @return type
         */
	public function val($val = null) {


		if($val){
			if(!$this->fixedValue) {
				$this->val = $val;
				return $this->val;
			}
		}else{
			return $this->val;
		}








		// if(is_array($val)){
		// 	if(!$this->fixedValue) {
		// 		$this->val = $val;
		// 		return $this->val;
		// 	}
		// }else if($val === '') {
		// 	$class = $this->element->getClass();
		// 	$this->element = new $class;
		// } else	if($val !== null) {
		// 	if(!$this->fixedValue) {
		// 		$this->element->fillFromDSById($val);
		// 	}
		// } else {
		// 	if(is_array($this->val)){
		// 		return $this->val;
		// 	} else return @$this->element->getId();
		// }
        // $this->element->addData('parentClass' , new SD_Hidden(null,'CUSf', $this->parent->getClass() )    );
        // $this->element->addData('dataName' , new SD_Hidden(null,'CUSf', $this->name(), '' )   );
        // $this->element->addData('parentId' , new SD_Hidden(null,'CUSf', $this->parent->getId())  );
        // //$this->element->addData('selectAction' , new SD_SelectAction('', array('Select')) );
	}

	function optionsDisplay(){
		if($this->val()){
			return  'display: none';
		}else{
			return '';
		}
	}

    /**
     * function showEmbededUpdateInput - this function works after function showInput, after 
     * have listed or added new values you can edit them or delete them without modify 
     * the original values and put the HTML to do it.
     * 
     * @return string
     */
	public function showEmbededUpdateInput()
	{
		if($this->val()){
			return $this->element->showEmbededUpdateInput(null,1);
		}
	}

	public function hiddenSelector(){
		if ($this->element){
			return '';
		}else{
			return 'hidden';
		}
	}

	public function showEmbededSearchInput()
	{
		if($this->val()){
			return $this->element->showEmbededSearchInput(null,1);
		}
	}

	
	public function doSearch(){
		$value = $this->val();
		$num = 0;
		if(is_array($value)){
			$arr = array();
			if($this->search() && $this->fetch()){
				foreach ($value as $val){
					$arr[] = array($this->name().$num, $this->getClass(), $val, $this->filterCriteria());
					$num += 1;
				}
			}
		} else {
			$arr= ($this->search() && $this->fetch())
			? array(array($this->name(), $this->getClass(), $this->val(), $this->filterCriteria()))
			: null;
		}

		return $arr;
	}

	public function filterCriteria($filterCriteria = null) {
		$glue = '_';
		$name = $this->name();
		$num = 0;
		$paste = '';
		$ret =array();
		$values = $this->val();
		if(is_array($values)){
			foreach ($values as $value) {
				if(property_exists($this->parent,$name.$num)){
					$paste = $name.$glue.$num;
					while(property_exists($this->parent,$paste)){
						$paste .= $glue;
					}
					$ret[]=$name.' == :'.$paste;
				}else{
					$ret[]=$name.' == :'.$name.$num;
				}
				$num += 1;
			}
			$criteria = implode(' OR ', $ret);
			return $criteria;
		}else{
			return parent::filterCriteria($filterCriteria);
		}
	}
	
    function makeSelection($id){
        /*@var parentElement SC_Element */
        $this->element->fillFromDSById($id);
		$this->checkPermissions($this->element,'showView');

        $return = array(
			'status' => true,
			'type' => 'commands',
			'data' => array(
                array(
                    'func' => 'changeValue',
                    'args' => array($this->element->getId())
                ),
                array(
                    'func' => 'changePreview',
                    //'args' => array($this->showEmbededUpdateInput(SC_Main::$GENERIC_TEMPLATES_PATH . $short_template, true).'')
                    'args' => array($this->showEmbededStrip())
					//'args' => array('Hola tu')
                ),
                array(
                    'func' => 'closeLightbox'
                ),
            )
        );

        header('Content-type: application/json');
        echo json_encode($return);

        
    }
}