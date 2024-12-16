<?php


class SI_FullPage extends SI_Page {

    protected
        $content,
        $title,
        $header,
        $footer;

    function __construct($content, $title = null, $header = null, $footer = null) {

        parent::__construct($content,$title);

        if(empty($header)){

            if(SC_Main::$PERMISSIONS){
                $menu = SC_Main::$PERMISSIONS->menu(); 
                $saludo = new SI_Text('Hola '.SC_Main::$PERMISSIONS->userName() );
            }else{
                $UsuariosLink = new SI_Link('Usuarios', $this->renderer->action('SE_User','showAdmin'));
               // $RolesLink = new SI_Link('Roles', $this->renderer->action('SE_Role','showAdmin'));
                $menu = new SI_SystemMenu([$UsuariosLink]);
                $saludo = new SI_Text('');
            }
            
            $message = new SI_SystemMessage();	
    
            $header = new SI_HContainer([$menu,$saludo,$message],'showView');
            $header->style(' grid-template-columns: 80fr auto 2fr;');
        }
     
        $this->header = $header;
        $this->footer = $footer;

    }

}