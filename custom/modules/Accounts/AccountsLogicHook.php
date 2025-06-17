<?php
class AccountsLogicHook {
	function showEmailAddress($focus, $event, $arguments) { 
		$sea = new SugarEmailAddress; 
		$primary = $sea->getPrimaryAddress($focus);
		$focus->main_email = $primary;
    }
}
?>