<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

if (!$GLOBALS['current_user']->isAdminForModule('Users')) {
    sugar_die('No Access');
}
$record = '';
if (isset($_REQUEST['record'])) {
    $record = $_REQUEST['record'];
}
?>

<form action="index.php" method="post" name="DetailView" id="form">
    <input type="hidden" name="module" value="Users">
    <input type="hidden" name="user_id" value="">
    <input type="hidden" name="record" value="<?php echo $record; ?>">
    <input type="hidden" name="isDuplicate" value=''>
    <input type="hidden" name="action">
</form>

<?php

$users = get_user_array(true, "Active", $record);

echo getClassicModuleTitle($mod_strings['LBL_MODULE_NAME'], array($mod_strings['LBL_MODULE_NAME']), true);

echo "<form action='index.php' name='Users' class='form-list__users'>
        <input type='hidden' name='action' value='ListRoles'>
        <input type='hidden' name='module' value='Users'>
        <select class='box-select' name='record' onchange='document.Users.submit();'>".get_select_options_with_id($users, $record) ."</select>
    </form>";

if (!empty($record)) {
    $hideTeams = true; // to not show the teams subpanel in the following file
    require_once('modules/ACLRoles/DetailUserRole.php');
}

?>