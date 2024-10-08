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
            $message = new SI_SystemMessage('Probando 1 2 3 probando...');


            $testLink1 = new SI_Link('Usuarios', $this->renderer->action('SE_User','showAdmin'));
            $testLink2 = new SI_Link('Roles', $this->renderer->action('SE_Role','showAdmin'));
            $testLink3 = new SI_Link('Comandas', $this->renderer->action('AE_Comanda','showAdmin'));
            $menu = new SI_SystemMenu([$testLink1,$testLink2,$testLink3]);
            $saludo = new SI_Text('Hola Mundo');

            
            $mensaje = new SI_SystemMessage();	
    
            $header = new SI_HContainer([$menu,$saludo,$mensaje],'showView');
            $header->style(' grid-template-columns: 80fr auto 2fr;');
        }
     
        $this->header = $header;
        $this->footer = $footer;

    }

}