<?php

use voku\helper\HtmlDomParser;

class SI_Table extends SI_Container {

    protected
        $extraColumns,
        $defaultCellMethod = 'showList',
        $colTitle = 'datasName',            // datasName firstRow none TODO: firstRow
        $rowTitle = 'none';                 // col-???? firstCol none TODO: col-???? firstCol
           

    function __construct(array $items, array $extraColumns = null, $options = null ){

        if(is_array($options)){
            if (array_key_exists("defaultCellMethod", $options)) { $this->defaultCellMethod = $options['defaultCellMethod'];}
            if (array_key_exists("colTitle", $options)) { $this->colTitle = $options['colTitle'];}
            if (array_key_exists("rowTitle", $options)) { $this->rowTitle = $options['rowTitle'];}
        } 
        
        if( !empty($extraColumns) AND !empty($items) AND is_array($extraColumns) AND $items[0] instanceof SC_Element AND reset($extraColumns) instanceof SD_Data){
            foreach ($items as $item) {
                foreach ($extraColumns as $key => $extraColumn) {
                    $item->addData($key, clone $extraColumn);
                }
            }
        }

        $this->items = $items;
    }

    function makeLayout(){
        /** @var SR_main */
        $renderer = SC_Main::$RENDERER;
        $path = SC_Main::$RENDERER->layoutPath($this);
        if(file_exists($path)){

            $dom = HtmlDomParser::file_get_html($path);

            $renderer->getStylesAndScriptsLinks($dom);
            $renderer->addStylesTagsToAutoCSS($this,$dom);

            $containerDom = $dom->findOne('section > *');
            $containerDom->class = 'IC II IC_'.$this->name().'_'.$this->getClass().' '.$containerDom->class;

            return $containerDom;
        }else{
            throw new SC_LayoutValidationException('Layout not found: '.$path);
        }
    }

    function fillLayout( voku\helper\HtmlDomParser $dom){
  
        $tableRows = $dom->findOne('.SI_Table .rows');
        $headerRow = $dom->findOne('.rows .header');
        $headerRowCell = $dom->findOne('.rows .header > *')->outerHtml();
        $row = $dom->findOne('.rows .row');
        $rowHeaderCell = $row->findOne('.row .header');
        $colCell = $row->findOne('.row .colCell');
        $colTitleInCell = $colCell->findOne('.title')->outerHtml();
        $colValInCell = $colCell->findOne('.val')->outerHtml();


        $colValInCell = explode('$val', $colValInCell );
        $colTitleInCell = explode('$colTitle', $colTitleInCell );
        $headerRowCell = explode('$colTitle', $headerRowCell );

        $colCell->innerhtml = '$val';
        $colCell = explode('$val', $colCell->outerHtml() );  
        $rowHeaderCell->innerhtml = '$val';
        $rowHeaderCell = explode('$val', $rowHeaderCell->outerHtml() );
        $row->innerhtml = '$val';
        $row = explode('$val', $row->outerHtml() ); 
        $headerRow->innerhtml = '$val';
        $headerRow = explode('$val', $headerRow->outerHtml() );

        $headRow = '';

        if(!empty($this->items)){  
            if($this->colTitle != 'none'){
                $cellTitles = array();
                if($this->colTitle == 'datasName' && $this->items[0] instanceof SC_Element){
                    foreach($this->items[0]->datasWith('list','datas') as $data){
                        $headRow .= $headerRowCell[0].$data->label().$headerRowCell[1]; 
                        $cellTitles[] = $colTitleInCell[0].$data->label().$colTitleInCell[1];
                    }
                    $headerRow = $headerRow[0].$headRow.$headerRow[1]; 
                }
            }
            

            if($this->rowTitle != 'none'){
                // <div class="header">$rowTitle</div>
            }

            if($this->items[0] instanceof SC_Element){
                $rows = $headerRow;
                foreach($this->items as $item){;
                    $rowHTML='';
                    $i=0;
                    foreach($item->datasWith('list','datas') as $data){
                        $data->parent($item);
                        $rowHTML .= $colCell[0].$cellTitles[$i].$colValInCell[0].$data->{$this->defaultCellMethod}().$colValInCell[1].$colCell[1];
                        $i++;
                    }
                    $rowHTML= $row[0].$rowHTML.$row[1];
                    $rows .= $rowHTML;
                }
            }

            $tableRows->innerhtml = $rows;
        }else{
            $headRow = $headerRowCell[0].SC_MAIN::L('There are no elements to show').$headerRowCell[1]; 
            $headerRow = $headerRow[0].$headRow.$headerRow[1]; 

            $rowHTML = $colCell[0].$colTitleInCell[0].' &nbsp;'.$colTitleInCell[1].$colValInCell[0].SC_MAIN::L('There are no elements to show').$colValInCell[1].$colCell[1];
            $rowHTML= $row[0].$rowHTML.$row[1];
            $tableRows->innerhtml = $rowHTML;
        }

        return $dom;
    }       
    
}
