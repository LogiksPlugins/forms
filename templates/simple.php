<?php
if(!defined('ROOT')) exit('No direct script access allowed');

echo '<div class="formbox"><div class="formbox-content">';
echo '<form class="form validate '.$formConfig['mode'].' '.($formConfig['simpleform']?"simple-form":"").'" method="POST" enctype="multipart/form-data" data-formkey="'.$formConfig["formkey"].'" data-glink="'.$formConfig['gotolink'].'" data-relink="'.$formConfig['reloadlink'].'" data-clink="'.$formConfig['cancellink'].'">';
echo "<div class='row'>";
echo getFormFieldset($formConfig['fields'],$formData,$formConfig['dbkey'],$formConfig['mode']);
echo "</div>";
echo '<hr class="hr-normal">';
echo '<div class="form-actions form-actions-padding"><div class="text-right">';
echo getFormActions($formConfig['actions']);
echo '</div></div>';
echo '</form></div></div>';
echo "<script>if(typeof initFormUI=='function') initFormUI(); else $(function() {initFormUI();});</script>";
?>
