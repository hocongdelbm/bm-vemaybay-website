<?php
require_once("include/Sugar_Smarty.php");
class Viewbkagent extends SugarView
{

    function display()
    {
        global $current_user;
        // if ($current_user->user_name != 'hungnh') {
        //      echo '<p class="alert alert-danger">Hệ thống đang bảo trì. Vui lòng quay lại sau.</p>';
        //      exit;
        // }

        if (is_admin($current_user)) {
            $smartyCont = new Sugar_Smarty();
            $this->populateContent($smartyCont);
            $smartyCont->display('modules/EC_Flight_Bookings/tpls/view_bkagent.tpl');
        } else {
            header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
            exit();
        }
    }

    function populateContent($smarty)
    {
        // đến ngày
        if (empty($_REQUEST['to_date'])) {
            $to_date = date('Y-m-d');
        } else $to_date = $_REQUEST['to_date'];

        // từ ngày
        if (empty($_REQUEST['from_date'])) {
            $from_date = date('Y-m-d', strtotime($to_date));
        } else $from_date = $_REQUEST['from_date'];

        // OPTION DATE
        // check quarter 
        switch (ceil(date('n') / 3)) {
            case 1:
                $cq_from_date = '01-01-' . date('Y');
                $cq_to_date = '31-03-' . date('Y');
                $lq_from_date = '01-01-' . date('Y', strtotime('- 1 year'));
                $lq_to_date = '31-03-' . date('Y', strtotime('- 1 year'));
                break;
            case 2:
                $cq_from_date = '01-04-' . date('Y');
                $cq_to_date = '30-06-' . date('Y');
                $lq_from_date = '01-01-' . date('Y');
                $lq_to_date = '31-03-' . date('Y');
                break;
            case 3:
                $cq_from_date = '01-07-' . date('Y');
                $cq_to_date = '30-09-' . date('Y');
                $lq_from_date = '01-04-' . date('Y');
                $lq_to_date = '30-06-' . date('Y');
                break;
            case 4:
                $cq_from_date = '01-10-' . date('Y');
                $cq_to_date = '31-12-' . date('Y');
                $lq_from_date = '01-07-' . date('Y');
                $lq_to_date = '30-09-' . date('Y');
                break;
            default:
                $cq_from_date = '';
                $cq_to_date = '';
                $lq_from_date = '';
                $lq_to_date = '';
                break;
        }
        $report_term_list = '<option from_date="' . date('d-m-Y') . '" to_date="' . date('d-m-Y') . '">Hôm nay</option>';
        $report_term_list .= '<option from_date="' . date('d-m-Y', strtotime("-1 day")) . '" to_date="' . date('d-m-Y', strtotime("-1 day")) . '">Hôm qua</option>';
        $report_term_list .= '<option from_date="' . date('d-m-Y', strtotime("first day of this month")) . '" to_date="' . date('d-m-Y', strtotime("last day of this month")) . '">Tháng này</option>';
        $report_term_list .= '<option from_date="' . date('d-m-Y', strtotime("first day of previous month")) . '" to_date="' . date('d-m-Y', strtotime("last day of previous month")) . '">Tháng trước</option>';
        $report_term_list .= '<option from_date="' . date('d-m-Y', strtotime($cq_from_date)) . '" to_date="' . date('d-m-Y', strtotime($cq_to_date)) . '">Quý này</option>';
        $report_term_list .= '<option from_date="' . date('d-m-Y', strtotime($lq_from_date)) . '" to_date="' . date('d-m-Y', strtotime($lq_to_date)) . '">Quý trước</option>';
        $smarty->assign('REPORT_TERM_LIST', $report_term_list);

        $smarty->assign('FROM_DATE_VALUE', date('d-m-Y', strtotime($from_date)));
        $smarty->assign('TO_DATE_VALUE', date('d-m-Y', strtotime($to_date)));
        $smarty->assign('AGENT_LIST_TBL', $this->populateBKAgentReport($from_date, $to_date));
        $smarty->assign('BOOKING_LIST_TBL', $this->populateBKReport($from_date, $to_date));
    }

    function populateBKAgentReport($from_date, $to_date)
    {
        global $db;
        $from_date  = date('Y-m-d 00:00:00', strtotime($from_date));
        $to_date    = date('Y-m-d 23:59:59', strtotime($to_date));

        $html = '
            <tr class="airline-row cursor-pointer" data-airline="ALL" title="Bấm để xem tất cả">
                <td></td>
                <td class="text-center text-decoration-underline"><b>Tổng</b></td>
                <td class="text-center"><b>$TOTAL_BK_QTY</b></td>
                <td class="text-center"><b>$TOTAL_TICKET_QTY</b></td>
                <td class="text-center"><b>$TOTAL_AMOUNT</b></td>
                <td class="text-center"><b>$TOTAL_PURCHASE</b></td>
                <td class="text-center"><b>$TOTAL_PROFIT</b></td>
            </tr>
        ';

        $sql = '
            SELECT 
                airline_code
                , SUM(IF(ticket_qty > 0, ticket_qty, 0)) AS ticket_qty
                -- , GROUP_CONCAT(IF(ticket_qty > 0, bk_name, NULL) SEPARATOR ", ") AS bk_name_err
                , GROUP_CONCAT(IF(ticket_qty > 0, booking_id, NULL) SEPARATOR ", ") AS bk_id_arr
            FROM ( 
                SELECT 
                    i.airline_code
                    , bk.name AS bk_name
                    , bk.id as booking_id
                    , IF(
                        bk.ticket_type = 2
                        , 1
                        , (
                            SELECT SUM(quantity)
                            FROM ec_booking_details
                            WHERE deleted = 0
                            AND booking_id = i.booking_id
                            AND direction = i.direction
                            GROUP BY direction
                        ) 
                    ) AS ticket_qty
                FROM ec_booking_itineraries i
                INNER JOIN ec_flight_bookings bk ON bk.id = i.booking_id AND bk.deleted = 0 AND bk.booking_status IN (3, 7, 8)
                AND bk.date_ticket_issue BETWEEN "' . $from_date . '" AND "' . $to_date . '"
                WHERE i.deleted = 0 
                GROUP BY i.airline_code, i.direction, bk.id
            ) AS t
            GROUP BY CASE 
                WHEN airline_code = "VJ" THEN "VJA"
                WHEN airline_code = "VN" THEN "VNA"
                ELSE airline_code END        
            ORDER BY ticket_qty DESC
        ';

        // if($GLOBALS['current_user']->user_name == 'hungnh') {
        //     pr($sql);
        // }

        $res = $db->query($sql);
        $i = 0;
        $total_bk_qty = 0;
        $total_ticket_qty = 0;
        $total_amout = 0;
        $total_purchase = 0;
        $total_profit = 0;
        $priceCache = [];
        $countedBkIds = [];

        while ($row = $db->fetchByAssoc($res)) {
            $airline = myGetAirlineInfo2($row['airline_code'], 'CODE');
            $airline_amout = 0;
            $airline_purchase = 0;
            $airline_profit = 0;

            $bkIds = [];
            if (!empty($row['bk_id_arr'])) {
                $bkIds = array_values(array_unique(
                    array_filter(
                        array_map('trim', preg_split('/\s*,\s*/', $row['bk_id_arr'], -1, PREG_SPLIT_NO_EMPTY)),
                        'strlen'
                    )
                ));
            }

            $sl_bk = count($bkIds);

            if ($sl_bk > 0) {
                foreach ($bkIds as $bk_id) {
                    if (!isset($priceCache[$bk_id])) {
                        $priceCache[$bk_id] = calculateBKAmt($bk_id);
                    }
                    $info_price = $priceCache[$bk_id];
                    $airline_amout += $info_price['total_amount'];
                    $airline_purchase += $info_price['total_purchase'];
                    $airline_profit += $info_price['total_profit'];

                    if (!isset($countedBkIds[$bk_id])) {
                        $countedBkIds[$bk_id] = true;
                        $total_amout += $info_price['total_amount'];
                        $total_purchase += $info_price['total_purchase'];
                        $total_profit += $info_price['total_profit'];
                    }
                }
            }

            $airline_name = isset($airline['data'][0]['name']) ? $airline['data'][0]['name'] : '';
            $airline_code = $row['airline_code'];
            if (empty($airline_name)) {
                if ($airline_code === '0V') {
                    $airline_name = 'VASCO';
                } elseif (empty($airline_code)) {
                    $airline_name = 'Khác';
                    $airline_code = 'N/A';
                } else {
                    $airline_name = 'Hãng khác';
                }
            }

            $html .= '
                         <tr class="airline-row cursor-pointer" data-airline="' . $airline_code . '" title="Bấm để lọc vé của hãng này">
                              <td class="text-center">' . ($i + 1) . '</td>
                              <td class="text-center fw-semibold text-decoration-underline">' . $airline_name . ' (' . $airline_code . ')</td>
                              <td class="text-center">' . format_number($sl_bk) . '</td>
                              <td class="text-center">' . format_number($row['ticket_qty']) . '</td>
                              <td class="text-center">' . format_number($airline_amout) . '</td>
                              <td class="text-center">' . format_number($airline_purchase) . '</td>
                              <td class="text-center">' . format_number($airline_profit) . '</td>
                         </tr>
                    ';

            $i++;
            $total_bk_qty += $sl_bk;
            $total_ticket_qty += $row['ticket_qty'];
        }

        $html = str_replace(
            array(
                '$TOTAL_BK_QTY',
                '$TOTAL_TICKET_QTY',
                '$TOTAL_AMOUNT',
                '$TOTAL_PURCHASE',
                '$TOTAL_PROFIT',
            ),
            array(
                format_number($total_bk_qty),
                format_number($total_ticket_qty),
                format_number($total_amout),
                format_number($total_purchase),
                format_number($total_profit),
            ),
            $html
        );

        return $html;
    }

    function populateBKReport($from_date, $to_date)
    {
        global $db, $app_list_strings;
        $from_date = date('Y-m-d 00:00:00', strtotime($from_date));
        $to_date = date('Y-m-d 23:59:59', strtotime($to_date));
        $html = '
            <tr id="booking_list_total_row">
                <td></td>
                <td class="center"><b>Tổng</b></td>
                <td></td>
                <td></td>
                <td class="center"><b id="total_filtered_ticket_qty">$TOTAL_TICKET_QTY</b></td>
                <td></td>
                <td></td>
            </tr>
        ';
        //  <td class="center"><b id="total_filtered_amount">$TOTAL_AMOUNT</b></td>

        $sql = '
            SELECT 
                airline_code, bk_name, bk_id, GROUP_CONCAT(direction) AS direction    
                , SUM(IF(ticket_qty > 0, ticket_qty, 0)) AS ticket_qty
                , date_entered, date_ticket_issue
                -- , GROUP_CONCAT(IF(ticket_qty > 0, bk_name, NULL) SEPARATOR ", ") AS bk_name_err
            FROM ( 
                SELECT 
                    i.airline_code, i.direction
                    , bk.name AS bk_name, bk.id AS bk_id, bk.date_entered, bk.date_ticket_issue
                    , IF(
                        bk.ticket_type = 2
                        , 1
                        , (
                            SELECT SUM(quantity)
                            FROM ec_booking_details
                            WHERE deleted = 0
                            AND booking_id = i.booking_id
                            AND direction = i.direction
                            GROUP BY direction
                        ) 
                    ) AS ticket_qty
                FROM ec_booking_itineraries i
                INNER JOIN ec_flight_bookings bk ON bk.id = i.booking_id AND bk.deleted = 0 AND bk.booking_status IN (3, 7, 8)
                AND bk.date_ticket_issue >= "' . $from_date . '"
                AND bk.date_ticket_issue <= "' . $to_date . '"
                WHERE i.deleted = 0 
                GROUP BY i.airline_code, i.direction, bk.id
            ) AS t
            GROUP BY bk_id, airline_code    
            ORDER BY date_entered, direction
        ';

        $res = $db->query($sql);
        $i = $total_ticket_qty = $total_amount = 0;
        while ($row = $db->fetchByAssoc($res)) {
            // $airline = myGetAirlineInfo2($row['airline_code'], 'CODE');
            if ($row['direction'] == '0') {
                $direction = 'Lượt đi';
            } else if ($row['direction'] == '1') {
                $direction = 'Lượt về';
            } else $direction = 'Lượt đi & về';

            $info_price = calculateBKAmt($row['bk_id']);
            $doanh_so = $info_price['total_profit'];
            global $timedate;
            $date_ticket_issue = !empty($row['date_ticket_issue']) ? $timedate->to_display_date($row['date_ticket_issue']) : '';
            $date_entered = !empty($row['date_entered']) ? $timedate->to_display_date_time($row['date_entered']) : '';

            $filter_airline_code = $row['airline_code'];
            if ($filter_airline_code === 'VJ') {
                $filter_airline_code = 'VJA';
            } elseif ($filter_airline_code === 'VN') {
                $filter_airline_code = 'VNA';
            }
            //<td class="center">' . format_number($doanh_so) . '</td> tạm ẩn
            $html .= '
                <tr class="booking-row" data-airline="' . $filter_airline_code . '" data-qty="' . $row['ticket_qty'] . '" data-amount="' . $doanh_so . '">
                    <td class="center stt-cell">' . ($i + 1) . '</td>
                    <td class="center"><a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['bk_id'] . '" target="_blank">' . $row['bk_name'] . '</a></td>
                    <td class="center">(' . $row['airline_code'] . ')</td>
                    <td class="center">' . $direction . '</td>
                    <td class="center">' . format_number($row['ticket_qty']) . '</td>
                    <td class="center">' . $date_ticket_issue . '</td>
                    <td class="center">' . $date_entered . '</td>
                </tr>
            ';
            $i++;
            $total_ticket_qty += $row['ticket_qty'];
            $total_amount += $doanh_so;
        }

        $html = str_replace(
            array(
                '$TOTAL_TICKET_QTY',
                // '$TOTAL_AMOUNT'
            ),
            array(
                format_number($total_ticket_qty),
                // format_number($total_amount)
            ),
            $html
        );

        return $html;
    }
}
