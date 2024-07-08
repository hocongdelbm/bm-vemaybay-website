<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $app_strings;

$dashletMeta['EC_Payment_VoucherDashlet'] = array(
    'module' => 'EC_Payment_Voucher',
    'title' => translate('LBL_HOMEPAGE_TITLE', 'EC_Payment_Voucher'),
    'description' => 'A customizable view into EC_Payment_Voucher',
    'category' => 'Module Views'
);
