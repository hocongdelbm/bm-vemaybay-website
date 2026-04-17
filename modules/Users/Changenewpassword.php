<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $app_language, $sugar_config, $app_list_strings, $app_strings, $mod_strings, $current_language;

/** @var DBManager $db */
$db = DBManagerFactory::getInstance();

require_once __DIR__ . '/../../modules/Users/language/en_us.lang.php';
$mod_strings = return_module_language('', 'Users');

// Recaptcha check
require_once __DIR__.'/../../include/utils/recaptcha_utils.php';
if (getRecaptchaChallengeField() !== false) {
    $response =  displayRecaptchaValidation();
    if ($response === 'Success') {
        echo $response;
        return;
    } else {
        die($response);
    }
}

///////////////////////////////////////////////////////////////////////////////
////	PASSWORD GENERATED LINK CHECK USING
////
//// This script :  - check the link expiration
////			   - send the filled form to authenticate.php after changing the password in the database
$redirect = true;
$errors = '';
if (!empty($_REQUEST['guid']) && !empty($_REQUEST['key'])) {
    // Change 'deleted = 0' clause to 'COALESCE(deleted, 0) = 0' because by default the values were NULL
    $Q = "SELECT * FROM users_password_link WHERE id = '" . $db->quote($_REQUEST['guid']) . "' AND COALESCE(deleted, 0) = '0'";
    $result = DBManagerFactory::getInstance()->limitQuery($Q, 0, 1, false);
    $row = DBManagerFactory::getInstance()->fetchByAssoc($result);

    $keyHash = !empty($row['keyhash']) ? $row['keyhash'] : null;

    $isValid = false;
    if ($keyHash !== null) {
        $isValid = User::checkPassword($_REQUEST['key'], $keyHash);
    }

    if (!empty($row) && $isValid === true) {
        $pwd_settings = $GLOBALS['sugar_config']['passwordsetting'];
        $expired = false;

        if ($pwd_settings['linkexpiration']) {
            $delay = $pwd_settings['linkexpirationtime'] * $pwd_settings['linkexpirationtype'];
            $stim = strtotime($row['date_generated']) + date('Z');
            $expiretime = TimeDate::getInstance()->fromTimestamp($stim)->get("+$delay  minutes")->asDb();
            $timenow = TimeDate::getInstance()->nowDb();
            if ($timenow > $expiretime) {
                $expired = true;
            }
        }

        if (!empty($row['user_id'])) {
            $userBean = BeanFactory::getBean('Users', $row['user_id']);
        }

        if (empty($userBean)) {
            $expired = true;
        }

        if (!$expired) {
            // if the form is filled and we want to login
            if (isset($_REQUEST['login']) && $_REQUEST['login'] == '1') {
                if ($row['username'] == $_POST['user_name']) {
                    $password = $_POST['new_password'];
                    $usr = new user();
                    $errors = $usr->passwordValidationCheck($password);
                    if (!$errors) {
                        $usr_id = $usr->retrieve_user_id($_POST['user_name']);
                        $usr->retrieve($usr_id);
                        $usr->setNewPassword($password);
                        $query2 = "UPDATE users_password_link SET deleted='1' where id='" . $db->quote($_REQUEST['guid']) . "'";
                        DBManagerFactory::getInstance()->query($query2, true, "Error setting link for $usr->user_name: ");
                        $_POST['user_name'] = $_REQUEST['user_name'];
                        $_POST['username_password'] = $_REQUEST['new_password'];
                        $_POST['module'] = 'Users';
                        $_POST['action'] = 'Authenticate';
                        $_POST['login_module'] = 'Home';
                        $_POST['login_action'] = 'index';
                        $_POST['Login'] = 'Login';
                        foreach ($_POST as $k => $v) {
                            $_REQUEST[$k] = $v;
                            $_GET[$k] = $v;
                        }
                        unset($_REQUEST['entryPoint'], $_GET['entryPoint']);
                        $GLOBALS['app']->execute();
                        die();
                    }
                    $redirect = false;
                }
            } else {
                $redirect = false;
            }
        } else {
            $query2 = "UPDATE users_password_link SET deleted='1' where id='" . $db->quote($_REQUEST['guid']) . "'";
            DBManagerFactory::getInstance()->query($query2, true, "Error setting link");
        }
    }
}

if ($redirect === true) {
    header('location:index.php?action=Login&module=Users');
    exit();
}

////	PASSWORD GENERATED LINK CHECK USING
///////////////////////////////////////////////////////////////////////////////

require_once('include/MVC/View/SugarView.php');
$view = new SugarView();
$view->init();
$view->displayHeader();

$sugar_smarty = new Sugar_Smarty();

$pwd_settings = $GLOBALS['sugar_config']['passwordsetting'];

$sugar_smarty->assign('sugar_md', getWebPath('include/images/sugar_md_open.png'));
$sugar_smarty->assign("MOD", $mod_strings);
$sugar_smarty->assign("CAPTCHA", displayRecaptcha());
$sugar_smarty->assign("IS_ADMIN", '1');
$sugar_smarty->assign("ENTRY_POINT", 'Changenewpassword');
$sugar_smarty->assign('return_action', 'login');
$sugar_smarty->assign("APP", $app_strings);
$sugar_smarty->assign("INSTRUCTION", $app_strings['NTC_LOGIN_MESSAGE']);
$sugar_smarty->assign("ERRORS", $errors);
// $sugar_smarty->assign(
//     "USERNAME_FIELD",
//     '<td scope="row" width="30%">' . $mod_strings['LBL_USER_NAME'] . ':</td><td width="70%"><input type="text" size="20" tabindex="1" id="user_name" name="user_name"  value=""></td>'
// );
$sugar_smarty->assign(
    "USERNAME_FIELD",
    '<div class="text-field">
        <label for="fp_user_name">' . $mod_strings['LBL_USER_NAME'] . '</label>
        <div class="input-group">
            <input type="text" class="form-control" size="20" id="user_name" name="user_name" tabindex="1" value="" placeholder="' . $mod_strings['LBL_USER_NAME'] . '" autocomplete="off">
        </div>
    </div>'
);
$sugar_smarty->assign('PWDSETTINGS', $GLOBALS['sugar_config']['passwordsetting']);


$rules = "'','',''";

$sugar_smarty->assign('SUBMIT_BUTTON', '<input title="' . $mod_strings['LBL_LOGIN_BUTTON_TITLE']
    . '" class="button" '
    . 'onclick="if(!set_password(form,newrules(' . $rules . '))) return false; validateCaptchaAndSubmit();" '
    . 'type="button" tabindex="3" id="login_button" name="Login" value="' . $mod_strings['LBL_LOGIN_BUTTON_LABEL'] . '" />');

if (!empty($_REQUEST['guid'])) {
    $sugar_smarty->assign("GUID", $_REQUEST['guid']);
}
if (!empty($_REQUEST['key'])) {
    $sugar_smarty->assign("KEY", $_REQUEST['key']);
}

$sugar_smarty->display(get_custom_file_if_exists('modules/Users/Changenewpassword.tpl'));

$view->displayFooter();
