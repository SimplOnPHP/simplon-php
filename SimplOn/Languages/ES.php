<?php

define('Lang', [
    'Admin'=>'Administrar',
    'Update'=>'Actualizar',
    'Create'=>'Crear',
    'Search'=>'Buscar',
    'View'=>'Vista',
    'Delete'=>'Borrar',
    'Cancel'=>'Cancelar',
    'Actions'=>'Acciones',
    
    'Can\'t create'=>'No se puede crear ',
    'in the data storage'=>'en el almacen de datos',
    'This field must be an integer number.'=>'Este campo debe ser un número entero.',
    'There are no elements to show'=>'No hay elementos para mostrar'
]);

function L($key) {
    return Lang[$key] ?? $key;
}