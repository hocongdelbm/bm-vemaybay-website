<?php

use custom\services\Notification\NotificationService;

if ((string)$_SERVER["REQUEST_METHOD"] === "POST") {
    $type = isset($_POST['type']) ? $_POST['type'] : "";

    if ((string)$type === "get_contact") { // Dùng khi gọi đến và gọi ra
        global $db;
        $phone = isset($_POST['phone']) ? global_test_input(str_replace(" ", "", $_POST['phone'])) : "";
        $zalo_id = isset($_POST['zalo_id']) ? global_test_input($_POST['zalo_id']) : "";

        // ƯU TIÊN HƯỚNG GỌI ZALO

        /**********  1. Get info from DB  **********/
        $data = [
            'contact_id'    => '',
            'name'          => '',
            'phone'         => $phone,
            'zalo_id'       => $zalo_id,
            'zalo_name'       => '',
            'email'         => '',
            'avatar'        => '',
            'info_booking'  => '',
            'last_interaction'  => '',
            'is_call_zalo'  => false,
            'is_uncomfortable'  => false,
            'is_ctv'  => false,
            'is_compare_price'  => false,
        ];

        $where = [];
        if (!empty($phone)) {
            $where[] = 'con.phone_mobile = ' . $db->quote($phone);
        }
        if (!empty($zalo_id)) {
            $where[] = 'zc.zalo_id = ' . $db->quote($zalo_id);
        }

        if (!empty($where)) {
            // Lấy thông tin liên hệ
            $sql = "SELECT con.id
                ,con.last_name AS name
                ,con.phone_mobile AS phone
                ,e.email_address AS email
                ,con.is_uncomfortable
                ,con.is_ctv
                ,con.is_compare_price
                ,zc.zalo_id
                ,zc.avatar
                ,zc.alias
                ,zc.last_interaction
            FROM contacts con
                LEFT JOIN email_addr_bean_rel eb ON eb.bean_id = con.id 
                    AND eb.bean_module = 'Contacts' AND eb.deleted = 0
                LEFT JOIN email_addresses e ON e.id = eb.email_address_id
                LEFT JOIN ec_zalo_contacts zc ON zc.contact_id = con.id
            WHERE (" . implode(' AND ', $where) . ") 
                AND con.deleted = 0
            ORDER BY con.date_entered
            LIMIT 1";

            $res = $db->query($sql);
            while ($row = $db->fetchByAssoc($res)) {
                $data['contact_id'] = $row['id'];
                $data['name']       = $row['name'] ?? '';
                $data['phone']      = $row['phone'] ?? $phone;
                $data['email']      = $row['email'] ?? '';
                $data['is_uncomfortable'] = (bool)$row['is_uncomfortable'] ?? '';
                $data['is_ctv'] = (bool)$row['is_ctv'] ?? '';
                $data['is_compare_price'] = (bool)$row['is_compare_price'] ?? '';

                $data['avatar']     = $row['avatar'] ?? '';
                $data['zalo_id']    = $row['zalo_id'] ?? $zalo_id;
                $data['zalo_name']  = $row['alias'] ?? '';
                $data['last_interaction'] = $row['last_interaction'] ?? '';
            }
        }

        // Kiểm tra tương tác Zalo
        $data['is_call_zalo'] = !empty($data['zalo_id']) ? EC_Zalo_Contacts_Helper::check_zalo_contact_action('call', $data['zalo_id']) : false;

        /**********  3. Get booking info of contact via phone **********/
        $phone_lh = (isset($data['phone']) && !empty($data['phone'])) ? $data['phone'] : $phone;
        // $data['info_booking'] = get_booking_info($phone_lh);

        /**********  4. Get call info of contact via phone **********/
        $data['info_refund_ticket'] = get_booking_refund($phone_lh);

        /**********  5. Get call info of contact via phone **********/
        $data['info_call'] = get_call_info($phone_lh);

        // Return
        echo json_encode($data);
        exit();
    } else if ((string)$type === "update_call") {
        try {
            $call_id        = isset($_POST['call_id']) ? global_test_input($_POST['call_id']) : "";
            $contact_id     = isset($_POST['contact_id']) ? global_test_input($_POST['contact_id']) : "";
            $phone          = isset($_POST['phone']) ? global_test_input(str_replace(" ", "", $_POST['phone'])) : "";
            $zalo_id        = isset($_POST['zalo_id']) ? global_test_input($_POST['zalo_id']) : "";
            $name           = isset($_POST['name']) ? global_test_input($_POST['name']) : "";
            $email          = isset($_POST['email']) ? global_test_input($_POST['email']) : "";
            $note           = isset($_POST['note']) ? addslashes($_POST['note']) : "";
            $call_reason    = isset($_POST['call_reason']) ? global_test_input($_POST['call_reason']) : "";
            $booking_id     = isset($_POST['booking_id']) ? global_test_input($_POST['booking_id']) : "";
            $type_call      = isset($_POST['type_call_booking']) && !empty($_POST['type_call_booking']) ? global_test_input($_POST['type_call_booking']) : "called";
            $journey_id     = isset($_POST['journey_id']) ? global_test_input($_POST['journey_id']) : "";
            $call_status    = (!empty($note) && !empty($call_reason)) ? 'done' : 'new';

            $is_uncomfortable   = isset($_POST['is_uncomfortable']) ? $_POST['is_uncomfortable'] : false;
            $is_ctv             = isset($_POST['is_ctv']) ? $_POST['is_ctv'] : false;
            $is_compare_price   = isset($_POST['is_compare_price']) ? $_POST['is_compare_price'] : false;

            $is_send_zbs_after_call = (int)($_POST['is_send_zbs_after_cal'] ?? 0);
            $data_zbs_after_call_code = global_test_input($_POST['data_zbs_after_call_code'] ?? '');
            $data_zbs_after_call_datetime = global_test_input($_POST['data_zbs_after_call_datetime'] ?? '');

            // Validate
            if (empty($call_id) || empty($note)) {
                echo json_encode([
                    "status" => 0,
                    "errorCode" => 400,
                    "message" => empty($note) ? "Vui lòng note đầy đủ" : "Cuộc gọi thiếu thông tin"
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }

            global $db, $sugar_config, $current_user;

            /**********  1. Handle Call & Contact  **********/
            if (empty($contact_id)) {
                $where = '';

                $where_clauses = [];
                if (!empty($phone)) {
                    $phone_escaped = $db->quote($phone);
                    $where_clauses[] = 'c.phone_mobile = ' . $phone_escaped;
                }
                if (!empty($zalo_id)) {
                    $zalo_escaped = $db->quote($zalo_id);
                    $where_clauses[] = 'zc.zalo_id = ' . $zalo_escaped;
                }

                if (!empty($where_clauses)) {
                    $sql = 'SELECT c.id, c.phone_mobile, zc.zalo_id
                            FROM contacts c
                                LEFT JOIN ec_zalo_contacts zc ON zc.contact_id = c.id
                            WHERE (' . implode(' OR ', $where_clauses) . ')
                                AND c.deleted = 0';
                    $result = $db->query($sql);

                    $found_ids = [];
                    while ($row = $db->fetchByAssoc($result)) {
                        $found_ids[] = $row['id'];
                    }

                    $contact_id = $found_ids[0];
                    if (count($found_ids) > 1) {
                        $cont = "Có nhiều hơn 1 liên hệ trùng thông tin";
                        $cont .= "\nSĐT: <b>$phone</b>";
                        $cont .= "\nZalo ID: <b>$zalo_id</b>";
                        $cont .= "\n<i>From epCallContact update_call()</i>";
                        $cont .= "\n<pre>" . json_encode($_POST, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
                        NotificationService::sendWarningMessage($cont, '', ['threadKey' => 'system']);
                    }
                }
            }

            $con = new Contact();
            if ($contact_id && !empty($contact_id)) {
                $save = false;
                $con->retrieve($contact_id);

                if (empty($con->phone_mobile) && !empty($phone) && !isExitsPhoneNumber('contacts', $phone)) {
                    $con->phone_mobile = trim($phone);
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
                if ($is_uncomfortable) {
                    $con->is_uncomfortable = $is_uncomfortable;
                }
                if ($is_ctv) {
                    $con->is_ctv = $is_ctv;
                }
                if ($is_compare_price) {
                    $con->is_compare_price = $is_compare_price;
                }

                if ($save === true) {
                    $con->description = "Cập nhật thông tin Liên hệ từ cuộc gọi có call_ID: $call_id";
                    $con->save();
                }
            } else {
                if (!empty($call_id) && !isExitsPhoneNumber('contacts', $phone)) {
                    $con->phone_mobile      = trim($phone);
                    $con->last_name         = $name;
                    $con->email1            = $email;
                    $con->description       = "Liên hệ tạo từ cuộc gọi có call_ID: $call_id";
                    if ($is_uncomfortable) {
                        $con->is_uncomfortable = $is_uncomfortable;
                    }
                    $con->assigned_user_id  = $current_user->id;
                    $con->save();
                }
            }

            // Map contact and zalo
            if (!empty($zalo_id) && is_string($con->id)) {
                $zaloContact = new EC_Zalo_Contacts();
                EC_Zalo_Contacts_Helper::map_contact_zalo($con->id, $zalo_id);
            }

            // CHECK CALL_ID ĐÃ CÓ TRONG DB HAY CHƯA
            $currentDate = date('Y-m-d H:i:s', strtotime('+7 hour'));
            $is_exist_callid = $db->getOne("SELECT IF(COUNT(id) > 0, 1, 0) FROM calls WHERE call_id = '{$call_id}' AND deleted = 0") ?? 0;
            if ($is_exist_callid) {

                // Gọi đến - Gọi hỏi vé -> Nếu phone đó có BK gần nhất chưa hoàn tất thì map vào luôn
                if (empty($booking_id) && (string)$call_reason === 'in_price' && !empty($phone)) {
                    // Bước 1: Kiểm tra phone này có BK nào không
                    $sql_check = '
                        SELECT
                            COUNT(*) AS total,
                            SUM(CASE WHEN booking_status = "8" THEN 1 ELSE 0 END) AS total_completed
                        FROM ec_flight_bookings
                        WHERE phone = ' . $db->quote(trim($phone)) . ' 
                        AND deleted = 0
                    ';
                    $res_check  = $db->query($sql_check);
                    $row_check  = $db->fetchByAssoc($res_check);
                    $total           = (int)($row_check['total'] ?? 0);
                    $total_completed = (int)($row_check['total_completed'] ?? 0);

                    // Bước 2: Có BK nhưng chưa có cái nào hoàn tất → Case 1 → Map
                    if ($total > 0 && $total_completed === 0) {
                        $row_bk = $db->fetchByAssoc($db->query('
                            SELECT id 
                            FROM ec_flight_bookings
                            WHERE phone = ' . $db->quote(trim($phone)) . ' AND deleted = 0
                            ORDER BY date_entered DESC 
                            LIMIT 1
                        '));

                        if (!empty($row_bk['id'])) {
                            $booking_id = $row_bk['id'];
                        }
                    }
                }
                // else if (empty($booking_id) && (string)$call_reason === 'in_consultant' && !empty($phone)) {}

                $sql_update_call = "UPDATE calls
                    SET parent_type = 'Contacts'
                        ,parent_id = '{$con->id}'
                        ,description = '{$note}'
                        ,booking_id = '{$booking_id}'
                        ,created_by = '{$current_user->id}'
                        ,modified_user_id = '{$current_user->id}'
                        ,assigned_user_id = '{$current_user->id}'
                        ,call_reason = '{$call_reason}'
                        ,journey_id = '{$journey_id}'
                        ,type_call_sources = '{$type_call}'
                        ,status = '{$call_status}'
                    WHERE call_id = '{$call_id}' AND deleted = 0";
                $result_update_call = $db->query($sql_update_call);

                if ($result_update_call) {
                    $log_save_calls = "[{$current_user->user_name}][{$currentDate}][success]$sql_update_call";
                    save_log_call($log_save_calls);
                } else {
                    $log_save_calls = "[{$current_user->user_name}][{$currentDate}][Failed_db]$sql_update_call";
                    save_log_call($log_save_calls);

                    echo json_encode([
                        "status"    => 0,
                        "errorCode" => 500,
                        "message"   => "Cập nhật cuộc gọi không thành công"
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            } else {
                $log_save_calls = "[{$current_user->user_name}][{$currentDate}][Failed_Callid]$sql_exist_callid";
                save_log_call($log_save_calls);

                echo json_encode([
                    "status"    => 0,
                    "errorCode" => 409,
                    "message"   => "Không tìm thấy cuộc gọi để cập nhật. Vui lòng đợi trong giây lát rồi thử lại!"
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            /**********  2. Handle Booking  **********/
            $sql_call = "SELECT id, name, status, call_talk, description, direction FROM calls WHERE call_id = '{$call_id}' AND deleted = 0 LIMIT 1";
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
                            $work->description          = $note . ' (' . $type_call . ' ' . $call_status . ' ' . $call['call_talk'] . ')';
                            // Gọi đến chỉ cần kết nối (call_talk > 0), gọi đi cần nói chuyện tối thiểu 20s
                            $work->$type_call           = (
                                (string)$call_status === 'done' && !empty($note) && (
                                    ((string)$call['direction'] === 'inbound' && (int)$call['call_talk'] > 0)
                                    || ((string)$call['direction'] === 'outbound' && (int)$call['call_talk'] >= 20)
                                )
                            ) ? 1 : 0;
                            $work->assigned_user_id     = $current_user->id;
                            $work->save();

                            if (empty($work->id)) {
                                // SEND TELE WARNING SAVE KPI FAILED
                                $messages = "- Domain: <b>" . $sugar_config['host_name'] . "</b>\n" .
                                    "- Call: <b>" . $call['name'] . " - " . $booking_name . "</b>\n" .
                                    "- User: <b>" . $current_user->user_name . "</b>\n" .
                                    "<pre>[WARNING]: SAVE KPI HAS BOOKING FAILED " . $call['description'] . ". Hội thoại: " . $call['call_talk'] . ".</pre>";
                                $content = html_entity_decode($messages, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                $messageData = json_encode([
                                    'text' => $content,
                                    'parse_mode' => 'HTML',
                                    'reply_markup' => [
                                        'inline_keyboard' => [
                                            [
                                                [
                                                    'text' => 'Redirect url',
                                                    'url' => 'https://' . $sugar_config['host_name'] . '/index.php?module=Calls&action=DetailView&record=' . $call['id'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ], JSON_UNESCAPED_UNICODE);
                                $botToken   = $sugar_config['telegram']['bot_token'] ?? '';
                                $chatId     = $sugar_config['telegram']['chat_id'] ?? '';
                                $threadId   = $sugar_config['telegram']['thread_id_logs'] ?? '';
                                Telegram::sendMessageData($messageData, $botToken, $chatId, $threadId);
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

                            $bk = new EC_Flight_Bookings();
                            $bk->retrieve($booking_id);
                            if ((string)$type_call === 'called' && (int)$bk->booking_status === 1) {
                                $db->query(
                                    "UPDATE ec_flight_bookings
                                    SET booking_status = '6', assigned_user_id = '{$current_user->id}'
                                    WHERE id = '$booking_id'"
                                );
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
                            }
                        }
                    }
                }
            } else {
                while ($call = $db->fetchByAssoc($result)) {
                    if (
                        !empty($note) &&  strtolower((string)$call['status']) === 'done' &&  (
                            ((string)$call['direction'] === 'inbound' && (int)$call['call_talk'] > 0) || ((string)$call['direction'] === 'outbound' && (int)$call['call_talk'] >= 20))
                    ) {
                        $work = new EC_Working_Process();
                        $work->name = $call['name'];
                        $work->parent_type = 'Calls';
                        $work->parent_id = $call['id'];
                        $work->description = $note . ' (' . $type_call . ') Cập nhật cuộc gọi';
                        $work->$type_call = 1;
                        $work->assigned_user_id = $current_user->id;
                        $work->save();

                        if (empty($work->id)) {
                            // SEND TELE WARNING SAVE KPI FAILED
                            $messages = "- Domain: <b>" . $sugar_config['host_name'] . "</b>\n" .
                                "- Call: <b>" . $call['name'] . "</b>\n" .
                                "- User: <b>" . $current_user->user_name . "</b>\n" .
                                "<pre>[WARNING]: SAVE KPI FAILED " . $call['description'] . ". Hội thoại: " . $call['call_talk'] . ".</pre>";
                            $content = html_entity_decode($messages, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                            $messageData = json_encode([
                                'text' => $content,
                                'parse_mode' => 'HTML',
                                'reply_markup' => [
                                    'inline_keyboard' => [
                                        [
                                            [
                                                'text' => 'Redirect url',
                                                'url' => 'https://' . $sugar_config['host_name'] . '/index.php?module=Calls&action=DetailView&record=' . $call['id'],
                                            ],
                                        ],
                                    ],
                                ],
                            ], JSON_UNESCAPED_UNICODE);
                            $botToken   = $sugar_config['telegram']['bot_token'] ?? '';
                            $chatId     = $sugar_config['telegram']['chat_id'] ?? '';
                            $threadId   = $sugar_config['telegram']['thread_id_logs'] ?? '';
                            Telegram::sendMessageData($messageData, $botToken, $chatId, $threadId);
                        }
                    }
                }
            }

            /**********  3. Send ZBS message (after-call-sale)  **********/
            try {
                $zbs_template_message_params = [];
                if ($is_send_zbs_after_call) {
                    if (!empty($data_zbs_after_call_code) && !empty($data_zbs_after_call_datetime)) {
                        if (!empty($phone)) {
                            $zbs_template_message_params = [
                                "phoneNumber" => $phone,
                                "type" => "after-call-sale",
                                "parentId" => $booking_id,
                                "parentType" => !empty($booking_id) ? "EC_Flight_Bookings" : "",
                                "templateData" => [
                                    "full_name" => "quý khách",
                                    "flight_no" => $data_zbs_after_call_code,
                                    "datetime" => $data_zbs_after_call_datetime,
                                ],
                            ];
                        } else if (!empty($zalo_id)) {
                            $zalo_info = EC_Zalo_Contacts_Helper::get_zalo_user_info($zalo_id, '', true);
                            if (isset($zalo_info['shared_info']['phone']) && !empty($zalo_info['shared_info']['phone'])) {
                                $zbs_template_message_params = [
                                    "phoneNumber" => $zalo_info['shared_info']['phone'],
                                    "type" => "after-call-sale",
                                    "parentId" => $booking_id,
                                    "parentType" => !empty($booking_id) ? "EC_Flight_Bookings" : "",
                                    "templateData" => [
                                        "full_name" => "quý khách",
                                        "flight_no" => $data_zbs_after_call_code,
                                        "datetime" => $data_zbs_after_call_datetime,
                                    ],
                                ];
                            }
                        }
                    }
                } else if (!empty($booking_id)) {
                    $booking = new EC_Flight_Bookings();
                    $booking->retrieve($booking_id);
                    if (!empty($booking->id) && strtoupper(trim($booking->contact_name)) == 'THAM KHAO' && $booking->total_amount == 0) {
                        $sqlItineraries = "SELECT departure AS dep_code, arrival AS des_code, departure_date
                            FROM ec_booking_itineraries 
                            WHERE booking_id = '{$booking_id}'
                                AND direction = '0'
                                AND deleted = 0";
                        $resItineraries = $db->query($sqlItineraries);
                        $rowItineraries = $db->fetchByAssoc($resItineraries);

                        $dep_code = $rowItineraries['dep_code'] ?? '';
                        $des_code = $rowItineraries['des_code'] ?? '';
                        $departure_date = $rowItineraries['departure_date'] ?? '';

                        if (!empty($dep_code) && !empty($des_code) && !empty($departure_date) && strtotime($departure_date) !== false) {
                            $zbs_template_message_params = [
                                "phoneNumber" => !empty($phone) ? $phone : $booking->phone,
                                "type" => "after-call-sale",
                                "parentId" => $booking_id,
                                "parentType" => "EC_Flight_Bookings",
                                "templateData" => [
                                    "full_name" => "quý khách",
                                    "flight_no" => "{$dep_code}-{$des_code}",
                                    "datetime" => date('d/m/Y', strtotime($departure_date)),
                                ],
                                "auto" => 1
                            ];
                        }
                    }
                }
                if (is_array($zbs_template_message_params) && !empty($zbs_template_message_params)) {
                    $entry = new entryFactory();
                    $entryOA = $entry->create('entryZaloOAClass');
                    $entryOA->sendTemplateMessage($zbs_template_message_params);
                }
            } catch (Throwable $th) {
                $GLOBALS['log']->fatal("Error happen when sending ZBS message (after-call-sale): {$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
            }

            echo json_encode(["status" => 1, "message" => "Success"]);
            exit;
        } catch (Throwable $th) {
            echo json_encode([
                "status"    => 0,
                "errorCode" => 500,
                "message"   => "Lỗi: {$th->getMessage()} on line {$th->getLine()}"
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    } else if ((string)$type === "check_missed_call") {
        $call_id = isset($_POST['call_id']) ? global_test_input($_POST['call_id']) : "";

        if (empty($call_id)) {
            echo 0;
            exit();
        }

        global $db;
        $sql = "SELECT call_id FROM calls WHERE call_id = '$call_id' AND direction = 'missed' AND deleted = 0";
        $id = $db->getOne($sql);

        if ($id && !empty($id)) echo 1;
        else echo 0;
        exit();
    } else if ((string)$type === "map_call_booking") {
        $call_name      = global_test_input($_POST['call_name'] ?? "");
        $booking_id     = global_test_input($_POST['booking_id'] ?? "");
        $booking_name   = global_test_input($_POST['booking_name'] ?? "");
        $force          = (int)($_POST['force'] ?? 0);

        /**
         * 0 — call not found
         * 1 — success
         * 2 — description empty
         * 3 — call already linked, force=0 (frontend should show confirm)
         */
        if (empty($call_name) || empty($booking_id)) {
            echo 0;
            exit();
        }

        global $db, $current_user;

        $call_id = $db->getOne("SELECT id FROM calls WHERE name = '$call_name' AND deleted = 0");

        // If found but already linked, return 3 unless force=1
        if (!empty($call_id) && $force === 0) {
            $existing_booking = $db->getOne("SELECT booking_id FROM calls WHERE id = '$call_id' AND deleted = 0");
            if (!empty($existing_booking)) {
                echo 3;
                exit();
            }
        }

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

            $db->query(
                "UPDATE ec_flight_bookings
                SET booking_status = '6',
                    assigned_user_id = '$assigned_user_id'
                WHERE id = '$booking_id'
                    AND booking_status NOT IN('3', '7', '8')
                    AND deleted = 0"
            );

            echo 1;
            exit();
        }

        echo 0;
        exit();
    } else if ((string)$type === "map_call_booking_auto") {
        $booking_id     = isset($_POST['booking_id']) ? global_test_input($_POST['booking_id']) : "";
        $booking_name   = isset($_POST['booking_name']) ? global_test_input($_POST['booking_name']) : "";
        $phone          = isset($_POST['phone']) ? global_test_input(trim($_POST['phone'])) : "";

        if (empty($phone) || empty($booking_id)) {
            echo 400;
            exit();
        }

        global $db, $current_user;

        // Tìm cuộc gọi đến gần nhất của SĐT phone
        $sql = 'SELECT id
                FROM calls
                WHERE call_from = "' . $db->quote(trim($phone)) . '"
                    AND direction = "inbound"
                    AND (booking_id IS NULL OR booking_id = "")
                    AND deleted = 0
                ORDER BY date_entered DESC
                LIMIT 1';
        $call_id = $db->getOne($sql);

        if (!empty($call_id)) {
            $cal = new Call();
            $cal->retrieve($call_id);
            $description = trim($cal->description);

            $cal->booking_id = $booking_id;
            $cal->save();
            $assigned_user_id = (is_null($cal->assigned_user_id) || empty($cal->assigned_user_id)) ? $current_user->id : $cal->assigned_user_id;

            $bean_note                      = new Note();
            $bean_note->id                  = '';
            $bean_note->name                = $booking_name;
            $bean_note->parent_type         = 'EC_Flight_Bookings';
            $bean_note->parent_id           = $booking_id;
            $bean_note->description         = $description . ' (automap_call_bk)';
            $bean_note->booking_status      = '8';
            $bean_note->assigned_user_id    = $assigned_user_id;
            $bean_note->save();

            $sql_update = 'UPDATE ec_flight_bookings SET booking_status = "6", assigned_user_id = "' . $assigned_user_id . '" WHERE id = "' . $booking_id . '" AND deleted = 0';
            $db->query($sql_update);

            echo 1;
            exit();
        }

        echo 0;
        exit();
    } else if ((string)$type === "search_calls_by_phone") {
        $phone = isset($_POST['phone']) ? global_test_input(trim($_POST['phone'])) : "";

        if (empty($phone)) {
            echo json_encode([]);
            exit();
        }

        global $db;

        $sql = 'SELECT
                    c.name,
                    c.date_start,
                    c.direction,
                    c.booking_id,
                    bk.name AS booking_name_linked
                FROM calls c
                LEFT JOIN ec_flight_bookings bk ON bk.id = c.booking_id AND bk.deleted = 0
                WHERE c.call_from = ' . $db->quote(trim($phone)) . '
                AND c.direction = "inbound"
                AND c.deleted = 0
                ORDER BY c.date_entered DESC
                LIMIT 10';

        $res   = $db->query($sql);
        $calls = [];
        while ($row = $db->fetchByAssoc($res)) {
            $calls[] = [
                'name'                => $row['name'],
                'date_start'          => $row['date_start'],
                'direction'           => $row['direction'],
                'booking_id'          => $row['booking_id'],
                'booking_name_linked' => $row['booking_name_linked'],
            ];
        }

        echo json_encode($calls, JSON_UNESCAPED_UNICODE);
        exit();
    } else if ((string)$type === 'save_log_call') {
        $log_call = isset($_POST['log']) ? $_POST['log'] : '';
        save_log_call($log_call);
        exit;
    } else if ((string)$type === 'get_history_activity_contacts') {
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

        if ($count_actibity > 0) {
            $html = '<div class="row flex-start">
                        <div class="col-md-12">
                            <div class="main-card mb-3 card">
                                <div class="card-body p-0">
                                    <div class="vertical-timeline vertical-timeline--animate vertical-timeline--one-column">';
            while ($row = $db->fetchByAssoc($res)) {

                switch ($row['interaction_type']) {
                    case 'call':
                        $interaction_type = 'primary';
                        $interaction_link = '<a class="fw-semibold text-decoration-underline text-' . $interaction_type . '" target="_blank" href="index.php?module=Calls&return_module=Calls&action=DetailView&record=' . $row['interaction_id'] . '">' . $row['interaction_item'] . '</a><span> (' . $GLOBALS['app_list_strings']['calls_direction_list'][$row['interaction_status']] . ')</span>';
                        break;
                    case 'booking':
                        $interaction_type = 'success';
                        $interaction_link = '<a class="fw-semibold text-decoration-underline text-' . $interaction_type . '" target="_blank" href="index.php?module=EC_Flight_Bookings&return_module=EC_Flight_Bookings&action=DetailView&record=' . $row['interaction_id'] . '">' . $row['interaction_item'] . '</a><span> (' . $GLOBALS['app_list_strings']['booking_status_list'][$row['interaction_status']] . ')</span>';
                        break;
                    case 'refund':
                        $interaction_type = 'warning';
                        $interaction_link = '<a class="fw-semibold text-decoration-underline text-' . $interaction_type . '" target="_blank" href="index.php?module=EC_Hoanve&return_module=EC_Hoanve&action=DetailView&record=' . $row['interaction_id'] . '">' . $row['interaction_item'] . '</a><span> (' . $GLOBALS['app_list_strings']['tinhtranghoanve_list'][$row['interaction_status']] . ')</span>';
                        break;
                    case 'receipt_voucher':
                        $interaction_type = 'info';
                        $interaction_link = '<a class="fw-semibold text-decoration-underline text-' . $interaction_type . '" target="_blank" href="index.php?module=EC_Receipt_Voucher&return_module=EC_Receipt_Voucher&action=DetailView&record=' . $row['interaction_id'] . '">' . $row['interaction_item'] . '</a><span> (' . $GLOBALS['app_list_strings']['receipt_voucher_status_list'][$row['interaction_status']] . ')</span>';
                        break;
                    case 'payment_voucher':
                        $interaction_type = 'danger';
                        $interaction_link = '<a class="fw-semibold text-decoration-underline text-' . $interaction_type . '" target="_blank" href="index.php?module=EC_Payment_Voucher&return_module=EC_Payment_Voucher&action=DetailView&record=' . $row['interaction_id'] . '">' . $row['interaction_item'] . '</a><span> (' . $GLOBALS['app_list_strings']['payment_voucher_status_list'][$row['interaction_status']] . ')</span>';
                        break;
                    default:
                        $interaction_link = $row['interaction_item'];
                        $interaction_type = 'dark';
                }

                $html .= ' 
                        <div class="vertical-timeline-item vertical-timeline-element">
                            <div class="flex-start gap-3">
                                <span class="vertical-timeline-element-date w-25 text-secondary fw-semibold">' . date('H:i:s d-m-Y', strtotime('+7 hours', strtotime($row['interaction_date']))) . '</span>
                                <span class="vertical-timeline-element-icon bounce-in text-center">
                                    <span class="badge badge-dot badge-dot-xl text-bg-' . $interaction_type . '"> </span>
                                </span>
                                <div class="vertical-timeline-element-content bounce-in flex-fill">
                                    <p class="mb-2">
                                        ' . $interaction_link . ' 
                                        <span class="d-block">' . $row['interaction_detail'] . '</span>
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
    } else if ((string)$type === 'get_history_activity_cskh') {
        $phone = isset($_POST['phone']) ? global_test_input(str_replace(" ", "", $_POST['phone'])) : "";
        $start_date = date('Y-m-d H:i:s', strtotime('-1 year +7 hours'));
        $end_date   = date('Y-m-d H:i:s', strtotime('+7 hours'));

        if (empty($phone)) {
            echo 0;
            exit();
        }

        global $db, $app_list_strings;

        $sql = "SELECT 
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
            ORDER BY c.date_entered DESC";

        $res        = $db->query($sql);
        $count_calls    = $db->getRowCount($res);
        $user_list      = get_user_array(true, '', '', true);
        $i = 1;
        $successful_calls = 0;
        $failed_calls = 0;
        $total_duration = 0;
        $total_wait = 0;
        $total_talk = 0;

        if ($count_calls > 0) {
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
                if (isset($log['call_talk']) && $log['call_talk'] > 0) {
                    $successful_calls++;
                    $total_talk += $log['call_talk'];
                } else {
                    $failed_calls++;
                }

                if (isset($log['call_duration'])) {
                    $total_duration += $log['call_duration'];
                }
                if (isset($log['call_wait'])) {
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
                    <p>⏱️ <strong>Tổng thời gian:</strong> ' . global_secondsToTimeFormat($total_duration) . ' (Thời gian TB: ' . round($total_duration / $total_calls) . ')</p>
                    <p>⏱️ <strong>Tổng thời gian đợi:</strong> ' . global_secondsToTimeFormat($total_wait) . ' (Thời gian TB: ' . round($total_wait / $total_calls) . ')</p>
                    <p>⏱️ <strong>Tổng thời gian thoại:</strong> ' . global_secondsToTimeFormat($total_talk) . ' (Thời gian TB: ' . round($total_talk / $total_calls) . ')</p>
                </div>
            </div>';

            echo $html_call_summary . $html;
        } else {
            echo 'Chưa có cuộc gọi CSKH nào!';
        }
        exit();
    } else if ((string)$type === 'get_infor_phone') {
        $phone = isset($_POST['phone']) ? global_test_input(str_replace(" ", "", $_POST['phone'])) : "";
        if (!empty($phone)) {
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
    $user_list = get_user_array(true, '', '', true);

    $sql = "SELECT id, name, status, direction, call_from, call_to, description, assigned_user_id
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
                        <th>Nhân viên</th>
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
                        <td align="center" class="employee_name">' . $user_list[$row['assigned_user_id']] . '</td>
                        <td align="left" class="description_call" style="max-width: 250px;">' . $row['description'] . '</td>
                    </tr>';
            $i++;
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
