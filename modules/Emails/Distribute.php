<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

if (!empty($_SESSION['distribute_where']) && !empty($_REQUEST['distribute_method']) && !empty($_REQUEST['users']) && !empty($_REQUEST['use'])) {

	$focus = new Email();

	$emailIds = array();
	// CHECKED ONLY:
	if ($_REQUEST['use'] == 'checked') {
		// clean up passed array
		$grabEx = explode('::', $_REQUEST['grabbed']);
		foreach ($grabEx as $k => $emailId) {
			if ($emailId != "undefined") {
				$emailIds[] = $emailId;
			}
		}

		// we have users and the items to distribute	
		if ($_REQUEST['distribute_method'] == 'roundRobin') {
			if ($focus->distRoundRobin($_REQUEST['users'], $emailIds)) {
				header('Location: index.php?module=Emails&action=ListViewGroup');
			}
		} elseif ($_REQUEST['distribute_method'] == 'leastBusy') {
			if ($focus->distLeastBusy($_REQUEST['users'], $emailIds)) {
				header('Location: index.php?module=Emails&action=ListViewGroup');
			}
		} elseif ($_REQUEST['distribute_method'] == 'direct') {
			if (count($_REQUEST['users']) > 1) {
				$error = 1;
			} else {
				$user = $_REQUEST['users'][0];
				if ($focus->distDirect($user, $emailIds)) {
					header('Location: index.php?module=Emails&action=ListViewGroup');
				}
			}

			header('Location: index.php?module=Emails&action=ListViewGroup&error=' . $error);
		}
	} elseif ($_REQUEST['use'] == 'all') {
		if ($_REQUEST['distribute_method'] == 'direct') {
			// no ALL assignments to 1 user
			header('Location: index.php?module=Emails&action=ListViewGroup&error=2');
		}

		// we have the where clause that generated the view above, so use it
		$q = 'SELECT emails.id FROM emails WHERE ' . $_SESSION['distribute_where'];
		$q = str_replace('&#039;', '"', $q);
		$r = $focus->db->query($q);
		$count = 0;
		while ($a = $focus->db->fetchByAssoc($r)) {
			$emailIds[] = $a['id'];
			$count++;
		}
		// we have users and the items to distribute	
		if ($_REQUEST['distribute_method'] == 'roundRobin') {
			if ($focus->distRoundRobin($_REQUEST['users'], $emailIds)) {
				header('Location: index.php?module=Emails&action=ListViewGroup');
			}
		} elseif ($_REQUEST['distribute_method'] == 'leastBusy') {
			if ($focus->distLeastBusy($_REQUEST['users'], $emailIds)) {
				header('Location: index.php?module=Emails&action=ListViewGroup');
			}
		}

		if ($count < 1) {
			$GLOBALS['log']->info('Emails distribute failed: query returned no results (' . $q . ')');
			header('Location: index.php?module=Emails&action=ListViewGroup&error=' . $error);
		}
	}
} else {
	// error
	header('Location: index.php?module=Emails&action=index');
}
