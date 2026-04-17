<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $current_user;

if (!is_admin($current_user)) {
    sugar_die("Unauthorized access to administration.");
}

$focus = BeanFactory::newBean('Administration');

// filter for relevant POST data and update config table
foreach ($_POST as $key => $val) {
    $prefix = $focus->get_config_prefix($key);
    if (in_array($prefix[0], $focus->config_categories)) {
        if ($prefix[0] == "license") {
            if ($prefix[1] == "expire_date") {
                global $timedate;
                $val = $timedate->swap_formats($val, $timedate->get_date_format(), $timedate->dbDayFormat);
            } else {
                if ($prefix[1] == "key") {
                    $val = trim($val); // bug 16860 tyoung - trim whitespace from the start and end of the licence key value
                }
            }
        }

        $focus->saveSetting($prefix[0], $prefix[1], $val);
    }
}






header("Location: index.php?action={$_POST['return_action']}&module={$_POST['return_module']}");
