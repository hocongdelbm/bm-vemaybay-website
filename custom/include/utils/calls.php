<?php 
/**
 * Tính thời gian chờ của cuộc gọi
 * @param array $log_call
 * @return int
 */
function calculateWaitTime($log_call) {
    if($log_call['call_talk'] == 0) {
        return $log_call['call_duration'] ?? 0;
    }

    // Nếu cuộc gọi có call_answer = 0 thì dùng call_wait
    if (isset($log_call['call_answer']) && $log_call['call_answer'] == 0) {
        return $log_call['call_wait'] ?? 0;
    }

    // Nếu có call_accepted, tính thời gian chờ dựa trên call_start
    if (isset($log_call['call_accepted'], $log_call['call_start'])) {
        return (int)strtotime($log_call['call_accepted']) - (int)strtotime($log_call['call_start']);
    }
}

/**
 * Lấy datetime của khi cuộc gọi được chấp nhận
 *
 * @param array $log_call
 * @return string
 */
function getCallAcceptDatetime($log_call) {
    $datetime_accept = '';

    if (isset($log_call['call_accepted']) && strtotime($log_call['call_accepted']) != strtotime($log_call['call_start'])) {
        $datetime_accept .= date('d-m-Y H:i:s', strtotime($log_call['call_accepted']));
    } else {
        if($log_call['call_talk'] != 0) {
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
    // 0: Offline
    // 1: Online
    // 2: Busy

    $array_admin = [
        '168889bb-54c2-59c7-8b3f-649102530d3c', //hungnh
        '622ecf27-f729-7187-7e27-6520e0dab882', //quangnd
        '1', //DDuc
    ];

    global $db, $current_user;
    if (empty($agent) || empty($status)) {
        $response['success'] = array(
            'code' => 400,
            'title' => 'agent status bad request',
        );
        echo json_encode($response);
        exit();
    }

    $agent_domain  = $agent . '@td.timchuyenbay.net';
    $toten  = 'sdjfhsgaksuegrqw38463784672793746rwadjksfgha3e467dhcauw4y5t783yr';
    $body_request = array(
        'agent' => $agent_domain,
        'status' => $status,
        'token' => $toten,
    );

    try {
        $curl = curl_init();
        if ($curl === false) {
            echo json_encode(array('error' => 1, 'httpcode' => 500, 'message' => 'cURL Failed to initialize'));
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL             => "https://td.timchuyenbay.net/agent_status/change_status.php",
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
                if($status != 'Logged Out'){
                    $where_sql .= ', last_online = "' . $timestamp_now . '"';
                }

                if ($sip_number) {
                    $sql_online = '
                        UPDATE ec_online_report 
                        SET status = '.$status_value.' '.$where_sql.'
                        WHERE assigned_user_id = "' . $sip_number . '"
                        AND DATE_FORMAT(DATE_ADD(date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") = "' . date('Y-m-d') . '"
                        AND deleted = 0
                    ';
                    $result_sql_online = $db->query($sql_online);

                    if($result_sql_online){
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
        case '02839977799':
        case '02839977788':
        case '1900636063':
            $call_sources = 'timchuyenbay.com';
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
            $call_sources = 'vietjet.net';
    }

    return $call_sources;
}