<?php

namespace Api\V8\Controller;

use Slim\Http\Response;
use Slim\Http\Request;
use BeanFactory;
use Throwable;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class CustomController extends BaseController
{
    /************  BOOKING  ************/
    public function save_booking(Request $request, Response $response, array $args)
    {
        try {
            global $db, $sugar_config;
            $params = (array) $request->getParsedBody();
            $user_id = $params['user_id'];
            $request_ip = $request->getServerParam('REMOTE_ADDR');

            // Validate here
            if (strlen($user_id) != 36)
                return $response->withJson(['error' => true, 'message' => "Invalid user id"], 400);
            if (!in_array($request_ip, $sugar_config['ip_whitelist']))
                return $response->withJson(['error' => true, 'message' => "Access $request_ip is not allowed"], 403);

            // Save booking
            $booking = BeanFactory::newBean("EC_Flight_Bookings");
            if (isset($params['ec_flight_bookings']) && !empty($params['ec_flight_bookings'])) {
                foreach ($params['ec_flight_bookings'] as $key => $value) {
                    $booking->$key = $value;
                }
            }

            $booking->update_modified_by = false;
            $booking->set_created_by = false;
            $booking->created_by = $user_id;
            $booking->modified_user_id = $user_id;

            // Save nganluong
            if (!isset($params['ec_flight_bookings']['nganluong_code']) || empty($params['ec_flight_bookings']['nganluong_code'])) {
                $booking->nganluong_code = get_payment_link();
                $booking->nganluong_datepaid = date('Y-m-d H:i:s');
            }

            /**
             * Map calls and booking telesale - get lastest call telesale of phone
             * Loại trừ các booking tham khảo, booking TEST
             */
            if (!empty($booking->phone) && !empty($booking->contact_name) && !in_array(strtoupper(trim($booking->contact_name)), $booking->contact_name_ignore)) {
                $call = BeanFactory::newBean("Calls");
                $call_id = $call->getTelesaleCalls($booking->phone, date('Y-m-d H:i:s'));
                if (!empty($call_id)) {
                    $booking->telesale_call_id = $call_id;
                    $booking->is_telesale = 1;
                }
            }

            $booking->save();

            // Save journeys
            if (isset($params['ec_booking_itineraries']) && !empty($params['ec_booking_itineraries'])) {
                foreach ($params['ec_booking_itineraries'] as $i) {
                    $itinerary = BeanFactory::newBean("EC_Booking_Itineraries");
                    foreach ($i as $key => $value) {
                        $itinerary->$key = $value;
                        $itinerary->booking_id = $booking->id;
                        $itinerary->update_modified_by = false;
                        $itinerary->set_created_by = false;
                        $itinerary->created_by = $user_id;
                        $itinerary->modified_user_id = $user_id;
                        $itinerary->save();
                    }
                }
            }

            // Save passengers
            if (isset($params['ec_booking_passengers']) && !empty($params['ec_booking_passengers'])) {
                foreach ($params['ec_booking_passengers'] as $p) {
                    $pass = BeanFactory::newBean("EC_Booking_Passengers");
                    foreach ($p as $key => $value) {
                        $pass->$key = $value;
                        $pass->booking_id = $booking->id;
                        $pass->update_modified_by = false;
                        $pass->set_created_by = false;
                        $pass->created_by = $user_id;
                        $pass->modified_user_id = $user_id;
                        $pass->save();
                    }
                }
            }

            // Save details
            if (isset($params['ec_booking_details']) && !empty($params['ec_booking_details'])) {
                foreach ($params['ec_booking_details'] as $d) {
                    $detail = BeanFactory::newBean("EC_Booking_Details");
                    foreach ($d as $key => $value) {
                        $detail->$key = $value;
                        $detail->booking_id = $booking->id;
                        $detail->update_modified_by = false;
                        $detail->set_created_by = false;
                        $detail->created_by = $user_id;
                        $detail->modified_user_id = $user_id;
                        $detail->save();
                    }
                }
            }

            // Save voucher
            if (isset($params['ec_vouchers']) && !empty($params['ec_vouchers'])) {
                foreach ($params['ec_vouchers'] as $type => $arr) {
                    $voucher_id = isset($arr['voucher_id']) ? $arr['voucher_id'] : '';
                    $discount_amount = isset($arr['discount_amount']) ? $arr['discount_amount'] : 0;
                    if (empty($voucher_id) || $discount_amount < 1)
                        continue;

                    // Save relationship booking & voucher
                    $booking->load_relationship('vouchers');
                    $booking->vouchers->add($voucher_id);
                    global $db;
                    $sql = "UPDATE bookings_vouchers
                            SET discount_amount = $discount_amount
                            WHERE booking_id = '$booking->id'
                                AND voucher_id = '$voucher_id'
                                AND deleted = 0";
                    $db->query($sql);

                    // Update voucher
                    if ($type == 'private') {
                        $sql = "UPDATE ec_vouchers v SET v.status = 'done' WHERE v.id = '$voucher_id' AND v.deleted = 0";
                        $db->query($sql);
                    }
                }
            }

            // Return
            $data = [
                'booking_id' => $booking->id,
                'booking_name' => $booking->name,
                'subtotal_amount' => $booking->subtotal_amount,
                'luggage_fee' => $booking->luggage_fee,
                'total_amount' => $booking->total_amount
            ];

            return $response->withJson([
                'error' => false,
                'message' => "Success",
                'data' => $data,
            ], 201);
        } catch (Throwable $e) {
            return $response->withJson([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /************  CALL  ************/
    public function save_call(Request $request, Response $response, array $args)
    {
        $params = (array) $request->getParsedBody();

        $call_id = isset($params['call_id']) ? global_test_input($params['call_id']) : '';
        $call_direction = isset($params['call_direction']) ? global_test_input($params['call_direction']) : '';

        $arr_whitelist = ['0898888280', '0348650381'];

        // Xử lý cuộc gọi đến thiếu số 0
        if (isset($params['call_from']) && $call_direction == 'inbound' && strlen($params['call_from']) < 10 && substr($params['call_from'], 0, 1) != 0) {
            $call_from = '0' . trim($params['call_from']);
        } else {
            $call_from = isset($params['call_from']) ? trim($params['call_from']) : '';
        }

        $call_to = isset($params['call_to']) ? trim($params['call_to']) : '';
        $call_start = isset($params['call_start']) ? global_test_input($params['call_start']) : '';
        $call_duration = isset($params['call_duration']) ? global_test_input($params['call_duration']) : 0;
        $call_end = isset($params['call_end']) ? global_test_input($params['call_end']) : '';
        $call_talk = isset($params['call_talk']) ? global_test_input($params['call_talk']) : 0;
        $call_wait = isset($params['call_wait']) ? global_test_input($params['call_wait']) : 0;
        $call_answer = isset($params['call_answer']) ? global_test_input($params['call_answer']) : 0;
        $record_file = isset($params['record_file']) ? global_test_input($params['record_file']) : '';
        $other_caller = isset($params['other_caller']) ? global_test_input($params['other_caller']) : '';
        $call_mos = isset($params['call_mos']) ? global_test_input($params['call_mos']) : null;
        $dialed = isset($params['dialed']) ? global_test_input($params['dialed']) : '';

        if (empty($call_id)) {
            // Lưu log
            $GLOBALS['log']->fatal('Lỗi params. Lưu thông tin cuộc gọi thất bại');
            write_file_backup_log_calls(json_encode($params));

            $response['fail'] = array(
                'error' => true,
                'code' => 400,
                'message' => 'Bad request params',
                'data_post' => json_encode($params)

            );
            echo json_encode($response);
            exit();
        }

        $platform = 'switchboard';
        $where = '';
        $number = $call_direction == 'inbound' ? $call_from : $call_to;
        if (strlen($number) < 15) {
            $where = 'phone_mobile = "' . $number . '"';
        } else {
            $platform = 'zalo';
            $where = 'zalo_id = "' . $number . '"';
        }

        // Get contact info
        global $db;
        $sql = '
            SELECT id
            FROM contacts
            WHERE ' . $where . ' AND deleted = 0
            LIMIT 1';
        $res = $db->query($sql);
        $row = $db->fetchByAssoc($res);

        $call = BeanFactory::newBean("Calls");
        if (!empty($row['id'])) { // Cập nhật thông tin liên hệ cho Call
            $call->parent_type = 'Contacts';
            $call->parent_id = $row['id'];
        }
        $call->call_id = $call_id;
        $call->call_from = $call_from;
        $call->call_to = $call_to;
        $call->call_type = strlen($number) < 15 ? 'phone' : 'zalo';
        $call->type_call_sources = 'called';

        if ($call_direction == 'internal') {
            $call->direction = 'internal';
        } else if ($call_direction == 'outbound') {
            $call->direction = 'outbound';
        } else if ($call_direction == 'inbound' && isSpamPhone($call_from) && strlen($call_from) < 15) {
            $call->direction = 'spam';
        } else if ($call_direction == 'inbound' && $call_talk == 0) {
            // Check file json
            $json_blacklist = get_blacklist_phone();

            if (!empty($json_blacklist)) {
                $arr_phone_blacklist = json_decode($json_blacklist, true);
                if (in_array(trim($call_from), $arr_phone_blacklist)) {
                    $call->direction = 'spam';
                }
            }

            if ($call->direction != 'spam') {
                // Lịch sử cuộc gọi có số lượng cuộc gọi nhỡ > 3 và call_duration AVG < 8s thì đánh dấu số đó là SPAM
                $sql_spam = 'SELECT 
                                COUNT(*) AS count_spam,
                                AVG(JSON_EXTRACT(log, "$.call_duration")) AS avg_duration
                            FROM calls
                            WHERE call_from = "' . trim($call_from) . '"
                                AND direction = "missed"
                                AND deleted = 0
                                AND STR_TO_DATE(date_start, "%d-%m-%Y %H:%i:%s") >= DATE_SUB(NOW(), INTERVAL 48 HOUR)';

                $res_spam = $db->query($sql_spam);
                $row_spam = $db->fetchByAssoc($res_spam);
                if ($row_spam['count_spam'] > 3 && $row_spam['avg_duration'] < 8) {
                    $call->direction = 'spam';

                    // Update số đó vào file JSON
                    if (!in_array(trim($call_from), $arr_whitelist)) {
                        add_blacklist_phone($call_from);
                    }
                } else {
                    $call->direction = 'missed';
                }
            }
        } else if ($call_direction == 'inbound' && $call_talk < 6) {
            $call->direction = 'suddenly';
        } else {
            $call->direction = $call_direction;
        }

        // Bổ sung assigned_user_id cho cuộc gọi đi / nội bộ
        if (in_array($call->direction, array('outbound', 'internal'))) {
            $call->assigned_user_id = custom_get_sip_number($call_from);
        } else if (in_array($call->direction, array('inbound'))) {
            if (trim($dialed)) {
                $user_id = custom_get_sip_number(trim($dialed));
                if (!empty($user_id)) {
                    $call->assigned_user_id = $user_id;
                }
            }
        }

        $call->date_start = date('d-m-Y H:i:s', strtotime($call_start));
        $call->date_end = $call_end ? date('d-m-Y H:i:s', strtotime($call_end)) : date('d-m-Y H:i:s', strtotime($call_start) + (int) $call_duration);
        $call->status = 'new';
        $call->log = json_encode($params);
        $call->record_file = $record_file;
        $call->other_caller = $other_caller;
        $call->call_mos = $call_mos;
        $call->call_duration = (int) $call_duration;
        $call->call_wait = (int) calculateWaitTime($params);
        $call->call_talk = (int) $call_talk;
        $call->is_success = ((int) $call_talk > 0) ? 1 : 0;

        if ($call_direction == 'inbound' || $call_direction != 'outbound') {
            $info_phone = getInfoCallSource($call_to);
            $site = isset($info_phone['website']) && !empty($info_phone['website']) ? $info_phone['website'] : 'giaonhanh.com.vn';
            $call->call_sources = $site;
        }
        $call->hangup_cause = $call->determineHangupCause($params);
        $call->save();

        if (!empty($call->id)) {
            // Save log zalo message with type call 
            try {
                if ($platform == 'zalo') {
                    global $sugar_config;
                    $oa_id = $sugar_config['zalo_config']['oa_id'] ?? '';

                    $src = '';
                    if (strlen($call->call_to) > strlen($oa_id) && strpos($call->call_to, $oa_id) === 0)
                        $src = 1;
                    else
                        $src = 0;

                    $assigned_user_id = '';
                    if ($src == 0 && !empty($call->call_from) && strlen($call->call_from) < 5) {
                        $assigned_user_id = $db->getOne("SELECT id FROM users WHERE td_sip = '" . $call->call_from . "' AND deleted = 0");
                    } elseif ($src == 1 && !empty($dialed) && strlen($dialed) < 5) {
                        $assigned_user_id = $db->getOne("SELECT id FROM users WHERE td_sip = '$dialed' AND deleted = 0");
                    }

                    $zalomes = BeanFactory::newBean("EC_Zalo_Messages");
                    $zalomes->id = '';
                    $zalomes->message_id = $call->id;
                    $zalomes->src = $src;
                    $zalomes->from_id = $src == 0 ? $oa_id : $call->call_from;
                    $zalomes->to_id = $src == 1 ? $oa_id : $call->call_to;
                    $zalomes->timestamp = round(microtime(true) * 1000); // Milliseconds
                    $zalomes->type = 'call';
                    $zalomes->sub_type = $call->direction;
                    $zalomes->data = json_encode([
                        'record_file' => $call->record_file,
                        'duration' => $call->call_duration,
                        'routing' => $src == 1 ? substr($call->call_to, -3) : $call->call_from
                    ]);
                    $zalomes->response = $call->log;
                    $zalomes->assigned_user_id = $assigned_user_id;
                    $zalomes->save();
                }
            } catch (Exception $e) {
                return json_encode([
                    'error' => false,
                    'code' => 200,
                    'message' => "Success",
                ]);
            }

            return json_encode([
                'error' => false,
                'code' => 200,
                'message' => "Success",
            ]);
        } else {
            $GLOBALS['log']->fatal('Lưu cuộc gọi thất bại.');
            write_file_backup_log_calls(json_encode($params));

            return json_encode([
                'error' => true,
                'code' => 401,
                'message' => "Failed",
                'data_post' => json_encode($params)
            ]);
        }
    }

    /************  VOUCHER  ************/
    /**
     * Get info single voucher by code
     */
    public function get_info_voucher(Request $request, Response $response, array $args)
    {
        global $sugar_config;
        $params = (array) $request->getParsedBody();
        $request_ip = $request->getServerParam('REMOTE_ADDR');
        $voucher_code = isset($params['voucher_code']) ? global_test_input($params['voucher_code']) : '';

        if (!in_array($request_ip, $sugar_config['ip_whitelist']))
            return $response->withJson([
                'error' => 1,
                'message' => "Access $request_ip is not allowed"
            ], 403);
        if (strlen($voucher_code) < 9 || strlen($voucher_code) > 20)
            return $response->withJson([
                'error' => 1,
                'message' => "Invalid voucher code $voucher_code"
            ], 400);

        global $db;
        $sql = "SELECT id
                    ,name
                    ,campaign_id
                    ,campaign_name
                    ,status
                    ,start_time
                    ,end_time
                    ,reduce_amount
                    ,reduce_percent
                    ,max_discount
                    ,condition_voucher
                FROM ec_vouchers
                WHERE name = '$voucher_code'
                    AND type = 'single'
                    AND status NOT IN('new', 'cancel')
                    AND deleted = 0
                LIMIT 1";

        $res = $db->query($sql);
        $result = $db->fetchByAssoc($res);

        if (!$result) {
            return $response->withJson([
                'error' => 1,
                'message' => "Not found",
                'data' => null
            ], 200);
        }

        // Clean JSON in condition_voucher field
        if (isset($result['condition_voucher']) && is_string($result['condition_voucher']))
            $result['condition_voucher'] = json_decode(html_entity_decode(trim($result['condition_voucher'])), true);

        return $response->withJson([
            'error' => 0,
            'message' => "Success",
            'data' => $result
        ], 200);
    }

    /************  ZALO  ************/
    /**
     * Send ZNS from website
     */
    public function send_zns(Request $request, Response $response, array $args)
    {
        return $response->withJson([
            "error" => true,
            "message" => "Tính năng ngừng hoạt động",
        ], 404);
    }

    // API TEST SAVE CONTACT - APPS SCRIPT
    public function save_contacts(Request $request, Response $response, array $args)
    {
        $contacts = (array) $request->getParsedBody() ?? [];
        $request_ip = $request->getServerParam('REMOTE_ADDR');

        if (is_array($contacts) && count($contacts) > 0) {
            foreach ($contacts as $contactData) {
                $contact = BeanFactory::newBean('Contacts');
                foreach ($contactData as $key => $value) {
                    $property = strtolower($key);

                    // TODO: Kiểm tra trùng phone. $property = phone_mobile
                    if (property_exists($contact, $property)) {
                        $contact->$property = $value;
                    }
                }
                $contact_id = $contact->save();

                if (!$contact_id) {
                    // $messages = "- SAVE CONTACT - APPS SCRIPT - ERROR:\n" .
                    //     "<pre>[ERROR]: Lưu thông tin liên hệ Apps script thất bại! " . json_encode($contactData) . "</pre>";
                    // $content = html_entity_decode($messages, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    // sendTelegramWarningSystem(
                    //     json_encode(array(
                    //         'text' => $content,
                    //         'parse_mode' => 'HTML',
                    //     ), JSON_UNESCAPED_UNICODE),
                    // );

                    // global $sugar_config;
                    // $message = Mattermost::markdownHeading("[ERROR] Save contact failed\n");
                    // $message .= "Lưu thông tin liên hệ Apps script thất bại!\n\n";
                    // $message .= json_encode($contactData);
                    // Mattermost::sendMessage($sugar_config['mattermost']['channel_id_logs'] ?? '', $message);
                }
            }
        }

        return $response->withJson([
            'error' => 0,
            'message' => "Access Success",
            'data' => $contacts,
            'ip' => $request_ip,
        ], 200);
    }
    // Save voucher in APP
    public function save_voucher(Request $request, Response $response, array $args)
    {
        try {
            global $db, $sugar_config;
            $params = (array) $request->getParsedBody();

            $booking_id = $params['booking_id'];
            $voucher_id = $params['voucher_id'];
            $discount_amount = $params['discount_amount'];
            $request_ip = $request->getServerParam('REMOTE_ADDR');

            if (!in_array($request_ip, $sugar_config['ip_whitelist'])) {
                return $response->withJson(['error' => true, 'message' => "Access denied"], 403);
            }

            $booking = BeanFactory::getBean("EC_Flight_Bookings", $booking_id);
            if (!$booking) {
                return $response->withJson(['error' => true, 'message' => "Booking not found"], 404);
            }

            $booking->load_relationship('vouchers');
            $booking->vouchers->add($voucher_id);

            $id = create_guid();
            $sql = "INSERT INTO bookings_vouchers (id, booking_id, voucher_id, discount_amount, deleted, date_modified) 
                VALUES ('{$id}', '{$booking_id}', '{$voucher_id}', {$discount_amount}, 0, NOW())
                ON DUPLICATE KEY UPDATE discount_amount = {$discount_amount}, date_modified = NOW()";
            $db->query($sql);

            return $response->withJson([
                'error' => false,
                'message' => "Voucher saved successfully",
                'data' => ['booking_id' => $booking_id, 'voucher_id' => $voucher_id]
            ], 201);

        } catch (Throwable $e) {
            return $response->withJson(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }
}
