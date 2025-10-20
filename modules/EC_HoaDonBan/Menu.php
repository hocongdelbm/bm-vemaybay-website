<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

// global $mod_strings, $app_strings, $sugar_config;

$editing_rights = ACLController::checkAccess('EC_Input_Invoices', 'edit', true);
if ($editing_rights) $module_menu[] = ["index.php?module=EC_HoaDonBan&action=inputinvoice&return_module=EC_Input_Invoices&return_action=DetailView", "D/s Hoá đơn đầu vào", 'EC_HoaDonBan', 'EC_HoaDonBan'];
if ($editing_rights) $module_menu[] = ["index.php?module=EC_HoaDonBan&action=requestinvoice&return_module=EC_HoaDonBan&return_action=DetailView", "D/s yêu cầu xuất HĐ", 'EC_HoaDonBan', 'EC_HoaDonBan'];
if ($editing_rights) $module_menu[] = ["index.php?module=EC_HoaDonBan&action=ListView&return_module=EC_HoaDonBan&return_action=DetailView", "D/s HĐ đầu ra", 'EC_HoaDonBan', 'EC_HoaDonBan'];
if ($editing_rights) $module_menu[] = ["index.php?module=EC_HoaDonBan&action=signedinvoice&return_module=EC_HoaDonBan&return_action=DetailView", "D/s HĐ ghi sổ", 'EC_HoaDonBan', 'EC_HoaDonBan'];

// if ($editing_rights) $module_menu[] = ["index.php?module=EC_HoaDonBan&action=outputinvoice&return_module=EC_HoaDonBan&return_action=DetailView", "D/s HĐ đầu ra TH", 'EC_HoaDonBan', 'EC_HoaDonBan'];

if ($editing_rights) $module_menu[] = ["index.php?module=EC_HoaDonBan&action=EditView&return_module=EC_HoaDonBan&return_action=DetailView", "Tạo HĐ đầu ra", 'CreateEC_HoaDonBan', 'EC_HoaDonBan'];
if ($editing_rights) $module_menu[] = ["index.php?module=EC_HoaDonBan&action=invoicereport&return_module=EC_HoaDonBan&return_action=DetailView", "Tồn kho tổng hợp", 'EC_HoaDonBan', 'EC_HoaDonBan'];
if ($editing_rights) $module_menu[] = ["index.php?module=EC_HoaDonBan&action=ioinvoice&return_module=EC_HoaDonBan&return_action=DetailView", "Mua vào - bán ra", 'EC_HoaDonBan', 'EC_HoaDonBan'];
