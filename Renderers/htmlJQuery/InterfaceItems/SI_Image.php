<?php


class SI_Image extends SI_Item{
    protected
        $image,
        $alt,
        $imageUrl;  
    
    function __construct($image, $alt = null)
    {
        
        parent::__construct();

        
        $this->image = $image;
        $this->imageUrl = $this->renderer->imgsWebRoot().DIRECTORY_SEPARATOR.$image;
        if (empty($alt)) { $this->alt = substr($image, 0, -4); }
    }

}