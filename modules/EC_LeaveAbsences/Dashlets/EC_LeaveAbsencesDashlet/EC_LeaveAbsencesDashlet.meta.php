<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $app_strings;

$dashletMeta['EC_LeaveAbsencesDashlet'] = array(
    'module' => 'EC_LeaveAbsences',
    'title' => translate('LBL_HOMEPAGE_TITLE', 'EC_LeaveAbsences'),
    'description' => 'A customizable view into EC_LeaveAbsences',
    'category' => 'Module Views'
);
