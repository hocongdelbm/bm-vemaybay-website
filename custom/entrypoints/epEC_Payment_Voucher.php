<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

global $db;

// tính số tiền đã hoàn của 1 phiếu hoàn
if(isset($_POST['for']) && $_POST['for'] == 'getPaidAmt') {
    $ret_voucher = new EC_HoanVe;
    $ret_voucher->retrieve($_POST['return_voucher']);
    $sql = '
        SELECT SUM(IFNULL(amount, 0))
        FROM ec_payment_voucher
        WHERE deleted = 0 AND pv_status = 3
        AND hoanve_id = "' . $_POST['return_voucher'] . '"';
    $paid = $db->getOne($sql);
    $left_amt = $ret_voucher->tongtienkhach - $paid;
    if($left_amt < 0) $left_amt = 0;
    echo (int)$left_amt;
}

// tính số tiền đã hoàn của 1 phiếu thu
if(isset($_POST['for']) && $_POST['for'] == 'getPaid_PT') {
    $refund_voucher = new EC_Receipt_Voucher;
    $refund_voucher->retrieve($_POST['return_pt']);
    $sql = '
        SELECT SUM(IFNULL(amount, 0))
        FROM ec_payment_voucher
        WHERE deleted = 0 AND pv_status = 3
        AND phieuthu_id = "' . $_POST['return_pt'] . '"';
    $paid = $db->getOne($sql);
    $left_amt = $refund_voucher->amount_converted - $paid;
    if($left_amt < 0) $left_amt = 0;
    echo (int)$left_amt;
}