<?php


/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

class SI_Table extends SI_Item {

    function __construct( $content = null, $extraColumns = [], $method = null, $rowTitle = null) {
        $this->content = $content;
        $this->method = $method;
        $this->rowTitle = $rowTitle;
        $this->extraColumns = $extraColumns;
        $this->addClass('SI_Table');
        $this->addStylesToAutoCSS("
            [data-theme=dark] {
                --table-background-color: #8d7e73;
                --odd-table-background-color: #323232;
            }

            [data-theme=light],
            :root:not([data-theme=dark]) {
                --table-background-color: #72818c;
                --odd-table-background-color: #cdcdcd;
            }

        

            .SI_Table > .rows > .header{
                text-align: center;
                background-color:var(--odd-background-color);
            }
            .SI_Table .row .title{
                text-align: right;
            }
            .SI_Table .row .title:after{
                content: ':';
            }
            .SI_Table .row div > a.Action {
                display: inline;
            }
            .IC_Acciones_SI_HContainer a{
                text-align: center;
            }

            @media (min-width:601px)  {
                .SI_Table>*>*{
                    display: grid;
                    grid-auto-columns: 1fr;
                    grid-auto-flow: column;
                }

                .SI_Table .row:nth-child(odd){
                    background-color:var(--odd-background-color);
                }

                .SI_Table .header > div, .SI_Table .row {
                    border: 0.05rem solid var(--table-background-color);
                }

                .SI_Table .row >* {
                    text-align: right;
                    padding: 0.1rem 0.3rem 0.1rem 0.1rem;
                }

                .SI_Table .title{
                    display: none;
                }
            }

            @media (max-width:600px)  {

                .SI_Table>.rows>.row>*:not(.header){
                    display: grid;
                    grid-template-columns:  3fr 7fr;
                }
                .SI_Table .rows > .header{
                    display: none;
                }
                .SI_Table .row{
                    border-radius: 0.3rem;
                    margin: 0.5rem 0.2rem;
                }
                .SI_Table .row, .SI_Table .row>*{
                    border: 0.05rem solid var(--table-background-color);
                }
                
                .SI_Table > .rows > .row > .header{
                    text-align: center;
                    background-color:var(--odd-background-color);
                }

                .SI_Table .row .title{
                    display: block;
                }
                .SI_Table .row>*>*{
                    margin: 0.1rem;
                }
            }
        ");
        static::$cssfiles['pico.min.css'] = './css/pico.min.css';
    }

    function html() {
        $this->setRenderVals();

        if(!$this->content){
            $ret = new SI_VContainer();
            $ret->addItem(new SI_Divider());
            $ret->addItem(new SI_Title(SC_Main::L('There are no results for this query'),5));
            $ret->addItem(new SI_Divider());
            return $ret;
        }

        $ret = "\n\n".'<div '.$this->attributesString().'>
                <div class="rows">'."\n";
        if($this->method){
            $ret .= $this->elementToHeader();
            foreach($this->content as $element){
                $ret .= $this->elementToRow($element);
            }
        }
        $ret .= "\n".'</div>'."\n".'</div>'."\n";

        return $ret;
    }

    function elementToRow($element){

        $ret = '<div class="row">'."\n";
        
        if($this->rowTitle){
            $ret .= '<div class="header">'.$element->{'O'.$this->rowTitle}()->val().'</div>'."\n";
        }
  
        if(in_array($this->method, SC_Main::$VCRSLMethods)){ $method = 'show'.ucfirst($this->method); }else { $method = $this->method; }
      
        if(is_array($this->extraColumns)){
            foreach($this->extraColumns as $key => $data){
                $element->addData($key,$data);
            }
        }
          
        foreach($element->datasWith($this->method) as $data){
            if($data != $this->rowTitle){
                $ret .= '<div class="colCell"><span class="title">'.$element->{'O'.$data}()->label().'</span><span class="val">'.$element->{'O'.$data}()->$method().'</span></div>'."\n";
            }
            // <div class="colCell"><span class="title">$colTitle</span><span class="val">$val</span></div>
        }
        $ret .= '</div>'."\n";

        return $ret;
    }
    
    function elementToHeader(){
        if(!$this->content){
            return '';
        } 
        $element = $this->content[0];

        if(is_array($this->extraColumns)){
            foreach($this->extraColumns as $key => $data){
                $element->addData($key,$data);
            }
        }

        $ret = '<div class="header">'."\n";
        if($this->rowTitle){
            //$ret .= '<div>'.$element->{'O'.$this->rowTitle}()->label().'</div>'."\n";
            $ret .= '<div></div>'."\n";
        }
        foreach($element->datasWith($this->method) as $data){
            if($data != $this->rowTitle){$ret .= '<div>'.$element->{'O'.$data}()->label().'</div>'."\n";}
        }
        $ret .= '</div>'."\n";

        return $ret;
    }
    
    function setRenderVals(){
        foreach($this as $atribute => $value){
            if(is_array($value) AND sizeof($value) == 2 AND  @$value[0] instanceof SC_BaseObject AND is_string( @$value[1] )){
                if($this->object instanceof SC_BaseObject){ $value[0] = $this->object; }
                $this->$atribute = $value();
            }
        }
    }

}