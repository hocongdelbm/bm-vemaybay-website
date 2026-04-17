<?php

class MisaInvoice
{
    private string $BASE_URL;
    private string $APP_ID;
    private string $ACCESS_CODE;
    private string $ORG_COMPANY_CODE;

    private string $TOKEN_CACHE_FILE;

    public function __construct()
    {
        global $sugar_config;
        LoggerHelper::setLogPath('secure_sessions/misa_logs');

        $this->BASE_URL             = $sugar_config['misa']['base_url']         ?? '';
        $this->APP_ID               = $sugar_config['misa']['app_id']           ?? '';
        $this->ACCESS_CODE          = $sugar_config['misa']['access_code']      ?? '';
        $this->ORG_COMPANY_CODE     = $sugar_config['misa']['company_code']     ?? '';
        $this->TOKEN_CACHE_FILE     = 'custom/json_files/misa/access_token.json';
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
        ?string $lastSyncTime = null
    ): string {
        $token = $this->getAccessToken();
        // pr($token);
        // die;

        if (!$token) {
            return $this->returnError(401, 'Không thể lấy access token từ AMIS Kế toán');
        }

        $body = [
            'data_type'      => $dataType,
            'skip'           => $skip,
            'take'           => $take,
            'app_id'         => $this->APP_ID,
            'last_sync_time' => $lastSyncTime,
            // 'branch_id'      => $this->getBranchId(),
        ];

        return $this->sendRequest(
            'POST',
            '/apir/sync/actopen/get_dictionary',
            $body,
            $token
        );
    }

    /**
     * Lấy branch_id của tổng công ty (organization_unit_type_id = 1)
     *
     * @return string|null branch_id hoặc null nếu thất bại
     */
    public function getBranchId(): ?string
    {
        // Lấy branch từ config
        global $sugar_config;
        if ($sugar_config['misa']['branch_id']) {
            return $sugar_config['misa']['branch_id'];
        }

        $raw = $this->getDictionary(6);
        $arr = json_decode($raw, true);

        if (empty($arr) || ($arr['error'] ?? 1) !== 0) {
            LoggerHelper::error('MISA getBranchId: getDictionary(6) thất bại', $arr ?? []);
            return null;
        }

        // Data là JSON string — decode thêm 1 lần
        $data = $arr['data'] ?? [];
        if (is_string($data)) {
            $data = json_decode($data, true) ?? [];
        }

        if (empty($data)) {
            LoggerHelper::error('MISA getBranchId: data rỗng');
            return null;
        }

        // Ưu tiên lấy tổng công ty (type_id = 1)
        // Nếu không tìm thấy thì lấy phần tử đầu tiên
        foreach ($data as $unit) {
            if (($unit['organization_unit_type_id'] ?? 0) === 1) {
                return $unit['branch_id'];
            }
        }

        // Fallback: lấy branch_id đầu tiên
        return $data[0]['branch_id'] ?? null;
    }

    /**
     * Tạo yêu cầu sinh chứng từ Bán hàng/Bán dịch vụ (sa_voucher)
     * Kết quả thực tế trả về qua Callback URL (bất đồng bộ)
     *
     * @param array $voucher  Thông tin chứng từ
     * @param array $details  Danh sách chi tiết hàng hóa/dịch vụ
     * @param array $saInvoice Thông tin hóa đơn
     *
     * @return string JSON {error, httpCode, message, data}
     */
    public function save(array $voucher, array $details, array $saInvoice): string
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return $this->returnError(401, 'Không thể lấy access token từ AMIS Kế toán');
        }

        if (empty($voucher['org_refid'])) {
            return $this->returnError(400, 'Thiếu ID chứng từ (org_refid)');
        }
        if (empty($details)) {
            return $this->returnError(400, 'Danh sách chi tiết không được rỗng');
        }
        if (empty($saInvoice)) {
            return $this->returnError(400, 'Thiếu thông tin hóa đơn (sa_invoice)');
        }

        // ============================================================
        // 1. THÔNG TIN CHỨNG TỪ (sa_voucher)
        // ============================================================
        $now = date('Y-m-d H:i:s.') . substr(microtime(), 2, 3);

        $voucherData = [
            'voucher_type'              => $voucher['voucher_type']          ?? 13, // (Bắt buộc)
            'reftype'                   => $voucher['reftype']               ?? 3530, // Bán hàng hóa, dịch vụ trong nước chưa thu tiền (Bắt buộc)
            'org_refid'                 => $voucher['org_refid'], // ID của chứng từ dữ liệu gốc (Bắt buộc)
            'org_refno'                 => $voucher['org_refno']             ?? '', // Số chứng từ gốc (Bắt buộc)
            'branch_id'                 => $voucher['branch_id']             ?? $this->getBranchId(), // ID chi nhánh (Bắt buộc)
            'account_object_code'       => $voucher['account_object_code']   ?? '', // Mã khách hàng
            'account_object_name'       => $voucher['account_object_name']   ?? '', // Tên khách hàng
            'account_object_address'    => $voucher['account_object_address'] ?? '', // Địa chỉ khách hàng
            'posted_date'               => $voucher['posted_date']           ?? $now,
            'refdate'                   => $voucher['refdate']               ?? $now,
            'due_day'                   => $voucher['due_day']               ?? '0',
            'due_date'                  => $voucher['due_date']              ?? $now,
            'include_invoice'           => 1, // (0: không kèm, 1: Nhận kèm HĐ, 2: Không có hóa đơn)
            'inv_date'                  => $voucher['inv_date'], // Ngày hóa đơn
            'is_sale_with_outward'      => false, // Bán hàng kiêm phiếu xuất kho
        ];

        // ============================================================
        // 2. CHI TIẾT HÀNG HÓA/DỊCH VỤ
        // ============================================================
        $detailData = [];
        foreach ($details as $idx => $item) {
            $detailData[] = [
                'sort_order'                 => $idx,
                'inventory_item_code'        => $item['inventory_item_code']        ?? '',
                'inventory_item_name'        => $item['inventory_item_name']        ?? '',
                'inventory_item_description' => $item['inventory_item_description'] ?? '',
                'description'                => $item['description']                ?? '',
                'unit_name'                  => $item['unit_name']                  ?? '',
                'quantity'                   => $item['quantity'],
                'unit_price'                 => $item['unit_price'],
                'amount'                     => $item['amount'],
                'vat_rate'                   => $item['vat_rate'],
                'vat_amount'                 => $item['vat_amount'],
                'vat_account'                => $item['vat_account']                ?? '',
                'is_description'             => $item['is_description']             ?? false,
                'account_object_code'        => $item['account_object_code']        ?? '',
                'account_object_name'        => $item['account_object_name']        ?? '',
            ];
        }
        $voucherData['detail'] = $detailData;

        // ============================================================
        // 3. THÔNG TIN HÓA ĐƠN (sa_invoice)
        // ============================================================
        $voucherData['sa_invoice'] = [
            'voucher_type'         => 11,
            'org_reftype'          => 0,
            'act_voucher_type'     => 0,
            'refdate'              => $voucherData['refdate'],
            'inv_date'             => $voucherData['inv_date'], // Ngày hóa đơn
            // 'branch_id'            => $voucherData['branch_id'],

            // Thông tin từ caller
            'discount_type'        => 2,
            'is_posted'            => true, //trạng thái đã hạch toán
            'reftype'               => 3560,
            'account_object_code'  => $saInvoice['account_object_code']  ?? $voucherData['account_object_code'],
            'account_object_name'  => $saInvoice['account_object_name']  ?? $voucherData['account_object_name'],
            'account_object_tax_code' => $saInvoice['account_object_tax_code'] ?? $voucherData['account_object_tax_code'],
            'account_object_address'  => $saInvoice['account_object_address']  ?? $voucherData['account_object_address'],
            'currency_id'          => $saInvoice['currency_id']          ?? 'VND',
            'buyer'                => '',
            'exchange_rate'        => $saInvoice['exchange_rate']         ?? 1,
            'inv_no'               => $saInvoice['inv_no']                ?? '', // Số hóa đơn
            'inv_series'           => $saInvoice['inv_series']            ?? '', // Ký hiệu hóa đơn
            'payment_method'       => $saInvoice['payment_method']        ?? 'TM/CK',
            'total_sale_amount'    => $saInvoice['total_sale_amount']     ?? 0,
            'total_vat_amount'     => $saInvoice['total_vat_amount']      ?? 0,
            'total_amount'         => $saInvoice['total_amount']          ?? 0,
        ];

        $body = [
            'org_company_code' => $this->ORG_COMPANY_CODE,
            'app_id'           => $this->APP_ID,
            'voucher'          => [$voucherData],
        ];

        return $this->sendRequest('POST', '/apir/sync/actopen/save', $body, $token);
    }

    /**
     * Xóa đề nghị sinh chứng từ
     * Chỉ xóa được chứng từ chưa được kế toán sinh thành CT chính thức
     *
     * @param string $orgRefId    ID chứng từ gốc từ hệ thống (org_refid khi save)
     * @param int    $voucherType Loại chứng từ (mặc định 13 = Bán hàng/dịch vụ)
     *
     * @return string JSON {error, httpCode, message, data}
     */
    public function delete(string $orgRefId, int $voucherType = 13): string
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return $this->returnError(401, 'Không thể lấy access token từ AMIS Kế toán');
        }

        if (empty($orgRefId)) {
            return $this->returnError(400, 'Thiếu org_refid');
        }

        $body = [
            'app_id'           => $this->APP_ID,
            'org_company_code' => $this->ORG_COMPANY_CODE,
            'voucher'          => [
                [
                    'voucher_type' => $voucherType,
                    'org_refid'    => $orgRefId,
                ],
            ],
        ];

        return $this->sendRequest('DELETE', '/apir/sync/actopen/delete', $body, $token);
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
        // VALIDATE 
        $missingFields = array_filter([
            'app_id'           => $this->APP_ID,
            'access_code'      => $this->ACCESS_CODE,
            'org_company_code' => $this->ORG_COMPANY_CODE,
        ], fn($v) => empty($v));

        if (!empty($missingFields)) {
            LoggerHelper::error('MISA connect: thiếu config ' . implode(', ', array_keys($missingFields)));
            return null;
        }

        $body = [
            'app_id'           => $this->APP_ID,
            'access_code'      => $this->ACCESS_CODE,
            'org_company_code' => $this->ORG_COMPANY_CODE,
        ];

        $raw = $this->sendRequest('POST', '/api/oauth/actopen/connect', $body);
        $arr = json_decode($raw, true);

        if (empty($arr) || ($arr['error'] ?? 1) !== 0) {
            LoggerHelper::error('MISA connect: gọi API thất bại', $arr ?? []);
            return null;
        }

        $data = $arr['data'] ?? [];

        // MISA trả Data là JSON string — decode thêm 1 lần
        if (is_string($data)) {
            $data = json_decode($data, true) ?? [];
        }

        $access_token = $data['access_token']       ?? null;
        $expiredTicks = $data['expired_time_ticks']  ?? null;
        $expiredTime  = $data['expired_time']        ?? null;

        if (!$access_token) {
            LoggerHelper::error('MISA connect: không parse được access_token', $data);
            return null;
        }

        // Ưu tiên: ticks → expired_time ISO string → fallback 12h
        if ($expiredTicks) {
            $expiredAt = intval(($expiredTicks - 621355968000000000) / 10000000);
        } elseif ($expiredTime) {
            $expiredAt = strtotime($expiredTime) ?: time() + (12 * 3600);
        } else {
            $expiredAt = time() + (12 * 3600);
        }

        $this->writeTokenCache($access_token, $expiredAt);

        return $access_token;
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

        if (empty($data['access_token']) || empty($data['expired_at'])) {
            return null;
        }

        // Dự phòng thêm 5 phút để tránh race condition sát giờ hết hạn
        if (time() >= ($data['expired_at'] - 300)) {
            return null;
        }

        return $data['access_token'];
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
                'access_token'      => $token,
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
        $url     = $this->BASE_URL . $path;
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
            if (isset($curl) && is_resource($curl)) curl_close($curl);
        }
    }


    // ============================================================
    // RESPONSE HELPERS 
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
     * Check response from request
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
