<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("validateInput")) {
	function validateInput($cols,$formConfig) {
		foreach ($cols as $key => $value) {
			if(isset($formConfig[$key])) {
				$field=$formConfig[$key];
				if(isset($field['disabled']) && $field['disabled']) {
					unset($cols[$key]);
				}
				if(isset($field['required']) && $field['required']) {
					if($value==null) {
						displayFormMsg("Empty field found for required '$key'.",'error');
					} elseif(is_array($value) && count($value)<=0) {
						displayFormMsg("Empty field found for required '$key'.",'error');
					} elseif(!is_array($value) && strlen($value)<=0) {
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
}
?>