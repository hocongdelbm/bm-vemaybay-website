<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $app_strings;

$dashletMeta['EC_WorkingOverTimesDashlet'] = array(
    'module' => 'EC_WorkingOverTimes',
    'title' => translate('LBL_HOMEPAGE_TITLE', 'EC_WorkingOverTimes'),
    'description' => 'A customizable view into EC_WorkingOverTimes',
    'category' => 'Module Views'
);
