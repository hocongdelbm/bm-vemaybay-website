<?php

function loadParentView($type)
{
    if (file_exists('custom/include/MVC/View/views/view.' . $type . '.php')) {
        require_once('custom/include/MVC/View/views/view.' . $type . '.php');
    } else {
        if (file_exists('include/MVC/View/views/view.' . $type . '.php')) {
            require_once('include/MVC/View/views/view.' . $type . '.php');
        }
    }
}


function getPrintLink()
{
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == "ajaxui") {
        return "javascript:SUGAR.ajaxUI.print();";
    }
    $requestString = null;
    if (isset($GLOBALS['request_string'])) {
        $requestString = $GLOBALS['request_string'];
    } else {
        LoggerManager::getLogger()->warn('Undefined index: request_string');
    }
    return "javascript:void window.open('index.php?{$requestString}',"
        . "'printwin','menubar=1,status=0,resizable=1,scrollbars=1,toolbar=0,location=1')";
}


function ajaxBannedModules()
{
    $bannedModules = array(
        'Calendar',
        'Emails',
        'Documents',
        'DocumentRevisions',
        'EmailMarketing',
        'Releases',
        'Groups',
        'EmailMan',
        "Administration",
        "ModuleBuilder",
        'Schedulers',
        'SchedulersJobs',
        'DynamicFields',
        'EditCustomFields',
        'EmailTemplates',
        'Users',
        'Currencies',
        'Trackers',
        'Import_1',
        'Import_2',
        'Versions',
        'CustomFields',
        'Roles',
        'Audit',
        'InboundEmail',
        'SavedSearch',
        'UserPreferences',
        'MergeRecords',
        'EmailAddresses',
        'Relationships',
        'Employees',
        'Import',
        'OAuthKeys',
    );

    if (!empty($GLOBALS['sugar_config']['addAjaxBannedModules'])) {
        $bannedModules = array_merge($bannedModules, $GLOBALS['sugar_config']['addAjaxBannedModules']);
    }
    if (!empty($GLOBALS['sugar_config']['overrideAjaxBannedModules'])) {
        $bannedModules = $GLOBALS['sugar_config']['overrideAjaxBannedModules'];
    }

    return $bannedModules;
}

function ajaxLink($url)
{
    global $sugar_config;
    $match = array();
    $javascriptMatch = array();

    preg_match('/module=([^&]*)/i', $url, $match);
    preg_match('/^javascript/i', $url, $javascriptMatch);

    if (!empty($sugar_config['disableAjaxUI'])) {
        return $url;
    } else {
        if (isset($match[1]) && in_array($match[1], ajaxBannedModules())) {
            return $url;
        }
        //Don't modify javascript calls.
        else {
            if (isset($javascriptMatch[0])) {
                return $url;
            } else {
                return "?action=ajaxui#ajaxUILoc=" . urlencode($url);
            }
        }
    }
}
