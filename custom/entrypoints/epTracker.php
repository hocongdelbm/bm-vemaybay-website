<?php
global $db, $current_user, $app_list_strings;

if (isset($_POST['for']) && $_POST['for'] == 'Tracker_ViewBooking') {
	$user_list     = get_user_array(true, 'Active', '', true);
     $item_id       = (isset($_POST['item_id']) && !empty($_POST['item_id'])) ? $_POST['item_id'] : '';
     
	$tbl_detail = '<table id="tbl_detail_viewbooking" class="table-details__booking" cellpadding="0" cellspacing="0">
                    <thead>
                         <th>STT</th>
                         <th>Họ và tên</th>
                         <th>Hành động</th>
                         <th>Lần xem gần nhất</th>
                         <th>Browser</th>
                         <th>IP</th>
                    </thead>';

     $sql = 'SELECT user_id, module_name, item_id, item_summary, date_modified, action, ip_address, browser_name
			FROM tracker 
			WHERE module_name = "EC_Flight_Bookings" AND item_id = "'.$item_id.'" AND deleted = 0
			ORDER BY date_modified DESC';

     $i = 1;
     $res = $db->query($sql);
     $action_arr = array(
          'detailview' => 'Xem',
          'editview' => 'Chỉnh sửa',
          'save' => 'Lưu',
     );

     while ($row = $db->fetchByAssoc($res)) {
          $tbl_detail .= '
               <tr>
                    <td align="center" class="fw-semibold">'.$i.'</td>
                    <td align="left" class="tracker_username">'.$user_list[$row['user_id']].'</td>
                    <td align="center" class="tracker_action">'.$action_arr[$row['action']].'</td>
                    <td align="center" class="tracker_dateview">'.date('H:i:s d-m-Y', strtotime('+7 hours', strtotime($row['date_modified']))).'</td>
                    <td align="center" class="tracker_browser_name">'.$row['browser_name'].'</td>
                    <td align="center" class="tracker_ipaddress">'.$row['ip_address'].'</td>
               </tr>
          ';

          $i++;
     }

	$tbl_detail .= '</table>';

	echo $tbl_detail;
}