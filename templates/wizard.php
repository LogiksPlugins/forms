<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(count($fieldGroups)>1) {
  
//   $formConfig['actions']["update"]['class']='btn btn-primary hidden';
  $formConfig['actions']["submit"]['class']='btn btn-success hidden pull-right';
  
  $formConfig['actions']['cancel']['class']='btn btn-primary pull-left';
  $formConfig['actions']['escape']['class']='btn btn-primary pull-left';
  
  $formConfig['actions']['previousWizardPane']=[
								"type"=>"button",
								"label"=>"Previous",
								"icon"=>"<i class='fa fa-angle-left form-icon left pull-left'></i>",
                "class"=>'btn btn-primary disabled'
							];
  $formConfig['actions']['nextWizardPane']=[
								"type"=>"button",
								"label"=>"Next",
								"icon"=>"<i class='fa fa-angle-right form-icon right pull-right'></i>",
                "class"=>'btn btn-primary'
							];
  
  //$formConfig['actions'][]=[];//Add Next button
  //$formConfig['actions'][]=[];//Add Previous button
  
	$groups=array_keys($fieldGroups);
	echo '<ul class="nav nav-tabs hidden">';
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
	echo getFormActions($formConfig['actions'],$formConfig);
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
	echo getFormActions($formConfig['actions'],$formConfig);
	echo '</div></div>';
	echo '</form></div></div>';
}
echo "<script>if(typeof initFormUI=='function' && typeof $.fn.sortable=='function') {initFormUI();} else $(function() {initFormUI();});</script>";
?>
<script>
function nextWizardPane(btn) {
  continerDiv=$(btn).closest("form.form").parent();
  if(continerDiv.find(".nav.nav-tabs").length>0) {
		continerDiv.find(".nav.nav-tabs  > .active").next('li').find('a').trigger('click');
  }
	if(continerDiv.find(".nav.nav-tabs  > .active").next('li').length<=0) {
		continerDiv.find(".form-actions button[cmd=submit]").removeClass("hidden");
		$('button[cmd="nextWizardPane"]').addClass("disabled");
	} else {
		continerDiv.find(".form-actions button[cmd=submit]").addClass("hidden");
		$('button[cmd="nextWizardPane"]').removeClass("disabled");
	}
	if(continerDiv.find(".nav.nav-tabs  > .active").prev('li').length<=0) {
		$('button[cmd="previousWizardPane"]').addClass("disabled");
	} else {
		$('button[cmd="previousWizardPane"]').removeClass("disabled");
	}
}
function previousWizardPane(btn) {
  continerDiv=$(btn).closest("form.form").parent();
  if(continerDiv.find(".nav.nav-tabs").length>0) {
		continerDiv.find(".nav.nav-tabs  > .active").prev('li').find('a').trigger('click');
  }
	if(continerDiv.find(".nav.nav-tabs  > .active").next('li').length<=0) {
		continerDiv.find(".form-actions button[cmd=submit]").removeClass("hidden");
		$('button[cmd="nextWizardPane"]').addClass("disabled");
	} else {
		continerDiv.find(".form-actions button[cmd=submit]").addClass("hidden");
		$('button[cmd="nextWizardPane"]').removeClass("disabled");
	}
	if(continerDiv.find(".nav.nav-tabs  > .active").prev('li').length<=0) {
		$('button[cmd="previousWizardPane"]').addClass("disabled");
	} else {
		$('button[cmd="previousWizardPane"]').removeClass("disabled");
	}
}
</script>