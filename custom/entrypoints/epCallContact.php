<?php
global $current_user, $db, $app_list_strings;

if ((string)$_SERVER["REQUEST_METHOD"] === "POST") {
    $type = isset($_POST['type']) ? $_POST['type'] : "";

    if ((string)$type === "get_contact") {
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


        $where = [];
        if (!empty($phone)) {
            $where[] = 'con.phone_mobile = ' . $db->quote($phone);
        }
        if (!empty($zalo_id)) {
            $where[] = 'con.zalo_id = ' . $db->quote($zalo_id);
        }

        if (!empty($where)) {
            $sql = '
                SELECT 
                    con.id,
                    con.last_name AS name,
                    IFNULL(con.phone_mobile, "") AS phone,
                    IFNULL(con.zalo_id, "") AS zalo_id,
                    IFNULL(e.email_address, "") AS email
                FROM contacts con
                    LEFT JOIN email_addr_bean_rel eb ON eb.bean_id = con.id 
                        AND eb.bean_module = "Contacts" AND eb.deleted = 0
                    LEFT JOIN email_addresses e ON e.id = eb.email_address_id
                WHERE (' . implode(' AND ', $where) . ') 
                    AND con.deleted = 0 LIMIT 1
            ';
    
            $res = $db->query($sql);
            while ($row = $db->fetchByAssoc($res)) {
                $data['contact_id'] = $row['id'];
                $data['name']       = !is_null($row['name']) ? $row['name'] : '';
                $data['phone']      = !is_null($row['phone']) ? $row['phone'] : $phone;
                $data['zalo_id']    = !is_null($row['zalo_id']) ? $row['zalo_id'] : $zalo_id;
                $data['email']      = !is_null($row['email']) ? $row['email'] : '';
            }
        }



        /**********  2. Get zalo info **********/
        if (!empty($data['zalo_id'])) {
            require_once('modules/EC_Zalo/Zalo.php');
            $zalo = new Zalo();

            // Get user info
            $json_user_info = $zalo->get_user($data['zalo_id']);
            $user_info      = json_decode($json_user_info, true);

            if ($user_info && $user_info['error'] == 0) {
                $zalo_phone = (isset($user_info['data']['shared_info']) && isset($user_info['data']['shared_info']['phone'])) ? $user_info['data']['shared_info']['phone'] : '';
                // Get phone from user_alias
                if (empty($zalo_phone)) $zalo_phone = $zalo->get_phone_by_alias($user_info['data']['user_alias']);

                if (empty($data['phone'])) $data['phone'] = $zalo->unformat_zalo_phone($zalo_phone);
                if (empty($data['name'])) $data['name'] = $user_info['data']['display_name'];
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
    } elseif ((string)$type === "get_contact_zalo") { // Dùng cho gọi ra đường Zalo
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
        if (strlen($number) < 15) {
            $sql_1 = '
                SELECT con.id,
                    con.last_name AS name,
                    IFNULL(con.phone_mobile, "") AS phone_mobile,
                    IFNULL(con.zalo_id, "") AS zalo_id,
                    IFNULL(e.email_address, "") AS email
                FROM contacts con
                    LEFT JOIN email_addr_bean_rel eb ON eb.bean_id = con.id AND eb.bean_module = "Contacts" AND eb.deleted = 0
                    LEFT JOIN email_addresses e ON e.id = eb.email_address_id
                WHERE con.phone_mobile = "' . $number . '" AND con.deleted = 0
                ORDER BY date_entered
                LIMIT 1
            ';

            $res_1 = $db->query($sql_1);
            while ($row = $db->fetchByAssoc($res_1)) {
                if (is_null($row['zalo_id']) || empty($row['zalo_id'])) continue;

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
                WHERE con.zalo_id = "' . $number . '" AND con.deleted = 0
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
        if (!empty($zalo_id)) {
            require_once('modules/EC_Zalo/Zalo.php');
            $zalo = new Zalo();

            // Get user info
            $json_user_info = $zalo->get_user($zalo_id);
            $user_info = json_decode($json_user_info, true);
            if ($user_info['error'] != 0) {
                echo json_encode([
                    'error' => 1,
                    'message' => isset($user_info['message']) ? $user_info['message'] : 'Thông tin Zalo không hợp lệ'
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $zalo_phone = (isset($user_info['data']['shared_info']) && isset($user_info['data']['shared_info']['phone'])) ? $user_info['data']['shared_info']['phone'] : '';
            // Get phone from user_alias
            if (empty($zalo_phone)) $zalo_phone = $zalo->get_phone_by_alias($user_info['data']['user_alias']);

            if (empty($data['phone'])) $data['phone'] = $zalo->unformat_zalo_phone($zalo_phone);
            if (empty($data['name'])) $data['name'] = $user_info['data']['display_name'];
            $data['avatar'] = $user_info['data']['avatars']['240'];
            $data['last_interaction'] = isset($user_info['data']['user_last_interaction_date']) ? $user_info['data']['user_last_interaction_date'] : ''; // dd/mm/yyyy

            // Check interaction within 30 days
            if (empty($data['last_interaction']) || ((strtotime(date('d/m/Y')) - strtotime($data['last_interaction'])) / 86400 > 30)) {
                echo json_encode([
                    'error' => 1,
                    'message' => 'Không thể gọi đến Zalo này',
                    'data' => $data,
                    'mes' => $messages
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }
        } else {
            echo json_encode([
                'error' => 1,
                'message' => 'Không có thông tin Zalo'
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        /**********  3. Get booking info of contact via phone **********/
        if (!empty($data['phone'])) {
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
    } elseif ((string)$type === "update_call") {
        require_once('modules/EC_Zalo/Zalo.php');
        $objZalo = new Zalo();

        $call_id    = isset($_POST['call_id']) ? global_test_input($_POST['call_id']) : "";
        $contact_id = isset($_POST['contact_id']) ? global_test_input($_POST['contact_id']) : "";
        $phone      = isset($_POST['phone']) ? global_test_input(str_replace(" ", "", $_POST['phone'])) : "";
        $zalo_id    = isset($_POST['zalo_id']) ? global_test_input($_POST['zalo_id']) : "";
        $name       = isset($_POST['name']) ? global_test_input($_POST['name']) : "";
        $email      = isset($_POST['email']) ? global_test_input($_POST['email']) : "";
        $note       = isset($_POST['note']) ? addslashes($_POST['note']) : "";
        $call_reason = isset($_POST['call_reason']) ? global_test_input($_POST['call_reason']) : "";

        $booking_id     = isset($_POST['booking_id']) ? global_test_input($_POST['booking_id']) : "";
        $type_call      = isset($_POST['type_call_booking']) && !empty($_POST['type_call_booking']) ? global_test_input($_POST['type_call_booking']) : "called";
        $journey_id     = isset($_POST['journey_id']) ? global_test_input($_POST['journey_id']) : "";

        $is_success     = isset($_POST['is_success']) ? $_POST['is_success'] : "";
        $call_status    = ((string)$is_success === 'true') ? 'done' : 'new';

        // Validate
        if (empty($call_id) || empty($note)) {
            echo 400;
            exit();
        }

        /**********  1. Handle Call & Contact  **********/
        if (empty($contact_id)) {
            $where = '';

            $where_clauses = [];
            if (!empty($phone)) {
                $phone_escaped = $db->quote($phone);
                $where_clauses[] = 'phone_mobile = ' . $phone_escaped;
            }
            if (!empty($zalo_id)) {
                $zalo_escaped = $db->quote($zalo_id);
                $where_clauses[] = 'zalo_id = ' . $zalo_escaped;
            }

            if (!empty($where_clauses)) {
                $sql = 'SELECT id, phone_mobile, zalo_id FROM contacts 
                        WHERE (' . implode(' OR ', $where_clauses) . ') 
                        AND deleted = 0';
                $result = $db->query($sql);
        
                $found_ids = [];
                while ($row = $db->fetchByAssoc($result)) {
                    $found_ids[] = $row['id'];
                }
        
                $contact_id = $found_ids[0]; 
                if (count($found_ids) > 1) {
                    $content = "**Có nhiều hơn 1 liên hệ trùng thông tin**";
                    $content .= "\nSố điện thoại: **$phone**";
                    $content .= "\nZaloID: **$zalo_id**";
                    $content .= "\nCall_ID: **$call_id**";
                    $metadata = [
                        "priority" => [
                            "priority" => "important",
                        ]
                    ];
                    Mattermost::sendMessage($sugar_config['mattermost']['channel_id_zalo_oa'] ?? '', $content, [], $metadata);
                }
            }
        }

        $con = new Contact();
        if ($contact_id && !empty($contact_id)) {
            $save = false;
            $con->retrieve($contact_id);

            if (empty($con->phone_mobile)) {
                $con->phone_mobile = $phone;
                $save = true;
            }
            if (empty($con->zalo_id)) {
                $con->zalo_id = $zalo_id;
                $save = true;
            }
            if (!empty($name)) {
                $con->last_name = $name;
                $save = true;
            }
            if (!empty($email)) {
                $con->email1 = $email;
                $save = true;
            }
            if ($save === true){
                $con->description = "Cập nhật thông tin Liên hệ từ cuộc gọi có call_ID: " . $call_id;
                $con->save();
            } 
        } else {
            if(!empty($call_id)){
                $con->phone_mobile = $phone;
                $con->zalo_id = $zalo_id;
                $con->last_name = $name;
                $con->email1 = $email;
                $con->description = "Liên hệ tạo từ cuộc gọi có call_ID: " . $call_id;
                $con->assigned_user_id = $current_user->id;
                $con->save();
            }
        }

        // CHECK CALL_ID ĐÃ CÓ TRONG DB HAY CHƯA
        $sql_exist_callid = 'SELECT IF(COUNT(id) > 0, 1, 0)
                    FROM calls 
                    WHERE call_id = "' . $call_id . '" AND deleted = 0';
        $is_exist_callid = $db->getOne($sql_exist_callid);

        if ($is_exist_callid) {
            $sql_update_call = 'UPDATE calls
                                SET parent_type = "Contacts", parent_id = "' . $con->id . '", description = "' . $note . '", booking_id = "' . $booking_id . '",
                                    created_by = "' . $current_user->id . '",
                                    modified_user_id = "' . $current_user->id . '",
                                    assigned_user_id = "' . $current_user->id . '",
                                    call_reason = "' . $call_reason . '",
                                    journey_id = "' . $journey_id . '",
                                    type_call_sources = "' . $type_call . '",
                                    status = "' . $call_status . '"
                                WHERE call_id = "' . $call_id . '" AND deleted = 0';
            $result_update_call = $db->query($sql_update_call);

            if ($result_update_call) {
                $log_save_calls = '[' . $current_user->user_name . '][' . date('Y-m-d H:i:s', strtotime('+7 hour')) . '][success]' . $sql_update_call;
                save_log_call($log_save_calls);
            } else {
                $log_save_calls = '[' . $current_user->user_name . '][' . date('Y-m-d H:i:s', strtotime('+7 hour')) . '][Failed_db]' . $sql_update_call;
                save_log_call($log_save_calls);
                echo 401;
                exit;
            }
        } else {
            $log_save_calls = '[' . $current_user->user_name . '][' . date('Y-m-d H:i:s', strtotime('+7 hour')) . '][Failed_Callid]' . $sql_exist_callid;
            save_log_call($log_save_calls);

            echo 404;
            exit;
        }
        /**********  2. Handle Booking  **********/
        $sql_call = 'SELECT id, name, status, call_talk, description, direction FROM calls WHERE call_id = "' . $call_id . '" AND deleted = 0 LIMIT 1';
        $result = $db->query($sql_call);

        if (!empty($booking_id)) {
            $booking_name = isset($_POST['booking_name']) ? global_test_input($_POST['booking_name']) : "";
            if (!empty($type_call) && !empty($call_id)) {
                while ($call = $db->fetchByAssoc($result)) {
                    if ($call) {
                        $work                       = new EC_Working_Process();
                        $work->name                 = $booking_name;
                        $work->parent_type          = 'EC_Flight_Bookings';
                        $work->parent_id            = $booking_id;
                        $work->description          = $note . ' (' . $type_call . ' '.$call_status.' '.$call['call_talk'].')';
                        // Gọi đi
                        $work->$type_call           = ((string)$call_status === 'done' && !empty($note) && (int)$call['call_talk'] >= 20) ? 1 : 0;
                        $work->assigned_user_id     = $current_user->id;
                        $work->save();

                        if(empty($work->id)) {
                            // // SEND TELE WARNING SAVE KPI FAILED
                            // $messages = "- Domain: <b>" . $sugar_config['host_name'] . "</b>\n" .
                            // "- Call: <b>" . $call['name'] . " - " . $booking_name . "</b>\n" .
                            // "- User: <b>" . $current_user->user_name . "</b>\n" .
                            // "<pre>[WARNING]: SAVE KPI HAS BOOKING FAILED ".$call['description'].". Hội thoại: " . $call['call_talk'] . ".</pre>";
                            // $content = html_entity_decode($messages, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                            // sendTelegramWarningSystem(
                            //     json_encode(array(
                            //         'text' => $content,
                            //         'parse_mode' => 'HTML',
                            //         'reply_markup' => array(
                            //             'inline_keyboard' => array(
                            //                 array(
                            //                     array(
                            //                         'text' => 'Redirect url',
                            //                         'url' => 'https://' . $sugar_config['host_name'] . '/index.php?module=Calls&action=DetailView&record=' . $call['id'],
                            //                     ),
                            //                 ),
                            //             ),
                            //         ),
                            //     ), JSON_UNESCAPED_UNICODE),
                            // );

                            $link = Mattermost::markdownLink("https://" . $sugar_config['host_name'] . "/index.php?module=Calls&action=DetailView&record=" . $call['id'], "Redirect url");
                            $message = Mattermost::$line_separation;
                            $message .= Mattermost::markdownHeading("[WARNING] Save KPI have booking failed");
                            $message .= "\n- Domain: **" . $sugar_config['host_name'] . "**";
                            $message .= "\n- Call: **" . $call['name'] . " - " . $booking_name . "**";
                            $message .= "\n- User: **$current_user->user_name**";
                            $message .= "\n- Description: **" . $call['description'] . "**";
                            $message .= "\n- Hội thoại: **" . $call['call_talk'] . "**";
                            $message .= "\n$link";
                            Mattermost::sendMessage($sugar_config['mattermost']['channel_id_logs'] ?? '', $message, );
                        }

                        $bean_note                      = new Note();
                        $bean_note->name                = $booking_name;
                        $bean_note->parent_type         = 'EC_Flight_Bookings';
                        $bean_note->parent_id           = $booking_id;
                        $bean_note->description         = $note . ' (' . $type_call . ')';;
                        $bean_note->booking_status      = ((string)$type_call === 'called' ? '6' : '');
                        $bean_note->working_process_id  = $work->id;
                        $bean_note->assigned_user_id    = $current_user->id;
                        $bean_note->save();

                        // Update status and assigned
                        if ((string)$type_call === 'called') {
                            $sql_update = 'UPDATE ec_flight_bookings
                                    SET booking_status = "6", assigned_user_id = "' . $current_user->id . '"
                                    WHERE id = "' . $booking_id . '" AND deleted = 0';
                            $db->query($sql_update);
                        }

                        // Update is_remind trong bảng ec_booking_itineraries = true nếu đã ghi nhận KPI
                        if (!empty($journey_id) && $call['call_talk'] > 0) {
                            $update_remind = "UPDATE ec_booking_itineraries 
                                SET is_remind = 1
                                WHERE id = '" . trim($journey_id) . "'
                                AND deleted = 0";
                            $result_remind = $db->query($update_remind);
                            if (!$result_remind) {
                                $GLOBALS['log']->fatal('updated remind thất bại: ' . $update_remind);
                            }
                        } else {
                            $GLOBALS['log']->fatal('updated remind thất bại: ' . json_encode($_POST));
                        }
                    }
                }
            }
        } else {
            while ($call = $db->fetchByAssoc($result)) {
                if (!empty($note) &&  strtolower((string)$call['status']) === 'done' &&  (
                    ((string)$call['direction'] === 'inbound' && (int)$call['call_talk'] > 0) || ((string)$call['direction'] === 'outbound' && (int)$call['call_talk'] >= 20))
                ) {
                    $work = new EC_Working_Process();
                    $work->name = $call['name'];
                    $work->parent_type = 'Calls';
                    $work->parent_id = $call['id'];
                    $work->description = $note . ' ('.$type_call.') Cập nhật cuộc gọi';
                    $work->$type_call = 1; 
                    $work->assigned_user_id = $current_user->id;
                    $work->save();

                    if(empty($work->id)) {
                        // // SEND TELE WARNING SAVE KPI FAILED
                        // $messages = "- Domain: <b>" . $sugar_config['host_name'] . "</b>\n" .
                        // "- Call: <b>" . $call['name'] . "</b>\n" .
                        // "- User: <b>" . $current_user->user_name . "</b>\n" .
                        // "<pre>[WARNING]: SAVE KPI FAILED ".$call['description'].". Hội thoại: " . $call['call_talk'] . ".</pre>";
                        // $content = html_entity_decode($messages, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        // sendTelegramWarningSystem(
                        //     json_encode(array(
                        //         'text' => $content,
                        //         'parse_mode' => 'HTML',
                        //         'reply_markup' => array(
                        //             'inline_keyboard' => array(
                        //                 array(
                        //                     array(
                        //                         'text' => 'Redirect url',
                        //                         'url' => 'https://' . $sugar_config['host_name'] . '/index.php?module=Calls&action=DetailView&record=' . $call['id'],
                        //                     ),
                        //                 ),
                        //             ),
                        //         ),
                        //     ), JSON_UNESCAPED_UNICODE),
                        // );

                        $link = Mattermost::markdownLink("https://" . $sugar_config['host_name'] . "/index.php?module=Calls&action=DetailView&record=" . $call['id'], "Redirect url");
                        $message = Mattermost::$line_separation;
                        $message .= Mattermost::markdownHeading("[WARNING] Save KPI failed");
                        $message .= "\n- Domain: **" . $sugar_config['host_name'] . "**";
                        $message .= "\n- Call: **" . $call['name'] . "**";
                        $message .= "\n- User: **" . $current_user->user_name . "**";
                        $message .= "\n- Description: **" . $call['description'] . "**";
                        $message .= "\n- Hội thoại: **" . $call['call_talk'] . "**";
                        $message .= "\n$link";
                        Mattermost::sendMessage($sugar_config['mattermost']['channel_id_logs'] ?? '', $message);
                    }
                } 
            }
        }

        /**********  3. Handle Zalo  **********/
        if (!empty($zalo_id) && empty($phone)) {
            $json = $objZalo->get_user($zalo_id);
            $arr = json_decode($json, true);

            if (isset($arr['error']) && (int)$arr['error'] === 0) {
                if (
                    !isset($arr['data']['shared_info']) ||
                    !isset($arr['data']['shared_info']['phone']) ||
                    empty($arr['data']['shared_info']['phone'])
                ) {
                    // Get phone from user_alias
                    if (empty($objZalo->get_phone_by_alias($arr['data']['user_alias']))) {
                        $data['element'] = $objZalo->get_template('request_user_info');
                        $objZalo->send_consultation('request_user_info', $zalo_id, $data);
                        Mattermost::sendMessage($sugar_config['mattermost']['channel_id_zalo_oa'] ?? '', "Gửi yêu cầu thông tin đến Zalo **$zalo_id**");
                    }
                }
            }
        }

        echo 200;
        exit();
    } elseif ((string)$type === "check_missed_call") {
        $call_id = isset($_POST['call_id']) ? global_test_input($_POST['call_id']) : "";

        if (empty($call_id)) {
            echo 0;
            exit();
        }

        global $db;
        $sql = 'SELECT call_id
                    FROM calls
                    WHERE call_id = "' . $call_id . '" AND direction = "missed" AND deleted = 0
                ';
        $id = $db->getOne($sql);

        if ($id && !empty($id)) echo 1;
        else echo 0;
        exit();
    } elseif ((string)$type === "map_call_booking") {
        $call_name      = isset($_POST['call_name']) ? global_test_input($_POST['call_name']) : "";
        $booking_id     = isset($_POST['booking_id']) ? global_test_input($_POST['booking_id']) : "";
        $booking_name   = isset($_POST['booking_name']) ? global_test_input($_POST['booking_name']) : "";

        if (empty($call_name) || empty($booking_id)) {
            echo 0;
            exit();
        }

        global $db, $current_user;

        $sql = 'SELECT id
                FROM calls cal
                WHERE cal.name = "' . trim($call_name) . '"
                    AND (cal.booking_id IS NULL OR cal.booking_id = "")
                    AND cal.deleted = 0';
        $call_id = $db->getOne($sql);

        if (!empty($call_id)) {
            $cal = new Call();
            $cal->retrieve($call_id);
            $cal->booking_id = $booking_id;

            $description = (is_null($cal->description) || empty($cal->description)) ? '' : trim(addslashes($cal->description));

            if (empty($description)) {
                echo 2;
                exit();
            }
            $cal->save();

            $assigned_user_id = (is_null($cal->assigned_user_id) || empty($cal->assigned_user_id)) ? $current_user->id : $cal->assigned_user_id;
            $work                       = new EC_Working_Process();
            $work->id                   = '';
            $work->name                 = $booking_name;
            $work->parent_type          = 'EC_Flight_Bookings';
            $work->parent_id            = $booking_id;
            $work->description          = $description . ' (map_call_booking)';
            $work->called               = 0;
            $work->assigned_user_id     = $assigned_user_id;
            $work->save();

            if (empty($work->id)) {
                echo 0;
                exit();
            }

            $bean_note                      = new Note();
            $bean_note->id                  = '';
            $bean_note->name                = $booking_name;
            $bean_note->parent_type         = 'EC_Flight_Bookings';
            $bean_note->parent_id           = $booking_id;
            $bean_note->description         = $description;
            $bean_note->booking_status      = '6';
            $bean_note->working_process_id  = $work->id;
            $bean_note->assigned_user_id    = $assigned_user_id;
            $bean_note->save();

            $sql_update = 'UPDATE ec_flight_bookings
                    SET booking_status = "6", assigned_user_id = "' . $assigned_user_id . '"
                    WHERE id = "' . $booking_id . '" AND deleted = 0';
            $db->query($sql_update);

            echo 1;
            exit();
        }

        echo 0;
        exit();
    } else if ((string)$type === 'save_log_call') {
        $log_call = isset($_POST['log']) ? $_POST['log'] : '';
        save_log_call($log_call);
        exit;
    } else if ((string)$type === 'get_history_activity_contacts'){
        $phone = isset($_POST['phone']) ? global_test_input(str_replace(" ", "", $_POST['phone'])) : "";
        $start_date = date('Y-m-d H:i:s', strtotime('-1 year +7 hours'));
        $end_date   = date('Y-m-d H:i:s', strtotime('+7 hours'));

        if (empty($phone)) {
            echo 0;
            exit();
        }
    
        $sql = "
                SELECT 
                    c.id AS interaction_id,
                    c.name AS interaction_item,
                    c.call_from as call_from,
                    c.call_to as call_to,
                    c.date_entered AS interaction_date,
                    'call' AS interaction_type,
                    c.direction AS interaction_status,
                    c.description AS interaction_detail,
                    c.assigned_user_id
                FROM calls c
                WHERE (c.call_from = '$phone' OR c.call_to = '$phone')
                AND c.date_entered BETWEEN '$start_date' AND '$end_date'
                AND c.deleted = 0

                UNION ALL
                SELECT 
                    b.id AS interaction_id,
                    b.name AS interaction_item,
                    b.phone as call_from,
                    b.phone as call_to, 
                    b.date_entered AS interaction_date,
                    'booking' AS interaction_type,
                    b.booking_status AS interaction_status,
                    b.description AS interaction_detail,
                    b.assigned_user_id
                FROM ec_flight_bookings b
                WHERE b.phone = '$phone'
                AND b.date_entered BETWEEN '$start_date' AND '$end_date'
                AND b.deleted = 0

                UNION ALL
                SELECT 
                    r.id AS interaction_id,
                    r.name AS interaction_item,
                    b.phone AS call_from,
                    b.phone AS call_to,
                    r.date_entered AS interaction_date,
                    'refund' AS interaction_type,
                    r.tinhtrang AS interaction_status,
                    r.description AS interaction_detail,
                    r.assigned_user_id
                FROM ec_hoanve r
                JOIN ec_flight_bookings b ON r.booking_id = b.id
                WHERE b.phone = '$phone'
                AND r.date_entered BETWEEN '$start_date' AND '$end_date'
                AND r.deleted = 0

                UNION ALL
                SELECT 
                    rv.id AS interaction_id,
                    rv.name AS interaction_item,
                    b.phone AS call_from,
                    b.phone AS call_to,
                    rv.date_entered AS interaction_date,
                    'receipt_voucher' AS interaction_type,
                    rv.rv_status AS interaction_status,
                    rv.description AS interaction_detail,
                    rv.assigned_user_id
                FROM ec_receipt_voucher rv
                JOIN ec_flight_bookings b ON rv.booking_id = b.id
                WHERE b.phone = '$phone'
                AND rv.date_entered BETWEEN '$start_date' AND '$end_date'
                AND rv.deleted = 0
                    
                UNION ALL
                SELECT 
                    pv.id AS interaction_id,
                    pv.name AS interaction_item,
                    b.phone AS call_from,
                    b.phone AS call_to,
                    pv.date_entered AS interaction_date,
                    'payment_voucher' AS interaction_type,
                    pv.pv_status AS interaction_status,
                    pv.description AS interaction_detail,
                    pv.assigned_user_id
                FROM ec_payment_voucher pv
                JOIN ec_flight_bookings b ON pv.booking_id = b.id
                WHERE b.phone = '$phone'
                AND pv.date_entered BETWEEN '$start_date' AND '$end_date'
                AND pv.deleted = 0

                ORDER BY interaction_date DESC;
        ";
        $res = $db->query($sql);
        $count_actibity = $db->getRowCount($res);
        $user_list = get_user_array(true, '', '', true);

        if($count_actibity > 0){
            $html = '<div class="row flex-start">
                        <div class="col-md-12">
                            <div class="main-card mb-3 card">
                                <div class="card-body p-0">
                                    <div class="vertical-timeline vertical-timeline--animate vertical-timeline--one-column">';
            while ($row = $db->fetchByAssoc($res)) {
                
                switch ($row['interaction_type']) {
                    case 'call':
                        $interaction_type = 'primary';
                        $interaction_link = '<a class="fw-semibold text-decoration-underline text-'.$interaction_type.'" target="_blank" href="index.php?module=Calls&return_module=Calls&action=DetailView&record=' . $row['interaction_id'] . '">' . $row['interaction_item'] . '</a><span> ('.$GLOBALS['app_list_strings']['calls_direction_list'][$row['interaction_status']].')</span>';
                        break;
                    case 'booking':
                        $interaction_type = 'success';
                        $interaction_link = '<a class="fw-semibold text-decoration-underline text-'.$interaction_type.'" target="_blank" href="index.php?module=EC_Flight_Bookings&return_module=EC_Flight_Bookings&action=DetailView&record=' . $row['interaction_id'] . '">' . $row['interaction_item'] . '</a><span> ('.$GLOBALS['app_list_strings']['booking_status_list'][$row['interaction_status']].')</span>';
                        break;
                    case 'refund':
                        $interaction_type = 'warning';
                        $interaction_link = '<a class="fw-semibold text-decoration-underline text-'.$interaction_type.'" target="_blank" href="index.php?module=EC_Hoanve&return_module=EC_Hoanve&action=DetailView&record=' . $row['interaction_id'] . '">' . $row['interaction_item'] . '</a><span> ('.$GLOBALS['app_list_strings']['tinhtranghoanve_list'][$row['interaction_status']].')</span>';
                        break;
                    case 'receipt_voucher':
                        $interaction_type = 'info';
                        $interaction_link = '<a class="fw-semibold text-decoration-underline text-'.$interaction_type.'" target="_blank" href="index.php?module=EC_Receipt_Voucher&return_module=EC_Receipt_Voucher&action=DetailView&record=' . $row['interaction_id'] . '">' . $row['interaction_item'] . '</a><span> ('.$GLOBALS['app_list_strings']['receipt_voucher_status_list'][$row['interaction_status']].')</span>';
                        break;
                    case 'payment_voucher':
                        $interaction_type = 'danger';
                        $interaction_link = '<a class="fw-semibold text-decoration-underline text-'.$interaction_type.'" target="_blank" href="index.php?module=EC_Payment_Voucher&return_module=EC_Payment_Voucher&action=DetailView&record=' . $row['interaction_id'] . '">' . $row['interaction_item'] . '</a><span> ('.$GLOBALS['app_list_strings']['payment_voucher_status_list'][$row['interaction_status']].')</span>';
                        break;
                    default:
                        $interaction_link = $row['interaction_item'];
                        $interaction_type = 'dark';
                }

                $html .= ' 
                        <div class="vertical-timeline-item vertical-timeline-element">
                            <div class="flex-start gap-3">
                                <span class="vertical-timeline-element-date w-25 text-secondary fw-semibold">'.date('H:i:s d-m-Y', strtotime('+7 hours', strtotime($row['interaction_date']))).'</span>
                                <span class="vertical-timeline-element-icon bounce-in text-center">
                                    <span class="badge badge-dot badge-dot-xl text-bg-'.$interaction_type.'"> </span>
                                </span>
                                <div class="vertical-timeline-element-content bounce-in flex-fill">
                                    <p class="mb-2">
                                        '.$interaction_link.' 
                                        <span class="d-block">'.$row['interaction_detail'].'</span>
                                    </p>
                                    <p>Nhân viên: <span class="fw-semibold text-dark">' . $user_list[$row['assigned_user_id']] . '</span></p>
                                </div>
                            </div>
                        </div>
                    ';
            }

            $html .= '</div>
                        </div>
                    </div>        
                </div> 
            </div>';
            echo $html;
        } else {
            echo 'Không có hoạt động!';
        }
        exit;
    } else if ((string)$type === 'get_history_activity_cskh'){
        $phone = isset($_POST['phone']) ? global_test_input(str_replace(" ", "", $_POST['phone'])) : "";
        $start_date = date('Y-m-d H:i:s', strtotime('-1 year +7 hours'));
        $end_date   = date('Y-m-d H:i:s', strtotime('+7 hours'));

        if (empty($phone)) {
            echo 0;
            exit();
        }
    
        $sql = "
                SELECT 
                    c.id,
                    c.name,
                    c.description,
                    c.assigned_user_id,
                    c.date_start,
                    c.date_end,
                    c.status,
                    c.direction,
                    c.call_from,
                    c.call_to,
                    c.record_file,
                    -- c.hangup_cause,
                    c.log
                FROM calls c
                WHERE (c.call_from = '$phone' OR c.call_to = '$phone')
                AND c.date_entered BETWEEN '$start_date' AND '$end_date'
                AND c.deleted = 0
                ORDER BY c.date_entered DESC;
        ";

        $res        = $db->query($sql);
        $count_calls    = $db->getRowCount($res);
        $user_list      = get_user_array(true, '', '', true);
        $i = 1;
        $successful_calls = 0;
        $failed_calls = 0;
        $total_duration = 0;
        $total_wait = 0;
        $total_talk = 0;

        if($count_calls > 0){
            $html = '<div class="list-calls-cskh">
                        <table class="tbl-check-cskh-calls table-details__booking">
                            <thead>
                                <tr>
                                    <th width="5%" class="text-center hide-mobile">#</th>
                                    <th width="10%">Mã cuộc gọi</th>
                                    <th width="8%">Trạng thái</th>
                                    <th width="8%">Gọi từ</th>
                                    <th width="8%">Gọi đến</th>
                                    <th width="8%">Loại</th>
                                    <th width="10%">Thời gian gọi</th>
                                    <th width="25%" align="center">Ghi chú</th>
                                    <th>Nhân viên xử lý</th>
                                </tr>
                            </thead>';
            while ($row = $db->fetchByAssoc($res)) {
                $status_class = 'text-normal';
                if ((string)$row['status'] === 'processing') {
                    $status_class = 'text-warning';
               } else if ((string)$row['status'] === 'done') {
                    $status_class = 'text-success';
               }

               // class direction
               $direction_class = 'text-normal';
               if ((string)$row['direction'] === 'suddenly') {
                    $direction_class = 'text-warning';
                } else if ((string)$row['direction'] === 'inbound') {
                    $direction_class = 'text-success';
                } else if ((string)$row['direction'] === 'missed') {
                    $direction_class = 'text-danger';
                } else if ((string)$row['direction'] === 'outbound') {
                    $direction_class = 'text-primary';
                } else if ((string)$row['direction'] === 'spam') {
                    $direction_class = 'text-spam';
                } 

                // Xử lý log
                $log = json_decode(html_entity_decode($row['log']), true);
                if(isset($log['call_talk']) && $log['call_talk'] > 0){
                    $successful_calls++;
                    $total_talk += $log['call_talk'];
                } else {
                    $failed_calls++;
                }

                if(isset($log['call_duration'])){
                    $total_duration += $log['call_duration'];
                }
                if(isset($log['call_wait'])){
                    $total_wait += $log['call_wait'];
                }

                $html .= '<tr>
							<td class="hide-mobile fw-bold text-center">' . $i . '</td>
							<td class="text-nowrap"><a href="index.php?module=Calls&action=DetailView&record=' . $row['id'] . '" target="_blank">' . $row['name'] . '</a></td>
                            <td align="center" class="' . $status_class . '"><strong>' . $app_list_strings['call_status_dom'][$row['status']] . '</strong></td>
							<td class="fw-bold text-center">' . $row['call_from'] . '</td>
							<td class="fw-bold text-center">' . $row['call_to'] . '</td>
                            <td align="center" class="' . $direction_class . '"><strong>' . $app_list_strings['calls_direction_list'][$row['direction']] . '</strong></td>
                            <td align="center">' . $row['date_start'] . '</td>
                            <td align="left">' . $row['description'] . '</td>
                            <td align="center">' . $user_list[$row['assigned_user_id']] . '</td>
						</tr>';
				$i++;
            }

            // SUMMARY
            $total_calls = ($i - 1);
            $html_call_summary = '
            <div class="d-flex gap-2 mb-3 flex-nowrap">
                <div class="flex-fill lh-base">
                    <p>📞 <strong>Tổng số cuộc gọi:</strong><span class="fw-bold">' . $total_calls . '</span></p>
                    <p>✅ <strong>Cuộc gọi thành công:</strong> <span class="text-success fw-bold">' . $successful_calls . '</span> (Tỷ lệ: <span class="fw-bold">' . round(($successful_calls / $total_calls * 100), 2) . '%</span>)</p>
                    <p>📵 <strong>Cuộc gọi thất bại:</strong> <span class="text-danger fw-bold">' . $failed_calls . '</span> (Tỷ lệ: <span class="fw-bold">' . round(($failed_calls / $total_calls * 100), 2) . '%</span>)</p>
                </div>

                <div class="flex-fill lh-base">
                    <p>⏱️ <strong>Tổng thời gian:</strong> ' . global_secondsToTimeFormat($total_duration) . ' (Thời gian TB: ' . round($total_duration/$total_calls) . ')</p>
                    <p>⏱️ <strong>Tổng thời gian đợi:</strong> ' . global_secondsToTimeFormat($total_wait) . ' (Thời gian TB: ' . round($total_wait/$total_calls) . ')</p>
                    <p>⏱️ <strong>Tổng thời gian thoại:</strong> ' . global_secondsToTimeFormat($total_talk) . ' (Thời gian TB: ' . round($total_talk/$total_calls) . ')</p>
                </div>
            </div>';

            echo $html_call_summary.$html;
        } else {
            echo 'Chưa có cuộc gọi CSKH nào!';
        }
        exit();
    } else if ((string)$type === 'autocall') {
        $phone = isset($_POST['phone']) ? global_test_input(str_replace(" ", "", $_POST['phone'])) : "";
        if(!empty($phone)){
            $phone_list = explode(",", $phone);
            echo send_callee_autocall($phone_list);
        }
        exit();
    } else if ((string)$type === 'get_infor_phone') {
        $phone = isset($_POST['phone']) ? global_test_input(str_replace(" ", "", $_POST['phone'])) : "";
        if(!empty($phone)){
            echo json_encode(getInfoCallSource($phone));
        }
        exit();
    }
}


function get_call_info($phone)
{
    if (is_null($phone) || empty($phone)) return '';

    global $db, $app_list_strings;
    $html = '';

    $sql = "SELECT id, name, status, direction, call_from, call_to, description
            FROM calls
            WHERE (call_from = '" . trim($phone) . "' OR call_to = '" . trim($phone) . "') AND deleted = 0
            ORDER BY date_entered DESC
            LIMIT 10";

    $res = $db->query($sql);

    if ($db->countRows($res) > 0) {
        $html .= '<table id="table-voicecall" class="table-details__booking">
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
                    
        $i = 1;
        while ($row = $db->fetchByAssoc($res)) {
            // class direction
            $direction_class = 'text-normal';
            if ((string)$row['direction'] === 'suddenly') {
                $direction_class = 'text-warning';
            } else if ((string)$row['direction'] === 'inbound') {
                $direction_class = 'text-success';
            } else if ((string)$row['direction'] === 'missed') {
                $direction_class = 'text-danger';
            } else if ((string)$row['direction'] === 'outbound') {
                $direction_class = 'text-primary';
            } else if ((string)$row['direction'] === 'spam') {
                $direction_class = 'text-spam';
            } 
    
            $html .= '
                    <tr>
                        <td align="center" class="fw-bold">' . $i . '</td>
                        <td align="left" class="fw-bold name_call text-nowrap"><a target="_blank" href="index.php?module=Calls&return_module=Calls&action=DetailView&record=' . $row['id'] . '">' . $row['name'] . '</a></td>
                        <td align="left" class="direction_call fw-bold ' . $direction_class . '">' . $app_list_strings['calls_direction_list'][$row['direction']] . '</td>
                        <td align="center" class="from_call">' . $row['call_from'] . '</td>
                        <td align="center" class="to_call">' . $row['call_to'] . '</td>
                        <td align="left" class="description_call" style="max-width: 250px;">' . $row['description'] . '</td>
                    </tr>';
            $i++;
        }
        $html .= '</tbody></table>';
    }

    return $html;
}

function get_booking_info($phone)
{
    if (is_null($phone) || empty($phone)) return '';

    global $db, $app_list_strings;

    $html = '';

    $sql = "SELECT id, name, info_data, count(*) as is_exsist
        FROM ec_customer c
        WHERE c.phone = '" . $phone . "' AND c.deleted = 0";

    $res = $db->query($sql);

    if ($db->countRows($res) > 0) {
        $html .= '<table id="table-voicebooking" class="table-details__booking">
                    <caption class="caption-voicebooking" align="top">THÔNG TIN BOOKING GẦN ĐÂY</caption>
                    <thead>
                        <th>STT</th>
                        <th>Booking</th>
                        <th>Trạng thái</th>
                        <th>Hành trình</th>
                        <th>Ngày đặt</th>
                        <th>Khách hàng</th>
                    </thead>
                    <tbody>';
                    
        while ($row = $db->fetchByAssoc($res)) {
            if ($row['is_exsist'] != 0) {
                $data_bk = array_reverse(json_decode(html_entity_decode($row['info_data']), true));
                $i = 1;
                foreach ($data_bk as $id => $v) {
                    if ($i <= 10) {
                        $html .= '
                        <tr>
                            <td align="center" class="fw-bold">' . $i . '</td>
                            <td><a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $id . '" target="_blank">' . $v['booking_number'] . '</a></td>
                            <td class="fw-bold text-center" style="color:' . $app_list_strings['booking_status_color_list'][$v['booking_status']] . ';" align="center">' . $app_list_strings['booking_status_list'][$v['booking_status']] . '</td>
                            <td class="text-center">' . $v['journey'] . '</td>
                            <td>' . ($v['booking_date'] != '' ? date('d-m-Y H:i', strtotime('+7 hours', strtotime($v['booking_date']))) : '') . '</td>
                            <td class="text-center">
                                <span>
                                    ' . $v['customer_name'] . '
                                </span>   
                            </td>
                        </tr>';
                        $i++;
                    }
                }
            } else {
                $html .= '<tr><td align="center" colspan="5" class="text-start fw-bold">Liên hệ chưa đặt booking!</td></tr>';
            }
        }
        $html .= '</tbody></table>';
    }

    return $html;
}

function get_booking_refund($phone)
{
    if (is_null($phone) || empty($phone)) return '';
    global $db, $app_list_strings;

    $html = '';

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
                WHERE bk.phone = "' . $phone . '" AND hv.deleted = 0
                ORDER BY hv.date_entered DESC
                LIMIT 5';

    $res = $db->query($sql_hv);

    if ($db->countRows($res) > 0) {
        $html .= '<table id="table-voicehv__booking" class="table-details__booking">
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
    
        $i = 1;
        while ($row = $db->fetchByAssoc($res)) {
            $html .= '<tr>
                        <td align="center" class="fw-bold">' . $i . '</td>
                        <td align="left" class="fw-bold hv_name"><a target="_blank" href="index.php?module=EC_HoanVe&return_module=EC_HoanVe&action=DetailView&record=' . $row['id'] . '">' . $row['name'] . '</a></td>
                        <td align="center" class="fw-bold hv_status">' . $app_list_strings['tinhtranghoanve_list'][$row['tinhtrang']] . '</td>
                        <td align="left" class="fw-bold bk_name"><a target="_blank" href="index.php?module=EC_Flight_Bookings&return_module=EC_Flight_Bookings&action=DetailView&record=' . $row['booking_id'] . '">' . $row['booking'] . '</a></td>
                        <td align="center" class="fw-bold hv_description" style="max-width: 300px;">' . $row['description'] . '</td>
                        <td align="center" class="fw-bold hv_htk">' . format_number($row['tongtienkhach']) . '</td>
                        <td align="center" class="fw-bold hv_refunded">' . format_number($row['refunded']) . '</td>
                    </tr>';
            $i++;
        }
    
        $html .= '</tbody></table>';
    }

    return $html;
}
