<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class APIZaloOA {
    /**
     * @var EC_Zalo_Apps
     */
    public $app;
    /**
     * @var EC_Zalo
     */
    protected $oa;
    protected $domain;
    protected const TEMPLATE_PATH = "custom/json_files/zalo_messages/templates.json";
    protected const ERROR_MAP = [
        0 => "Thành công: Gửi yêu cầu thành công.",
        // RATE LIMIT
        -32 => "Vượt giới hạn API: Giảm tần suất request.",
        // TEMPLATE / CONTENT
        -109 => "ID Template không hợp lệ.",
        -110 => "Zalo phiên bản cũ: Yêu cầu user cập nhật app.",
        -111 => "Template trống: Không có dữ liệu gửi.",
        -1121 => "Tham số quá dài.",
        -1122 => "Thiếu tham số bắt buộc.",
        -1123 => "Lỗi QR code.",
        -1124 => "Sai định dạng tham số.",
        -113 => "Button không hợp lệ.",
        -1131 => "Link không đúng định dạng.",
        -116 => "Nội dung tham số không hợp lệ.",
        -117 => "Không có quyền dùng Template.",
        -121 => "Template không có nội dung.",
        -122 => "Sai định dạng JSON.",
        -130 => "Vượt giới hạn ký tự (max 100k).",
        -131 => "Template chưa được duyệt.",
        -132 => "Tham số không hợp lệ.",
        -147 => "Template vượt giới hạn gửi trong ngày.",
        -1471 => "Vượt giới hạn gửi tháng (user).",
        -1472 => "Vượt giới hạn gửi ngày (promotion).",
        // ATTACHMENT / FILE
        -100 => "Tệp đính kèm hết hạn, tải lên lại để lấy ID mới.",
        -158 => "File quá lớn.",
        -159 => "Định dạng file không hỗ trợ.",
        // REQUEST / DATA
        -153 => "Dữ liệu request sai cấu trúc hoặc không hợp lệ.",
        -210 => "Tham số vượt giới hạn cho phép.",
        -201 => "Tham số không hợp lệ.",
        // SYSTEM / UNKNOWN
        -200 => "Gửi tin nhắn thất bại.",
        // APP / AUTH
        -101 => "Ứng dụng không hợp lệ.",
        -103 => "Ứng dụng chưa kích hoạt.",
        -104 => "Secret key không hợp lệ.",
        -106 => "Phương thức API không hỗ trợ.",
        -124 => "Access token không hợp lệ hoặc hết hạn.",
        -1241 => "appsecret_proof không hợp lệ.",
        -216 => "Access token không hợp lệ hoặc hết hạn.",
        -220 => "Access token đã hết hạn hoặc bị thu hồi.",
        -219 => "Ứng dụng đã bị vô hiệu hóa hoặc gỡ bỏ.",
        -209 => "API chưa được kích hoạt.",
        -212 => "Ứng dụng chưa đăng ký API.",
        -223 => "OA chưa cấp quyền cho API hoặc vượt quota nội dung.",
        -242 => "appsecret_proof cung cấp trong tham số API không hợp lệ.",
        // OA / ACCOUNT
        -120 => "OA chưa có quyền sử dụng tính năng.",
        -1202 => "OA không có quyền dùng media.",
        -125 => "OA ID không hợp lệ.",
        -204 => "OA đã bị vô hiệu hóa hoặc bị xóa.",
        -205 => "OA không tồn tại.",
        -221 => "OA chưa xác thực.",
        -224 => "OA chưa nâng cấp gói dịch vụ.",
        -135 => "OA chưa xác thực (gửi qua SĐT).",
        -1351 => "OA bị chặn do vi phạm chính sách.",
        -136 => "Chưa kết nối Zalo Cloud Account.",
        -138 => "Ứng dụng chưa được cấp quyền API.",
        -1381 => "Extension chưa có quyền.",
        -235 => "API không hỗ trợ loại OA này.",
        // USER
        -108 => "Số điện thoại không hợp lệ.",
        -118 => "Tài khoản người dùng không tồn tại.",
        -139 => "Người dùng từ chối nhận tin.",
        -140 => "Người dùng không đủ điều kiện nhận tin.",
        -141 => "Người dùng đã chặn OA.",
        -213 => "Người dùng chưa quan tâm OA.",
        -217 => "Người dùng đã chặn lời mời.",
        -218 => "Vượt giới hạn gửi tới user.",
        -227 => "User bị khóa hoặc không hoạt động >45 ngày.",
        -230 => "User không tương tác trong 7 ngày.",
        -232 => "User chưa tương tác hoặc đã hết hạn tương tác.",
        -244 => "User hạn chế nhận loại tin nhắn này.",
        // QUOTA / BILLING
        -115 => "Tài khoản không đủ số dư.",
        -126 => "Ví development không đủ tiền.",
        -137 => "Thanh toán thất bại.",
        -144 => "Vượt quota gửi ngày.",
        -1441 => "Vượt quota promotion tháng.",
        -211 => "Vượt quota sử dụng.",
        -320 => "Chưa kết nối Zalo Cloud Account.",
        -321 => "Zalo Cloud Account không đủ tiền.",
        // MESSAGE / POLICY
        -145 => "Loại tin nhắn không được phép.",
        -233 => "Loại tin nhắn không hợp lệ.",
        -234 => "Không được gửi tin từ 22h - 6h.",
        -248 => "Vi phạm chính sách nền tảng.",
        -249 => "Template không hỗ trợ gửi qua UID.",
        // JOURNEY / TOKEN
        -148 => "Journey token không hợp lệ hoặc hết hạn.",
        -149 => "Journey token không hợp lệ hoặc hết hạn.",
        -150 => "Journey token không hợp lệ hoặc hết hạn.",
        // GROUP / ASSET
        -237 => "Nhóm chat đã hết hạn.",
        -238 => "asset_id không hợp lệ hoặc đã dùng.",
        -241 => "asset_id miễn phí đã được sử dụng.",
        -403 => "OA không thuộc nhóm chat này.",
        // API VERSION
        -240 => "API V2 đã ngừng: Hãy dùng API V3.",
        // FORM
        -1340 => "Không tìm thấy Form.",
        -1341 => "OA không có quyền truy cập Form.",
        // OTHER
        -107 => "ID thông báo không hợp lệ.",
        -127 => "Tin test chỉ gửi cho admin.",
        -142 => "Thiếu RSA key.",
        -143 => "RSA key đã tồn tại.",
        -160 => "Vượt quota tạo Template.",
        -161 => "sending_mode không hợp lệ.",
        -162 => "sending_mode không được hỗ trợ.",
    ];

    public function __construct($app_id = '', $oa_id = '') {
        global $sugar_config;
        $this->domain = $sugar_config['host_name'] ?? $_SERVER['SERVER_NAME'];

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
        $error_code = (int)($error_code);
        return self::ERROR_MAP[$error_code] ?? "Lỗi hệ thống ($error_code), vui lòng kiểm tra lại.";
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

        if(file_exists(self::TEMPLATE_PATH)) {
            $json = file_get_contents(self::TEMPLATE_PATH);
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
