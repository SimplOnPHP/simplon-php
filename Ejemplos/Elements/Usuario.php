<?php

use \SimplOn\Elements\Element, \SimplOn\Datas;

class Usuario extends Element {

 function construct($id = null, &$specialDataStorage = null) {
     $this->id = new Datas\StringId('Nombre de usuario');
     $this->password = new Datas\Password('Contraseña');
     $this->maestro = new Datas\RadioButtonSelfId(null, 'C');
     $this->alumno = new Datas\RadioButtonSelfId(null, 'C');
     $this->bio = new Datas\Text('Bio');
    }

}
?>
