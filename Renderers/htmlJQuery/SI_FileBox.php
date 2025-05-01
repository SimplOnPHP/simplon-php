<?php

/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

class SI_FileBox extends SI_Input{
    function __construct( $name, $value='', $placeHolder=False, $required=False, $id = False) {
        parent::__construct( $name, $value, 'file', $placeHolder, $required, $id) ;
    }
}