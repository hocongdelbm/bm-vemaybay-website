<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require __DIR__ . '/../../config.php';

global $current_language;

if (file_exists(__DIR__ . '/../language/' . $current_language . '.lang.php')) {
    require_once __DIR__ . '/../language/' . $current_language . '.lang.php';
} else {
    require_once __DIR__ . '/../language/en_us.lang.php';
}

global $sugar_config;
global $timedate;


//Sent when the admin generate a new password
if (
    !isset($sugar_config['passwordsetting']['generatepasswordtmpl'])
    || empty($sugar_config['passwordsetting']['generatepasswordtmpl'])
) {
    $EmailTemp = BeanFactory::newBean('EmailTemplates');
    $EmailTemp->name = $mod_strings['advanced_password_new_account_email']['name'];
    $EmailTemp->description = $mod_strings['advanced_password_new_account_email']['description'];
    $EmailTemp->subject = $mod_strings['advanced_password_new_account_email']['subject'];
    $EmailTemp->type = $mod_strings['advanced_password_new_account_email']['type'];
    $EmailTemp->body = $mod_strings['advanced_password_new_account_email']['txt_body'];
    $EmailTemp->body_html = $mod_strings['advanced_password_new_account_email']['body'];
    $EmailTemp->deleted = 0;
    $EmailTemp->published = 'off';
    $EmailTemp->text_only = 0;
    $id = $EmailTemp->save();
    $sugar_config['passwordsetting']['generatepasswordtmpl'] = $id;
}


//User generate a link to set a new password
if (
    !isset($sugar_config['passwordsetting']['lostpasswordtmpl'])
    || empty($sugar_config['passwordsetting']['lostpasswordtmpl'])
) {
    $EmailTemp = BeanFactory::newBean('EmailTemplates');
    $EmailTemp->name = $mod_strings['advanced_password_forgot_password_email']['name'];
    $EmailTemp->description = $mod_strings['advanced_password_forgot_password_email']['description'];
    $EmailTemp->subject = $mod_strings['advanced_password_forgot_password_email']['subject'];
    $EmailTemp->type = $mod_strings['advanced_password_new_account_email']['type'];
    $EmailTemp->body = $mod_strings['advanced_password_forgot_password_email']['txt_body'];
    $EmailTemp->body_html = $mod_strings['advanced_password_forgot_password_email']['body'];
    $EmailTemp->deleted = 0;
    $EmailTemp->published = 'off';
    $EmailTemp->text_only = 0;
    $id = $EmailTemp->save();
    $sugar_config['passwordsetting']['lostpasswordtmpl'] = $id;
}


//Two Factor Authentication code template
if (
    !isset($sugar_config['passwordsetting']['factoremailtmpl'])
    || empty($sugar_config['passwordsetting']['factoremailtmpl'])
) {
    $EmailTemp = BeanFactory::newBean('EmailTemplates');
    $EmailTemp->name = $mod_strings['two_factor_auth_email']['name'];
    $EmailTemp->description = $mod_strings['two_factor_auth_email']['description'];
    $EmailTemp->subject = $mod_strings['two_factor_auth_email']['subject'];
    $EmailTemp->type = $mod_strings['two_factor_auth_email']['type'];
    $EmailTemp->body = $mod_strings['two_factor_auth_email']['txt_body'];
    $EmailTemp->body_html = $mod_strings['two_factor_auth_email']['body'];
    $EmailTemp->deleted = 0;
    $EmailTemp->published = 'off';
    $EmailTemp->text_only = 0;
    $id = $EmailTemp->save();
    $sugar_config['passwordsetting']['factoremailtmpl'] = $id;
}

// set all other default settings
if (!isset($sugar_config['passwordsetting'])) {
    $sugar_config['passwordsetting']['forgotpasswordON'] = true;
    $sugar_config['passwordsetting']['SystemGeneratedPasswordON'] = true;
    $sugar_config['passwordsetting']['systexpirationtime'] = 7;
    $sugar_config['passwordsetting']['systexpiration'] = 1;
    $sugar_config['passwordsetting']['linkexpiration'] = true;
    $sugar_config['passwordsetting']['linkexpirationtime'] = 24;
    $sugar_config['passwordsetting']['linkexpirationtype'] = 60;
    $sugar_config['passwordsetting']['minpwdlength'] = 6;
    $sugar_config['passwordsetting']['oneupper'] = false;
    $sugar_config['passwordsetting']['onelower'] = false;
    $sugar_config['passwordsetting']['onenumber'] = false;
    $sugar_config['passwordsetting']['onespecial'] = false;
}

if ($sugar_config['passwordsetting']['systexpirationtype'] === '0') {
    $sugar_config['passwordsetting']['systexpirationtype'] = 1;
}

write_array_to_file("sugar_config", $sugar_config, "config.php");
