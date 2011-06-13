<?php
namespace DOF\Elements;

/**
 *
 * @var array $columns	Collection of objects of type Data that represents the columns to print out.
 * @var DataBase $db	Database handler.
 *
 * @author Luca
 *
 */
class Collection extends Element
{
	private $elementsType;
	
	static protected $db;
	
	protected	$data,
				$columns,
				$elements,
				$prefix;
				
				
	function search($term = '', $limit = 0) {
		return print_r(func_get_args(), true);
	}
	
	/**
	 *
	 * @param $elementsType	Objetos tipo Elemento de catalogo que especifican que tipo de objetos que se listaran
	 * @param $columns
	 * @param $prefix
	 * @return unknown_type
	 */
	public function __construct($elementsType,$columns=null,$prefix=null)
	{
		global $db;
		
		if($prefix){ $this->prefix($prefix); }
		
		if(is_string($elementsType)){$elementsType=array($elementsType);}
		
		$this->elementsType=$elementsType;
		$this->columns=$columns;
		
		foreach($this->elementsType as $tipoDeElemento)
		{
			$this->elements[$tipoDeElemento]=new $tipoDeElemento();
		}

		if(!$this->columns)
		{

			//$this->columns[]
		
			//chek($this->elements);
			foreach($this->elements as $elemento)
			{
				foreach($elemento as $dato)
				{
					if($dato instanceof Dato)
					{
						/*@var $dato Dato */
						//chek(get_class($dato));
	
							$this->columns[]=$dato;
						
					}
				}
			}
			
		}
		
		if($db)
		{
			$this->db=&$db;
		}
		
			//	check($this->columns);
		
	}


	public function jssYcsss()
	{
		$libreriasJS=array('jquery.min.js','jquery.livequery.js','jquery.form.js','jquery-form-init.js');
		
		foreach($this->columns as $keydato=>$dato)
		{
			if($dato instanceof Dato)
			{
				//chek($dato->getlibreriaJS());
				//check($dato);
				$libreriasJS=array_merge( (array)$libreriasJS, (array)$dato->getlibreriaJS() );
				$hojasCSS=array_merge( (array)$hojasCSS, (array)$dato->gethojaCSS() );
			}
		}
		
		$libreriasJS=array_unique($libreriasJS);
		$hojasCSS=array_unique($hojasCSS);
		
		return array('libreriasJS'=>$libreriasJS,'hojasCSS'=>$hojasCSS);
	}
	
	
	public function llenaDesdeBD($where=null,$limit=null,$order='id DESC')
	{

		if( !$where && !$limit  ){ $limit=LIMITE_BUSQUEDAS; }else if($where){$limit=200;}

		$this->data=$this->db->queryArreglo($this->cadenaSelectQuery($where,$limit,$order));
		
		//check($this->data);
		//chek($this->cadenaSelectQuery($where,$limit,$order));
	}
	
	public function cadenaSelectQuery($where=null,$limit=null,$order='id DESC')
	{
		$ret="SELECT ".$this->cadenacolumnsSelect()." FROM ".$this->cadenaTablasQuery().(($where)?" WHERE ".$where:"").(($order)?" ORDER BY ".$order:"").(($limit)?" limit ".$limit:"");
		//check($ret);
		return $ret;
	}

	public function cadenacolumnsSelect()
	{
		//check( $this->columns );
		
		foreach($this->columns as $columna)
		{
			/*@var $columna Dato */
			//check( $columna->getClass().' -- '.$columna->cadenaColumnaQuery() );
			if($columna->cadenaColumnaQuery()){ $ret[]=$columna->cadenaColumnaQuery(); }
		}
		return implode(', ',$ret);
	}

	public function cadenaTablasQuery()
	{
		foreach($this->elements as $elemento)
		{
			//chek($elemento->gettabla());
			$ret[]=$elemento->tabla();
		}
		return implode(', ',$ret);
	}
	
	
  function cadenaHTML($class=null, $width='100%',$header=1)
  {
	//chek($this->columns);
  	//chek($this->data);
  	
  	if($class){ $class='class="'.$class.'" ';}
	if($width){ $width='width="'.$width.'" ';}
	
	
  	if( $this->prefix() ){ $ret.="<div class='resultados".$this->prefix()."'>"; }
  	$ret.="<table $class $width >";
	if($header==1)
	{
		$ret.='<tr>';
		foreach($this->columns as $dato)
		{
			$ret.='<th>'.$dato->cadenaLabel().'</th>';
		}
		$ret.='</tr>';
	}
	
  	foreach($this->data as &$row)
	{
		$ret.='<tr>';
		foreach($this->columns as $columna)
	  	{
	  		/*@var $columna Dato*/
	  		$ret.='<td class="'.$columna->getcssClass().'">';
				//chek($row);
	  			$columna->llenaDesdeArregloBD($row);
				$ret.= $columna->cadenaHTMLTabla();
				//$ret.='a<br />a<br />a<br />a<br />a<br />a<br />a<br />a<br />a<br />a<br />a<br />a<br />a<br />a<br />a<br />a<br />a<br />xxxa<br />';
	  		$ret.='</td>';
	  	}
	  	$ret.='</tr>';
	}
	$ret.='</table>';
	
  	if( $this->prefix() ){ $ret.="</div>"; }
	
	return $ret;
  }

	
  function imprimeHTMLGrid($id, $width, $height=150,$rowtitle=null)
  {
	?>
	<script>
		$(document).ready(
		
			function ()
			{
				<?php
				$this->imprimeHTMLGridStartUP($id);
				?>
 			}
		);
	</script>
	<div id="<?= $id; ?>" style="width:<?= $width; ?>"><?php
		?><table style="border: 0px solid #ccc; background-color:#cfd1b6" cellpadding="0" cellspacing="0" width="100%"><!-- BORDE DEL header --><?php
			?><thead><?php
			?><tr><?php
			foreach($this->campossalida as $camposalida)
		  	{
		  		/* @var $camposalida camposalida */
		  		?><th <?php if($camposalida->ancho){ ?> style="width:<?= $camposalida->ancho; ?>px; max-width:<?= $camposalida->ancho; ?>px; height:16px; padding-bottom:3px" width="<?= $camposalida->ancho; ?>" <?php } ?>><?php
				
		  		echo $camposalida->nombre;
		  		?></th><?php
		  	}
		  	?><td style="width:16px; color:white" width="16">&nbsp;</td>
            </tr><?php
	  	?></thead><?php
	 ?></table><?php
	 
	 ?><div style="overflow:scroll;  margin-right:16px; height:<?= $height; ?>px; overflow-y:scroll; overflow-x: hidden;"><?php
	 ?><table style="border: 0px solid #ccc;" cellspacing="0" width="100%"><!-- BORDE DE LA TABLA --><?php
	  	?><?php

		  		foreach($this->tabla as &$row)
				{
			  		
					?><tr <?php if($rowtitle) { ?>id="<?= $row['id'] ?>" title="<?= $rowtitle ?>"<?php }  ?>><?php
					foreach($this->campossalida as $camposalida)
				  	{
				  		?><td <?php if($camposalida->ancho){ ?> style="word-wrap:break-word; max-width:<?= $camposalida->ancho; ?>px" width="<?= $camposalida->ancho; ?>" <?php } ?> ><?php
						/* @var $camposalida camposalida */
				  		$camposalida->imprimeHTML($row);
				  		//chek($row);
				  		?></td><?php
				  	}
		
				  	?></tr><?php
				}
				

		?><?php
	?></table><?php
	?></div><?php
	
	?>
	<div style="display: none">
	<table style="border: 1px solid #ccc;" cellspacing="0" width="100%">
    <tfoot>
		<tr>
			<td colspan=7>
				<div class="pagination"></div>
				<div class="paginationTitle">Page</div>

				<div class="selectPerPage"></div>
				<div class="status"></div>
			</td>
		</tr>
	</tfoot>
	</table>
	</div>
	
	</div>
	<?php
  }

  function imprimeHTMLGridStartUP($id)
  {
	$renglones = sizeof( $this->tabla );
  	echo "
  	$('#$id').jTPS( {perPages:[$renglones],scrollStep:1,scrollDelay:30} );
	// bind mouseover for each tbody row and change cell (td) hover style
	$('#$id tbody tr:not(.stubCell)').bind('mouseover mouseout',
		function (e)
		{
			// hilight the row
			e.type == 'mouseover' ? $(this).children('td').addClass('hilightRow') : $(this).children('td').removeClass('hilightRow');
		}
	);
	";
  }

	public function cadenaHeader()
	{
		foreach($this->columns as $dato)
		{
			if($dato instanceof Dato)
			{
				//chek($dato->getlibreriaJS());
				//check($dato);
				$libreriasJS=array_merge( (array)$libreriasJS, (array)$dato->getlibreriaJS() );
				$hojasCSS=array_merge( (array)$hojasCSS, (array)$dato->gethojaCSS() );
			}
		}
		$libreriasJS=array_unique($libreriasJS);
		$hojasCSS=array_unique($hojasCSS);
		
		//chek($libreriasJS);
		
		
		$ret='<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>'."\n";
		
		foreach($libreriasJS as $libreriaJS)
		{
			$ret.='<script language="JavaScript" type="text/javascript" src="'.URL_PRIVADA.'/js/'.$libreriaJS.'"></script>'."\n";
		}
		
		foreach($hojasCSS as $hojaCSS)
		{
			$ret.='<link type="text/css" href="'.URL_PRIVADA.'/css/'.$hojaCSS.'" rel="stylesheet" />'."\n";
		}
		
		
		
		return $ret;
	}
  
  
  
  
	
}