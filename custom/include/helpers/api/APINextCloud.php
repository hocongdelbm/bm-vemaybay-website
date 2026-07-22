<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class APINextCloud
{
    private $ENDPOINT;
    private $USERNAME;
    private $PASSWORD;
    private $BASE_OCS_URL;
    private $USER_ERROR_CODE = 400;
    private $SYSTEM_ERROR_CODE = 500;
    private $VERIFY_SSL = false;
    private $FOLLOW_REDIRECTS = true;
    private $CONNECT_TIMEOUT_MS = 20000;
    private $TIMEOUT_MS = 60000;
    private $DEADLINE_AT_MS;
    private $CONNECT_TIMEOUT_CONFIGURED = false;
    private $TIMEOUT_CONFIGURED = false;


    public function __construct(array $options = [])
    {
        global $sugar_config;
        $this->USERNAME = $sugar_config['next-cloud']['user'];
        $this->PASSWORD = $sugar_config['next-cloud']['password'];
        $this->ENDPOINT = rtrim($sugar_config['next-cloud']['endpoint'], '/') . '/' . $this->USERNAME;
        $this->BASE_OCS_URL = $sugar_config['next-cloud']['base_url_ocs'];

        if (array_key_exists('verify_ssl', $options)) {
            $this->VERIFY_SSL = (bool) $options['verify_ssl'];
        }
        if (array_key_exists('follow_redirects', $options)) {
            $this->FOLLOW_REDIRECTS = (bool) $options['follow_redirects'];
        }
        if (isset($options['connect_timeout_ms']) && is_numeric($options['connect_timeout_ms'])
            && (int) $options['connect_timeout_ms'] > 0
        ) {
            $this->CONNECT_TIMEOUT_MS = (int) $options['connect_timeout_ms'];
            $this->CONNECT_TIMEOUT_CONFIGURED = true;
        }
        if (isset($options['timeout_ms']) && is_numeric($options['timeout_ms'])
            && (int) $options['timeout_ms'] > 0
        ) {
            $this->TIMEOUT_MS = (int) $options['timeout_ms'];
            $this->TIMEOUT_CONFIGURED = true;
        }
        if (isset($options['deadline_at_ms']) && is_numeric($options['deadline_at_ms'])
            && (float) $options['deadline_at_ms'] > 0
        ) {
            $this->DEADLINE_AT_MS = (float) $options['deadline_at_ms'];
        }
    }

    public function createFolder($remoteFolderPath)
    {
        if ($error = $this->validateRemoteFileName($remoteFolderPath)) {
            return $error;
        }
        $url = $this->buildDavUrl($remoteFolderPath, true);
        $header = [
            "Authorization: Basic " . base64_encode($this->USERNAME . ":" . $this->PASSWORD)
        ];
        return $this->sendRequest('MKCOL', $url, $header);
    }


    public function uploadFile($localFilePath, $remoteFileName, array $options = [])
    {

        if ($error = $this->validateLocalFile($localFilePath)) {
            return $error;
        }
        if ($error = $this->validateRemoteFileName($remoteFileName)) {
            return $error;
        }
        $url = $this->buildDavUrl($remoteFileName);
        $fileData = file_get_contents($localFilePath); // Read file content
        if ($fileData === false) {
            return $this->createErrorResponse(
                "Unable to read local file",
                "The specified local file '$localFilePath' could not be read.",
                $this->SYSTEM_ERROR_CODE
            );
        }
        $header = [
            "Authorization: Basic " . base64_encode($this->USERNAME . ":" . $this->PASSWORD),
            "Content-Type: application/octet-stream",
            "Content-Length: " . strlen($fileData)
        ];
        if (!empty($options['prevent_overwrite'])) {
            $header[] = 'If-None-Match: *';
        }
        return $this->sendRequest('PUT', $url, $header, $fileData);
    }

    public function deleteFile($remoteFileName)
    {
        if ($error = $this->validateRemoteFileName($remoteFileName)) {
            return $error;
        }
        $url = $this->buildDavUrl($remoteFileName);
        $header = [
            "Authorization: Basic " . base64_encode($this->USERNAME . ":" . $this->PASSWORD)
        ];
        return $this->sendRequest('DELETE', $url, $header);
    }

    public function removeFile($remoteFileName, $destitationPath)
    {
        if ($error = $this->validateRemoteFileName($remoteFileName)) {
            return $error;
        }
        if ($error = $this->validateRemoteFileName($destitationPath)) {
            return $error;
        }

        $url = $this->buildDavUrl($remoteFileName);
        $header = [
            "Authorization: Basic " . base64_encode($this->USERNAME . ":" . $this->PASSWORD),
            "Destination: " . $this->buildDavUrl($destitationPath)
        ];
        return $this->sendRequest('MOVE', $url, $header);
    }

    /**
     * ======Public API Methods ======
     */
        /**
     * Create a public share for a file or folder
     * 
     * @param string $remoteFolderPath Path to the file/folder on NextCloud (e.g., "/Documents/MyFolder")
     * @param int $permissions Optional permissions (1=read, 15=read+write+create+delete)
     * @param string $password Optional password for the share
     * @param string $expireDate Optional expire date (Y-m-d format)
     * 
     * @return string JSON response containing share link
     */
    public function createShare($remoteFolderPath, $permissions = 1, $password = null, $expireDate = null)
    {
        if (empty($remoteFolderPath)) {
            return $this->createErrorResponse(
                "Remote path is required",
                "The remote file/folder path cannot be empty.",
                $this->USER_ERROR_CODE
            );
        }

        // OCS API endpoint for creating shares
        $url = $this->BASE_OCS_URL . '?format=json';
        
        $postData = [
            'path' => $remoteFolderPath,
            'shareType' => 3, // 3 = public link
            'permissions' => $permissions
        ];

        if (!is_null($password)) {
            $postData['password'] = $password;
        }

        $header = [
            "Authorization: Basic " . base64_encode($this->USERNAME . ":" . $this->PASSWORD),
            "Content-Type: application/x-www-form-urlencoded",
            "OCS-APIRequest: true"
        ];

        return $this->sendRequest('POST', $url, $header, http_build_query($postData));
    }

    /**
     * Get all shares for a path or all shares for current user
     * 
     * @param string $path Optional path to get shares for
     * @param bool $reshares Include reshares
     * @param bool $subfiles Include shares of subfiles
     * 
     * @return string JSON response
     */
    public function getShares($path = null, $reshares = false, $subfiles = false)
    {
        $url = $this->BASE_OCS_URL . '?format=json';
        
        $params = [
            'reshares' => $reshares ? 'true' : 'false',
            'subfiles' => $subfiles ? 'true' : 'false'
        ];
        
        if (!is_null($path)) {
            $params['path'] = $path;
        }
        
        $url .= '&' . http_build_query($params);
        
        $header = [
            "Authorization: Basic " . base64_encode($this->USERNAME . ":" . $this->PASSWORD),
            "OCS-APIRequest: true"
        ];

        return $this->sendRequest('GET', $url, $header);
    }

    /**
     * Get information about a specific share
     * 
     * @param int $shareId The share ID
     * 
     * @return string JSON response
     */
    public function getShareInfo($shareId)
    {
        $url = $this->BASE_OCS_URL . '/' . $shareId . '?format=json';
        
        $header = [
            "Authorization: Basic " . base64_encode($this->USERNAME . ":" . $this->PASSWORD),
            "OCS-APIRequest: true"
        ];

        return $this->sendRequest('GET', $url, $header);
    }

    /**
     * Delete a share
     * 
     * @param int $shareId The share ID
     * 
     * @return string JSON response
     */
    public function deleteShare($shareId)
    {
        $url = $this->BASE_OCS_URL . '/' . $shareId . '?format=json';
        
        $header = [
            "Authorization: Basic " . base64_encode($this->USERNAME . ":" . $this->PASSWORD),
            "OCS-APIRequest: true"
        ];

        return $this->sendRequest('DELETE', $url, $header);
    }

    /**
     * Update share permissions, password, or expiration
     * 
     * @param int $shareId The share ID
     * @param array $updates Array of updates (e.g., ['password' => 'newpass', 'expireDate' => '2026-12-31'])
     * 
     * @return string JSON response
     */
    public function updateShare($shareId, $updates)
    {
        $url = $this->BASE_OCS_URL . '/' . $shareId . '?format=json';
        
        $header = [
            "Authorization: Basic " . base64_encode($this->USERNAME . ":" . $this->PASSWORD),
            "Content-Type: application/x-www-form-urlencoded",
            "OCS-APIRequest: true"
        ];

        return $this->sendRequest('PUT', $url, $header, http_build_query($updates));
    }

    /**
     * Fetch file from public share URL (no authentication)
     * 
     * @param string $publicShareUrl The public share download URL
     * 
     * @return array ['success' => bool, 'data' => file content, 'contentType' => mime type, 'httpCode' => int, 'error' => string]
     */
    public function fetchPublicFile($publicShareUrl)
    {
        if (empty($publicShareUrl)) {
            return [
                'success' => false,
                'data' => null,
                'contentType' => null,
                'httpCode' => 400,
                'error' => 'Public share URL is required'
            ];
        }

        if ($this->isDeadlineExpired()) {
            return [
                'success' => false,
                'data' => null,
                'contentType' => null,
                'httpCode' => 504,
                'error' => 'NextCloud request deadline exceeded',
                'timedOut' => true
            ];
        }

        $ch = curl_init();
        if ($ch === false) {
            return [
                'success' => false,
                'data' => null,
                'contentType' => null,
                'httpCode' => 500,
                'error' => 'cURL failed to initialize',
                'curlErrorNo' => 0,
                'timedOut' => false
            ];
        }
        curl_setopt($ch, CURLOPT_URL, $publicShareUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->FOLLOW_REDIRECTS);
        // NOTE: No Authorization header - this is PUBLIC access
        $fetchConnectTimeoutMs = $this->CONNECT_TIMEOUT_CONFIGURED ? $this->CONNECT_TIMEOUT_MS : 10000;
        $fetchTimeoutMs = $this->TIMEOUT_CONFIGURED ? $this->TIMEOUT_MS : 30000;
        $this->applyTransportOptions($ch, $fetchConnectTimeoutMs, $fetchTimeoutMs);

        $fileData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $errorNo = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        $deadlineExpired = $this->isDeadlineExpired();
        if ($fileData === false || $errorNo) {
            $timedOut = $errorNo === CURLE_OPERATION_TIMEDOUT || $deadlineExpired;
            return [
                'success' => false,
                'data' => null,
                'contentType' => null,
                'httpCode' => $timedOut ? 504 : $httpCode,
                'error' => $error ?: ($timedOut ? 'NextCloud request deadline exceeded' : "HTTP {$httpCode}"),
                'curlErrorNo' => $errorNo,
                'timedOut' => $timedOut
            ];
        }

        if ($deadlineExpired) {
            return [
                'success' => false,
                'data' => null,
                'contentType' => null,
                'httpCode' => 504,
                'error' => 'NextCloud request deadline exceeded',
                'timedOut' => true
            ];
        }

        if ($httpCode != 200) {
            return [
                'success' => false,
                'data' => null,
                'contentType' => null,
                'httpCode' => $httpCode,
                'error' => "HTTP {$httpCode}"
            ];
        }

        return [
            'success' => true,
            'data' => $fileData,
            'contentType' => $contentType,
            'httpCode' => $httpCode,
            'error' => null
        ];
    }

    /**
     * ======== UTILITY METHODS ========
     */

    /**
     * Send HTTP request
     * 
     * @param string $method GET, POST, PUT,...
     * @param string $url
     * @param array $header
     * @param array|string $requestBody
     * @param array $curlOptions
     * 
     * @return string JSON
     */
    private function sendRequest($method, $url, $header = [], $requestBody = null, $curlOptions = [])
    {
        try {
            if ($this->isDeadlineExpired()) {
                return $this->createDeadlineExceededResponse();
            }

            $curl = curl_init();
            if ($curl === false) {
                LoggerHelper::error("$method $url cURL failed to initialize");
                return $this->createErrorResponse(
                    "Can't connect to NextCloud API",
                    "cURL failed to initialize",
                    $this->SYSTEM_ERROR_CODE,
                    [
                        'curlErrorNo' => 0,
                        'timedOut' => false
                    ]
                );
            }
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            if (!is_null($requestBody)) curl_setopt($curl, CURLOPT_POSTFIELDS, $requestBody);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, $this->FOLLOW_REDIRECTS ? 1 : 0);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 16);
            foreach ($curlOptions as $key => $value) {
                curl_setopt($curl, $key, $value);
            }
            $this->applyTransportOptions($curl);
            $response = curl_exec($curl); // JSON
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorNo = curl_errno($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if ($response === false || $errorNo) {
                $timedOut = $errorNo === CURLE_OPERATION_TIMEDOUT || $this->isDeadlineExpired();
                LoggerHelper::error("$method $url cURL error $errorNo: $error");
                return $this->createErrorResponse(
                    "Can't connect to NextCloud API",
                    "cURL error $errorNo: $error",
                    $timedOut ? 504 : $this->SYSTEM_ERROR_CODE,
                    [
                        'curlErrorNo' => $errorNo,
                        'timedOut' => $timedOut
                    ]
                );
            }

            if ($this->isDeadlineExpired()) {
                return $this->createDeadlineExceededResponse();
            }

            $responseArr = json_decode($response, true);
            // 2. Nếu decode thất bại (do body rỗng hoặc XML) nhưng HTTP Code báo thành công (2xx)
            if (json_last_error() !== JSON_ERROR_NONE && $httpCode >= 200 && $httpCode < 300) {
                // Tự tạo một response giả lập thành công
                $responseArr = [
                    'status' => 1, // Giả lập status thành công
                    'message' => 'Action completed successfully (No body returned)',
                    'data' => null
                ];
            }

            // OCS returns: {"ocs": {"meta": {...}, "data": {...}}}
            if (isset($responseArr['ocs'])) {
                $ocsData = $responseArr['ocs'];
                $meta = $ocsData['meta'] ?? [];
                $statusCode = $meta['statuscode'] ?? 0;
                
                // OCS statuscode: 100 = OK (legacy), 200 = OK (modern), 400+ = errors
                if ($statusCode == 100 || $statusCode == 200) {
                    $responseArr = [
                        'status' => 1,
                        'httpCode' => $httpCode,
                        'message' => $meta['message'] ?? 'Success',
                        'data' => $ocsData['data'] ?? null
                    ];
                } else {
                    return $this->createErrorResponse(
                        $meta['message'] ?? "OCS Error $statusCode",
                        $ocsData,
                        $statusCode
                    );
                }
            }

            if ($httpCode < 200 || $httpCode >= 300) {
                return $this->createErrorResponse(
                    $responseArr["message"] ?? "Error $httpCode: Failed to handle request",
                    $responseArr ?? $response,
                    $httpCode
                );
            }

            if (isset($responseArr['status'])) {
                $responseArr['status'] = (int)$responseArr['status'];
            } else {
                $responseArr['status'] = 1; // success
            }
            $responseArr['httpCode'] = $httpCode;

            return json_encode($responseArr);
        } catch (Throwable $th) {
            $message = "Exception error {$th->getCode()}: {$th->getMessage()} on line {$th->getLine()}";
            LoggerHelper::error("$method $url $message");
            return $this->createErrorResponse(
                "System exception occurred",
                $message,
                $this->SYSTEM_ERROR_CODE
            );
        } finally {
            if (isset($curl) && is_resource($curl)) curl_close($curl);
        }
    }

    /**
     * Create error response JSON
     * 
     * @param string $message
     * @param string|array $description
     * @param int $httpCode
     * 
     * @return string JSON
     */
    private function createErrorResponse($message, $description, $httpCode, array $extra = [])
    {
        return json_encode(array_merge([
            "status" => 0,
            "httpCode" => $httpCode,
            "message" => $message,
            "data" => null,
            "description" => $description
        ], $extra));
    }

    private function createDeadlineExceededResponse()
    {
        return $this->createErrorResponse(
            "NextCloud request deadline exceeded",
            "The configured deadline elapsed before the request could complete.",
            504,
            ['timedOut' => true]
        );
    }

    private function applyTransportOptions($curl, $connectTimeoutMs = null, $timeoutMs = null)
    {
        $timeoutMs = is_null($timeoutMs) ? $this->TIMEOUT_MS : (int) $timeoutMs;
        $remainingMs = $this->getRemainingDeadlineMs();
        if (!is_null($remainingMs)) {
            $timeoutMs = min($timeoutMs, max(1, $remainingMs));
        }

        $connectTimeoutMs = is_null($connectTimeoutMs) ? $this->CONNECT_TIMEOUT_MS : (int) $connectTimeoutMs;
        $connectTimeoutMs = min($connectTimeoutMs, $timeoutMs);

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, $connectTimeoutMs);
        curl_setopt($curl, CURLOPT_TIMEOUT_MS, $timeoutMs);
        curl_setopt($curl, CURLOPT_NOSIGNAL, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->VERIFY_SSL);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $this->VERIFY_SSL ? 2 : 0);
    }

    private function getRemainingDeadlineMs()
    {
        if (is_null($this->DEADLINE_AT_MS)) {
            return null;
        }

        return (int) floor($this->DEADLINE_AT_MS - (microtime(true) * 1000));
    }

    private function isDeadlineExpired()
    {
        $remainingMs = $this->getRemainingDeadlineMs();
        return !is_null($remainingMs) && $remainingMs <= 0;
    }

    private function validateLocalFile($path)
    {
        if (!file_exists($path)) {
            return $this->createErrorResponse(
                "Local file does not exist",
                "The specified local file path '$path' does not exist.",
                $this->USER_ERROR_CODE
            );
        }
        return null;
    }

    private function validateRemoteFileName($path)
    {
        if (empty($path)) {
            return $this->createErrorResponse(
                "Remote file name is required",
                "The remote file name cannot be empty.",
                $this->USER_ERROR_CODE
            );
        }
        return null;
    }

    private function buildDavUrl($path, $isCollection = false)
    {
        $normalizedPath = ltrim((string) $path, '/');
        $url = rtrim($this->ENDPOINT, '/') . '/' . $this->encodePath($normalizedPath);

        return $isCollection ? rtrim($url, '/') . '/' : $url;
    }

    public function ensureFolderExists($remoteFolderPath)
    {
        $parts = explode('/', trim($remoteFolderPath, '/'));
        $currentPath = '';
        foreach ($parts as $part) {
            $currentPath .= '/' . $part;
            $resultJson = $this->createFolder($currentPath);
            $result = json_decode($resultJson, true);
            
            // WebDAV returns 405 Method Not Allowed if folder already exists
            // 201 Created if successfully created
            if ($result['httpCode'] != 405 && $result['httpCode'] != 201) {
                // If there is a real error, we might log it, but continue to try just in case
                if ($result['httpCode'] >= 400 && $result['httpCode'] != 409 && $result['httpCode'] != 405) {
                    // return false;
                }
            }
        }
        return true;
    }

    // Hàm util để encode path đúng cách
    private function encodePath($path)
    {
        // Tách chuỗi bằng dấu /, encode từng phần tử, rồi nối lại
        $parts = explode('/', $path);
        $encodedParts = array_map('rawurlencode', $parts);
        return implode('/', $encodedParts);
    }
}
