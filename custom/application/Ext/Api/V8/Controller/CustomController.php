<?php

namespace Api\V8\Controller;

use Slim\Http\Response;
use Slim\Http\Request;
use BeanFactory;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class CustomController extends BaseController
{
    private $IP_WHITELIST = [
        '14.161.31.237', // LBM
        '119.17.253.63', // timchuyenbay.com (Main site)
        
        '103.160.5.21', // timchuyenbay.com
        '157.119.251.18', // vietjet.net
        '157.119.251.220', // timchuyenbay.net
        '157.119.251.12', // timchuyenbay.com.vn
        '157.119.251.225', // timchuyenbay.vn
        '157.119.251.195', // sanvemaybaygiare.net
        '157.119.251.148', // sanvemaybay.com.vn
        '157.119.251.197', // vemaybay.website, dailyve.net
        '157.119.251.191', // ve5s.com.vn
        '157.119.251.69', // giavemaybayvietjet.com
        '157.119.251.154', // vemaybayphuongnam.net
        '157.119.251.70', // datvedoan.net
        '157.119.251.122', // datvedoan.com
        '157.119.251.106', // appvemaybay.net
        '157.119.251.106', // vemaybaynamphuong.vn
        '157.119.251.145', // vemaybaynamphuong.com.vn
        '157.119.251.218', // vietjetstar.net

        '119.17.253.171', // vemaybay5s.com ; vemaybaynamphuong.com ; vietjet.net.vn
        '202.151.168.26', // travelpass.vn ; vietjetkhuyenmai.vn
    ];

    /************  BOOKING  ************/
    public function save_booking(Request $request, Response $response, array $args)
    {
        $params  = (array)$request->getParsedBody();
        $user_id = $params['user_id'];
        $request_ip = $request->getServerParam('REMOTE_ADDR');

        // Validate here
        if (strlen($user_id) != 36) return $response->withJson(['error' => true, 'message' => "Invalid user id"], 400);
        if (!in_array($request_ip, $this->IP_WHITELIST)) return $response->withJson(['error' => true, 'message' => "Access is not allowed"], 403);

        // Save booking
        $booking = BeanFactory::newBean("EC_Flight_Bookings");
        foreach ($params['ec_flight_bookings'] as $key => $value) {
            $booking->$key = $value;
        }
        $booking->update_modified_by    = false;
        $booking->set_created_by        = false;
        $booking->created_by            = $user_id;
        $booking->modified_user_id      = $user_id;
        // Save nganluong
        if(!isset($params['ec_flight_bookings']['nganluong_code']) || empty($params['ec_flight_bookings']['nganluong_code'])){
            $booking->nganluong_code     = get_payment_link();
            $booking->nganluong_datepaid = date('Y-m-d H:i:s');
        }
        $booking->save();

        // Save journeys
        foreach ($params['ec_booking_itineraries'] as $i) {
            $itinerary = BeanFactory::newBean("EC_Booking_Itineraries");
            foreach ($i as $key => $value) {
                $itinerary->$key = $value;
                $itinerary->booking_id = $booking->id;
                $itinerary->update_modified_by  = false;
                $itinerary->set_created_by      = false;
                $itinerary->created_by          = $user_id;
                $itinerary->modified_user_id    = $user_id;
                $itinerary->save();
            }
        }

        // Save passengers
        foreach ($params['ec_booking_passengers'] as $p) {
            $pass = BeanFactory::newBean("EC_Booking_Passengers");
            foreach ($p as $key => $value) {
                $pass->$key = $value;
                $pass->booking_id = $booking->id;
                $pass->update_modified_by   = false;
                $pass->set_created_by       = false;
                $pass->created_by           = $user_id;
                $pass->modified_user_id     = $user_id;
                $pass->save();
            }
        }

        // Save details
        foreach ($params['ec_booking_details'] as $d) {
            $detail = BeanFactory::newBean("EC_Booking_Details");
            foreach ($d as $key => $value) {
                $detail->$key = $value;
                $detail->booking_id = $booking->id;
                $detail->update_modified_by = false;
                $detail->set_created_by     = false;
                $detail->created_by         = $user_id;
                $detail->modified_user_id   = $user_id;
                $detail->save();
            }
        }

        // Return
        $data = [
            'booking_id'        => $booking->id,
            'booking_name'      => $booking->name,
            'subtotal_amount'   => $booking->subtotal_amount,
            'luggage_fee'       => $booking->luggage_fee,
            'total_amount'      => $booking->total_amount
        ];

        return $response->withJson([
            'error' => false,
            'message' => "Success",
            'data' => $data,
        ], 201);
    }

    /************  CALL  ************/
    public function save_call(Request $request, Response $response, array $args)
    {
        $params = (array)$request->getParsedBody();

        $call_id        = isset($params['call_id']) ? global_test_input($params['call_id']) : '';
        $call_direction = isset($params['call_direction']) ? global_test_input($params['call_direction']) : '';

        // Xử lý cuộc gọi đến thiếu số 0
        if(isset($params['call_from']) && $call_direction == 'inbound' && strlen($params['call_from']) < 10 && substr($params['call_from'], 0, 1) != 0){
            $call_from      = '0'.trim($params['call_from']);
        } else {
            $call_from      = isset($params['call_from']) ? trim($params['call_from']) : '';
        }

        $call_to        = isset($params['call_to']) ? trim($params['call_to']) : '';
        $call_start     = isset($params['call_start']) ? global_test_input($params['call_start']) : '';
        $call_duration  = isset($params['call_duration']) ? global_test_input($params['call_duration']) : '';
        $record_file    = isset($params['record_file']) ? global_test_input($params['record_file']) : '';

        $where = '';
        $number = $call_direction == 'inbound' ? $call_from : $call_to;
        if (strlen($number) < 15) $where = 'phone_mobile = "' . $number . '"';
        else $where = 'zalo_id = "' . $number . '"';
        
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
            $call->parent_type  = 'Contacts';
            $call->parent_id    = $row['id'];
        }
        $call->call_id   = $call_id;
        $call->call_from = $call_from;
        $call->call_to   = $call_to;
        $call->call_type = strlen($number) < 15 ? 'phone' : 'zalo';

        if ($call_direction == 'internal') {
            $call->direction = 'internal';
        }
        else if ($call_direction == 'outbound') {
            $call->direction = 'outbound';
        }
        else if ($call_direction == 'inbound' && isSpamPhone($call_from) && strlen($call_from) < 15) {
            $call->direction = 'spam';
        }
        else if ($call_direction == 'inbound' && $params['call_talk'] == 0) {
            // Check file json
            $json_blacklist = get_blacklist_phone();

            if(!empty($json_blacklist)) {
                $arr_phone_blacklist = json_decode($json_blacklist, true);
                if(in_array(trim($call_from), $arr_phone_blacklist)) {
                    $call->direction = 'spam';
                }
            }

            if($call->direction != 'spam') {
                // Lịch sử cuộc gọi có số lượng cuộc gọi nhỡ > 3 và call_duration AVG < 8s thì đánh dấu số đó là SPAM
                $sql_spam = 'SELECT 
                                COUNT(*) AS count_spam,
                                AVG(JSON_EXTRACT(log, "$.call_duration")) AS avg_duration
                            FROM calls
                            WHERE call_from = "'.trim($call_from).'"
                                AND direction = "missed"
                                AND deleted = 0
                                AND STR_TO_DATE(date_start, "%d-%m-%Y %H:%i:%s") >= DATE_SUB(NOW(), INTERVAL 48 HOUR)';
    
                $res_spam = $db->query($sql_spam);
                $row_spam = $db->fetchByAssoc($res_spam);
                if($row_spam['count_spam'] > 3 && $row_spam['avg_duration'] < 8){
                    $call->direction = 'spam';

                    // Update số đó vào file JSON
                    add_blacklist_phone($call_from);
                }
                else {
                    $call->direction = 'missed';
                }
            }

        }
        else if ($call_direction == 'inbound' && $params['call_talk'] < 6) {
            $call->direction = 'suddenly';
        }
        else {
            $call->direction = $call_direction;
        }

        // Bổ sung assigned_user_id cho cuộc gọi đi
        if ($call_direction == 'outbound') {
            $call->assigned_user_id = custom_get_sip_number($call_from);
        }
        $call->date_start   = date('d-m-Y H:i:s', strtotime($call_start));
        $call->date_end     = date('d-m-Y H:i:s', strtotime($call_start) + $call_duration);
        $call->status       = 'new';
        $call->log          = json_encode($params);
        $call->record_file  = $record_file;
        $call->save();

        if (!empty($call->id)) {
            return json_encode([
                'error' => false,
                'message' => "Success",
            ]);
        } else {
            return json_encode([
                'error' => true,
                'message' => "Failed",
            ]);
        }
    }

    /************  VOUCHER  ************/
    public function get_vouchers(Request $request, Response $response, array $args)
    {
        global $db;

        $params = (array)$request->getParsedBody();
        $code_voucher  = isset($params['code_voucher']) ? global_test_input($params['code_voucher']) : '';

        $data = [
            'name' => '',
            'status' => '',
            'validate_from_date' => '',
            'validate_to_date' => '',
            'reduce_amount' => '',
            'condition_voucher' => '',
        ];

        if (strlen($code_voucher) < 3 || strlen($code_voucher) > 12) {
            return $response->withJson([
                'error' => true,
                'message' => "Failed",
                'data' => 'Invalid code voucher. Length of code voucher incorrect.',
            ], 400);
        } else if(strlen($code_voucher) >= 3 && strlen($code_voucher) <= 6){
            $sql = '
                SELECT
                    id,
                    name,
                    status,
                    validate_from_date,
                    validate_to_date,
                    reduce_amount,
                    time_limit,
                    condition_voucher
                FROM ec_vouchers
                WHERE status = "active" 
                AND booking_receive_id = "" 
                AND name LIKE "' . $code_voucher . '%" 
                AND time_limit IS NULL OR TIMESTAMPDIFF(MINUTE, DATE_ADD(time_limit, INTERVAL 7 HOUR), NOW()) > 5
                AND deleted = 0
                ORDER BY RAND()
                LIMIT 1
            ';

            $res    = $db->query($sql);
            $count  = $db->countRows($res);

            if($count != 0){
                while ($row = $db->fetchByAssoc($res)) {
                    // Cập nhật time_limit cho voucher này
                    $sql_update = '
                        UPDATE ec_vouchers
                        SET time_limit = "'.date("Y-m-d H:i:s").'"
                        WHERE id = "'.$row['id'].'"
                    ';
                    $db->query($sql_update);

                    $data = [
                        'id' => !is_null($row['id']) ? $row['id'] : '',
                        'name' => !is_null($row['name']) ? $row['name'] : '',
                        'status' => !is_null($row['status']) ? $row['status'] : '',
                        'validate_from_date' => !is_null($row['validate_from_date']) ? $row['validate_from_date'] : '',
                        'validate_to_date' => !is_null($row['validate_to_date']) ? $row['validate_to_date'] : '',
                        'reduce_amount' => !is_null($row['reduce_amount']) ? $row['reduce_amount'] : '',
                        'condition_voucher' => !is_null($row['condition_voucher']) ? json_decode(html_entity_decode($row['condition_voucher']), true) : '',
                    ];
                }

            } else {
                return $response->withJson([
                    'error' => true,
                    'message' => "Failed",
                    'data' => 'Invalid code voucher. Voucher has expired.',
                ], 400);
            }

        } else {
            $sql = '
                SELECT
                    id,
                    name,
                    status,
                    validate_from_date,
                    validate_to_date,
                    reduce_amount,
                    condition_voucher
                FROM ec_vouchers
                WHERE name = "' . $code_voucher . '" AND deleted = 0
            ';
    
            $res = $db->query($sql);
            while ($row = $db->fetchByAssoc($res)) {
                $data = [
                    'id' => !is_null($row['id']) ? $row['id'] : '',
                    'name' => !is_null($row['name']) ? $row['name'] : '',
                    'status' => !is_null($row['status']) ? $row['status'] : '',
                    'validate_from_date' => !is_null($row['validate_from_date']) ? $row['validate_from_date'] : '',
                    'validate_to_date' => !is_null($row['validate_to_date']) ? $row['validate_to_date'] : '',
                    'reduce_amount' => !is_null($row['reduce_amount']) ? $row['reduce_amount'] : '',
                    'condition_voucher' => !is_null($row['condition_voucher']) ? json_decode(html_entity_decode($row['condition_voucher']), true) : '',
                ];
            }
        }

        return $response->withJson([
            'error' => false,
            'message' => "Success",
            'data' => $data,
        ], 200);
    }
}
