<?php
require_once("include/Sugar_Smarty.php");
class Viewbkagent extends SugarView
{

     function display()
     {
          if (isAllowedUser()) {
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
          global $db, $app_list_strings;
          $from_date  = date('Y-m-d 00:00:00', strtotime($from_date));
          $to_date    = date('Y-m-d 23:59:59', strtotime($to_date));

          $html = '
            <tr>
                <td></td>
                <td class="center"><b>Tổng</b></td>
                <td class="center"><b>$TOTAL_TICKET_QTY</b></td>
                <td></td>
            </tr>
        ';

          $sql = '
            SELECT 
                airline_code
                , SUM(IF(ticket_qty > 0, ticket_qty, 0)) AS ticket_qty
                , GROUP_CONCAT(IF(ticket_qty = 0, bk_name, NULL) SEPARATOR ", ") AS bk_name_err
            FROM ( 
                SELECT 
                    i.airline_code, bk.name AS bk_name
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
                INNER JOIN ec_flight_bookings bk ON bk.id = i.booking_id
                AND bk.deleted = 0 AND bk.booking_status IN (7, 8)
                AND bk.date_ticket_issue >= "' . $from_date . '"
                AND bk.date_ticket_issue <= "' . $to_date . '"
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
          $i = $total_ticket_qty = 0;
          while ($row = $db->fetchByAssoc($res)) {
               $airline = myGetAirlineInfo2($row['airline_code'], 'CODE');
               $html .= '
                <tr>
                    <td class="center">' . ($i + 1) . '</td>
                    <td class="center">' . $airline['data'][0]['name'] . '&nbsp;(' . $row['airline_code'] . ')</td>
                    <td class="center">' . format_number($row['ticket_qty']) . '</td>
                    <td>' . $row['bk_name_err'] . '</td>
                </tr>
            ';
               $i++;
               $total_ticket_qty += $row['ticket_qty'];
          }

          $html = str_replace(
               array(
                    '$TOTAL_TICKET_QTY'
               ),
               array(
                    format_number($total_ticket_qty)
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
            <tr>
                <td></td>
                <td class="center"><b>Tổng</b></td>
                <td></td>
                <td></td>
                <td class="center"><b>$TOTAL_TICKET_QTY</b></td>
                <td></td>
            </tr>
        ';

          $sql = '
            SELECT 
                airline_code, bk_name, bk_id, GROUP_CONCAT(direction) AS direction    
                , SUM(IF(ticket_qty > 0, ticket_qty, 0)) AS ticket_qty
                , GROUP_CONCAT(IF(ticket_qty = 0, bk_name, NULL) SEPARATOR ", ") AS bk_name_err
            FROM ( 
                SELECT 
                    i.airline_code, i.direction
                    , bk.name AS bk_name, bk.id AS bk_id, bk.date_entered
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
                INNER JOIN ec_flight_bookings bk ON bk.id = i.booking_id
                AND bk.deleted = 0 AND bk.booking_status IN (7, 8)
                AND bk.date_ticket_issue >= "' . $from_date . '"
                AND bk.date_ticket_issue <= "' . $to_date . '"
                WHERE i.deleted = 0 
                GROUP BY i.airline_code, i.direction, bk.id
            ) AS t
            GROUP BY bk_id, airline_code    
            ORDER BY date_entered, direction
        ';
          $res = $db->query($sql);
          $i = $total_ticket_qty = 0;
          while ($row = $db->fetchByAssoc($res)) {
               // $airline = myGetAirlineInfo2($row['airline_code'], 'CODE');
               if ($row['direction'] == '0') {
                    $direction = 'Lượt đi';
               } else if ($row['direction'] == '1') {
                    $direction = 'Lượt về';
               } else $direction = 'Lượt đi & về';
               $html .= '
                <tr>
                    <td class="center">' . ($i + 1) . '</td>
                     <td class="center"><a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['bk_id'] . '" target="_blank">' . $row['bk_name'] . '</a></td>
                    <td class="center">(' . $row['airline_code'] . ')</td>
                    <td class="center">' . $direction . '</td>
                    <td class="center">' . format_number($row['ticket_qty']) . '</td>
                    <td>' . $row['bk_name_err'] . '</td>
                </tr>
            ';
               $i++;
               $total_ticket_qty += $row['ticket_qty'];
          }

          $html = str_replace(
               array(
                    '$TOTAL_TICKET_QTY'
               ),
               array(
                    format_number($total_ticket_qty)
               ),
               $html
          );

          return $html;
     }
}
