<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type = isset($_POST['type']) ? $_POST['type'] : "";

    if($type == "get_contact") {
        global $db, $current_user, $app_list_strings;
        $phone = isset($_POST['phone']) ? global_test_input(str_replace(" ", "", $_POST['phone'])) : "";
        $zalo_id = isset($_POST['zalo_id']) ? global_test_input($_POST['zalo_id']) : "";


        /**********  1. Get info from DB  **********/
        $data = [
            'contact_id'    => '',
            'name'          => '',
            'phone'         => $phone,
            'zalo_id'       => $zalo_id,
            'email'         => '',
            'avatar'        => '',
            'info_booking'  => ''
        ];


        $where = '';
        if(!empty($phone) && !empty($zalo_id)) $where = '(con.phone_mobile = "'.$phone.'" AND con.zalo_id = "'.$zalo_id.'")';
        elseif(!empty($phone)) $where = 'con.phone_mobile = "'.$phone.'"';
        elseif(!empty($zalo_id)) $where = 'con.zalo_id = "'.$zalo_id.'"';
        
        $sql = '
            SELECT 
                con.id,
                con.last_name AS name,
                IFNULL(con.phone_mobile, "") AS phone,
                IFNULL(con.zalo_id, "") AS zalo_id,
                IFNULL(e.email_address, "") AS email
            FROM contacts con
                LEFT JOIN email_addr_bean_rel eb ON eb.bean_id = con.id AND eb.bean_module = "Contacts" AND eb.deleted = 0
                LEFT JOIN email_addresses e ON e.id = eb.email_address_id
            WHERE '.$where.' 
                AND con.deleted = 0
        ';

        $res = $db->query($sql);
        while ($row = $db->fetchByAssoc($res)) {
            $data['contact_id'] = $row['id'];
            $data['name']       = !is_null($row['name']) ? $row['name'] : '';
            $data['phone']      = !is_null($row['phone']) ? $row['phone'] : $phone;
            $data['zalo_id']    = !is_null($row['zalo_id']) ? $row['zalo_id'] : $zalo_id;
            $data['email']      = !is_null($row['email']) ? $row['email'] : '';
        }


        /**********  2. Get zalo info **********/
        if(!empty($data['zalo_id'])) {
            require_once('modules/EC_SMS_Logs/Zalo.php');
            $zalo = new Zalo();

            // Get user info
            $json_user_info = $zalo->get_user_info($data['zalo_id']);
            $user_info      = json_decode($json_user_info, true);
            
            if($user_info['error'] == 0) {
                $zalo_phone = (isset($user_info['data']['shared_info']) && isset($user_info['data']['shared_info']['phone'])) ? $user_info['data']['shared_info']['phone'] : '';
                // Get phone from user_alias
                if(empty($zalo_phone)) $zalo_phone = get_phone_by_alias($user_info['data']['user_alias']);

                if(empty($data['phone'])) $data['phone'] = $zalo->unformat_zalo_phone($zalo_phone);
                if(empty($data['name'])) $data['name'] = $user_info['data']['display_name'];
                $data['avatar'] = isset($user_info['data']['avatars']['240']) ? $user_info['data']['avatars']['240'] : $user_info['data']['avatar'];
            }
        }

        
        /**********  3. Get booking info of contact via phone **********/
        $phone_lh = (isset($data['phone']) && !empty($data['phone'])) ? $data['phone'] : $phone;
        $data['info_booking'] = get_booking_info($phone_lh);

        /**********  4. Get call info of contact via phone **********/
        $data['info_refund_ticket'] = get_booking_refund($phone_lh);

        /**********  5. Get call info of contact via phone **********/
        $data['info_call'] = get_call_info($phone_lh);

        // Return
        echo json_encode($data);
        exit();
    }
    elseif($type == "get_contact_zalo") { // Dùng cho gọi ra đường Zalo
        /** Tương tác của người dùng với OA là một trong các hành động:
         * Quan tâm OA
         * Gửi tin nhắn đến OA
         * Gọi đến OA hoặc chấp nhận cuộc gọi từ OA
         * Nhấn menu Tương tác nhanh, menu Dịch vụ hoặc các CTA của OA Chatbot
         */

        global $db, $app_list_strings;
        $number = isset($_POST['number']) ? global_test_input(str_replace(" ", "", $_POST['number'])) : "";


        /**********  1. Get info from DB  **********/
        $zalo_id = '';
        $data = array(
            'contact_id'    => '',
            'name'          => '',
            'phone'         => '',
            'zalo_id'       => '',
            'email'         => '',
            'avatar'        => '',
            'info_booking'  => ''
        );

        // Lấy thông tin từ số điện thoại
        if(strlen($number) < 15) {
            $sql_1 = '
                SELECT con.id,
                    con.last_name AS name,
                    IFNULL(con.phone_mobile, "") AS phone_mobile,
                    IFNULL(con.zalo_id, "") AS zalo_id,
                    IFNULL(e.email_address, "") AS email
                FROM contacts con
                    LEFT JOIN email_addr_bean_rel eb ON eb.bean_id = con.id AND eb.bean_module = "Contacts" AND eb.deleted = 0
                    LEFT JOIN email_addresses e ON e.id = eb.email_address_id
                WHERE con.phone_mobile = "'.$number.'" AND con.deleted = 0
                ORDER BY date_entered
                LIMIT 1
            ';

            $res_1 = $db->query($sql_1);
            while ($row = $db->fetchByAssoc($res_1)) {
                if(is_null($row['zalo_id']) || empty($row['zalo_id'])) continue;

                $data['contact_id'] = $row['id'];
                $data['name']       = !is_null($row['name']) ? $row['name'] : '';
                $data['phone']      = $number;
                $data['zalo_id']    = !is_null($row['zalo_id']) ? $row['zalo_id'] : '';
                $data['email']      = !is_null($row['email']) ? $row['email'] : '';
            }

            $zalo_id = $data['zalo_id'];
        }
        // Lấy thông tin từ Zalo id
        else {
            $sql_1 = '
                SELECT con.id,
                    con.last_name AS name,
                    IFNULL(con.phone_mobile, "") AS phone_mobile,
                    IFNULL(con.zalo_id, "") AS zalo_id,
                    IFNULL(e.email_address, "") AS email
                FROM contacts con
                    LEFT JOIN email_addr_bean_rel eb ON eb.bean_id = con.id AND eb.bean_module = "Contacts" AND eb.deleted = 0
                    LEFT JOIN email_addresses e ON e.id = eb.email_address_id
                WHERE con.zalo_id = "'.$number.'" AND con.deleted = 0
                ORDER BY date_entered
                LIMIT 1
            ';

            $res_1 = $db->query($sql_1);
            while ($row = $db->fetchByAssoc($res_1)) {
                $data['contact_id'] = $row['id'];
                $data['name']       = !is_null($row['name']) ? $row['name'] : '';
                $data['phone']      = !is_null($row['phone_mobile']) ? $row['phone_mobile'] : '';
                $data['zalo_id']    = $number;
                $data['email']      = !is_null($row['email']) ? $row['email'] : '';
            }

            $zalo_id = $number;
        }


        /**********  2. Get and check zalo info **********/
        if(!empty($zalo_id)) {
            require_once('modules/EC_SMS_Logs/Zalo.php');
            $zalo = new Zalo();

            // Get user info
            $json_user_info = $zalo->get_user_info($zalo_id);
            $user_info = json_decode($json_user_info, true);
            if($user_info['error'] != 0) {
                echo json_encode([
                    'error' => 1,
                    'message' => isset($user_info['message']) ? $user_info['message'] : 'Thông tin Zalo không hợp lệ'
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $zalo_phone = (isset($user_info['data']['shared_info']) && isset($user_info['data']['shared_info']['phone'])) ? $user_info['data']['shared_info']['phone'] : '';
            // Get phone from user_alias
            if(empty($zalo_phone)) $zalo_phone = get_phone_by_alias($user_info['data']['user_alias']);

            if(empty($data['phone'])) $data['phone'] = $zalo->unformat_zalo_phone($zalo_phone);
            if(empty($data['name'])) $data['name'] = $user_info['data']['display_name'];
            $data['avatar'] = $user_info['data']['avatars']['240'];
            $data['last_interaction'] = isset($user_info['data']['user_last_interaction_date']) ? $user_info['data']['user_last_interaction_date'] : ''; // dd/mm/yyyy

            // // Get messages to get last interaction
            // $json_message = $zalo->get_messages($zalo_id);
            // $messages = json_decode($json_message, true);
            
            // if($messages['error'] == 0 && !empty($messages['data'])) {
            //     foreach($messages['data'] as $row_message) {
            //         // User chủ động
            //         if($row_message['from_id'] == $zalo_id) {
            //             $data['last_interaction'] = date('Y-m-d H:i:s', strtotime(str_replace("/", "-", $row_message['sent_time'])));
            //             break;
            //         }
            //         // OA chủ động (User trả lời cuộc gọi)
            //         else if($row_message['type'] == 'nosupport') {
            //             $sql_2 = 'SELECT log
            //                 FROM calls
            //                 WHERE direction = "outbound"
            //                     AND call_from = "'.$zalo->TRAVELPASS_ZALO_ID.'"
            //                     AND call_to = "'.$zalo_id.'"
            //                     AND deleted = 0
            //                 ORDER BY date_entered DESC
            //             ';

            //             $res_2 = $db->query($sql_2);
            //             while ($row = $db->fetchByAssoc($res_2)) {
            //                 $call_log = json_decode(html_entity_decode($row['log']), true);

            //                 // User có trả lời
            //                 if($call_log['call_talk'] > 0) {
            //                     $data['last_interaction'] = $call_log['call_start'];
            //                     break;
            //                 }
            //             }

            //             if(!empty($data['last_interaction'])) break;
            //         }
            //     }
            // }

            // Check interaction within 30 days
            if( empty($data['last_interaction']) || ((strtotime(date('d/m/Y')) - strtotime($data['last_interaction'])) / 86400 > 30) ) {
                echo json_encode([
                    'error' => 1,
                    'message' => 'Không thể gọi đến Zalo này',
                    'data' => $data,
                    'mes' => $messages
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }
        }
        else {
            echo json_encode([
                    'error' => 1,
                    'message' => 'Không có thông tin Zalo'
                ], JSON_UNESCAPED_UNICODE);
            exit();
        }


        /**********  3. Get booking info of contact via phone **********/
        if(!empty($data['phone'])) {
            $phone_lh = global_test_input(str_replace(" ", "", $data['phone']));
            $data['info_booking'] = get_booking_info($phone_lh);
        }


        // Return
        echo json_encode([
            'error' => 0,
            'message' => '',
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
    elseif($type == "update_call") {
        $call_id    = isset($_POST['call_id']) ? global_test_input($_POST['call_id']) : "";
        $contact_id = isset($_POST['contact_id']) ? global_test_input($_POST['contact_id']) : "";
        $booking_id = isset($_POST['booking_id']) ? global_test_input($_POST['booking_id']) : "";
        $phone      = isset($_POST['phone']) ? global_test_input(str_replace(" ", "", $_POST['phone'])) : "";
        $zalo_id    = isset($_POST['zalo_id']) ? global_test_input($_POST['zalo_id']) : "";
        $name       = isset($_POST['name']) ? global_test_input($_POST['name']) : "";
        $email      = isset($_POST['email']) ? global_test_input($_POST['email']) : "";
        $note       = isset($_POST['note']) ? global_test_input($_POST['note']) : "";

        // Validate
        if(empty($call_id)) {
            echo 0;
            exit();
        }
        global $db, $current_user;


        /**********  1. Handle Call & Contact  **********/
        if(empty($contact_id)) {
            $where = '';
            // if(!empty($phone) && !empty($zalo_id)) $where = '(phone_mobile = "'.$phone.'" AND zalo_id = "'.$zalo_id.'")';
            if(!empty($phone)) $where = 'phone_mobile = "'.$phone.'"';
            elseif(!empty($zalo_id)) $where = 'zalo_id = "'.$zalo_id.'"';

            $sql = 'SELECT id
                FROM contacts
                WHERE '.$where.' AND deleted = 0
                LIMIT 1';
            $contact_id = $db->getOne($sql);
        }

        $con = new Contact();
        if($contact_id && !empty($contact_id)) {
            $save = false;
            $con->retrieve($contact_id);

            if(empty($con->phone_mobile)) {
                $con->phone_mobile = $phone;
                $save = true;
            }
            if(empty($con->zalo_id)) {
                $con->zalo_id = $zalo_id;
                $save = true;
            }
            if(!empty($name)) {
                $con->last_name = $name;
                $save = true;
            }
            if(!empty($email)) {
                $con->email1 = $email;
                $save = true;
            }
            if($save === true) $con->save();
        }
        else {
            $sql = 'SELECT name
                FROM calls
                WHERE call_id = "'.$call_id.'" AND deleted = 0';
            $call_name = $db->getOne($sql);

            $con->phone_mobile = $phone;
            $con->zalo_id = $zalo_id;
            $con->last_name = $name;
            $con->email1 = $email;
            $con->description = "Liên hệ tạo từ cuộc gọi " . $call_name;
            $con->assigned_user_id = $current_user->id;
            $con->save();
        }

        $sql = '
            UPDATE calls
            SET parent_type = "Contacts", parent_id = "'.$con->id.'", description = "'.$note.'", booking_id = "'.$booking_id.'",
                created_by = "'.$current_user->id.'",
                modified_user_id = "'.$current_user->id.'",
                assigned_user_id = "'.$current_user->id.'"
            WHERE call_id = "'.$call_id.'" AND deleted = 0';
        $db->query($sql);


        /**********  2. Handle Booking  **********/
        if(!empty($booking_id)) {
            $booking_name = isset($_POST['booking_name']) ? global_test_input($_POST['booking_name']) : "";
            $type_call_booking = isset($_POST['type_call_booking']) ? global_test_input($_POST['type_call_booking']) : "";

            if(!empty($type_call_booking)) {
                $work                       = new EC_Working_Process();
                $work->name 			    = $booking_name;
                $work->parent_type 		    = 'EC_Flight_Bookings';
                $work->parent_id 		    = $booking_id;
                $work->description 		    = $note;
                $work->$type_call_booking   = 1;
                $work->assigned_user_id     = $current_user->id;
                $work->save();
    
                $bean_note                      = new Note();
                $bean_note->name                = $booking_name;
                $bean_note->parent_type         = 'EC_Flight_Bookings';
                $bean_note->parent_id           = $booking_id;
                $bean_note->description         = $note;
                $bean_note->booking_status      = ($type_call_booking == 'called' ? '6' : '');
                $bean_note->working_process_id  = $work->id;
                $bean_note->assigned_user_id    = $current_user->id;
                $bean_note->save();

                // Update status and assigned
                if($type_call_booking == 'called') {
                    $sql_update = 'UPDATE ec_flight_bookings
                        SET booking_status = "6", assigned_user_id = "'.$current_user->id.'"
                        WHERE id = "'.$booking_id.'" AND deleted = 0';
                    $db->query($sql_update);
                }
            }
        }


        /**********  3. Handle Zalo  **********/
        if(!empty($zalo_id) && empty($phone)) {
            require_once('modules/EC_SMS_Logs/Zalo.php');
            $objZalo = new Zalo();
            $json = $objZalo->get_user_info($zalo_id);
            $arr = json_decode($json, true);

            if(isset($arr['error']) && $arr['error'] == 0) {
        
                // Send request info
                if( !isset($arr['data']['shared_info']) ||
                    !isset($arr['data']['shared_info']['phone']) ||
                    empty($arr['data']['shared_info']['phone'])
                ) {
                    // Get phone from user_alias
                    if(empty(get_phone_by_alias($arr['data']['user_alias']))) {
                        $objZalo->send_request_user_info($zalo_id);
                        $objZalo->send_to_telegram('Gửi yêu cầu thông tin đến Zalo <b>'. $zalo_id .'</b>');
                    }
                }
            }
        }

        echo 1;
        exit();
    }
    elseif($type == "check_missed_call") {
        $call_id = isset($_POST['call_id']) ? global_test_input($_POST['call_id']) : "";

        if(empty($call_id)) {
            echo 0;
            exit();
        }

        global $db;
        $sql = 'SELECT call_id
            FROM calls
            WHERE call_id = "'.$call_id.'" AND direction = "missed" AND deleted = 0
        ';
        $id = $db->getOne($sql);

        if($id && !empty($id)) echo 1;
        else echo 0;
        exit();
    }
    elseif($type == "map_call_booking") {
        $call_name = isset($_POST['call_name']) ? global_test_input($_POST['call_name']) : "";
        $booking_id = isset($_POST['booking_id']) ? global_test_input($_POST['booking_id']) : "";
        $booking_name = isset($_POST['booking_name']) ? global_test_input($_POST['booking_name']) : "";

        if(empty($call_name) || empty($booking_id)) {
            echo 0;
            exit();
        }

        global $db, $current_user;

        $sql = 'SELECT id
            FROM calls cal
            WHERE cal.name = "'.$call_name.'"
                AND (cal.booking_id IS NULL OR cal.booking_id = "")
                AND cal.deleted = 0 
        ';
        $call_id = $db->getOne($sql);

        if(!empty($call_id)) {
            $cal = new Call();
            $cal->retrieve($call_id);
            $cal->booking_id = $booking_id;

            $description = (is_null($cal->description) || empty($cal->description)) ? '' : trim(addslashes($cal->description));

            if(empty($description)){
                echo 2;
                exit();
            }
            $cal->save();

            $assigned_user_id = (is_null($cal->assigned_user_id) || empty($cal->assigned_user_id)) ? $current_user->id : $cal->assigned_user_id;

            $work                       = new EC_Working_Process();
            $work->id                   = '';
            $work->name 			    = $booking_name;
            $work->parent_type 		    = 'EC_Flight_Bookings';
            $work->parent_id 		    = $booking_id;
            $work->description 		    = $description;
            $work->called               = 1;
            $work->assigned_user_id     = $assigned_user_id;
            $work->save();

            if(empty($work->id)) {
                echo 0;
                exit();
            }

            $bean_note                      = new Note();
            $bean_note->id 			        = '';
            $bean_note->name                = $booking_name;
            $bean_note->parent_type         = 'EC_Flight_Bookings';
            $bean_note->parent_id           = $booking_id;
            $bean_note->description         = $description;
            $bean_note->booking_status      = '6';
            $bean_note->working_process_id  = $work->id;
            $bean_note->assigned_user_id    = $assigned_user_id;
            $bean_note->save();

            $sql_update = 'UPDATE ec_flight_bookings
                SET booking_status = "6", assigned_user_id = "'.$assigned_user_id.'"
                WHERE id = "'.$booking_id.'" AND deleted = 0';
            $db->query($sql_update);

            echo 1;
            exit();
        }

        echo 0;
        exit();
    } 
}

function get_call_info($phone) {
    if(is_null($phone) || empty($phone)) return '';

    global $db, $app_list_strings;
    $html = '<table id="table-voicecall" class="table-details__booking">
                <caption class="caption-voicecall" align="top">THÔNG TIN CUỘC GỌI GẦN ĐÂY</caption>
                <thead>
                    <th>STT</th>
                    <th>Cuộc gọi</th>
                    <th>Direction</th>
                    <th>Gọi từ</th>
                    <th>Gọi đến</th>
                    <th>Mô tả</th>
                </thead>
                <tbody>';
    
    $sql = "SELECT id, name, status, direction, call_from, call_to, description
            FROM calls
            WHERE (call_from = '" . trim($phone) . "' OR call_to = '" . trim($phone) . "') AND deleted = 0
            ORDER BY date_entered DESC
            LIMIT 10";

    $res = $db->query($sql);
    $i = 1;
    while($row = $db->fetchByAssoc($res)){
            // class direction
            if($row['direction'] == 'suddenly'){
                $direction_class = 'text-warning';
           } else if ($row['direction'] == 'inbound'){
                $direction_class = 'text-success';
           } else if ($row['direction'] == 'missed'){
                $direction_class = 'text-danger';
           } else if ($row['direction'] == 'outbound'){
                $direction_class = 'text-primary';     

           } else if ($row['direction'] == 'spam'){
                $direction_class = 'text-spam';
           } else {
                $direction_class = 'text-normal';
           }
            
            $html .= '
                <tr>
                    <td align="center" class="fw-bold">'.$i.'</td>
                    <td align="left" class="fw-bold name_call"><a target="_blank" href="index.php?module=Calls&return_module=Calls&action=DetailView&record='.$row['id'].'">'.$row['name'].'</a></td>
                    <td align="left" class="direction_call fw-bold '.$direction_class.'">'.$app_list_strings['calls_direction_list'][$row['direction']].'</td>
                    <td align="center" class="from_call">'.$row['call_from'].'</td>
                    <td align="center" class="to_call">'.$row['call_to'].'</td>
                    <td align="left" class="description_call" style="max-width: 250px;">'.$row['description'].'</td>
                </tr>';
            $i++;
    }
    $html .= '</tbody></table>';

    return $html;
}

function get_booking_info($phone) {
    if(is_null($phone) || empty($phone)) return '';

    global $db, $app_list_strings;
    $html = '<table id="table-voicebooking" class="table-details__booking">
                <caption class="caption-voicebooking" align="top">THÔNG TIN BOOKING GẦN ĐÂY</caption>
                <thead>
                    <th>STT</th>
                    <th>Booking</th>
                    <th>Trạng thái</th>
                    <th>Hành trình</th>
                    <th>Ngày đặt</th>
                </thead>
                <tbody>';
    
    $sql = "SELECT id, name, info_data, count(*) as is_exsist
        FROM ec_customer c
        WHERE c.phone = '" . $phone . "' AND c.deleted = 0";

    $res = $db->query($sql);
    while($row = $db->fetchByAssoc($res)){
        if($row['is_exsist'] != 0){
            $data_bk = array_reverse(json_decode(html_entity_decode($row['info_data']), true));
            $i = 1;
            foreach($data_bk as $id => $v) {
                if($i <= 10){
                    $html .= '
                    <tr>
                        <td align="center" class="fw-bold">'.$i.'</td>
                        <td><a href="index.php?module=EC_Flight_Bookings&action=DetailView&record='.$id.'" target="_blank">'.$v['booking_number'].'</a></td>
                        <td class="fw-bold text-center" style="color:'.$app_list_strings['booking_status_color_list'][$v['booking_status']].';" align="center">'.$app_list_strings['booking_status_list'][$v['booking_status']].'</td>
                        <td class="text-center">'.$v['journey'].'</td>
                        <td>'.($v['booking_date'] != '' ? date('d-m-Y H:i', strtotime('+7 hours', strtotime($v['booking_date']))) : '').'</td>
                    </tr>';
                    $i++;
                }
            }
        }
        else {
            $html .= '<tr><td align="center" colspan="5" class="text-start fw-bold">Liên hệ chưa đặt booking!</td></tr>';
        }
    }
    $html .= '</tbody></table>';

    return $html;
}

function get_booking_refund($phone){
    if(is_null($phone) || empty($phone)) return '';
    global $db, $app_list_strings;
    $html = '<table id="table-voicehv__booking" class="table-details__booking">
                <caption class="caption-voicehv__booking" align="top">THÔNG TIN HOÀN VÉ GẦN ĐÂY</caption>
                <thead>
                    <th>STT</th>
                    <th>Phiếu hoàn</th>
                    <th>Trạng thái</th>
                    <th>Booking</th>
                    <th>Diễn giải</th>
                    <th>Hoàn tiền khách</th>
                    <th>Đã chi tiền</th>
                </thead>
                <tbody>';

    $sql_hv = 'SELECT hv.id, hv.name, hv.tinhtrang, hv.description, bk.name as booking, hv.booking_id as booking_id, hv.tongtienkhach,
                        (
                            SELECT SUM(IFNULL(p.amount, 0)) 
                            FROM ec_payment_voucher p
                            WHERE p.hoanve_id = hv.id 
                            AND p.pv_status = "3"
                            AND p.deleted = 0
                        ) as refunded
                FROM ec_hoanve hv
                LEFT JOIN ec_flight_bookings bk ON bk.id = hv.booking_id AND bk.deleted = 0
                WHERE bk.phone = "'.$phone.'" AND hv.deleted = 0
                ORDER BY hv.date_entered DESC
                LIMIT 5';

    $res = $db->query($sql_hv);
    $i = 1;
    while($row = $db->fetchByAssoc($res)){
        $html .= '<tr>
                    <td align="center" class="fw-bold">'.$i.'</td>
                    <td align="left" class="fw-bold hv_name"><a target="_blank" href="index.php?module=EC_HoanVe&return_module=EC_HoanVe&action=DetailView&record='.$row['id'].'">'.$row['name'].'</a></td>
                    <td align="center" class="fw-bold hv_status">'.$app_list_strings['tinhtranghoanve_list'][$row['tinhtrang']].'</td>
                    <td align="left" class="fw-bold bk_name"><a target="_blank" href="index.php?module=EC_Flight_Bookings&return_module=EC_Flight_Bookings&action=DetailView&record='.$row['booking_id'].'">'.$row['booking'].'</a></td>
                    <td align="center" class="fw-bold hv_description" style="max-width: 300px;">'.$row['description'].'</td>
                    <td align="center" class="fw-bold hv_htk">'.format_number($row['tongtienkhach']).'</td>
                    <td align="center" class="fw-bold hv_refunded">'.format_number($row['refunded']).'</td>
                </tr>';
        $i++;
    }

    $html .= '</tbody></table>';
    return $html;
}

// Use with zalp
function get_phone_by_alias($alias) {
    if(is_null($alias) || empty($alias)) return '';

    preg_match_all('!\d+!', $alias, $matches);
    if(isset($matches[0]) && !empty($matches[0])) {
        return $matches[0][count($matches[0])-1];
    }

    return '';
}