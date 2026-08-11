<?php
class APIChatUserSync
{
    private $restUrl;

    public function __construct()
    {
        global $sugar_config;
        $chat = $sugar_config['chat_widget'] ?? [];
        $this->restUrl = rtrim($chat['restUrl'] ?? '', '/');
    }

    /**
     * @param string $userId SuiteCRM user id — shared _id with chat's users collection
     * @param array $fields any of Username/FullName/Email/Phone/Title/Status/IsAdmin
     * @return array|false decoded response body, or false on failure/not available
     */
    public function upsertUser($userId, array $fields)
    {
        return $this->request('PATCH', '/api/suitecrm/users/' . rawurlencode($userId), $fields);
    }

    /**
     * @param string $userId
     * @param string $password plaintext — chat hashes it server-side
     * @return array|false
     */
    public function syncPassword($userId, $password)
    {
        return $this->request('POST', '/api/suitecrm/users/' . rawurlencode($userId) . '/password', [
            'Password' => $password,
        ]);
    }

    private function authHeaderB64()
    {
        if (!empty($_SESSION['chat_credentials_b64'])) {
            return $_SESSION['chat_credentials_b64'];
        }
        global $current_user;
        if (!empty($current_user->id) && !empty($_SESSION['chat_login_password'])) {
            return base64_encode($current_user->id . ':' . $_SESSION['chat_login_password']);
        }
        return null;
    }

    private function request($method, $path, array $body)
    {
        $authB64 = $this->authHeaderB64();
        if ($this->restUrl === '' || $authB64 === null) {
            return false;
        }

        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $this->restUrl . $path);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Basic ' . $authB64,
            ]);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($curl, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);
            curl_close($curl);

            if ($error) {
                $GLOBALS['log']->warn("APIChatUserSync: $method $path failed: $error");
                return false;
            }
            if ($httpCode >= 300) {
                $GLOBALS['log']->warn("APIChatUserSync: $method $path returned HTTP $httpCode: $response");
                return false;
            }
            return json_decode($response, true);
        } catch (\Throwable $e) {
            $GLOBALS['log']->warn('APIChatUserSync: ' . $method . ' ' . $path . ' exception: ' . $e->getMessage());
            return false;
        }
    }
}
