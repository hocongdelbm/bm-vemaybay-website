<?php
global $db, $sugar_config;
use BeanFactory;

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

            // SAVE CALL
            $sql = 'SELECT id FROM contacts WHERE phone_mobile = "' . $call_to . '" AND deleted = 0 LIMIT 1';
            $res = $db->query($sql);
            $row = $db->fetchByAssoc($res);

            $call                    = BeanFactory::newBean("Calls");
            $call->new_with_id       = true;
            $call->id                = $uuid;
            if (!empty($row['id'])) { 
                $call->parent_type  = 'Contacts';
                $call->parent_id    = $row['id'];
            }
            $call->call_id           = $call_id;
            $call->direction         = $direction;
            $call->call_from         = $call_from;
            $call->call_to           = $call_to;
            $call->is_success        = ($is_take_care) ? 1 : 0;
            $call->status            = ($is_take_care) ? 'done' : 'new';
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
                // 'ip_client' => $ip
            );
            echo json_encode($response);
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
