<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $app_strings;

$dashletMeta['EC_Zalo_AppsDashlet'] = array(
    'module' => 'EC_Zalo_Apps',
    'title' => translate('LBL_HOMEPAGE_TITLE', 'EC_Zalo_Apps'),
    'description' => 'A customizable view into EC_Zalo_Apps',
    'category' => 'Module Views'
);
