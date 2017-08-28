<?php
if(!defined('ROOT')) exit('No direct script access allowed');

echo '<div class="formbox"><div class="formbox-content">';
echo '<form class="form validate" method="POST" enctype="multipart/form-data" data-formkey="'.$formConfig["formkey"].'" data-glink="'.$formConfig['gotolink'].'" >';
echo "<div class='row'>";
echo getFormFieldset($formConfig['fields'],$formData,$formConfig['dbkey'],$formConfig['mode']);
echo "</div>";
echo '<hr class="hr-normal">';
echo '<div class="form-actions form-actions-padding"><div class="text-right">';
echo getFormActions($formConfig['actions']);
echo '</div></div>';
echo '</form></div></div>';
?>