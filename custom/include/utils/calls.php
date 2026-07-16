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
    global $db, $sugar_config, $timedate;
    $domain = $sugar_config['postgreconfig']['domain_name'] ?? '';

    if (empty($agent) || empty($status) || empty($domain)) {
        $GLOBALS['log']->error("agent_change_status bad request: agent={$agent}, status={$status}, domain={$domain}");
        return false;
    }

    $token  = 'sdjfhsgaksuegrqw38463784672793746rwadjksfgha3e467dhcauw4y5t783yr';
    $url   = "https://{$domain}/agent_status/change_status_v1.php";

    try {
        $curl = curl_init($url);

        if ($curl === false) {
            $GLOBALS['log']->error("agent_change_status: cURL failed to initialize");
            return false;
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL             => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 6,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_CUSTOMREQUEST   => 'POST',
            CURLOPT_POSTFIELDS     => [
                'agent'  => "{$agent}@{$domain}",
                'status' => $status,
                'token'  => $token,
            ],
        ));

        $json  = curl_exec($curl);
        $httpcode  = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($json === false || $httpcode !== 200) {
            $GLOBALS['log']->error("Agent status CURL error: httpcode={$httpcode}, curlError=" . $curlError . ", body=" . $json);
            return false;
        }

        $arr = json_decode($json, true);
        if (empty($arr['success']['code']) || $arr['success']['code'] != 200) {
            $GLOBALS['log']->error("Agent status change rejected by PBX: " . $json);
            return false;
        }

        if ($httpcode == 200 && $arr['success']['code'] == 200) {
            $agent_quoted  = $db->quote($agent);
            $status_quoted = $db->quote($status);
            $sql_as = 'UPDATE users
                       SET agent_status = "' . $status_quoted . '"
                       WHERE td_sip = "' . $agent_quoted . '"
                       AND deleted = 0';

            $result_sql_as = $db->query($sql_as);
            $GLOBALS['log']->debug("agent_change_status: updated users.agent_status, agent={$agent}, status={$status}, affected_rows=" . ($result_sql_as ? $db->getAffectedRowCount($result_sql_as) : 'query_failed'));

            if ($result_sql_as) {
                $sip_number     = custom_get_sip_number($agent);
                $status_value   = $status == 'Available' ? 1 : ($status == 'On Break' ? 2 : 0);

                // Luôn cập nhật last_online (kể cả khi Logged Out) để không giữ lại
                // mốc last_online cũ/nhỏ -> tránh việc user online lại chen lên vị trí 1.
                $last_online_update = ', last_online = "' . $timedate->nowDb() . '"';

                $GLOBALS['log']->debug("agent_change_status: custom_get_sip_number(agent={$agent}) resolved sip_number=" . var_export($sip_number, true));

                if ($sip_number) {
                    $today_vn = (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d');
                    $sql_online =
                        "UPDATE ec_online_report
                        SET status = $status_value
                            $last_online_update
                        WHERE assigned_user_id = '$sip_number'
                            AND DATE(DATE_ADD(date_entered, INTERVAL 7 HOUR)) = '$today_vn'
                            AND deleted = 0";

                    $result_sql_online = $db->query($sql_online);
                    $GLOBALS['log']->debug("agent_change_status: updated ec_online_report, sip_number={$sip_number}, date={$today_vn}, affected_rows=" . ($result_sql_online ? $db->getAffectedRowCount($result_sql_online) : 'query_failed'));

                    if ($result_sql_online) {
                        $busy = ($status == 'Available') ? 0 : 1;
                        $current_datetime_vn = (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s');

                        content_log($sip_number, $current_datetime_vn, $busy);
                    }
                }
            }
        } else {
            $GLOBALS['log']->debug("agent_change_status: skipped users/ec_online_report update, httpcode={$httpcode}, arr_code=" . ($arr['success']['code'] ?? 'n/a'));
        }

        return true;
    } catch (Exception $e) {
        $GLOBALS['log']->error("agent_change_status error: " . $e->getCode() . ': ' . $e->getMessage());
        return false;
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

function getInfoCallSource($call_to)
{
    $result = [
        'phone' => $call_to ?? '',
        'format_phone' => '',
        'website' => '',
        'label' => '',
        'brand_name' => '',
        'network_provider' => '',
    ];
    if (!$call_to || empty($call_to)) return $result;

    global $db;
    $sql = "SELECT name, format_phone, website, label, brand_name, network_provider
        FROM ec_outbound_phone
        WHERE (name = '$call_to' OR mapping = '$call_to')
            AND deleted = 0
        LIMIT 1";
    $res = $db->query($sql);
    $total_phone = $db->countRows($res);
    if ($total_phone > 0) {
        $row = $db->fetchByAssoc($res);
        $result = [
            'phone'             => $row['name'] ?? $call_to,
            'format_phone'      => $row['format_phone'],
            'website'           => $row['website'],
            'label'             => $row['label'],
            'brand_name'        => $row['brand_name'],
            'network_provider'  => $row['network_provider'],
        ];
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

function proposeCallImprovementStrategy($asr)
{
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


/**
 * Format seconds to -> "d ngày HH:MM:SS"
 */
function seconds_to_ngay_hms($sec)
{
    if (!is_numeric($sec)) return '0 ngày 00:00:00';

    $sign = ($sec < 0) ? '-' : '';
    $s = (int) round(abs($sec));

    $days = intdiv($s, 86400);
    $s -= $days * 86400;
    $hrs  = intdiv($s, 3600);
    $s -= $hrs * 3600;
    $min  = intdiv($s, 60);
    $s -= $min * 60;
    $sec  = $s;

    return $sign . $days . ' ngày ' . sprintf('%02d:%02d:%02d', $hrs, $min, $sec);
}
