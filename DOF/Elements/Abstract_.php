<?php
namespace DOF\Elements;

/**
 * This is the core element to build the site. DOF (Data Oriented FrameWork) is based on data representation, stoarge and manipulation.
 * Elements are the way to indicate the system all data that conforms it. Each Element represents a data set.
 *
 * In practical terms Elements are just Objets with extended capabilities to handdle some comon tasks like:
 * Print their contets, Store their contents, find and retrive the proper data froma a dataStorage, etc.
 *
 * Elemnts are programed and used like any other regular object except
 * that in orther to make their special features work some of their attributes must be DOF data objects.
 *
 * @author RSL
 */
class Abstract_ extends \DOF\BaseObject {
	protected $dir;
	protected $repository;
	protected $tempFormPrefix;

	protected $externalJS;
	protected $externalCSS;
	protected $internalJS;
	protected $internalCSS;

	/*@var dataStorage DataStorage*/
	protected $dataStorage;
	protected $autoDSRepositoryGeneration = true;
	
	/**
	* Costructor.
	*
	* Meant to be added at the end of all heir's constructors width:
	*
	* parent::__construct($id=null);
	*
	*
	* beacuse it perfroms DOFdata dependant methods but that are common to all DOFelements
	*
	* @param int $id
	*/
	public function __construct($id=null,&$specialDataStorage=null)
	{
		//On heirs put here the asignation of DOFdata and attributes
		
		//Asings the storage element for the DOFelement. (a global one : or a particular one)
		if(!$specialDataStorage){
			$this->dataStorage = \DOF\Main::$DATA_STORAGE;
		}else{
			$this->dataStorage=&$specialDataStorage;
		}
		
		//checking if there is already a dataStorage and repository for this element
		if($this->autoDSRepositoryGeneration) {
			$this->dataStorage->ensureElementRepository($this);
		}
		
		//if there is a storage and an ID it fills the element with the proper info.
		if($id) {
			$this->id($id);
			
			//check($this->id());
			
			$this->fillFromDSById($id);
			
			//check($this->id());
		}

		// Tells the DOFdata whose thier "container" in case any of it has context dependent info or functions.
		$this->asignAsDataParent($this);
	}

	public function fillFromDSById($id=null)
	{
		if(isset($id)){$this->id($id);}
		
		if( $this->id() ){
			/*@var $this->dataStorage DataStorage*/
			$dataArray = $this->dataStorage->getElementData( $this );
			
			//check($dataArray);
			
			$this->fillFromArray($dataArray);
		}else{
			throw new Exception('The object of class: '.$this->getClass()." has no id so it can't be filled using method fillElementById" );
		}
	}
	
	public function fillFromArray(&$array)
	{
		foreach($this as $data)
		{
			if($data instanceof Data )
			{
				$data->fillFromArray($array);
			}
		}
	}
	
	/**
	 *
	 * NOTE: This method is not a simple redirection to $this->fillFromArray($_REQUEST) because the file upload requeires the $_FILES array
	 * Thus the redirection from fillFromRequest to fillFromArray is made at the DOFData and there for any DOFData that needs to
	 * distinguish between both can do it.
	 *
	 */
	public function fillFromRequest()
	{
		foreach($this as $data)
		{
			if($data instanceof Data )
			{
				$data->fillFromRequest();
			}
		}
		
		return $this;
	}
	
	public function saveInDS()
	{
		/*@var $this->dataStorage DataStorage*/
		$this->dataStorage->saveElement($this);
	}
	
	public function createInDS()
	{
		/*@var $this->dataStorage DataStorage*/
		$this->dataStorage->createElement($this);
	}
		
	public function updateInDS()
	{
		/*@var $this->dataStorage DataStorage*/
		$this->dataStorage->updateElement($this);
	}

	public function deleteFromDS()
	{
		/*@var $this->dataStorage DataStorage*/
		$this->dataStorage->deleteElement($this);
	}
	
	public function toHtml($template=null)
	{
		//if no template is received
		if(!$template){
			//if the default class template exits use it
			if(file_exists(TEMPLATES_ROOT.'layout/'.$this->getClass()))
			{
				$template = file_get_html($this->getClass());
				
			}else{ //if not generate it in RAM
				$template="<div class='".$this->getClass()."'>"."\n";
				
				foreach($this as $keydata=>$data)
				{
					if($data instanceof Data && $data->view() )
					{
						$template.="\t".'<div class="D-'.$keydata.'">';
						$template.="\t"."\t".$data->HTML();
						$template.="\t".'</div>'."\n";
					}
				}
				$template.="</div>";
				
				//if enabeled write the template into a file.
				if(CREATE_LAYOUT_TEMPLATES)
				{
					$file = fopen (TEMPLATES_ROOT.'layout/'.$this->getClass(), "w");
					fwrite($file, $template);
					fclose ($file);
				}
			}
		}
		
		//if the template is a string
		if(is_string($template)){
			//try to opene it as a file
			if( file_exists(TEMPLATES_ROOT.'layout/'.$template) )
			{
				$template = file_get_html($template);
			}else{ //if it's not a file open it as a HTML string
				$template = str_get_html($template);
			}
		}
		
		//if the template is a simple dom object proceed to process it.
		if($template instanceof simple_html_dom_node)
		{
			$this->processTemplate($template);
		}else{ // if not sound the alarm
			throw new Exception("<br />".$this->getClass()." received the invalid template: <br>".$template);
			return;
		}
	}

	
	
	
	
	//@todo verify and implement or remove the use of mesaje
	public function formElementGenerator($formType=null, $action=null, $method='post')
	{
		global $prefix;
		
		if(!$prefix){$prefix=1;}
		$prePrefix=$prefix;
		$name=$prefix.$this->getClass();
		
		if(!$formType){
			if($this->id()) {$formType='update';}
			else  {$formType='create';}
		}
		
		$formImput = $formType.'Input';
		
		//check($formImput);
		
		if(!$action){ $action = $adminUrl.'Ajax/process'.ucfirst($formType).'Form';}
		
		//--------------
		
		
		
		/*@var $data Data */
		$enctype = '';
		foreach($this as $keydata=>$data) {
			//echo $keydata ;
		
			if($data instanceof Data && $data->$formType()  )
			{
				$ret.='<div class="L'.$keydata.'">'.$data->label().'</div>';
				$ret.='<div class="I'.$keydata.'">'.$data->$formImput().'</div>'."\n\r";
			
				if($data instanceof File){ $enctype='enctype="multipart/form-data"'; }
			}
		}
		
		
		$ret="\n\r<form action='$action' $enctype  method='$method'>".$ret;
		
		$ret.="
		<input name='class' value='".$this->getClass()."' type='hidden' />
		<input name='prefijo' value='".$prefix."' type='hidden' />
		<input class='button' name='saveButton' value='Enviar' type='submit' />";
		$ret.="</form>\n\r";
		
		return $ret;
	}
	
	
	public function updateForm ( $template=null, $mesaje=null,$action=null,$method='post')
	{
		//check($action);
		return $this->formGetter('update', true, $template, $mesaje,$action,$method);
	}
	
	
	//@todo verify and implement or remove the use of mesaje
	public function formGetter($formType, $reloadInputs=true, $template=null, $mesaje=null,$action=null,$method='post')
	{
		//$formType = 'update';
		
		global $prefix;
		
		if(!$prefix){$prefix=1;}
		
		$prePrefix=$prefix;

		//make shure $template has a proper file name
		if( !is_string($template) ){ $template = $this->getTemplatePath( 'forms/'.$this->getClass().ucfirst($formType) ); }
		else { $template = $this->getTemplatePath( 'forms/'.trim($template) ); }

		//if there is not template or it must be redone redo it.
		if( (CREATE_FORM_TEMPLATES AND !file_exists($template) ) ){
			file_put_contents($template, $this->formElementGenerator($formType, $action, $method) );
		}else if( OVERWRITE_FORM_TEMPLATES ){
			unlink($template);
			file_put_contents($template, $this->formElementGenerator($formType, $action, $method) );
		}
		
		//if templates must be used and there is a template.
		if(USE_FORM_TEMPLATES && file_exists($template)){
			$form = file_get_html($template);
			
			//form acction
			if($action){$form->find('form', 0)->action = $action;}

			//form values
			if($reloadInputs){
				foreach($form->find('class^=I') as $element)
				{
					$keydata = substr($element->class(), 1);
					$formImput = $formType.'Input';
					
					$element->outertext = $this->$keydata()->$formImput();
				}
			}
			
			$form = $form->save();
			
		}else{ // else create the default template to be used
			$form = $this->formElementGenerator($formType, $action, $method);
		}
		
	
	//FormWarper
		$prefix=$prePrefix;
		if($prefix!=1){ $floatBox=' floatBox'; }
		$ret.="<div id='".$this->getClass().$this->id()."' class='Cambio$prefix $floatBox'>";
		if($prefix!=1)
		{
			$ret.="<span class='linkPointer cancelar' onclick=\"quita('#".$this->getClass().$this->id()."')\"><img src='./imgs/borrar.png' class='over'/></span>";
		}
		$ret.="<div class='cabezaFormulario'>Cambia ".$this->getClass()."</div>";
		$ret.=$form;
		$ret.="</div>";
	//FormWarper

	
		return $ret;
	}
	
	public function createForm($template=null, $mensaje=null,$action=null,$name='forma1',$method='post')
	{
		return $this->updateForm($template,$mensaje,$action,false,$name,$method);
	}

	//@todo verify and implement or remove the use of mesaje
	public function filterForm($template=null, $mesaje=null,$action=null,$printval=true, $name='forma1',$method='post')
	{
		global $prefix;
		
		if(!$prefix){$prefix=1;}
		
		$prePrefix=$prefix;
			
		$name=$prefix.$name;
			
		if(!$action && $printval){ //Chage
			$action = $adminUrl.'Ajax/pfagrega.php';
		}else if(!$action &&  !$printval){//Add
			$action= $adminUrl.'Ajax/pfNuevaAgrega.php';
		}

		//if no template is recived
		if(!$template OR $template==$this->getClass().'.php'){
			//if the default class template exits use it

			$file = file_exists( TEMPLATES_ROOT.'forms/'.$this->getClass().'.php' ) ;
			if($file)
			{
				$template = file_get_html($this->getClass());
			}else{ //if not generate it in RAM
				$template="<div class='".$this->getClass()."'>"."\n";

				$template.="<form action='".$action."' name='".$name."' method='".$method."' ".$multipart." >";
		
				/*@var $data Data */
				foreach($this as $keydata=>$data)
				{
					if($data instanceof Data && $data->update()  )
					{
						$template.="\t".'<div class="I'.$keydata.'">';
						if($printval && $data->updateInput()){
							$template.="\t"."\t".$data->label().$data->updateInput();
						}else if($data->createInput()){
							$template.="\t"."\t".$data->label().$data->createInput();
						}
						$template.="\t".'</div>'."\n";
					}
				}

				$template.="
			
				<input name='class' value='".$this->getClass()."' type='hidden' />
				<input name='prefijo' value=':**:variable:*:prefijo:**:' type='hidden' />
				<input class='button' name=':**:variable:*:prefijo:**:BotonGuardar' value='Guardar' type='submit' />
				<div class='clear'></div>";
				
				
				$template.="</form>";
				$template.="</div>";
				
				//if enabeled write the template into a file.
				if(CREATE_LAYOUT_TEMPLATES)
				{
					$file = fopen ($file, "w");
					fwrite($file, $template);
					fclose ($file);
				}
				
				$prefix=$prePrefix;
				
				//to avoid to have to use processTemplate for just the prefix
				$template = str_replace(':**:variable:*:prefijo:**:',$prefix,$template);
				
			}
		}else if(is_string($template)){ //if the template is a string
			//try to opene it as a file
			if( file_exists($file) )
			{
				$template = file_get_html($file);
			}else{ //if it's not a file open it as a HTML string
				$template = str_get_html($template);
			}
		}
		
		//check($template);
		
		//if the template is a simple dom object proceed to process it.
		if($template instanceof simple_html_dom_node)
		{
			$template = $this->processTemplate($template); //Note: this function may return a proper result with out runing this line -see return above.
		}
		
		$prefix=$prePrefix;
		
		if($prefix!=1){ $floatBox=' floatBox'; }
		
		$ret.="<div id='".$this->getClass().$this->id()."' class='Cambio$prefix $floatBox'>";
		
		if($prefix!=1)
		{
			$ret.="<span class='linkPointer cancelar' onclick=\"quita('#".$this->getClass().$this->id()."')\"><img src='./imgs/borrar.png' class='over'/></span>";
		}
	
		$ret.="<div class='cabezaFormulario'>Cambia ".$this->getClass()."</div>";
		
		$ret.=$template;
		
		$ret.="</div>";
		
		return $ret;
	}

	
	
	
	/**
	* Tells the DOFdata whose thier "container" in case any of it has context dependent info or functions.
	*
	* @param &$dataParent Reference to the logical data parent.
	*/
	public function asignAsDataParent(&$dataParent=null)
	{
		foreach($this as $data)
		{
			if($data instanceof Data)
			{
				if( $data->hasMethod('dataParent')  )
				{
					$data->dataParent($dataParent);
				}
			}
		}
	}


	public function getDOFDataAttributeKeys()
	{
		return $this->getAttributeKeys('Data');
	}
	
	
    public function __call($name, $arguments)
    {
        
    	if($this->$name instanceof Data )
        {
        	if($arguments){ $this->$name->val($arguments[0]); return; }
        	else{ return $this->$name->val(); }
        	
        }else{
        	
        	$letter=substr($name,0,1);
        	$Xname=substr($name,1);
        	
			if(isset($this->$Xname) && ($this->$Xname instanceof Data)) {
				switch($letter) {
					case 'O': 
		   				if($arguments){ $this->$Xname->val($arguments[0]); }
			        	else{ return $this->$Xname; }
						break;
					case 'F':
						if($arguments){ $this->$Xname->val($arguments[0]); }
        				else{ return $this->$Xname->field(); }
						break;
					case 'L':
						if($arguments){ $this->$Xname->val($arguments[0]); }
	        			else{ return $this->$Xname->label(); }
						break;
					default:
						throw new \Exception('Letter '.$letter.' not recognized!');
				}
			} else{
        		return parent::__call($name, $arguments);
        	}
        }
    }
	 
    
/**
 *
 */
function getTemplatePath($template) {
	global $adminPath;
	global $siteTemplatePath;
	global $dofPath;

	if( file_exists($adminPath.'templates/'.$template.'.html') ){  return $adminPath.'templates/'.$template.'.html'; }else
	if( file_exists($siteTemplatePath.'templates/'.$template.'.html') ){  return $siteTemplatePath.'templates/'.$template.'.html'; }else
	if( file_exists($dofPath.'templates/'.$template.'.html') ){  return $dofPath.'templates/'.$template.'.html'; }
	else { 	return $adminPath.'templates/'.$template.'.html'; }
}
    
    
    
/*@todo determina if this method is neceary or not
 updateInDS // este debe ser automatico desde el save si se tiene id se genera
*/
	
}




?>