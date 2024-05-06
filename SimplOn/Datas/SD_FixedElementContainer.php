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



/**
 * 
 * Encapsulates an Element so it acts as a Data. 
 * 
 * @author RSL
 */
class SD_FixedElementContainer extends SD_ElementContainer {
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
		$column;


	public function name($name = null) {
        if($name){
			$this->name = $name;
            $this->element->nameInParent($name);
		}else{
			return $this->name;
		}
	}
	
	/**
         * 
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
     * 
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
    
	/**
         * 
         * function showView - this function obtains the html content of the son
         * element for introduce just that part into the parent element in the 
         * differents templates and modifies the nesting level of the elements.
         * 
         * @param string $template
         * @return string
         */
	// function showView($template = null)
	// {
    //     if($template) {
    //         $template = SC_Main::loadDom($template);
    //         $template = $this->element->showView($template[$this->cssSelector().' '.$this->element->cssSelector()],true);
    //     } else {
    //        // creates a dummy template
    //        /** @var SC_Element $element */
	// 	   $element = $this->element->getClass();
    //        $element = new $element($this->element->getId());
    //        $template = $element->showView(null, true);
    //     }  
    //     $template=$template;
    //     $dom = \phpQuery::newDocument($template);
        
    //     if(@$element) {
    //         $this->nestingLevelFix($dom);
    //     }
        
    //     return $dom.'';
        
	// }

	function showList(){
		return $this->element->showEmbededStrip();
	}

	public function viewVal() {
		if( $this->element->getId() != $this->val() ){
			$this->element->fillElementById($this->val());
		}
		return $this->element->showEmbededStrip();
	}
	
	function showEmbeded($template = null)
	{
        if($template) {
            $template = SC_Main::loadDom($template);
            $template = $this->element->showEmbeded($template[$this->cssSelector().' '.$this->element->cssSelector()],true);
        } else {
           // creates a dummy template
           /** @var SC_Element $element */
		   $element = $this->element->getClass();
           $element = new $element($this->element->getId());
           $template = $element->showEmbeded(null);
        }  
        $template=$template;
        $dom = \phpQuery::newDocument($template);
        
        if(@$element) {
            //$this->nestingLevelFix($dom);
        }
        
        return $dom.'';
        
	}

	function showEmbededStrip($template = null){
		if($this->val()){
			$this->element->setId( $this->val() );
			return $this->element->showEmbededStrip();
		}
	}
	

	public function val($val=null) {
        if($val){
            $this->setVal($val);
        } else {
            return @$this->element->getId();
        }
    }

	/**
         * 
         * function val - this function introduces the values from database of son element 
         * to database of parent element without modify the original values.
         * @param type $val
         * @return type
         */
	public function setVal($val) {
		if(is_array($val)){
			$this->val = $val;
			return $this->val;
		}else if($val === '') {
			$class = $this->element->getClass();
			$this->element = new $class;
		} else	if($val !== null) {
			$this->element->fillFromDSById($val);
		} else {
			if(is_array($this->val)){
				return $this->val;
			} else return @$this->element->getId();
		}
        $this->element->addData('parentClass' , new SD_Hidden(null,'CUSf', $this->parent->getClassName() )    );
        $this->element->addData('dataName' , new SD_Hidden(null,'CUSf', $this->name(), '' )   );
        $this->element->addData('parentId' , new SD_Hidden(null,'CUSf', $this->parent->getId())  );
        $this->element->addData('selectAction' , new SD_SelectAction('', array('Select')) );
	}

	function optionsDisplay(){
		if($this->val()){
			return  'display: none';
		}else{
			return '';
		}
	}


	/**
         * function input - this function displies the HTML to list and add new values into 
         * parent element. 
         * @param boolean $fill
         * @return string
         */
	// function showInput($fill=true)
	// {
    //     $nextStep = $this->encodeURL('makeSelection');
    //     $addHref = htmlentities(
	// 		$this->element->encodeURL(
	// 			array(),
	// 			'showCreate',
	// 			array(
	// 				'',
	// 				$this->element->encodeURL(
	// 					array(),
	// 					'processCreate',
	// 					array($nextStep)
	// 				)
	// 			)
	// 		)
	// 	);
    //     return  '
    //         <div class="SimplOn label">'.$this->label().':</div>
	// 		<div class="ElementBox">
	// 			<div class="SimplOn preview">
	// 				'.$this->showEmbededUpdateInput().'
	// 			</div>
	// 			<div class="SimplOn options">
	// 				<a class="SimplOn lightbox boton" href="'.htmlentities($this->encodeURL('showSelect')).'"><img src="./Imgs/chooseIcon.webp" alt="Choose"></a>
	// 				<a class="SimplOn lightbox boton" href="'.$addHref.'"><img src="./Imgs/addIcon.webp" alt="Add"></a>
	// 			</div>
	// 		</div>
    //         <input class="SimplOn input" name="'.$this->name().'" type="hidden" value="'.($fill?$this->val():'').'" />
	// 	';
	// }


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


	// function showSearch() {
	// 	$class = $this->element;
	// 	$element = new $class();
	// 	$element->fillFromRequest();
	// 	$parent = $this->parent();
	// 	$element->parent($parent);
	// 	/** @var SC_Element $element 
	// 	 *  @var SC_Element[] $options 
	// 	*/
	// 	$options = $element->Elements();
	// 	$ret = '<div class="SimplOn Data Input '.$this->getClass().'">
	// 				<label>'.$this->label().':</label>
	// 				<div class="Options">';
	// 				foreach ($options as $option) {
	// 					$ret.='<div class="CheckBox"><input id='.$option->fieldId().' class="'
	// 							.$this->getClass().
	// 							'" name="'.$this->name().'[]'.
	// 							'"value="'.$option->getId().
	// 							'"type="radio" '
	// 							.((is_array($this->val()))
	// 							? ((in_array($option->getId(), $this->val())) ? 'Checked' : '')
	// 							: '').
	// 							'/><label for='.$option->fieldId().'>'.$option->showEmbededStrip().'</label>
	// 							</div>';
	// 				}
	// 				$ret.='
	// 				</div>
	// 			</div>';
	// 	return  $ret;
	// }
    

	
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
	
    /**
     * function nestingLevelFix - this function update the nesting level of the elements
     * every time that a new element is added.
     * 
     * @param type $dom
     */
    // function nestingLevelFix(&$dom) {
    //     $startingNestingLevel = $this->parent->nestingLevel();
    //     foreach($dom['.SimplOn.Element, .SimplOn.Data'] as $node) {
    //         $domNode = pq($node);
    //         $classes = explode(' ', $domNode->attr('class'));
    //         if(substr($classes[2], 0, 4) == 'SNL-') {
    //             $nestingLevel = substr($classes[2], 4) + $startingNestingLevel;
    //             $classes[2] = 'SNL-' . $nestingLevel;
    //             $domNode->attr('class', implode(' ', $classes));
    //         }
    //     }
    // }

    function makeSelection($id){
        /*@var parentElement SC_Element */
        $this->element->fillFromDSById($id);

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