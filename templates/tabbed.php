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
	echo '<form class="form validate">';
	echo '<div class="tab-content">';
	foreach ($groups as $nx=>$fkey) {
		if($nx==0) {
			echo "<div role='tabpanel' class='tab-pane active' id='{$fkey}'>";
		} else {
			echo "<div role='tabpanel' class='tab-pane' id='{$fkey}'>";
		}
		echo '<div class="formbox"><div class="formbox-content">';
		echo "<div class='row'>";
		echo getFormFieldset($fieldGroups[$fkey],$formData,$formConfig['dbkey']);
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
	echo '<div class="formbox"><div class="formbox-content"><form class="form validate">';
	echo "<div class='row'>";
	echo getFormFieldset($formConfig['fields'],$formData,$formConfig['dbkey']);
	echo "</div>";
	echo '<hr class="hr-normal">';
	echo '<div class="form-actions form-actions-padding"><div class="text-right">';
	echo getFormActions($formConfig['actions']);
	echo '</div></div>';
	echo '</form></div></div>';
}

?>