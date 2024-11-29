<?php


class SI_Container extends SC_BaseObject {

    protected
        $name = '',
        $defaultItemMethod = 'showView',
        $parent,
        $renderer,
        $items = [];


    function __construct(array $items = [], $defaultItemMethod = 'showView' )
    {
        $split = explode('::', $defaultItemMethod);
        if(isset($split[1])){$this->defaultItemMethod = $split[1];}
        else{$this->defaultItemMethod = $defaultItemMethod;}
        $this->renderer = SC_Main::$RENDERER;  
        $this->items($items);
    }

    function addItem($item, $method = null)
    {
        if(empty($method)){$method = $this->defaultItemMethod;}
        $this->items[] = ['item'=>$item,'method'=>$method];
    }

    function items($items = null, $method = null)
    {
        if(!empty($items)){
            $this->items = [];
            if(empty($method)){$method = $this->defaultItemMethod;}
            foreach($items as $item){
                $this->items[] = ['item'=>$item,'method'=>$method];
            }
        }else{
            return $this->items;
        }
    }

	public function parent($parent = null)
	{
		if($parent){

			foreach ($this->items as $item) {

                if (is_object($item['item']) AND property_exists($item['item'], 'parent')  ) {
				    $item['item']->parent($parent);
    
                }
			}
			$this->parent = $parent;
		}else{

				return $this->parent;

		}
	}

    function changeItemMethod($itemIndex, $method)
    {
        if(!empty($this->items[$itemIndex])){
            if(isset($this->items[$itemIndex]['method'])){ $this->items[$itemIndex]['method']=$method; }
            else{$this->items[$itemIndex] = ['item'=>$this->items[$itemIndex],'method'=>$method];}
        }
    }
}