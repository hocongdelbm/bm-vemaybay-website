<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once __DIR__ . '/../../include/entryPoint.php';
require_once __DIR__ . '/../../modules/Users/language/en_us.lang.php';

global $app_strings, $sugar_config, $new_pwd, $current_user;

    $mod_strings=return_module_language('', 'Users');
    $res=$GLOBALS['sugar_config']['passwordsetting'];
    $regexmail = "/^\w+(['\.\-\+]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,})+\$/";

///////////////////////////////////////////////////
///////  Retrieve user
$username = '';
$useremail = '';
if (isset($_POST['user_name'])) {
    $username = $_POST['user_name'];
} else {
    if (isset($_POST['username'])) {
        $username = $_POST['username'];
    }
}

if (isset($_POST['Users0emailAddress0'])) {
    $useremail = $_POST['Users0emailAddress0'];
} else {
    if (isset($_POST['user_email'])) {
        $useremail = $_POST['user_email'];
    }
}

    $usr= new user();
    if (isset($username) && $username != '' && isset($useremail) && $useremail != '') {
        if ($username != '' && $useremail != '') {
            $usr_id=$usr->retrieve_user_id($username);
            $usr->retrieve($usr_id);
            if (!$usr->isPrimaryEmail($useremail)) {
                echo $mod_strings['LBL_PROVIDE_USERNAME_AND_EMAIL'];
                return;
            }

            if ($usr->portal_only || $usr->is_group) {
                echo $mod_strings['LBL_PROVIDE_USERNAME_AND_EMAIL'];
                return;
            }
        } else {
            echo  $mod_strings['LBL_PROVIDE_USERNAME_AND_EMAIL'];
            return;
        }
    } else {
        if (isset($_POST['userId']) && $_POST['userId'] != '') {
            $usr->retrieve($_POST['userId']);
        } else {
            if (!empty($_POST['sugar_user_name'])) {
                $usr_id=$usr->retrieve_user_id($_POST['sugar_user_name']);
                $usr->retrieve($usr_id);
            } else {
                echo  $mod_strings['LBL_PROVIDE_USERNAME_AND_EMAIL'];
                return;
            }
        }

        // Check if current_user is admin or the same user
        if (empty($current_user->id) || empty($usr->id) || ($usr->id != $current_user->id && !$current_user->is_admin)) {
            echo  $mod_strings['LBL_PROVIDE_USERNAME_AND_EMAIL'];
            return;
        }
    }

///////
///////////////////////////////////////////////////

///////////////////////////////////////////////////
///////  Check email address

    if (!preg_match($regexmail, $usr->emailAddress->getPrimaryAddress($usr))) {
        echo $mod_strings['ERR_EMAIL_INCORRECT'];
        return;
    }

///////
///////////////////////////////////////////////////
    $isLink = isset($_POST['link']) && $_POST['link'] == '1';
    // if i need to generate a password (not a link)
    $password = $isLink ? '' : User::generatePassword();

$isPasswordGenerationActive = $res['SystemGeneratedPasswordON'] ?? false;
if(!$isLink && empty($isPasswordGenerationActive)) {
    echo 'Access Denied';
    return;
}

// Create URL
if ($isLink) {
    global $timedate;
    $guid = create_guid();
    $key = create_guid();
    $hashedKey = User::getPasswordHash($key);
    $url = $GLOBALS['sugar_config']['site_url'] . "/index.php?entryPoint=Changenewpassword&guid=$guid&key=$key";
    $time_now = TimeDate::getInstance()->nowDb();
    $userID = $usr->retrieve_user_id($username);
    $q = "INSERT INTO users_password_link (id, keyhash, username, date_generated, user_id) VALUES('" .
        $guid . "','" .
        $hashedKey . "','" .
        $username . "','" .
        $time_now . "','" .
        $userID . "') ";
    $usr->db->query($q);
}

///////  Email creation
    if ($isLink) {
        $emailTemp_id = $res['lostpasswordtmpl'];
    } else {
        $emailTemp_id = $res['generatepasswordtmpl'];
    }

    $additionalData = array(
        'link' => $isLink,
        'password' => $password
    );
    if (isset($url)) {
        $additionalData['url'] = $url;
    }
    $result = $usr->sendEmailForPassword($emailTemp_id, $additionalData);
    if ($result['status'] == false && $result['message'] != '') {
        echo $result['message'];
        $new_pwd = '4';
        return;
    }

    if ($result['status'] == true) {
        echo '1';
    } else {
        $new_pwd='4';
        if ($current_user->is_admin) {
            $email_errors=$mod_strings['ERR_EMAIL_NOT_SENT_ADMIN'];
            $email_errors.="\n-".$mod_strings['ERR_RECIPIENT_EMAIL'];
            $email_errors.="\n-".$mod_strings['ERR_SERVER_STATUS'];
            echo $email_errors;
        } else {
            echo $mod_strings['LBL_EMAIL_NOT_SENT'];
        }
    }
    return;
