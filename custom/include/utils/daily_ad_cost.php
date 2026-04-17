<?php

/**
 * Chi phí quảng cáo theo ngày (báo cáo DS theo ngày xuất vé).
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

function ad_cost_ensure_table($db)
{
    static $done = false;
    if ($done) {
        return;
    }
    $sql = "CREATE TABLE IF NOT EXISTS ec_daily_ad_cost (
        id CHAR(36) NOT NULL,
        cost_date DATE NOT NULL,
        amount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        created_by CHAR(36) DEFAULT NULL,
        modified_user_id CHAR(36) DEFAULT NULL,
        date_entered DATETIME DEFAULT NULL,
        date_modified DATETIME DEFAULT NULL,
        deleted TINYINT(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (id),
        UNIQUE KEY uk_ec_daily_ad_cost_date (cost_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $db->query($sql);
    $done = true;
}

/**
 * @return array<string,float> Y-m-d => amount
 */
function ad_cost_fetch_map($db, $fromYmd, $toYmd)
{
    ad_cost_ensure_table($db);
    $fromYmd = preg_replace('/[^0-9\-]/', '', (string) $fromYmd);
    $toYmd = preg_replace('/[^0-9\-]/', '', (string) $toYmd);
    if (strlen($fromYmd) !== 10 || strlen($toYmd) !== 10) {
        return [];
    }
    $map = [];
    $q = "SELECT cost_date, amount FROM ec_daily_ad_cost
          WHERE deleted = 0 AND cost_date >= '{$fromYmd}' AND cost_date <= '{$toYmd}'";
    $res = $db->query($q);
    if (!$res) {
        return [];
    }
    while ($row = $db->fetchByAssoc($res)) {
        if (!empty($row['cost_date'])) {
            $map[$row['cost_date']] = (float) $row['amount'];
        }
    }
    return $map;
}

/**
 * Parse số VN (dấu chấm ngăn cách nghìn).
 */
function ad_cost_parse_amount($raw)
{
    $s = preg_replace('/[^\d,\.\-]/', '', (string) $raw);
    $s = str_replace(['.', ','], ['', ''], $s);
    if ($s === '' || $s === '-') {
        return 0.0;
    }

    return (float) $s;
}

/**
 * Ngày được phép nhập/sửa: từ (hôm nay - 2) đến hôm nay (3 ngày liên tiếp).
 */
function ad_cost_is_editable_date($costDateYmd, $todayYmd = null)
{
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    if ($todayYmd === null) {
        $todayYmd = date('Y-m-d');
    }
    $min = date('Y-m-d', strtotime($todayYmd . ' -2 days'));
    return ($costDateYmd >= $min && $costDateYmd <= $todayYmd);
}
