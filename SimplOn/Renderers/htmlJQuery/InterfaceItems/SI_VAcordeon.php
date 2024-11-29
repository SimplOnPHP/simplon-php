<?php

class SI_VAcordeon extends SI_Container {
    protected 
        $activeIndex = 0;
    
    public function __construct(array $items = [], $defaultItemMethod = 'showView') {
        parent::__construct($items, $defaultItemMethod);
    }
    
    public function setActiveIndex($index) {
        $this->activeIndex = $index;
        return $this;
    }
}
