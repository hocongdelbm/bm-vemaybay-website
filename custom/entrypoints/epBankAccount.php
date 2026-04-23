
<?php

global $db, $current_user;

// thay đổi vị trí trong bảng bank
if (isset($_POST['for']) && $_POST['for'] == 'changeBankAccountPosition') {
	$onl = new EC_Bank_Account();
	$onl_res = $onl->changeBankAccountPosition($_POST['stk'] ?? '', $_POST['type'], $_POST['booking']);
	
	echo $onl_res;
}