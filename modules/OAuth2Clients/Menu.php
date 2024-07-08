<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings;

if (ACLController::checkAccess('OAuth2Clients', 'edit', true)) {
    $module_menu[] = [
        "index.php?module=OAuth2Clients&action=EditViewPassword&return_module=OAuth2Clients&return_action=DetailView",
        $mod_strings['LNK_NEW_OAUTH2_PASSWORD_CLIENT'],
        "Create"
    ];
}
if (ACLController::checkAccess('OAuth2Clients', 'edit', true)) {
    $module_menu[] = [
        "index.php?module=OAuth2Clients&action=EditViewCredentials&return_module=OAuth2Clients&return_action=DetailView",
        $mod_strings['LNK_NEW_OAUTH2_CREDENTIALS_CLIENT'],
        "Create"
    ];
}

if (ACLController::checkAccess('OAuth2Clients', 'list', true)) {
    $module_menu[] = [
        "index.php?module=OAuth2Clients&action=index&return_module=OAuth2Clients&return_action=DetailView",
        $mod_strings['LNK_OAUTH2_CLIENT_LIST'],
        "List"
    ];
}

if (ACLController::checkAccess('OAuth2Tokens', 'list', true)) {
    $module_menu[] = [
        "index.php?module=OAuth2Tokens&action=index&return_module=OAuth2Tokens&return_action=DetailView",
        $mod_strings['LNK_OAUTH2_TOKEN_LIST'],
        "List"
    ];
}
