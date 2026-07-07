<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class Viewreturnbooking extends SugarView
{
    function display()
    {
        $smarty = new Sugar_Smarty();
        $this->populateContent($smarty);
        $smarty->display('modules/EC_HoanVe/tpls/view_returnbooking.tpl');
    }

    function populateContent($smarty)
    {
        if (isset($_POST['clear_search'])) {
            unset($_POST);
        }

        if (!isset($_POST['from_date']) || empty($_POST['from_date']) || !empty($_POST['from_date']) && strtotime($_POST['from_date']) < strtotime('2021-01-01')) {
            $_POST['from_date'] = date('01-m-') . date('Y');
        }

        if (!isset($_POST['to_date']) || empty($_POST['to_date']) || !empty($_POST['to_date']) && strtotime($_POST['to_date']) < strtotime('2021-01-01')) {
            $_POST['to_date'] = date('d-m-Y');
        }

        if (!isset($_POST['bk_search']) || empty($_POST['bk_search'])) {
            $_POST['bk_search'] = '';
        }

        if (!isset($_POST['return_stt'])) {
            $_POST['return_stt'] = '';
        }

        $warning = '';
        if (strtotime($_POST['from_date']) <= strtotime($_POST['to_date'])) {
            $smarty->assign('RETURN_BK', $this->populatereturnbooking($_POST['from_date'], $_POST['to_date'], $_POST['bk_search'], $_POST['return_stt']));
        } else {
            $warning = 'Từ ngày < Đến ngày';
        }
        $smarty->assign('warning_sen', $warning);

        if (!empty($_POST['bk_search'])) {
            $_POST['from_date'] = '';
            $_POST['to_date'] = '';
            $_POST['return_stt'] = '';
        }

        if (!empty($_POST['from_date'])) {
            $smarty->assign('from_date', $_POST['from_date']);
        }

        // thời gian tìm kiếm, từ ngày
        if (!empty($_POST['to_date'])) {
            $smarty->assign('to_date', $_POST['to_date']);
        }

        // thời gian tìm kiếm, đến ngày
        if (!empty($_POST['bk_search'])) {
            $smarty->assign('bk_search', $_POST['bk_search']);
        }

        // tình trạng hoàn vé
        $return_stt = array(
            0 => 'Tất cả',
            1 => 'Nợ khách',
            2 => 'Hãng nợ',
            3 => 'Đang hoàn',
            4 => 'Đã hoàn',
            5 => 'Khác'
        );
        $smarty->assign('return_stt', get_select_options_with_id($return_stt, $_POST['return_stt']));
    }

    function populatereturnbooking($from_date, $to_date, $booking, $return_stt)
    {
        $sql_search = '';
        if (!empty($booking)) {
            $sql_search = ' AND bk.name = "' . $this->bean->db->quote($booking) . '"';
        } else {
            $sql_search = ' AND DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) >= "' . date('Y-m-d', strtotime($from_date)) . '"
            AND DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) <= "' . date('Y-m-d', strtotime($to_date)) . '"';
        }

        $sql = '
            SELECT
                booking_id, booking_name, hoanve_id, bk_date, booking_date_entered, hv_status
                , GROUP_CONCAT(
                    CONCAT_WS(
                        "|"
                        , direction
                        , airline_code
                        , pnr
                        , total_sup_return
                        , sup_return
                        , total_payment
                    )
                ) AS return_inf
                , SUM(total_sup_return) AS total_sup_return
                , SUM(sup_return) AS sup_return
                , SUM(total_payment) AS total_payment
                , SUM(DISTINCT
                    IFNULL((
                        SELECT SUM(IFNULL(p.amount,0))
                        FROM ec_payment_voucher p
                        WHERE p.deleted = 0
                        AND p.pv_status = 3
                        AND p.hoanve_id = t.hoanve_id
                    ), 0)
                ) AS paid
            FROM (
                SELECT
                    bk.id AS booking_id, bk.name AS booking_name, hv.tinhtrang AS hv_status
                    , hv.id AS hoanve_id, bk.date_entered AS booking_date_entered
                    , cth.chieubay AS direction, cth.airline_code
                    , GROUP_CONCAT(DISTINCT TRIM(cth.pnr) SEPARATOR "; ") AS pnr
                    , DATE_FORMAT(
                        DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)
                        , "%d-%m-%Y"
                    ) AS bk_date
                    , SUM(IFNULL(cth.sotienhang, 0)) AS total_sup_return
                    , SUM(IF(hv.tinhtrang = 1, IFNULL(cth.sotienhang, 0), 0)) AS sup_return
                    , SUM(cth.sotienkhach) AS total_payment
                FROM ec_chitiethoanve cth
                INNER JOIN ec_hoanve hv ON hv.id = cth.hoanve_id
                INNER JOIN ec_flight_bookings bk
                ON bk.id = hv.booking_id
                AND bk.deleted = 0
                WHERE cth.deleted = 0
                ' . $sql_search . '
                GROUP BY cth.chieubay, cth.airline_code, hv.id
                ORDER BY booking_date_entered, cth.chieubay
            ) AS t
            GROUP BY t.booking_id
            ORDER BY t.booking_date_entered';

        $res = $this->bean->db->query($sql);
        $i = 0;
        $html = '<tbody>';
        if (empty($booking)) {
            $open_amt = $this->getOpeningAmt($from_date, $return_stt);
            $sup_open_amt = $this->getSupplierOpeningAmt($from_date, $return_stt);
            $html .= '
                <tr>
                    <td></td>
                    <td></td>
                    <td><b>Nợ đầu kỳ</b></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="text-end"><b>' . format_number($sup_open_amt['debt']) . '</b></td>
                    <td class="text-end"><b>' . format_number($sup_open_amt['sup_return']) . '</b></td>
                    <td class="text-end"><b>' . format_number($sup_open_amt['debt'] - $sup_open_amt['sup_return']) . '</b></td>
                    <td class="text-end bg_return"><b>' . format_number($open_amt['total_payment']) . '</b></td>
                    <td class="text-end bg_return"><b>' . format_number($open_amt['total_paid']) . '</b></td>
                    <td class="text-end bg_return"><b>' . format_number($open_amt['return_acc']) . '</b></td>
                    <td class="text-end bg_return"><b>' . ($open_amt['return_acc'] > 0 ? format_number($open_amt['total_payment'] - $open_amt['total_paid'] - $open_amt['return_acc']) : format_number($open_amt['return_acc'])) . '</b></td>
                </tr>';
        }

        $total_payment = $total_paid = $total_return_acc = $total_sup_return = $tt_sup_return = $total_debt = $return_acc = 0;
        $airline_arr = array(
            'VNA' => 'VN',
            'VJA' => 'VJ'
        );
        while ($row = $this->bean->db->fetchByAssoc($res)) {
            // qua booking mới thì reset các giá trị tổng của từng dòng
            $total_sup_return_ln = $sup_return_ln = $total_payment_ln = $return_acc = 0;
            // phân tích các thông tin hoàn vé
            // booking có số dòng = số hãng x số chiều
            $return_inf = explode(',', $row['return_inf']);
            if ($row['hv_status'] == 1) {
                $return_acc -= $row['paid'];
            }

            $return_arr = array();
            $rowspan = 0;
            for ($r = 0; $r < count($return_inf); $r++) {
                $return_dt = explode('|', $return_inf[$r]);
                // kiểm tra chiều và hãng bay, nếu có rồi thì cộng thêm
                $airline_code = array_search($return_dt[1], $airline_arr);
                $airline_code = ($airline_code != false ? $airline_code : $return_dt[1]);
                // nếu đã có trong chiều của booking thì cộng thêm giá trị
                if (in_array($airline_code, array_keys($return_arr))) {
                    if (strpos($return_dt[2], $return_arr[$airline_code]['pnr']) === false) {
                        $return_arr[$airline_code]['pnr'] .= '; ' . $return_dt[2];
                    }
                    $return_arr[$airline_code]['total_sup_return'] += $return_dt[3];
                    $return_arr[$airline_code]['sup_return'] += $return_dt[4];
                    $return_arr[$airline_code]['total_payment'] += $return_dt[5];
                } else {
                    $return_arr[$airline_code]['pnr'] = $return_dt[2];
                    $return_arr[$airline_code]['total_sup_return'] = $return_dt[3];
                    $return_arr[$airline_code]['sup_return'] = $return_dt[4];
                    $return_arr[$airline_code]['total_payment'] = $return_dt[5];
                    $rowspan++;
                }

                if ($row['hv_status'] == 1) {
                    $return_acc += $return_dt[5];
                }

                // giá trị tổng của từng dòng
                $total_sup_return_ln += $return_dt[3];
                $sup_return_ln += $return_dt[4];
                $total_payment_ln += $return_dt[5];
            }

            // tình trạng hoàn vé
            // note_code khớp với value của dropdown return_stt: 1 Nợ khách, 2 Hãng nợ, 3 Đang hoàn, 4 Đã hoàn, 5 Khác
            if ($return_acc < 0) {
                $note_code = 5;
                $note = '<font color="#9932CC"><b>Trả dư</b></font>';
            } else if ($total_sup_return_ln == $sup_return_ln && $total_payment_ln <= $row['paid']) {
                $note_code = 4;
                $note = '<font color="blue"><b>Đã hoàn</b></font>';
            } else if ($sup_return_ln == 0 && $row['paid'] == 0 && $return_acc == 0) {
                $note_code = 3;
                $note = '<font color="hotpink"><b>Đang hoàn</b></font>';
            } else if ($total_sup_return_ln > $sup_return_ln && $row['paid'] > 0) {
                $note_code = 2;
                $note = '<font color="red"><b>Hãng nợ</b></font>';
            } else if ($total_sup_return_ln == $sup_return_ln && $row['paid'] < $total_payment_ln) {
                $note_code = 1;
                $note = '<font color="orange"><b>Nợ khách</b></font>';
            } else {
                $note_code = 5;
                $note = '<b>Khác</b>';
            }

            // lọc theo tình trạng đã chọn trên dropdown ('' hoặc '0' = Tất cả)
            if ($return_stt !== '' && $return_stt !== '0' && (string) $note_code !== (string) $return_stt) {
                continue;
            }

            // giá trị tổng cuối - chỉ cộng cho các booking đã qua bộ lọc tình trạng
            $total_payment += $total_payment_ln;
            $total_sup_return += $total_sup_return_ln;
            $tt_sup_return += $sup_return_ln;
            $total_debt += $total_sup_return_ln - $sup_return_ln;

            $b_row = 0;
            foreach ($return_arr as $return_key => $return_val) {
                if ($b_row == 0) {
                    $html .= '<tr class="return_ln' . $i . '">';
                    $html .= '<td class="text-center" rowspan="' . $rowspan . '">' . ($i + 1) . '</td>';
                    $html .= '<td class="text-center" rowspan="' . $rowspan . '">' . $row['bk_date'] . '</td>';
                    $html .= '<td class="text-center" rowspan="' . $rowspan . '" style="color: #0b578f; text-decoration: underline; font-weight: 600;"><span onclick="document.getElementById(\'booking_basic\').value=\'' . $row['booking_name'] . '\';document.getElementById(\'booking_id_basic\').value=\'' . $row['booking_id'] . '\';$(\'#search_bk_return\').submit();">' . ($row['booking_name']) . '</span></td>';
                    $html .= '<td class="text-center">' . $return_key . '</td>';
                    $html .= '<td class="text-center">' . $return_val['pnr'] . '</td>';
                    $html .= '<td class="text-center" rowspan="' . $rowspan . '">' . $note . '</td>';
                    $html .= '<td class="text-end">' . format_number($return_val['total_sup_return']) . '</td>';
                    $html .= '<td class="text-end">' . format_number($return_val['sup_return']) . '</td>';
                    $html .= '<td class="text-end">' . format_number($return_val['total_sup_return'] - $return_val['sup_return']) . '</td>';
                    $html .= '<td class="text-end bg_return fw-bold">' . format_number($return_val['total_payment']) . '</td>';
                    $html .= '<td class="text-end bg_return fw-bold" rowspan="' . $rowspan . '">' . format_number($row['paid']) . '</td>';
                    $html .= '<td class="text-end bg_return fw-bold" rowspan="' . $rowspan . '">' . format_number($return_acc) . '</td>';
                    $html .= '<td class="text-end bg_return fw-bold" rowspan="' . $rowspan . '">' . ($return_acc > 0 ? format_number($return_val['total_payment'] - $row['paid'] - $return_acc) : format_number($return_acc)) . '</td>';
                    $html .= '</tr>';
                } else {
                    $html .= '
                    <tr class="return_ln' . $i . '">
                        <td class="text-center">' . $airline_code . '</td>
                        <td class="text-center">' .  $return_arr[$airline_code]['pnr'] . '</td>
                        <td class="text-end">' . format_number($return_arr[$airline_code]['total_sup_return']) . '</td>
                        <td class="text-end">' . format_number($return_arr[$airline_code]['sup_return']) . '</td>
                        <td class="text-end">' . format_number($return_arr[$airline_code]['total_sup_return'] - $return_arr[$airline_code]['sup_return']) . '</td>
                        <td class="text-end bg_return fw-bold">' . format_number($return_arr[$airline_code]['total_payment']) . '</td>
                    </tr>';
                }

                $b_row++;
            }

            $i++;

            $total_return_acc += $return_acc;
            $total_paid += $row['paid'];
        }

        $html .= '
            <tr>
                <td></td>
                <td></td>
                <td><b>Nợ cuối kỳ<b></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-end"><b>' . format_number($total_sup_return + $sup_open_amt['debt']) . '</b></td>
                <td class="text-end"><b>' . format_number($tt_sup_return + $sup_open_amt['sup_return']) . '</b></td>
                <td class="text-end"><b>' . format_number($total_debt + $sup_open_amt['debt'] - $sup_open_amt['sup_return']) . '</b></td>
                <td class="text-end bg_return fw-bold"><b>' . format_number($total_payment + $open_amt['total_payment']) . '</b></td>
                <td class="text-end bg_return fw-bold"><b>' . format_number($total_paid + $open_amt['total_paid']) . '</b></td>
                <td class="text-end bg_return fw-bold"><b><font color="red">' . format_number($total_return_acc + $open_amt['return_acc']) . '</font></b></td>
                <td class="text-end bg_return fw-bold"><b>' . format_number($total_payment - $total_paid - $total_return_acc + $open_amt['total_payment'] - $open_amt['total_paid'] - $open_amt['return_acc']) . '</b></td>
            </tr>';

        $html .= '</tbody>';
        return $html;
    }

    // tính nợ đầu kỳ của tiền phải trả khách
    function getOpeningAmt($to_date, $return_stt)
    {
        switch ($return_stt) {
            case '4': // Đã hoàn
                $sql_con = 'HAVING return_acc <= 0';
                break;
            default:
                $sql_con = '';
                break;
        }
        // tính từ đầu năm 2021 trước đó không tính
        $sql = '
            SELECT SUM(tongtienkhach) AS total_payment
                , SUM(paid) AS paid
                , SUM(return_acc) AS return_acc
            FROM (
                SELECT hv.tongtienkhach
                , IFNULL((
                    SELECT SUM(IFNULL(p.amount,0))
                    FROM ec_payment_voucher p
                    WHERE p.deleted = 0 
                    AND p.pv_status = 3
                    AND p.hoanve_id = hv.id
                ), 0) AS paid
                , IF(
                    hv.tinhtrang = 1
                    , hv.tongtienkhach 
                    - IFNULL((
                            SELECT SUM(IFNULL(p.amount,0))
                            FROM ec_payment_voucher p
                            WHERE p.deleted = 0 
                            AND p.pv_status = 3
                            AND p.hoanve_id = hv.id
                        ), 0)
                    , 0
                ) AS return_acc
                FROM ec_hoanve hv
                INNER JOIN ec_flight_bookings bk 
                ON bk.id = hv.booking_id
                AND bk.deleted = 0
                WHERE hv.deleted = 0
                AND DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) >= "2021-01-01"
                AND DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) < "' . date('Y-m-d', strtotime($to_date)) . '"
                GROUP BY hv.id
                ' . $sql_con . '
            ) AS t';
        $res = $this->bean->db->query($sql);
        $row = $this->bean->db->fetchByAssoc($res);
        return array('total_payment' => $row['total_payment'], 'total_paid' => $row['paid'], 'return_acc' => $row['return_acc']);
    }

    // tính nợ đầu kỳ của tiền phải thu hãng
    function getSupplierOpeningAmt($to_date, $return_stt)
    {
        switch ($return_stt) {
            case '4': // Đã hoàn
                $sql_con = 'HAVING debt = sup_return';
                break;
            default:
                $sql_con = '';
                break;
        }
        // tính từ đầu năm 2021 trước đó không tính
        $sql = '
            SELECT SUM(debt) AS debt
                 , SUM(sup_return) AS sup_return 
            FROM (
                SELECT IFNULL(hv.tongtienhang, 0) AS debt
                     , IF(hv.tinhtrang = 1, IFNULL(hv.tongtienhang, 0), 0) AS sup_return
                FROM ec_hoanve hv
                INNER JOIN ec_flight_bookings bk 
                ON bk.id = hv.booking_id
                AND bk.deleted = 0
                WHERE hv.deleted = 0
                AND DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) >= "2021-01-01"
                AND DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) < "' . date('Y-m-d', strtotime($to_date)) . '"
                GROUP BY hv.id
                ' . $sql_con . '
            ) AS t';
        $res = $this->bean->db->query($sql);
        $row = $this->bean->db->fetchByAssoc($res);
        return array('debt' => (float)$row['debt'], 'sup_return' => (float)$row['sup_return']);
    }
}
