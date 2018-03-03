<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!isset($_REQUEST["action"])) {
	$_REQUEST["action"]="";
}
if(!isset($_REQUEST['formid'])) {
	trigger_error("Form Not Found");
}
include_once __DIR__."/api.php";

if(!defined("APPS_USERDATA_FOLDER")) define("APPS_USERDATA_FOLDER","usermedia/");

switch($_REQUEST["action"]) {
	case "autocomplete":
		$formKey=$_REQUEST['formid'];
		if(!isset($_SESSION['FORMAUTOCOMPLETE'][$formKey])) {
			printServiceMsg([]);
			return;
		}
		if(!isset($_REQUEST['srcname']) || !isset($_SESSION['FORMAUTOCOMPLETE'][$formKey][$_REQUEST['srcname']])) {
			printServiceMsg([]);
			return;
		}
		$src=$_SESSION['FORMAUTOCOMPLETE'][$formKey][$_REQUEST['srcname']];
		$data=[];
		if(isset($src['table']) && isset($src['columns'])) {
			if(isset($src['where'])) {
				if(is_array($src['where'])) {
					foreach($src['where'] as $aaa=>$bbb) {
						$src['where'][$aaa]=_replace($bbb);
					}
				} else {
					$src['where']=_replace($src['where']);
				}
			} else {
				$src['where']=[];
			}

			$data=_db()->_selectQ($src['table'],$src['columns'],$src['where']);
			if(isset($src['orderby'])) {
				$data=$data->_orderby($src['orderby']);
			} elseif(isset($src['sortby'])) {
				$data=$data->_orderby($src['sortby']);
			}

			if(isset($src['groupby'])) {
				$data=$data->_groupby($src['groupby']);
			} else {
				if(!is_array($src['columns'])) {
					$gCols=explode(",",$src['columns']);
				} else {
					$gCols=$src['columns'];
				}
				$gCols[0]=explode(" ",$gCols[0]);
				$data=$data->_groupby($gCols[0][0]);
			}

			if(!isset($src['limit'])) {
				$src['limit']=100;
			}

			$data=$data->_limit($src['limit'],0)->_GET();

			if(isset($_REQUEST['type']) && strtolower($_REQUEST['type'])=="raw") {
		
			} elseif(isset($_REQUEST['type']) && strtolower($_REQUEST['type'])=="single") {
				$data=$data[0];
			} else {
				$fData=[];
				foreach ($data as $key => $row) {
					$fData[$row['title']]=$row['value'];
				}
				$data=$fData;
			}
			
		} elseif(isset($src['file']) && file_exists(APPROOT.$src['file'])) {
			$data=include_once APPROOT.$src['file'];
		}
		printServiceMsg($data);
	break;
	case "empty":
		if(!isset($_POST['field'])) {
			displayFormMsg("Sorry, form field not defined.");
		}
		$formKey=$_REQUEST['formid'];
		if(!isset($_SESSION['FORM'][$formKey])) {
			displayFormMsg("Sorry, form key not found.");
		}

		$formConfig=$_SESSION['FORM'][$formKey];
		if(isset($formConfig['fields'][$_POST['field']])) {
			$field=$formConfig['fields'][$_POST['field']];
			$source=$formConfig['source'];

			if(isset($field['disabled']) && $field['disabled']) {
				displayFormMsg("Sorry, disabled field not supported.");
			} else {
				if(!isset($formConfig['submit']) && !isset($formConfig['submit']['type'])) {
					$formConfig['submit']['type']="sql";
				}

				switch($formConfig['submit']['type']) {
					case "php":
						$file=APPROOT.$formConfig['submit']['file'];
						if(file_exists($file) && is_file($file)) {
							include_once($file);
						} else {
							displayFormMsg("Sorry, Form Submit Source File Not Found.");
						}
						break;
					case "sql":
						$where=[];
						$dbKey=$formConfig['dbkey'];
						if(isset($formConfig['source']['where_auto']) && is_array($formConfig['source']['where_auto'])) {
							$where=array_merge($where,$formConfig['source']['where_auto']);
						}
						if(count($where)>0) {
							$sqlRes=_db($dbKey)->_updateQ($source['table'],[$_POST['field']=>"","edited_by"=>$_SESSION['SESS_USER_ID'],"edited_on"=>date("Y-m-d H:i:s")],$where)->_RUN();
							if($sqlRes) {
								displayFormMsg([$_POST['field']=>""],'success');
							} else {
								displayFormMsg("Sorry, Update failed, try again later.");
							}
						} else {
							displayFormMsg("Sorry, Condition not satisfied.");
						}
						break;
					default:
						displayFormMsg("Sorry, Form Submit Type Not Supported.");
				}
			}
		} else {
			displayFormMsg("Sorry, form field not supported.");
		}
		break;
	case "submit":
		$formKey=$_REQUEST['formid'];
		if(!isset($_SESSION['FORM'][$formKey])) {
			displayFormMsg("Sorry, form key not found.");
		}

		$formConfig=$_SESSION['FORM'][$formKey];
		processFormHook("dataposted",["config"=>$formConfig,"mode"=>$formConfig['mode']]);
		
		if(!isset($formConfig['source']) || !isset($formConfig['source']['type'])) {
			displayFormMsg("Sorry, Form Submit Source Not Found.");
		}
		
		if(isset($_REQUEST['forsite']) && in_array($_REQUEST['forsite'],$_SESSION['SESS_ACCESS_SITES'])) {
			$fs=_fs($_REQUEST['forsite'],[
					"driver"=>"local",
					"basedir"=>ROOT.APPS_FOLDER.$_REQUEST['forsite']."/".APPS_USERDATA_FOLDER
				]);
		} else {
// 			$fs=_fs(SITENAME,[
// 					"driver"=>"local",
//         	"basedir"=>ROOT.APPS_FOLDER.SITENAME."/".APPS_USERDATA_FOLDER
// 				]);
			$fs=_fs();
			$fs->cd(APPS_USERDATA_FOLDER);
		}

// 			printArray($formConfig);
		//printArray($_POST);exit();
		$files=handleFileUpload($formConfig,$fs);

		$source=$formConfig['source'];
		switch ($source['type']) {
			case 'sql':
				$cols=array_keys($formConfig['fields']);
				$where=$source['where'];
				if(!is_array($where)) $where=explode(",", $where);

				$oriData=$_POST;
				$oriData=array_merge($formConfig['data'],$oriData);

				if($formConfig['mode']=="update" || $formConfig['mode']=="edit") {
					$where=array_flip($where);
					foreach ($where as $key => $value) {
						if(isset($_POST[$key])) {
							//$where[$key]=$_POST[$key];
							unset($_POST[$key]);
						}
						if(isset($cols[$key])) {
							//$where[$key]=$_POST[$key];
							unset($cols[$key]);
						}

						if(array_key_exists($key, $formConfig['data'])) {
							$where[$key]=$formConfig['data'][$key];
						} else {
							unset($where[$key]);
						}
					}
					if(isset($formConfig['source']['where_auto']) && is_array($formConfig['source']['where_auto'])) {
						$where=array_merge($where,$formConfig['source']['where_auto']);
					}

					if(count($where)<=0) {
						displayFormMsg("Incomplete submit condition");
					}
				}

				$cols=array_flip($cols);
				foreach ($cols as $key => $value) {
					if(isset($_POST[$key])) {
// 						if(isset($formConfig['fields'][$key]['disabled']) && $formConfig['fields'][$key]['disabled']) {
// 							unset($cols[$key]);
// 							continue;
// 						}
						if(isset($formConfig['fields'][$key]['nofill']) && $formConfig['fields'][$key]['nofill']) {
							unset($cols[$key]);
							continue;
						}
						/*if(array_key_exists($key, $formConfig['data']) && md5($formConfig['data'][$key])==md5($_POST[$key])) {
							unset($cols[$key]);
							unset($_POST[$key]);
							continue;
						}*/
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

				//Merge With fixed data that needs autofilling
				$cols=mergeFixedData($cols,$formConfig,$oriData);

				//printArray($cols);exit();

				//insert/update detection
				$dbKey=$formConfig['dbkey'];
				switch ($formConfig['mode']) {
					case 'new':
					case 'insert':
						processFormHook("preSubmit",["config"=>$formConfig,"data"=>$cols,"mode"=>"new"]);
						$sql=_db($dbKey)->_insertQ1($source['table'],$cols);

						if($sql->_run()) {
							$whereNew=['id'=>_db($dbKey)->get_insertID()];
							finalizeSubmit($formConfig,$cols,$whereNew);
							
							$formConfig['mode']="update";
							$_SESSION['FORM'][$formKey]['mode']="update";
							$_SESSION['FORM'][$formKey]['source']['where_auto']=$whereNew;
							
							$_REQUEST['hashid']=md5($whereNew['id']);
							displayFormMsg($cols,'success',$formConfig['gotolink']);
						} else {
							$msg=_db($dbKey)->get_error();
							$msgMicro=explode(" ", strtolower($msg));
							if($msgMicro[0]=="duplicate") {
								$msgX=$msgMicro[count($msgMicro)-1]." is unique and a record already exists.";
								echo $msg;
								displayFormMsg($msgX,'error');
							} else {
								echo $msg;
								displayFormMsg("Error updating database, try again later",'error');
							}
						}
						break;

					case 'edit':
					case 'update':
						processFormHook("preSubmit",["config"=>$formConfig,"data"=>$cols,"where"=>$where,"mode"=>"edit"]);

						$sql=_db($dbKey)->_updateQ($source['table'],$cols,$where);
						//displayFormMsg($sql->_SQL());exit();
						if($sql->_run()) {
							if(isset($where['md5(id)'])) {
								$_REQUEST['hashid']=$where['md5(id)'];
							} else {
								$sqlData=_db($dbKey)->_selectQ($source['table'],'md5(id) as hashid',$where)->_GET();
								if(isset($sqlData[0]) && isset($sqlData[0]['hashid'])) {
									$_REQUEST['hashid']=$sqlData[0]['hashid'];
								}
							}
							
							finalizeSubmit($formConfig,$cols,$where);
							displayFormMsg($cols,'success',$formConfig['gotolink']);
						} else {
							echo _db($dbKey)->get_error();
							displayFormMsg("Error updating database, try again later",'error');
						}
						break;

					default:
						displayFormMsg("Form mode could not be detected",'error');
						break;
				}
				displayFormMsg("ALL GOOD");
				break;
			case "php":
				$file=APPROOT.$formConfig['source']['file'];
				if(file_exists($file) && is_file($file)) {
					$data=include_once($file);
					displayFormMsg($data,'success',$formConfig['gotolink']);
				} else {
					displayFormMsg("Sorry, Form Submit Source File Not Found.");
				}
				break;
			default:
				displayFormMsg("Sorry, Form Source Type Not Supported.");
				break;
		}
	break;
	default:
		trigger_error("Action Not Defined or Not Supported");
}
function finalizeSubmit($formConfig,$data,$where) {
// 	printArray($formConfig['mode']);
	if(isset($formConfig['slavetable']) && $formConfig['mode']=="new" && isset($where['id'])) {
		if(!is_array($formConfig['slavetable'])) $formConfig['slavetable']=explode(",",$formConfig['slavetable']);
		foreach($formConfig['slavetable'] as $tbl=>$keys) {
			if(is_array($keys)) {

			} else {
				$ans=_db()->_insertQ1($tbl,[$keys=>$where['id']])->_RUN();
			}
		}
	}

	$_ENV['FORMSUBMIT']=["data"=>$data,"where"=>$where,"config"=>$formConfig,"mode"=>$formConfig['mode']];
	processFormHook("postSubmit",$_ENV['FORMSUBMIT']);
}
function mergeFixedData($cols,$formConfig,$data) {
	//printArray($formConfig);
	$user="guest";
	if(isset($_SESSION['SESS_USER_ID'])) $user=$_SESSION['SESS_USER_ID'];
	$guid="global";
	if(isset($_SESSION['SESS_GUID'])) $guid=$_SESSION['SESS_GUID'];

	$defaultArr=[
			"guid"=>$guid,
			"edited_by"=>$user,
			"edited_on"=>date("Y-m-d H:i:s"),
			"timestamp"=>date("Y-m-d H:i:s"),
			"client_ip"=>$_SERVER['REMOTE_ADDR'],
			"user_agent"=>$_SERVER['HTTP_USER_AGENT'],
			"sitename"=>SITENAME,
		];

	if(isset($formConfig['NOGUID']) && $formConfig['NOGUID']) unset($defaultArr['guid']);
	elseif(getConfig("FORMS_NOGUID")) unset($defaultArr['guid']);

	if($_SESSION['SESS_PRIVILEGE_ID']<=ROLE_PRIME) {
		if(isset($cols['guid'])) {
			$defaultArr['guid']=$cols['guid'];
		}
		if(isset($cols['timestamp'])) {
			$defaultArr['timestamp']=$cols['timestamp'];
		}
	}

	if(!isset($formConfig['mode']) || $formConfig['mode']=="new" || $formConfig['mode']=="insert") {
		$defaultArr["created_on"]=date("Y-m-d H:i:s");
		$defaultArr["created_by"]=$user;
	}

	//printArray([$cols,$formConfig,$data]);
	if(!isset($formConfig['autofill'])) $formConfig['autofill']="guid,created_by,edited_by,created_on,edited_on";
	if(!is_array($formConfig['autofill'])) $formConfig['autofill']=explode(",",$formConfig['autofill']);

	foreach($formConfig['autofill'] as $key) {
		if(isset($defaultArr[$key])) {
			$cols[$key]=$defaultArr[$key];
		}
	}

	if(!isset($formConfig['forcefill'])) $formConfig['forcefill']=[];
	foreach($formConfig['forcefill'] as $key=>$val) {
		$cols[$key]=_replace($val);
	}

	if(!isset($formConfig['nofill'])) $formConfig['nofill']=[];
	foreach($formConfig['nofill'] as $key) {
		unset($cols[$key]);
	}

	return $cols;
}
function processInput($cols,$formConfig,$data) {
	foreach($formConfig['fields'] as $key=>$field) {
		if($formConfig['mode']=="update" && isset($field['disabled']) && $field['disabled']) {
			continue;
		}
		//printArray($data[$key]);echo $key;
		if(isset($field['type']) && $field['type']=="jsonfield") {
			$table=[];
			if(isset($data[$key]) && is_array($data[$key])) {
				$keys=array_keys($data[$key]);
				foreach($data[$key][$keys[0]] as $a1=>$b1) {
					foreach($keys as $k1) {
						if(!isset($data[$key][$k1]) || !isset($data[$key][$k1][$a1])) $data[$key][$k1][$a1]="";
						$table[$a1][$k1]=$data[$key][$k1][$a1];
					}
				}
				$cols[$key]=json_encode($table);
			} else {
				$cols[$key]="[]";
			}
		} elseif(isset($data[$key]) && is_array($data[$key])) {
			$cols[$key]=implode(",",$data[$key]);
		}

		if(isset($field['concat']) && count($field['concat'])>0) {
			$concatData="";$concatSeparator="";
			if(!isset($cols[$key])) $cols[$key]="";

			if(!isset($field['concat']['position'])) {
				$field['concat']['position']="after";
			}
			if(isset($data[$field['concat']['field']])) {
				$concatData=$data[$field['concat']['field']];
			}
			if(isset($field['concat']['separator']) && isset($data[$field['concat']['separator']])) {
				$concatSeparator=$data[$field['concat']['separator']];
			}

			switch ($field['concat']['position']) {
				case 'after':
					$cols[$key]=$concatData.$concatSeparator.$cols[$key];
					break;
				case 'before':
					$cols[$key].=$concatSeparator.$concatData;
					break;
			}
		}

		if(isset($field['type'])) {
			switch(strtolower($field['type'])) {
				case "date":
					$cols[$key]=_date($cols[$key],"d/m/Y","Y-m-d");
					break;
				case "datetime":
					$dtArr=explode(" ",$cols[$key]);
					$cols[$key]=_date($dtArr[0],"d/m/Y","Y-m-d")." {$dtArr[1]}";
					break;
			}
		}
	}
	return $cols;
}

function displayFormMsg($msg,$type='error',$gotoLink="") {
	$formid=$_REQUEST['formid'];
	$msg=str_replace("'", '"', $msg);

	switch ($type) {
		case 'error':
			if(isset($_REQUEST['submitType']) && $_REQUEST['submitType']=="ajax") {
				trigger_error($msg);
			} else {
				echo "ERR:$msg";
				echo "<script>parent.formsSubmitStatus('$formid','$msg','error','$gotoLink');</script>";
			}
			break;
		case 'info':
			if(isset($_REQUEST['submitType']) && $_REQUEST['submitType']=="ajax") {
				printServiceMsg(['type'=>'info','msg'=>$msg]);
			} else {
				echo "INF:$msg";
				echo "<script>parent.formsSubmitStatus('$formid','$msg','info','$gotoLink');</script>";
			}
			break;
		case 'success':
			if(isset($_REQUEST['submitType']) && $_REQUEST['submitType']=="ajax") {
				printServiceMsg(['type'=>'success','msg'=>$msg]);
			} else {
				if($gotoLink!=null && strlen($gotoLink)>0) {
					$gotoLink=_replace(str_replace("{","#",str_replace("}","#",$gotoLink)));
				}
				
				echo "MSG:Submitted/Updated Successfully";
				foreach($msg as $a=>$b) {
					if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$b)) {
						$msg[$a]=_pDate($b,"d/m/Y");
					}
				}
				$msg=json_encode($msg);
				echo "<script>parent.formsSubmitStatus('$formid',$msg,'success','$gotoLink');</script>";
			}
			break;
		default:
			if(isset($_REQUEST['submitType']) && $_REQUEST['submitType']=="ajax") {
				printServiceMsg(['type'=>'success','msg'=>$msg]);
			} else {
				echo "MSG:$msg";
				echo "<script>parent.formsSubmitStatus('$formid','$msg','success','$gotoLink');</script>";
			}
	}
	exit();
}
function handleFileUpload($formConfig,$fs) {
	if(!isset($_FILES) || count($_FILES)<=0) return [];
	
	$formuid=$formConfig['formuid'];
	$date=date("Y-m-d");
	$attachmentFolder="formsStorage/{$formuid}/";
	//$attachmentFolderAbsolute=APPROOT."usermedia/{$attachmentFolder}";
	$appFolder=APPROOT;
	if(defined("CMS_APPROOT")) {
		$appFolder=CMS_APPROOT;
	}

	$a=$fs->cd($attachmentFolder,true);
	$attachmentFolderAbsolute=$fs->pwd();
	
	$files=[];
	$date=date("Y-m-d-H-i-s");

	$fields=$formConfig['fields'];
	foreach ($_FILES as $key => $upFiles) {
		if(is_array($upFiles['name'])) {
			foreach($upFiles['name'] as $k=>$f) {
				$finfo=[
					"name"=>$upFiles['name'][$k],
					"type"=>$upFiles['type'][$k],
					"tmp_name"=>$upFiles['tmp_name'][$k],
					"error"=>$upFiles['error'][$k],
					"size"=>$upFiles['size'][$k],
				];

				$fname=$ext=explode(".", $finfo['name']);
				$ext=$ext[count($ext)-1];
				$fname=array_splice($fname,0,count($fname)-1);
				$fname=implode(".",$fname);
				$fname=cleanSpecial($fname);

				if(isset($fields[$key])) {
					if($finfo['error']==4) continue;
					if($finfo['error']!=0) {
						displayFormMsg("Error uploading file (0).",'error');
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

					$finalFileName="{$key}/{$date}/".md5($formuid.$_SESSION['SESS_USER_ID'].time())."_{$fname}.".strtolower($ext);

					// if(isset($fields[$key]['filepath'])) {
					// } else {
					// }
					$x=$fs->upload($finfo['tmp_name'],$attachmentFolderAbsolute.$finalFileName);
					if($x) {
						$files[$key][]=$attachmentFolder.$finalFileName;
					} else {
						displayFormMsg("File for '$key' could not be moved form storage.",'error');
					}
				} else {

				}
			}
			unset($_FILES[$key]);
		} else {
			$finfo=$upFiles;

			$ext=explode(".", $finfo['name']);
			$ext=$ext[count($ext)-1];

			if(isset($fields[$key])) {
				if($finfo['error']==4) continue;
				if($finfo['error']!=0) {
					displayFormMsg("Error uploading file (0).",'error');
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

				$x=$fs->upload($finfo['tmp_name'],$attachmentFolderAbsolute.$finalFileName);
				if($x) {
					$files[$key]=$attachmentFolder.$finalFileName;
				} else {
					displayFormMsg("File for '$key' could not be moved form storage.",'error');
				}
			} else {
				unset($_FILES[$key]);
			}
		}
	}
	//printArray($files);
	foreach ($files as $key => $value) {
		//$value=str_replace("#{$appFolder}","","#{$value}");
		if(isset($_POST[$key]) && is_array($_POST[$key])) {
			if(is_array($value)) {
				foreach($value as $v) $_POST[$key][]=$v;
			} else {
				$_POST[$key][]=$value;
			}
		} else {
			$_POST[$key]=$value;
		}
		if(is_array($_POST[$key])) {
			$_POST[$key]=array_unique($_POST[$key]);
		}
	}
	return $files;
}
?>

