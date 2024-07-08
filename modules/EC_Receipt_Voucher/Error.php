<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point'); 

global $app_strings;

if(isset($_REQUEST['ie_error']) && $_REQUEST['ie_error'] == 'true') {
	echo '<a href="index.php?module=EC_Receipt_Voucher&action=EditView&record='.$_REQUEST['id'].'">'.$mod_strings['ERR_IE_FAILURE1'].'</a><br>';
	echo $mod_strings['ERR_IE_FAILURE2'];
} else {
?>

<div class="d-flex gap-2 align-items-center">
	<span class='error'>
		<?php if (isset($_REQUEST['error_string'])) echo '<span>'.$_REQUEST['error_string'].'</span>';}?>
	</span>
	<input value="Quay lại" class="btn btn-secondary" type="button" onclick="history.back()" />
</div>