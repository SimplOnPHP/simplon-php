<?php


class SD_ComplexData extends SD_Data {

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

    public function __construct( $label=null, $dataPrepare =null, $layouts=null, $flags=null, $val=null, $filterCriteria=null)
	{
        
        if(is_callable($dataPrepare)){$this->dataPrepare = $dataPrepare->bindTo($this);}else{$this->dataPrepare = null;}
        if($layouts instanceof SI_Item){$this->layouts = ['showView'=>$layouts];}
        elseif(is_array($layouts)){$this->layouts = $layouts;}
        parent::__construct($label,$flags,$val, $filterCriteria);
	}

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
