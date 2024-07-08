<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

if (isset($_SESSION['authenticated_user_id'])) {
    ob_clean();
    header('Location: ' . $GLOBALS['app']->getLoginRedirect());
    sugar_cleanup(true);
    return;
}

// display the logged out screen
$smarty = new Sugar_Smarty();
$smarty->assign(array(
    'LOGIN_URL'  => 'index.php?action=Login&module=Users',
    'STYLESHEET' => getJSPath('modules/Users/login.css'),
));
$smarty->display('modules/Users/LoggedOut.tpl');
