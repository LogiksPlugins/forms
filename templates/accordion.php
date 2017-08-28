<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(count($fieldGroups)>1) {
	$groups=array_keys($fieldGroups);

	$accordionID=$formConfig['formkey'];

	echo '<form class="form validate" method="POST" enctype="multipart/form-data" data-formkey="'.$formConfig["formkey"].'" data-glink="'.$formConfig['gotolink'].'" >';
	echo '<div class="panel-group" id="accordion'.$accordionID.'" role="tablist" aria-multiselectable="true">';
	foreach ($groups as $nx=>$fkey) {
		$title=toTitle(_ling($fkey));
		$panelID=md5($fkey);
		echo '<div class="panel panel-default">';

		echo '<div class="panel-heading" role="tab" id="heading'.$panelID.'">';
		echo '<h4 class="panel-title">';
		if($nx==0) {
		echo '<a role="button" data-toggle="collapse" aria-expanded="true" aria-controls="collapse'.$panelID.'" data-parent="#accordion'.$accordionID.'" href="#collapse'.$panelID.'" >'.$title.'</a>';
		} else {
		echo '<a role="button" data-toggle="collapse" aria-expanded="false" aria-controls="collapse'.$panelID.'" data-parent="#accordion'.$accordionID.'" href="#collapse'.$panelID.'" >'.$title.'</a>';
		}
		echo '</h4>';
		echo '</div>';

		if($nx==0) {
		echo '<div id="collapse'.$panelID.'" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading'.$panelID.'">';
		} else {
		echo '<div id="collapse'.$panelID.'" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading'.$panelID.'">';
		}

		echo '<div class="panel-body">';

		echo "<div class='row'>";
		echo getFormFieldset($fieldGroups[$fkey],$formData,$formConfig['dbkey'],$formConfig['mode']);
		echo "</div>";

		echo '</div>';
		echo '</div>';

		echo '</div>';
	}
	echo '</div>';

	echo '<hr class="hr-normal">';
	echo '<div class="form-actions form-actions-padding"><div class="text-right">';
	echo getFormActions($formConfig['actions']);
	echo '</div></div>';

	echo '</form>';
} else {
	echo '<div class="formbox"><div class="formbox-content">';
	echo '<form class="form validate" method="POST" enctype="multipart/form-data" data-formkey="'.$formConfig["formkey"].'" >';
	echo "<div class='row'>";
	echo getFormFieldset($formConfig['fields'],$formData,$formConfig['dbkey'],$formConfig['mode']);
	echo "</div>";
	echo '<hr class="hr-normal">';
	echo '<div class="form-actions form-actions-padding"><div class="text-right">';
	echo getFormActions($formConfig['actions']);
	echo '</div></div>';
	echo '</form></div></div>';
}

?>