<?php
    header('Content-Type: text/css');
// config|_override.php
if (is_file('../../../config.php')) {
    require_once '../../../config.php';
}

// load up the config_override.php file.  This is used to provide default user settings
if (is_file('../../../config_override.php')) {
    require_once '../../../config_override.php';
}

if (!isset($sugar_config['theme_settings']['SuiteP'])) {
    return;
}

//set file type back to css from php
header('Content-type: text/css; charset: UTF-8');

?>

<?php
// TODO add theme color settings here: for e.g:
//.navbar-inverse {
//background:#< ? php echo $sugar_config['theme_settings']['SuiteP']['navbar']; ? > !important;
//}
?>