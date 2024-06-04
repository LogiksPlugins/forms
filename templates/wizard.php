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
		echo '<div class="formbox"><div id="'.$formConfig["formkey"].'" class="formbox-content">';
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
<script>
const WIZARD_NAV_LISTENER = [];
function nextWizardPane(btn) {
	var proceed = true;
	$(btn).closest("form.form").find(".tab-pane.active label.error").detach();
	$(btn).closest("form.form").find(".tab-pane.active").find("input[required],select[required]").each(function() {
	    if($(this).val().length<=0) {
	        var randKey = $(this).attr("name")+Math.ceil(Math.random()*100000000000);
	        $(`<label id="${randKey}" generated="true" class="error">This field is required.</label>`).insertAfter(this);
	        $(this).change(function() {
	        	$(`label.error#${randKey}`).detach();
	        });
	        proceed = false;
	    }
	});
	if(!proceed) {
		if(typeof lgksToast=="function") lgksToast("Some required fields are invalid. They have been marked.<br>Please fix them to proceed.");
		else if(typeof lgksAlert=="function") lgksAlert("Some required fields are invalid. They have been marked.<br>Please fix them to proceed.");
		else {
			alert("Some required fields are invalid. They have been marked.<br>Please fix them to proceed.");
		}
		return;
	}

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

	$.each(WIZARD_NAV_LISTENER, function(k, func) {
		if(typeof func == "function") func($(btn).closest("form.form"), "next");
	});
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

	$.each(WIZARD_NAV_LISTENER, function(k, func) {
		if(typeof func == "function") func($(btn).closest("form.form"), "prev");
	});
}
</script>