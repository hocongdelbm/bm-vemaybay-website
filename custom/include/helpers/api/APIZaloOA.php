<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class APIZaloOA {
    private $template_path;
    private $images_path;
    private $domain;
    protected $oa_id;
    protected $app_id;
    protected $app_secret;
    protected $code_verifier;
    protected $code_challenge;

    public function __construct($oa_id = '') {
        global $sugar_config;
        $this->domain           = $sugar_config['host_name'] ?? $_SERVER['SERVER_NAME'];
        $this->template_path    = "custom/json_files/zalo_oa/templates.json";
        $this->images_path      = "https://{$this->domain}/custom/themes/default/images/zalo_oa";
        $this->oa_id            = is_string($oa_id) && !empty($oa_id) ? $oa_id : $sugar_config['zalo_config']['oa_id'] ?? '';
        // App info
        $this->app_id           = $sugar_config['zalo_config']['app_id'] ?? '';
        $this->app_secret       = $sugar_config['zalo_config']['app_secret'] ?? '';
        $this->code_verifier    = $sugar_config['zalo_config']['code_verifier'] ?? '';
        $this->code_challenge   = $sugar_config['zalo_config']['code_challenge'] ?? '';
    }

    public function get_oa_id() {return $this->oa_id;}
    public function get_app_id() {return $this->app_id;}
    public function get_images_path() {return $this->images_path;}


    /***************  AUTH  ***************/
    /** 
     * Return link to integrate zalo 
     * 
     * @return string url
     */
    public function get_link_integrate() {
        $entrypoint = "https://{$this->domain}/index.php?entryPoint=entryPointZaloAuthCallback";
        return "https://oauth.zaloapp.com/v4/oa/permission?app_id={$this->app_id}&redirect_uri=".urlencode($entrypoint);
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
            "secret_key: {$this->app_secret}"
        ];
        $requestBody = http_build_query([
            "code"          => $code,
            "app_id"        => $this->app_id,
            "grant_type"    => "authorization_code",
            "code_verifier" => $this->code_verifier,
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
            "secret_key: {$this->app_secret}"
        ];
        $requestBody = http_build_query([
            "app_id"        => $this->app_id,
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
        global $db;

        $arr = [];

        // Use cache
        if(isset($_SESSION) && isset($_SESSION['api_oauth_info']) && !empty($_SESSION['api_oauth_info'])) {
            $arr = $_SESSION['api_oauth_info'];
        }
        // Use query
        else {
            $api_oauth_info = $db->getOne("SELECT api_oauth_info FROM ec_zalo WHERE id = '{$this->oa_id}' AND deleted = 0");
            $arr = json_decode(html_entity_decode($api_oauth_info), true);   
        }

        $refresh_token = isset($arr['refresh_token']) ? $arr['refresh_token'] : '';
        $access_token = isset($arr['access_token']) ? $arr['access_token'] : '';
        $expires_at = isset($arr['expires_at']) ? $arr['expires_at'] : 0;

        if($type == 'refresh') return $refresh_token;
        elseif($type == 'access' && $expires_at > time()) return $access_token;
        else {
            $json = $this->get_new_token($refresh_token);
            $arr2 = json_decode($json, true);
            return isset($arr2['access_token']) ? $arr2['access_token'] : '';
        }
        return '';
    }

    /** 
     * Save token returned from api
     * 
     * @param string $json
     * @return bool
     */
    protected function save_token($json, $raw = false) {
        global $db;

        if($raw) {
            $query = "UPDATE ec_zalo SET api_oauth_info = '{$json}' WHERE id = '{$this->oa_id}' AND deleted = 0";
            if(isset($_SESSION) && isset($_SESSION['api_oauth_info'])) unset($_SESSION['api_oauth_info']);
            return $db->query($query);
        }

        if(!$json || empty($json)) {
            if(isset($_SESSION) && isset($_SESSION['api_oauth_info'])) unset($_SESSION['api_oauth_info']);
            return false;
        }

        $arr = json_decode($json, true);
        $api_oauth_info = [];
        $api_oauth_info['access_token'] = isset($arr['access_token']) ? $arr['access_token'] : '';
        $api_oauth_info['refresh_token'] = isset($arr['refresh_token']) ? $arr['refresh_token'] : '';
        $api_oauth_info['expires_in'] = isset($arr['expires_in']) ? $arr['expires_in'] : 90000; // Default 25h
        $api_oauth_info['expires_at'] = time() + $api_oauth_info['expires_in'];
        $api_oauth_info['expires_at_format'] = date('d-m-Y H:i:s', time() + $api_oauth_info['expires_in']);

        // Save cache
        if(isset($_SESSION)) $_SESSION['api_oauth_info'] = $api_oauth_info;

        // Save database
        $query = "UPDATE ec_zalo SET api_oauth_info = '".json_encode($api_oauth_info)."' WHERE id = '{$this->oa_id}' AND deleted = 0";
        return $db->query($query);
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
        return "https://oa.zalo.me/chatv2?uid={$zalo_id}&oaid={$this->oa_id}&src=share";
    }

    /** 
     * Send consulting messages
     * 
     * @param string $type
     * @param string $zalo_id
     * @param array $data
     * @return string json
     */
    public function send_consultation($type, $zalo_id, $data = []) {
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
    
    /** 
     * Send transaction message
     * 
     * @param string $zalo_id
     * @param string $type transaction_reward, transaction_order, transaction_billing,...
     * @param array $data
     * @return string json
     */
    public function send_transaction($zalo_id, $type, $header, $text, $table = [], $text2 = [], $buttons = []) {
        $url = "https://openapi.zalo.me/v3.0/oa/message/transaction";
        $header = [
            "Content-Type: application/json",
            "access_token: ". $this->get_token()
        ];
        $curlOptions = [
            CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
            CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
        ];

        // Request body
        $banner_link = '';
        switch ($type) {
            case 'transaction_reward':
                $banner_link = "https://$this->domain/modules/EC_Zalo/images/banner_points.jpg";
                break;
            default:
                $banner_link = '';
                break;
        } 

        $requestBody = [
            "recipient" => [
                "user_id" => $zalo_id
            ],
            "message" => [
                "attachment" => [
                    "type" => "template",
                    "payload" => [
                        "template_type" => $type, // Type
                        "language" => "VI",
                        "elements" => [
                            [
                                "type" => "banner",
                                "image_url" => $banner_link
                            ],
                            [
                                "type" => "header",
                                "content" => $header,
                                "align" => ""
                            ],
                            [
                                "type" => "text",
                                "content" => $text,
                                "align" => ""
                            ],
                        ],
                    ]
                ]
            ]
        ];
        if(!empty($table)) {
            $requestBody["message"]["attachment"]["payload"]["elements"][] = [
                "type" => "table",
                "content" => $table
            ];
        }
        if(!empty($text2)) {
            $requestBody["message"]["attachment"]["payload"]["elements"][] = [
                "type" => "text",
                "align" => "center",
                "content" => $text2
            ];
        }
        if(!empty($buttons)) $requestBody["message"]["attachment"]["payload"]["buttons"] = $buttons;

        return $this->send_request("POST", $url, json_encode($requestBody), $header, $curlOptions);
    }

    /** 
     * Send promotion message
     * 
     * @param string $zalo_id
     * @param string $banner_link
     * @param string $header
     * @param string $text 
     * @param array $table
     * @param string $text2
     * @param array $buttons 
     * @return string json
     */
    public function send_promotion($zalo_id, $banner_link, $header, $text, $table = [], $text2 = "", $buttons = []) {
        $url = "https://openapi.zalo.me/v3.0/oa/message/promotion";
        $header = [
            "Content-Type: application/json",
            "access_token: ". $this->get_token()
        ];
        $curlOptions = [
            CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
            CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
        ];

        // Request body
        $requestBody = [
            "recipient" => [
                "user_id" => $zalo_id
            ],
            "message" => [
                "attachment" => [
                    "type" => "template",
                    "payload" => [
                        "template_type" => "promotion", // Type
                        "language" => "VI",
                        "elements" => [
                            [
                                "type" => "banner",
                                "image_url" => $banner_link
                            ],
                            [
                                "type" => "header",
                                "content" => $header,
                                "align" => ""
                            ],
                            [
                                "type" => "text",
                                "content" => $text,
                                "align" => ""
                            ],
                        ],
                    ]
                ]
            ]
        ];
        if(!empty($table)) {
            $requestBody["message"]["attachment"]["payload"]["elements"][] = [
                "type" => "table",
                "content" => $table
            ];
        }
        if(!empty($text2)) {
            $requestBody["message"]["attachment"]["payload"]["elements"][] = [
                "type" => "text",
                "align" => "center",
                "content" => $text2
            ];
        }
        if(!empty($buttons)) $requestBody["message"]["attachment"]["payload"]["buttons"] = $buttons;

        return $this->send_request("POST", $url, json_encode($requestBody), $header, $curlOptions);
    }



    /***************  ZNS  ***************/
    /** 
     * Send ZNS message
     * Replaced by sendMessage() in class APIOMNI
     * 
     * @param string $phone
     * @param string $template_id
     * @param string|array $template_data json|array
     * @return string json
     */
    public function send_zns($phone, $template_id, $template_data) {
        return json_encode([
            "error" => 1,
            "httpCode" => 501,
            "message" => "Unsupported feature",
            "data" => null,
            "description" => "Replaced by sendMessage() in class APIOMNI"
        ]);
        
        $url = "https://business.openapi.zalo.me/message/template";
        $header = [
            "Content-Type: application/json",
            "access_token: ". $this->get_token()
        ];

        $requestBody = json_encode([
            'phone'         => $this->format_phone_number($phone, 'zalo'),
            'template_id'   => $template_id,
            'template_data' => is_array($template_data) ? $template_data : json_decode($template_data, true),
            'tracking_id'   => $phone . time()
        ]);

        $curlOptions = [
            CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
            CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
        ];

        return $this->send_request("POST", $url, $requestBody, $header, $curlOptions);
    }

    /** 
     * Send template id
     * 
     * @param string $type
     * @return string
     */
    public function get_template_id_zns($type) {
        return "";
        switch ($type) {
            case 'journey-one-way':
                return "347078"; // Hành trình một chiều
                break;
            case 'journey-round-trip':
                return "347088"; // Hành trình khứ hồi
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
                return "346699"; // CSKH sau khi gọi
                break;
            case 'delay':
                return "346656"; // Thông báo delay
                break;
            case 'remind-flight':
                return "346651"; // Nhắc nhở giờ bay
                break;
            case 'points':
                return "411270"; // Thông báo tích điểm
                break;
            case 'share-phone':
                return "433046"; // Gửi thông tin chương trình chia sẻ SĐT
                break;
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
    public function get_template_name_zns($template_id = null) {
        return "";
        switch ($template_id) {
            case '347078':
            case '347088':
                return "Thông tin hành trình";
                break;
            case '345209':
                return "Thông tin thanh toán";
                break;
            case '288276':
            case '288279':
                return "Thông tin code vé";
                break;
            case '346656':
                return "Thông báo delay";
                break;
            case '346651':
                return "Nhắc nhở giờ bay";
                break;
            case '346699':
                return "Chăm sóc khách hàng (Call sale)";
                break;
            case '411270':
                return "Thông báo tích điểm";
                break;
            case '433046':
                return "Gửi thông tin chương trình chia sẻ SĐT";
                break;
            default:
                return "";
        }
    }

    /** 
     * Get error description when sending ZNS fail
     * 
     * @param int $error_code
     * @return string
     */
    public function get_error_description_zns($error_code) {
        switch($error_code) {
            case -108:
                return "Số điện thoại không hợp lệ.";
            case -110:
                return "Phiên bản Zalo app của người dùng quá cũ nên không được hỗ trợ";
            case -111:
                return "Mẫu ZNS không có dữ liệu";
            case -114:
            case -119:
            case -139:
            case -141:
                return "Số điện thoại này không thể nhận tin. Người dùng không nhận được ZNS vì các lý do: Người dùng từ chối nhận ZNS từ OA, Trạng thái tài khoản, Tùy chọn nhận ZNS, Sử dụng Zalo phiên bản cũ, hoặc các lỗi nội bộ khác...";
            case -115:
            case -126:
                return "Tài khoản ZNS không đủ số dư";
            case -116:
            case -121:
            case -130:
            case -131:
                return "Nội dung tin không hợp lệ";
            case -118:
                return "Số điện thoại không có Zalo";
            case -133:
                return "Không được phép gửi tin vào ban đêm (từ 22h-6h)";
            case -137:
                return "Thanh toán ZCA thất bại (ví không đủ số dư, ...)";
            case -144:
            case -147:
                return "OA đã vượt giới hạn gửi ZNS trong ngày";
            case -146:
                return "Mẫu tin này đã bị vô hiệu hóa do chất lượng gửi thấp";
            default:
                return "Gửi tin nhắn thất bại";
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
     * Get template zalo message
     * 
     * @param string $name
     * @param string $return_type
     * @return mixed
     */
    public function get_template($name, $return_type = 'array') {
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

    public function format_phone_number($phone, $type = '') {
        $phone_format = trim($phone);
    
        if($type == 'zalo') {
            $phone_format = str_replace(' ', '', $phone_format);
            $phone_format = preg_replace('/^\+84/', 0, $phone_format);
            $phone_format = preg_replace('/^84/', 0, $phone_format);
            $phone_format = preg_replace('/^00/', 0, $phone_format);
            $phone_format = preg_replace('/^0/', '84', $phone_format);
        }
        else {
            $phone_format = str_replace(' ', '', $phone_format);
            $phone_format = str_replace('+', '', trim($phone_format));
            $phone_format = str_replace('84', '0', $phone);
        }
    
        return $phone_format;
    }

    public function unformat_zalo_phone($zalo_phone) {
        if(!$zalo_phone || empty($zalo_phone)) return '';
        if(substr($zalo_phone, 0, 2) == 84) return trim('0' . substr($zalo_phone, 2));
        elseif(substr($zalo_phone, 0, 3) == "+84") return trim('0' . substr($zalo_phone, 3));
        return trim($zalo_phone);
    }
}
