<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $current_user, $sugar_config;
global $mod_strings;
global $app_list_strings;
global $app_strings;
global $theme;

if (!is_admin($current_user)) {
    sugar_die("Unauthorized access to administration.");
}

require_once('modules/Configurator/Configurator.php');


echo getClassicModuleTitle(
    "Administration",
    array(
        "<a href='index.php?module=Administration&action=index'>" . translate('LBL_MODULE_NAME', 'Administration') . "</a>",
        $mod_strings['LBL_AOS_ADMIN_MANAGE_AOS'],
    ),
    false
);

$cfg = new Configurator();
$sugar_smarty = new Sugar_Smarty();
$errors = array();

if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'save') {
    foreach ($_POST as $key => $value) {
        if (strcmp((string)$value, 'true') == 0) {
            $value = true;
        }
        if (strcmp((string)$value, 'false') == 0) {
            $value = false;
        }
        $_POST[$key] = $value;
    }

    $cfg->saveConfig();

    SugarApplication::redirect('index.php?module=Administration&action=index');
    exit();
}

$sugar_smarty->assign('MOD', $mod_strings);
$sugar_smarty->assign('APP', $app_strings);
$sugar_smarty->assign('APP_LIST', $app_list_strings);
$sugar_smarty->assign('LANGUAGES', get_languages());
$sugar_smarty->assign("JAVASCRIPT", get_set_focus_js());
$sugar_smarty->assign('config', $sugar_config);
$sugar_smarty->assign('error', $errors);


$buttons = <<<EOQ
    <input title="{$app_strings['LBL_SAVE_BUTTON_TITLE']}" accessKey="{$app_strings['LBL_SAVE_BUTTON_KEY']}" class="btn btn-primary" type="submit" name="save" onclick="return check_form('ConfigureSettings');" value="{$app_strings['LBL_SAVE_BUTTON_LABEL']}" >
    <input title="{$mod_strings['LBL_CANCEL_BUTTON_TITLE']}" onclick="document.location.href='index.php?module=Administration&action=index'" class="btn btn-danger"  type="button" name="cancel" value="  {$app_strings['LBL_CANCEL_BUTTON_LABEL']}  " >
EOQ;

$sugar_smarty->assign("BUTTONS", $buttons);

$sugar_smarty->display('modules/Administration/AOSAdmin.tpl');

$javascript = new javascript();
$javascript->setFormName('ConfigureSettings');
echo $javascript->getScript();
?>
<script language="Javascript" type="text/javascript">
    addToValidateLessThan('ConfigureSettings', 'aos_invoices_initialNumber', 'int', false, "", 9999999999,"Initial Invoice number cannot be bigger than 9999999999");
</script>

