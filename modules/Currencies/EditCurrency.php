<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
if ($current_user->is_admin) {
    require_once('modules/Currencies/ListCurrency.php');
    $lc = new ListCurrency();
    $lc->handleDelete();
    $lc->handleAdd();
    $lc->handleUpdate();
    echo $lc->getTable();
} else {
    echo 'Admin\'s Only';
}
