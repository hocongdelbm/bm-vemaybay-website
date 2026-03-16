<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class APIZaloOA {
    private $template_path;
    private $images_path;
    protected $domain;
    /**
     * @var EC_Zalo_Apps
     */
    public $app;
    /**
     * @var EC_Zalo
     */
    protected $oa;

    public function __construct($app_id = '', $oa_id = '') {
        global $sugar_config;
        $this->domain           = $sugar_config['host_name'] ?? $_SERVER['SERVER_NAME'];
        $this->template_path    = "custom/json_files/zalo_oa/templates.json";
        $this->images_path      = "custom/themes/SuiteP/images/zalo_oa";

        if(!is_string($app_id) || empty($app_id)) $app_id = $sugar_config['zalo_config']['app_id_default'] ?? '';
        if(!is_string($oa_id) || empty($oa_id)) $oa_id = $sugar_config['zalo_config']['oa_id_default'] ?? '';
        
        // App info
        if(!empty($app_id)) {
            $this->app = new EC_Zalo_Apps();
            $this->app->retrieve($app_id);
        }

        // OA info
        if(!empty($oa_id)) {
            $this->oa = new EC_Zalo();
            $this->oa->retrieve($oa_id);
        }
    }

    public function get_oa_id() {return $this->oa->id;}
    public function get_app_id() {return $this->app->id;}
    public function get_images_path() {return $this->images_path;}
    public function get_domain() {return $this->domain;}


    /***************  AUTH  ***************/
    /** 
     * Return link to integrate zalo 
     * 
     * @return string url
     */
    public function get_link_integrate() {
        $entrypoint = "https://{$this->domain}/index.php?entryPoint=entryPointZaloAuthCallback";
        return "https://oauth.zaloapp.com/v4/oa/permission?app_id={$this->app->id}&redirect_uri=".urlencode($entrypoint);
    }

    /** 
     * Generate code verifier and code challenge using PKCE
     * 
     * @return array [code_verifier, code_challenge]
     */
    public function generate_code() {
        // Code_verifier là một chuỗi ASCII 43 ký tự
        $code_verifier = '';
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        for ($i = 0; $i < 43; $i++) {
            $code_verifier .= $characters[random_int(0, $charactersLength - 1)];
        }
        // Hash code_verifier bằng SHA-256
        $sha256_hash = hash('sha256', $code_verifier, true);
        // Encode kết quả hash bằng Base64
        $code_challenge = base64_encode($sha256_hash);
        // Chuẩn hóa PKCE
        $code_challenge = str_replace(['+', '/', '='], ['-', '_', ''], $code_challenge);
        return json_encode(['code_verifier' => $code_verifier, 'code_challenge' => $code_challenge]);
    }
    
    /** 
     * Get access token by authorization code
     * 
     * @param string $code
     * @return string json
     */
    public function get_new_token_auth($code) {
        $url = "https://oauth.zaloapp.com/v4/oa/access_token";
        $header = [
            "Content-Type: application/x-www-form-urlencoded",
            "secret_key: {$this->app->secret_key}"
        ];
        $requestBody = http_build_query([
            "code"          => $code,
            "app_id"        => $this->app->id,
            "grant_type"    => "authorization_code",
            "code_verifier" => $this->app->code_verifier,
        ]);

        $json = $this->send_request("POST", $url, $requestBody, $header);

        $arr = json_decode($json, true);
        if(isset($arr['access_token']) && !empty($arr['access_token'])) $this->save_token($json);
        else $this->save_token($json, true);

        return $json;
    }

    /** 
     * Get access token from refresh token
     * 
     * @param string $refresh_token
     * @return string json
     */
    public function get_new_token($refresh_token) {
        $url = "https://oauth.zaloapp.com/v4/oa/access_token";
        $header = [
            "Content-Type: application/x-www-form-urlencoded",
            "secret_key: {$this->app->secret_key}"
        ];
        $requestBody = http_build_query([
            "app_id"        => $this->app->id,
            "grant_type"    => "refresh_token",
            "refresh_token" => $refresh_token
        ]);
        $curlOptions = [
            CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
            CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
        ];

        $json = $this->send_request("POST", $url, $requestBody, $header, $curlOptions);

        $arr = json_decode($json, true);
        if($arr && isset($arr['access_token']) && !empty($arr['access_token'])) $this->save_token($json);
        else $this->save_token($json, true);

        return $json;
    }

    /** 
     * Get token
     * 
     * @param string $type
     * @return string
     */
    public function get_token($type = "access") {
        if($type == 'refresh') return $this->app->refresh_token;
        elseif($type == 'access' && strtotime($this->app->expires_at) > $this->get_timestamp()) return $this->app->access_token;
        else {
            $this->get_new_token($this->app->refresh_token);
            return $this->app->access_token;
        }
        return '';
    }

    /** 
     * Save token returned from api
     * 
     * @param string $json
     * @param bool $is_error
     * @return bool
     */
    protected function save_token($json, $is_error = false) {
        if($is_error && !empty($this->app->id)) {
            $this->app->description = $json;
            return $this->app->save();
        }

        if(is_string($json) && !empty($json)) {
            $arr = json_decode($json, true);

            $expires_in = isset($arr['expires_in']) ? (int)$arr['expires_in'] : 90000; //Seconds
            $expires_at_timestamp       = $this->get_timestamp('UTC') + $expires_in; // UTC timezones
            $this->app->access_token    = $arr['access_token'] ?? $this->app->access_token;
            if (!empty($arr['refresh_token'])) {
                $this->app->refresh_token = $arr['refresh_token'];
            }
            $this->app->expires_at      = date('Y-m-d H:i:s', $expires_at_timestamp);

            return $this->app->save();
        }
    }

    /****************  OA  ****************/
    /** 
     * Get info OA
     * 
     * @return string json
     */
    public function get_info_oa() {
        $url = "https://openapi.zalo.me/v2.0/oa/getoa";
        $header = [
            "access_token: ". $this->get_token()
        ];
        $curlOptions = [
            CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
            CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
        ];

        return $this->send_request('GET', $url, null, $header, $curlOptions);
    }

    /** 
     * Get quota messages OA
     * 
     * @param string $quota_owner
     * @param string $product_type
     * @param string $quota_type
     * @return string json
     */
    public function get_quota_oa($quota_owner = 'OA', $product_type = 'cs', $quota_type = 'sub_quota') {
        $url = "https://openapi.zalo.me/v3.0/oa/quota/message";
        $header = [
            "Content-Type: application/json",
            "access_token: ". $this->get_token(),
        ];
        $requestBody = json_encode([
            "quota_owner"   => $quota_owner,
            "product_type"  => $product_type, // Tin tư vấn
            "quota_type"    => $quota_type // Quota tin theo gói dịch vụ OA
        ]);
        $curlOptions = [
            CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
            CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
        ];

        return $this->send_request("POST", $url, $requestBody, $header, $curlOptions);
    }



    /***************  USER  ***************/
    /** 
     * Get list user id
     * 
     * @param int $offset
     * @param int $count
     * @param string $last_interaction_period TODAY, YESTERDAY, L7D, L30D, <YYYY_MM_DD:YYYY_MM_DD>
     * @param bool $is_follower
     * @param string $tag_name
     * @return string json
     */
    public function get_list_user($offset = 0, $count = 50, $last_interaction_period = '', $is_follower = null, $tag_name = '') {
        $data = [
            'offset' => $offset,
            'count' => $count > 50 ? 50 : $count
        ];
        if(!is_null($is_follower)) $data['is_follower'] = $is_follower;
        if(!empty($last_interaction_period)) $data['last_interaction_period'] = $last_interaction_period;
        if(!empty($tag_name)) $data['tag_name'] = $tag_name;
        $data = json_encode($data);

        $url = "https://openapi.zalo.me/v3.0/oa/user/getlist?data=" . urlencode($data);
        $header = [
            "access_token: ". $this->get_token()
        ];
        $curlOptions = [
            CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
            CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
        ];

        return $this->send_request('GET', $url, null, $header, $curlOptions);
    }

    /** 
     * Get user information
     * 
     * @param string $zalo_id
     * @return string json
     */
    public function get_user($zalo_id) {
        $data = json_encode(['user_id' => $zalo_id]);
        $url = "https://openapi.zalo.me/v3.0/oa/user/detail?data=" . urlencode($data);
        $header = [
            "access_token: ". $this->get_token()
        ];
        $curlOptions = [
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0
        ];

        return $this->send_request('GET', $url, null, $header, $curlOptions);
    }

    /** 
     * Get limit quota for sending messages to users
     * 
     * @param string $zalo_id
     * @return string json
     */
    public function get_quota_user($zalo_id) {
        $url = "https://openapi.zalo.me/v3.0/oa/quota/message";
        $header = [
            "Content-Type: application/json",
            "access_token: ". $this->get_token()
        ];
        $requestBody = json_encode(['user_id' => $zalo_id]);
        $curlOptions = [
            CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
            CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
        ];

        return $this->send_request("POST", $url, $requestBody, $header, $curlOptions);
    }

    /** 
     * Get the list of tags
     * 
     * @return string json
     */
    public function get_list_tag() {
        $url = "https://openapi.zalo.me/v2.0/oa/tag/gettagsofoa";
        $header = [
            "access_token: ". $this->get_token()
        ];
        $curlOptions = [
            CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
            CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
        ];

        return $this->send_request('GET', $url, null, $header, $curlOptions);
    }

    /** 
     * Update user info
     * 
     * @param string $zalo_id
     * @param string $user_alias
     * @param array $shared_info
     * @return string json
     */
    public function update_user($zalo_id, $user_alias = '', $shared_info = []) {
        $url = "https://openapi.zalo.me/v3.0/oa/user/update";

        $header = [
            "Content-Type: application/json",
            "access_token: ". $this->get_token()
        ];

        $requestBody = ["user_id" => $zalo_id];
        if(!empty($user_alias)) $requestBody['user_alias'] = $user_alias;
        if(!empty($shared_info) && count($shared_info) > 4) $requestBody['shared_info'] = $shared_info;

        $curlOptions = [
            CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
            CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
        ];

        return $this->send_request("POST", $url, json_encode($requestBody), $header, $curlOptions);
    }

    /** 
     * Add tag to user (Each user can only have 1 tag)
     * 
     * @param string $zalo_id
     * @param string $tag_name
     * @return string json
     */
    public function add_tag_user($zalo_id, $tag_name) {
        $url = "https://openapi.zalo.me/v2.0/oa/tag/tagfollower";
        $header = [
            "Content-Type: application/json",
            "access_token: ". $this->get_token()
        ];
        $requestBody = json_encode([
            "user_id" => $zalo_id,
            "tag_name" => $tag_name
        ]);
        $curlOptions = [
            CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
            CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
        ];

        return $this->send_request("POST", $url, $requestBody, $header, $curlOptions);
    }

    /** 
     * Remove tag from user
     * 
     * @param string $zalo_id
     * @param string $tag_name
     * @return string json
     */
    public function remove_tag_user($zalo_id, $tag_name) {
        $url = "https://openapi.zalo.me/v2.0/oa/tag/rmfollowerfromtag";
        $header = [
            "Content-Type: application/json",
            "access_token: ". $this->get_token()
        ];
        $requestBody = json_encode([
            "user_id" => $zalo_id,
            "tag_name" => $tag_name
        ]);
        $curlOptions = [
            CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
            CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
        ];

        return $this->send_request("POST", $url, $requestBody, $header, $curlOptions);
    }



    /***************  MESSAGES  ***************/
    /** 
     * Get messages in a conversation
     * 
     * @param string $zalo_id
     * @param string $offset (Optional)
     * @param string $count (Optional)
     * @return string json
     */
    public function get_messages($zalo_id, $offset = 0, $count = 10) {
        $data = json_encode([
            'user_id' => $zalo_id,
            'offset' => $offset,
            'count' => $count
        ]);

        $url = "https://openapi.zalo.me/v2.0/oa/conversation?data=" . urlencode($data);
        $header = [
            "Content-Type: application/json",
            "access_token: ". $this->get_token()
        ];
        $curlOptions = [
            CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
            CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
        ];

        return $this->send_request('GET', $url, null, $header, $curlOptions);
    }

    /** 
     * Get recent messages
     * 
     * @param string $offset (Optional)
     * @param string $count (Optional)
     * @return string json
     */
    public function get_recent_messages($offset = 0, $count = 10) {
        $data = json_encode([
            'offset' => $offset,
            'count' => $count
        ]);

        $url = "https://openapi.zalo.me/v2.0/oa/listrecentchat?data=" . urlencode($data);
        $header = [
            "Content-Type: application/json",
            "access_token: ". $this->get_token()
        ];
        $curlOptions = [
            CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
            CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
        ];

        return $this->send_request('GET', $url, null, $header, $curlOptions);
    }

    /** 
     * Get chat link on zalo
     * 
     * @param string $zalo_id
     * @return string url
     */
    public function get_chat_link($zalo_id) {
        return "https://oa.zalo.me/chatv2?uid={$zalo_id}&oaid={$this->oa->id}&src=share";
    }

    /** 
     * Send consulting messages
     * 
     * @param string $type
     * @param string $zalo_id
     * @param array $data
     * @return string json
     */
    public function send_consultation_message($type, $zalo_id, $data = []) {
        $url = "https://openapi.zalo.me/v3.0/oa/message/cs";
        $header = [
            "Content-Type: application/json",
            "access_token: ". $this->get_token()
        ];
        $curlOptions = [
            CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
            CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
        ];

        $requestBody = null;
        if($type == 'text') {
            if(!isset($data["text"]) || empty($data["text"]))
                return json_encode(['error' => 1, 'httpcode' => 403, 'message' => 'Invalid parameters', 'data' => $data]);

            $arr = [
                "recipient" => [
                    "user_id" => $zalo_id
                ],
                "message" => [
                    "text" => $data["text"]
                ]
            ];
            if(isset($data['quote_message_id']) && !empty($data['quote_message_id'])) $arr['message']['quote_message_id'] = $data['quote_message_id'];
            $requestBody = json_encode($arr);
        }
        elseif($type == 'image') {
            if(!isset($data["element"]) || empty($data["element"]))
                return json_encode(['error' => 1, 'httpcode' => 403, 'message' => 'Invalid parameters', 'data' => $data]);

            $requestBody = json_encode([
                "recipient" => [
                    "user_id" => $zalo_id
                ],
                "message" => [
                    "text" => $data["text"],
                    "attachment" => [
                        "type" => "template",
                        "payload" => [
                            "template_type" => "media",
                            "elements" => [$data['element']]
                        ]
                    ]
                ]
            ]);
        }
        elseif($type == 'file') {
            if(!isset($data["token"]) || empty($data["token"]))
                return json_encode(['error' => 1, 'httpcode' => 403, 'message' => 'Invalid parameters', 'data' => $data]);

            $requestBody = json_encode([
                "recipient" => [
                    "user_id" => $zalo_id
                ],
                "message" => [
                    "text" => $data["text"],
                    "attachment" => [
                        "type" => "file",
                        "payload" => [
                            "token" => $data['token']
                        ]
                    ]
                ]
            ]);
        }
        elseif($type == 'request_user_info') {
            if(!isset($data["element"]) || empty($data["element"]))
                return json_encode(['error' => 1, 'httpcode' => 403, 'message' => 'Invalid parameters', 'data' => $data]);

            $requestBody = json_encode([
                "recipient" => [
                    "user_id" => $zalo_id,
                ],
                "message" => [
                    "attachment" => [
                        "type" => "template",
                        "payload" => [
                            "template_type" => "request_user_info",
                            "elements" => [$data['element']]
                        ]
                    ]
                ]
            ]);
        }

        return $this->send_request("POST", $url, $requestBody, $header, $curlOptions);
    }



    /***************  ZBS  ***************/
    /** 
     * Send ZBS template message by phone
     * 
     * @param string $phone
     * @param string $template_id
     * @param string|array $template_data json|array
     * @param bool $is_hashphone
     * @param bool $is_dev_mode
     * @return string json
     */
    public function send_template_message_by_phone($phone, $template_id, $template_data, $is_hashphone = true, $is_dev_mode = false) {
        $url = "https://business.openapi.zalo.me/message/template";
        if($is_hashphone) $url = "https://business.openapi.zalo.me/message/template/hashphone";

        $header = [
            "Content-Type: application/json",
            "access_token: ". $this->get_token()
        ];

        $requestBody = [
            'template_id'   => $template_id,
            'template_data' => is_array($template_data) ? $template_data : json_decode($template_data, true),
            'tracking_id'   => $phone . time()
        ];

        $format_phone = $this->format_zalo_phone($phone);
        if($is_hashphone) $requestBody['hash_phone'] = hash('sha256', $format_phone);
        else $requestBody['phone'] = $format_phone;

        if($is_dev_mode) $requestBody['mode'] = 'development';
    
        $curlOptions = [
            CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
            CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
        ];

        return $this->send_request("POST", $url, json_encode($requestBody), $header, $curlOptions);
    }

    /** 
     * Send ZBS template message by zalo_id
     * 
     * @param string $zalo_id
     * @param string $template_id
     * @param string|array $template_data json|array
     * @return string json
     */
    public function send_template_message_by_uid($zalo_id, $template_id, $template_data) {
        $url = "https://openapi.zalo.me/v3.0/oa/message/template";

        $header = [
            "Content-Type: application/json",
            "access_token: ". $this->get_token()
        ];

        $requestBody = [
            'user_id'       => $zalo_id,
            'template_id'   => $template_id,
            'template_data' => is_array($template_data) ? $template_data : json_decode($template_data, true),
            'tracking_id'   => $zalo_id . time()
        ];

        $curlOptions = [
            CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
            CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
        ];

        return $this->send_request("POST", $url, json_encode($requestBody), $header, $curlOptions);
    }

    /** 
     * Send template id
     * 
     * @param string $type
     * @return string
     */
    public function get_template_id($type) {
        switch ($type) {
            case 'journey-one-way':
                return "466986"; // Hành trình một chiều
            case 'journey-round-trip':
                return "466988"; // Hành trình khứ hồi
            case 'payment':
                return "466992"; // Thông tin thanh toán
            case 'code-one-way':
                return "466996"; // Code vé một chiều
            case 'code-round-trip':
                return "466998"; // Code vé khứ hồi
            case 'after-call-sale': 
                return "467009"; // CSKH sau khi gọi
            case 'delay':
                return "467001"; // Thông báo delay
            case 'remind-flight':
                return "467004"; // Nhắc nhở giờ bay
            case 'points':
                return "467010"; // Thông báo tích điểm
            case 'share-phone':
                return "467011"; // Gửi thông tin chương trình chia sẻ SĐT
            case 'cheap-flight':
                return "549919"; // Gửi vé giá rẻ booking tham khảo (CSKH)
            case 'otp':
                return "518686"; // Gửi OTP qua SĐT
            default:
                return "";
        }
    }

    /** 
     * Send template name
     * 
     * @param string $template_id
     * @return string
     */
    public function get_template_name($template_id = null) {
        switch ($template_id) {
            case '466986':
            case '466988':
                return "Thông tin hành trình";
            case '466992':
                return "Thông tin thanh toán";
            case '466996':
            case '466998':
                return "Thông tin code vé";
            case '467001':
                return "Thông báo delay";
            case '467004':
                return "Nhắc nhở giờ bay";
            case '467009':
                return "Chăm sóc khách hàng (Call sale)";
            case '467010':
                return "Thông báo tích điểm";
            case '467011':
                return "Gửi thông tin chương trình chia sẻ SĐT";
            case '518686':
                return "Gửi OTP";
            case '549919':
                return "Gửi vé giá rẻ booking tham khảo (CSKH)";
            default:
                return "";
        }
    }

    /**
     * Get cost to send template message
     * 
     * @param string $template_id
     * @param string $send_by "phone_number" or "uid"
     * @return int
     */
    public function get_cost_by_template($template_id, $send_by) {
        $arr = [
            "466992" => [
                "phone_number" => 300,
                "uid" => 0,
            ],
            "518686" => [
                "phone_number" => 300,
                "uid" => 210,
            ],
            "549919" => [
                "phone_number" => 200,
                "uid" => 140,
            ],
        ];
        return $arr[(string)$template_id][$send_by] ?? 200;
    }

    /**
     * Check template id that can send by uid in new rule (Declared after 10/12/2025)
     */
    public function check_template_can_send_by_uid($template_id) {
        return !in_array($template_id, [
            "466986", "466988", "466992", "466996", "466998", "467009", "467001", "467004", "467010", "467011",
            "347078", "347088", "345209", "288276", "288279", "346656", "346651", "411270", "433046", "346699",
            // OPT
            "518686", "518205",
        ]);
    }

    /**
     * Converts Zalo ZBS error codes to a single Vietnamese error string
     * 
     * @param int|string $error_code The error code from Zalo API
     * @return string Combined error message and handling instruction
     */
    public function get_error_description($error_code) {
        switch ((int)$error_code) {
            case 0:
                return "Thành công: Gửi tin nhắn thành công.";
            case -109:
                return "ID Template không hợp lệ: Vui lòng kiểm tra lại ID của Template.";
            case -110:
                return "Phiên bản Zalo không hỗ trợ: Người dùng cần cập nhật Zalo app phiên bản mới nhất.";
            case -111:
                return "Dữ liệu Template trống: Template không có dữ liệu để gửi.";
            case -1121:
                return "Tham số quá dài: Dữ liệu tham số vượt quá giới hạn ký tự cho phép.";
            case -1122:
                return "Thiếu tham số: Dữ liệu truyền vào thiếu tham số bắt buộc trong Template.";
            case -1123:
                return "Lỗi QR code: Không thể tạo QR code, vui lòng kiểm tra lại dữ liệu đầu vào.";
            case -1124:
                return "Sai định dạng tham số: Kiểm tra lại format dữ liệu của các biến (ví dụ: ngày tháng, số tiền).";
            case -113:
                return "Nút bấm (Button) không hợp lệ.";
            case -1131:
                return "Link không đúng định dạng: Kiểm tra lại đường dẫn liên kết của các nút thao tác.";
            case -116:
                return "Nội dung tham số không hợp lệ.";
            case -117:
                return "Không có quyền dùng Template: OA hoặc App chưa được cấp quyền cho Template này. Kiểm tra AppID/OAID/tempID.";
            case -121:
                return "Nội dung trống: Template không có nội dung, vui lòng nhập nội dung mẫu.";
            case -122:
                return "Sai định dạng Body: Body request không đúng định dạng JSON.";
            case -130:
                return "Vượt quá ký tự: Nội dung Template vượt quá giới hạn (tối đa 100k ký tự).";
            case -131:
                return "Template chưa phê duyệt: Vui lòng chờ Zalo duyệt mẫu tin nhắn này trước khi gửi.";
            case -132:
                return "Tham số không hợp lệ.";
            case -249:
                return "Không hỗ trợ UID: Template cũ (trước 10/12/2025) hoặc loại OTP/Journey không hỗ trợ gửi qua UID. Hãy clone/tạo mới template.";
            case -100:
                return "Lỗi không xác định: Vui lòng thử lại sau.";
            case -101:
                return "Ứng dụng không hợp lệ: Kiểm tra lại ID ứng dụng tại Zalo for Developers.";
            case -103:
                return "Ứng dụng chưa kích hoạt: Truy cập Zalo for Developers để bật kích hoạt ứng dụng.";
            case -104:
                return "Secret key không hợp lệ: Kiểm tra lại Secret key trong thiết lập ứng dụng.";
            case -106:
                return "Phương thức không hỗ trợ: Đối chiếu phương thức gọi API với tài liệu Zalo.";
            case -107:
                return "ID thông báo không hợp lệ.";
            case -108:
                return "Số điện thoại không hợp lệ: Kiểm tra lại định dạng (ví dụ: 84xxxx hoặc 0xxxx).";
            case -115:
                return "Hết hạn mức: Tài khoản ZBS không đủ số dư, vui lòng nạp tiền tại ZBS Account.";
            case -118:
                return "Tài khoản không tồn tại: Người dùng chưa đăng ký Zalo hoặc tài khoản bị vô hiệu hóa.";
            case -120:
                return "OA không có quyền: Cần mua gói dịch vụ để sử dụng tính năng này.";
            case -1202:
                return "OA không có quyền sử dụng tài nguyên media (image/logo).";
            case -124:
                return "Access token không hợp lệ: Vui lòng làm mới (refresh) access token.";
            case -1241:
                return "appsecret_proof không hợp lệ: Kiểm tra lại mã hóa appsecret_proof.";
            case -125:
                return "ID Official Account không hợp lệ: Kiểm tra lại OA ID trong quản lý OA.";
            case -126:
                return "Ví development không đủ số dư: Vui lòng kiểm tra lại tài khoản thử nghiệm.";
            case -127:
                return "Lỗi gửi thử: Tin nhắn test chỉ có thể gửi cho quản trị viên.";
            case -135:
                return "OA chưa xác thực: Cần xác thực OA hoặc nâng cấp khỏi gói miễn phí để gửi qua SĐT.";
            case -1351:
                return "OA bị chặn: Hệ thống chặn gửi tin do phát hiện vi phạm chính sách.";
            case -136:
                return "Chưa kết nối ZBS Account: Cần liên kết App ID vào Zalo Cloud Account (ZCA).";
            case -137:
                return "Thanh toán thất bại: Ví ZBS không đủ số dư để thực hiện giao dịch.";
            case -138:
                return "Ứng dụng chưa được cấp quyền: Kiểm tra xét duyệt API gửi tin SĐT tại trang Developer.";
            case -1381:
                return "Extension chưa có quyền: OA chưa cấp quyền sử dụng ZBS Account cho Extension.";
            case -139:
                return "Người dùng từ chối: Khách hàng đã tắt nhận loại tin nhắn SĐT này.";
            case -140:
                return "Không đủ điều kiện: Người dùng không nằm trong diện nhận tin theo chính sách Zalo.";
            case -141:
                return "Người dùng chặn OA: Khách hàng đã từ chối nhận tin SĐT từ Official Account này.";
            case -142:
                return "Thiếu RSA key: Vui lòng gọi API khởi tạo RSA key.";
            case -143:
                return "RSA key đã tồn tại: Vui lòng gọi API lấy RSA key hiện có.";
            case -144:
                return "Vượt định mức ngày: OA đã gửi quá giới hạn tin nhắn SĐT cho phép trong ngày.";
            case -1441:
                return "Vượt định mức khuyến mãi: OA đã gửi vượt ngưỡng monthly promotion quota.";
            case -145:
                return "Loại tin nhắn không được phép: Nội dung (Tag) này không được hỗ trợ cho OA của bạn.";
            case -147:
                return "Template vượt định mức: Mẫu tin nhắn này đã đạt giới hạn gửi trong ngày.";
            case -1471:
                return "Vượt giới hạn tháng: Đã gửi quá số lượng tin hậu mãi cho người dùng này trong tháng.";
            case -1472:
                return "Vượt giới hạn ngày: Đã gửi quá số lượng tin promotion cho người dùng này trong ngày.";
            case -148:
            case -149:
            case -150:
                return "Lỗi Journey Token: Token không tồn tại, không hợp lệ hoặc đã hết hạn.";
            case -153:
                return "Dữ liệu sai quy định: Kiểm tra lại cấu trúc JSON hoặc tham số truyền vào API.";
            case -158:
                return "File quá lớn: Dung lượng file vượt quá giới hạn cho phép.";
            case -159:
                return "Định dạng file không hỗ trợ.";
            case -160:
                return "Hết quota tạo Template: Đã vượt quá số lượng tạo/chỉnh sửa template trong ngày.";
            case -161:
                return "sending_mode sai: Giá trị chế độ gửi không hợp lệ.";
            case -162:
                return "Chế độ gửi không hỗ trợ: Tag 1, 2 không được dùng sending_mode = 3.";
            default:
                return "Lỗi hệ thống ($error_code): Vui lòng kiểm tra lại cấu hình hoặc liên hệ hỗ trợ Zalo.";
        }
    }



    /***************  UPLOAD  ***************/
    /** 
     * Get file extension is supported
     * 
     * @param string $type
     * @return array
     */
    public function get_file_extension($type) {
        if($type == 'image') return ['png', 'jpg', 'gif'];
        elseif($type == 'file') return ['pdf', 'doc', 'docx', 'csv'];
    }

    /** 
     * Upload to zalo
     * 
     * @param string $path
     * @param string $extension
     * @param string $name
     * @return string json
     */
    public function upload($path, $extension, $name = null) {
        $type = '';
        if($extension == "gif") $type = "gif";
        elseif(in_array($extension, $this->get_file_extension('image'))) $type = "image";
        elseif(in_array($extension, $this->get_file_extension('file'))) $type = "file";

        $url = "https://openapi.zalo.me/v2.0/oa/upload/{$type}";
        $header = [
            "access_token: ". $this->get_token()
        ];
        $requestBody = ['file' => new CURLFile($path, null, $name)];
        $curlOptions = [
            CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
            CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
        ];

        return $this->send_request("POST", $url, $requestBody, $header, $curlOptions);
    }



    /***************  TEMPLATES  ***************/
    /** 
     * Get zalo message templates that is declared by file in system 
     * 
     * @param string $name
     * @param string $return_type
     * @return mixed
     */
    public function get_template_handmade($name, $return_type = 'array') {
        $result = [];

        if(file_exists($this->template_path)) {
            $json = file_get_contents($this->template_path);
            $arr  = json_decode($json, true);

            if($arr && isset($arr[$name])) {
                $result = $arr[$name];
            }
        }

        if($return_type == 'json') return json_encode($result);
        return $result;
    }



    /***************  Utils  ***************/
    /**
     * Send HTTP request
     * 
     * @param string $method GET, POST, PUT,...
     * @param string $url
     * @param array|string $requestBody
     * @param array $header
     * @param array $curlOptions
     * 
     * @return string JSON
     */
    private function send_request($method, $url, $requestBody = null, $header = [], $curlOptions = []) {
        try {
            $curl = curl_init();
            if ($curl === false) {
                return json_encode([
                    "error" => 1,
                    "httpCode" => 500,
                    "message" => "System error",
                    "data" => null,
                    "description" => "cURL failed to initialize in BM"
                ]);
            }
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            if(!is_null($requestBody)) curl_setopt($curl, CURLOPT_POSTFIELDS, $requestBody);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 24);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 20);
            curl_setopt($curl, CURLOPT_TIMEOUT, 60);
            foreach ($curlOptions as $key => $value) {
                curl_setopt($curl, $key, $value);
            }
            $response = curl_exec($curl); // JSON
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorNo = curl_errno($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if ($response === false || $errorNo) {
                return json_encode([
                    "error" => 1,
                    "httpCode" => 500,
                    "message" => "Can't connect to Zalo API",
                    "data" => null,
                    "description" => "cURL error $errorNo: $error"
                ]);
            }

            $responseArr = json_decode($response, true);

            if ($httpCode < 200 || $httpCode >= 300) {
                return json_encode([
                    "error" => 1,
                    "httpCode" => $httpCode,
                    "message" => $responseArr["message"] ?? "Error $httpCode: Failed to handle request",
                    "data" => null,
                    "description" => $responseArr
                ]);
            }

            return $response;
        }
        catch (Throwable $th) {
            $message = "Exception error {$th->getCode()}: {$th->getMessage()} on line {$th->getLine()}";
            return json_encode([
                "error" => 1,
                "httpCode" => 500,
                "message" => "An exception error has occurred in BM",
                "data" => null,
                "description" => $message
            ]);
        }
        finally {
            if (isset($curl) && is_resource($curl)) curl_close($curl);
        }
    }

    public function get_phone_by_alias($alias) {
        if(is_null($alias) || empty($alias)) return '';
        preg_match_all('!\d+!', $alias, $matches);
        if(isset($matches[0]) && !empty($matches[0])) {
            foreach ($matches[0] as $number) {
                if (strlen($number) === 10) {
                    return $number;
                }
            }
        }
        return '';
    }

    public function format_zalo_phone($phone) {
        $phone_format = trim($phone);
        $phone_format = str_replace(' ', '', $phone_format);
        $phone_format = preg_replace('/^\+84/', 0, $phone_format);
        $phone_format = preg_replace('/^84/', 0, $phone_format);
        $phone_format = preg_replace('/^00/', 0, $phone_format);
        $phone_format = preg_replace('/^0/', '84', $phone_format);
        return $phone_format;
    }

    public function unformat_zalo_phone($zalo_phone) {
        if(!$zalo_phone || empty($zalo_phone)) return '';
        if(substr($zalo_phone, 0, 2) == 84) return trim('0' . substr($zalo_phone, 2));
        elseif(substr($zalo_phone, 0, 3) == "+84") return trim('0' . substr($zalo_phone, 3));
        return trim($zalo_phone);
    }

    /**
     * Get timestamp
     * 
     * @param string $timezone
     * @return int
     */
    public function get_timestamp($timezone = 'Asia/Ho_Chi_Minh') {
        date_default_timezone_set($timezone);
        return time();
    }
}
