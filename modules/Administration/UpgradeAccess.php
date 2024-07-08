<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once __DIR__ . '/../../install/install_utils.php';
handleHtaccess();

$uploadHta = '';

if (empty($GLOBALS['sugar_config']['upload_dir'])) {
    $GLOBALS['sugar_config']['upload_dir'] = 'upload/';
}

$uploadHta = 'upload://.htaccess';

$denyAll = <<<eoq
	Order Deny,Allow
	Deny from all
eoq;

if (file_exists($uploadHta) && filesize($uploadHta)) {
    // file exists, parse to make sure it is current
    if (is_writable($uploadHta)) {
        $oldHtaccess = file_get_contents($uploadHta);
        // use a different regex boundary b/c .htaccess uses the typicals
        if (strpos($oldHtaccess, $denyAll) === false) {
            $oldHtaccess .= "\n";
            $oldHtaccess .= $denyAll;
        }
        if (!file_put_contents($uploadHta, $oldHtaccess)) {
            $htaccess_failed = true;
        }
    } else {
        $htaccess_failed = true;
    }
} elseif (!file_put_contents($uploadHta, $denyAll)) {
    $htaccess_failed = true;
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'UpgradeAccess') {
    // only display message in the repair tool and not during the upgrade process
    echo "\n" . $mod_strings['LBL_HT_DONE'] . "<br />\n";
}
