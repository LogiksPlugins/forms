<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(count($fieldGroups)>1) {
	if(isset($fieldGroups['common'])) {
		$cgroup = $fieldGroups['common'];
		unset($fieldGroups['common']);

		$groups=array_keys($fieldGroups);

		$fieldGroups['common'] = $cgroup;
	} else {
		$groups=array_keys($fieldGroups);
	}
	// printArray($fieldGroups);

	echo '<form class="form validate '.$formConfig['mode'].' '.($formConfig['simpleform']?"simple-form":"").'" method="POST" enctype="multipart/form-data" data-formkey="'.$formConfig["formkey"].'" data-glink="'.$formConfig['gotolink'].'" data-relink="'.$formConfig['reloadlink'].'" data-clink="'.$formConfig['cancellink'].'" >';
	if(isset($fieldGroups['common'])) {
		echo "<div role='commonpanel' class='panel form-panel'>";
		echo '<div class="formbox"><div id="'.$formConfig["formkey"].'" class="formbox-content">';
		echo "<div class='row'>";

		$hasAvatar = array_search("avatar", array_column($fieldGroups['common'], 'type'));
		
		if($hasAvatar!==false) {
			$fieldSet1 = $fieldGroups["common"];
			unset($fieldSet1[$hasAvatar]);
			
			$avatarField = $fieldGroups["common"][$hasAvatar];
			$avatarField['width'] = 12;

			echo "<div class='col-xs-12 col-md-3 col-lg-2'>";
			echo getFormFieldset([$avatarField],$formData,$formConfig['dbkey'],$formConfig['mode']);
			echo "</div>";
			echo "<div class='col-xs-12 col-md-9 col-lg-10'>";
			echo getFormFieldset($fieldSet1,$formData,$formConfig['dbkey'],$formConfig['mode']);
			echo "</div>";
		} else {
			echo getFormFieldset($fieldGroups["common"],$formData,$formConfig['dbkey'],$formConfig['mode']);
		}
		echo "</div>";
		echo '</div></div>';
		echo "</div>";
	}
	echo '<ul class="nav nav-tabs">';
	foreach ($groups as $nx=>$fkey) {
		if(strtolower($fkey)=="common") continue;
		$title=toTitle(_ling($fkey));
		if($nx==0) {
			echo "<li role='presentation' class='active'><a href='#{$fkey}' role='tab' aria-controls='{$fkey}'  data-toggle='tab'>{$title}</a></li>";
		} else {
			echo "<li role='presentation'><a href='#{$fkey}' role='tab' aria-controls='{$fkey}'  data-toggle='tab'>{$title}</a></li>";
		}
	}
	echo '</ul>';
	echo '<div class="tab-content">';
	foreach ($groups as $nx=>$fkey) {
		if(strtolower($fkey)=="common") continue;
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
	echo getFormActions($formConfig['actions'],$formConfig);
	echo '</div></div>';
	echo '</form>';
} else {
	echo '<div class="formbox"><div id="'.$formConfig["formkey"].'" class="formbox-content">';
	echo '<form class="form validate '.$formConfig['mode'].' '.($formConfig['simpleform']?"simple-form":"").'" method="POST" enctype="multipart/form-data" data-formkey="'.$formConfig["formkey"].'" data-glink="'.$formConfig['gotolink'].'" data-relink="'.$formConfig['reloadlink'].'" data-clink="'.$formConfig['cancellink'].'" >';
	echo "<div class='row'>";
	echo getFormFieldset($formConfig['fields'],$formData,$formConfig['dbkey'],$formConfig['mode']);
	echo "</div>";
	echo '<hr class="hr-normal">';
	echo '<div class="form-actions form-actions-padding"><div class="text-right">';
	echo getFormActions($formConfig['actions'],$formConfig);
	echo '</div></div>';
	echo '</form></div></div>';
}
echo "<script>if(typeof initFormUI=='function' && typeof $.fn.sortable=='function') {initFormUI('#{$formConfig["formkey"]}');} else $(function() {initFormUI('#{$formConfig["formkey"]}');});</script>";
?>
