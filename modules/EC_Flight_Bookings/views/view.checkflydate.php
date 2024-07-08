<?php
require_once("include/Sugar_Smarty.php");
// require_once("phpexcel/Classes/PHPExcel/IOFactory.php");

class Viewcheckflydate extends SugarView {
    function display() {
        if (ACLController::checkAccess('EC_Flight_Bookings', 'list', true)) {
            $smartyCont = new Sugar_Smarty();
            $this->populateContent($smartyCont);
            $smartyCont->display('modules/EC_Flight_Bookings/tpls/view_checkflydate.tpl');
        } else {
            header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
            exit();
        }
    }

    function populateContent($smartyobj) {
        global $db, $app_list_strings, $current_user;
        $sql_search = "";

        if (isset($_POST['tungay']) && !empty($_POST['tungay'])) {
            $sql_search .= " AND DATE(i.departure_date) >= '" . date('Y-m-d', strtotime($_POST['tungay'])) . "' ";
            $post_tungay = $_POST['tungay'];
        } else {
            $sql_search .= " AND DATE(i.departure_date) >= '" . date('Y-m-d') . "' ";
            $post_tungay = date('d-m-Y');
        }

        if (isset($_POST['denngay']) && !empty($_POST['denngay'])) {
            $sql_search .= " AND DATE(i.departure_date) <= '" . date('Y-m-d', strtotime($_POST['denngay'])) . "' ";
            $post_denngay = $_POST['denngay'];
        } else {
            $sql_search .= " AND DATE(i.departure_date) <= '" . date('Y-m-d') . "' ";
            $post_denngay = date('d-m-Y');
        }

        if (isset($_POST['airlines']) && !empty($_POST['airlines'])) {
            if ($_POST['airlines'] == 'VJ' || $_POST['airlines'] == 'VJA') {
                $sql_search .= " AND i.airline_code IN ('VJ', 'VJA')";
            } else if ($_POST['airlines'] == 'VN' || $_POST['airlines'] == 'VNA') {
                $sql_search .= " AND i.airline_code IN ('VN', 'VNA')";
            } else {
                $sql_search .= " AND i.airline_code='" . $_POST['airlines'] . "' ";
            }
        }

        $user_id = '';
        if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
            $user_id = preg_replace('/[^0-9a-zA-Z\-]/', '', $_POST['user_id']);
            $sql_search .= " AND b.assigned_user_id='" . $user_id . "' ";
        }

        if (!is_admin($current_user)) {
            // $sql_search.=" AND ".SecurityGroup::getGroupWhere("b","EC_Flight_Bookings",$current_user->id);
        }

        $post_denngay_s = strtotime($post_denngay);
        $post_tungay_s  = strtotime($post_tungay);
        $khoangcach     = ($post_denngay_s - $post_tungay_s) / 86400;

        if ($khoangcach < 0) {
            echo '<p class="error">Đến ngày phải lớn hơn hoặc bằng Từ ngày</p>';
            exit;
        }
        if ($khoangcach > 30) {
            echo '<p class="error">Khoảng thời gian tối đa được phép xem là 30 ngày</p>';
            exit;
        }

        // Bổ sung thêm các hãng nước ngoài
        $aircode_inter          = simplexml_load_file('custom/airlines.xml');
        $aircode_inter_json     = json_encode($aircode_inter);
        $aircode_inter_par      = json_decode($aircode_inter_json, true);

        foreach ($aircode_inter_par['RECORD'] as $item) {
            $aircode_inter_arr[$item['code']] = $item['name'] . ' (' . $item['code'] . ')';
            $aircode_inter_arr2[$item['code']] = $item['code'];
        }

        $aircode = array_merge(array('VNA' => 'VN', 'VJA' => 'VJ', 'JET' => 'BL', 'BBA' => 'QH', 'VNP' => 'VNP', 'VTA' => 'VTA'), $aircode_inter_arr2);
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
					i.is_remind,
					(
					    SELECT SUM(IFNULL(d.quantity, 0)) 
					    FROM ec_booking_details d 
					    WHERE d.booking_id = i.booking_id AND d.direction = i.direction AND d.deleted = 0
					) AS total_qty,
                    (
                        SELECT DATE_ADD(p.date_entered, INTERVAL 7 HOUR)
                        FROM ec_working_process p
                        WHERE p.parent_id = b.id AND p.completed = 1 AND p.deleted = 0
                        LIMIT 1
                    ) AS complete_time
				FROM ec_booking_itineraries i
				    LEFT JOIN ec_flight_bookings b ON i.booking_id = b.id AND b.deleted = 0
				WHERE b.booking_status IN ('7','8')" . $sql_search . "
                    AND i.deleted=0 
				GROUP BY i.booking_id 
				ORDER BY b.date_ticket_issue, complete_time ";

        // if($current_user->user_name == 'hungnh'){
        //     pr($sql);
        // }

        $res    = $db->query($sql);
        $i      = 0;
        $html   = '';

        while ($row = $db->fetchByAssoc($res)) {
            $complete_time = (empty($row['complete_time']) ? '' : date('d/m/Y H:i:s', strtotime($row['complete_time'])));
            if($row['is_remind'] == 1){
                $is_remind     = 'background: #cfeafe';
                $class_remind   = 'remind';
            } else {
                $is_remind     = '';
                $class_remind   = '';
            }

            $html .= '<tr class="'.$class_remind.'" style="'.$is_remind.'">
				<td class="fw-semibold hide-mobile" align="center">' . ($i + 1) . '</td>
				<td class="fw-semibold" align="center"><a target="_blank" href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['booking_id'] . '">' . $row['booking'] . '</a></td>
				<td align="left">' . $row['contact_name'] . '</td>
				<td align="center">' . $row['phone'] . '</td>
				<td align="center" class="hide-mobile">
                    <img style="width:40px;" src="custom/themes/default/images/airline-icon-100x100/' . $aircode[$row['airline_code']] . '.png" border="0" />
                </td>
				<td align="center" class="hide-mobile">' . $row['flight_number'] . '</td>
				<td align="center" class="hide-mobile">' . $row['departure'] . '-' . $row['arrival'] . '</td>
				<td align="center">' . date('d/m/Y', strtotime($row['departure_date'])) . '<br>' . date('H:i', strtotime($row['departure_date'])) . ' - ' . date('H:i', strtotime($row['arrival_date'])) . '</td>
				<td align="center" class="hide-mobile">' . (strpos($row['ticket_class'], '-') ? substr($row['ticket_class'], strpos($row['ticket_class'], '-') + 1) : $row['ticket_class']) . '</td>
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


        $smartyobj->assign('AIRLINES', get_select_options_with_id(($app_list_strings['aircode_list'] + $aircode_inter_arr), (isset($_POST['airlines']) ? $_POST['airlines'] : '')));
    }

    function getHourList($val) {
        $html = '';
        for ($i = 0; $i < 24; $i++) {
            $selected = ($i == $val) ? 'selected="selected"' : '';
            $html .= '<option ' . $selected . ' value="' . str_pad($i, 2, '0', STR_PAD_LEFT) . '">' . str_pad($i, 2, '0', STR_PAD_LEFT) . '</option>';
        }
        return $html;
    }

    function getMinuteList($val) {
        $html = '';
        for ($i = 0; $i < 60; $i++) {
            $selected = ($i == $val) ? 'selected="selected"' : '';
            $html .= '<option ' . $selected . ' value="' . str_pad($i, 2, '0', STR_PAD_LEFT) . '">' . str_pad($i, 2, '0', STR_PAD_LEFT) . '</option>';
        }
        return $html;
    }
}
