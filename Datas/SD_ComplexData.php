<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

/**
* Data type made to as base class for datas that combine or use other datas in an element.
*
* SD_ComplexData serves as a foundation for data types that derive their value or representation
* by processing or combining the values of other {@see SC_Data} attributes within the same {@see SC_Element} instance.
* Unlike simple data types that hold a single value directly, complex data types perform
* operations on other data fields to produce their output for viewing, listing, etc.
* They are typically not designed for direct creation, update, or storage, but rather
* for presenting synthesized information derived from other parts of the Element.
*
* Examples include:
*   SD_Concat:     That joins several SD_String in a single string like when displainyg full name made of name and last name
*   SD_TimeSince:  That calculates time since a SD_Date like when displaying age
*   SD_Sum:        That Sums several numeric datas like in Amount + Taxes to  make a Total
*
* SD_ComplexData can also be used directly with a custom data prepare function and/or layouts.
* like in {SC_Element::showAdmin()} method.
*
* @version 1b.1.0
* @package SimplOn\Datas
* @author Ruben Schaffer
*/
class SD_ComplexData extends SC_Data {

    protected $components,
        $dataPrepare,
        $layouts;

    protected
        $view = true,
        $create = false,
        $update = false,
        $search = false,
        $list = true,
        $required = false,
        $fetch = false,
        $embeded = true,
        $delete = false;

    /**
     * Constructor for SD_ComplexData.
     *
     * Initializes a new instance of the SD_ComplexData class. This data type is
     * designed to represent complex data derived from other data fields within an Element.
     *
     * @param string|null $label An optional label for the data element.
     * @param callable|null $dataPrepare An optional callable function to prepare the data before displaying.
     *                                   This function will be bound to the current object instance.
     * @param SI_Item|array|null $layouts An optional SI_Item instance or an array of SI_Item instances
     *                                  defining the layouts for different display methods (e.g., 'showView', 'showList').
     *                                  If a single SI_Item is provided, it will be used for 'showView'.
     * @param int|null $flags Optional flags to modify the data's behavior.
     * @param mixed|null $val Optional initial value for the data.
     * @param mixed|null $filterCriteria Optional criteria for filtering the data.
     */
    public function __construct( $label=null, $dataPrepare =null, $layouts=null, $flags=null, $val=null, $filterCriteria=null){
        if(is_callable($dataPrepare)){$this->dataPrepare = $dataPrepare->bindTo($this);}else{$this->dataPrepare = null;}
        if($layouts instanceof SI_Item){$this->layouts = ['showView'=>$layouts];}
        elseif(is_array($layouts)){$this->layouts = $layouts;}
        parent::__construct($label,$flags,$val, $filterCriteria);
	}

	/**
	 * Displays the complex data based on the specified method.
	 *
	 * This method handles the display logic for the complex data. It first checks for
	 * render overrides or fixed values. If a data preparation function is set, it is called.
	 * It then attempts to use a layout defined for the given method. If no specific layout
	 * is found but a 'showView' layout exists, it falls back to 'showView'.
	 *
	 * @param string $method The display method to use (e.g., 'showView', 'showList').
	 * @return SI_Item|string|false An SI_Item instance representing the layout for the specified method,
	 *                              an empty string if render override is 'showEmpty', or false if no
	 *                              suitable layout is found.
	 */
	public function show($method) {

		if($this->renderOverride()=='showEmpty'){return '';}elseif($this->fixedValue()) {$input =  new SI_FixedValue($this->name(), $this->viewVal());
		}else{	
            if(is_callable($this->dataPrepare)){($this->dataPrepare)();}
        
            if (!isset($this->layouts[$method]) && isset($this->layouts['showView'])) {
                $method = 'showView';
            } else {
                return false;
            }
            $layout = clone $this->layouts[$method];
            
            //$layout->object($this->parent()); 
            return $layout;
		}
	}

	public function showView() {
		return $this->show(__METHOD__);
	}

	public function showList() {
		return $this->show(__METHOD__);
	}
	
	public function showEmbeded() {
		return $this->show(__METHOD__);
	}

	public function showCreate() {
		return $this->show(__METHOD__);
	}
		
	public function showUpdate() {
		return $this->show(__METHOD__);
	}

	public function showDelete() {
		return $this->show(__METHOD__);
    }

	public function showSearch() {
		return $this->show(__METHOD__);
	}
}
