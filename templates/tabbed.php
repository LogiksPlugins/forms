<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(count($fieldGroups)>1) {
	$groups=array_keys($fieldGroups);
	echo '<ul class="nav nav-tabs">';
	foreach ($groups as $nx=>$fkey) {
		$title=toTitle(_ling($fkey));
		if($nx==0) {
			echo "<li role='presentation' class='active'><a href='#{$fkey}' role='tab' aria-controls='{$fkey}'  data-toggle='tab'>{$title}</a></li>";
		} else {
			echo "<li role='presentation'><a href='#{$fkey}' role='tab' aria-controls='{$fkey}'  data-toggle='tab'>{$title}</a></li>";
		}
	}
	echo '</ul>';
	echo '<form class="form validate '.$formConfig['mode'].' '.($formConfig['simpleform']?"simple-form":"").'" method="POST" enctype="multipart/form-data" data-formkey="'.$formConfig["formkey"].'" data-glink="'.$formConfig['gotolink'].'" data-relink="'.$formConfig['reloadlink'].'" data-clink="'.$formConfig['cancellink'].'" >';
	echo '<div class="tab-content">';
	foreach ($groups as $nx=>$fkey) {
		if($nx==0) {
			echo "<div role='tabpanel' class='tab-pane active' id='{$fkey}'>";
		} else {
			echo "<div role='tabpanel' class='tab-pane' id='{$fkey}'>";
		}
		echo '<div class="formbox"><div class="formbox-content">';
		echo "<div class='row'>";
		echo getFormFieldset($fieldGroups[$fkey],$formData,$formConfig['dbkey'],$formConfig['mode']);
		echo "</div>";
		echo '</div></div>';
		echo "</div>";
	}
	echo '</div>';
	echo '<hr class="hr-normal">';
	echo '<div class="form-actions form-actions-padding"><div class="text-right">';
	echo getFormActions($formConfig['actions']);
	echo '</div></div>';
	echo '</form>';
} else {
	echo '<div class="formbox"><div class="formbox-content">';
	echo '<form class="form validate '.$formConfig['mode'].' '.($formConfig['simpleform']?"simple-form":"").'" method="POST" enctype="multipart/form-data" data-formkey="'.$formConfig["formkey"].'" data-glink="'.$formConfig['gotolink'].'" data-relink="'.$formConfig['reloadlink'].'" data-clink="'.$formConfig['cancellink'].'" >';
	echo "<div class='row'>";
	echo getFormFieldset($formConfig['fields'],$formData,$formConfig['dbkey'],$formConfig['mode']);
	echo "</div>";
	echo '<hr class="hr-normal">';
	echo '<div class="form-actions form-actions-padding"><div class="text-right">';
	echo getFormActions($formConfig['actions']);
	echo '</div></div>';
	echo '</form></div></div>';
}
echo "<script>if(typeof initFormUI=='function' && typeof $.fn.sortable=='function') {initFormUI();} else $(function() {initFormUI();});</script>";
?>
