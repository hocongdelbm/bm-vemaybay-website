<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class Viewcheckflydate extends SugarView
{
    function display()
    {
        if (ACLController::checkAccess('EC_Flight_Bookings', 'list', true)) {
            $smartyCont = new Sugar_Smarty();
            $this->populateContent($smartyCont);
            $smartyCont->display('modules/EC_Flight_Bookings/tpls/view_checkflydate.tpl');
        } else {
            header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
            exit();
        }
    }

    function populateContent($smartyobj)
    {
        global $db, $app_list_strings;
        $sql_search = "";

        $post_tungay  = !empty($_POST['tungay'])  ? $_POST['tungay']  : date('d-m-Y');
        $post_denngay = !empty($_POST['denngay']) ? $_POST['denngay'] : date('d-m-Y');

        $tungay_db  = date('Y-m-d', strtotime($post_tungay));
        $denngay_db = date('Y-m-d', strtotime($post_denngay));

        $khoangcach = (strtotime($post_denngay) - strtotime($post_tungay)) / 86400;
        if ($khoangcach < 0) {
            echo '<p class="error">Đến ngày phải lớn hơn hoặc bằng Từ ngày</p>';
            exit;
        }
        if ($khoangcach > 30) {
            echo '<p class="error">Khoảng thời gian tối đa được phép xem là 30 ngày</p>';
            exit;
        }

        $sql_search .= " AND i.departure_date >= '{$tungay_db} 00:00:00' AND i.departure_date <= '{$denngay_db} 23:59:59'";

        if (!empty($_POST['airlines'])) {
            $airline = $_POST['airlines'];
            $airline_mapping = [
                'VJ' => ['VJ', 'VJA'],
                'VJA' => ['VJ', 'VJA'],
                'VN' => ['VN', 'VNA'],
                'VNA' => ['VN', 'VNA']
            ];

            if (isset($airline_mapping[$airline])) {
                $codes = $airline_mapping[$airline];
                $quoted_codes = array_map([$db, 'quote'], $codes);
                $sql_search .= " AND i.airline_code IN (" . implode(',', $quoted_codes) . ")";
            } else {
                $sql_search .= " AND i.airline_code=" . $db->quote($airline);
            }
        }

        $user_id = '';
        if (!empty($_POST['user_id'])) {
            $user_id = preg_replace('/[^0-9a-zA-Z\-]/', '', $_POST['user_id']);
            $sql_search .= " AND b.assigned_user_id=" . $db->quote($user_id);
        }

        $aircode_inter_arr  = $this->getAirlineData();
        $aircode_inter_arr2 = [];
        foreach ($aircode_inter_arr as $code => $_) {
            $aircode_inter_arr2[$code] = $code;
        }

        $aircode = array_merge(
            ['VNA' => 'VN', 'VN' => 'VNA', 'VJA' => 'VJ', 'JET' => 'BL', 'BBA' => 'QH', 'VNP' => 'VNP', 'VTA' => 'VTA'],
            $aircode_inter_arr2
        );

        $sql = "SELECT b.id AS booking_id,
                    b.name AS booking,
                    b.contact_name,
                    b.phone,
                    i.departure,
                    i.arrival,
                    i.departure_date,
                    i.arrival_date,
                    i.airline_code,
                    i.flight_number,
                    i.base_price,
                    i.ticket_class,
                    b.date_ticket_issue,
                    i.checkin_status,
                    i.is_remind,
                    COALESCE(d_sum.total_qty, 0) AS total_qty,
                    p_max.complete_time
                FROM ec_booking_itineraries i
                LEFT JOIN ec_flight_bookings b ON i.booking_id = b.id AND b.deleted = 0
                LEFT JOIN (
                    SELECT DISTINCT booking_id
                    FROM ec_booking_itineraries
                    WHERE add_type = 3 AND deleted = 0
                ) i_chg ON i_chg.booking_id = i.booking_id
                LEFT JOIN (
                    SELECT booking_id, direction, SUM(IFNULL(quantity, 0)) AS total_qty
                    FROM ec_booking_details
                    WHERE deleted = 0
                    GROUP BY booking_id, direction
                ) d_sum ON d_sum.booking_id = i.booking_id AND d_sum.direction = i.direction
                LEFT JOIN (
                    SELECT parent_id, MAX(DATE_ADD(date_entered, INTERVAL 7 HOUR)) AS complete_time
                    FROM ec_working_process
                    WHERE completed = 1 AND deleted = 0
                    GROUP BY parent_id
                ) p_max ON p_max.parent_id = b.id
                WHERE b.booking_status IN ('7','8')" . $sql_search . "
                    AND i.deleted = 0
                    AND (i.add_type != 0 OR i_chg.booking_id IS NULL)
                GROUP BY i.booking_id, b.id, b.name, b.contact_name, b.phone, i.departure, i.arrival,
                         i.departure_date, i.arrival_date, i.airline_code, i.flight_number, i.base_price,
                         i.ticket_class, b.date_ticket_issue, i.checkin_status, i.is_remind, d_sum.total_qty, p_max.complete_time
                ORDER BY b.date_ticket_issue, p_max.complete_time";

        $res  = $db->query($sql);
        $i    = 0;
        $html = '';

        while ($row = $db->fetchByAssoc($res)) {
            $complete_time = empty($row['complete_time']) ? '' : date('d/m/Y H:i:s', strtotime($row['complete_time']));

            if ($row['is_remind'] == 1) {
                $row_style = 'background: #cfeafe';
                $row_class = 'remind';
            } else {
                $row_style = '';
                $row_class = '';
            }

            $checkin_class = '';
            if ($row['checkin_status'] == 1) {
                $checkin_class = 'text-danger';
            } elseif ($row['checkin_status'] == 2) {
                $checkin_class = 'text-success';
            }

            $ticket_class = strpos($row['ticket_class'], '-') !== false
                ? substr($row['ticket_class'], strpos($row['ticket_class'], '-') + 1)
                : $row['ticket_class'];

            $airline_icon = $aircode[$row['airline_code']] ?? $row['airline_code'];

            $html .= '<tr class="' . $row_class . '" style="' . $row_style . '">
                        <td class="fw-semibold hide-mobile" align="center">' . ($i + 1) . '</td>
                        <td class="fw-semibold" align="center"><a target="_blank" href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['booking_id'] . '">' . $row['booking'] . '</a></td>
                        <td align="left">' . $row['contact_name'] . '</td>
                        <td align="center" class="fw-semibold ' . $checkin_class . '">' . $app_list_strings['booking_checkin_status_list'][$row['checkin_status']] . '</td>
                        <td align="center">' . $row['phone'] . '</td>
                        <td align="center" class="hide-mobile">
                            <img style="width:40px;" src="custom/themes/default/images/airline-icon-100x100/' . $airline_icon . '.png" border="0" />
                        </td>
                        <td align="center" class="hide-mobile">' . $row['flight_number'] . '</td>
                        <td align="center" class="hide-mobile">' . $row['departure'] . '-' . $row['arrival'] . '</td>
                        <td align="center">' . date('d/m/Y', strtotime($row['departure_date'])) . '<br>' . date('H:i', strtotime($row['departure_date'])) . ' - ' . date('H:i', strtotime($row['arrival_date'])) . '</td>
                        <td align="center" class="hide-mobile">' . $ticket_class . '</td>
                        <td align="right" class="hide-mobile">' . format_number($row['base_price']) . '</td>
                        <td align="center" class="hide-mobile">' . format_number($row['total_qty']) . '</td>
                        <td align="center" class="hide-mobile">' . date('d/m/Y', strtotime($row['date_ticket_issue'])) . '</td>
                        <td align="center" class="hide-mobile">' . $complete_time . '</td>
                    </tr>';
            $i++;
        }

        $smartyobj->assign('DATA', $html);
        $smartyobj->assign('POST_TUNGAY', $post_tungay);
        $smartyobj->assign('POST_DENNGAY', $post_denngay);
        $smartyobj->assign('USER_LIST', myGetSelectOptionsWithDb('Users', $user_id, 'id', " AND title IN ('Booker','KeToan','Leader') AND status='Active' ORDER BY first_name ASC "));
        $smartyobj->assign('AIRLINES', get_select_options_with_id(($app_list_strings['aircode_list'] + $aircode_inter_arr), $_POST['airlines'] ?? ''));
    }

    private function getAirlineData()
    {
        static $aircode_cache = null;

        if ($aircode_cache === null) {
            $aircode_cache = [];
            if (file_exists('custom/airlines.xml')) {
                $aircode_inter_xml = simplexml_load_file('custom/airlines.xml');
                $records = json_decode(json_encode($aircode_inter_xml), true)['RECORD'] ?? [];
                foreach ($records as $item) {
                    $aircode_cache[$item['code']] = $item['name'] . ' (' . $item['code'] . ')';
                }
            }
        }

        return $aircode_cache;
    }

    function getHourList($val)
    {
        $html = '';
        for ($i = 0; $i < 24; $i++) {
            $pad = str_pad($i, 2, '0', STR_PAD_LEFT);
            $html .= '<option ' . ($i == $val ? 'selected' : '') . ' value="' . $pad . '">' . $pad . '</option>';
        }
        return $html;
    }

    function getMinuteList($val)
    {
        $html = '';
        for ($i = 0; $i < 60; $i++) {
            $pad = str_pad($i, 2, '0', STR_PAD_LEFT);
            $html .= '<option ' . ($i == $val ? 'selected' : '') . ' value="' . $pad . '">' . $pad . '</option>';
        }
        return $html;
    }
}
