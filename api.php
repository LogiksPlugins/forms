<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("findForm")) {

	function findForm($file) {
		$fsArr=[
				$file,
				APPROOT.APPS_MISC_FOLDER."forms/{$file}.json",
			];
		$file=false;
		foreach ($fsArr as $fs) {
			if(file_exists($fs)) {
				$file=$fs;
				break;
			}
		}
		if(!file_exists($file)) {
			return false;
		}

		$formConfig=json_decode(file_get_contents($file),true);

		$formConfig['sourcefile']=$file;
		if(isset($formConfig['singleton']) && $formConfig['singleton']) {
			$formConfig['formkey']=md5(session_id().$file);
		} else {
			$formConfig['formkey']=md5(session_id().time().$file);
		}

		return $formConfig;
	}

	function printForm($mode,$formConfig,$dbKey="app",$whereCondition=false) {
		if(!is_array($formConfig)) $formConfig=findForm($formConfig);

		if(!is_array($formConfig) || count($formConfig)<=2) {
			trigger_logikserror("Corrupt form defination");
			return false;
		}
		
		if(!isset($formConfig['formkey'])) $formConfig['formkey']=md5(rand().time());
		
		$formConfig['formcode']=md5($_SESSION['SESS_USER_ID'].$formConfig['sourcefile']);
		$formConfig['formuid']=md5($formConfig['sourcefile']);

		$formConfig['dbkey']=$dbKey;

		if(!isset($formConfig['template'])) {
			$formConfig['template']="tabbed";
		}

		$fieldGroups=[];
		foreach ($formConfig['fields'] as $fieldKey => $fieldset) {
			if(!isset($fieldset['label'])) $fieldset['label']=_ling($fieldKey);
			if(!isset($fieldset['width'])) $fieldset['width']=6;
			if(!isset($fieldset['group'])) $fieldset['group']="default";

			$fieldset['fieldkey']=$fieldKey;

			if(!isset($fieldGroups[$fieldset['group']])) $fieldGroups[$fieldset['group']]=[];

			$formConfig['fields'][$fieldKey]=$fieldset;
			$fieldGroups[$fieldset['group']][]=$fieldset;
		}

		if(!isset($formConfig['actions'])) {
			switch ($mode) {
				case 'update':
				case 'edit':
					$formConfig['actions']=[
							"update"=>[
								"type"=>"submit",
								"label"=>"Update",
								"icon"=>"<i class='fa fa-save form-icon right'></i>"
							]
						];
					break;
				
				case 'insert':
				case 'new':	
				default:
					$formConfig['actions']=[
							"submit"=>[
								"type"=>"submit",
								"label"=>"Submit",
								"icon"=>"<i class='fa fa-save form-icon right'></i>"
							]
						];
					break;
			}
		}

		$formData=[];
		if($mode=="update") {
			$source=$formConfig['source'];
			switch ($source['type']) {
				case 'sql':
					$sql=_db($dbKey)->_selectQ($source['table'],array_keys($formConfig['fields']),$whereCondition);
					$data=$sql->_get();
					
					if(isset($data[0])) {
						$formData=$data[0];
					} else {
						$formData=[];
					}
				break;
				case 'php':
					$file=APPROOT.$source['file'];
					if(file_exists($file) && is_file($file)) {
						$formData=include_once($file);
					} else {
						trigger_error("Form Data Source File Not Found");
					}
				break;
			}
		}

		$formConfig['data']=$formData;

		$formConfig['mode']=$mode;
		
		$formKey=$formConfig['formkey'];
		$_SESSION['FORM'][$formKey]=$formConfig;

		//printArray($formData);return;

		//Loading Form Template
		$templateArr=[
				$formConfig['template'],
				__DIR__."/templates/{$formConfig['template']}.php"
			];

		foreach ($templateArr as $f) {
			if(file_exists($f) && is_file($f)) {
				if(isset($formConfig['preload'])) {
					if(isset($formConfig['preload']['modules'])) {
						loadModules($formConfig['preload']['modules']);
					}
					if(isset($formConfig['preload']['api'])) {
						foreach ($formConfig['preload']['api'] as $apiModule) {
							loadModuleLib($apiModule,'api');
						}
					}
					if(isset($formConfig['preload']['helpers'])) {
						loadHelpers($formConfig['preload']['helpers']);
					}
				}
				echo _css('forms');
				include $f;
				echo _js(array('jquery.validate','forms'));
				return true;
			}
		}
		trigger_logikserror("Form Template Not Found",null,404);
	}

	function getFormActions($formActions=[]) {
		$html="";
		foreach ($formActions as $key => $button) {
			if(!isset($button['class'])) $button['class']="btn btn-primary btn-lg";
			if(isset($button['label'])) $label=$button['label'];
			else $label=toTitle(_ling($key));

			if(isset($button['icon']))  $icon=$button['icon'];
			else $icon="";

			if(!isset($button['type'])) $button['type']="button";

			$html.="<button type='{$button['type']}' cmd='{$key}' class='{$button['class']}'>{$icon}{$label}</button>";
		}
		return $html;
	}
	function getFormFieldset($fields,$data=[],$dbKey="app") {
		if(!is_array($fields)) return false;
		//printArray($fields);

		$html="<fieldset>";
		foreach ($fields as $field) {
			if(!isset($field['fieldkey'])) {
				continue;
			}
			
			if(!isset($field['label'])) {
				$fieldKey=$field['fieldkey'];
				$field['label']=_ling($fieldKey);
			}
			if(!isset($field['width'])) $field['width']=6;
			
			$html.="<div class='col-sm-{$field['width']} col-lg-{$field['width']}'>";
			$html.="<div class='form-group'>";
			if(isset($field['required']) && $field['required']==true) {
				$html.="<label>{$field['label']} <span class='span-required'>*</span></label>";
			} else {
				$html.="<label>{$field['label']}</label>";
			}
			$html.=getFormField($field,$data,$dbKey);
			$html.="</div>";
			$html.="</div>";
		}
		$html.="</fieldset>";

		return $html;
	}

	function getFormField($fieldinfo,$data,$dbKey="app") {
		$formKey=$fieldinfo['fieldkey'];
		if(!isset($data[$formKey])) $data[$formKey]="";

		if(!isset($fieldinfo['type'])) $fieldinfo['type']="text";
		if(!isset($fieldinfo['label'])) $fieldinfo['label']=_ling($formKey);
		if(!isset($fieldinfo['placeholder'])) $fieldinfo['placeholder']="";

		$html="";

		$class="form-control field-{$formKey}";
		$xtraAttributes=[];

		if(isset($fieldinfo['class']) && strlen($fieldinfo['class'])>0) {
			$class.=" ".$fieldinfo['class'];
		}
		if(isset($fieldinfo['disabled']) && $fieldinfo['disabled']==true) {
			$xtraAttributes[]="disabled";
		}
		if(isset($fieldinfo['readonly']) && $fieldinfo['readonly']==true) {
			$xtraAttributes[]="readonly";
		}
		if(isset($fieldinfo['required']) && $fieldinfo['required']==true) {
			$class.=" required";
			$xtraAttributes[]="required";
		}
		if(isset($fieldinfo['multiple']) && $fieldinfo['multiple']==true) {
			$class.=" multiple";
			$xtraAttributes[]="multiple";
		}

		if(!isset($fieldinfo['no-option'])) {
			$fieldinfo['no-option']="No $formKey";
		}
		$noOption=_ling($fieldinfo['no-option']);

		$xtraAttributes=trim(implode(" ", $xtraAttributes));
		switch ($fieldinfo['type']) {
			case 'dataMethod': case 'dataSelector': case 'dataSelectorFromUniques': case 'dataSelectorFromTable':
			case 'select': case 'selectAJAX': 
				if(!isset($fieldinfo['options'])) $fieldinfo['options']=[];

				$html.="<select class='{$class} {$fieldinfo['type']}' $xtraAttributes name='{$formKey}' data-value=\"".$data[$formKey]."\" data-selected=\"".$data[$formKey]."\">";
				
				if(!array_key_exists("", $fieldinfo['options']) || $fieldinfo['options']['']===true) {
					$html.="<option value=''>{$noOption}</option>";
				}

				$html.=generateSelectOptions($fieldinfo,$data[$formKey],$dbKey);

				$html.="</select>";
				break;

			case 'textarea':
				$html.="<textarea class='{$class}' $xtraAttributes name='{$formKey}' placeholder='{$fieldinfo['placeholder']}'>".$data[$formKey]."</textarea>";
				break;
			
			case 'color': case 'radio': case 'checkbox': 
				$html.="<input class='{$class}' $xtraAttributes name='{$formKey}' value=\"".$data[$formKey]."\" placeholder='{$fieldinfo['placeholder']}' type='{$fieldinfo['type']}'>";
				break;

			case 'date': case 'datetime': case 'datetime-local': case 'month': case 'time': case 'week': 
			case 'currency': case 'number': case 'range': 
			case 'email': case 'tel': case 'url': 
			case 'text': case 'password':
			
				$html.="<input class='{$class}' $xtraAttributes name='{$formKey}' value=\"".$data[$formKey]."\" placeholder='{$fieldinfo['placeholder']}' type='{$fieldinfo['type']}'>";
				break;

			case 'file':
				$html.="<input class='{$class}' $xtraAttributes name='{$formKey}' placeholder='{$fieldinfo['placeholder']}' type='file'>";
				break;

			default:

				$html.="<input class='{$class}' $xtraAttributes name='{$formKey}' value=\"".$data[$formKey]."\" placeholder='{$fieldinfo['placeholder']}' type='text'>";
				break;
		}
		

		return $html;
	}
}
?>