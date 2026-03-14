<?php

class MisaInvoice
{
    private string $ENDPOINT   = 'https://actapp.misa.vn';
    private string $APP_ID;
    private string $ACCESS_CODE;
    private string $ORG_COMPANY_CODE;

    private string $TOKEN_CACHE_FILE;

    public function __construct()
    {
        global $sugar_config;
        $this->APP_ID           = $sugar_config['misa']['app_id']           ?? '';
        $this->ACCESS_CODE      = $sugar_config['misa']['access_code']      ?? '';
        $this->ORG_COMPANY_CODE = $sugar_config['misa']['org_company_code'] ?? '';
        $this->TOKEN_CACHE_FILE = 'json_files/misa/access_token.json';
    }

    // ============================================================
    // PUBLIC API METHODS
    // ============================================================

    /**
     * Lấy danh mục từ AMIS Kế toán
     *
     * data_type:
     *   1  = Đối tượng (KH/NCC/Nhân viên)
     *   2  = Vật tư – Hàng hóa
     *   3  = Kho
     *   4  = Đơn vị tính
     *   5  = Hệ thống tài khoản
     *   6  = Cơ cấu tổ chức (branch_id)
     *   8  = Tài khoản ngân hàng
     *   14 = Nhóm vật tư hàng hóa
     *
     * @param int         $dataType      Loại danh mục
     * @param int         $skip          Bỏ qua N bản ghi (phân trang)
     * @param int         $take          Lấy tối đa N bản ghi (max 1000)
     * @param string|null $lastSyncTime  Lọc dữ liệu thay đổi từ mốc này, null = lấy tất cả
     * @param string|null $branchId      Lọc theo chi nhánh, null = tất cả chi nhánh
     *
     * @return string JSON {error, httpCode, message, data}
     */
    public function getDictionary(
        int    $dataType,
        int    $skip         = 0,
        int    $take         = 1000,
        ?string $lastSyncTime = null,
        ?string $branchId     = null
    ): string {
        $token = $this->getAccessToken();
        if (!$token) {
            return $this->returnError(401, 'Không thể lấy access token từ AMIS Kế toán');
        }

        $body = [
            'data_type'      => $dataType,
            'skip'           => $skip,
            'take'           => $take,
            'app_id'         => $this->APP_ID,
            'last_sync_time' => $lastSyncTime,
            'branch_id'      => $branchId,
        ];

        return $this->sendRequest(
            'POST',
            '/apir/sync/actopen/get_dictionary',
            $body,
            $token
        );
    }


    // ============================================================
    // TOKEN MANAGEMENT
    // ============================================================

    /**
     * Lấy access token còn hiệu lực.
     * Ưu tiên đọc từ cache, chỉ gọi API khi token hết hạn hoặc chưa có.
     *
     * @return string|null access_token hoặc null nếu thất bại
     */
    public function getAccessToken(): ?string
    {
        // 1. Thử đọc từ cache trước
        $cached = $this->readTokenCache();
        if ($cached) {
            return $cached;
        }

        // 2. Cache miss / hết hạn → gọi API lấy token mới
        return $this->connect();
    }

    /**
     * Gọi API connect để lấy access_token mới và lưu cache.
     *
     * @return string|null access_token hoặc null nếu thất bại
     */
    private function connect(): ?string
    {
        $body = [
            'app_id'           => $this->APP_ID,
            'access_code'      => $this->ACCESS_CODE,
            'org_company_code' => $this->ORG_COMPANY_CODE,
        ];

        $raw = $this->sendRequest('POST', '/api/oauth/actopen/connect', $body);
        $arr = json_decode($raw, true);

        if (empty($arr) || ($arr['error'] ?? 1) !== 0) {
            return null;
        }

        $data = $arr['data'] ?? [];

        // Data từ MISA trả về dạng JSON string lồng bên trong
        if (is_string($data)) {
            $data = json_decode($data, true) ?? [];
        }

        // MISA có thể trả về nhiều token object liên tiếp trong 1 chuỗi JSON
        // Lấy token đầu tiên có expired_time_ticks
        $token       = null;
        $expiredTicks = null;

        if (isset($data['access_token'])) {
            // Trường hợp data là 1 object đơn
            $token        = $data['access_token'];
            $expiredTicks = $data['expired_time_ticks'] ?? null;
        } elseif (is_array($data)) {
            // Trường hợp data là array các object
            foreach ($data as $item) {
                if (!empty($item['access_token']) && !empty($item['expired_time_ticks'])) {
                    $token        = $item['access_token'];
                    $expiredTicks = $item['expired_time_ticks'];
                    break;
                }
            }
        }

        if (!$token) {
            return null;
        }

        // Tính thời gian hết hạn Unix timestamp từ .NET Ticks
        // .NET Ticks: số 100-nanosecond intervals từ 0001-01-01
        // Unix epoch bắt đầu từ 1970-01-01 = 621355968000000000 ticks
        $expiredAt = null;
        if ($expiredTicks) {
            $unixTicks   = $expiredTicks - 621355968000000000;
            $expiredAt   = intval($unixTicks / 10000000); // chuyển sang Unix timestamp (giây)
        } else {
            // Fallback: token có hiệu lực 12h theo tài liệu MISA
            $expiredAt = time() + (12 * 3600);
        }

        $this->writeTokenCache($token, $expiredAt);

        return $token;
    }


    // ============================================================
    // TOKEN CACHE HELPERS
    // ============================================================

    /**
     * Đọc token từ cache file.
     * Trả về token string nếu còn hiệu lực, null nếu hết hạn hoặc không tồn tại.
     */
    private function readTokenCache(): ?string
    {
        if (!file_exists($this->TOKEN_CACHE_FILE)) {
            return null;
        }

        $raw  = file_get_contents($this->TOKEN_CACHE_FILE);
        $data = json_decode($raw, true);

        if (empty($data['token']) || empty($data['expired_at'])) {
            return null;
        }

        // Dự phòng thêm 5 phút để tránh race condition sát giờ hết hạn
        if (time() >= ($data['expired_at'] - 300)) {
            return null;
        }

        return $data['token'];
    }

    /**
     * Ghi token và thời gian hết hạn vào cache file.
     */
    private function writeTokenCache(string $token, int $expiredAt): void
    {
        $dir = dirname($this->TOKEN_CACHE_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(
            $this->TOKEN_CACHE_FILE,
            json_encode([
                'token'      => $token,
                'expired_at' => $expiredAt,
            ]),
            LOCK_EX // tránh ghi đè đồng thời
        );
    }


    // ============================================================
    // HTTP HELPER
    // ============================================================

    /**
     * Gửi HTTP request đến AMIS API.
     *
     * @param string      $method  GET | POST | DELETE
     * @param string      $path    Đường dẫn API (bắt đầu bằng /)
     * @param array       $body    Request body (sẽ được json_encode)
     * @param string|null $token   access_token (nếu có)
     *
     * @return string JSON {error, httpCode, message, data}
     */
    private function sendRequest(
        string  $method,
        string  $path,
        array   $body   = [],
        ?string $token  = null
    ): string {
        $url     = $this->ENDPOINT . $path;
        $headers = ['Content-Type: application/json'];

        if ($token) {
            $headers[] = "X-MISA-AccessToken: $token";
        }

        $jsonBody = json_encode($body, JSON_UNESCAPED_UNICODE);

        try {
            $curl = curl_init();
            if ($curl === false) {
                $logId = LoggerHelper::error("MISA $method $url cURL failed to initialize");
                return $this->returnError(0, 'Lỗi khởi tạo kết nối', "Mã lỗi: $logId");
            }

            curl_setopt_array($curl, [
                CURLOPT_URL            => $url,
                CURLOPT_CUSTOMREQUEST  => $method,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_POSTFIELDS     => $jsonBody,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_TIMEOUT        => 60,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorNo  = curl_errno($curl);
            $error    = curl_error($curl);
            curl_close($curl);

            if ($response === false || $errorNo) {
                $logId = LoggerHelper::error("MISA $method $url cURL error $errorNo: $error");
                return $this->returnError($httpCode, 'Không thể kết nối đến AMIS Kế toán', "Mã lỗi: $logId");
            }

            $responseArr = json_decode($response, true);

            LoggerHelper::info("MISA $method $url $httpCode", [
                'request'  => $body,
                'response' => $responseArr ?? $response,
            ]);

            // MISA trả về Success: true/false
            $misaSuccess = $responseArr['Success'] ?? $responseArr['success'] ?? false;

            if ($httpCode >= 200 && $httpCode < 300 && $misaSuccess === true) {
                return json_encode([
                    'error'    => 0,
                    'httpCode' => $httpCode,
                    'message'  => 'Thao tác thành công',
                    'data'     => $responseArr['Data'] ?? $responseArr['data'] ?? [],
                ], JSON_UNESCAPED_UNICODE);
            }

            $errorMsg = $responseArr['ErrorMessage']
                ?? $responseArr['error_message']
                ?? "Thao tác thất bại ($path)";

            return $this->returnError($httpCode, $errorMsg);
        } catch (Throwable $th) {
            $msg   = "Exception {$th->getCode()}: {$th->getMessage()} on line {$th->getLine()}";
            $logId = LoggerHelper::error("MISA $method $url $msg");
            return $this->returnError($httpCode ?? 0, 'Đã có lỗi xảy ra', "Mã lỗi: $logId");
        } finally {
            if (isset($curl) && is_resource($curl)) {
                curl_close($curl);
            }
        }
    }


    // ============================================================
    // RESPONSE HELPERS  (giống WinInvoice)
    // ============================================================

    /**
     * @return string JSON {error:1, httpCode, message, data, description?}
     */
    protected function returnError(int $httpCode = 0, string $message = '', string $description = '', $data = null): string
    {
        $arr = [
            'error'    => 1,
            'httpCode' => $httpCode,
            'message'  => $message,
            'data'     => $data,
        ];
        if (!empty($description)) {
            $arr['description'] = $description;
        }
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Kiểm tra response có thành công không.
     *
     * @param string $raw JSON string
     * @return bool
     */
    public function checkResponse(string $raw): bool
    {
        if (empty($raw)) return false;
        $arr = json_decode($raw, true);
        return isset($arr['error']) && $arr['error'] === 0;
    }
}
