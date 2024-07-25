<?php
global $sugar_config;
define("SUGAR_CONFIG", $sugar_config);

class Zalo {
    private $token_path;
    private $template_path;
    private $domain;

    public function __construct() {
        $this->token_path = "modules/EC_Zalo/json/token.json";
        $this->template_path = "modules/EC_Zalo/json/templates.json";
        $this->domain = $_SERVER['SERVER_NAME'];
    }

    public function get_oa_id() {
        return SUGAR_CONFIG['zalo_config']['oa_id'] ? SUGAR_CONFIG['zalo_config']['oa_id'] : '';
    }
    public function get_app_id() {
        return SUGAR_CONFIG['zalo_config']['app_id'] ? SUGAR_CONFIG['zalo_config']['app_id'] : '';
    }
    protected function get_app_secret() {
        return SUGAR_CONFIG['zalo_config']['app_secret'] ? SUGAR_CONFIG['zalo_config']['app_secret'] : '';
    }
    public function get_code_verifier() {
        return SUGAR_CONFIG['zalo_config']['code_verifier'] ? SUGAR_CONFIG['zalo_config']['code_verifier'] : '';
    }
    public function get_code_challenge() {
        return SUGAR_CONFIG['zalo_config']['code_challenge'] ? SUGAR_CONFIG['zalo_config']['code_challenge'] : '';
    }



    /***************  AUTH  ***************/
    /** 
     * Return link to integrate zalo 
     * 
     * @return string url
     */
    public function get_link_integrate() {
        $entrypoint = 'https://'.$this->domain.'/index.php?entryPoint=entryPointZaloAuthCallback';
        return 'https://oauth.zaloapp.com/v4/oa/permission?app_id='.$this->get_app_id().'&redirect_uri='.urlencode($entrypoint);
    }

    /** 
     * Generate code verifier and code challenge using PKCE
     * 
     * @return array [code_verifier, code_challenge]
     */
    public function generate_code() {
        // Code_verifier là một chuỗi ASCII
        $code_verifier = $this->random_string(43);
        // Hash code_verifier bằng SHA-256
        $sha256_hash = hash('sha256', $code_verifier, true);
        // Encode kết quả hash bằng Base64
        $code_challenge = base64_encode($sha256_hash);
        // Chuẩn hóa PKCE
        $code_challenge = str_replace(['+', '/', '='], ['-', '_', ''], $code_challenge);
        return json_encode(['code_verifier' => $code_verifier, 'code_challenge' => $code_challenge]);
    }
    
    /** 
     * Get access token by authorization cod
     * 
     * @param string $code
     * @return string json
     */
    public function get_new_token_auth($code) {
        try {
            $data = [
                "code" => $code,
                "app_id" => $this->get_app_id(),
                "grant_type" => "authorization_code",
                "code_verifier" => $this->get_code_verifier(),
            ];

            $curl = curl_init();
            if ($curl === false) {
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => 'cURL Failed to initialize']);
            }

            curl_setopt_array($curl, [
                CURLOPT_URL            => "https://oauth.zaloapp.com/v4/oa/access_token",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT        => 0,
                CURLOPT_CUSTOMREQUEST  => "POST",
                CURLOPT_POSTFIELDS     => http_build_query($data),
                CURLOPT_HTTPHEADER     => [
                    "Content-Type: application/x-www-form-urlencoded",
                    "secret_key: ".$this->get_app_secret()
                ],
            ]);
            $json = curl_exec($curl);

            if ($json === false) {
                $m = trim(curl_error($curl).' ('.curl_errno($curl).')');
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => $m]);
            }

            // Process response
            // $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $arr = json_decode($json, true);
            if(isset($arr['access_token']) && !empty($arr['access_token'])) $this->save_token($json);
            else $this->save_token($json, true);

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'httpcode' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage()]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }

    /** 
     * Get access token from refresh token
     * 
     * @param string $refresh_token
     * @return string json
     */
    public function get_new_token($refresh_token) {
        try {
            $data = [
                "app_id" => $this->get_app_id(),
                "grant_type" => "refresh_token",
                "refresh_token" => $refresh_token
            ];

            $curl = curl_init();
            if ($curl === false) {
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => 'cURL Failed to initialize']);
            }

            curl_setopt_array($curl, [
                CURLOPT_URL            => "https://oauth.zaloapp.com/v4/oa/access_token",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
                CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
                CURLOPT_TIMEOUT        => 0,
                CURLOPT_CUSTOMREQUEST  => "POST",
                CURLOPT_POSTFIELDS     => http_build_query($data),
                CURLOPT_HTTPHEADER     => [
                    "Content-Type: application/x-www-form-urlencoded",
                    "secret_key: ".$this->get_app_secret()
                ],
            ]);
            $json = curl_exec($curl);

            if ($json === false) {
                $m = trim(curl_error($curl).' ('.curl_errno($curl).')');
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => $m]);
            }

            // Process response
            // $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $arr = json_decode($json, true);
            if(isset($arr['access_token']) && !empty($arr['access_token'])) $this->save_token($json);
            else $this->save_token($json, true);

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'httpcode' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage()]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }

    /** 
     * Get token
     * 
     * @param string $type
     * @return string
     */
    public function get_token($type = "access") {
        if(file_exists($this->token_path)) {
            $arr = json_decode(file_get_contents($this->token_path), true);

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

        return '';
    }

    /** 
     * Save token returned from api
     * 
     * @param string $json
     * @return bool
     */
    protected function save_token($json, $raw = false) {
        if($raw) {
            $file = fopen($this->token_path, "w") or die("Error something !!!");
            fwrite($file, json_encode($json));
            fclose($file);
            return true;
        }

        if(!$json || empty($json)) return false;

        $arr = json_decode($json, true);
        $result = [];
        $result['access_token'] = isset($arr['access_token']) ? $arr['access_token'] : '';
        $result['refresh_token'] = isset($arr['refresh_token']) ? $arr['refresh_token'] : '';
        $result['expires_in'] = isset($arr['expires_in']) ? $arr['expires_in'] : 90000; // Default 25h
        $result['expires_at'] = time() + $result['expires_in'];
        $result['expires_at_format'] = date('d-m-Y H:i:s', time() + $result['expires_in']);

        $file = fopen($this->token_path, "w") or die("Error something !!!");
        fwrite($file, json_encode($result));
        fclose($file);
        return true;
    }



    /****************  OA  ****************/
    /** 
     * Get info OA
     * 
     * @return string json
     */
    public function get_info_oa() {
        try {
            $curl = curl_init();
            if ($curl === false) {
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => 'cURL Failed to initialize']);
            }

            curl_setopt_array($curl, [
                CURLOPT_URL            => "https://openapi.zalo.me/v2.0/oa/getoa",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
                CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
                CURLOPT_TIMEOUT        => 0,
                CURLOPT_HTTPHEADER     => ["access_token: ". $this->get_token()]
            ]);
            $json = curl_exec($curl);

            if ($json === false) {
                $m = trim(curl_error($curl).' ('.curl_errno($curl).')');
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => $m]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'httpcode' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage()]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
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
        $data = [
            "quota_owner"   => $quota_owner,
            "product_type"  => $product_type, // Tin tư vấn
            "quota_type"    => $quota_type // Quota tin theo gói dịch vụ OA
        ];

        try {
            $curl = curl_init();
            if ($curl === false) {
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => 'cURL Failed to initialize']);
            }

            curl_setopt_array($curl, [
                CURLOPT_URL            => "https://openapi.zalo.me/v3.0/oa/quota/message",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
                CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
                CURLOPT_TIMEOUT        => 0,
                CURLOPT_CUSTOMREQUEST  => 'POST',
                CURLOPT_POSTFIELDS     => json_encode($data),
                CURLOPT_HTTPHEADER     => [
                    "access_token: ". $this->get_token(),
                    "Content-Type: application/json"
                ]
            ]);
            $json = curl_exec($curl);

            if ($json === false) {
                $m = trim(curl_error($curl).' ('.curl_errno($curl).')');
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => $m]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'httpcode' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage()]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
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

        try {
            $curl = curl_init();
            if ($curl === false) {
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => 'cURL Failed to initialize']);
            }

            curl_setopt_array($curl, [
                CURLOPT_URL            => "https://openapi.zalo.me/v3.0/oa/user/getlist?data=" . urlencode($data),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
                CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
                CURLOPT_TIMEOUT        => 0,
                CURLOPT_HTTPHEADER     => ["access_token: ". $this->get_token()]
            ]);
            $json = curl_exec($curl);

            if ($json === false) {
                $m = trim(curl_error($curl).' ('.curl_errno($curl).')');
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => $m]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'httpcode' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage()]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }

    /** 
     * Get user information
     * 
     * @param string $zalo_id
     * @return string json
     */
    public function get_user($zalo_id) {
        $data = json_encode(['user_id' => $zalo_id]);

        try {
            $curl = curl_init();
            if ($curl === false) {
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => 'cURL Failed to initialize']);
            }

            curl_setopt_array($curl, [
                CURLOPT_URL            => "https://openapi.zalo.me/v3.0/oa/user/detail?data=$data",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
                CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
                CURLOPT_TIMEOUT        => 0,
                CURLOPT_HTTPHEADER     => ["access_token: ". $this->get_token()]
            ]);
            $json = curl_exec($curl);

            if ($json === false) {
                $m = trim(curl_error($curl).' ('.curl_errno($curl).')');
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => $m]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'httpcode' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage()]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }

    /** 
     * Get limits for sending messages to user
     * 
     * @param string $zalo_id
     * @return string json
     */
    public function get_quota_user($zalo_id) {
        $data = json_encode(['user_id' => $zalo_id]);

        try {
            $curl = curl_init();
            if ($curl === false) {
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => 'cURL Failed to initialize']);
            }

            curl_setopt_array($curl, [
                CURLOPT_URL            => "https://openapi.zalo.me/v3.0/oa/quota/message",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
                CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
                CURLOPT_CUSTOMREQUEST  => "POST",
                CURLOPT_POSTFIELDS     => $data,
                CURLOPT_TIMEOUT        => 0,
                CURLOPT_HTTPHEADER     => [
                    "Content-Type: application/json",
                    "access_token: ". $this->get_token()
                ]
            ]);
            $json = curl_exec($curl);

            if ($json === false) {
                $m = trim(curl_error($curl).' ('.curl_errno($curl).')');
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => $m]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'httpcode' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage()]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }

    /** 
     * Get the list of tags
     * 
     * @return string json
     */
    public function get_list_tag() {
        try {
            $curl = curl_init();
            if ($curl === false) {
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => 'cURL Failed to initialize']);
            }

            curl_setopt_array($curl, [
                CURLOPT_URL            => "https://openapi.zalo.me/v2.0/oa/tag/gettagsofoa",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
                CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
                CURLOPT_TIMEOUT        => 0,
                CURLOPT_HTTPHEADER     => ["access_token: ". $this->get_token()]
            ]);
            $json = curl_exec($curl);

            if ($json === false) {
                $m = trim(curl_error($curl).' ('.curl_errno($curl).')');
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => $m]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'httpcode' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage()]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
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
        $data = ["user_id" => $zalo_id];
        if(!empty($user_alias)) $data['user_alias'] = $user_alias;
        if(!empty($shared_info) && count($shared_info) > 4) $data['shared_info'] = $shared_info;

        try {
            $curl = curl_init();
            if ($curl === false) {
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => 'cURL Failed to initialize']);
            }

            curl_setopt_array($curl, [
                CURLOPT_URL            => "https://openapi.zalo.me/v3.0/oa/user/update",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
                CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
                CURLOPT_TIMEOUT        => 0,
                CURLOPT_CUSTOMREQUEST  => 'POST',
                CURLOPT_POSTFIELDS     => json_encode($data),
                CURLOPT_HTTPHEADER     => [
                    "access_token: ". $this->get_token(),
                    "Content-Type: application/json"
                ]
            ]);
            $json = curl_exec($curl);

            if ($json === false) {
                $m = trim(curl_error($curl).' ('.curl_errno($curl).')');
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => $m]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'httpcode' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage()]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
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

        try {
            $curl = curl_init();
            if ($curl === false) {
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => 'cURL Failed to initialize']);
            }

            curl_setopt_array($curl, [
                CURLOPT_URL            => "https://openapi.zalo.me/v2.0/oa/conversation?data=$data",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
                CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
                CURLOPT_TIMEOUT        => 0,
                CURLOPT_HTTPHEADER     => [
                    "Content-Type: application/json",
                    "access_token: ". $this->get_token()
                ]
            ]);
            $json = curl_exec($curl);

            if ($json === false) {
                $m = trim(curl_error($curl).' ('.curl_errno($curl).')');
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => $m]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'httpcode' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage()]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
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

        try {
            $curl = curl_init();
            if ($curl === false) {
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => 'cURL Failed to initialize']);
            }

            curl_setopt_array($curl, [
                CURLOPT_URL            => "https://openapi.zalo.me/v2.0/oa/listrecentchat?data=$data",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
                CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
                CURLOPT_TIMEOUT        => 0,
                CURLOPT_HTTPHEADER     => [
                    "access_token: ". $this->get_token()
                ]
            ]);
            $json = curl_exec($curl);

            if ($json === false) {
                $m = trim(curl_error($curl).' ('.curl_errno($curl).')');
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => $m]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'httpcode' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage()]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }

    /** 
     * Get chat link on zalo
     * 
     * @param string $zalo_id
     * @return string url
     */
    public function get_chat_link($zalo_id) {
        return "https://oa.zalo.me/chatv2?uid=$zalo_id&oaid=".$this->get_oa_id()."&src=share";
    }

    /** 
     * Send consulting messages
     * 
     * @param string $type
     * @param string $zalo_id
     * @param array $data
     * @return string json
     */
    public function send_consultation($type, $zalo_id, $data = array()) {
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
            $body_request = json_encode($arr);
        }
        elseif($type == 'image') {
            if(!isset($data["element"]) || empty($data["element"]))
                return json_encode(['error' => 1, 'httpcode' => 403, 'message' => 'Invalid parameters', 'data' => $data]);

            $body_request = json_encode([
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

            $body_request = json_encode([
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

            $body_request = json_encode([
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

        try {
            $curl = curl_init();
            if ($curl === false) {
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => 'cURL Failed to initialize']);
            }

            curl_setopt_array($curl, [
                CURLOPT_URL            => "https://openapi.zalo.me/v3.0/oa/message/cs",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
                CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
                CURLOPT_TIMEOUT        => 0,
                CURLOPT_CUSTOMREQUEST  => "POST",
                CURLOPT_POSTFIELDS     => $body_request,
                CURLOPT_HTTPHEADER     => [
                    "Content-Type: application/json",
                    "access_token: ". $this->get_token()
                ]
            ]);
            $json = curl_exec($curl);

            if ($json === false) {
                $m = trim(curl_error($curl).' ('.curl_errno($curl).')');
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => $m]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'httpcode' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage()]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }



    /***************  ZNS  ***************/
    /** 
     * Send ZNS message
     * 
     * @param string $phone
     * @param string $template_id
     * @param string $template_data json
     * @return string json
     */
    public function send_zns($phone, $template_id, $template_data) {
        $body_request = [
            'phone'         => $this->format_phone_number($phone, 'zalo'),
            'template_id'   => $template_id,
            'template_data' => $template_data,
            'tracking_id'   => $phone . time()
        ];

        try {
            $curl = curl_init();
            if ($curl === false) {
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => 'cURL Failed to initialize']);
            }

            curl_setopt_array($curl, [
                CURLOPT_URL            => "https://business.openapi.zalo.me/message/template",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
                CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
                CURLOPT_CUSTOMREQUEST  => "POST",
                CURLOPT_POSTFIELDS     => $body_request,
                CURLOPT_TIMEOUT        => 0,
                CURLOPT_HTTPHEADER     => [
                    "Content-Type: application/json",
                    "access_token: ". $this->get_token()
                ]
            ]);
            $json = curl_exec($curl);

            if ($json === false) {
                $m = trim(curl_error($curl).' ('.curl_errno($curl).')');
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => $m]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'httpcode' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage()]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }

    /** 
     * Send template id
     * 
     * @param string $type
     * @return string
     */
    public function get_template_id_zns($type) {
        if(is_null($type) || empty($type)) return "";

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
            default:
                return "";
        }
    }

    /** 
     * Send template name
     * 
     * @param string $id
     * @return string
     */
    public function get_template_name_zns($id = null) {
        if(is_null($id) || empty($id)) return "";
    
        switch ($id) {
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
            default:
                return "";
        }
    }



    /***************  UPLOAD  ***************/
    /** 
     * Upload to zalo
     * @param string $type
     * @return array
     */
    public function get_file_extension($type) {
        if($type == 'image') return ['png', 'jpg', 'gif'];
        elseif($type == 'file') return ['pdf', 'doc', 'docx', 'csv', 'txt'];
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

        try {
            $curl = curl_init();
            if ($curl === false) {
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => 'cURL Failed to initialize']);
            }

            curl_setopt_array($curl, [
                CURLOPT_URL            => "https://openapi.zalo.me/v2.0/oa/upload/$type",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYHOST => $this->domain == 'localhost' ? 0 : 2,
                CURLOPT_SSL_VERIFYPEER => $this->domain == 'localhost' ? 0 : 1,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => ['file' => new CURLFile($path, null, $name)],
                CURLOPT_TIMEOUT        => 0,
                CURLOPT_HTTPHEADER     => [
                    "access_token: ". $this->get_token()
                ]
            ]);
            $json = curl_exec($curl);

            if ($json === false) {
                $m = trim(curl_error($curl).' ('.curl_errno($curl).')');
                return json_encode(['error' => 1, 'httpcode' => null, 'message' => $m]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'httpcode' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage()]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
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
    
    public function random_string($length) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
?>