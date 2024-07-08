<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

function canSendPassword()
{
    require_once('include/SugarPHPMailer.php');
    global $mod_strings;
    global $current_user;
    global $app_strings;
    $mail = new SugarPHPMailer();
    $emailTemp = BeanFactory::newBean('EmailTemplates');
    $mail->setMailerForSystem();
    $emailTemp->disable_row_level_security = true;


    if ($current_user->is_admin) {
        if ($emailTemp->retrieve($GLOBALS['sugar_config']['passwordsetting']['generatepasswordtmpl']) == '') {
            return $mod_strings['LBL_EMAIL_TEMPLATE_MISSING'];
        }
        if (empty($emailTemp->body) && empty($emailTemp->body_html)) {
            return $app_strings['LBL_EMAIL_TEMPLATE_EDIT_PLAIN_TEXT'];
        }
        if ($mail->Mailer == 'smtp' && $mail->Host == '') {
            return $mod_strings['ERR_SERVER_SMTP_EMPTY'];
        }

        $email_errors = $mod_strings['ERR_EMAIL_NOT_SENT_ADMIN'];
        if ($mail->Mailer == 'smtp') {
            $email_errors .= "<br>-" . $mod_strings['ERR_SMTP_URL_SMTP_PORT'];
        }
        if ($mail->SMTPAuth) {
            $email_errors .= "<br>-" . $mod_strings['ERR_SMTP_USERNAME_SMTP_PASSWORD'];
        }
        $email_errors .= "<br>-" . $mod_strings['ERR_RECIPIENT_EMAIL'];
        $email_errors .= "<br>-" . $mod_strings['ERR_SERVER_STATUS'];
        return $email_errors;
    } else {
        return $mod_strings['LBL_EMAIL_NOT_SENT'];
    }
}

function hasPasswordExpired($username)
{
    $current_user = new user();
    $usr_id = $current_user->retrieve_user_id($username);
    $current_user->retrieve($usr_id);
    $type = 'syst';

    if (($current_user->portal_only == '0') && ($current_user->system_generated_password == '1')) {
        global $mod_strings, $timedate;
        $res = $GLOBALS['sugar_config']['passwordsetting'];
        if ($type != '') {
            switch ($res[$type . 'expiration']) {

                case '1':
                    global $timedate;
                    if ($current_user->pwd_last_changed == '') {
                        $current_user->pwd_last_changed = $timedate->nowDb();
                        $current_user->save();
                    }

                    $expireday = $res[$type . 'expirationtype'] * $res[$type . 'expirationtime'];

                    $timeFromUser = $timedate->fromUser($current_user->pwd_last_changed);

                    if ($timeFromUser === null) {
                        return false;
                    }

                    $expiretime = $timeFromUser->get("+{$expireday} days")->ts;

                    if ($timedate->getNow()->ts < $expiretime) {
                        return false;
                    } else {
                        $_SESSION['expiration_type'] = $mod_strings['LBL_PASSWORD_EXPIRATION_TIME'];
                        return true;
                    }
                    break;


                case '2':
                    $login = $current_user->getPreference('loginexpiration');
                    $current_user->setPreference('loginexpiration', $login + 1);
                    $current_user->save();
                    if ($login + 1 >= $res[$type . 'expirationlogin']) {
                        $_SESSION['expiration_type'] = $mod_strings['LBL_PASSWORD_EXPIRATION_LOGIN'];
                        return true;
                    } else {
                        return false;
                    }
                    break;

                case '0':
                    return false;
                    break;
            }
        }
    }
}
