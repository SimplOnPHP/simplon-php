<?php
require_once "includer.php";

$class = $_REQUEST['class'] ?: 'Nota';

$element = new $class();

$element->PformElementGenerator('','save.php');