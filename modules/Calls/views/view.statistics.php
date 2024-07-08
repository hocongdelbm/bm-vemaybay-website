<?php
require_once("include/Sugar_Smarty.php");

class Viewstatistics extends SugarView
{
     function display()
     {
          if (ACLController::checkAccess('Calls', 'list', true)) {
               $smartyCont = new Sugar_Smarty();
               $this->displayJS();
               $this->populateContent($smartyCont);
               $smartyCont->display('modules/Calls/tpls/statistics.tpl');
          } else {
               header("Location: index.php?module=Calls&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
               exit();
          }
     }

     function displayJS()
     {
          $js = '';
          $js .= '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.3/dist/chart.umd.min.js"></script>';
          $js .= '<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.1.0"></script>';
          echo $js;
     }

     function populateContent($smartyobj)
     {
          global $current_user;

          $from_date = date('d-m-Y');
          $to_date   = date('d-m-Y');

          if (!empty($_POST['from_date'])) {
               $from_date = $_POST['from_date'];
          } else {
               // $from_date = date('d-m-Y', strtotime('monday this week'));
	          $from_date = date('d-m-Y', strtotime('+7 hours'));
          }

          if (!empty($_POST['to_date'])) {
               $to_date = $_POST['to_date'];
          } else{
               // $to_date = date('d-m-Y', strtotime('sunday this week'));
	          $to_date = date('d-m-Y', strtotime('+7 hours'));
          }

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
		$arr_date = array(
			'<option value="" fromdate="" todate="">---Trống---</option>',
			'<option value="this_month" fromdate="' . date('d-m-Y', strtotime('first day of this month')) . '" todate="' . date('d-m-Y', strtotime('last day of this month')) . '">Tháng này</option>',
			'<option value="previous_month" fromdate="' . date('d-m-Y', strtotime('first day of last month')) . '" todate="' . date('d-m-Y', strtotime('last day of last month')) . '">Tháng trước</option>',
			'<option value="quater_this_month" fromdate="' . $quater_fromdate . '" todate="' . $quater_todate . '">Quý này</option>',
			'<option value="quater_previous_month" fromdate="' . date('d-m-Y', strtotime('-3 months', strtotime($quater_fromdate))) . '" todate="' . date('d-m-Y', strtotime('-3 months', strtotime($quater_todate))) . '">Quý trước</option>',
			'<option value="this_year" fromdate="' . date('01-01-Y') . '" todate="' . date('31-12-Y') . '">Năm nay</option>',
			'<option value="previous_year" fromdate="' . date('01-01-Y', strtotime('-1 year')) . '" todate="' . date('31-12-Y', strtotime('-1 year')) . '">Năm trước</option>',
		);
		$smartyobj->assign('DATE_OPTION', implode('', $arr_date));
          $smartyobj->assign('FROM_DATE', $from_date);
          $smartyobj->assign('TO_DATE', $to_date);
          
          if(isAllowedUser()){
               $smartyobj->assign('USER_NAME', '');
          } else {
               $full_name = $current_user->last_name . ' ' .$current_user->first_name;
               $smartyobj->assign('USER_NAME', $full_name);
          }

          // OTPION DATE - RADIO
          $smartyobj->assign('YESTERDAY_FROMDATE', date('d-m-Y', strtotime('-1 day')));
          $smartyobj->assign('YESTERDAY_TODATE', date('d-m-Y', strtotime('-1 day')));
          $smartyobj->assign('DAYBEFORE_FROMDATE', date('d-m-Y', strtotime('-2 day')));
          $smartyobj->assign('DAYBEFORE_TODATE', date('d-m-Y', strtotime('-2 day')));
          $smartyobj->assign('CURRENT_WEEK_FROMDATE', date('d-m-Y', strtotime('monday this week')));
          $smartyobj->assign('CURRENT_WEEK_TODATE', date('d-m-Y', strtotime('sunday this week')));
          $smartyobj->assign('PREVIOUS_WEEK_FROMDATE', date('d-m-Y', strtotime('monday previous week')));
          $smartyobj->assign('PREVIOUS_WEEK_TODATE', date('d-m-Y', strtotime('sunday previous week')));

          // THỐNG KÊ THEO DIRECTION
          $this->statisticsDirection($smartyobj, $from_date, $to_date);
          
          // THỐNG KÊ THEO SĐT
          $this->statisticSDT($smartyobj, $from_date, $to_date);
          
          // THỐNG KÊ THEO NHÂN VIÊN
          $this->statisticsCallEmployees($smartyobj, $from_date, $to_date);
          
          // THỐNG KÊ THỜI LƯỢNG CUỘC GỌI
          $this->statisticsDurationAverage($from_date, $to_date);

          // THỐNG KÊ THỜI LƯỢNG CUỘC GỌI
          $this->statisticsCallSources($from_date, $to_date);

          // ROLE
          $smartyobj->assign('IS_ADMIN', (isAllowedUser()));
		$smartyobj->assign('OWNER', (!isAllowedUser() ? 1 : 0));
     }

     function statisticsDirection($smartyobj, $from_date, $to_date){
          global $db, $current_user;

          $sql_search = "";
          if (!isAllowedUser()) {
			$sql_search .= " AND assigned_user_id='" . $current_user->id . "' ";
		}

          $sql_dir = '
               SELECT COUNT(id) as quantity, direction 
               FROM calls
               WHERE DATE_ADD(date_entered, INTERVAL 7 HOUR) >= "'. date('Y-m-d', strtotime($from_date)) .'"
               AND DATE_ADD(date_entered, INTERVAL 7 HOUR) <= "'. date('Y-m-d', strtotime($to_date)) .' 23:59:59"
              ' . $sql_search . '
               AND deleted = 0
               GROUP BY direction';

          $inbound = $outbound = $missed = $spam = $suddenly = $internal = $total_call_dir = 0;
          $res_dir = $db->query($sql_dir);
          while ($row = $db->fetchByAssoc($res_dir)) {
               $total_call_dir += $row['quantity'];

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
                    case 'spam':
                         $spam = $row['quantity'];
                         break;
                    case 'suddenly':
                         $suddenly = $row['quantity'];
                         break;
                    case 'internal':
                         $internal = $row['quantity'];
                         break;
                    default:
                         break;
               }
          }

          $smartyobj->assign('TOTAL_CALLS_QUANTITY', $total_call_dir);
          $js_data_direction = '['.$inbound.','.$outbound.','.$missed.','.$spam.','.$suddenly.','.$internal.']';
          echo '<script type="text/javascript">
                let js_data_direction = '.$js_data_direction.';
            </script>';
     }

     function statisticSDT($smartyobj, $from_date, $to_date){
          global $db, $current_user;
          $html = '';

          $count_total_inbound = $count_total_outbound = 0;

          $arr_sdt = [
               "1900636060" => array('type'=> 'HOTLINE', 'inbound' => 0, 'outbound' => 0, 'status' => ''), 
               "1900636063" => array('type'=> 'HOTLINE', 'inbound' => 0, 'outbound' => 0, 'status' => ''),  

               "0968304455" => array('type'=> 'VIETTEL', 'inbound' => 0, 'outbound' => 0, 'status' => 'Không gọi ra'),  
               "02866509900" => array('type'=> 'VIETTEL', 'inbound' => 0, 'outbound' => 0, 'status' => 'Không gọi ra'),  
               "0973834501" => array('type'=> 'VIETTEL', 'inbound' => 0, 'outbound' => 0, 'status' => 'Blocked'),  
               "0973891401" => array('type'=> 'VIETTEL', 'inbound' => 0, 'outbound' => 0, 'status' => 'Blocked'),  
               "0974015001" => array('type'=> 'VIETTEL', 'inbound' => 0, 'outbound' => 0, 'status' => 'Blocked'),  
               "0974091002" => array('type'=> 'VIETTEL', 'inbound' => 0, 'outbound' => 0, 'status' => 'Blocked'),  
               "0983171970" => array('type'=> 'VIETTEL', 'inbound' => 0, 'outbound' => 0, 'status' => ''),  
               "0983103703" => array('type'=> 'VIETTEL', 'inbound' => 0, 'outbound' => 0, 'status' => 'Blocked'),  
               "0983129202" => array('type'=> 'VIETTEL', 'inbound' => 0, 'outbound' => 0, 'status' => 'Blocked'),  
               
               // 01/07/2024
               "0962768782" => array('type'=> 'VIETTEL', 'inbound' => 0, 'outbound' => 0, 'status' => ''),  
               "0963323407" => array('type'=> 'VIETTEL', 'inbound' => 0, 'outbound' => 0, 'status' => ''),  
               "0963498793" => array('type'=> 'VIETTEL', 'inbound' => 0, 'outbound' => 0, 'status' => ''),  
               "0963678130" => array('type'=> 'VIETTEL', 'inbound' => 0, 'outbound' => 0, 'status' => ''),  
               "0963986905" => array('type'=> 'VIETTEL', 'inbound' => 0, 'outbound' => 0, 'status' => ''),  
               "0963987527" => array('type'=> 'VIETTEL', 'inbound' => 0, 'outbound' => 0, 'status' => ''),  
               "0964031020" => array('type'=> 'VIETTEL', 'inbound' => 0, 'outbound' => 0, 'status' => ''),  
               "0964359785" => array('type'=> 'VIETTEL', 'inbound' => 0, 'outbound' => 0, 'status' => ''),  

               // MOBIFONE
               "0933296508" => array('type'=> 'MOBIFONE', 'inbound' => 0, 'outbound' => 0, 'status' => ''),  
               "0933297608" => array('type'=> 'MOBIFONE', 'inbound' => 0, 'outbound' => 0, 'status' => ''),  
               "0933625233" => array('type'=> 'MOBIFONE', 'inbound' => 0, 'outbound' => 0, 'status' => ''),  
               "0933799860" => array('type'=> 'MOBIFONE', 'inbound' => 0, 'outbound' => 0, 'status' => ''),  
               "0933026416" => array('type'=> 'MOBIFONE', 'inbound' => 0, 'outbound' => 0, 'status' => ''),  
               "0933611306" => array('type'=> 'MOBIFONE', 'inbound' => 0, 'outbound' => 0, 'status' => ''),  
               "0937451098" => array('type'=> 'MOBIFONE', 'inbound' => 0, 'outbound' => 0, 'status' => ''),  
               "0937523198" => array('type'=> 'MOBIFONE', 'inbound' => 0, 'outbound' => 0, 'status' => ''),  

               // VINAPHONE
               "0913030802" => array('type'=> 'VINAPHONE', 'inbound' => 0, 'outbound' => 0, 'status' => ''),  
               "0914491010" => array('type'=> 'VINAPHONE', 'inbound' => 0, 'outbound' => 0, 'status' => 'Không gọi ra'),  
               "0918038348" => array('type'=> 'VINAPHONE', 'inbound' => 0, 'outbound' => 0, 'status' => ''),  
               "0919018102" => array('type'=> 'VINAPHONE', 'inbound' => 0, 'outbound' => 0, 'status' => ''),  
               
               "02839977788" => array('type'=> 'VNPT', 'inbound' => 0, 'outbound' => 0, 'status' => ''),  
               "02839977799" => array('type'=> 'VNPT', 'inbound' => 0, 'outbound' => 0, 'status' => ''),  
               "02873001886" => array('type'=> 'FPT', 'inbound' => 0, 'outbound' => 0, 'status' => ''),
               "2941581384627345950101" => array('type'=> 'Zalo domestic', 'inbound' => 0, 'outbound' => 0, 'status' => ''),
               "2941581384627345950102" => array('type'=> 'Zalo inter', 'inbound' => 0, 'outbound' => 0, 'status' => ''),
          ];

          $sql_sdt = 'SELECT 
                         direction,
                         IF(
                              JSON_UNQUOTE(JSON_EXTRACT(log, "$.call_direction")) = "inbound",JSON_UNQUOTE(JSON_EXTRACT(log, "$.call_to")), NULL
                         ) AS call_in,
                         IF(
                              JSON_UNQUOTE(JSON_EXTRACT(log, "$.call_direction")) = "outbound" AND JSON_UNQUOTE(JSON_EXTRACT(log, "$.other_caller")) IS NOT NULL,
                              JSON_UNQUOTE(JSON_EXTRACT(log, "$.other_caller")), NULL
                         ) AS call_out
               FROM calls
               WHERE DATE_ADD(date_entered, INTERVAL 7 HOUR) >= "'. date('Y-m-d', strtotime($from_date)) .'"
               AND DATE_ADD(date_entered, INTERVAL 7 HOUR) <= "'. date('Y-m-d', strtotime($to_date)) .' 23:59:59"
               AND deleted = 0';

          $res_sdt = $db->query($sql_sdt);
          while ($row = $db->fetchByAssoc($res_sdt)) {
               if (array_key_exists($row['call_in'], $arr_sdt)) {
                    $arr_sdt[$row['call_in']]['inbound']++;
                    $count_total_inbound++;
               }
               if (array_key_exists($row['call_out'], $arr_sdt)) {
                    $arr_sdt[$row['call_out']]['outbound']++;
                    $count_total_outbound++;
               }
          }

          foreach($arr_sdt as $sdt => $count){
               
               if($count['type'] == 'HOTLINE') $count['type'] = '<b style="color:red">'.$count['type'].'</b>';
               elseif($count['type'] == 'VIETTEL') $count['type'] = '<b style="color:#ea3a59; text-transform:lowercase;">'.$count['type'].'</b>';
               elseif($count['type'] == 'MOBIFONE') $count['type'] = '<b style="color:#006db7">mobi</b><b style="color:#ec1d24">fone</b>';
               elseif($count['type'] == 'VINAPHONE') $count['type'] = '<b style="color:#00aeed; text-transform:lowercase;">'.$count['type'].'</b>';
               elseif($count['type'] == 'VNPT') $count['type'] = '<b style="color:#0066ba; letter-spacing:3px;">'.$count['type'].'</b>';
               elseif($count['type'] == 'FPT') $count['type'] = '<b style="color:#054da2">F</b><b style="color:#f37021">P</b><b style="color:#52b848">T</b>';
               elseif(str_contains($count['type'], 'Zalo')) $count['type'] = '<b style="color:#0068ff">'.$count['type'].'</b>';
               
               $html .= '<tr>
                              <td align="center" class="fw-semibold">'.$count['type'].'</td>
                              <td align="left" class="fw-semibold">'.$sdt.'</td>
                              <td align="center" class="fw-semibold text-primary">'.$count['outbound'].'</td>
                              <td align="center" class="fw-semibold text-success">'.$count['inbound'].'</td>
                              <td align="center" class="fw-semibold text-danger">'.$count['status'].'</td>
                         </tr>';
          }

          $smartyobj->assign('COUNT_SDT', $html);
          $smartyobj->assign('COUNT_SDT_INBOUND', $count_total_inbound);
          $smartyobj->assign('COUNT_SDT_OUTBOUND', $count_total_outbound);
     }

     function statisticsCallEmployees($smartyobj, $from_date, $to_date){
          global $db, $current_user, $app_list_strings;
          $user_list = get_user_array(true, '', '', true);

          $arr_id_admin = array(
               '1', //ducpham
               '168889bb-54c2-59c7-8b3f-649102530d3c', //hungnh
               '9eb0f65f-a9f6-65bb-1985-637ca8511491', //trinh
               '4f4d7a13-4171-9b7d-251c-64dd8f9885e4', //panda
               '622ecf27-f729-7187-7e27-6520e0dab882', //quangnd
               'e4a1676e-536d-b5d2-75c2-6502656a118b', //cuong
               '493ad5e5-ffea-a84f-96d7-6577fed623d6', //booker
          );

          $notInCondition = "'" . implode("', '", $arr_id_admin) . "'";
          $sql = "SELECT CONCAT(IFNULL(u.last_name,''),IF(u.first_name IS NOT NULL,' ',''),IFNULL(u.first_name,'')) AS full_name,
                         u.id as user_id,
                         c.name AS code_calls,
                         c.id AS id_call,
                         c.assigned_user_id,
                         c.direction,
                         c.log,
                         c.call_from,
                         c.call_to,
                         c.status,
                         c.date_end,
                         c.date_start,
                         c.description,
                         c.call_sources
               FROM calls c
               LEFT JOIN users u ON c.assigned_user_id = u.id AND u.deleted = 0
               WHERE DATE(DATE_ADD(c.date_entered, INTERVAL 7 HOUR)) BETWEEN '". date('Y-m-d', strtotime($from_date)) ."' AND '". date('Y-m-d', strtotime($to_date)) ." 23:59:59'
               AND c.deleted = 0
               AND c.assigned_user_id NOT IN (".$notInCondition.")";

          $res = $db->query($sql);
          $html = $all_of_all = '';
          $i   = 1;

          $arr_user_calls = array(
               '976a054f-370f-7945-7776-5d1c28f96e8a' => array(
                    'name' => 'Nguyễn Ngọc Lan Phương',
                    'agent' => '101',
               ),
               '2d7dfd04-2e91-8302-0c91-56d69b4cc08e' => array(
                    'name' => 'Nguyễn Thị Đông',
                    'agent' => '102',
               ),
               'a2dae06b-ca09-7b35-b1a2-5ccb9c7cab3d' => array(
                    'name' => 'Đoàn Thị Kim Ly',
                    'agent' => '103',
               ),
               'da25400e-a030-389c-4228-5c233d8cd04e' => array(
                    'name' => 'Trần Minh Tuấn',
                    'agent' => '104',
               ),
               '7c20e013-b0d6-e1f3-b113-53deed58f0a2' => array(
                    'name' => 'Trần Như Điền',
                    'agent' => '105',
               ),
               '9a9ba7fd-bb1a-e132-b5fc-5bee7dcada12' => array(
                    'name' => 'Trương Mỹ Nhân',
                    'agent' => '106',
               ),
               'ebc40fa1-8878-1a86-000d-5b6949a87e11' => array(
                    'name' => 'Lê Tín Nghĩa',
                    'agent' => '107',
               ),
               'cb0ad38e-3524-deea-220f-62f20cec08d5' => array(
                    'name' => 'Nguyễn Duy Đăng',
                    'agent' => '108',
               ),
               '37cd4853-721c-9808-af64-5600c8835d03' => array(
                    'name' => 'Đỗ Thị Kim Ngân',
                    'agent' => '120',
               ),
               'b4ff32c8-8a1e-0648-b20d-63437ab44554' => array(
                    'name' => 'Nguyễn Trang Đài',
                    'agent' => '121',
               ),
               'd61ac0c1-91b3-0dc8-049a-518b21d2deb9' => array(
                    'name' => 'Chung Thanh Nhân',
                    'agent' => '122',
               ),
               '9f381038-99c2-7515-938f-558939fee19a' => array(
                    'name' => 'Thiều Tuấn Anh',
                    'agent' => '201',
               ),
               'd14007fa-aaed-cac7-9a00-62cfccf58d5a' => array(
                    'name' => 'Nghiêm Xuân Đức',
                    'agent' => '202',
               ),
               '61b537e5-6bc5-77e5-1102-5ff3dc1e40ee' => array(
                    'name' => 'Phạm Chiến Thắng',
                    'agent' => '203',
               ),
               '9ba5c5a0-a402-02f4-76d3-53ba0481ce45' => array(
                    'name' => 'Thái Thị Yến Oanh',
                    'agent' => '124',
               ),
               '72ece22c-cb25-8e30-9dea-56f2201cd359' => array(
                    'name' => 'Bùi Thị Quỳnh Trang',
                    'agent' => '123',
               ),
               'b5523dbd-b9a7-67c0-77b5-533e6ece89b1' => array(
                    'name' => 'Nguyễn Ngọc Thu',
                    'agent' => '125',
               ),
               '4ef24994-3d8e-ff0d-2784-599d0b3e56e1' => array(
                    'name' => 'Nguyễn Lộc Danh',
                    'agent' => '109',
               ),
               'empty' => array(
                    'name' => 'Không xác định',
                    'agent' => '000',
               ),
          );

          while ($row = $db->fetchByAssoc($res)) {
               $userId        = (isset($row['assigned_user_id']) && !empty($row['assigned_user_id'])) ? $row['assigned_user_id'] : 'empty';
               $direction     = $row['direction'];

               if($direction == 'inbound'){
                    // Cuộc gọi đến
                    $call_log_inbound = json_decode(html_entity_decode($row['log']), true);
                    if(isset($call_log_inbound['list_agent']) && !empty($call_log_inbound['list_agent'])){
                         $list_agent_inbound = explode(',', $call_log_inbound['list_agent']);
                         $element_last =  array_pop($list_agent_inbound); //101

                         if(count($list_agent_inbound) > 0){
                              foreach($list_agent_inbound as $agent){
                                   $user_id = custom_get_sip_number($agent);
                                   $arr_user_calls[$user_id]['missed'][] = $row;
                              }
                              
                              // inbound - elemennt_last
                              if($userId && $userId != 'empty'){
                                   $arr_user_calls[$userId]['inbound'][] = $row;
                              } else {
                                   $user_id_last = custom_get_sip_number($element_last);
                                   $arr_user_calls[$user_id_last]['inbound'][] = $row;
                              }
                         } else {
                              $arr_user_calls[$userId]['inbound'][] = $row;
                         }
                    } 
               } 
               else if($direction == 'missed'){
                    // Cuộc gọi nhỡ
                    $call_log_missed = json_decode(html_entity_decode($row['log']), true);
                    if(isset($call_log_missed['list_agent']) && !empty($call_log_missed['list_agent'])){
                         $list_agent_missed = explode(',', $call_log_missed['list_agent']);
                         // Đỗ chuông qua agent nhưng không nghe máy
                         foreach($list_agent_missed as $agent){
                              $user_id = custom_get_sip_number($agent);
                              $arr_user_calls[$user_id]['missed'][] = $row;
                         }
                    } else {
                         // Khách chủ động tắt máy
                         $arr_user_calls['empty']['missed'][] = $row;
                    }
               } else if($direction == 'outbound'){
                    // Cuộc gọi đi
                    $arr_user_calls[$userId]['outbound'][] = $row;
               } else if($direction == 'spam'){
                    // Số rác
                    $arr_user_calls[$userId]['spam'][] = $row;
               } else if($direction == 'suddenly'){
                    $arr_user_calls[$userId]['suddenly'][] = $row;
               }  else if($direction == 'internal'){
                    $user_id = custom_get_sip_number($row['call_from']);
                    if(isset($user_id) && !empty($user_id)){
                         $arr_user_calls[$user_id]['internal'][] = $row;
                    } else {
                         $arr_user_calls['empty']['internal'][] = $row;
                    }
               }
          }

          $total_inbound      = $total_outbound = $total_missed = $total_suddenly = $total_spam =  $total_internal = $total_final = 0;
          $total_inbound_all  = $total_outbound_all = $total_missed_all = $total_spam_all = $total_suddenly_all = $total_internal_all = 0;

          $total_outbound_answer = $total_outbound_noanswer = 0;

          // Sắp xếp mảng theo giá trị giảm dần của 'inbound'
          uasort($arr_user_calls, function ($a, $b) {
               return count($b['inbound']) - count($a['inbound']);
          });
          
          // if($current_user->user_name = 'hungnh'){
          //      pr($arr_user_calls);
          // }

          foreach($arr_user_calls as $user_id => $user){
               if(count($user) > 2){
                    if($current_user->id == $user_id || isAllowedUser()){
                         // ALL OF CALL
                         foreach($user as $key => $dir){
                              // class direction
                              if($key == 'suddenly'){
                                   $direction_class = 'text-warning';
                              } else if ($key == 'inbound'){
                                   $direction_class = 'text-success';
                              } else if ($key == 'missed'){
                                   $direction_class = 'text-danger';
                              } else if ($key == 'outbound'){
                                   $direction_class = 'text-primary';     

                              } else if ($key == 'spam'){
                                   $direction_class = 'text-spam';
                              } else {
                                   $direction_class = 'text-normal';
                              }
               
                              if($key != 'name' && $key != 'agent'){
                                   usort($dir, function($a, $b) {
                                        $dateA = DateTime::createFromFormat('d-m-Y H:i:s', $a['date_start']);
                                        $dateB = DateTime::createFromFormat('d-m-Y H:i:s', $b['date_start']);
                                        return $dateA <=> $dateB;
                                   });

                                   foreach($dir as $index => $call){
                                        if ($key == 'outbound'){
                                             // Gọi đi Nghe máy và Không nghe máy
                                             $data_log = json_decode(html_entity_decode($call['log']), true);
                                             if($data_log['call_talk'] > 0){
                                                  $user['outbound_answer']++;
                                                  $key_direction = 'outbound ob_answer';
                                             } else {
                                                  $user['outbound_noanswer']++;
                                                  $key_direction = 'outbound ob_noanswer';
                                             }
                                        }  else {
                                             $key_direction = $key;
                                        }

                                        // Class status
                                        if($call['status'] == 'processing'){
                                             $status_class = 'text-warning';
                                        } else if ($call['status'] == 'done'){
                                             $status_class = 'text-success';
                                        } else {
                                             $status_class = 'text-normal';
                                        }

                                        // caculation duration
                                        $duration =  date('H:i:s', (strtotime($call['date_end']) - strtotime($call['date_start'])));

                                        $all_of_all .='<tr class="" data-user_id="'.$user_id.'" data-direction="'.$key_direction.'">
                                                            <td align="center" class="fw-semibold">'.($index+1).'</td>
                                                            <td align="left"><a target="_blank" href="index.php?module=Calls&return_module=Calls&action=DetailView&record='.$call['id_call'].'">'.$call['code_calls'].'</a></td>
                                                            <td align="center" class="status '.$status_class.'"><strong>'.$app_list_strings['call_status_dom'][$call['status']].'</strong></td>
                                                            <td class="call_from">'.$call['call_from'].'</td>
                                                            <td class="call_to">'.$call['call_to'].'</td>
                                                            <td class="call_sources">'.$call['call_sources'].'</td>
                                                            <!-- <td align="center" class="'.$direction_class.'"><strong>'.$app_list_strings['calls_direction_list'][$key].'</strong></td> -->
                                                            <td align="center" class="time-call">'.$call['date_start'].'</td>
                                                            <td align="center" class="duration">'.$duration.'</td>
                                                            <td align="left" class="description">'.$call['description'].'</td>
                                                       </tr>';
                                        $index++;
                                   }
                              }
                         };

                         // EMPLOYEE CALL
                         // ----------------------------------
                         // Gọi đến
                         $inbound       = isset($user['inbound']) ? '<strong class="text-success">'.count($user['inbound']).'</strong>' : '';
                         $total_inbound = isset($user['inbound']) ? count($user['inbound']) : 0;
                         $total_inbound_all += $total_inbound;

                         // Gọi đi
                         $outbound  = isset($user['outbound']) ? '<strong class="text-primary">'.count($user['outbound']).'</strong>' : '';
                         $total_outbound = isset($user['outbound']) ? count($user['outbound']) : 0;
                         $total_outbound_all += $total_outbound;

                         $outbound_answer    = isset($user['outbound_answer']) ? '<strong class="text-dark">'.$user['outbound_answer'].'</strong>' : '';
                         $outbound_noanswer  = isset($user['outbound_noanswer']) ? '<strong class="text-dark">'.$user['outbound_noanswer'].'</strong>': '';
                         isset($user['outbound_answer']) ? $total_outbound_answer += $user['outbound_answer'] : 0;
                         isset($user['outbound_noanswer']) ? $total_outbound_noanswer += $user['outbound_noanswer'] : 0;
                    
                         // Nhỡ
                         $missed    = isset($user['missed']) ? '<strong class="text-danger">'.count($user['missed']).'</strong>' : '';
                         $total_missed = isset($user['missed']) ? count($user['missed']) : 0;
                         $total_missed_all += $total_missed;
                         
                         // Nhá
                         $suddenly      = isset($user['suddenly']) ? '<strong class="text-warning">'.count($user['suddenly']).'</strong>' : '';
                         $total_suddenly = isset($user['suddenly']) ? count($user['suddenly']) : 0;
                         $total_suddenly_all += $total_suddenly;
                         
                         // Số rác
                         $spam      = isset($user['spam']) ? '<strong class="text-spam">'.count($user['spam']).'</strong>' : '';
                         $total_spam = isset($user['spam']) ? count($user['spam']) : 0;
                         $total_spam_all += $total_spam;
                         
                         // Nội bộ
                         $internal  = isset($user['internal']) ? '<strong class="text-normarl">'.count($user['internal']).'</strong>' : '';
                         $total_internal = isset($user['internal']) ? count($user['internal']) : 0;
                         $total_internal_all += $total_internal;

                         $total_calls = $total_inbound + $total_outbound + $total_missed + $total_suddenly + $total_spam + $total_internal;
                         $total_final += $total_calls;

                         $html .= '
                              <tr>
                                   <td align="center" class="fw-semibold hide-mobile">'.$i.'</td>
                                   <td align="left" class="full_name">'.$user['name'].'</td>
                                   <td align="center" class="cursor-pointer view-detail-calls" data-username="'.$user['name'].'" data-user_id="'.$user_id.'" data-direction="outbound">'.$outbound.'</td>
                                   <td align="center" class="cursor-pointer view-detail-calls" data-username="'.$user['name'].'" data-user_id="'.$user_id.'" data-direction="ob_answer">'.$outbound_answer.'</td>
                                   <td align="center" class="cursor-pointer view-detail-calls" data-username="'.$user['name'].'" data-user_id="'.$user_id.'" data-direction="ob_noanswer">'.$outbound_noanswer.'</td>
                                   <td align="center" class="cursor-pointer view-detail-calls" data-username="'.$user['name'].'" data-user_id="'.$user_id.'" data-direction="inbound">'.$inbound.'</td>
                                   <td align="center" class="cursor-pointer view-detail-calls" data-username="'.$user['name'].'" data-user_id="'.$user_id.'" data-direction="missed">'.$missed.'</td>
                                   <td align="center" class="cursor-pointer view-detail-calls hide-mobile" data-username="'.$user['name'].'" data-user_id="'.$user_id.'" data-direction="spam">'.$spam.'</td>
                                   <td align="center" class="cursor-pointer view-detail-calls hide-mobile" data-username="'.$user['name'].'" data-user_id="'.$user_id.'" data-direction="suddenly">'.$suddenly.'</td>
                                   <td align="center" class="cursor-pointer view-detail-calls hide-mobile" data-username="'.$user['name'].'" data-user_id="'.$user_id.'" data-direction="internal">'.$internal.'</td>
                              </tr>
                         ';
                         $i++;
                    }
               }

          
          }


          $smartyobj->assign('CALLS_DATA', $html);
          $smartyobj->assign('TTL_INBOUND', $total_inbound_all);

          $smartyobj->assign('TTL_OUTBOUND', $total_outbound_all);
          $smartyobj->assign('TTL_OUTBOUND_ANSWER', $total_outbound_answer);
          $smartyobj->assign('TTL_OUTBOUND_NOANSWER', $total_outbound_noanswer);

          $smartyobj->assign('TTL_MISSED', $total_missed_all);
          $smartyobj->assign('TTL_SPAM', $total_spam_all);
          $smartyobj->assign('TTL_SUDDENLY', $total_suddenly_all);
          $smartyobj->assign('TTL_INTERNAL', $total_internal_all);
          $smartyobj->assign('TTL_FINAL', $total_final);
          $smartyobj->assign('ALL_OF_CALLS', $all_of_all);
     }

     function statisticsDurationAverage($from_date, $to_date){
          global $db, $current_user;

          $sql_search = "";
          if (!isAllowedUser()) {
			$sql_search .= " AND assigned_user_id='" . $current_user->id . "' ";
		}

          $sql = "SELECT name, log, call_type
          FROM calls
          WHERE direction IN ('inbound', 'outbound')
          AND DATE(DATE_ADD(date_entered, INTERVAL 7 HOUR)) BETWEEN '". date('Y-m-d', strtotime($from_date)) ."' AND '". date('Y-m-d', strtotime($to_date)) ." 23:59:59'
          " . $sql_search . "
          AND deleted = 0";

          $res = $db->query($sql);
          $count_phone = 0;
          $total_duration_phone = $total_ring_phone = $total_talk_phone = 0;
          $count_zalo = 0;
          $total_duration_zalo = $total_ring_zalo = $total_talk_zalo = 0;

          // if($current_user->user_name == 'hungnh'){
          //      pr($sql);
          // }

          while ($row = $db->fetchByAssoc($res)) {
               
               if($row['call_type'] == 'phone'){
                    $arr_log_phone = json_decode(html_entity_decode($row['log']), true);

                    $total_duration_phone += $arr_log_phone['call_duration'];
                    $total_talk_phone += $arr_log_phone['call_talk'];
                    $total_ring_phone += ($arr_log_phone['call_duration'] - $arr_log_phone['call_talk']);
                    $count_phone++;
               } else if($row['call_type'] == 'zalo'){
                    $arr_log_zalo = json_decode(html_entity_decode($row['log']), true);

                    $total_duration_zalo += $arr_log_zalo['call_duration'];
                    $total_talk_zalo += $arr_log_zalo['call_talk'];
                    $total_ring_zalo += ($arr_log_zalo['call_duration'] - $arr_log_zalo['call_talk']);
                    $count_zalo++;
               }
          }

          $average_duration_phone  = empty($count_phone) ? 0 : (int)($total_duration_phone / $count_phone);
          $average_ring_phone      = empty($count_phone) ? 0 : (int)($total_ring_phone / $count_phone);
          $average_talk_phone      = empty($count_phone) ? 0 : (int)($total_talk_phone / $count_phone);

          $average_duration_zalo  = empty($count_zalo) ? 0 : (int)($total_duration_zalo / $count_zalo);
          $average_ring_zalo      = empty($count_zalo) ? 0 : (int)($total_ring_zalo / $count_zalo);
          $average_talk_zalo      = empty($count_zalo) ? 0 : (int)($total_talk_zalo / $count_zalo);

          echo '<script type="text/javascript">
                    let data_duration = ['.$average_duration_phone.','.$average_duration_zalo.'];
                    let data_ringing = ['.$average_ring_phone.','.$average_ring_zalo.'];
                    let data_talk = ['.$average_talk_phone.','.$average_talk_zalo.'];
               </script>';
     }

     function statisticsCallSources($from_date, $to_date){
          global $db, $current_user;

          $sql_search = "";
          if (!isAllowedUser()) {
			$sql_search .= " AND assigned_user_id='" . $current_user->id . "' ";
		}

          $sql = "SELECT call_sources, direction, count(*) as quantity
          FROM calls
          WHERE direction IN ('inbound', 'missed')
          AND DATE(DATE_ADD(date_entered, INTERVAL 7 HOUR)) BETWEEN '". date('Y-m-d', strtotime($from_date)) ."' AND '". date('Y-m-d', strtotime($to_date)) ." 23:59:59'
          " . $sql_search . "
          AND deleted = 0
          GROUP BY call_sources, direction";

          $res = $db->query($sql);
          $label_source_arr   = array();
          $label_source       = "[";
          $quantity_inbound   = "[";
          $quantity_missed    = "[";

          while ($row = $db->fetchByAssoc($res)) {
               $label_source_arr[$row['call_sources']][$row['direction']] = $row['quantity'];
          }

          foreach($label_source_arr as $source => $quantity){
               if(empty($source)){
                    $source = 'Khác';
               }

               $label_source .= "'".$source."',";
               $quantity_inbound .= "'".$quantity['inbound']."',";
               $quantity_missed .= "'".$quantity['missed']."',";
          }

          $label_source = substr($label_source, 0, -1); //Loại bỏ dấu , của element cuối cùng
          $label_source .= "]";

          $quantity_inbound = substr($quantity_inbound, 0, -1); //Loại bỏ dấu , của element cuối cùng
          $quantity_inbound .= "]";

          $quantity_missed = substr($quantity_missed, 0, -1); //Loại bỏ dấu , của element cuối cùng
          $quantity_missed .= "]";

          echo '<script type="text/javascript">
                const label_source = '.$label_source.';
                const quantity_inbound = '.$quantity_inbound.';
                const quantity_missed = '.$quantity_missed.';
            </script>';
     }
}
