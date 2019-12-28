<?php
if(!defined('ROOT')) exit('No direct script access allowed');

include_once __DIR__."/validation.php";

if(!function_exists("findForm")) {
	define("FORM_CSS",'bootstrap.datetimepicker,forms');
	define("FORM_JS",'jquery.validate,moment,bootstrap.datetimepicker,forms');

	$_SESSION['SESS_CURRENT_YEAR'] = date("Y");
	$_SESSION['SESS_CURRENT_MONTH'] = date("m");
	$_SESSION['SESS_CURRENT_MONTH_NAME'] = date("M");
	$_SESSION['SESS_CURRENT_DATE'] = date("Y-m-d");
	$_SESSION['SESS_CURRENT_DAY'] = date("D");

	function findForm($file) {
		$fileName=$file;
		if(!file_exists($file)) {
			$file=str_replace(".","/",$file);
		}

		$fsArr=[
				$file,
				APPROOT.APPS_MISC_FOLDER."forms/{$file}.json",
			];
		if(isset($_REQUEST['forSite']) && defined("CMS_SITENAME")) {
			$fsArr[]=ROOT."apps/".CMS_SITENAME."/".APPS_MISC_FOLDER."forms/{$file}.json";
		}
		
		$fArr = explode("/",$file);
		if(count($fArr)>1) {
			$fPath = checkModule($fArr[0]);
			if($fPath) {
				unset($fArr[0]);
				$fsArr[] = dirname($fPath)."/forms/".implode("/",$fArr).".json";
			}
		}
		
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
		$formConfig['srckey']=$fileName;
		if(!isset($formConfig['dbkey'])) $formConfig['dbkey']="app";

		return $formConfig;
	}

	function printForm($mode,$formConfig,$dbKey="app",$whereCondition=false,$params=[]) {
		if(!is_array($formConfig)) $formConfig=findForm($formConfig);

		if(!is_array($formConfig) || count($formConfig)<=2) {
			trigger_logikserror("Corrupt form defination");
			return false;
		}

		if(isset($formConfig['policy']) && strlen($formConfig['policy'])>0) {
	      $allow=checkUserPolicy($formConfig['policy']);
	      if(!$allow) {
	        trigger_logikserror("Sorry, you are not allowed to access this form");
	        return false;
	      }
	    }

		if($params==null) $params=[];
		$formConfig=array_replace_recursive($formConfig,$params);
		
		if(isset($_SESSION['FORM_CONFIG']) && is_array($_SESSION['FORM_CONFIG'])) {
			if(isset($_SESSION['FORM_CONFIG'][$mode])) {
				$globalParams = $_SESSION['FORM_CONFIG'][$mode];
			} else {
				if(isset($_SESSION['FORM_CONFIG']['edit'])) unset($_SESSION['FORM_CONFIG']['edit']);
				if(isset($_SESSION['FORM_CONFIG']['new'])) unset($_SESSION['FORM_CONFIG']['new']);
				
				$globalParams = $_SESSION['FORM_CONFIG'];
			}
			// $formConfig=array_replace_recursive($formConfig,$globalParams);
			$formConfig=array_merge_recursive($formConfig,$globalParams);
		}

		if(!isset($formConfig['formkey'])) $formConfig['formkey']=md5(session_id().time());

		$formConfig['formcode']=md5($_SESSION['SESS_USER_ID'].$formConfig['sourcefile']);
		$formConfig['formuid']=md5($formConfig['sourcefile']);

		$formConfig['dbkey']=$dbKey;

		if(!isset($formConfig['template'])) {
			$formConfig['template']="tabbed";
		}

		if(!isset($formConfig['gotolink'])) {
			$formConfig['gotolink']="";
		}

		if(!isset($formConfig['config'])) {
			$formConfig['config']=[];
		}

		$fieldGroups=[];
		foreach ($formConfig['fields'] as $fieldKey => $fieldset) {
			if(!isset($fieldset['label'])) $fieldset['label']=_ling($fieldKey);
			if(!isset($fieldset['width'])) $fieldset['width']=6;
			if(!isset($fieldset['group'])) $fieldset['group']="default";

			$fieldset['group']=str_replace(" ","_",$fieldset['group']);

			$fieldset['fieldkey']=$fieldKey;

			if(!isset($fieldGroups[$fieldset['group']])) $fieldGroups[$fieldset['group']]=[];

			$formConfig['fields'][$fieldKey]=$fieldset;
			$fieldGroups[$fieldset['group']][]=$fieldset;
		}

		if(!isset($formConfig['actions'])) $formConfig['actions']=[];
		
		$formConfig['reloadlink']=SiteLocation.substr($_SERVER['REQUEST_URI'],1);
		
		if(!isset($formConfig['gotolink'])) $formConfig['gotolink']=$formConfig['reloadlink'];
	    // if(substr($formConfig['gotolink'],0,5)!="http:" && substr($formConfig['gotolink'],0,6)!="https:") {
	    //   $formConfig['gotolink']=_link($formConfig['gotolink']);
	    // }

		if(isset($formConfig['reportlink'])) {
			$formConfig['cancellink']=_link($formConfig['reportlink']);
		} else {
			$formConfig['cancellink']=$formConfig['reloadlink'];
		}
    
	    $formConfig['cancellink']=_replace($formConfig['cancellink']);
	    $formConfig['gotolink']=_replace($formConfig['gotolink']);
	    $formConfig['reloadlink']=_replace($formConfig['reloadlink']);

		$formConfig['actions']['cancel']=[
								"type"=>"button",
								"label"=>"Cancel",
								"icon"=>"<i class='fa fa-angle-left form-icon right'></i>"
							];
		
		$formConfig['actions']['escape']=[
								"type"=>"button",
								"label"=>"Close",
								"icon"=>"<i class='fa fa-times form-icon right'></i>",
								"extras"=>'data-dismiss="modal"'
							];
		
		switch ($mode) {
			case 'update':
			case 'edit':
				$formConfig['actions']['update']=[
							"type"=>"submit",
							"label"=>"Update",
							"icon"=>"<i class='fa fa-save form-icon right'></i>"
						];
				break;

			case 'insert':
			case 'new':
			default:
// 				$formConfig['actions']["submitnew"]=[
// 							"type"=>"submitnew",
// 							"label"=>"Submit & New",
// 							"icon"=>"<i class='fa fa-save form-icon right'></i>"
// 						];
				$formConfig['actions']["submit"]=[
							"type"=>"submit",
							"label"=>"Submit",
							"icon"=>"<i class='fa fa-save form-icon right'></i>"
						];
				break;
		}

		$formData=[];
		if(isset($formConfig['data']) && count($formConfig['data'])>0) {
			$formData=$formConfig['data'];
		}
		if($mode=="update" || $mode=="edit") {
			$source=$formConfig['source'];
			switch ($source['type']) {
				case 'sql':
					if(isset($formConfig['config']['GUID_LOCK']) && $formConfig['config']['GUID_LOCK']===true) {
						$whereCondition["guid"]=$_SESSION['SESS_GUID'];
					}

					$tempFields = array_filter($formConfig['fields'], function($value,$key){
											return ((strpos($key, '__') !== 0) && !(isset($value['nodb']) && $value['nodb']));
									}, ARRAY_FILTER_USE_BOTH );

// 					printArray($formConfig['fields']);exit();

					$sql=_db($dbKey)->_selectQ($source['table'],array_keys($tempFields),$whereCondition);
// 					echo $sql->_SQL();exit();
					//echo $sql->_SQL();printArray([$formConfig['fields'],$whereCondition]);

					$res=_dbQuery($sql,$dbKey);
					if($res) {
						$data=_dbData($res,$dbKey);
						_dbFree($res,$dbKey);
						if(isset($data[0])) {
							$formData=$data[0];
							$formConfig['source']['where_auto']=$whereCondition;
						} else {
							$formData=[];
						}
					} else {
						trigger_logikserror(_db($dbKey)->get_error());
					}
					//printArray($data);exit($sql->_SQL());

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
		if($formConfig['mode']==null || strlen($formConfig['mode'])<=0) {
			$formConfig['mode']="new";
		}

		$formKey=$formConfig['formkey'];
		$_SESSION['FORM'][$formKey]=$formConfig;
		$_ENV['FORMKEY']=$formKey;

		//Loading Form Template
		$templateArr=[
				$formConfig['template'],
				__DIR__."/templates/{$formConfig['template']}.php"
			];
		
// 		printArray($formConfig);return;
		foreach ($templateArr as $f) {
			if(file_exists($f) && is_file($f)) {
				processFormHook("preLoad",["config"=>$formConfig,"mode"=>$formConfig['mode']]);

				include __DIR__."/vendors/autoload.php";
				echo _css(explode(",",FORM_CSS));
				echo _js(explode(",",FORM_JS));
				if(isset($formConfig['style']) && strlen($formConfig['style'])>0) {
					echo _css(["forms/{$formConfig['style']}",$formConfig['style']]);
				}

				if(isset($_SESSION['FORM'][$_ENV['FORMKEY']]) && isset($_SESSION['FORM'][$_ENV['FORMKEY']]['data'])) {
					$formConfig['data'] = $_SESSION['FORM'][$_ENV['FORMKEY']]['data'];
					$formData = $formConfig['data'];
				}
				
				include $f;
				
				if(isset($formConfig['script']) && strlen($formConfig['script'])>0) {
					echo _js(["forms/{$formConfig['script']}",$formConfig['script']]);
				}
				return true;
			}
		}
		trigger_logikserror("Form Template Not Found",null,404);
	}

	function getFormActions($formActions=[]) {
		$html="";
		foreach ($formActions as $key => $button) {
			if(!isset($button['class'])) $button['class']="btn btn-primary";
			if(isset($button['label'])) $label=_ling($button['label']);
			else $label=toTitle(_ling($key));

			if(isset($button['icon']))  $icon=$button['icon'];
			else $icon="";

			if(strlen($icon)>0 && $icon == strip_tags($icon)) {
				$icon="<i class='{$icon}'></i> ";
			}

			if(!isset($button['type'])) $button['type']="button";
			if(!isset($button['extras'])) $button['extras']="";

			$html.="<button type='{$button['type']}' cmd='{$key}' class='{$button['class']}' {$button['extras']}>{$icon}{$label}</button>";
		}
		return $html;
	}
	function getFormFieldset($fields,$data=[],$dbKey="app",$formMode='new') {
		if(!is_array($fields)) return false;
		//printArray($fields);

		$noLabelFields=["widget","source","module"];

		$html="<fieldset>";
		foreach ($fields as $field) {
			if(!isset($field['fieldkey'])) {
				continue;
			}
			if(isset($field['policy']) && strlen($field['policy'])>0) {
				$allow=checkUserPolicy($field['policy']);
				if(!$allow) continue;
			}
			
			if(isset($field['form']) && !$field['form']) {
				continue;
			}

			if(!isset($field['important'])) $field['important'] = false;
			elseif($field['important']) {
				$field['width'] = 12;
			}

			if(isset($field['vmode'])) {
				if(!is_array($field['vmode'])) {
					$field['vmode']=explode(",",$field['vmode']);
				}
				if(!in_array($formMode,$field['vmode'])) {
					continue;
				}
			}

			if(!isset($field['label'])) {
				$fieldKey=$field['fieldkey'];
				$field['label']=_ling($fieldKey);
			} else {
				$field['label']=_ling($field['label']);
			}

			if(!isset($field['width'])) $field['width']=6;

			if(isset($field['hidden']) && $field['hidden']==true) {
				$html.="<div class='col-sm-{$field['width']} col-lg-{$field['width']} field-container hidden'>";
			} else {
				$html.="<div class='col-sm-{$field['width']} col-lg-{$field['width']} field-container'>";
			}

			if(!isset($field['type'])) $field['type']="text";


			if($field['important']) {
				$html.="<div class='form-group important-field'>";
			} else {
				$html.="<div class='form-group'>";
			}
			
			if(!in_array($field['type'],$noLabelFields)) {
				$html.="<label>{$field['label']}";
			}

			if(isset($field['required']) && $field['required']==true) {
				$html.="<span class='span-required'>*</span>";
			}
			if(isset($field['tips']) && strlen($field['tips'])>1) {
				if(substr($field['tips'], 0,7)=="http://" || substr($field['tips'], 0,8)=="https://")
					$html.="<a href='{$field['tips']}' target=_blank class='field-tips pull-right fa fa-question-circle'></a>";
				elseif(strlen($field['tips'])<=25)
					$html.="<span title='{$field['tips']}' class='field-tips pull-right fa fa-question-circle'> {$field['tips']}</span>";
				else
					$html.="<span title='{$field['tips']}' class='field-tips pull-right fa fa-question-circle'> ".substr($field['tips'], 0,20)."...</span>";
			}
			$html.="</label>";
			$html.=getFormField($field,$data,$dbKey);
			$html.="</div>";
			$html.="</div>";
		}
		$html.="</fieldset>";

		return $html;
	}

	function getFormField($fieldinfo,$data,$dbKey="app") {
		$formKey=$fieldinfo['fieldkey'];
		if(!isset($data[$formKey])) {
			if(isset($_GET[$formKey])) {
				$data[$formKey]=$_GET[$formKey];
				if(isset($_GET['form_allow_edit']) && $_GET['form_allow_edit']=="true") {
					$fieldinfo['readonly']=false;
				} else {
					$fieldinfo['readonly']=true;
					$fieldinfo['type']="text";
				}
			} elseif(isset($fieldinfo['default'])) {
				$data[$formKey]=$fieldinfo['default'];
			} else {
				$data[$formKey]="";
			}
		}

		if(!isset($fieldinfo['type'])) $fieldinfo['type']="text";
		if(!isset($fieldinfo['label'])) $fieldinfo['label']=_ling($formKey);
		if(!isset($fieldinfo['placeholder'])) $fieldinfo['placeholder']="";

		$html="";

		$class="form-control field-{$fieldinfo['type']} field-{$formKey}";
		$xtraAttributes=[];

		if(isset($fieldinfo['class']) && strlen($fieldinfo['class'])>0) {
			$class.=" ".$fieldinfo['class'];
		}
		if(isset($fieldinfo['search']) && $fieldinfo['search']==true) {
			$class.=" search";
		}
		if(isset($fieldinfo['disabled']) && $fieldinfo['disabled']==true) {
			$xtraAttributes[]="disabled";
		}
		if(isset($fieldinfo['readonly']) && $fieldinfo['readonly']==true) {
			$xtraAttributes[]="readonly";
		}
		if(isset($fieldinfo['noedit']) && $fieldinfo['noedit']==true) {
			if(strlen($data[$formKey])>0) {
				$xtraAttributes[]="disabled";
			}
		}
		if(isset($fieldinfo['required']) && $fieldinfo['required']==true) {
			$class.=" required";
			$xtraAttributes[]="required";
		}
		if(isset($fieldinfo['multiple']) && $fieldinfo['multiple']==true) {
			$class.=" multiple";
			$xtraAttributes[]="multiple";
		}

		if(isset($fieldinfo['max']) && strlen($fieldinfo['max'])>0) {
			$xtraAttributes[]="max={$fieldinfo['max']}";
		}
		if(isset($fieldinfo['min']) && strlen($fieldinfo['min'])>0) {
			$xtraAttributes[]="min={$fieldinfo['min']}";
		}
		if(isset($fieldinfo['maxlength']) && strlen($fieldinfo['maxlength'])>0) {
			$xtraAttributes[]="maxlength={$fieldinfo['maxlength']}";
		}
		if(isset($fieldinfo['minlength']) && strlen($fieldinfo['minlength'])>0) {
			$xtraAttributes[]="minlength={$fieldinfo['minlength']}";
		}
		if(isset($fieldinfo['pattern']) && strlen($fieldinfo['pattern'])>0) {
			$xtraAttributes[]="pattern={$fieldinfo['pattern']}";
		}
		if(isset($fieldinfo['size']) && strlen($fieldinfo['size'])>0) {
			$xtraAttributes[]="size={$fieldinfo['size']}";
		}
		if(isset($fieldinfo['step']) && strlen($fieldinfo['step'])>0) {
			$xtraAttributes[]="step={$fieldinfo['step']}";
		}
		if(isset($fieldinfo['src']) && strlen($fieldinfo['src'])>0) {
			$xtraAttributes[]="src='{$fieldinfo['src']}'";
		}
		if(isset($fieldinfo['nodb']) && $fieldinfo['nodb']==true) {
			$class.=" nodb";
			$xtraAttributes[]="nodb";
		}

		if(isset($fieldinfo['ajaxchain']) && isset($fieldinfo['ajaxchain']['target'])>0) {
			if(isset($fieldinfo['ajaxchain']['scmd'])) {
				$class.=" ajaxchain ajaxchainscmd";
				$xtraAttributes[]="ajaxchain-target='{$fieldinfo['ajaxchain']['target']}' ajaxchain-scmd='{$fieldinfo['ajaxchain']['scmd']}'";
			} elseif(isset($fieldinfo['ajaxchain']['src'])) {
				$class.=" ajaxchain ajaxchainself";
				$autoSrc=$fieldinfo['ajaxchain']['src'];

				$_SESSION['FORMAUTOCOMPLETE'][$_ENV['FORMKEY']][$formKey]=$autoSrc;
				$xtraAttributes[]="ajaxchain-target='{$fieldinfo['ajaxchain']['target']}'";
			}
		}
		if(isset($fieldinfo['autocomplete'])) {
			if(is_array($fieldinfo['autocomplete']) && isset($fieldinfo['autocomplete']['target'])>0) {
				if(isset($fieldinfo['autocomplete']['scmd'])) {
					$class.=" autocomplete autocompletescmd";
					$xtraAttributes[]="autocomplete-target='{$fieldinfo['autocomplete']['target']}' autocomplete-scmd='{$fieldinfo['autocomplete']['scmd']}'";
				} elseif(isset($fieldinfo['autocomplete']['src'])) {
					$class.=" autocomplete autocompleteself";
					$autoSrc=$fieldinfo['autocomplete']['src'];

					$_SESSION['FORMAUTOCOMPLETE'][$_ENV['FORMKEY']][$formKey]=$autoSrc;
					$xtraAttributes[]="autocomplete-target='{$fieldinfo['autocomplete']['target']}'";
				}
			} elseif($fieldinfo['autocomplete']===false) {
				$xtraAttributes[]="autocomplete='off'";
			}
		}

		if(!isset($fieldinfo['no-option'])) {
			$fieldinfo['no-option']="Select ".toTitle($formKey);
		}
		$noOption=_ling($fieldinfo['no-option']);

		$xtraAttributes[]="no-options='{$noOption}'";

		if(isset($fieldinfo['multiple']) && $fieldinfo['multiple']==true) {
			$xtraAttributes[]="data-name='{$formKey}[]'";
		} else {
			$xtraAttributes[]="data-name='{$formKey}'";
		}
		
		if(!isset($data[$formKey])) {
			if(isset($fieldinfo['default'])) {
				$data[$formKey]=$fieldinfo['default'];
			}
		}

		$xtraAttributes=trim(implode(" ", $xtraAttributes));

		$typeArr=explode("@",$fieldinfo['type']);
		$typeS=current($typeArr);
		switch ($typeS) {
			case 'dataMethod': case 'dataSelector': case 'dataSelectorFromUniques': case 'dataSelectorFromTable':
			case 'dropdown': case 'select': case 'selectAJAX':
				if(!isset($fieldinfo['options'])) $fieldinfo['options']=[];

				$html.="<div class='select-group'>";

				if(isset($fieldinfo['multiple']) && $fieldinfo['multiple']==true) {
					$html.="<select class='{$class} field-dropdown {$fieldinfo['type']}' $xtraAttributes name='{$formKey}[]' data-value=\"".$data[$formKey]."\" data-selected=\"".$data[$formKey]."\">";
				} else {
					$html.="<select class='{$class} field-dropdown {$fieldinfo['type']}' $xtraAttributes name='{$formKey}' data-value=\"".$data[$formKey]."\" data-selected=\"".$data[$formKey]."\">";
				}

				if(is_array($fieldinfo['options'])) {
					if(!array_key_exists("", $fieldinfo['options']) || $fieldinfo['options']['']===true) {
						$html.="<option value=''>{$noOption}</option>";
					}
				}
				if(isset($fieldinfo['dbkey'])) $dkey1=$fieldinfo['dbkey'];
				else $dkey1=$dbKey;
				
				if(isset($fieldinfo['search']) && $fieldinfo['search']==true) {
					
				} else {
					$html.=generateSelectOptions($fieldinfo,$data[$formKey],$dkey1);
				}
				$html.="</select>";
				$html.="</div>";
				break;

			case 'radiolist': case 'checkboxlist':
				if(!isset($fieldinfo['options'])) $fieldinfo['options']=[];

				$html.="<div class='fieldlist {$fieldinfo['type']}' $xtraAttributes name='{$formKey}' data-value=\"".$data[$formKey]."\" data-selected=\"".$data[$formKey]."\">";
				//TODO
				//$fieldinfo['type']="select";

				$html.="<select>";
				$html.=generateSelectOptions($fieldinfo,$data[$formKey],$dbKey);
				$html.="</select>";
				$html.="</div>";
				break;

			case 'textarea': case 'longtext': case 'richtextarea': case 'markup':
				$data[$formKey]=stripslashes(str_replace("\\r\\n","",$data[$formKey]));
				$data[$formKey]=stripslashes(str_replace("&amp%3B","&amp;",$data[$formKey]));
				$data[$formKey]=str_replace("%3B","",$data[$formKey]);
// 				$data[$formKey]=urldecode($data[$formKey]);
				$html.="<textarea class='{$class}' $xtraAttributes name='{$formKey}' placeholder='{$fieldinfo['placeholder']}'>".$data[$formKey]."</textarea>";
				break;

			case 'color':
				$html.="<input class='{$class}' $xtraAttributes name='{$formKey}' value=\"".$data[$formKey]."\" placeholder='{$fieldinfo['placeholder']}' type='{$fieldinfo['type']}'>";
				break;

			case 'radio': case 'checkbox':
				$html.="<input class='{$class}' $xtraAttributes name='{$formKey}' value=\"".$data[$formKey]."\" placeholder='{$fieldinfo['placeholder']}' type='{$fieldinfo['type']}'>";
				break;
			case 'date': case 'datetime': case 'month': case 'year': case 'time'://case 'datetime-local': case 'week':
				if($fieldinfo['type']!="time") {
					if($data[$formKey]==null || strlen($data[$formKey])<=1 || $data[$formKey]==0) $data[$formKey]="";
					else {
						$data[$formKey] = str_replace("T"," ",current(explode("Z", $data[$formKey])));
						if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/",$data[$formKey])) {
							$data[$formKey]=_pDate($data[$formKey],"d/m/Y");
						}
					}
				}
				$html.="<div class='input-group'>";
				$html.="<input class='{$class}' $xtraAttributes name='{$formKey}' value=\"".$data[$formKey]."\" placeholder='{$fieldinfo['placeholder']}' type='text'>";
				$html.="<div class='input-group-addon'><i class='fa fa-calendar'></i></div>";
				$html.="</div>";
				break;

			case 'currency':
				if(!isset($fieldinfo['currency_type'])) $fieldinfo['currency_type']="mxx";
				if($fieldinfo['placeholder']==null || strlen($fieldinfo['placeholder'])<=0) $fieldinfo['placeholder']=_ling("0.00");
				$html.="<div class='input-group'>";
				$html.="<input class='{$class}' $xtraAttributes name='{$formKey}' value=\"".$data[$formKey]."\" placeholder='{$fieldinfo['placeholder']}' type='number'>";
				$html.="<div class='input-group-addon'><i class='fa fa-money fa-{$fieldinfo['currency_type']}'></i></div>";
				$html.="</div>";
				break;
			case 'creditcard':case 'debitcard':case 'moneycard':
				if(!isset($fieldinfo['card_type'])) $fieldinfo['card_type']="credit-card";
				if($fieldinfo['placeholder']==null || strlen($fieldinfo['placeholder'])<=0) $fieldinfo['placeholder']=_ling("XXXX XXXX XXXX XXXX");
				$html.="<div class='input-group'>";
				$html.="<input class='{$class}' $xtraAttributes name='{$formKey}' value=\"".$data[$formKey]."\" placeholder='{$fieldinfo['placeholder']}' type='{$fieldinfo['type']}'>";
				$html.="<div class='input-group-addon'><i class='fa fa-credit-card fa-{$fieldinfo['card_type']}'></i></div>";
				$html.="</div>";
				break;
			case 'email':
				$html.="<div class='input-group'>";
				$html.="<input class='{$class}' $xtraAttributes name='{$formKey}' value=\"".$data[$formKey]."\" placeholder='{$fieldinfo['placeholder']}' type='{$fieldinfo['type']}'>";
				$html.="<div class='input-group-addon'>@</div>";
				$html.="</div>";
				break;
			case 'tel':case 'phone':
				$html.="<div class='input-group'>";
				$html.="<input class='{$class}' $xtraAttributes name='{$formKey}' value=\"".$data[$formKey]."\" placeholder='{$fieldinfo['placeholder']}' type='{$fieldinfo['type']}'>";
				$html.="<div class='input-group-addon'><i class='fa fa-phone'></i></div>";
				$html.="</div>";
				break;
			case 'mobile':
				$html.="<div class='input-group'>";
				$html.="<input class='{$class}' $xtraAttributes name='{$formKey}' value=\"".$data[$formKey]."\" placeholder='{$fieldinfo['placeholder']}' type='{$fieldinfo['type']}'>";
				$html.="<div class='input-group-addon'><i class='fa fa-mobile'></i></div>";
				$html.="</div>";
				break;
			case 'tags':case 'tag':
				$html.="<div class='input-group'>";
				$html.="<input class='{$class}' $xtraAttributes name='{$formKey}' value=\"".$data[$formKey]."\" placeholder='{$fieldinfo['placeholder']}' type='tags'>";
				$html.="<div class='input-group-addon'><i class='fa fa-tags'></i></div>";
				$html.="</div>";
				break;
			case 'url':
				$html.="<div class='input-group'>";
				$html.="<input class='{$class}' $xtraAttributes name='{$formKey}' value=\"".$data[$formKey]."\" placeholder='{$fieldinfo['placeholder']}' type='{$fieldinfo['type']}'>";
				$html.="<div class='input-group-addon'><i class='fa fa-globe'></i></div>";
				$html.="</div>";
				break;
			case 'social':case 'brand':
				if(isset($typeArr[1]) && strlen($typeArr[1])>0) {
					$html.="<div class='input-group'>";
					$html.="<input class='{$class}' $xtraAttributes name='{$formKey}' value=\"".$data[$formKey]."\" placeholder='{$fieldinfo['placeholder']}' type='{$fieldinfo['type']}'>";
					$html.="<div class='input-group-addon'><i class='fa fa-{$typeArr[1]}'></i></div>";
					$html.="</div>";
				} else {
					$html.="<input class='{$class}' $xtraAttributes name='{$formKey}' value=\"".$data[$formKey]."\" placeholder='{$fieldinfo['placeholder']}' type='{$fieldinfo['type']}'>";
				}
				break;
			case 'number':
// 				$html.="<div class='input-group'>";
				$html.="<input class='{$class}' $xtraAttributes name='{$formKey}' value=\"".$data[$formKey]."\" placeholder='{$fieldinfo['placeholder']}' type='{$fieldinfo['type']}'>";
// 				$html.="<div class='input-group-addon'><i class='fa fa-calendar'></i></div>";
// 				$html.="</div>";
				break;
			case 'barcode':
				$html.="<div class='input-group'>";
				$html.="<input class='{$class}' $xtraAttributes name='{$formKey}' value=\"".$data[$formKey]."\" placeholder='{$fieldinfo['placeholder']}' type='{$fieldinfo['type']}'>";
				$html.="<div class='input-group-addon'><i class='fa fa-barcode'></i></div>";
				$html.="</div>";
				break;
			case 'qrcode':
				$html.="<div class='input-group'>";
				$html.="<input class='{$class}' $xtraAttributes name='{$formKey}' value=\"".$data[$formKey]."\" placeholder='{$fieldinfo['placeholder']}' type='{$fieldinfo['type']}'>";
				$html.="<div class='input-group-addon'><i class='fa fa-qrcode'></i></div>";
				$html.="</div>";
				break;
			case 'search':
				$html.="<div class='input-group'>";
				$html.="<input class='{$class}' $xtraAttributes name='{$formKey}' value=\"".$data[$formKey]."\" placeholder='{$fieldinfo['placeholder']}' type='{$fieldinfo['type']}'>";
				$html.="<div class='input-group-addon'><i class='fa fa-search'></i></div>";
				$html.="</div>";
				break;
			case "suggest":
				$suggestid=uniqid("S-");
				$html.="<div class='input-group'>";
				$html.="<input class='{$class}' $xtraAttributes name='{$formKey}' value=\"".$data[$formKey]."\" placeholder='{$fieldinfo['placeholder']}' type='{$fieldinfo['type']}' list='{$suggestid}'>";
				$html.="<datalist id='{$suggestid}'>";
				if(isset($_ENV['FORMKEY']) && isset($_SESSION['FORM'][$_ENV['FORMKEY']]) && isset($_SESSION['FORM'][$_ENV['FORMKEY']]['source']) && isset($_SESSION['FORM'][$_ENV['FORMKEY']]['source']['table'])) {
					if(isset($fieldinfo['where']) && is_array($fieldinfo['where']) && count($fieldinfo['where'])>0) {
						$html.=createDataSelectorFromUniques($_SESSION['FORM'][$_ENV['FORMKEY']]['source']['table'],$formKey,$formKey, $fieldinfo['where']);
					} else {
						$html.=createDataSelectorFromUniques($_SESSION['FORM'][$_ENV['FORMKEY']]['source']['table'],$formKey,$formKey);
					}
				}
				$html.="</datalist>";
				$html.="<div class='input-group-addon'><i class='fa fa-caret-down'></i></div>";
				$html.="</div>";
				break;
			case 'password':
				$html.="<div class='input-group'>";
				$html.="<input class='{$class}' $xtraAttributes name='{$formKey}' value=\"".$data[$formKey]."\" placeholder='{$fieldinfo['placeholder']}' type='password'>";
				$html.="<div class='input-group-addon'><i class='fa fa-key'></i></div>";
				$html.="</div>";
				break;

			case 'text': case 'range':
				$html.="<input class='{$class}' $xtraAttributes name='{$formKey}' value=\"".$data[$formKey]."\" placeholder='{$fieldinfo['placeholder']}' type='{$fieldinfo['type']}'>";
				break;

			case 'file':case 'files':case 'attachment':
				$fieldHash=md5($formKey.time());
				if(isset($fieldinfo['multiple']) && $fieldinfo['multiple']==true) {
					$html.="<div name='{$formKey}' class='file-input file-input-attachment file-field-{$fieldinfo['type']} file-input-multiple' $xtraAttributes><div class='file-preview'>";

					$html.="<div class='file-drop' data-fhash='{$fieldHash}'><div class='file-upload'>";
					$html.="<i class='fa fa-cloud-upload'></i>";
					$html.="<input type='file' class='form-file-field hidden' >";
					$html.="</div></div>";

					$html.="<div class='file-preview-thumbnails' data-fhash='{$fieldHash}' >";

					if(isset($data[$formKey]) && strlen($data[$formKey])>0) {
						$mediaArr=explode(",",$data[$formKey]);
						foreach($mediaArr as $m) {
							$media=searchMedia($m);
							if($media) {
								$html.="<div class='file-preview-thumb'>";
								$html.="<span class='pull-right fa fa-times fa-close'></span>";
								$html.="<i class='fileicon fa ".getFileIcon($media['src'])."'></i>";
								$html.="<span class='filename'>{$media['name']}</span>";
								$html.="<input name='{$formKey}[]' type='hidden' class='hidden' value='{$media['raw']}' >";
								$html.="</div>";
							} else {

							}
						}
					}

					$html.="</div>";
					$html.="</div></div>";
				} else {
					$html.="<div name='{$formKey}' class='file-input file-input-attachment file-field-{$fieldinfo['type']}' $xtraAttributes><div class='file-preview'>";

					$html.="<div class='file-drop' data-fhash='{$fieldHash}'><div class='file-upload'>";
					$html.="<i class='fa fa-cloud-upload'></i>";
					$html.="<input type='file' class='form-file-field hidden' >";
					$html.="</div></div>";

					$html.="<div class='file-preview-thumbnails' data-fhash='{$fieldHash}' >";

					if(isset($data[$formKey]) && strlen($data[$formKey])>0) {
						$media=searchMedia($data[$formKey]);
						if($media) {
							$html.="<div class='file-preview-thumb'>";
							$html.="<span class='pull-right fa fa-times fa-close'></span>";
							$html.="<i class='fileicon fa ".getFileIcon($media['src'])."'></i>";
							$html.="<span class='filename'>{$media['name']}</span>";
							$html.="<input name='{$formKey}' type='hidden' class='hidden' value='{$media['raw']}' >";
							$html.="</div>";
						} else {

						}
					}

					$html.="</div>";
					$html.="</div></div>";
				}
				break;

			case 'photo':case 'photos':case 'image':case 'avatar':case 'gallery':
				$fieldHash=md5($formKey.time());

				if($fieldinfo['type']=="avatar") {
					$fieldinfo['multiple']=false;
				}

				if(isset($fieldinfo['multiple']) && $fieldinfo['multiple']==true) {
					$html.="<div name='{$formKey}' class='file-input file-field-{$fieldinfo['type']} file-input-multiple' $xtraAttributes><div class='file-preview'>";

					if($fieldinfo['type']=="gallery") {
						$html.="<div class='file-gallery' data-fhash='{$fieldHash}'><div class='file-upload'>";
						$html.="<i class='fa fa-paperclip'></i>";
						$html.="</div></div>";
					} else {
						$html.="<div class='file-drop' data-fhash='{$fieldHash}'><div class='file-upload'>";
						$html.="<i class='fa fa-cloud-upload'></i>";
						$html.="<input type='file' class='form-file-field hidden' >";
						$html.="</div></div>";
					}

					$html.="<div class='file-preview-thumbnails' data-fhash='{$fieldHash}' >";

					if(isset($data[$formKey]) && strlen($data[$formKey])>0) {
						$mediaArr=explode(",",$data[$formKey]);
						foreach($mediaArr as $m) {
							$media=searchMedia($m);
							if($media) {
								$html.="<div class='file-preview-thumb'>";
								$html.="<span class='pull-right fa fa-times fa-close'></span>";
								if($media['ext']=="png" || $media['ext']=="gif" || $media['ext']=="jpg" || $media['ext']=="jpeg") {
									$html.="<img src='{$media['url']}' />";
								} else {
									$html.="<i class='fileicon fa ".getFileIcon($media['src'])."'></i>";
								}
								$html.="<input name='{$formKey}[]' type='hidden' class='hidden' value='{$media['raw']}' >";
								$html.="</div>";
							} else {

							}
						}
					}

					$html.="</div>";
					$html.="</div></div>";
				} else {
					$html.="<div name='{$formKey}' class='file-input file-field-{$fieldinfo['type']}' $xtraAttributes><div class='file-preview'>";

					if($fieldinfo['type']=="gallery") {
						$html.="<div class='file-gallery' data-fhash='{$fieldHash}'><div class='file-upload'>";
						$html.="<i class='fa fa-paperclip'></i>";
						$html.="</div></div>";
					} else {
						$html.="<div class='file-drop' data-fhash='{$fieldHash}'><div class='file-upload'>";
						$html.="<i class='fa fa-cloud-upload'></i>";
						$html.="<input type='file' class='form-file-field hidden' >";
						$html.="</div></div>";
					}

					$html.="<div class='file-preview-thumbnails' data-fhash='{$fieldHash}' >";

					if(isset($data[$formKey]) && strlen($data[$formKey])>0) {
						$media=searchMedia($data[$formKey]);
						if($media) {
							$html.="<div class='file-preview-thumb'>";
							$html.="<span class='pull-right fa fa-times fa-close'></span>";
							if($media['ext']=="png" || $media['ext']=="gif" || $media['ext']=="jpg" || $media['ext']=="jpeg") {
								$html.="<img src='{$media['url']}' />";
							} else {
								$html.="<i class='fileicon fa ".getFileIcon($media['src'])."'></i>";
							}
							$html.="<input name='{$formKey}' type='hidden' class='hidden' value='{$media['raw']}' >";
							$html.="</div>";
						} else {

						}
					}

					$html.="</div>";
					$html.="</div></div>";
				}
				break;

			case 'jsonfield':
				if(!isset($fieldinfo['columns'])) $fieldinfo['columns']="key,value";
				if(!is_array($fieldinfo['columns'])) $fieldinfo['columns']=array_flip(explode(",",$fieldinfo['columns']));

				$html.="<div class='table-responsive'>";
				$html.="<table class='table table-condensed jsonField' name='{$formKey}'>";
				if(isset($fieldinfo['noheader']) && $fieldinfo['noheader']) {
					$html.="<thead class='hidden'><tr>";
				} else {
					$html.="<thead><tr>";
				}
				$html.="<th width=25px></th>";
				foreach($fieldinfo['columns'] as $key=>$cols) {
					if(!is_array($cols)) $cols=[];
					if(!isset($cols['label'])) $cols['label']=toTitle($key);
					if(!isset($cols['type'])) $cols['type']="text";
					$html.="<th class='text-center col' name='{$key}' type='{$cols['type']}'>{$cols['label']}</th>";
				}
				$html.="<th width=25px></th>";
				$html.="</tr></thead>";

				$html.="<tbody>";
				if(isset($data[$formKey]) && strlen($data[$formKey])>2) {
					$data[$formKey]=json_decode(stripslashes($data[$formKey]),true);
					foreach($data[$formKey] as $dx) {
						$hx=[];
						$hx[]="<td width=25px><i class='fa fa-bars reorderRow'></i></td>";
						foreach($dx as $dx1=>$dx2) {
							$hx[]="<td><input name='{$formKey}[{$dx1}][]' class='form-control' placeholder='{$dx1}' value='{$dx2}' /></td>";
						}
						$hx[]="<td width=25px><i class='fa fa-times cmdAction' cmd='removeJSONKeyField'></i></td>";
						$html.="<tr>".implode("",$hx)."</tr>";
					}
				}
				$html.="</tbody>";

				$html.="<tfoot>";
				$html.="<tr>";
				$html.="<th colspan=1000 class='text-center cmdAction' cmd='addJSONKeyField'><i class='fa fa-plus'></i></th>";
				$html.="</tr>";
				$html.="</tfoot>";
				$html.="</table>";
				//$html.="<input class='{$class}' $xtraAttributes name='{$formKey}' value=\"".$data[$formKey]."\" placeholder='{$fieldinfo['placeholder']}' type='password'>";
				//$html.="<div class='input-group-addon'></div>";
				$html.="</div>";
				break;

			case 'widget':
				if(isset($fieldinfo['src'])) {
					ob_start();
					loadWidget($fieldinfo['src']);
					$html.=ob_get_contents();
					ob_clean();
				} else {
					$html.="Widget '{$fieldinfo['src']}' not found.";
				}
				break;
			case 'module':
				if(isset($fieldinfo['src'])) {
					$src=explode(".",$fieldinfo['src']);
					if(count($src)>1 && strlen($src[1])>0) {
						ob_start();
						loadModuleComponent($src[0],$src[1]);
						$html.=ob_get_contents();
						ob_clean();
					} else {
						ob_start();
						loadModules($fieldinfo['src']);
						$html.=ob_get_contents();
						ob_clean();
					}
				} else {
					$html.="Module '{$fieldinfo['src']}' not found.";
				}
				break;
			case 'source':
				if(isset($fieldinfo['src'])) {
					if(file_exists($fieldinfo['src'])) {
						ob_start();
						include $fieldinfo['src'];
						$html.=ob_get_contents();
						ob_clean();
					} elseif(file_exists(APPROOT.$fieldinfo['src'])) {
						ob_start();
						include APPROOT.$fieldinfo['src'];
						$html.=ob_get_contents();
						ob_clean();
					} else {
						$html.="Source '".basename($fieldinfo['src'])."' not found.";
					}
				} else {
					$html.="Source '".basename($fieldinfo['src'])."' not defined.";
				}
				break;
				
			case 'static':
				$content=$fieldinfo['placeholder'];
				if(isset($data[$formKey]) && strlen($data[$formKey])>1) $content=$data[$formKey];

				$html.="<div class='form-control-static field-{$formKey}' name='{$formKey}' $xtraAttributes>{$content}</div>";
				break;

			default:
				$html.="<input class='{$class}' $xtraAttributes name='{$formKey}' value=\"".$data[$formKey]."\" placeholder='{$fieldinfo['placeholder']}' type='text'>";
				break;
		}

		return $html;
	}

	function autoReferenceSystem($formConfig,$refid) {
		if(isset($formConfig['source']['refmaster'])) {
			$dCount = _db()->_selectQ($formConfig['source']['table'], "count(*) as cnt", ["md5({$formConfig['source']['refcol']})"=>$refid])->_GET();
			if($dCount[0]['cnt']<=0) {
				$dRefers = _db()->_selectQ($formConfig['source']['refmaster'], "id", ["md5(id)"=>$refid])->_GET();
				if(isset($dRefers[0])) {
					$srcid=$dRefers[0]['id'];
					$inData=[
									'guid'=>$_SESSION['SESS_GUID'],
									$formConfig['source']['refcol']=>$srcid,
									'created_by'=>$_SESSION['SESS_USER_ID'],
									'edited_by'=>$_SESSION['SESS_USER_ID'],
									'created_on'=>date('Y-m-d H:i:s'),
									'edited_on'=>date('Y-m-d H:i:s'),
								];
					$ans=_db()->_insertQ1($formConfig['source']['table'], $inData)->_RUN();

					if($ans) {
						header("Location:"._link(PAGE));
					} else {
						trigger_logikserror("Failed to auto create reference record.");
					}
				} else {
					trigger_logikserror("Reference does not exist for this record.");
				}
			}
		} else {
			trigger_logikserror("RefAutoCreate is enabled, but refmaster not defined.");
		}
	}

	function getFormFieldData($key) {
		if(isset($_SESSION['FORM']) &&
			isset($_SESSION['FORM'][$_ENV['FORMKEY']]) &&
			isset($_SESSION['FORM'][$_ENV['FORMKEY']]['data']) &&
			isset($_SESSION['FORM'][$_ENV['FORMKEY']]['data'][$key])
			) {
			return $_SESSION['FORM'][$_ENV['FORMKEY']]['data'][$key];
		} else {
			return false;
		}
	}
	
	function setFormFieldData($key,$data) {
		$_SESSION['FORM'][$_ENV['FORMKEY']]['data'][$key] = $data;
		return $data;
	}
}
if(!function_exists("searchMedia")) {
	function searchMedia($media) {
		if(isset($_REQUEST['forsite'])) {
			$fs=_fs($_REQUEST['forsite'],[
					"driver"=>"local",
					"basedir"=>ROOT.APPS_FOLDER.$_REQUEST['forsite']."/".APPS_USERDATA_FOLDER
				]);
		} else {
			$fs=_fs();
		}
		$mediaDir=$fs->pwd();

		if(file_exists($media)) {
			$ext=explode(".",$media);
			$mediaName=explode("_",basename($media));
			$mediaName=array_slice($mediaName,1);
			$mediaName=implode("_",$mediaName);
			return [
				"name"=>$mediaName,
				"raw"=>$media,
				"src"=>$media,
				"url"=>getWebPath($media),
				"size"=>filesize($media)/1024,
				"ext"=>strtolower(end($ext)),
			];
		} elseif(file_exists($mediaDir.$media)) {
			$ext=explode(".",$media);
			$mediaName=explode("_",basename($media));
			$mediaName=array_slice($mediaName,1);
			$mediaName=implode("_",$mediaName);
			return [
				"name"=>$mediaName,
				"raw"=>$media,
				"src"=>$mediaDir.$media,
				"url"=>getWebPath($mediaDir.$media),
				"size"=>filesize($mediaDir.$media)/1024,
				"ext"=>strtolower(end($ext)),
			];
		} else {
			return false;
		}
	}
}
if(!function_exists("getFileIcon")) {
	function getFileIcon($file) {
		if($file==null || strlen($file)<=0) return "";

		$ext=explode(".",$file);
		$ext=strtolower(end($ext));

		if(strlen($ext)<=0) return "fa-file";

		switch(strtolower($ext)) {
			case "png":case "gif":case "jpg":case "jpeg":case "bmp":
				return "fa-file-image-o";
				break;
			case "mp3":case "ogg":case "wav":case "aiff":case "wma":
				return "fa-file-audio-o";
				break;
			case "mp4":case "mpeg":case "mpg":case "avi":case "mov":case "wmv":
				return "fa-file-video-o";
				break;
			case "doc":case "txt":case "rdf":case "odt":
				return "fa-file-word-o";
				break;
			case "xls":case "ods":
				return "fa-file-excel-o";
				break;
			case "zip":case "tar":case "bz":case "bz2":case "gz":case "rar":case "zip":
				return "fa-file-zip-o";
				break;
			case "pdf":
				return "fa-file-pdf-o";
				break;
			case "php":case "html":case "js":case "css":case "java":case "py":case "c":case "cpp":case "sql":
				return "fa-file-code-o";
				break;
			default:
				return "fa-file";
		}
	}
}
if(!function_exists("processFormHook")) {
	function processFormHook($hookState,$formParams) {
		$formParams=array_merge(["data"=>[],"where"=>[],"config"=>[],"mode"=>"new"],$formParams);

		$_ENV['FORM-HOOK-PARAMS']=$formParams;

		$mode=$formParams['mode'];
		$formConfig=$formParams['config'];

		if($mode=="new") $mode="insert";
		if($mode=="edit") $mode="update";

		$type=strtolower($hookState."-".$mode);
		switch($type) {
			case "preload-insert":
				executeFormHook("preload",$formConfig);
				executeFormHook("preloadCreate",$formConfig);
				break;
			case "preload-update":
				executeFormHook("preload",$formConfig);
				executeFormHook("preloadUpdate",$formConfig);
				break;
			case "presubmit-insert":
				executeFormHook("presubmit",$formConfig);
				executeFormHook("presubmitCreate",$formConfig);
				break;
			case "presubmit-update":
				executeFormHook("presubmit",$formConfig);
				executeFormHook("presubmitUpdate",$formConfig);
				break;
			case "postsubmit-insert":
				executeFormHook("postsubmit",$formConfig);
				executeFormHook("postsubmitCreate",$formConfig);
				break;
			case "postsubmit-update":
				executeFormHook("postsubmit",$formConfig);
				executeFormHook("postsubmitUpdate",$formConfig);
				break;
			case "dataposted-insert":
				executeFormHook("dataposted",$formConfig);
				executeFormHook("datapostedCreate",$formConfig);
				break;
			case "dataposted-update":
				executeFormHook("dataposted",$formConfig);
				executeFormHook("datapostedUpdate",$formConfig);
				break;
		}
		if(isset($_ENV['FORM-HOOK-PARAMS'])) unset($_ENV['FORM-HOOK-PARAMS']);
	}
	function executeFormHook($state,$formConfig) {
		if(!isset($formConfig['hooks']) || !is_array($formConfig['hooks'])) return false;
		$state=strtolower($state);

		if(isset($formConfig['hooks'][$state]) && is_array($formConfig['hooks'][$state])) {
			$postCFG=$formConfig['hooks'][$state];

			if(isset($postCFG['modules'])) {
				loadModules($postCFG['modules']);
			}
			if(isset($postCFG['api'])) {
				if(!is_array($postCFG['api'])) $postCFG['api']=explode(",",$postCFG['api']);
				foreach ($postCFG['api'] as $apiModule) {
					loadModuleLib($apiModule,'api');
				}
			}
			if(isset($postCFG['helpers'])) {
				loadHelpers($postCFG['helpers']);
			}
			if(isset($postCFG['method'])) {
				if(!is_array($postCFG['method'])) $postCFG['method']=explode(",",$postCFG['method']);
				foreach($postCFG['method'] as $m) {
					if(function_exists($m)) {
						call_user_func($m,$_ENV['FORM-HOOK-PARAMS']);
					}
				}
			}
			if(isset($postCFG['file'])) {
				if(!is_array($postCFG['file'])) $postCFG['file']=explode(",",$postCFG['file']);
				foreach($postCFG['file'] as $m) {
					if(file_exists($m)) include $m;
					elseif(file_exists(APPROOT.$m)) include APPROOT.$m;
				}
			}
		}
	}
}
?>
