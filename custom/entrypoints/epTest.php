<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $TOKEN = 'SJKDFHASJKDFGHAJKUDSW38984729384234B23J4G2H3J4GBHMNBCVMNbdFU4G0';

    $JSON_DATA  = file_get_contents('php://input');
    $params     = json_decode($JSON_DATA, true);
    $token      = isset($params['token']) ? trim($params['token']) : '';
    $ip         = get_ip_address_from_client();

    if ($token === $TOKEN) {
        $list_phone = [];

        $pbx = BeanFactory::getBean('Calls');
        $list_phone_round_robin = $pbx->get_list_phone_pbx('', 1);

        if (!empty($list_phone_round_robin) && is_array($list_phone_round_robin)) {
            foreach ($list_phone_round_robin as $network_provider => $phones) {
                $list_phone[strtolower($network_provider)] = array_map(function ($phone) {
                    return [
                        'phone' => trim($phone['name']),
                        'address' => trim($phone['proxy']),
                    ];
                }, $phones);
            }
        }

        $response['success'] = array(
            'code' => 200,
            'title' => 'Success',
            'list_phone' => $list_phone
        );
        echo json_encode($response);
        exit;
    } else {
        $response['fail'] = array(
            'code' => 400,
            'title' => 'Bad request',
        );
        echo json_encode($response);
        exit;
    }
}
