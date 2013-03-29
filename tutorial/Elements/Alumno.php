<?php

class Alumno extends Persona{
    
    function construct($id_or_array = null, &$specialDataStorage = null) {
        parent::construct($id_or_array, $specialDataStorage);
        
        $this->matematicas = new \SimplOn\Datas\Integer();
        $this->fisica = new \SimplOn\Datas\Integer();
        $this->quimica = new \SimplOn\Datas\Integer();
        $this->promedio  = new \SimplOn\Datas\Average('Promedio', array('matematicas','fisica','quimica'),'L');
    }
    
}
?>
