<?php


class SD_ElementsContainerMM extends SD_Data {
	protected 
		$fetch = false,
        
		/**
		 * Logic parent
		 * @var SE_Element
		 */
		$parent, 
            
		/**
		 * List of Elements
		 * @var array of Element
		 */
		$elements = array(),


        $element,
        
		/**
		 * Pivot element (for pivot tables)
		 * @var SE_Element
		 */
		$pivot,

        /**
         * @var array of Elements instances or names
         */
        $allowedClassesInstances = array();

    public function id(){
        return 'aaa';
    }
	
    public function __construct(  $element, $label=null, $flags=null, $element_id=null) {

        // if(!is_array($allowedClassesInstances)){ 
        //     $allowedClassesInstances = [$allowedClassesInstances]; 
        // }

        // foreach($allowedClassesInstances as $e) {
        //     if (is_string($e) && class_exists($e)) {
        //         $this->allowedClassesInstances[$e] = new $e;
        //     } else if( $e instanceof SE_Element ) {
        //         $this->allowedClassesInstances[$e->getClass()] = $e;
        //     } else {
        //         // error elements must be an array of valid classes names or Elements
        //     }
        // }

		if($element instanceof SE_Element){
			if($element_id){$element->setId($element_id);}
			$this->element($element);
		}else{
			//To avoid ciclic Calls Use a elements stack and do no create an element that depends on the elment thats calling it
			$GLOBALS['callersStack'][] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['class'];

			if(in_array($element,$GLOBALS['callersStack'])){

				$element = new SE_ElementContainerIDPlaceHolder();
				$this->element($element);
			}else{

				$element = new $element();
				$this->element($element);
			}
			array_pop($GLOBALS['callersStack']);
		}
    }

	function elements($elements = null)
	{
        // $colum = $this->column;

        // if( empty( $this->element->{$colum}() ) ){ 
        //     $this->val($this->parent->getId());
        //     $this->element->{$colum}( $this->val() ); 
        // }

                    //$search = new SE_Search(array($this->element->getClass()));
                    //$search->getResults();

                    //$this->element->filterCriteria('cuentaSalida = :cuentaSalida');

        // /** @var parent->dataStorage() SDS_DataStorage */
        // $results = $this->parent->dataStorage()->readElements($this->element, 'Elements', $position, $limit);

        // $params = ['colsTitles'=>true,'rowsTitles'=>false,'colsTitlesIn'=>'keys','rowsTitlesIn'=>'keys','columnsToAdd'=>array()];

        // //print(var_dump($results));

        if(is_array($elements)){$this->elements=$elements;}
        else{

            if(!is_array($this->elements)){$this->postRead();}

            if( !empty($this->elements) ){
                $results = new SD_Table('Results',$params,$this->elements);
            }else{
                $results = new SD_Text('Results','VL','No hay datos que mostrar');
            }

            /** @var SR_html $redender */
            $redender = $GLOBALS['redender'];
            return $redender->renderData($results,'showView',null,1);
        }
	}

	
	public function val($val = null) {
		if($val === '') {
			$this->elements = array();

		} elseif(is_array($val)) {
            if(!$this->fixedValue) {
                $this->elements = array();
                foreach($val as $key => $id) {
                    if(is_numeric($id)) {
                        $class = $this->element->getClass();
                        $this->elements[$key] =  new $class($id);
                    }
                }
            }
		} else {
			return $this->elements;
		}
	}

    /**
     * @param Element $parent
     * @return Element
     */
    // function parent(&$parent = null){
    //     if($parent === null){
    //         return $this->parent;
    //     } else {
    //         $this->parent = $parent;
            
    //         if(!$this->pivot){
    //            $this->pivot = new SE_PivotTable(null, 'Pivot_'.strtr($this->parent->getClass(), '\\', '_').'_'.$this->name);
    //         }
            
    //         foreach($this->elements as $element){
    //             $element->parent($parent);
    //         }
        
    //         foreach($this->allowedClassesInstances as $classInstance){
    //             $classInstance->nestingLevel($parent->nestingLevel()+1);
    //         }
    //     }
    // }

    /**
     * @param Element $parent
     * @return Element
     */
    function parent(&$parent = null){
        if($parent === null){
            return $this->parent;
        } else {
            $this->parent = $parent;
            
            if(!$this->pivot){
               $this->pivot = new SE_PivotTable(null, 'Pivot_'.strtr($this->parent->getClass(), '\\', '_').'_'.$this->name,$this->parent->dataStorage());
            }
            
            foreach($this->elements as $element){
                $element->parent($parent);
            }
        
            foreach($this->allowedClassesInstances as $classInstance){
                $classInstance->nestingLevel($parent->nestingLevel()+1);
            }
        }
    }



    function test(){


    }



    // function makeAppendSelection($id){ 
    //     $element = new $class($id);
    //     $element->nestingLevel($this->parent->nestingLevel() + 1);
    //     $return = array(
	// 		'status' => true,
	// 		'type' => 'commands',
	// 		'data' => array(
    //             array(
    //                 'func' => 'appendContainedElement',
    //                 //'func' => 'prependContainedElement',
    //                 'args' => array($this->showInputView($element),null,'.preview')
    //             ),
    //             array(
    //                 'func' => 'closeLightbox'
    //             ),
    //         )
    //     );
        
    //     header('Content-type: application/json');
    //     echo json_encode($return);
    // }


	public function doRead(){}
	public function postRead(){
        // loads up
        $this->pivot->parentId($this->parent->getId());

        //loads all elements from the table
        $array_elements = $this->pivot->dataStorage()->readElements($this->pivot);
        $this->elements = array();
        foreach($array_elements as $data) {
            $element = new $data['childClass']($data['childId']);
            $element->parent($this->parent);
            $element->nameInParent($this->name); //check if it's not $this->element->nameInParent($this->name); 
            $this->elements[] = $element;
        }


	}
   
	public function doCreate(){}
	public function postCreate(){
        $this->pivot->parentId($this->parent->getId());
        
        //delete all elements in the table
        $tmpId = $this->pivot->getId();
        $this->pivot->clearId();
        $this->pivot->dataStorage()->delete($this->pivot);
        $this->pivot->setId($tmpId);
        // create
        foreach($this->elements as $element) {
            $this->pivot->childId($element->getId());
            $this->pivot->childClass($element->getClass());
            $this->pivot->create();
        }
    }
		
	public function doUpdate(){
        // update
        $this->postCreate();
	}

	public function doSearch(){}

	public function name($name = null) {
        if($name){
			$this->name = $name;
            $this->element->nameInParent($name);
		}else{
			return $this->name;
		}
	}

    public function updateElements(){
        //TODO make this method use the renderer so it can be truly independent ( as it's now will only work on text based rederers)
        $ret = '';
        foreach($this->elements as $element){
            $element->nameInParent($this->name().'[]');
            $ret .= $element->showEmbededAppendInput();
        }
        return $ret;

    }


}