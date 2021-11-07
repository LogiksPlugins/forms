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
	case "ajaxdropdown":
		$formKey=$_REQUEST['formid'];
		if(!isset($_REQUEST['srcname']) || !isset($_SESSION['FORM'][$formKey]['fields'][$_REQUEST['srcname']])) {
			printServiceMsg([]);
			return;
		}

		$src=$_SESSION['FORM'][$formKey]['fields'][$_REQUEST['srcname']];
		
		if(!isset($src['type'])) {
			printServiceMsg([]);
			return;
		}

		switch(strtolower($src['type'])) {
			case "dataselectorfromtable":
				if(isset($src['table']) || isset($src['columns'])) {
					if(!is_array($src['columns'])) {
						$searchColumns=preg_replace("/[a-zA-Z0-9]+\([a-zA-Z0-9-_'\",\[\] ]+\)[a-zA-Z0-9-_ ]+,/", "", $src['columns']);
						$cols=explode(",",$searchColumns);
					} else {
						$cols=$src['columns'];
						foreach($cols as $k=>$a) {
							if(strpos($a,"(")>0) {
								unset($cols[$k]);
							}
						}
					}
					
					if(!isset($src['where'])) {
						$src['where'] = ["blocked" => "false"];
					}
					
					$sqlData=_db()->_selectQ($src['table'],$src['columns'],$src['where']);
					if(isset($src['where']) && $src['where']) {
						$sqlData->_where($src['where']);
					}
					$sqlData=$sqlData->_limit(10)->_GET();
					
					printServiceMsg($sqlData);
				}
				break;
			case "dataselectorfromuniques":
				if(isset($src['table']) || isset($src['columns'])) {
					if(!is_array($src['columns'])) {
						$searchColumns=preg_replace("/[a-zA-Z0-9]+\([a-zA-Z0-9-_'\",\[\] ]+\)[a-zA-Z0-9-_ ]+,/", "", $src['columns']);
						$cols=explode(",",$searchColumns);
					} else {
						$cols=$src['columns'];
						foreach($cols as $k=>$a) {
							if(strpos($a,"(")>0) {
								unset($cols[$k]);
							}
						}
					}
					
					if(!isset($src['where'])) {
						$src['where'] = ["blocked" => "false"];
					}
					
					$sqlData=_db()->_selectQ($src['table'],$src['columns'],$src['where']);
					$sqlData=$sqlData->_limit(10)->_GET();
					
					printServiceMsg($sqlData);
				}
				break;
			default:
				printServiceMsg([]);
				return;
		}
		break;
	case "dropsearch":
		$formKey=$_REQUEST['formid'];
		if(!isset($_REQUEST['srcname']) || !isset($_SESSION['FORM'][$formKey]['fields'][$_REQUEST['srcname']])) {
			printServiceMsg([]);
			return;
		}
		if(isset($_POST['v']) && ($_POST['v']=="0" || strlen($_POST['v'])<=0)) {
	      	printServiceMsg([]);
			return;
	    }
	    if(isset($_POST['q']) && strlen($_POST['q'])<=0) {
	      	printServiceMsg([]);
			return;
	    }
		//$src=$_SESSION['FORMAUTOCOMPLETE'][$formKey][$_REQUEST['srcname']];
		$src=$_SESSION['FORM'][$formKey]['fields'][$_REQUEST['srcname']];
		
		if(!isset($src['type'])) {
			printServiceMsg([]);
			return;
		}
		
		switch(strtolower($src['type'])) {
			case "dataselectorfromtable":
				if(isset($src['table']) || isset($src['columns'])) {
					if(!is_array($src['columns'])) {
						$searchColumns=preg_replace("/[a-zA-Z0-9]+\([a-zA-Z0-9-_'\",\[\] ]+\)[a-zA-Z0-9-_ ]+,/", "", $src['columns']);
						$cols=explode(",",$searchColumns);
					} else {
						$cols=$src['columns'];
						foreach($cols as $k=>$a) {
							if(strpos($a,"(")>0) {
								unset($cols[$k]);
							}
						}
					}
					
					$whr=[];
					if(isset($_POST['q'])) {
						foreach($cols as $a=>$b) {
							$b=explode(" as ",$b);
							$cols=$b[0];
							$whr[$b[0]]=[$_POST['q'],"SW"];
						}
					} elseif(isset($_POST['v'])) {
						foreach($cols as $a=>$b) {
							$b=explode(" as ",$b);
							$cols=$b[0];
							$whr[$b[0]]=$_POST['v'];
						}
					}

					if(!isset($src['where'])) {
						$src['where'] = ["blocked" => "false"];
					}
					
					$sqlData=_db()->_selectQ($src['table'],$src['columns'],$src['where'])->_WHERE($whr,"AND","OR");
					if(isset($src['where']) && $src['where']) {
						$sqlData->_where($src['where']);
					}
					$sqlData=$sqlData->_limit(10)->_GET();
					
					printServiceMsg($sqlData);
				}
				break;
			case "dataselectorfromuniques":
				if(isset($src['table']) || isset($src['columns'])) {
					if(!is_array($src['columns'])) {
						$searchColumns=preg_replace("/[a-zA-Z0-9]+\([a-zA-Z0-9-_'\",\[\] ]+\)[a-zA-Z0-9-_ ]+,/", "", $src['columns']);
						$cols=explode(",",$searchColumns);
					} else {
						$cols=$src['columns'];
						foreach($cols as $k=>$a) {
							if(strpos($a,"(")>0) {
								unset($cols[$k]);
							}
						}
					}
					
					$whr=[];
					if(isset($_POST['q'])) {
						foreach($cols as $a=>$b) {
							$b=explode(" as ",$b);
							$cols=$b[0];
							$whr[$b[0]]=[$_POST['q'],"SW"];
						}
					} elseif(isset($_POST['v'])) {
						foreach($cols as $a=>$b) {
							$b=explode(" as ",$b);
							$cols=$b[0];
							$whr[$b[0]]=$_POST['v'];
						}
					}

					if(!isset($src['where'])) {
						$src['where'] = ["blocked" => "false"];
					}
					
					$sqlData=_db()->_selectQ($src['table'],$src['columns'],$src['where'])->_WHERE($whr,"AND","OR");
					$sqlData=$sqlData->_limit(10)->_GET();
					
					printServiceMsg($sqlData);
				}
				break;
			default:
				printServiceMsg([]);
				return;
		}
		break;
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
				if(isset($data[0])) $data=$data[0];
				else $data=[];
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
						if(file_exists($formConfig['submit']['file']) && is_file($formConfig['submit']['file'])) {
							$data = include_once($formConfig['submit']['file']);
							displayFormMsg($data,'success',$formConfig['gotolink']);
						} elseif(file_exists($file) && is_file($file)) {
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

		//printArray($formConfig);
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

						if(count($where)<=0) {
							displayFormMsg("Error in where condition, try again !");
						}

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
							// echo _db($dbKey)->get_error();
							displayFormMsg("Error updating database, try again later",'error');
						}
						break;

					default:
						displayFormMsg("Form mode could not be detected",'error');
						break;
				}
				displayFormMsg("Data created successfully",'success');
				break;
			case "php":
				$file=APPROOT.$formConfig['source']['file'];
				if(file_exists($formConfig['source']['file']) && is_file($formConfig['source']['file'])) {
					$data = include_once($formConfig['source']['file']);
					displayFormMsg($data,'success',$formConfig['gotolink']);
				} elseif(file_exists($file) && is_file($file)) {
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
?>
