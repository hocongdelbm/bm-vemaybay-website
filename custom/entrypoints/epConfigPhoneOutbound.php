<?php 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $TOKEN = 'SJKDFHASJKDFGHAJKUDSW38984729384234B23J4G2H3J4GBHMNBCVMNbdFU4G0';
    
    $JSON_DATA  = file_get_contents('php://input');
    $params     = json_decode($JSON_DATA, true);
    $token      = isset($params['token']) ? trim($params['token']) : '';
    $ip         = get_ip_address_from_client();

    if($token === $TOKEN){
        $IP_WHITELIST = [
            '14.161.31.237', //LBM
            '157.119.248.142', //td timchuyenbay

            // ip anh Tiên
            '103.72.98.73',
        ];

        if(in_array($ip, $IP_WHITELIST)) {
            // LIST PHONE
            $list_phone = [
                'viettel' => [
                    0 => [
                        'phone' => '0964031020',
                        'address' => '103.232.121.103:55000',
                    ],
                    1 => [
                        'phone' => '0983171970',
                        'address' => '103.232.121.103:55000',
                    ],
                    2 => [
                        'phone' => '0962768782',
                        'address' => '103.232.121.103:55000',
                    ],
                    3 => [
                        'phone' => '0963323407',
                        'address' => '103.232.121.103:55000',
                    ],
                    4 => [
                        'phone' => '0963498793',
                        'address' => '103.232.121.103:55000',
                    ],
                    5 => [
                        'phone' => '0963678130',
                        'address' => '103.232.121.103:55000',
                    ],
                    6 => [
                        'phone' => '0963986905',
                        'address' => '103.232.121.103:55000',
                    ],
                    7 => [
                        'phone' => '0963987527',
                        'address' => '103.232.121.103:55000',
                    ],
                    8 => [
                        'phone' => '0964031020',
                        'address' => '103.232.121.103:55000',
                    ],
                    9 => [
                        'phone' => '0964359785',
                        'address' => '103.232.121.103:55000',
                    ],
                ],
                'mobiphone' => [
                    0 => [
                        'phone' => '0933296508',
                        'address' => '103.232.121.103:55000',
                    ],
                    1 => [
                        'phone' => '0933625233',
                        'address' => '103.199.78.74:65000',
                    ],
                    2 => [
                        'phone' => '0933799860',
                        'address' => '103.199.78.74:65000',
                    ],
                    3 => [
                        'phone' => '0933026416',
                        'address' => '103.232.121.103:55000',
                    ],
                    4 => [
                        'phone' => '0933297608',
                        'address' => '103.232.121.103:55000',
                    ],
                    5 => [
                        'phone' => '0933611306',
                        'address' => '103.232.121.103:55000',
                    ],
                    6 => [
                        'phone' => '0937451098',
                        'address' => '103.232.121.103:55000',
                    ],
                    7 => [
                        'phone' => '0937523198',
                        'address' => '103.232.121.103:55000',
                    ],
                ],
                'vinaphone' => [
                    0 => [
                        'phone' => '0913030802',
                        'address' => '14.238.2.146:5060',
                    ],
                    1 => [
                        'phone' => '0918038348',
                        'address' => '103.232.121.103:55000',
                    ],
                    2 => [
                        'phone' => '0919018102',
                        'address' => '103.232.121.103:55000',
                    ],
                ]
            ];

            $response['success'] = array(
                'code' => 200,
                'title' => 'Success',
                'list_phone' => $list_phone
            );
            echo json_encode($response);
        } else {
            $response['fail'] = array(
                'code' => 403,
                'title' => 'Authen Failed',
            );
            echo json_encode($response);
        }
    } else {
        $response['fail'] = array(
            'code' => 400,
            'title' => 'Bad request',
        );
        echo json_encode($response);
    }
}