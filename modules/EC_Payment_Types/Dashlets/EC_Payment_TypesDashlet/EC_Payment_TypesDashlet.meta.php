<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $app_strings;

$dashletMeta['EC_Payment_TypesDashlet'] = array(
    'module' => 'EC_Payment_Types',
    'title' => translate('LBL_HOMEPAGE_TITLE', 'EC_Payment_Types'),
    'description' => 'A customizable view into EC_Payment_Types',
    'category' => 'Module Views'
);
