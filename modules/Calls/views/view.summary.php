<?php
require_once("include/Sugar_Smarty.php");

class Viewsummary extends SugarView
{
     function display()
     {
          if (ACLController::checkAccess('Calls', 'list', true)) {
               $smartyCont = new Sugar_Smarty();
               $this->displayJS();
               $con_ret = $this->populateContent();

               $this->assignFields($con_ret, $smartyCont);
               $smartyCont->display('modules/Calls/tpls/summary.tpl');
          } else {
               $this->redirectUnauthorized();
          }
     }

     private function redirectUnauthorized()
     {
          header("Location: index.php?module=Calls&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
          exit();
     }

     function displayJS()
     {
          $js = '';
          $js .= '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.3/dist/chart.umd.min.js"></script>';
          $js .= '<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.1.0"></script>';
          echo $js;
     }

     function populateContent()
     {
          global $current_user;

          $currMonth     = date('n');
          $currDay       = date('d-m-Y');
          $currYear      = date('Y');
  
          if (!empty($_REQUEST['from_date']) && strtotime($_REQUEST['from_date']) !== false) {
              $from_date = date('d-m-Y', strtotime($_REQUEST['from_date']));
          } else {
               $from_date = date('01-m-Y', strtotime($currDay));
               $_REQUEST['from_date'] = $from_date;
          }
  
          if (!empty($_REQUEST['to_date']) && strtotime($_REQUEST['to_date']) !== false) {
              $to_date = date('d-m-Y', strtotime($_REQUEST['to_date']));
          } else {
               $to_date = date('t-m-Y', strtotime($currDay));
               $_REQUEST['to_date'] =  $to_date;
          }

          $month_select = isset($_REQUEST['month_select']) ? $_REQUEST['month_select'] : '';
          return [
               'curr_month' => $currMonth,
               'curr_year' => $currYear,
               'from_date'    => $from_date,
               'to_date'      => $to_date,
               'month_select' => $month_select,
          ];
     }

     function assignFields($con, $smarty){
          $month_select = $this->generateDateOptions($con);
          $smarty->assign('MONTH_SELECT', $month_select);

          // LOẠI CUỘC GỌI
          $data_type_call = $this->callTypeDirectionTotals($con);
          $smarty->assign('TOTAL_INBOUND', $data_type_call['inbound']);
          $smarty->assign('TOTAL_OUTBOUND', $data_type_call['outbound']);
          $smarty->assign('TOTAL_MISSED', $data_type_call['missed']);
          $smarty->assign('TOTAL_INTERNAL', $data_type_call['internal']);
     }

     private function generateDateOptions($params)
     {
          $quarter_ret   = $this->populateTimeReturn('quarter');
          $month_ret     = $this->populateTimeReturn('month');

          pr($params);

          $opt_arr = [
               [
                   'name' => 'Năm nay',
                   'from_date' => date('Y-01-01'),
                   'to_date' => date('Y-12-31'),
               ], 
          ];
          $opt_arr = array_merge($opt_arr, $quarter_ret);
          $opt_arr = array_merge($opt_arr, $month_ret);

          $html = ''; 
          foreach($opt_arr AS $opt_idx => $opt_item) {
               $selected = '';
               if($params['month_select'] == $opt_idx && $params['month_select'] != '') {
                    $selected = 'selected';
               } else if(strtotime($opt_item['from_date']) == strtotime($params['from_date']) && strtotime($opt_item['to_date']) == strtotime($params['to_date'])) {
                    $selected = 'selected';
               }
               $html .= '<option value="' . $opt_idx . '" ' . $selected . ' data-from-date="' . $opt_item['from_date'] . '" data-to-date="' . $opt_item['to_date'] . '">' . $opt_item['name'] . '</option>';
          }
          return $html;
     }

     function populateTimeReturn($time_opt) {
          $ret = [];
          // trả về 4 quý
          if($time_opt == 'quarter') {
              for($i = 1; $i <= 10; $i+=3) {
                  $from_date = date('Y-' . str_pad($i, 2, 0, STR_PAD_LEFT) . '-01');
                  $to_date = date('Y-' . str_pad(($i + 2), 2, 0, STR_PAD_LEFT) . '-01');
                  $quarter = ceil($i / 3);
                  $ret[$quarter - 1]['from_date'] = $from_date;
                  $ret[$quarter - 1]['to_date'] = $to_date;
                  $ret[$quarter - 1]['name'] = 'Quý ' . $quarter;
              }
          } 
          // trả về 12 tháng
          else if($time_opt == 'month') {
              for ($i = 1; $i <= 12; $i++) {
                  $from_date = date('Y-' . str_pad($i, 2, 0, STR_PAD_LEFT) . '-01');
                  $to_date = date('Y-m-t', strtotime($from_date));
                  $ret[$i - 1]['from_date'] = $from_date;
                  $ret[$i - 1]['to_date'] = $to_date;
                  $ret[$i - 1]['name'] = 'Tháng ' . $i;
              }
          }
  
          return $ret;
     }

     // CALL TYPE DIRECTION - TOTALS
     private function callTypeDirectionTotals($con)
     {
          global $db, $current_user;

          $sql_search = "";
          if (!isAllowedUser()) {
               $sql_search .= " AND assigned_user_id='" . $current_user->id . "' ";
          }

          $inbound = $outbound = $missed = $internal = 0;

          $sql_dir = '
               SELECT COUNT(id) as quantity, direction 
               FROM calls
               WHERE DATE_ADD(date_entered, INTERVAL 7 HOUR) >= "' . date('Y-m-d', strtotime($con['from_date'])) . '"
               AND DATE_ADD(date_entered, INTERVAL 7 HOUR) <= "' . date('Y-m-d', strtotime($con['to_date'])) . ' 23:59:59"
              ' . $sql_search . '
               AND deleted = 0
               GROUP BY direction';

          $res_dir = $db->query($sql_dir);

          while ($row = $db->fetchByAssoc($res_dir)) {
               switch ($row['direction']) {
                    case 'inbound':
                         $inbound = $row['quantity'];
                         break;
                    case 'outbound':
                         $outbound = $row['quantity'];
                         break;
                    case 'missed':
                         $missed = $row['quantity'];
                         break;
                    case 'internal':
                         $internal = $row['quantity'];
                         break;
                    default:
                         break;
               }
          }

          return [
               'inbound' => $inbound,
               'outbound' => $outbound,
               'missed' => $missed,
               'internal' => $internal,
          ];
     }
}
