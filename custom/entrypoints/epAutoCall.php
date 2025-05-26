<?php
global $db, $sugar_config;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $TOKEN      = 'ASJHDGAJHSDGASJHDGJAHSGDJHASGDJHASSADGJHASGDHAJSDJHYJSDVFJHSDFGBASJH';

    $JSON_DATA  = file_get_contents('php://input');
    $params     = json_decode(html_entity_decode($JSON_DATA), true);
    $token      = isset($params['token']) ? global_test_input($params['token']) : '';
    $ip         = get_ip_address_from_client();

    if (strtoupper($token) === strtoupper($TOKEN)) {
        $IP_WHITELIST = [
            $sugar_config['postgreconfig']['ip'], //pbx
            '14.161.31.237', //LBM
        ];

        if (in_array($ip, $IP_WHITELIST)) {
            $call_from      = isset($params['call_from']) ? global_test_input($params['call_from']) : '';
            $call_to        = isset($params['call_to']) ? global_test_input($params['call_to']) : '';
            $direction      = isset($params['direction']) ? global_test_input($params['direction']) : 'outbound';
            $uuid           = isset($params['uuid']) ? global_test_input($params['uuid']) : '';
            $call_id        = isset($params['call_id']) ? global_test_input($params['call_id']) : '';
            $is_take_care   = isset($params['is_take_care']) && $params['is_take_care'] === 'true';
            $log = [];
            $result = '';

            if($is_take_care){
                $messages = "- SĐT: <b>" . $call_to . "</b>\n" .
                            "<pre>[INFO]: Khách hàng đang quan tâm dịch vụ. Vui lòng liên hệ lại! ".json_encode($params)."</pre>";
                $content = html_entity_decode($messages, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
                $result = sendTelegramWarningSystem(
                    // $result = sendTeleConfirmCallSales(
                    json_encode(array(
                        'text' => $content,
                        'parse_mode' => 'HTML',
                    ), JSON_UNESCAPED_UNICODE),
                );
            }

            // if (!empty($uuid) || !empty($call_id)) {
            //     $attempts = 0;
            //     $max_attempts = 3;
            //     $sleep_time = 20;
            //     $log_message = '';

            //     do {
            //         $start_time = microtime(true);
            //         $log = json_decode(html_entity_decode(get_log_call($uuid, $call_id)), true);
            //         $end_time = microtime(true);
            //         $execution_time = ($end_time - $start_time) * 1000; // Convert to milliseconds

            //         $attempts++;
            
            //         if (isset($log['error']) && (int)$log['error'] === 1 || isset($log['code']) && (int)$log['code'] !== 200 || isset($log['data']) && empty($log['data'])) {
            //             $log_message = '[' . date('Y-m-d H:i:s', strtotime('+7 hour')) . ']' . ': Lỗi không lấy được log từ TD của ['.$uuid.']. Thử lại lần thứ ' . $attempts . '/' . $max_attempts . "\n";
            //             sleep($sleep_time);
            //         } else {
            //             break; 
            //         }
            //     } while ($attempts < $max_attempts);

            //     if ($attempts === $max_attempts) {
            //         save_log_call($log_message);
            //     }
            // }

            // $call_id        = isset($log['data']) && !empty($log['data']) ? global_test_input($log['data']['call_id']) : $call_id;
            // $direction      = isset($log['data']) && !empty($log['data']) ? global_test_input($log['data']['call_direction']) : $direction;
            // $record_file    = isset($log['data']) && !empty($log['data']) ? global_test_input($log['data']['record_file']) : '';
            // $hangup_cause   = isset($log['data']) && !empty($log['data']) ? global_test_input($log['data']['hangup_cause']) : '';
            // $call_talk      = isset($log['data']) && !empty($log['data']) ? global_test_input($log['data']['call_talk']) : 0;
            // $call_duration  = isset($log['data']) && !empty($log['data']) ? global_test_input($log['data']['call_duration']) : 0;
            // $call_mos       = isset($log['data']) && !empty($log['data']) ? global_test_input($log['data']['call_mos']) : 0;
            // $call_start     = isset($log['data']) && !empty($log['data']) ? global_test_input($log['data']['call_start']) : '';
            // $call_end       = isset($log['data']) && !empty($log['data']) ? global_test_input($log['data']['call_end']) : '';

            $sql = 'SELECT id FROM contacts WHERE phone_mobile = "' . $call_to . '" AND deleted = 0 LIMIT 1';
            $res = $db->query($sql);
            $row = $db->fetchByAssoc($res);

            $call                    = new Call();
            $call->new_with_id       = true;
            $call->id                = create_guid();
            if (!empty($row['id'])) { 
                $call->parent_type  = 'Contacts';
                $call->parent_id    = $row['id'];
            }
            $call->call_id           = $call_id;
            $call->direction         = $direction;
            $call->call_from         = $call_from;
            $call->call_to           = $call_to;
            $call->other_caller      = $call_from;
            // $call->call_from      = (isset($log['data']) && !empty($log['data'])) ? global_test_input($log['data']['call_from']) : $call_from;
            // $call->other_caller   = (isset($log['data']) && !empty($log['data'])) ? global_test_input($log['data']['call_from']) : $call_from;
            // $call->date_start     = !empty($call_start) ? date('d-m-Y H:i:s', strtotime($call_start)) : '';
            // $call->date_end       = !empty($call_end) ? date('d-m-Y H:i:s', strtotime($call_end)) : (!empty($call_start) ? date('d-m-Y H:i:s', strtotime($call_start) + (int)$call_duration) : '');
            // $call->call_to        = (isset($log['data']) && !empty($log['data'])) ? global_test_input($log['data']['call_to']) : $call_to;
            // $call->record_file    = $record_file;
            // $call->call_talk      = (int)$call_talk;
            // $call->call_duration  = (int)$call_duration;
            // $call->call_mos       = $call_mos;
            // $call->hangup_cause   = (isset($log['data']) && !empty($log['data'])) ? $call->determineHangupCause($log['data']) : null;
            // $call->call_wait      = (isset($log['data']) && !empty($log['data'])) ? (int)calculateWaitTime($log['data']) : 0;
            // $call->is_success     = ($is_take_care || (int)$call_talk > 0) ? 1 : 0;
            // $call->status         = ($is_take_care || (int)$call_talk > 0) ? 'done' : 'new';
            // $call->call_reason    = ($is_take_care || (int)$call_talk > 0) ? 'out_interest' : 'out_no_response';
            // $call->log            = (isset($log['data']) && !empty($log['data'])) ? json_encode(array_merge($params, $log['data'])) : json_encode($params);
            $call->status            = 'new';
            $call->call_type         = 'phone';
            $call->type_call_sources = 'autocall';
            $call->description       = '[Auto]: Cuộc gọi tự động từ hệ thống.';
            $call->assigned_user_id  = 'e3bbb3e5-6660-0bf7-8976-54869c4ee609'; //ksnb
            $call->log               = json_encode($params);
            $call->save();

            $response['success'] = array(
                'code' => 200,
                'title' => 'Success',
                'message' => $result,
            );
            echo json_encode($response);
            exit;
        } else {
            $response['fail'] = array(
                'code' => 403,
                'title' => 'Authen Failed',
            );
            echo json_encode($response);
            exit;
        }
    } else {
        $response['fail'] = array(
            'code' => 400,
            'title' => 'Bad request',
        );
        echo json_encode($response);
        exit;
    }
}
