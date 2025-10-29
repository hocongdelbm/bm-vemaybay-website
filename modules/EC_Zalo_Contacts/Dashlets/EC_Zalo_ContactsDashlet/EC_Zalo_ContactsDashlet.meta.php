<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $app_strings;

$dashletMeta['EC_Zalo_ContactsDashlet'] = array(
    'module' => 'EC_Zalo_Contacts',
    'title' => translate('LBL_HOMEPAGE_TITLE', 'EC_Zalo_Contacts'),
    'description' => 'A customizable view into EC_Zalo_Contacts',
    'category' => 'Module Views'
);
