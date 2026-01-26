<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class APINextCloud
{
    private $ENDPOINT;
    private $USERNAME;
    private $PASSWORD;
    private $USER_ERROR_CODE = 400;
    private $SYSTEM_ERROR_CODE = 500;


    public function __construct()
    {
        global $sugar_config;
        $this->USERNAME = $sugar_config['next-cloud']['user'];
        $this->PASSWORD = $sugar_config['next-cloud']['password'];
        $this->ENDPOINT = rtrim($sugar_config['next-cloud']['endpoint'], '/') . '/' . $this->USERNAME;
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
