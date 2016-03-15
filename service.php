<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!isset($_REQUEST["action"])) {
	$_REQUEST["action"]="";
}
if(!isset($_REQUEST['formid'])) {
	trigger_error("Form Not Found");
}
include_once __DIR__."/api.php";

switch($_REQUEST["action"]) {
	case "submit":
		$formKey=$_REQUEST['formid'];
		if(!isset($_SESSION['FORM'][$formKey])) {
			displayFormMsg("Sorry, form key not found.");
		}
		
		$formConfig=$_SESSION['FORM'][$formKey];

		$fs=_fs($_REQUEST['forsite'],[
					"driver"=>"local",
        			"basedir"=>ROOT.APPS_FOLDER.$_REQUEST['forsite']."/".APPS_USERDATA_FOLDER
				]);

		//printArray($formConfig);
		//printArray($_POST);
		$files=handleFileUpload($formConfig,$fs);

		$source=$formConfig['source'];
		switch ($source['type']) {
			case 'sql':
				$cols=array_keys($formConfig['fields']);
				$where=$source['where'];
				if(!is_array($where)) $where=explode(",", $where);

				$oriData=$_POST;
				$oriData=array_merge($formConfig['data'],$oriData);

				if($formConfig['mode']=="update") {
					$where=array_flip($where);
					foreach ($where as $key => $value) {
						if(isset($_POST[$key])) {
							$where[$key]=$_POST[$key];
							unset($_POST[$key]);
						} elseif(array_key_exists($key, $formConfig['data'])) {
							$where[$key]=$formConfig['data'][$key];
						} else {
							displayFormMsg("Incomplete submit condition");
						}
					}
				}

				$cols=array_flip($cols);
				foreach ($cols as $key => $value) {
					if(isset($_POST[$key])) {
						if(isset($formConfig['fields'][$key]['disabled']) && $formConfig['fields'][$key]['disabled']) {
							unset($cols[$key]);
							continue;
						}
						if(array_key_exists($key, $formConfig['data']) && md5($formConfig['data'][$key])==md5($_POST[$key])) {
							unset($cols[$key]);
							unset($_POST[$key]);
							continue;
						}
						$cols[$key]=$_POST[$key];
						unset($_POST[$key]);
					} else {
						unset($cols[$key]);
					}
				}
				if(count($cols)<=0) {
					displayFormMsg("No change found",'info');
				}
				//validation
				$cols=validateInput($cols,$formConfig['fields']);
				$cols=processInput($cols,$formConfig,$oriData);

				//printArray($cols);

				//insert/update detection
				$dbKey=$formConfig['dbkey'];
				switch ($formConfig['mode']) {
					case 'new':
					case 'insert':
						$sql=_db($dbKey)->_insertQ1($source['table'],$cols);

						if($sql->_run()) {
							$formConfig['mode']="update";
							$_SESSION['FORM'][$formKey]['mode']="update";
							displayFormMsg($cols,'success');
						} else {
							echo _db($dbKey)->get_error();
							displayFormMsg("Error updating database, try again later",'error');
						}
						break;
					
					case 'edit':
					case 'update':
						$sql=_db($dbKey)->_updateQ($source['table'],$cols,$where);

						if($sql->_run()) {
							displayFormMsg($cols,'success');
						} else {
							echo _db($dbKey)->get_error();
							displayFormMsg("Error updating database, try again later",'error');
						}
						break;

					default:
						displayFormMsg("Form mode could not be detected",'error');
						break;
				}
				
				break;
			
			default:
				displayFormMsg("Sorry, Form Source Type Not Supported.");
				break;
		}
		displayFormMsg("ALL GOOD");
	break;
	default:
		trigger_error("Action Not Defined or Not Supported");
}
function processInput($cols,$formConfig,$data) {
	foreach($formConfig['fields'] as $key=>$field) {
		if($formConfig['mode']=="update" && isset($field['disabled']) && $field['disabled']) {
			continue;
		}

		if(isset($field['concat']) && count($field['concat'])>0) {
			if(isset($data[$field['concat']['field']])) {
				if(!isset($cols[$key])) $cols[$key]="";
				switch ($field['concat']['position']) {
					case 'after':
						$cols[$key]=$data[$field['concat']['field']].$cols[$key];
						break;
					case 'before':
						$cols[$key].=$data[$field['concat']['field']];
						break;
					case 'after-with-space':
						$cols[$key]=$data[$field['concat']['field']]." ".$cols[$key];
						break;
					case 'before-with-space':
						$cols[$key].=" ".$data[$field['concat']['field']];
						break;
				}
			}
		}
	}
	return $cols;
}
function validateInput($cols,$formConfig) {
	foreach ($cols as $key => $value) {
		if(isset($formConfig[$key])) {
			$field=$formConfig[$key];
			if(isset($field['disabled']) && $field['disabled']) {
				unset($cols[$key]);
			}
			if(isset($field['required']) && $field['required']) {
				if($value==null || strlen($value)<=0) {
					displayFormMsg("Empty field found for required '$key'.",'error');
				}
			}
			if(isset($field['validate']) && is_array($field['validate'])) {
				foreach ($field['validate'] as $vkey => $rule) {
					switch ($vkey) {
						case 'email':
							$regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/';
							if($rule && !preg_match($regex, $value)) {
								displayFormMsg("Invalid $vkey format for '$key'.",'error');
							}
						break;
						case 'mobile':
							$regex="/^[1-9][0-9]*$/"; 
							if($rule && !preg_match($regex, $value)) {
								displayFormMsg("Invalid $vkey format for '$key'.",'error');
							}
						break;

						case 'regex':
							if($rule && !preg_match("/{$rule}/", $value)) {
								displayFormMsg("'$key' needs data to be in format : $rule.",'error');
							}
						break;

						case 'date':
							if($rule) {
								if(preg_match("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/", $value, $matches) || 
										preg_match("/([0-9]{2})-([0-9]{2})-([0-9]{4})/", $value, $matches)) {
									if (!checkdate($matches[2], $matches[1], $matches[3])) {
										displayFormMsg("'$key' needs to be a date : dd/MM/YYYY.",'error');
									} else {
										$cols[$key]=_date($value);
									}
								} else {
									displayFormMsg("'$key' needs to be a date : dd/MM/YYYY.",'error');
								}
							}
						break;
						case 'time':
							if($rule) {
								if(preg_match("/([0-9]{2}):([0-9]{2})/", $value, $matches) || 
										preg_match("/([0-9]{2})-([0-9]{2})/", $value, $matches)) {
									
								} else {
									displayFormMsg("'$key' needs to be a time : HH:MM.",'error');
								}
							}
						break;
						case 'timesec':
							if($rule) {
								if(preg_match("/([0-9]{2}):([0-9]{2}):([0-9]{2})/", $value, $matches)) {
									
								} else {
									displayFormMsg("'$key' needs to be a time : HH:MM:SEC.",'error');
								}
							}
						break;

						case 'number':case 'numeric':
							if($rule && !ctype_digit($value)) {
								displayFormMsg("Invalid $vkey format for '$key'.",'error');
							}
						break;
						case 'float':case 'decimal':
							if($rule && !is_float($value + 0)) {
								displayFormMsg("'$key' needs to be $rule decimal point number.",'error');
							} else {
								$cols[$key]=number_format($value,$rule);
							}
						break;
						case 'alphanumeric':
							if($rule && !ctype_alnum($value)) {
								displayFormMsg("Invalid $vkey format for '$key'.",'error');
							}
						break;
						case 'alpha':
							if($rule && !ctype_alpha($value)) {
								displayFormMsg("Invalid $vkey format for '$key'.",'error');
							}
						break;
						case 'upper':
							if($rule) {
								$cols[$key]=strtoupper($value);
							}
						break;
						case 'lower':
							if($rule) {
								$cols[$key]=strtolower($value);
							}
						break;

						case 'length-min':
							if(strlen($value)<$rule && strlen($value)>0) {
								displayFormMsg("Minimum length required for '$key' is $rule.",'error');
							}
						break;
						case 'length-max':
							if(strlen($value)>$rule && strlen($value)>0) {
								displayFormMsg("Maximum length required for '$key' is $rule.",'error');
							}
						break;
					}
				}
			}
		}
	}
	return $cols;
}
function displayFormMsg($msg,$type='error') {
	$formid=$_REQUEST['formid'];
	$msg=str_replace("'", '"', $msg);

	switch ($type) {
		case 'error':
			if(isset($_REQUEST['submitType']) && $_REQUEST['submitType']=="ajax") {
				trigger_error($msg);
			} else {
				echo "ERR:$msg";
				echo "<script>parent.formsSubmitStatus('$formid','$msg','error');</script>";
			}
			break;
		case 'info':
			if(isset($_REQUEST['submitType']) && $_REQUEST['submitType']=="ajax") {
				printServiceMsg(['type'=>'info','msg'=>$msg]);
			} else {
				echo "INF:$msg";
				echo "<script>parent.formsSubmitStatus('$formid','$msg','info');</script>";
			}
			break;
		case 'success':
			if(isset($_REQUEST['submitType']) && $_REQUEST['submitType']=="ajax") {
				printServiceMsg(['type'=>'success','msg'=>$msg]);
			} else {
				echo "MSG:Submitted/Updated Successfully";
				$msg=json_encode($msg);
				echo "<script>parent.formsSubmitStatus('$formid',$msg,'success');</script>";
			}
			break;
		default:
			if(isset($_REQUEST['submitType']) && $_REQUEST['submitType']=="ajax") {
				printServiceMsg(['type'=>'success','msg'=>$msg]);
			} else {
				echo "MSG:$msg";
				echo "<script>parent.formsSubmitStatus('$formid','$msg','success');</script>";
			}
	}
	exit();
}
function handleFileUpload($formConfig,$fs) {
	$formuid=$formConfig['formuid'];
	$attachmentFolder="formsStorage/$formuid/";
	
	$a=$fs->cd($attachmentFolder);
	if(!$a) {
		$fs->mkdir($attachmentFolder);
	}

	$files=[];
	$date=date("Y-m-d-H-i-s");

	$fields=$formConfig['fields'];
	foreach ($_FILES as $key => $finfo) {
		$ext=explode(".", $finfo['name']);
		$ext=$ext[count($ext)-1];

		if(isset($fields[$key])) {
			if($finfo['error']!=0) {
				displayFormMsg("Error uploading file.",'error');
			}
			if(isset($fields[$key]['mimes'])) {
				$mimes=explode(",", strtolower($fields[$key]['mimes']));
				if(!in_array($ext, $mimes)) {
					displayFormMsg("'$key' supports only ({$fields[$key]['mimes']}) file types.",'error');
				}
			}
			if(isset($fields[$key]['maxsize']) && $fields[$key]['maxsize']>0) {
				if($finfo['size']>$fields[$key]['maxsize']) {
					displayFormMsg("'$key' supports only {$fields[$key]['maxsize']} file size.",'error');
				}
			}
			$finalFileName="{$key}/{$date}/".md5($formuid.$_SESSION['SESS_USER_ID'].time())."_{$finfo['name']}";

			// if(isset($fields[$key]['filepath'])) {
			// } else {
			// }
			$x=$fs->upload($finfo['tmp_name'],$fs->pwd()."{$finalFileName}");
			if($x) {
				$files[$key]=$attachmentFolder.$finalFileName;
			} else {
				displayFormMsg("File for '$key' could not be moved form storage.",'error');
			}
		} else {
			unset($_FILES[$key]);
		}
	}
	foreach ($files as $key => $value) {
		$_POST[$key]=$value;
	}
	return $files;
}
?>
