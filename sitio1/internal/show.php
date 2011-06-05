<?php
require_once "includer.php";


$class = $_REQUEST['class'] ?: 'Nota';
$id_class = $_REQUEST['id'] ?: 1;

$element = new $class($id_class);

$element->toHtml();