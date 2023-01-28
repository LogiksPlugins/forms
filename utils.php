<?php
if(!defined('ROOT')) exit('No direct script access allowed');

include_once __DIR__."/validation.php";

if(!function_exists("mergeFixedData")) {
	
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
				$_REQUEST[$key]=$cols[$key];
			}
		}

		if(!isset($formConfig['forcefill'])) $formConfig['forcefill']=[];
		foreach($formConfig['forcefill'] as $key=>$val) {
			if(substr($val, 0, 9)=="#AUTOGEN:") {
				$cols[$key]=generateAutoNumber($key, $val);
				$_REQUEST[$key]=$cols[$key];
				continue;
			} elseif(substr($val, 0, 9)=="#ROWHASH:") {
				$cols[$key]=generateRowHash($key, $val);
				$_REQUEST[$key]=$cols[$key];
				continue;
			}
			$cols[$key]=_replace($val);
			$_REQUEST[$key]=$cols[$key];
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
			if(!isset($data[$key])) {
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
						if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$cols[$key])) {
						    
						} elseif (preg_match("/^[0-9]{4}\/(0[1-9]|1[0-2])\/(0[1-9]|[1-2][0-9]|3[0-1])$/",$cols[$key])) {
						    
						} else {
							$cols[$key]=_date($cols[$key],"d/m/Y","Y-m-d");
						}
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
		
		switch ($type) {
			case 'error':
				$msg=str_replace("'", '"', $msg);
				if(isset($_REQUEST['submitType']) && $_REQUEST['submitType']=="ajax") {
					trigger_error($msg);
				} else {
					echo "ERR:$msg";
					echo "<script>parent.formsSubmitStatus('$formid','$msg','error','$gotoLink');</script>";
				}
				break;
			case 'info':
				$msg=str_replace("'", '"', $msg);
				if(isset($_REQUEST['submitType']) && $_REQUEST['submitType']=="ajax") {
					printServiceMsg(['type'=>'info','msg'=>$msg]);
				} else {
					echo "INF:$msg";
					echo "<script>parent.formsSubmitStatus('$formid','$msg','info','$gotoLink');</script>";
				}
				break;
			case 'success':
				if(isset($_REQUEST['submitType']) && $_REQUEST['submitType']=="ajax") {
					$msg=str_replace("'", '"', $msg);
					printServiceMsg(['type'=>'success','msg'=>$msg]);
				} else {
					if($gotoLink!=null && strlen($gotoLink)>0) {
						$gotoLink=_replace(str_replace("{","#",str_replace("}","#",$gotoLink)));
					}
					
					echo "MSG:Submitted/Updated Successfully";
					if(is_array($msg)) {
						foreach($msg as $a=>$b) {
							if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$b)) {
								$msg[$a]=_pDate($b,"d/m/Y");
							}
						}
						$msg=json_encode($msg);
						echo "<script>parent.formsSubmitStatus('$formid',$msg,'success','$gotoLink');</script>";
					} else {
						$msg=str_replace("'", '"', $msg);
						echo "<script>parent.formsSubmitStatus('$formid','$msg','success','$gotoLink');</script>";
					}
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
		if(isset($_GET['NOEXIT'])) return;
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
}
?>