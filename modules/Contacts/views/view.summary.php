<?php
require_once("include/Sugar_Smarty.php");

class Viewsummary extends SugarView
{
     function display()
     {
          global $current_user;

          if (ACLController::checkAccess('Contacts', 'list', true)) {
               $smartyCont = new Sugar_Smarty();
               $con_ret = $this->populateContent();

               $this->assignFields($con_ret, $smartyCont);
               $smartyCont->display('modules/Contacts/tpls/summary.tpl');
          }
     }

     function populateContent()
     {
          $currMonth     = date('n');
          $currYear      = date('Y');
          $reportTime    = 'năm ' . date('Y');

          if (!empty($_REQUEST['from_date']) && strtotime($_REQUEST['from_date']) !== false) {
               $from_date = date('d-m-Y', strtotime($_REQUEST['from_date']));
          } else {
               $from_date = date('d-m') . '-' . ($currYear - 1);
               $_REQUEST['from_date'] = $from_date;
          }

          if (!empty($_REQUEST['to_date']) && strtotime($_REQUEST['to_date']) !== false) {
               $to_date = date('d-m-Y', strtotime($_REQUEST['to_date']));
          } else {
               $to_date = date('d-m-Y', strtotime($currYear));
               $_REQUEST['to_date'] =  $to_date;
          }

          $year_select = isset($_REQUEST['year_select']) ? $_REQUEST['year_select'] : '';

          return [
               'curr_month'   => $currMonth,
               'curr_year'    => $currYear,
               'report_time'  => $reportTime,
               'from_date'    => $from_date,
               'to_date'      => $to_date,
               'year_select'   => $year_select,
          ];
     }

     function assignFields($con, $smarty)
     {
          global $current_user;
          $time_opt = $this->populateReportYearSelect($con);
          $smarty->assign('YEAR_SELECT', $time_opt['html']);
          $smarty->assign('OPTION_SELECTED', $con['year_select']);
          $smarty->assign('FROM_DATE', $con['from_date']);
          $smarty->assign('TO_DATE', $con['to_date']);
          $smarty->assign('MODULE_NAME', $this->bean->module_dir);
          $smarty->assign('REPORT_TIME', $time_opt['report_time']);

          // LOẠI LIÊN HỆ
          $data_type_contacts = $this->getContactsTypeTotals($con) ?? [];

          // CHART DONUT
          $filtered_data = array_filter($data_type_contacts, function ($key) {
               // return $key !== 'OTHER'; // Loại bỏ loại 'khác'
               return $key !== 'DEFAULT_GROUP'; // Loại bỏ loại 'khác'
           }, ARRAY_FILTER_USE_KEY);

          $chart_labels_donut = implode(",", array_map(function($key) {
               return $GLOBALS['app_list_strings']['contact_type_list'][$key] ?? $key;
          }, array_keys($filtered_data)));
          $chart_data_donut = implode("|", array_map(fn($item) => $item['cnt'], $filtered_data));

          $smarty->assign('CHART_LABELS_DONUT', $chart_labels_donut); //
          $smarty->assign('CHART_DATA_DONUT', $chart_data_donut);
          $smarty->assign('CHART_COLORS_DONUT', '#696cff,#0dcaf0,#33A8FF,#ffc107,#212529,#ff3e1d');

          //  CHART BAR COMBO
          $data_currency_contacts = $this->getContactsTop($con) ?? [];
          $chartLabels = [];
          $chartProfit0 = [];
          $chartProfit1 = [];
          $chartProfit2 = [];
          foreach ($data_currency_contacts as $row) {
               $chartLabels[] = $row['last_name'] . ' (' . $row['type_customer']['label']. ')'; 
               $chartProfit0[] = $row['total_profit'];  
               $chartProfit1[] = $row['profit_period_1'];  
               $chartProfit2[] = $row['profit_period_2'];  
          }
          $smarty->assign('CHART_LABELS_BARCOMBO', implode(',', $chartLabels)); // Tách bằng dấu phẩy
          $smarty->assign('CHART_DATA_PROFIT_0', implode('|', $chartProfit0));  // Tách bằng dấu "|"
          $smarty->assign('CHART_DATA_PROFIT_1', implode('|', $chartProfit1));  // Tách bằng dấu "|"
          $smarty->assign('CHART_DATA_PROFIT_2', implode('|', $chartProfit2));  // Tách bằng dấu "|"

          $smarty->assign('DATA_TYPE_CONTACTS', $this->arrayContactsTypeData($data_type_contacts));
     }

     function populateReportYearSelect($con){
          $currYear = $con['curr_year'] ?? date('Y');
          $reportTime = $con['year_select'] ?? 'period';
  
          $labels = ['Chu kỳ', 'Năm ' . $currYear, 'Năm ' . ($currYear - 1), 'Năm ' . ($currYear - 2), 'Năm ' . ($currYear - 3)];
          $values = ['period', 'this_year', 'previous_year', 'past_year', 'old_year'];
          $dateOptions = [];
  
          foreach ($values as $key => $value) {
              $year = $currYear - $key + 1; // Lấy năm tương ứng.
              $fromdate = $value === 'period' ? date('d-m') . '-' . ($currYear - 1) : "01-01-$year";
              $todate = $value === 'period' ? date('d-m-Y') : "31-12-$year";
              $dateOptions[] = [
                  'value' => $value,
                  'fromdate' => $fromdate,
                  'todate' => $todate,
                  'label' => $labels[$key],
                  'selected' => $reportTime === $value
              ];
          }
  
          $html = implode('', array_map(function ($option) {
              return '<option value="' . $option['value'] . '" fromdate="' . $option['fromdate'] . '" todate="' . $option['todate'] . '"'
                  . ($option['selected'] ? ' selected' : '') . '>' . $option['label'] . '</option>';
          }, $dateOptions));
      
          return [
              'html' => $html,
              'report_time' => $reportTime
          ];
      }

     function arrayContactsTypeData($data)
     {
          $result = [
               array(
                    'label' => 'KH mới',
                    'data_cnt' => format_number($data['NEW_CUSTOMER']['cnt']),
                    'data_profit' => format_number($data['NEW_CUSTOMER']['profit']),
                    'type' => 'new_customer',
                    'link' => '',
                    'class' => 'primary',
                    'desc' => 'Trong khoảng thời gian xem báo cáo, khách hàng có từ <code class="fs-6">1-3 booking hoàn tất</code>. Trước đó <strong>CHƯA</strong> có booking hoàn tất nào.',
               ),
               array(
                    'label' => 'Trở lại',
                    'data_cnt' => format_number($data['RETURN_CUSTOMER']['cnt']),
                    'data_profit' => format_number($data['RETURN_CUSTOMER']['profit']),
                    'type' => 'return_customer',
                    'link' => '',
                    'class' => 'info',
                    'desc' => 'Trong khoảng thời gian xem báo cáo, khách hàng có từ <code class="fs-6">1-3 booking hoàn tất</code>. Trước đó đã có ít nhất <code class="fs-6">1 booking hoàn tất</code>',
               ),
               array(
                    'label' => 'Bạc',
                    'data_cnt' => format_number($data['SILVER_MEMBER']['cnt']),
                    'data_profit' => format_number($data['SILVER_MEMBER']['profit']),
                    'type' => 'silver_member',
                    'link' => '',
                    'class' => 'secondary',
                    'desc' => 'Trong khoảng thời gian xem báo cáo. Khách hàng có từ <code class="fs-6">4-9 booking hoàn tất</code>.',
               ),
               array(
                    'label' => 'Vàng',
                    'data_cnt' => format_number($data['GOLD_MEMBER']['cnt']),
                    'data_profit' => format_number($data['GOLD_MEMBER']['profit']),
                    'type' => 'gold_member',
                    'link' => '',
                    'class' => 'warning',
                    'desc' => 'Trong khoảng thời gian xem báo cáo. Khách hàng có từ <code class="fs-6">10 booking hoàn tất</code> trở lên.',
               ),
               array(
                    'label' => 'VIP Member',
                    'data_cnt' => format_number($data['VIP_MEMBER']['cnt']),
                    'data_profit' => format_number($data['VIP_MEMBER']['profit']),
                    'type' => 'vip_member',
                    'link' => '',
                    'class' => 'dark',
                    'desc' => 'Khách hàng có từ <code class="fs-6">20 booking hoàn tất</code> trở lên và doanh số đạt trên <code class="fs-6">20tr</code>.',
               ),
               array(
                    'label' => 'Loại khác',
                    'data_cnt' => format_number($data['OTHER']['cnt']),
                    'data_profit' => format_number($data['OTHER']['profit']),
                    'type' => 'other',
                    'link' => '',
                    'class' => 'danger',
                    'desc' => 'Không thuộc 1 trong 5 loại trên',
               ),
          ];
          return $result;
     }

     /**
      * Lấy tổng số lượng cuộc gọi theo loại - direction
      * @param mixed $params: Khoảng thời gian xem báo báo
      * @return int: Tổng số lượng cuộc gọi theo từng loại
      */
     function getContactsTypeTotals($params)
     {
          global $db, $current_user;
          $result = [
               'NEW_CUSTOMER'    => ['cnt' => 0, 'profit' => 0],
               'RETURN_CUSTOMER' => ['cnt' => 0, 'profit' => 0],
               'SILVER_MEMBER'   => ['cnt' => 0, 'profit' => 0],
               'GOLD_MEMBER'     => ['cnt' => 0, 'profit' => 0],
               'VIP_MEMBER'      => ['cnt' => 0, 'profit' => 0],
               'OTHER'           => ['cnt' => 0, 'profit' => 0],
          ];

          // $start_date    = date('Y-m-d', strtotime('-1 year +7 hours')); // Ngày 1 năm trước
          // $end_date      = date('Y-m-d', strtotime('+7 hours')); // Ngày hiện tại
          $start_date = date('Y-m-d 00:00:00', strtotime($params['from_date']));
          $end_date = date('Y-m-d 23:59:59', strtotime($params['to_date']));

          $sql = "SELECT 
                    CASE 
                         -- VIP members: tổng cộng từ 20 booking trở lên và doanh số trên 20tr
                         WHEN completed >= 20 AND total_profit > 20000000 THEN 'VIP_MEMBER'
                         -- Gold members: trong chu kỳ năm có từ 10 booking trở lên
                         WHEN current_completed >= 10 THEN 'GOLD_MEMBER'
                         -- Silver members: trong chu kỳ năm có từ 4 - 9 booking
                         WHEN current_completed BETWEEN 4 AND 9 THEN 'SILVER_MEMBER'
                         -- Return customer: trong chu kỳ năm có từ 1-3 booking và quá khứ có ít nhất 01 booking
                         WHEN current_completed BETWEEN 1 AND 3 AND past_completed > 0 THEN 'RETURN_CUSTOMER'
                         -- New customer: trong chu kỳ năm có từ 1-3 booking và quá khứ không có booking
                         WHEN current_completed BETWEEN 1 AND 3 AND past_completed = 0 THEN 'NEW_CUSTOMER'
                         WHEN current_completed > 0 THEN 'OTHER'
                         ELSE 'DEFAULT_GROUP'
                    END AS type,
                    SUM(t.total_profit) as total_profit,
                    COUNT(*) AS total_count
               FROM (
                    SELECT 
                         contact_id,
                         -- SUM(CASE WHEN YEAR(date_entered) = YEAR(CURDATE()) THEN 1 ELSE 0 END) AS current_year,
                         -- SUM(CASE WHEN YEAR(date_entered) < YEAR(CURDATE()) THEN 1 ELSE 0 END) AS past_year,
                         SUM(CASE WHEN date_entered BETWEEN '$start_date' AND '$end_date' THEN 1 ELSE 0 END) AS current_completed,
                         SUM(CASE WHEN date_entered < '$start_date' THEN 1 ELSE 0 END) AS past_completed,
                         COUNT(*) AS completed,
                         -- Tổng doanh số
                         SUM(
                              CASE 
                                   WHEN date_entered BETWEEN '$start_date' AND '$end_date' THEN (
                                        bk.total_amount
                                        - IFNULL((
                                             SELECT SUM(IFNULL(bd.total_bought_price, 0))
                                             FROM ec_booking_details bd
                                             WHERE bd.deleted = 0 AND bd.booking_id = bk.id
                                        ), 0)
                                        - IFNULL((
                                        SELECT SUM(
                                             IF(bk.flight_type = '0', 
                                                  IF(px.luggage_price > 0, IFNULL(px.luggage_purchase, 0), 0)
                                                  + IF(px.luggage_price_inbound > 0, IFNULL(px.luggage_purchase_inbound, 0), 0),
                                                  IF(px.luggage_price > 0, IFNULL(px.luggage_purchase, 0), 0)
                                             )
                                        )
                                        FROM ec_booking_passengers px
                                        WHERE px.deleted = 0 AND px.booking_id = bk.id
                                        ), 0)
                                   )
                                   ELSE 0
                              END
                         ) AS total_profit
                    FROM ec_flight_bookings bk
                    WHERE bk.booking_status = '8' 
                    AND bk.deleted = 0
                    AND bk.contact_id IS NOT NULL 
                    AND bk.contact_id != ''
                    GROUP BY bk.contact_id
               ) t
               GROUP BY type";

          $res_dir = $db->query($sql);
          while ($row = $db->fetchByAssoc($res_dir)) {
               $type = strtoupper($row['type']);
               $result[$type] = [
                    'cnt' => $row['total_count'],
                    'profit' => $row['total_profit']
               ];
          }

          return $result;
     }

     function getContactsTop($params)
     {
          global $db, $current_user;
          // $start_date    = date('Y-m-d', strtotime('-1 year +7 hours')); // Ngày 1 năm trước
          // $end_date      = date('Y-m-d', strtotime('+7 hours')); // Ngày hiện tại
          $start_date = date('Y-m-d 00:00:00', strtotime($params['from_date']));
          $end_date = date('Y-m-d 23:59:59', strtotime($params['to_date']));
          $top_contacts = [];

          $sql = "SELECT 
                    c.id,
                    c.last_name,
                    SUM(
                        CASE 
                            WHEN bk.date_entered BETWEEN '$start_date' AND '$end_date' THEN (
                                bk.total_amount
                                - IFNULL((
                                    SELECT SUM(IFNULL(bd.total_bought_price, 0))
                                    FROM ec_booking_details bd
                                    WHERE bd.deleted = 0 AND bd.booking_id = bk.id
                                ), 0)
                                - IFNULL((
                                SELECT SUM(
                                    IF(bk.flight_type = '0', 
                                        IF(px.luggage_price > 0, IFNULL(px.luggage_purchase, 0), 0)
                                        + IF(px.luggage_price_inbound > 0, IFNULL(px.luggage_purchase_inbound, 0), 0),
                                        IF(px.luggage_price > 0, IFNULL(px.luggage_purchase, 0), 0)
                                    )
                                )
                                FROM ec_booking_passengers px
                                WHERE px.deleted = 0 AND px.booking_id = bk.id
                                ), 0)
                            )
                            ELSE 0
                        END
                    ) AS total_profit,
                    SUM(
                         CASE 
                              WHEN bk.date_entered BETWEEN DATE_SUB('$start_date', INTERVAL 1 YEAR) AND DATE_SUB('$end_date', INTERVAL 1 YEAR) THEN (
                                   bk.total_amount
                                   - IFNULL((SELECT SUM(IFNULL(bd.total_bought_price, 0)) 
                                        FROM ec_booking_details bd 
                                        WHERE bd.deleted = 0 AND bd.booking_id = bk.id), 0)
                                   - IFNULL((SELECT SUM(
                                             IF(bk.flight_type = '0', 
                                                  IF(px.luggage_price > 0, IFNULL(px.luggage_purchase, 0), 0)
                                                  + IF(px.luggage_price_inbound > 0, IFNULL(px.luggage_purchase_inbound, 0), 0),
                                                  IF(px.luggage_price > 0, IFNULL(px.luggage_purchase, 0), 0)
                                             )
                                        )
                                        FROM ec_booking_passengers px
                                        WHERE px.deleted = 0 AND px.booking_id = bk.id), 0)
                                   )
                              ELSE 0
                         END
                    ) AS profit_period_1, -- Doanh số chu kỳ trước
                    -- Doanh số chu kỳ 2 năm trước (2021/22)
                    SUM(
                        CASE 
                            WHEN bk.date_entered BETWEEN DATE_SUB('$start_date', INTERVAL 2 YEAR) AND DATE_SUB('$end_date', INTERVAL 2 YEAR) THEN (
                                   bk.total_amount
                                   - IFNULL((SELECT SUM(IFNULL(bd.total_bought_price, 0)) 
                                        FROM ec_booking_details bd 
                                        WHERE bd.deleted = 0 AND bd.booking_id = bk.id), 0)
                                   - IFNULL((SELECT SUM(
                                        IF(bk.flight_type = '0', 
                                             IF(px.luggage_price > 0, IFNULL(px.luggage_purchase, 0), 0)
                                             + IF(px.luggage_price_inbound > 0, IFNULL(px.luggage_purchase_inbound, 0), 0),
                                             IF(px.luggage_price > 0, IFNULL(px.luggage_purchase, 0), 0)
                                        )
                                   )
                                   FROM ec_booking_passengers px
                                   WHERE px.deleted = 0 AND px.booking_id = bk.id), 0)
                              )
                              ELSE 0
                         END
                    ) AS profit_period_2
                    FROM ec_flight_bookings bk
                    JOIN contacts c ON bk.contact_id = c.id
                    WHERE bk.booking_status = '8' 
                         AND bk.deleted = 0
                         AND (bk.contact_id IS NOT NULL OR bk.contact_id != '')
                    GROUP BY c.id
                    ORDER BY total_profit DESC 
                    LIMIT 5
               ";

          $res_dir = $db->query($sql);
          while ($row = $db->fetchByAssoc($res_dir)) {
               $top_contacts[] = [
                    'contact_id' => $row['id'],
                    'last_name' => $row['last_name'],
                    'total_profit' => $row['total_profit'],
                    'profit_period_1' => $row['profit_period_1'],
                    'profit_period_2' => $row['profit_period_2'],
                    'type_customer' => classifyContactv2($row['id']),
               ];
          }

          return $top_contacts;
     }
}
