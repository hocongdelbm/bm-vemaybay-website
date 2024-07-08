<?php 
class Zalo {
    private $ENDPOINT = 'https://zalo.timchuyenbay.net/api/v2';
    public $TRAVELPASS_ZALO_ID = '2941581384627345950';

    public function send_zns($phone, $template_id, $template_data) {
        $url = $this->ENDPOINT . '/sendZNS';
        $header = array('Content-Type: application/json');

        $body_request = json_encode([
            'phone' => $phone,
            'template_id' => $template_id,
            'template_data' => json_decode($template_data, true)
        ]);

        return $this->curl('POST', $url, $body_request, $header);
    }

    public function send_request_user_info($user_id) {
        $url = $this->ENDPOINT . '/sendRequestUserInfo';
        $body_request = ['user_id' => $user_id];

        return $this->curl('POST', $url, $body_request);
    }

    /**
     * Send message promotion to user on OA
     * @param string $user_id
     * @param string $banner
     * @param string $header
     * @param string $text
     * @param array $table
     * @param string $text2
     * @param array $buttons (Optional)
     */
    public function send_promotion($user_id, $banner, $header, $text, $table, $text2 = '', $buttons = array()) {
        $url = $this->ENDPOINT . '/sendPromotion';
        $request_header = array('Content-Type: application/json');

        $body_request = json_encode([
            "user_id" => $user_id, 
            "banner" => $banner, 
            "header" => $header, 
            "text" => $text,
            "table" => $table,
            "text2" => $text2,
            "buttons" => $buttons
        ]);

        return $this->curl('POST', $url, $body_request, $request_header);
    }

    /**
     * Send message promotion to user on OA
     * @param string $post_id
     * @param array $filters (Optional)
     */
    public function send_broadcast($post_id, $filters = array()) {
        $url = $this->ENDPOINT . '/sendBroadcast';
        $header = array('Content-Type: application/json');

        $body_request = json_encode([
            "post_id" => $post_id, 
            "filters" => $filters
        ]);

        return $this->curl('POST', $url, $body_request, $header);
    }

    public function get_user_info($user_id) {
        $url = $this->ENDPOINT . '/getUser';
        $header = array('Content-Type: multipart/form-data');
        $body_request = ['user_id' => $user_id];

        return $this->curl('POST', $url, $body_request, $header);
    }

    public function get_messages($user_id, $offset = 0, $count = 10) {
        $url = $this->ENDPOINT . '/getMessages';
        $body_request = ['user_id' => $user_id, 'offset' => $offset, 'count' => $count];

        return $this->curl('POST', $url, $body_request);
    }

    public function get_quota_user($user_id) {
        $url = $this->ENDPOINT . '/getQuotaUser';
        $body_request = ['user_id' => $user_id];

        return $this->curl('POST', $url, $body_request);
    }

    public function get_post($post_id) {
        $url = $this->ENDPOINT . '/getPost';
        $body_request = ['post_id' => $post_id];

        return $this->curl('POST', $url, $body_request);
    }

    public function get_link_post($post_id) {
        return 'https://officialaccount.me/d/'.$this->TRAVELPASS_ZALO_ID.'?id='.$post_id.'&pageId='.$this->TRAVELPASS_ZALO_ID;
    }

    private function curl($method, $url, $body, $header = '') {
        try {
            $curl = curl_init();

            // Check if initialization had gone wrong
            if ($curl === false) {
                return 'Failed to initialize';
            }

            $curl_params = array(
                CURLOPT_URL             => $url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 0,
                CURLOPT_CUSTOMREQUEST   => $method,
                CURLOPT_POSTFIELDS      => $body,
            );
            if(!empty($header)) $curl_params[CURLOPT_HTTPHEADER] = $header;
            curl_setopt_array($curl, $curl_params); // Better to explicitly set URL
            $json = curl_exec($curl);
        
            // Check the return value of curl_exec(), too
            if ($json === false) {
                return curl_error($curl) . ' (errno ' . curl_errno($curl) . ')';
            }
        
            // Check HTTP return code, too; might be something else than 200
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            // Process result here
            return trim($json);
        }
        catch(Exception $e) {
            trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
        }
        finally {
            if (is_resource($curl)) {
                curl_close($curl);
            }
        }
    }

    public function get_template_id_zns($type) {
        if(is_null($type) || empty($type)) return "";

        switch ($type) {
            case 'journey-one-way':
                return "347078"; // Hành trình một chiều
                // return "288268"; // Hành trình một chiều
                break;
            case 'journey-round-trip':
                return "347088"; // Hành trình khứ hồi
                // return "288272"; // Hành trình khứ hồi
                break;
            case 'payment':
                return "345209"; // Thông tin thanh toán
                break;
            case 'code-one-way':
                return "288276"; // Code vé một chiều
                break;
            case 'code-round-trip':
                return "288279"; // Code vé khứ hồi
                break;
            case 'after-call-sale': 
                return "346699"; // CSKH
                break;
            case 'delay':
                return "346656"; // Thông báo delay
                break;
            case 'remind-flight':
                return "346651"; // Nhắc nhở giờ bay
                break;
            default:
                return "";
        }
    }

    public function get_template_name_zns($id = null) {
        if(is_null($id) || empty($id)) return "";
    
        switch ($id) {
            // case '288268':
            // case '288272':
            case '347078':
            case '347088':
                return "thông tin hành trình";
                break;
            case '345209': // (New)
                return "thông tin thanh toán";
                break;
            case '288276':
            case '288279':
                return "thông tin code vé";
                break;
            case '346656':
                return "thông báo delay";
                break;
            case '346651':
                return "nhắc nhở giờ bay";
                break;
            case '346699':
                return "chăm sóc khách hàng";
                break;
            default:
                return "";
        }
    }

    public function send_to_telegram($content, $parseMode = 'HTML', $timeout = 15) {
        $token  = '6940954517:AAFINEfJWBOcuoThjXNycvNRRZjT3ZgLey8'; // TimChuyenBayOA_bot
        $chatId = '-1002134640739'; // Tìm Chuyến Bay OA Zalo ZNS

        $url = "https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $chatId;
        $url = $url . "&parse_mode=".$parseMode."&text=" . urlencode($content);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    public function unformat_zalo_phone($zalo_phone) {
        if(substr($zalo_phone, 0, 2) == 84) return '0' . substr($zalo_phone, 2);
        elseif(substr($zalo_phone, 0, 3) == "+84") return '0' . substr($zalo_phone, 3);

        return $zalo_phone;
    } 
}
