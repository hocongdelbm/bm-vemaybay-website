<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");
date_default_timezone_set("Asia/Ho_Chi_Minh");

class Viewcurrentsales extends SugarView
{
    var $_loai_thu_str = "'4', '5', '10', '11', '12', '13', '14', '16'";

    function display()
    {
        if (ACLController::checkAccess('EC_Flight_Bookings', 'view', true)) {
            $smartyCont = new Sugar_Smarty();
            $this->populateContent($smartyCont);
            $smartyCont->display('modules/EC_Flight_Bookings/tpls/view_currentsales.tpl');
        } else {
            header("Location: index.php?module$=EC_Flight_Bookings&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
            exit();
        }
    }

    function populateContent($smartyobj)
    {
        global $db, $app_list_strings, $current_user;

        $sql_search         = "";
        $sql_search_rv      = "";
        $sql_search_hv      = "";
        $from_date          = isset($_REQUEST['from_date']) ? preg_replace('/[^0-9\-]/', '', $_REQUEST['from_date']) : date('Y-m-d');
        $to_date            = isset($_REQUEST['to_date']) ? preg_replace('/[^0-9\-]/', '', $_REQUEST['to_date']) : date('Y-m-d');
        $fr_date_arr        = explode('-', $from_date); // 0=>day, 1=>month, 2=>year
        $to_date_arr        = explode('-', $to_date);

        // From date
        if (!empty($from_date) && checkdate((int)$fr_date_arr[1], (int)$fr_date_arr[0], (int)$fr_date_arr[2])) {
            $sql_search .= " AND bk.date_ticket_issue >= '" . date('Y-m-d', strtotime($from_date)) . "' ";
            $sql_search_rv .= " AND DATE(p.ngayhachtoan) >= '" . date('Y-m-d', strtotime($from_date)) . "' ";
            $sql_search_hv .= " AND DATE(p.ngayhachtoan) >= '" . date('Y-m-d', strtotime($from_date)) . "' ";
            $from_date_value = $from_date;
        } else {
            $sql_search .= " AND bk.date_ticket_issue >= '" . date('Y-m-d') . "' ";
            $sql_search_rv .= " AND DATE(p.ngayhachtoan) >= '" . date('Y-m-d') . "' ";
            $sql_search_hv .= " AND DATE(p.ngayhachtoan) >= '" . date('Y-m-d') . "' ";
            $from_date_value = date('d-m-Y');
        }

        $smartyobj->assign('CURRENT_FROMDATE', date('d-m-Y', strtotime('first day of this month')));
        $smartyobj->assign('CURRENT_TODATE', date('d-m-Y', strtotime('last day of this month')));
        $smartyobj->assign('PREVIOUS_FROMDATE', date('d-m-Y', strtotime('first day of last month')));
        $smartyobj->assign('PREVIOUS_TODATE', date('t-m-Y', strtotime('last day of last month')));
        $smartyobj->assign('PREVIOUSYEAR_FROMDATE', date('d-m-Y', strtotime('-1 year')));
        $smartyobj->assign('PREVIOUSYEAR_TODATE', date('d-m-Y', strtotime('-1 year')));

        switch (ceil(date('n') / 3)) {
            case 1:
                $quater_fromdate = '01-01-' . date('Y');
                $quater_todate = '31-03-' . date('Y');
                break;
            case 2:
                $quater_fromdate = '01-04-' . date('Y');
                $quater_todate = '30-06-' . date('Y');
                break;
            case 3:
                $quater_fromdate = '01-07-' . date('Y');
                $quater_todate = '30-09-' . date('Y');
                break;
            case 4:
                $quater_fromdate = '01-10-' . date('Y');
                $quater_todate = '31-12-' . date('Y');
                break;
            default:
                break;
        }

        // '<option  fromdate="' . date('d-m-Y') . '" todate="' . date('d-m-Y') . '">Hôm nay</option>',
        // '<option  fromdate="' . date('d-m-Y', strtotime('-1 day')) . '" todate="' . date('d-m-Y', strtotime('-1 day')) . '">Hôm qua</option>',
        // '<option  fromdate="' . date('d-m-Y', strtotime('monday this week')) . '" todate="' . date('d-m-Y', strtotime('sunday this week')) . '">Tuần này</option>',
        // '<option  fromdate="' . date('d-m-Y', strtotime('monday last week')) . '" todate="' . date('d-m-Y', strtotime('sunday last week')) . '">Tuần trước</option>',
        $arr_date = array(
            '<option value=""  fromdate="" todate="">---Trống---</option>',
            '<option value="this_month" fromdate="' . date('d-m-Y', strtotime('first day of this month')) . '" todate="' . date('d-m-Y', strtotime('last day of this month')) . '">Tháng này</option>',
            '<option value="previous_month" fromdate="' . date('d-m-Y', strtotime('first day of last month')) . '" todate="' . date('d-m-Y', strtotime('last day of last month')) . '">Tháng trước</option>',
            '<option value="quater_this_month" fromdate="' . $quater_fromdate . '" todate="' . $quater_todate . '">Quý này</option>',
            '<option value="quater_previous_month" fromdate="' . date('d-m-Y', strtotime('-3 months', strtotime($quater_fromdate))) . '" todate="' . date('d-m-Y', strtotime('-3 months', strtotime($quater_todate))) . '">Quý trước</option>',
            '<option value="this_year" fromdate="' . date('01-01-Y') . '" todate="' . date('31-12-Y') . '">Năm nay</option>',
            '<option value="previous_year" fromdate="' . date('01-01-Y', strtotime('-1 year')) . '" todate="' . date('31-12-Y', strtotime('-1 year')) . '">Năm trước</option>',
        );
        $smartyobj->assign('DATE_OPTION', implode('', $arr_date));

        // OTPION DATE - RADIO
        $smartyobj->assign('YESTERDAY_FROMDATE', date('d-m-Y', strtotime('-1 day')));
        $smartyobj->assign('YESTERDAY_TODATE', date('d-m-Y', strtotime('-1 day')));
        $smartyobj->assign('DAYBEFORE_FROMDATE', date('d-m-Y', strtotime('-2 day')));
        $smartyobj->assign('DAYBEFORE_TODATE', date('d-m-Y', strtotime('-2 day')));
        $smartyobj->assign('CURRENT_WEEK_FROMDATE', date('d-m-Y', strtotime('monday this week')));
        $smartyobj->assign('CURRENT_WEEK_TODATE', date('d-m-Y', strtotime('sunday this week')));
        $smartyobj->assign('PREVIOUS_WEEK_FROMDATE', date('d-m-Y', strtotime('monday previous week')));
        $smartyobj->assign('PREVIOUS_WEEK_TODATE', date('d-m-Y', strtotime('sunday previous week')));

        $smartyobj->assign('CURRENT_QUARTER_FROMDATE', $quater_fromdate);
        $smartyobj->assign('CURRENT_QUARTER_TODATE', $quater_todate);
        $smartyobj->assign('PREVIOUS_QUARTER_FROMDATE', date('d-m-Y', strtotime('-3 months', strtotime($quater_fromdate))));
        $smartyobj->assign('PREVIOUS_QUARTER_TODATE', date('d-m-Y', strtotime('-3 months', strtotime($quater_todate))));

        // To date
        if (!empty($to_date) && checkdate((int)$to_date_arr[1], (int)$to_date_arr[0], (int)$to_date_arr[2])) {
            $sql_search .= " AND bk.date_ticket_issue <= '" . date('Y-m-d', strtotime($to_date)) . "' ";
            $sql_search_rv .= " AND DATE(p.ngayhachtoan) <= '" . date('Y-m-d', strtotime($to_date)) . "' ";
            $sql_search_hv .= " AND DATE(p.ngayhachtoan) <= '" . date('Y-m-d', strtotime($to_date)) . "' ";
            $to_date_value = $to_date;
        } else {
            $sql_search .= " AND bk.date_ticket_issue <= '" . date('Y-m-d') . "' ";
            $sql_search_rv .= " AND DATE(p.ngayhachtoan) <= '" . date('Y-m-d') . "' ";
            $sql_search_hv .= " AND DATE(p.ngayhachtoan) <= '" . date('Y-m-d') . "' ";
            $to_date_value = date('d-m-Y');
        }

        // Permission
        $user_title = strtolower(preg_replace('/\W/', '', $current_user->title));
        if ($user_title != 'quanly' && !is_admin($current_user) && $user_title != 'ketoan') {
            $sql_search .= " AND bk.assigned_user_id='" . $current_user->id . "' ";
            $sql_search_rv .= " AND p.assigned_user_id='" . $current_user->id . "' ";
            $sql_search_hv .= " AND p.assigned_user_id='" . $current_user->id . "' ";
        }

        // XUẤT VÉ NỘI ĐỊA
        $sql = "SELECT SUM(total_quantity) AS soluongve,
					SUM(subtotal_amount) AS doanhthu,
					SUM(total_bought_price) AS giamua,
                    SUM(discount_amt) AS chietkhau
				FROM (
					SELECT 
                        bk.id,
                        SUM(bkd.quantity) AS total_quantity,
                        bk.total_amount AS subtotal_amount,
                        (
                            SUM(IFNULL(bkd.total_bought_price,0))
                            +
                            IFNULL((
                                SELECT IF(bk.flight_type='0'
                                        ,SUM(IF(px.luggage_price>0, IFNULL(px.luggage_purchase,0), 0) + IF(px.luggage_price_inbound>0, IFNULL(px.luggage_purchase_inbound,0), 0))
                                        ,SUM(IF(px.luggage_price>0, IFNULL(px.luggage_purchase,0), 0))
                                        )
                                FROM ec_booking_passengers px
                                WHERE px.booking_id=bk.id AND px.deleted=0 AND px.add_type IS NULL
                            ), 0)
                        ) AS total_bought_price,
                        (SELECT amount FROM ec_payment_voucher WHERE booking_id=bkd.booking_id AND pv_status='3' AND ec_payment_types_id_c='3f9f8060-1866-2b2e-8322-52e36b8f58d5' AND deleted=0 LIMIT 1) AS discount_amt
			    	FROM ec_booking_details bkd 
                    LEFT JOIN ec_flight_bookings bk ON bkd.booking_id=bk.id AND bk.deleted=0 
			    	WHERE bk.booking_status IN ('7','8') AND bk.ticket_type <> 2" . $sql_search . " AND bkd.deleted=0
					GROUP BY bk.id 

					UNION
					SELECT p.id,
						0 AS total_quantity,
						SUM(IFNULL(p.amount,0)) AS subtotal_amount,
						SUM(IFNULL(p.bought_amount,0) + IFNULL(p.bought_amount2,0) + IFNULL(p.bought_amount3,0)) AS total_bought_price,
                        0 AS discount_amt
					FROM ec_receipt_voucher p
                    LEFT JOIN ec_flight_bookings bk ON bk.id = p.booking_id AND bk.deleted = 0
					WHERE p.rv_status IN (1, 2) 
                        " . $sql_search_rv . "
                        AND p.loai_thu IN (" . $this->_loai_thu_str . ")
                        AND IF(p.loai_thu = 10, IF(p.bought_amount IS NULL OR p.bought_amount = 0, 0, 1), 1) = 1
                        AND bk.ticket_type <> 2
                        AND p.deleted=0
					GROUP BY p.id
					
					UNION
					SELECT p.id,
						-(SELECT COUNT(id) FROM ec_chitiethoanve WHERE hoanve_id = p.id AND deleted = 0) AS total_quantity,
						SUM(IFNULL(p.tongtienhang,0)) AS subtotal_amount,
						SUM(IFNULL(p.tongtienkhach,0)) AS total_bought_price,
                        0 AS discount_amt
					FROM ec_hoanve p
                    LEFT JOIN ec_flight_bookings bk ON bk.id = p.booking_id AND bk.deleted = 0
					WHERE p.tinhtrang='1' AND bk.ticket_type <> 2 AND p.deleted=0 " . $sql_search_hv . "
					GROUP BY p.id
					
				) AS temp";

        // if($current_user->user_name == 'hungnh'){
        //     pr($sql);
        // }

        // Lấy Tổng số vé và booking DOMESTIC
        $sql2 = "SELECT bk.booking_status, SUM(bk.total_qty) AS total_quantitys, count(*) AS total_bookings
        FROM ec_flight_bookings bk
        WHERE bk.ticket_type <> 2 AND bk.date_entered >= '" . date('Y-m-d H:i:s', strtotime($from_date) - 7 * 3600) . "' AND bk.date_entered <= '" . date('Y-m-d 16:59:59', strtotime($to_date)) . "' AND bk.deleted=0
        GROUP BY bk.booking_status";

        // if($current_user->user_name == 'hungnh'){
        //     pr($sql2);
        // }

        $total_bookings         = 0;
        $total_quantity         = 0;
        $total_bookings_cancel  = 0;
        $total_bookings_succes  = 0;
        $array_complete         = array('7', '8');

        $res2        = $db->query($sql2);
        while ($row2 = $db->fetchByAssoc($res2)) {
            // Tính tổng số booking và tổng số vé
            $total_bookings += $row2['total_bookings'];
            $total_quantity += $row2['total_quantitys'];

            // Tổng số vé hủy
            if (!in_array($row2['booking_status'], $array_complete)) {
                $total_bookings_cancel += $row2['total_bookings'];
            }

            // Tổng số vé hoàn tất
            if (in_array($row2['booking_status'], $array_complete)) {
                $total_bookings_succes += $row2['total_bookings'];
            }
        }
        // END TOTAL

        // XUẤT VÉ QUỐC TẾ
        $sql_inter = "SELECT SUM(total_quantity) AS soluongve,
					SUM(subtotal_amount) AS doanhthu,
					SUM(total_bought_price) AS giamua,
                    SUM(discount_amt) AS chietkhau
				FROM (
					SELECT 
                        bk.id,
                        SUM(bkd.quantity) AS total_quantity,
                        bk.total_amount AS subtotal_amount,
                        (
                            SUM(IFNULL(bkd.total_bought_price,0))
                            +
                            IFNULL((
                                SELECT IF(bk.flight_type='0'
                                        ,SUM(IF(px.luggage_price>0, IFNULL(px.luggage_purchase,0), 0) + IF(px.luggage_price_inbound>0, IFNULL(px.luggage_purchase_inbound,0), 0))
                                        ,SUM(IF(px.luggage_price>0, IFNULL(px.luggage_purchase,0), 0))
                                        )
                                FROM ec_booking_passengers px
                                WHERE px.booking_id=bk.id AND px.deleted=0 AND px.add_type IS NULL
                            ), 0)
                        ) AS total_bought_price,
                        (SELECT amount FROM ec_payment_voucher WHERE booking_id=bkd.booking_id AND pv_status='3' AND ec_payment_types_id_c='3f9f8060-1866-2b2e-8322-52e36b8f58d5' AND deleted=0 LIMIT 1) AS discount_amt
			    	FROM ec_booking_details bkd 
                    LEFT JOIN ec_flight_bookings bk ON bkd.booking_id=bk.id AND bk.deleted=0 
			    	WHERE bk.booking_status IN ('7','8') AND bk.ticket_type = 2" . $sql_search . " AND bkd.deleted=0
					GROUP BY bk.id 

					UNION
					SELECT p.id,
						0 AS total_quantity,
						SUM(IFNULL(p.amount,0)) AS subtotal_amount,
						SUM(IFNULL(p.bought_amount,0) + IFNULL(p.bought_amount2,0) + IFNULL(p.bought_amount3,0)) AS total_bought_price,
                        0 AS discount_amt
					FROM ec_receipt_voucher p
                    LEFT JOIN ec_flight_bookings bk ON bk.id = p.booking_id AND bk.deleted = 0
					WHERE p.rv_status IN (1, 2) 
                        " . $sql_search_rv . "
                        AND p.loai_thu IN (" . $this->_loai_thu_str . ")
                        AND IF(p.loai_thu = 10, IF(p.bought_amount IS NULL OR p.bought_amount = 0, 0, 1), 1) = 1
                        AND bk.ticket_type = 2
                        AND p.deleted=0
					GROUP BY p.id
					
					UNION
					SELECT p.id,
						-(SELECT COUNT(id) FROM ec_chitiethoanve WHERE hoanve_id = p.id AND deleted = 0) AS total_quantity,
						SUM(IFNULL(p.tongtienhang,0)) AS subtotal_amount,
						SUM(IFNULL(p.tongtienkhach,0)) AS total_bought_price,
                        0 AS discount_amt
					FROM ec_hoanve p
                    LEFT JOIN ec_flight_bookings bk ON bk.id = p.booking_id AND bk.deleted = 0
					WHERE p.tinhtrang='1' AND bk.ticket_type = 2 AND p.deleted=0 " . $sql_search_hv . "
					GROUP BY p.id
					
        ) AS temp";

        $res_inter  = $db->query($sql_inter);
        $row_inter  = $db->fetchByAssoc($res_inter);
        $ds_inter   = ($row_inter['doanhthu'] - $row_inter['giamua'] - $row_inter['chietkhau']);
        $sove_inter = $row_inter['soluongve'];
        // END TOTAL_INTER 

        // if($current_user->user_name == 'hungnh'){
        //     pr($sql_inter);
        // }

        $html   = '';
        $res    = $db->query($sql);
        $row    = $db->fetchByAssoc($res);
        $date_diff = 1;
        if ((strtotime($to_date_value) - strtotime($from_date_value)) > 1) {
            $date_diff = round((strtotime($to_date_value) - strtotime($from_date_value)) / 86400);
        }
        $target = (int)$app_list_strings['company_info_list']['ticket_target'] * $date_diff;
        $paid_booking = $this->getPaidBooking();

        //  if($current_user->user_name == 'hungnh'){
        //     pr($paid_booking);
        // }

        // D/s ID User được phép xem
        $array_iduser = array(
            '37cd4853-721c-9808-af64-5600c8835d03' //Ngandtk
        );

        if (is_admin($current_user) || $current_user->title == 'QuanLy' || in_array($current_user->id, $array_iduser)) {
            // % Booking = Tỷ lệ booking thành công / tổng số booking
            // % Số vé = Tỷ lệ vé đó / tổng số vé
            // % Hủy BK = Tỷ lệ booking thất bại / tổng số booking. (Booking chưa xuất được coi là thất bại)
            $ds_noidia = $row['doanhthu'] - $row['giamua'] - $row['chietkhau'];
            $html .= '<thead>
                        <tr>
                            <th>Số vé</th>
                            <th>% Số vé</th>
                            <th>Doanh số</th>
                            <th>Chưa xuất</th>
                            <th>Vé quốc tế</th>
                            <th>Booking</th>
                            <th>% Booking</th>
                            <th>% Hủy Booking</th>
                        </tr>
                        </thead>
                    <tr>
                        <td class="text-center"><img src="themes/SuiteP/images/modules/ec_flight_booking/ticket.svg" alt="ticket" border="0" width="80" /></td>
                        <td class="text-center"><img src="themes/SuiteP/images/modules/ec_flight_booking/ticket_percent.png" alt="ticket_percent" border="0" width="80" /></td>
                        <td class="text-center"><img src="themes/SuiteP/images/modules/ec_flight_booking/money-bag.svg" alt="note" border="0" width="80" /></td>
                        <td class="text-center"><img src="themes/SuiteP/images/modules/ec_flight_booking/warning.svg" alt="warning" border="0" width="80" /></td>
                        <td class="text-center"><img src="themes/SuiteP/images/modules/ec_flight_booking/inter_ticket.svg" alt="inter_ticket" border="0" width="80" /></td>
                        <td class="text-center"><img src="themes/SuiteP/images/modules/ec_flight_booking/flight_booking.svg" alt="flight_booking" border="0" width="80" /></td>
                        <td class="text-center"><img src="themes/SuiteP/images/modules/ec_flight_booking/success_booking_ticket.svg" alt="success_booking_ticket" border="0" width="80" /></td>
                        <td class="text-center"><img src="themes/SuiteP/images/modules/ec_flight_booking/cancel_booking_ticket.svg" alt="cancel_booking_ticket" border="0" width="80" /></td>
                    </tr>
                ';
            $html .= '<tr class="footer-tr">
                            <td><span class="detail_domestic">' . format_number($row['soluongve']) . '</span> / ' . $total_quantity . '</td>
                            <td>' . (empty($total_quantity) ? 0 : format_number(($row['soluongve']) * 100 / $total_quantity)) . '%</td>
                            <td>' . format_number($ds_noidia) . '</td>
                            <td>' . format_number($paid_booking['total_tkt']) . '</td>
                            <td><span class="detail_inter">' . format_number($sove_inter) . '</span> / ' . format_number($ds_inter) . '</td>
                            <td>' . format_number($total_bookings) . '</td>
                            <td>' . (empty($total_bookings) ? 0 : format_number($total_bookings_succes * 100 / $total_bookings)) . '%</td>
                            <td>' . (empty($total_bookings) ? 0 : format_number($total_bookings_cancel * 100 / $total_bookings)) . '%</td>
                        </tr>
                        <tr class="footer-tr">
                            <td class="text-start bg-yellow">Tổng doanh số</td>
                            <td class="bg-yellow">'.format_number($ds_noidia + $ds_inter + $paid_booking['total_ds']).'</td>
                            <td class="text-start bg-yellow">Tổng số vé</td>
                            <td class="bg-yellow">'.format_number($sove_inter + $row['soluongve'] + $paid_booking['total_tkt']).'</td>
                        </tr>';

        } else {
            $html .= '<thead>
                        <tr>
                            <th>Số vé</th>
                            <th>Doanh thu</th>
                            <th>Doanh số</th>
                            <th>Mục tiêu</th>
                            <th>Thực hiện</th>
                            <th>Chưa xuất</th>
                        </tr>
                    </thead>
                    <tr>
                        <td class="text-center"><img src="themes/SuiteP/images/modules/ec_flight_booking/ticket.svg" alt="ticket" border="0" width="96" /></td>
                        <td class="text-center"><img src="themes/SuiteP/images/modules/ec_flight_booking/doanhthu.svg" alt="coin" border="0" width="96" /></td>
                        <td class="text-center"><img src="themes/SuiteP/images/modules/ec_flight_booking/money-bag.svg" alt="note" border="0" width="96" /></td>
                        <td class="text-center"><img src="themes/SuiteP/images/modules/ec_flight_booking/target.svg" alt="target" border="0" width="96" /></td>
                        <td class="text-center"><img src="themes/SuiteP/images/modules/ec_flight_booking/percent.svg" alt="percent" border="0" width="96" /></td>
                        <td class="text-center"><img src="themes/SuiteP/images/modules/ec_flight_booking/warning.svg" alt="warning" border="0" width="96" /></td>
                    </tr>
                ';
            $html .= '<tr class="footer-tr">
                        <td>' . format_number($row['soluongve']) . '</td>
                        <td>' . format_number($row['doanhthu']) . '</td>
                        <td>' . format_number($row['doanhthu'] - $row['giamua'] - $row['chietkhau']) . '</td>
                        <td>' . format_number($target) . '</td>
                        <td>' . format_number($row['soluongve'] / $target * 100) . ' %</td>
                        <td>' . format_number($paid_booking['total_tkt']) . '</td>
                    </tr>';
        }

        $smartyobj->assign('CHECK_VIEW_PERMISSION', false);
        $smartyobj->assign('DATA', $html);
        $smartyobj->assign('FROM_DATE_VALUE', $from_date_value);
        $smartyobj->assign('TO_DATE_VALUE', $to_date_value);
        $smartyobj->assign('DATA2', $paid_booking['html']);
        $smartyobj->assign('SODONG', format_number($paid_booking['total_row']));
        $smartyobj->assign('TONGTIENDOANHSO', format_number($paid_booking['total_ds']));
        $smartyobj->assign('TONGTIENCHUAXUAT', format_number($paid_booking['total_amt']));
        $smartyobj->assign('TONGSOVECHUAXUAT', format_number($paid_booking['total_tkt']));
    }

    /**
     * Get paid booking list but ticket not issued
     * @return array
     */
    function getPaidBooking()
    {
        global $db, $app_list_strings, $timedate, $current_user;
        $date_format = $timedate->get_date_time_format();

        $total_row  = 0;
        $total_amt  = 0;
        $total_ds   = 0;
        $total_tkt  = 0;
        $arr        = array();
        $html       = '';

        // Booking ở tình trạng xác nhận -> hiện hết lên báo cáo
        // Booking ở tình trạng xuất vé -> chỉ lấy booking có những vé chưa xuất
        $sql = "SELECT 
                    (SELECT u.user_name FROM users u WHERE u.id=b.assigned_user_id AND u.deleted=0 LIMIT 1) AS user_name,
                    (SELECT u.id FROM users u WHERE u.id=b.assigned_user_id AND u.deleted=0 LIMIT 1) AS user_id,
					b.name AS booking,
					b.id AS booking_id,
					(SELECT i.departure FROM ec_booking_itineraries i WHERE i.booking_id=b.id AND i.direction='0' AND i.deleted=0 LIMIT 1) AS dep_code,
					(SELECT i.arrival FROM ec_booking_itineraries i WHERE i.booking_id=b.id AND i.direction='0' AND i.deleted=0 LIMIT 1) AS arv_code,
					(SELECT i.airline_code FROM ec_booking_itineraries i WHERE i.booking_id=b.id AND i.direction='0' AND i.deleted=0 LIMIT 1) AS dep_airline,
					(SELECT i.airline_code FROM ec_booking_itineraries i WHERE i.booking_id=b.id AND i.direction='1' AND i.deleted=0 LIMIT 1) AS ret_airline,
					(SELECT i.departure_date FROM ec_booking_itineraries i WHERE i.booking_id=b.id AND i.direction='0' AND i.deleted=0 LIMIT 1) AS dep_date,
					(SELECT i.departure_date FROM ec_booking_itineraries i WHERE i.booking_id=b.id AND i.direction='1' AND i.deleted=0 LIMIT 1) AS ret_date,
					b.booking_status,
					b.contact_name,
					b.phone,
					b.email,
					b.description,
					b.total_amount,
					b.date_entered,
					(SELECT SUM(d.quantity) FROM ec_booking_details d WHERE d.booking_id=b.id AND d.deleted=0 LIMIT 1) AS total_tkt,
                    (SELECT SUM(d.quantity) FROM ec_booking_details d WHERE d.booking_id=b.id AND d.supplier_id IS NULL AND d.deleted=0 LIMIT 1) AS total_not_issued,
                    (SELECT SUM(d.total_bought_price) FROM ec_booking_details d WHERE d.booking_id=b.id AND d.deleted=0 LIMIT 1) AS total_bought_price,
					b.flight_type
				FROM ec_flight_bookings b
				WHERE b.booking_status IN ('3', '7') AND b.deleted = 0
				ORDER BY b.date_entered ";

        // if($current_user->user_name == 'hungnh'){
        //     pr($sql);
        // }

        $res = $db->query($sql);
        while ($row = $db->fetchByAssoc($res)) {
            if ($row['booking_status'] == '3' || !empty($row['total_not_issued']) && $row['total_not_issued'] < $row['total_tkt']) {
                // $html .= '<tr>
                // 	<td align="center"><a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['booking_id'] . '" target="_blank">' . $row['booking'] . '</a></td>
                // 	<td align="center">' . $row['dep_code'] . ' - ' . $row['arv_code'] . ($row['flight_type'] == '0' ? ' - ' . $row['dep_code'] : '') . '</td>
                // 	<td align="center">' . $row['dep_airline'] . ($row['flight_type'] == '0' ? '-' . $row['ret_airline'] : '') . '</td>
                // 	<td align="center">' . date($date_format, strtotime($row['dep_date'])) . ($row['flight_type'] == '0' ? '<br />' . date($date_format, strtotime($row['ret_date'])) : '') . '</td>
                // 	<td align="center" class="fw-bold" style="color:' . $app_list_strings['booking_status_color_list'][$row['booking_status']] . '">' . $app_list_strings['booking_status_list'][$row['booking_status']] . '</td>
                // 	<td align="left">' . $row['contact_name'] . '</td>
                // 	<td align="left">' . $row['phone'] . '</td>
                // 	<td align="left" style="word-break: break-word; white-space: normal;">' . $row['email'] . '</td>
                // 	<td align="left">' . $row['description'] . '</td>
                // 	<td align="center">' . format_number($row['total_tkt']) . '</td>
                // 	<td align="right"  class="fw-bold">' . format_number($row['total_amount']) . '</td>
                // 	<td align="center"><a href="index.php?module=Employees&return_module=Employees&action=DetailView&record=' . $row['user_id'] . '" target="_blank">' . $row['user_name'] . '</a></td>
                // 	<td align="center">' . date('d-m-Y H:i', strtotime($row['date_entered']) + 7 * 3600) . '</td>
                // </tr>';
                $html .= '<tr>
    				<td align="center"><a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['booking_id'] . '" target="_blank">' . $row['booking'] . '</a></td>
    				<td align="center">' . $row['dep_code'] . ' - ' . $row['arv_code'] . ($row['flight_type'] == '0' ? ' - ' . $row['dep_code'] : '') . '</td>
    				<td align="center" class="hide-mobile">' . $row['dep_airline'] . ($row['flight_type'] == '0' ? '-' . $row['ret_airline'] : '') . '</td>
    				<td align="center" class="hide-mobile">' . date($date_format, strtotime($row['dep_date'])) . ($row['flight_type'] == '0' ? '<br />' . date($date_format, strtotime($row['ret_date'])) : '') . '</td>
    				<td align="center" class="fw-bold hide-mobile" style="color:' . $app_list_strings['booking_status_color_list'][$row['booking_status']] . '">' . $app_list_strings['booking_status_list'][$row['booking_status']] . '</td>
    				<td align="left">' . $row['contact_name'] . '</td>
    				<td align="left">' . $row['phone'] . '</td>
    				<td align="left">' . $row['description'] . '</td>
    				<td align="center">' . format_number($row['total_tkt']) . '</td>
    				<td align="right"  class="fw-bold">' . format_number($row['total_amount'] - $row['total_bought_price']) . '</td>
    				<td align="right"  class="fw-bold hide-mobile">' . format_number($row['total_amount']) . '</td>
    				<td align="center" class="hide-mobile"><a href="index.php?module=Employees&return_module=Employees&action=DetailView&record=' . $row['user_id'] . '" target="_blank">' . $row['user_name'] . '</a></td>
    				<td align="center" class="hide-mobile">' . date('d-m-Y H:i', strtotime($row['date_entered']) + 7 * 3600) . '</td>
    			</tr>';

                $total_row++;
                $total_tkt += $row['total_tkt'];
                $total_amt += $row['total_amount'];
                $total_ds += ($row['total_amount'] - $row['total_bought_price']);
            }
        }
        $arr['total_row']   = $total_row;
        $arr['total_amt']   = $total_amt;
        $arr['total_ds']    = $total_ds;
        $arr['total_tkt']   = $total_tkt;
        $arr['html'] = $html;
        return $arr;
    }
}
