<?php

use \SimplOn\Elements\Element, \SimplOn\Datas;

class Ejemplos extends Element{

    public $semestres=array(
        'Primero'=>'1',
        'Segundo'=> '2',
        'Tercero'=> '3');
    
    public $carreras=array(
        'uno'=>'Sistemas',
        'dos'=> 'Redes',
        'tres'=> 'Ofimatica');
    
 function construct($id = null, &$specialDataStorage = null) {
    $this->mensaje = new Datas\Message('Mensaje','L','Sistema Escolar');
    $this->id = new Datas\NumericId('Id');
    $this->nombre = new Datas\String('Nombre','vUR');
    $this->apellido = new Datas\Alphabetic('Apellidos','vUR');
    $this->direccion = new Datas\Alphanumeric('Matricula','vUR');
    $this->correo = new Datas\Email('E-mail','CsUR');
    $this->calif1 = new Datas\Integer('Calificacion Parcial 1','vUR');
    $this->calif2 = new Datas\Integer('Calificacion Parcial 2','vUR');
    $this->calif3 = new Datas\Integer('Calificacion Parcial 3','vUR');
    $this->div = new Datas\Average('Promedio',array('calif1','calif2','calif3'),'VL');
    $this->materia = new Datas\ElementContainer(new Materia(),'Materia','CLV');
    $this->semestre = new Datas\RadioButtonNumber('Semestre ',$this->semestres,'CU');
    $this->inicioSem = new Datas\TimeSince('Inicio de Semestre', null, 'days', 'ymd');
    $this->finSem = new Datas\TimeTo('Fin de Semestre', null, 'days', 'ymd');
    $this->carrera = new Datas\RadioButtonText('Carrera ',$this->carreras,'CU');
    $this->colegiatura = new Datas\Float('Colegiatura $','CsURL');
    $this->fecha = new Datas\Date('Fecha','SUR');
    $this->comentarios = new Datas\HTMLText('Comentarios','sU');
    $this->compose = new Datas\Compose('', array('%s %s','nombre','apellido'), 'V');
    $this->concat = new Datas\Concat('', array('-','nombre','apellido'));
    $this->separador = new Datas\Message('Separador', 'L', '<br /><h2>Ejemplos de ElementsContainer</h2><br />');
    $this->componentes1 = new Datas\ElementsContainer(array(new Llantas(),new Motor()),'ElementsContainer');
    $this->componentes2 = new Datas\ElementsContainerAddAtView(array(new Llantas(),new Motor()), 'ElementsContainerAddAtView');
    $this->componentes3 = new Datas\ElementsContainerViewAndCreate(array(new Llantas(),new Motor()), 'ElementsContainerViewAndCreate');
    
    //Datas para Metodos Especiales
    $this->crear = new Datas\CreateAction('', array('Crear'));
    $this->eliminar =  new Datas\DeleteAction('',array('Eliminar'));
    $this->actualizar = new Datas\UpdateAction('', array('Actualizar'));
    $this->ver =  new Datas\ViewAction('',array('Ver'));
    $this->include = new Datas\HTMLInclude('Encabezado',array('Encabezado.html','Cuerpo.html','Pie.html'));
    $this->link0 = new Datas\SimplOnLink('', array('Crear Usuario'), 'Usuario', array(), 'showCreate');
    $this->link1 = new Datas\ElementLink('',array('Ingresar datos'),'showCreate');
    $this->link2 = new Datas\ElementLink('',array('Borrar Datos'),'showDelete');
    $this->link3 = new Datas\ElementLink('',array('Listar,Buscar y Actualizar Datos'),'showAdmin');
    $this->link4 = new Datas\ElementLink('',array('Ver ejemplo de HTMLInclude'),'verHTML');
    }
    
    function verHTML(){
	    echo $this->include();
    }
    function menu() {
        echo '--Bienvenido--'.'<br>'.'Pulse en el enlace la acci&oacute;n que desea realizar<br>'.$this->link0().'<br>'.$this->link1().'<br>'.$this->link2().'<br>'.$this->link3().'<br>'.$this->link4();
    }
    
    function menu2() {
        echo 'Menu Simple, Uso de Datas *Action<br />';
        echo '<br />'.$this->crear().'<br />';
        echo '<br />'.$this->eliminar().'<br />';
        echo '<br />'.$this->actualizar().'<br />';
        echo '<br />'.$this->ver().'<br />';
    }
    
    function contenedores() {
        $this->id = new Datas\NumericId('Id');
        
        
        
    }

}
?>
