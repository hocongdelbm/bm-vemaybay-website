<?php
class SMS {
    public $SENDER;
    private $ENDPOINT_SMS;
    private $TOKEN_SMS;
    // Use for ad
    private $ENDPOINT_STATIC_SMS_CAMPAIGN;
    private $TOKEN_STATIC_SMS_CAMPAIGN;
    Private $ENDPOINT_DYNAMIC_SMS_CAMPAIGN;
    private $TOKEN_DYNAMIC_SMS_CAMPAIGN;

    function __construct($sender = "Travelpass") {
        $this->SENDER = $sender;

        // $this->ENDPOINT_SMS = "https://api-01.worldsms.vn/webapi/sendSMS";
        // $this->ENDPOINT_SMS = "https://api-05.worldsms.vn/webapi/sendSMS";
        $this->ENDPOINT_SMS = "https://api-02.worldsms.vn/webapi/sendSMS";
        $this->TOKEN_SMS = "c25leHRfdHJhdmVsOk1xYkY1UmpY";

        // Update at 26/02/2025
        $this->ENDPOINT_STATIC_SMS_CAMPAIGN = "https://adv-api.worldsms.vn/api/v1/sms/campaign/create-static";
        $this->TOKEN_STATIC_SMS_CAMPAIGN = "c25leHRfdHJhdmVscWM6RjR6QVhlTTE=";
        $this->ENDPOINT_DYNAMIC_SMS_CAMPAIGN = "https://adv-api.worldsms.vn/api/v1/sms/campaign/create-dynamic";
        $this->TOKEN_DYNAMIC_SMS_CAMPAIGN = "c25leHRfdHJhdmVscWM6RjR6QVhlTTE=";
    }

    public function header() {
        return [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer c25leHRfdHJhdmVsOk1xYkY1UmpY'
        ];
    }

    /** 
     * Gửi tin nhắn qua số điện thoại
     * 
     * @param string $phone
     * @param string $text
     * @param string $contentid (optional)
     * @return string JSON
     */
    public function send($phone, $text, $contentid = 0) {
        if(is_null($phone) || empty($phone) || strlen($phone) < 10 || strlen($phone) > 12 || is_null($text) || empty($text)) {
            return json_encode([
                "status" => 0,
                "errorcode" => null,
                "description" => "Invalid params",
            ]);
        }

        $unicode = $this->is_unicode($text);
        $body = '{"from":"'.$this->SENDER.'","to":"'.$phone.'","text":"'.$text.'","unicode":'.$unicode.',"contentid":"'.$contentid.'"}';

        try {
            $curl = curl_init();
 
            if ($curl === false) {
                return json_encode([
                    "status" => 0,
                    "errorcode" => null,
                    "description" => "Failed to initialize",
                ]);
            }
        
            curl_setopt_array($curl, [
                CURLOPT_URL             => $this->ENDPOINT_SMS,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 0,
                CURLOPT_CUSTOMREQUEST   => 'POST',
                CURLOPT_POSTFIELDS      => $body,
                CURLOPT_HTTPHEADER      => [
                    "Content-Type: application/json",
                    "Accept: application/json",
                    "Authorization: Basic $this->TOKEN_SMS"
                ],
            ]);
            $json = curl_exec($curl);
        
            if ($json === false) {
                return json_encode([
                    "status" => 0,
                    "errorcode" => null,
                    "description" => curl_errno($curl) . ": " . curl_error($curl),
                ]);
            }
        
            $httpReturnCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
            // Process result here
            if($httpReturnCode == 200) {
                $json = trim($json);
                $json = trim(str_replace("\\", "",  $json), '"');  // Fix json string
                return $json;
            }

            return json_encode([
                "status"        => 0,
                "errorcode"     => $httpReturnCode,
                "description"   => "Fail",
            ]);
        }
        catch(Exception $e) {
            return json_encode([
                "status"        => 0,
                "errorcode"     => $e->getCode(),
                "description"   => $e->getMessage(),
            ]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }  

    /** 
     * Gửi chiến dịch tin nhắn QC nội dung tĩnh
     * 
     * @param string $sent_time Time to excute (d-m-Y H:i:s)
     * @param string $message
     * @param array  $list_phone List of phone numbers
     * @param string $campaign_name
     * @param string $client_request_id
     * @return string JSON
     */
    public function send_list_static($sent_time, $message, $list_phone, $campaign_name = '', $client_request_id = '') {
        if(is_null($sent_time) || empty($sent_time)
            || is_null($message) || empty($message)
            || is_null($list_phone) || empty($list_phone)
        ) return json_encode([
            "Status"        => 0,
            "Code"          => null,
            "Description"   => "Invalid params",
            "CampaignId"    => "",
        ]);

        // Format message
        $message = trim($message);
        $last_character = substr($message, -1);
        if($last_character != '.') $message .= '.';

        // Format sent time
        $sent_time = date('d-m-Y H:i:00', strtotime($sent_time));

        $body = [
            "Sender"            => $this->SENDER,
            "SentDate"          => $sent_time,
            "Msg"               => $message,
            "ListPhone"         => $list_phone,
            "CampaignName"      => $campaign_name,
            "ClientRequestId"   => $client_request_id,
            "Unicode"           => $this->is_unicode($message),
        ];

        try {
            $curl = curl_init();
 
            if ($curl === false) {
                return json_encode([
                    "Status"        => 0,
                    "Code"          => null,
                    "Description"   => "Failed to initialize",
                    "CampaignId"    => "",
                ]);
            }
        
            curl_setopt_array($curl, [
                CURLOPT_URL             => $this->ENDPOINT_STATIC_SMS_CAMPAIGN,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 0,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_CUSTOMREQUEST   => 'POST',
                CURLOPT_POSTFIELDS      => json_encode($body),
                CURLOPT_HTTPHEADER      => [
                    "Content-Type: application/json",
                    "Accept: application/json",
                    "Authorization: Basic $this->TOKEN_STATIC_SMS_CAMPAIGN"
                ],
            ]);
            $response = curl_exec($curl); // JSON
        
            if ($response === false) {
                return json_encode([
                    "Status"        => 0,
                    "Code"          => null,
                    "Description"   => curl_errno($curl) . ": " . curl_error($curl),
                    "CampaignId"    => "",
                ]);
            }

            $httpReturnCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            // Process result here
            if($httpReturnCode == 200) {
                $response = trim($response);
                $response = trim(str_replace("\\", "",  $response), '"');  // Fix json string
                $response = rtrim($response, "n");
                return $response;
            }

            return json_encode([
                "Status"        => 0,
                "Code"          => $httpReturnCode,
                "Description"   => "Fail",
                "CampaignId"    => "",
            ]);
        }
        catch(Exception $e) {
            return json_encode([
                "Status"        => 0,
                "Code"          => $e->getCode(),
                "Description"   => $e->getMessage(),
                "CampaignId"    => "",
            ]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }

    /** 
     * Gửi chiến dịch tin nhắn QC có tham số
     * 
     * @param string $sent_time Time to excute (d-m-Y H:i:s)
     * @param string $message
     * @param array  $list_data List of data to send
     * @param string $campaign_name
     * @param string $client_request_id
     * @return string JSON
     */
    public function send_list_dynamic($sent_time, $message, $list_data, $campaign_name = '', $client_request_id = '') {
        if(is_null($sent_time) || empty($sent_time)
            || is_null($message) || empty($message)
            || is_null($list_data) || empty($list_data)
        ) return json_encode([
            "Status"        => 0,
            "Code"          => null,
            "Description"   => "Invalid params",
            "CampaignId"    => "",
        ]);

        // Format message
        $message = trim($message);
        $last_character = substr($message, -1);
        if($last_character != '.') $message .= '.';

        // Format sent date
        $sent_time = date('d-m-Y H:i:00', strtotime($sent_time));

        $body = [
            "Sender"            => $this->SENDER,
            "SentDate"          => $sent_time,
            "Msg"               => $message,
            "ListData"          => $list_data,
            "CampaignName"      => $campaign_name,
            "ClientRequestId"   => $client_request_id,
            "Unicode"           => $this->is_unicode($message),
        ];

        try {
            $curl = curl_init();

            if ($curl === false) {
                return json_encode([
                    "Status"        => 0,
                    "Code"          => null,
                    "Description"   => "Failed to initialize",
                    "CampaignId"    => "",
                ]);
            }
        
            curl_setopt_array($curl, [
                CURLOPT_URL             => $this->ENDPOINT_DYNAMIC_SMS_CAMPAIGN,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 0,
                CURLOPT_CUSTOMREQUEST   => 'POST',
                CURLOPT_POSTFIELDS      => json_encode($body),
                CURLOPT_HTTPHEADER      => [
                    "Content-Type: application/json",
                    "Accept: application/json",
                    "Authorization: Basic $this->TOKEN_DYNAMIC_SMS_CAMPAIGN"
                ],
            ]);
            
            $json = curl_exec($curl);
        
            if ($json === false) {
                return json_encode([
                    "Status"        => 0,
                    "Code"          => null,
                    "Description"   => curl_errno($curl) . ": " . curl_error($curl),
                    "CampaignId"    => "",
                ]);
            }
        
            $httpReturnCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            // Process result here
            if($httpReturnCode == 200) {
                $json = trim($json);
                $json = trim(str_replace("\\", "",  $json), '"');  // Fix json string
                $json = rtrim($json, "n");
                return $json;
            }

            return json_encode([
                "Status"        => 0,
                "Code"          => $httpReturnCode,
                "Description"   => "Fail",
                "CampaignId"    => "",
            ]);
        }
        catch(Exception $e) {
            return json_encode([
                "Status"        => 0,
                "Code"          => $e->getCode(),
                "Description"   => $e->getMessage(),
                "CampaignId"    => "",
            ]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }

    public function caculate_fee($type, $message) {
        $count = 0;

        if($type == 'sms') {
            if($this->is_unicode($message)) {
                $len = strlen($this->convert_unicode_chars($message));
                if($len <= 70) $count = 1;
                elseif($len <= 134) $count = 2;
                elseif($len <= 201) $count = 3;
                elseif($len <= 268) $count = 4;
                elseif($len <= 335) $count = 5;
                elseif($len <= 402) $count = 6;
                elseif($len <= 469) $count = 7;
                elseif($len <= 536) $count = 8;
                elseif($len <= 603) $count = 9;
            }
            else {
                $len = strlen($message);
                if($len <= 160) $count = 1;
                elseif($len <= 306) $count = 2;
                elseif($len <= 459) $count = 3;
                elseif($len <= 612) $count = 4;
                elseif($len <= 765) $count = 5;
                elseif($len <= 799) $count = 6;
                elseif($len <= 918) $count = 7;
            }
        }

        return $count;
    }

    protected function is_unicode($string) {
        return preg_match('/[^\x20-\x7e]/', $string);
    }

    protected function convert_unicode_chars($str) {
        if (!$str) return false;
        $utf8 = [
            'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'D' => 'Đ',
            'd' => 'đ',
            'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
        ];
        foreach ($utf8 as $ascii => $uni) $str = preg_replace("/($uni)/i", $ascii, $str);
        return $str;
    }
    
}