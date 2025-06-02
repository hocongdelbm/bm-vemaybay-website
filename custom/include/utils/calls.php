<?php

/**
 * Tính thời gian chờ của cuộc gọi
 * @param array $log_call
 * @return int
 */
function calculateWaitTime($log_call)
{
    if ($log_call['call_talk'] == 0) {
        return $log_call['call_duration'] ?? 0;
    }

    // Nếu cuộc gọi có call_answer = 0 thì dùng call_wait
    if (isset($log_call['call_answer']) && $log_call['call_answer'] == 0) {
        return $log_call['call_wait'] ?? 0;
    }

    // Nếu có call_accepted và khác rỗng, tính thời gian chờ dựa trên call_start
    if (isset($log_call['call_accepted'], $log_call['call_start'])) {
        return (int)strtotime($log_call['call_accepted']) - (int)strtotime($log_call['call_start']);
    }

    return $log_call['call_wait'] ?? 0;
}

/**
 * Lấy datetime của khi cuộc gọi được chấp nhận
 *
 * @param array $log_call
 * @return string
 */
function getCallAcceptDatetime($log_call)
{
    $datetime_accept = '';

    if (isset($log_call['call_accepted']) && !empty($log_call['call_accepted']) && strtotime($log_call['call_accepted']) != strtotime($log_call['call_start'])) {
        $datetime_accept .= date('d-m-Y H:i:s', strtotime($log_call['call_accepted']));
    } else {
        if ($log_call['call_talk'] != 0) {
            $datetime_accept .= date('d-m-Y H:i:s', strtotime($log_call['call_start']) + (int)$log_call['call_wait']);
        }
    }

    return $datetime_accept;
}

/**
 * Writes a backup of call information to a JSON file.
 *
 * This function appends the provided JSON data to a backup file
 * named with the current date in the 'secure_sessions/backup_log_calls' directory.
 * If the file or directory does not exist, they are created.
 *
 * @param string $json JSON encoded string containing call information.
 * @return bool Returns true if the operation is successful, false if the input is empty.
 */

function write_file_backup_log_calls($json)
{
    if (empty($json)) return false;

    $file_name = "secure_sessions/backup_log_calls/" . str_replace('-', '_', date('d-m-Y') . '.json');

    // Kiểm tra xem tệp có tồn tại không
    if (!file_exists($file_name)) {
        $dir_name = dirname($file_name);
        if (!is_dir($dir_name)) {
            mkdir($dir_name, 0777, true);
        }
        file_put_contents($file_name, json_encode([]));
    }

    $file_content = file_get_contents($file_name);
    $json_data = json_decode($file_content, true);

    if (!is_array($json_data)) {
        $json_data = [];
    }

    $json_data[] = json_decode($json, true);

    file_put_contents($file_name, json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    return true;
}


/**
 * Changes the status of an agent in the FusionPBX system
 *
 * @param string $agent The agent's username
 * @param string $status The status to change to. Values are "Available", "On Break", "Logged Out", or "Offline"
 *
 * @return void
 */
function agent_change_status($agent, $status)
{
    global $db, $sugar_config;
    $domain = $sugar_config['postgreconfig']['domain_name'] ?? '';

    // 0: Offline
    // 1: Online
    // 2: Busy
    $array_admin = [
        '168889bb-54c2-59c7-8b3f-649102530d3c', //hungnh
        '622ecf27-f729-7187-7e27-6520e0dab882', //quangnd
        '1', //DDuc
    ];

    global $db, $current_user;
    if (empty($agent) || empty($status) || empty($domain)) {
        $response['success'] = array(
            'code' => 400,
            'title' => 'agent status bad request',
        );
        echo json_encode($response);
        exit();
    }

    $agent_domain  = $agent . '@' . $domain;
    $token  = 'sdjfhsgaksuegrqw38463784672793746rwadjksfgha3e467dhcauw4y5t783yr';
    $body_request = array(
        'agent' => $agent_domain,
        'status' => $status,
        'token' => $token,
    );

    try {
        $curl = curl_init();
        if ($curl === false) {
            echo json_encode(array('error' => 1, 'httpcode' => 500, 'message' => 'cURL Failed to initialize'));
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL             => "https://" . $domain . "/agent_status/change_status.php",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => false, // Use at localhost
            CURLOPT_SSL_VERIFYPEER => false, // Use at localhost
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_CUSTOMREQUEST   => 'POST',
            CURLOPT_POSTFIELDS      => $body_request,
        ));

        $json = curl_exec($curl);
        $httpcode   = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $arr = json_decode($json, true);

        if ($httpcode == 200 && $arr['success']['code'] == 200) {
            $sql_as = 'UPDATE users
                       SET agent_status = "' . $status . '"
                       WHERE td_sip = "' . $agent . '"
                       AND deleted = 0';

            $result_sql_as = $db->query($sql_as);
            if ($result_sql_as) {
                $timestamp_now  = date('Y-m-d H:i:s');
                $sip_number     = custom_get_sip_number($agent);
                $status_value   = $status == 'Available' ? 1 : ($status == 'On Break' ? 2 : 0);

                $where_sql = '';
                if ($status != 'Logged Out') {
                    $where_sql .= ', last_online = "' . $timestamp_now . '"';
                }

                if ($sip_number) {
                    $sql_online = '
                        UPDATE ec_online_report 
                        SET status = ' . $status_value . ' ' . $where_sql . '
                        WHERE assigned_user_id = "' . $sip_number . '"
                        AND DATE_FORMAT(DATE_ADD(date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") = "' . date('Y-m-d') . '"
                        AND deleted = 0
                    ';
                    $result_sql_online = $db->query($sql_online);

                    if ($result_sql_online) {
                        $busy           = ($status == 'Available') ? 0 : 1;
                        $time_current   = date('Y-m-d H:i:s', strtotime('+7 hour'));
                        if (!in_array($sip_number, $array_admin)) {
                            content_log($sip_number, $time_current, $busy);
                        }
                    }
                }
            }
        }
    } catch (Exception $e) {
        return json_encode(array('error' => 1, 'httpcode' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage()));
    }
}

/**
 * Checks if a given phone number is considered spam.
 *
 * A phone number is considered spam if it has a prefix listed in the
 * predefined array of known spam prefixes and contains three consecutive
 * identical digits in the rest of the number.
 *
 * @param string $phone The phone number to check.
 * @return bool Returns true if the phone number is spam, otherwise false.
 */

function isSpamPhone($phone)
{
    $top_phone = array('028', '024', '021', '022', '029', '195', '252', '247', '231', '371', '232', '224', '027', '020');
    $sub_phone = substr(trim($phone), 0, 3);

    if (in_array($sub_phone, $top_phone)) {
        $digits = str_split($phone);

        for ($i = 3; $i < count($digits) - 3; $i++) {
            if ($digits[$i] == $digits[$i + 1] && $digits[$i] == $digits[$i + 2]) {
                return true;
            }
        }
    }

    return false;
}


/**
 * Adds a given phone number to the blacklist of spam phone numbers.
 *
 * @param string $phone The phone number to add to the blacklist.
 * @return bool Returns true if the phone number was added successfully, otherwise false.
 */
function add_blacklist_phone($phone)
{
    if (empty($phone)) return false;

    $json = get_blacklist_phone();
    if (empty($json)) {
        $arr = [$phone];
    } else {
        $arr = json_decode($json, true);
        if (array_search($phone, $arr) === false) {
            $arr[] = $phone;
        }
    }

    $file_name = 'custom/jssip_webrtc/blacklist.json';
    $myfile = fopen($file_name, "w") or die("Error something !!!");
    fwrite($myfile, json_encode($arr));
    fclose($myfile);
}

/**
 * Retrieves the list of blacklisted phone numbers.
 *
 * This function checks for the existence of a JSON file containing
 * blacklisted phone numbers. If the file exists, it reads and returns
 * the JSON content. If the file does not exist, it returns an empty string.
 *
 * @return string The JSON content of blacklisted phone numbers or an empty string if the file does not exist.
 */

function get_blacklist_phone()
{
    // Check file json
    $file_path = 'custom/jssip_webrtc/blacklist.json';

    if (file_exists($file_path)) {
        $json = file_get_contents($file_path);
        return $json;
    }

    return '';
}

/**
 * Gets the call source from a given phone number.
 *
 * This function takes a phone number and returns the corresponding call source.
 * The call source is determined by a predefined mapping of phone numbers to
 * their respective call sources.
 *
 * @param string $call_to The phone number to get the call source for.
 * @return string The call source for the given phone number.
 */
function getCallSource($call_to)
{
    $call_to = str_replace(" ", "", trim($call_to));
    $call_sources = '';

    switch ($call_to) {
        case '02866509900':
            $call_sources = 'sanvemaybay.com.vn';
            break;
        case '0911236600':
        case '01388506538':
            $call_sources = 'Laptop Dell';
            break;
        case '02873001886':
            $call_sources = 'suatuoiuc.vn';
            break;
        // Zalo
        case '2941581384627345950101':
            $call_sources = 'Zalo nội địa';
            break;
        case '2941581384627345950102':
            $call_sources = 'Zalo quốc tế';
            break;
        case '2941581384627345950103':
            $call_sources = 'Khiếu nại';
            break;
        default:
            $call_sources = 'timchuyenbay.com';
    }

    return $call_sources;
}

function getInfoCallSource($call_to){
    global $db;

    $result = array(
        'phone' => $call_to ?? '',
        'format_phone' => '',
        'website' => '',
        'label' => '',
        'brand_name' => '',
        'network_provider' => '',
    );

    if (empty($call_to)) {
        return $result;
    }

    $sql = 'SELECT * FROM ec_outbound_phone WHERE name = "' . $call_to . '" AND deleted = 0 LIMIT 1';
    $res = $db->query($sql);
    $total_phone = $db->countRows($res);
    if ($total_phone > 0) {
        $row = $db->fetchByAssoc($res);

        $result = array(
            'phone' => $row['name'] ?? $call_to,
            'format_phone' => $row['format_phone'],
            'website' => $row['website'],
            'label' => $row['label'],
            'brand_name' => $row['brand_name'],
            'network_provider' => $row['network_provider'],
        );
    }

    return $result;
}

/**
 * @param float $mos Giá trị của Mean Opinion Score (MOS), dao động từ 1 đến 5.
 * @return string Nhãn để mô tả chất lượng của cuộc gọi.
 */

function getMOSLabel($mos)
{
    if (!is_numeric($mos) || $mos < 0 || $mos > 5) {
        return 'Giá trị MOS không hợp lệ.';
    }

    if ($mos >= 4.5 && $mos <= 5.0) {
        $label = 'Rất tốt';
        $description = 'Chất lượng âm thanh rõ ràng, không có méo tiếng hay gián đoạn.';
    } elseif ($mos >= 4.0 && $mos < 4.5) {
        $label = 'Tốt';
        $description = 'Chất lượng ổn định, chỉ có biến đổi nhỏ về âm thanh.';
    } elseif ($mos >= 3.5 && $mos < 4.0) {
        $label = 'Chấp nhận được';
        $description = 'Có biến dạng âm thanh nhưng không ảnh hưởng đáng kể.';
    } elseif ($mos >= 3.0 && $mos < 3.5) {
        $label = 'Trung bình';
        $description = 'Chất lượng âm thanh không ổn định, có hiện tượng gián đoạn.';
    } elseif ($mos >= 2.5 && $mos < 3.0) {
        $label = 'Kém';
        $description = 'Méo tiếng nhiều, tín hiệu không rõ ràng.';
    } else { // $mos < 2.5
        $label = 'Rất kém';
        $description = 'Chất lượng quá thấp, khó có thể nhận diện nội dung.';
    }

    return "$label: $description (MOS: $mos)";
}

/**
 * Gets the meaning of a call failed cause.
 *
 * This function takes a cause from the call failed cause list and returns the
 * corresponding meaning of the cause.
 *
 * @param string $cause The call failed cause to get the meaning for.
 * @return string The meaning of the call failed cause.
 */
function getCallFailedCauseMeaning($cause)
{
    $meanings = [
        'USER_NOT_REGISTERED' => 'Người dùng SIP chưa đăng ký với tổng đài',
        'NO_ANSWER' => 'Không ai trả lời cuộc gọi',
        'USER_BUSY' => 'Người nhận đang bận',
        'CALL_REJECTED' => 'Cuộc gọi bị từ chối',
        'NO_USER_RESPONSE' => 'Người nhận không phản hồi',
        'NORMAL_CLEARING' => 'Cuộc gọi kết thúc bình thường',
        'SUBSCRIBER_ABSENT' => 'Thuê bao vắng mặt hoặc tắt máy',
        'DESTINATION_OUT_OF_ORDER' => 'Điểm đến không hoạt động',
        'NETWORK_OUT_OF_ORDER' => 'Mạng bị lỗi',
        'PROTOCOL_ERROR' => 'Lỗi giao thức SIP',
        'UNALLOCATED_NUMBER' => 'Số điện thoại không hợp lệ hoặc không tồn tại'
    ];

    return $meanings[strtoupper($cause)] ?? 'Không xác định';
}


function returnStragtegyCallSales($strategy) {}

function proposeCallImprovementStrategy($asr)
{
    // $strategies = [
    //     'high' => [
    //         "Tỷ lệ bắt máy cao! Khách hàng phản hồi tốt. Duy trì khung giờ gọi hiện tại vì nó đang hiệu quả.",
    //         "Tỷ lệ bắt máy cao! Khách hàng phản hồi tốt. Tiếp tục cá nhân hóa lời chào để duy trì sự thân thiện.",
    //         "Tỷ lệ bắt máy cao! Khách hàng phản hồi tốt. Tăng tần suất gọi cho nhóm khách hàng tiềm năng.",
    //         "Tỷ lệ bắt máy cao! Khách hàng phản hồi tốt. Thử mở rộng danh sách khách hàng tương tự nhóm hiện tại.",
    //         "Tỷ lệ bắt máy cao! Khách hàng phản hồi tốt. Ghi nhận feedback từ khách hàng để cải thiện thêm."
    //     ],
    //     'good' => [
    //         "Tỷ lệ nghe máy khá ổn, nhưng vẫn có thể cải thiện. Thử tối ưu hóa thời gian gọi, hoặc cách mở đầu cuộc gọi để tăng tỷ lệ phản hồi.",
    //         "Tỷ lệ nghe máy khá ổn, nhưng vẫn có thể cải thiện. Điều chỉnh nhịp điệu giọng nói để tạo sự thu hút hơn.",
    //         "Tỷ lệ nghe máy khá ổn, nhưng vẫn có thể cải thiện. Tăng cường tương tác với khách hàng để xây dựng lòng tin.",
    //         "Tỷ lệ nghe máy khá ổn, nhưng vẫn có thể cải thiện. Hãy tối ưu cách mở đầu cuộc gọi bằng cách giới thiệu ngắn gọn nhưng hấp dẫn, nhấn mạnh vào giá trị mang lại ngay từ những giây đầu tiên.",
    //         "Tỷ lệ nghe máy khá ổn, nhưng vẫn có thể cải thiện. Nếu khách hàng chưa nghe máy, hãy thử lại vào ngày khác thay vì liên tục gọi trong cùng một ngày."
    //     ],
    //     'average' => [
    //         "Mức trung bình! Có thể khách hàng không quan tâm hoặc thời gian gọi chưa hợp lý. Tránh gọi vào giờ nghỉ trưa (12-13h), thử khung giờ khác như 3-5h chiều.",
    //         "Mức trung bình! Có thể khách hàng không quan tâm hoặc thời gian gọi chưa hợp lý. Thay đổi kịch bản gọi để nhấn mạnh lợi ích cho khách hàng ngay từ đầu.",
    //         "Mức trung bình! Có thể khách hàng không quan tâm hoặc thời gian gọi chưa hợp lý. Giảm tần suất gọi lặp lại để tránh làm phiền khách hàng.",
    //         "Mức trung bình! Có thể khách hàng không quan tâm hoặc thời gian gọi chưa hợp lý. Kiểm tra danh sách khách hàng, đảm bảo số điện thoại không nằm trong danh sách chặn cuộc gọi quảng cáo nhằm liên hệ đúng đối tượng mục tiêu.",
    //         "Mức trung bình! Có thể khách hàng không quan tâm hoặc thời gian gọi chưa hợp lý. Gnhấn mạnh lợi ích cụ thể cho khách hàng ngay trong 10 giây đầu.",
    //     ],
    //     'low' => [
    //         "Khách hàng ít bắt máy! Có thể cách tiếp cận chưa phù hợp. Hãy thử điều chỉnh giọng điệu tự nhiên hơn, tránh đọc kịch bản một cách máy móc.",
    //         "Khách hàng ít bắt máy! Có thể cách tiếp cận chưa phù hợp. Sàng lọc lại danh sách khách hàng để loại bỏ số điện thoại không hoạt động.",
    //         "Khách hàng ít bắt máy! Tối ưu lại nội dung cuộc gọi, nhấn mạnh vào lợi ích, dịch vụ cung cấp cụ thể thay vì chỉ giới thiệu chung chung.",
    //         "Khách hàng ít bắt máy! Có thể khách hàng không nhận diện được số lạ, hãy thử gửi tin nhắn Zalo trước khi gọi để tăng khả năng khách hàng nhận diện và phản hồi.",
    //     ],
    //     'very_low' => [
    //         "Tỷ lệ bắt máy quá thấp! Kiểm tra lại toàn bộ danh sách khách hàng để đảm bảo đúng đối tượng.",
    //         "Tỷ lệ bắt máy quá thấp! Cần xem lại cách tiếp cận mới.",
    //         "Tỷ lệ bắt máy quá thấp! Thử các kênh liên lạc khác để thăm dò họ có quan tâm dịch vụ hay không.",
    //         "Tỷ lệ bắt máy quá thấp! Học hỏi từ các cuộc gọi thành công để tối ưu kịch bản.",
    //         "Tỷ lệ bắt máy quá thấp! Cải thiện giọng điệu và tốc độ nói để tạo cảm giác thân thiện hơn.",
    //         "Tỷ lệ bắt máy quá thấp! Thử kịch bản gọi mới, tập trung vào câu mở đầu ngắn gọn và nhấn mạnh giá trị độc đáo của dịch vụ. Tôi là ai? cung cấp dịch vụ gì?"
    //     ]
    // ];
    $strategies = [
        'high' => [
            "Tỷ lệ bắt máy cao!",
        ],
        'good' => [
            "Tỷ lệ bắt máy ổn!",
        ],
        'average' => [
            "Tỷ lệ bắt máy trung bình!",
        ],
        'low' => [
            "Tỷ lệ bắt máy thấp!",
        ],
        'very_low' => [
            "Tỷ lệ bắt máy quá thấp!",
        ]
    ];

    if ($asr >= 80) {
        $key = 'high';
    } elseif ($asr >= 60) {
        $key = 'good';
    } elseif ($asr >= 50) {
        $key = 'average';
    } elseif ($asr >= 35) {
        $key = 'low';
    } else {
        $key = 'very_low';
    }

    $randomIndex = array_rand($strategies[$key]);
    return $strategies[$key][$randomIndex];
}


function send_callee_autocall($callees)
{

    global $db, $sugar_config;
    $bearer_token  = $sugar_config['postgreconfig']['bearer_token'] ?? '';
    $domain_name = $sugar_config['postgreconfig']['domain_name'] ?? '';

    if (empty($bearer_token) || empty($domain_name)) {
        return json_encode([
            'error' => 1,
            'httpcode' => 400,
            'message' => 'Bad request',
        ]);
    }

    $body_request = array(
        'callee_numbers' => $callees,
        'domain_name' => $domain_name,
    );

    try {
        $curl = curl_init();
        if ($curl === false) {
            return json_encode(['error' => 1, 'httpcode' => 500, 'message' => 'cURL Failed to initialize']);
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL             => "https://$domain_name/apiv1/autocall.php",
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_HTTPHEADER      => [
                "Content-Type: application/json",
                "Authorization: Bearer $bearer_token"
            ],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => false, // Use at localhost
            CURLOPT_SSL_VERIFYPEER => false, // Use at localhost
            CURLOPT_TIMEOUT         => 0,
            CURLOPT_CUSTOMREQUEST   => 'POST',
            CURLOPT_POSTFIELDS      => json_encode($body_request),
        ));

        $json = curl_exec($curl);
        $httpcode   = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $arr = json_decode(html_entity_decode($json), true);

        if ($httpcode === 200 && isset($arr['code']) && (int)$arr['code'] === 200) {
            return json_encode(array('error' => 0, 'response' => $arr));
        } else {
            return json_encode(['error' => 1, 'httpcode' => $httpcode, 'response' => $arr]);
        }
    } catch (Exception $e) {
        return json_encode(array('error' => 1, 'httpcode' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage()));
    }
}


function get_log_call($call_id, $uuid = '')
{

    global $db, $sugar_config;
    $domain_name = $sugar_config['postgreconfig']['domain_name'] ?? 'td.timchuyenbay.net';
    $token  = 'f47a2d3b91e8c0f6b5d44a13c8a7e2dd38f9627aef1b79c452e0ad5e68f3c1db65f8e2a9374d1a26c6f5b8e49b029fd03a4c78b821c1a2fe56d74e9b3a8fc2f';

    $body_request = array(
        'uuid' => $uuid,
        'call_id' => $call_id,
        'token' => $token,
    );

    try {
        $curl = curl_init();
        if ($curl === false) {
            return json_encode(['error' => 1, 'httpcode' => 500, 'message' => 'cURL Failed to initialize']);
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL             => "https://" . $domain_name . "/apiv1/getLogCall.php",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => false, // Use at localhost
            CURLOPT_SSL_VERIFYPEER => false, // Use at localhost
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_CUSTOMREQUEST   => 'POST',
            CURLOPT_POSTFIELDS      => $body_request,
        ));

        $json = curl_exec($curl);
        $httpcode   = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $arr = json_decode(html_entity_decode($json), true);

        if ($httpcode === 200 && isset($arr['code']) && (int)$arr['code'] === 200) {
            return json_encode(array('error' => 0, 'httpcode' => $arr['code'], 'data' => $arr['data']));
        } else {
            return json_encode(['error' => 1, 'httpcode' => $arr['code'], 'data' => $arr]);
        }
    } catch (Exception $e) {
        return json_encode(array('error' => 1, 'httpcode' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage()));
    }
}

function update_log_autocall(){
    global $db;

    $sql = "SELECT log
            FROM calls
            WHERE type_call_sources = 'autocall'
            AND (date_start IS NULL OR date_start = '')
            AND (date_end IS NULL OR date_end = '')
            AND deleted = 0
    ";

    $res = $db->query($sql);
    $total_autocall = $db->countRows($res);
    $index = 0;
    if ($total_autocall > 0) {
        while ($row = $db->fetchByAssoc($res)) {
            $log_call = json_decode(html_entity_decode($row['log']), true);
            if(isset($log_call['call_id']) && !empty($log_call['call_id'])){
                $full_log_json = get_log_call($log_call['call_id'], $log_call['uuid']);
                $full_log = json_decode(html_entity_decode($full_log_json), true);

                if(isset($full_log['error']) && (int)$full_log['error'] === 0 && isset($full_log['data']) && !empty($full_log['data'])){
                    $log = $full_log['data'];
                    $call_id        = isset($log['call_id']) && !empty($log['call_id']) ? global_test_input($log['call_id']) : '';
                    $direction      = isset($log['call_direction']) && !empty($log['call_direction']) ? global_test_input($log['call_direction']) : '';
                    $direction      = strtolower($direction) === 'local' ? 'internal' :$direction;
                    $record_file    = isset($log['record_file']) && !empty($log['record_file']) ? global_test_input($log['record_file']) : '';
                    $hangup_cause   = isset($log['hangup_cause']) && !empty($log['hangup_cause']) ? global_test_input($log['hangup_cause']) : '';
                    $call_talk      = isset($log['call_talk']) && !empty($log['call_talk']) ? global_test_input($log['call_talk']) : 0;
                    $call_duration  = isset($log['call_duration']) && !empty($log['call_duration']) ? global_test_input($log['call_duration']) : 0;
                    $call_mos       = isset($log['call_mos']) && !empty($log['call_mos']) ? global_test_input($log['call_mos']) : 0;
                    $call_start     = isset($log['call_start']) && !empty($log['call_start']) ? global_test_input($log['call_start']) : '';
                    $call_end       = isset($log['call_end']) && !empty($log['call_end']) ? global_test_input($log['call_end']) : '';
                    $call_answer    = isset($log['call_answer']) && !empty($log['call_answer']) ? global_test_input($log['call_answer']) : 0;
                    $call_wait      = isset($log['call_wait']) && !empty($log['call_wait']) ? global_test_input($log['call_wait']) : 0;
                    $call_accepted  = isset($log['call_accepted']) && !empty($log['call_accepted']) ? global_test_input($log['call_accepted']) : '';
                    $is_success     = (int)$call_talk > 0 ? 1 : 0;
                    $call_reason    = isset($full_log['is_take_care']) && $full_log['is_take_care'] ? 'out_interest' : ((int)$call_talk > 0 ? 'out_no_uncomfortable' : 'out_no_response');
                    $status         = (int)$call_talk > 0 ? 'done' : 'new';
                    $call_wait      = (int)calculateWaitTime($log);
                    $hangup_cause   = (new Call())->determineHangupCause($log);

                    if(!empty($call_id)){
                        $sql_update = "UPDATE calls
                                        SET date_start = '$call_start',
                                            date_end = '$call_end',
                                            call_wait = $call_wait,
                                            status = '$status',
                                            direction = '$direction',
                                            call_mos = '$call_mos',
                                            call_duration = $call_duration,
                                            call_talk = $call_talk,
                                            is_success = $is_success,
                                            call_reason = '$call_reason',
                                            hangup_cause = '$hangup_cause',
                                            record_file = '$record_file',
                                            log = '" . json_encode(array_merge($log_call, $log)) . "'
                                        WHERE call_id = '$call_id'
                                        AND deleted = 0";

                        $result_sql = $db->query($sql_update);
                        if (!$result_sql) {
                            $mess_log = '[' . date('Y-m-d H:i:s', strtotime('+7 hour')) . ']: CẬP NHẬT LOG AUTOCALL THẤT BẠI ' . $sql_update;
                            save_log_call($mess_log);

                            // // notify telegram
                            // $messages = "- Call_ID: <b>" . $call_id . "</b>\n" .
                            //         "<pre>[ERROR]: UPDATED AUTOCALL FAILED! ".$sql_update."</pre>";
                            // $content = html_entity_decode($messages, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                            // $result = sendTelegramWarningSystem(
                            //     json_encode(array(
                            //         'text' => $content,
                            //         'parse_mode' => 'HTML',
                            //     ), JSON_UNESCAPED_UNICODE),
                            // );

                            // Notify Mattermost
                            $message = Mattermost::$line_separation;
                            $message .= Mattermost::markdownHeading("[ERROR] Updated autocall failed");
                            $message .= "\n- Call ID: **$call_id**";
                            $message .= "\n- SQL query: **$sql_update**";
                            Mattermost::sendMessage($sugar_config['mattermost']['channel_id_logs'] ?? '', $message);
                        }
                        else {
                            $index++;
                        }
                    }
                }
            }
        }
    } 

    return ((int)$index === (int)$total_autocall) ? true : false;
}


function save_log_call($log_call)
{
    if (!empty($log_call)) {
        $year = date('Y');
        $month = str_pad(date('m'), 2, "0", STR_PAD_LEFT);
        $file_name = "secure_sessions/save_call_logs/$year/$month/" . str_replace('-', '_', date('d-m-Y') . '_log');

        if (!file_exists($file_name)) {
            $dir_name = dirname($file_name);
            if (!is_dir($dir_name)) {
                mkdir($dir_name, 0777, true);
            }
            touch($file_name);
        }

        $myfile = fopen($file_name, "a") or die("Error: Không thể mở file ghi log!");
        fwrite($myfile, $log_call . PHP_EOL);
        fclose($myfile);
        return true;
    }
}

/**
 * Format seconds to time
 * 
 * @param string $seconds
 * @return string
 */
function global_secondsToTimeFormat($seconds)
{
    if (empty($seconds)) {
        return '00:00:00';
    }

    $hours      = floor($seconds / 3600);
    $minutes    = floor(($seconds % 3600) / 60);
    $seconds    = $seconds % 60;

    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
}