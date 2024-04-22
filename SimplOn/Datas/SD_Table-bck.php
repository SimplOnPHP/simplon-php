<?php


class SD_Table extends SD_ComplexData{

    protected
    $rows,
    $lastRowFuncs = array();

	public function __construct($label,$flags=null,$searchOp=null){
		// $this->sources is an array with items to be used for complex data
		parent::__construct($label,null,$flags,null,$searchOp);
	}

    public function showView($template = null, $sources = null)
    {
        $rows = $this->val();
        $lastRow = array();
        if(!empty($rows[0]) && is_array($rows[0])){
            $headers = array_keys($rows[0]);
            $ret = '<div>Movimientos:</div>';    
            $ret .= '<div class="table">';  
            $ret .= '<div class="tableHeader">';
            $ret .= wrap_implode($headers, '<div>','</div>');
            $ret .= '</div>';
            foreach($rows as $row){
                $ret .= '<div class="tableRow">';
                foreach($row as $key=>$val){
                    $ret .='<div class="cardHeader">'.$key.'</div><div>'.$val.'</div>';
                    if(!empty($this->lastRowFuncs[$key])){
                        $ColFunc = explode('-',$this->lastRowFuncs[$key]);
                        if($ColFunc[0] == 'str'){
                            $lastRow[$key]=$ColFunc[1];
                        }else if($ColFunc[0] == 'opt'){
                            if($ColFunc[1] == 'sum'){
                                $lastRow[$key]+=$val;
                            } else if($ColFunc[1] == 'avg'){
                                $lastRow[$key]=($lastRow[$key]*$lastRow[$key.'Count'])+$val;
                                $lastRow[$key.'Count']+=1;
                                $lastRow[$key]=$lastRow[$key]/$lastRow[$key.'Count'];
                            }  else if($ColFunc[1] == 'max'){
                                if($lastRow[$key]<$val){$lastRow[$key]=$val;}
                            }  else if($ColFunc[1] == 'min'){
                                if(floatval($lastRow[$key])>$val){$lastRow[$key]=$val;}
                            } 
                        }
                    }
                }
                $ret .= '</div>';
            }
            if($lastRow){  
                $ret .= '<div class="tableRow">';
                foreach($row as $key=>$val){
                    $ret .='<div class="cardHeader"></div><div>'.$lastRow[$key].'</div>';
                }
                $ret .= '</div>';
            }
            $ret .= '</div>';



            return $ret;
        }
    }
}