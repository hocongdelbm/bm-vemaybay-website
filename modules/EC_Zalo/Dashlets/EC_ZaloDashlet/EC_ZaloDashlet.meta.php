<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $app_strings;

$dashletMeta['EC_ZaloDashlet'] = array(
    'module' => 'EC_Zalo',
    'title' => translate('LBL_HOMEPAGE_TITLE', 'EC_Zalo'),
    'description' => 'A customizable view into EC_Zalo',
    'category' => 'Module Views'
);
