<?php



/**
 * [Description SC_Manager]
 */
class SC_Manager extends SC_BaseObject{

    public function appElements(Type $var = null)
    {
        global $app_root; 
        $files = scandir($app_root);
        $links = '';
        foreach($files as $file){
            if(strpos($file,'.php') && $file != 'index.php') { 

                $class = substr($file, 0, -4);  


                $rc = new \ReflectionClass($class);
                /** @var SC_Element $obj */
                $obj = $rc->newInstanceArgs();
                $redender = $GLOBALS['redender'];
                $links .= '<div><a target="vista" href="'.$redender->encodeURL($obj,array(),'showAdmin').'">'.$obj->getClassNameWords().'</a></div>';
                // $obj->addData('adminAction',new SD_AdminAction('', array($class)) ) ;

                // echo $obj->adminAction();
            } 
        }



        $ret = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Simplon Manager</title>
            <style>
                body{
                    display: grid;
                    grid-template-columns: 2fr 11fr ;
                    margin: 0;
                    padding: 0;
                }
                
                .vista iframe{
                    width: 99%; 
                    height: 99vh;
                }
            </style>
        </head>
        <body>
            <div class="links">'.$links.'</div>
            <div class="vista">
                <iframe name="vista">
            </div>       
        </body>
        </html>';
        return $ret;
    }

}