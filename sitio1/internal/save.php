<?php
require_once "includer.php";


$class = $_REQUEST['class'];

$element = new $class();

$element->fillFromRequest()->saveInDS();