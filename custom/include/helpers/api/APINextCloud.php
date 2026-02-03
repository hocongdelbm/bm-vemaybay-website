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


    public function __construct()
    {
        global $sugar_config;
        $this->USERNAME = $sugar_config['next-cloud']['user'];
        $this->PASSWORD = $sugar_config['next-cloud']['password'];
        $this->ENDPOINT = rtrim($sugar_config['next-cloud']['endpoint'], '/') . '/' . $this->USERNAME;
        $this->BASE_OCS_URL = $sugar_config['next-cloud']['base_url_ocs'];
    }

    public function createFolder($remoteFolderPath)
    {
        if ($error = $this->validateRemoteFileName($remoteFolderPath)) {
            return $error;
        }
        $cleanedPath = $this->encodePath(rtrim($remoteFolderPath, '/'));
        $url = $this->ENDPOINT . '/' . $cleanedPath . '/'; //For collection, folder must end with '/'
        $header = [
            "Authorization: Basic " . base64_encode($this->USERNAME . ":" . $this->PASSWORD)
        ];
        return $this->sendRequest('MKCOL', $url, $header);
    }


    public function uploadFile($localFilePath, $remoteFileName)
    {

        if ($error = $this->validateLocalFile($localFilePath)) {
            return $error;
        }
        if ($error = $this->validateRemoteFileName($remoteFileName)) {
            return $error;
        }
        $url = $this->ENDPOINT . '/' . $this->encodePath($remoteFileName);
        $fileData = file_get_contents($localFilePath); // Read file content
        $header = [
            "Authorization: Basic " . base64_encode($this->USERNAME . ":" . $this->PASSWORD),
            "Content-Type: application/octet-stream",
            "Content-Length: " . strlen($fileData)
        ];
        return $this->sendRequest('PUT', $url, $header, $fileData);
    }

    public function deleteFile($remoteFileName)
    {
        if ($error = $this->validateRemoteFileName($remoteFileName)) {
            return $error;
        }
        $url = $this->ENDPOINT . '/' . $this->encodePath($remoteFileName);
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

        $url = $this->ENDPOINT . '/' . $this->encodePath($remoteFileName);
        $header = [
            "Authorization: Basic " . base64_encode($this->USERNAME . ":" . $this->PASSWORD),
            "Destination: " . rtrim($this->ENDPOINT, '/') . '/' . $this->encodePath($destitationPath)
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

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $publicShareUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // NOTE: No Authorization header - this is PUBLIC access
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $fileData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($fileData === false || $httpCode != 200) {
            return [
                'success' => false,
                'data' => null,
                'contentType' => null,
                'httpCode' => $httpCode,
                'error' => $error ?: "HTTP {$httpCode}"
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
            $curl = curl_init();
            if ($curl === false) {
                LoggerHelper::error("$method $url cURL failed to initialize");
                return $this->createErrorResponse(
                    "Can't connect to NextCloud API",
                    "cURL failed to initialize",
                    $this->SYSTEM_ERROR_CODE
                );
            }
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            if (!is_null($requestBody)) curl_setopt($curl, CURLOPT_POSTFIELDS, $requestBody);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 16);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 20);
            curl_setopt($curl, CURLOPT_TIMEOUT, 60);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // Skip SSL verification for local dev
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            foreach ($curlOptions as $key => $value) {
                curl_setopt($curl, $key, $value);
            }
            $response = curl_exec($curl); // JSON
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorNo = curl_errno($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if ($response === false || $errorNo) {
                LoggerHelper::error("$method $url cURL error $errorNo: $error");
                return $this->createErrorResponse(
                    "Can't connect to NextCloud API",
                    "cURL error $errorNo: $error",
                    $this->SYSTEM_ERROR_CODE
                );
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
    private function createErrorResponse($message, $description, $httpCode)
    {
        return json_encode([
            "status" => 0,
            "httpCode" => $httpCode,
            "message" => $message,
            "data" => null,
            "description" => $description
        ]);
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
    // public function ensureFolderExists($remoteFolderPath)
    // {
    //     $resultJson = $this->createFolder($remoteFolderPath);
    //     $result = json_decode($resultJson, true);

    //     // WebDAV trả về 405 Method Not Allowed nếu folder đã tồn tại
    //     // NextCloud đôi khi trả 409 nếu cha chưa tồn tại (nhưng logic đơn giản ta chấp nhận 405 là folder đã có)
    //     if ($result['httpCode'] == 405) {
    //         return true; // Folder đã có, coi như OK
    //     }

    //     if ($result['httpCode'] == 201) {
    //         return true; // Mới tạo thành công
    //     }

    //     return false; // Lỗi khác
    // }

    // Hàm util để encode path đúng cách
    private function encodePath($path)
    {
        // Tách chuỗi bằng dấu /, encode từng phần tử, rồi nối lại
        $parts = explode('/', $path);
        $encodedParts = array_map('rawurlencode', $parts);
        return implode('/', $encodedParts);
    }
}
