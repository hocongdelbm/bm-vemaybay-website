<?php
class WinInvoice {
    private $ENDPOINT = 'https://quanly.wininvoice.vn/api/';
    private $USER;
    private $PASSWORD;
    private $INVOICE_NUMBER; // Mẫu số hóa đơn
    private $TIMEOUT = 30;

    public function __construct($have_code = 1) {
        global $sugar_config;
        $this->USER = $sugar_config['win_invoice']['user'] ?? '';
        $this->PASSWORD = $sugar_config['win_invoice']['password'] ?? '';
        $this->INVOICE_NUMBER = '1';
    }

    public function header() {
        return [
            "Content-Type: application/json",
            "Authorization: Basic " . base64_encode($this->USER . ':' . $this->PASSWORD)
        ];
    }

    /**
     * Tạo hóa đơn, Cập nhật hóa đơn (nếu đã tồn tại và chưa ký số)
     * 
     * @param array $invoice Thông tin hóa đơn
     * @param array $buyer Thông tin khách hàng 
     * @param array $items Thông tin sản phẩm/dịch vụ
     * @return string JSON
     */
    public function set($invoice, $buyer, $items) {
        if (!is_array($invoice) || !is_array($buyer) || !is_array($items) || empty($invoice) || empty($buyer) || empty($items)) {
            return json_encode([
                "error" => 1,
                "httpCode" => 400,
                "message" => "Thiếu dữ liệu để xử lý yêu cầu",
                "data" => null,
            ]);
        }

        $post_data = array();
        /******  1. THÔNG TIN HÓA ĐƠN  ******/
        $post_data['invName']    = $this->INVOICE_NUMBER;
        $post_data['invSerial']  = $invoice['invSerial'] ?? '';
        $post_data['invDate']    = isset($invoice['invDate']) ? $this->format_date($invoice['invDate']) : date('Y-m-d'); // yyyy-mm-dd
        $post_data['invRef']     = $invoice['invRef'] ?? ''; // Mã hóa đơn
        $post_data['invRefDate'] = isset($invoice['invRefDate']) ? $this->format_date($invoice['invRefDate']) : date('Y-m-d'); // yyyy-mm-dd
        // Thông tin tiền (Required)
        $post_data['invSubTotal']    = isset($invoice['invSubTotal']) ? $invoice['invSubTotal'] : 0; // TỔNG TIỀN HÀNG (CHƯA VAT) (CHƯA CHIẾT KHẤU)
        $post_data['invVatRate']     = isset($invoice['invVatRate']) ? $invoice['invVatRate'] : 0; // THUẾ SUẤT TRÊN HÓA ĐƠN
        $post_data['invVatAmount']   = isset($invoice['invVatAmount']) ? $invoice['invVatAmount'] : 0; // TỔNG TIỀN THUẾ
        $post_data['invTotalAmount'] = isset($invoice['invTotalAmount']) ? $invoice['invTotalAmount'] : 0; // TỔNG CỘNG(TIỀN HÀNG + TIỀN VAT)
        // Tiền tệ (Optional)
        $post_data['invPayment']      = isset($invoice['invPayment']) ? $invoice['invPayment'] : 'TM/CK';
        $post_data['invCurrency']     = isset($invoice['invCurrency']) ? $invoice['invCurrency'] : 'VND';
        $post_data['invExchangeRate'] = isset($invoice['invExchangeRate']) ? $invoice['invExchangeRate'] : 1;
        // Chiết khấu (Optional)
        $post_data['invDscnAmnt'] = isset($invoice['invDscnAmnt']) ? $invoice['invDscnAmnt'] : 0;
        $post_data['note'] = isset($invoice['note']) ? $invoice['note'] : '';
        // Thông tin khác (Optional)
        $post_data['invAutoSign'] = '0'; // KÝ TỰ ĐỘNG
        $post_data['invCustomer'] = (string)($invoice['invCustomer'] ?? '1'); // KH cá nhân hay tổ chức (1:Cá nhân ; 0:Tổ chức)
        

        /******  2. THÔNG TIN KHÁCH HÀNG  ******/
        $post_data['buyerName']      = isset($buyer['buyerName']) ? html_entity_decode($buyer['buyerName']) : '';
        $post_data['buyerCompany']   = isset($buyer['buyerCompany']) ? html_entity_decode($buyer['buyerCompany']) : '';
        $post_data['buyerEmail']     = $buyer['buyerEmail'] ?? 'quynhtrang@giaonhanh.net';
        // Optional
        $post_data['buyerCode']      = $buyer['buyerCode'] ?? '';
        $post_data['buyerTax']       = $buyer['buyerTax'] ?? '';
        $post_data['buyerAddress']   = isset($buyer['buyerAddress']) ? html_entity_decode($buyer['buyerAddress']) : '';
        $post_data['buyerAcc']       = $buyer['buyerAcc'] ?? '';
        $post_data['buyerBank']      = isset($buyer['buyerBank']) ? html_entity_decode($buyer['buyerBank']) : '';
        $post_data['buyerPhone']     = $buyer['buyerPhone'] ?? '';
        $post_data['buyerFax']       = $buyer['buyerFax'] ?? '';
        $post_data['buyerCitizenIDNumber']  = $buyer['buyerCitizenIDNumber'] ?? ''; // CCCD
        $post_data['buyerPassportNumber']   = $buyer['buyerPassportNumber'] ?? ''; // Passport
        // $post_data['govUnitCode']           = $buyer['govUnitCode'] ?? ''; // Mã đơn vị có quan hệ với ngân sách

        /******  3. THÔNG TIN SẢN PHẨM/DỊCH VỤ  ******/
        $post_data['items'] = [];
        $check_item = true;
        foreach ($items as $k => $i) {
            if (!isset($i['itemName']) || empty($i['itemName']) || $i['itemPrice'] == 0 || $i['itemAmountNoVat'] == 0) {
                $check_item = false;
                continue;
            }

            $post_data['items'][] = [
                'itemNo'          => isset($i['itemNo']) ? $i['itemNo'] : '',
                'itemCode'        => isset($i['itemCode']) ? $i['itemCode'] : '',
                'itemName'        => isset($i['itemName']) ? $i['itemName'] : '',
                'itemQuantity'    => isset($i['itemQuantity']) ? $i['itemQuantity'] : 1,
                'itemPrice'       => isset($i['itemPrice']) ? $i['itemPrice'] : 0, // GIÁ CHƯA VAT
                'itemVatRate'     => isset($i['itemVatRate']) ? (int)$i['itemVatRate'] : 0, // VAT SẢN PHẨM
                'itemVatAmnt'     => isset($i['itemVatAmnt']) ? (int)$i['itemVatAmnt'] : 0, // TIỀN VAT
                'itemAmountNoVat' => isset($i['itemAmountNoVat']) ? $i['itemAmountNoVat'] : 0, // THÀNH TIỀN CHƯA VAT
                // Optional
                'itemPack'       => isset($i['itemPack']) ? $i['itemPack'] : '',
                'itemDate'       => isset($i['itemDate']) ? $i['itemDate'] : '',
                'itemUnit'       => isset($i['itemUnit']) ? $i['itemUnit'] : '',
                'itemDscnAmnt'   => isset($i['itemDscnAmnt']) ? $i['itemDscnAmnt'] : 0,
                'itemNote'       => isset($i['itemNote']) ? $i['itemNote'] : '',
                'itemPromo'      => isset($i['itemPromo']) ? $i['itemPromo'] : '0'
            ];
        }

        /******  4. KIỂM TRA DỮ LIỆU  ******/
        if (empty($post_data['items']) || $check_item === false) {
            return json_encode([
                'error' => 1,
                "httpCode" => 400,
                'message' => 'Sản phảm/Dịch vụ không hợp lệ',
                'data' => null
            ]);
        }

        /******  5. CALL API  ******/
        $path = "invoice/add_type_2";
        $res = $this->sendRequest('POST', $path, json_encode($post_data, JSON_UNESCAPED_UNICODE), $this->header()); // Array
        return json_encode($res);
    }

    /**
     * Ký số hóa đơn
     * 
     * @param string $invRef Số phiếu bán
     * @return bool
     */
    public function sign($invRef) {
        if (is_null($invRef) || empty($invRef)) {
            return json_encode([
                "error" => 1,
                "httpCode" => 400,
                "message" => "Không tìm thấy số phiếu bán",
                "data" => null,
            ]);
        }

        $arrInv = json_decode($this->get($invRef), true);
        if(isset($arrInv['error']) && $arrInv['error'] == 0) {
            $dataInv = $arrInv['data'][0] ?? [];

            if(empty($dataInv)) {
                return json_encode([
                    "error" => 1,
                    "httpCode" => 400,
                    "message" => "Không lấy được dữ liệu hóa đơn",
                    "data" => null
                ]);
            }

            $requestBody = [];
            /******  1. THÔNG TIN HÓA ĐƠN  ******/
            $requestBody['invName']    = $dataInv['invName'];
            $requestBody['invSerial']  = $dataInv['invSerial'];
            $requestBody['invDate']    = $dataInv['invDate'];
            $requestBody['invRef']     = $dataInv['invRef'];
            $requestBody['invRefDate'] = $dataInv['invRefDate'];
            $requestBody['invSubTotal']    = $dataInv['invSubTotal'];
            $requestBody['invVatRate']     = $dataInv['invVatRate'];
            $requestBody['invVatAmount']   = $dataInv['invVatAmount'];
            $requestBody['invTotalAmount'] = $dataInv['invTotalAmount'];
            // Tiền tệ (Optional)
            $requestBody['invPayment']      = $dataInv['invPayment'];
            $requestBody['invCurrency']     = $dataInv['invCurrency'];
            $requestBody['invExchangeRate'] = $dataInv['invExchangeRate'];
            // Chiết khấu (Optional)
            $requestBody['invDscnAmnt'] = $dataInv['invDscnAmnt'];
            $requestBody['note'] = $dataInv['note'];
            // Thông tin khác (Optional)
            $requestBody['invAutoSign'] = '1'; // KÝ TỰ ĐỘNG
            $requestBody['invCustomer'] = $dataInv['invCustomer'];

            /******  2. THÔNG TIN KHÁCH HÀNG  ******/
            $requestBody['buyerName']      = html_entity_decode($dataInv['buyerName']);
            $requestBody['buyerCompany']   = html_entity_decode($dataInv['buyerCompany']);
            $requestBody['buyerEmail']     = $dataInv['buyerEmail'];
            // Optional
            $requestBody['buyerCode']      = $dataInv['buyerCode'];
            $requestBody['buyerTax']       = $dataInv['buyerTax'];
            $requestBody['buyerAddress']   = html_entity_decode($dataInv['buyerAddress']);
            $requestBody['buyerAcc']       = $dataInv['buyerAcc'];
            $requestBody['buyerBank']      = html_entity_decode($dataInv['buyerBank']);
            $requestBody['buyerPhone']     = $dataInv['buyerPhone'];
            $requestBody['buyerFax']       = $dataInv['buyerFax'];

            /******  3. THÔNG TIN SẢN PHẨM/DỊCH VỤ  ******/
            $requestBody['items'] = $dataInv['items'];

            /******  4. KIỂM TRA DỮ LIỆU  ******/
            if (empty($requestBody['items'])) {
                return json_encode([
                    "error" => 1,
                    "httpCode" => 400,
                    "message" => "Sản phảm/Dịch vụ không hợp lệ",
                    "data" => $dataInv
                ]);
            }

            /******  5. CALL API  ******/
            $path = "invoice/add_type_2";
            $res = $this->sendRequest('POST', $path, json_encode($requestBody, JSON_UNESCAPED_UNICODE), $this->header()); // Array
            return json_encode($res);
        }

        return json_encode([
            "error" => 1,
            "httpCode" => 404,
            "message" => "Không tìm thấy hóa đơn",
            "data" => null,
        ]);
    }

    /**
     * Lấy thông tin hóa đơn
     * 
     * @param string $invRef Số phiếu bán
     * @param string $type Loại dữ liệu (json, xml, array)
     * @param string $customerType Loại KH
     * @return string JSON
     */
    public function get($invRef) {
        if (!is_string($invRef) || empty($invRef)) {
            return json_encode([
                "error" => 1,
                "httpCode" => 400,
                "message" => "Số hóa đơn không hợp lệ",
                "data" => null,
            ]);
        }

        $path = 'invoice/get_inv';
        $requestBody = json_encode(['invRef' => $invRef]);
        $res = $this->sendRequest('POST', $path, $requestBody, $this->header());
        return json_encode($res);
    }

    /**
     * Lấy thông tin hóa đơn
     * 
     * @param string $invRef Số phiếu bán
     * @param string $invSerial Ký hiệu HĐ
     * @return string XML
     */
    public function getXML($invRef, $invSerial) {
        $path = "invoice/get_xml_content";
        $requestBody = json_encode([
            "invSign"   => $invSerial,
            "invRef"    => $invRef,
            "invSample" => $this->INVOICE_NUMBER,
        ]);

        $res = $this->sendRequest('POST', $path, $requestBody, $this->header());
        return json_encode($res);
    }

    /** 
     * Lấy danh sách hóa đơn đã kí theo khoảng thời gian
     * 
     * @param string $from_date yyyy/mm/dd
     * @param string $to_date yyyy/mm/dd
     * @return string
     */
    public function get_list($from_date, $to_date) {
        if (empty($from_date) || empty($to_date)) {
            return json_encode([
                "error" => 1,
                "httpCode" => 400,
                "message" => "Thiếu dữ liệu để xử lý yêu cầu",
                "data" => null,
            ]);
        }

        $path = 'invoice/get_signed_inv';
        $requestBody = json_encode([
            "fromDate" => date('Y/m/d', strtotime($from_date)),
            "toDate" => date('Y/m/d', strtotime($to_date))
        ]);
        $res = $this->sendRequest('POST', $path, $requestBody, $this->header());
        return json_encode($res);
    }

    /**
     * Lấy link thông tin hóa đơn
     * 
     * @param array $params Thông tin hóa đơn
     * @param int $raw Là bản nháp
     * @return string
     */
    public function get_link($params, $isRaw = 1) {
        if (is_null($params) || empty($params)) return '';

        $path = '';
        $requestBody = [];
        if ($isRaw == 0) {
            $path = 'invoice/get_link';
            $requestBody['invSample'] = $this->INVOICE_NUMBER;
            $requestBody['invSign']   = $params['invSerial'] ?? '';
            $requestBody['invCode']   = $params['invCode'] ?? ''; // Số hóa đơn
            $requestBody['cmpnKey']   = $params['cmpnKey'] ?? ''; // Mã số thuế
        } elseif ($isRaw == 1) {
            $path = 'invoice/get_link_byref';
            $requestBody['invSample'] = $this->INVOICE_NUMBER;
            $requestBody['invSign']   = $params['invSerial'] ?? '';
            $requestBody['invRef']    = $params['invRef'] ?? ''; // Số phiếu bán
        }
        $res = $this->sendRequest('POST', $path, json_encode($requestBody), $this->header());
        return json_encode($res);
    }

    /**
     * Lấy thông tin công ty dựa vào mã số thuế
     * 
     * @param string $tax_code Mã số thuế
     * @return string
     */
    public function get_company_info($tax_code) {
        if (is_null($tax_code) || empty($tax_code)) {
            return json_encode([
                "error" => 1,
                "httpCode" => 400,
                "message" => "Mã số thuế không hợp lệ",
                "data" => null,
            ]);
        }

        $user_name = 'mhv-api';
        $password = 'mhv-67efa2e8-9ee7-49b5-9174-16670cb7f399';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            // CURLOPT_URL => 'http://tracuunnt.wintvan.vn/api/Tracuu/tracuumst?mst=' . $tax_code,
            CURLOPT_URL => "https://taxinfo.wintvan.vn/api/Tracuu/tracuumst?mst=$tax_code",
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => "$user_name:$password",
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 40,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $json = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return $json;
    }

    /**
     * Hủy hóa đơn
     * 
     * @param array $params Thông tin hóa đơn
     * @param int $is_signed Đã ký số 
     * @return int
     */
    public function delete($params, $is_signed = 0) {
        if (is_null($params) || empty($params)) {
            return json_encode([
                "error" => 1,
                "httpCode" => 400,
                "message" => "Thiếu dữ liệu để xử lý yêu cầu",
                "data" => null,
            ]);
        }

        $path = '';
        $requestBody = [];
        if ($is_signed == 0) {
            $path = 'invoice/delete_raw_inv';
            // $requestBody['invcCode']  = isset($params['invcCode']) ? $params['invcCode'] : ''; // Số hóa đơn
            $requestBody['invcSign']  = $params['invcSign'] ?? ''; // Ký hiệu HĐ
            $requestBody['invRef']    = $params['invRef'] ?? ''; // Số phiếu bán
            $requestBody['note']      = $params['note'] ?? '';
        }
        elseif ($is_signed == 1) { // Hiện tại chỗ này chưa sử dụng vì còn nhiều thủ tục
            return json_encode([
                "error" => 0,
                "httpCode" => 200,
                "message" => "Tính năng chưa sử dụng vì còn nhiều thủ tục",
                "data" => null,
            ]);

            $path = 'invoice/delete?client_id=' . $this->USER;
            $requestBody['invcSign']      = $params['invcSign'] ?? ''; // Ký hiệu HĐ
            $requestBody['invcCode']      = $params['invcCode'] ?? ''; // Số phiếu bán
            $requestBody['invcSample']    = $this->INVOICE_NUMBER;
            $requestBody['description']   = $params['description'] ?? '';
            $requestBody['returnDocNo']   = $params['returnDocNo'] ?? ''; // Số biên bản thu hồi/xóa hóa đơn
        }

        $res = $this->sendRequest('POST', $path, json_encode($requestBody), $this->header());
        return json_encode($res);
    }

    /**
     * Kiểm tra hóa đơn đã được ký hay chưa
     * 
     * @param string $invRef Số phiếu bán
     * @param string $invSerial Ký hiệu HĐ
     * @return int
     */
    public function is_signed($invRef, $invSerial) {
        if (is_null($invRef) || empty($invRef)) {
            return json_encode([
                "error" => 1,
                "httpCode" => 400,
                "message" => "Số phiếu bán không hợp lệ",
                "data" => null,
            ]);
        }

        $path = 'invoice/check_signed';
        $requestBody = json_encode([
            'invRef'    => $invRef,
            'invName'   => $this->INVOICE_NUMBER,
            'invSerial' => $invSerial
        ]);

        $res = $this->sendRequest('POST', $path, json_encode($requestBody), $this->header());
        return json_encode($res);
    }

    // Format date to yyyy-mm-dd
    public function format_date($date) {
        if (is_null($date) || empty($date)) return '';
        $str_replace = str_replace('/', '-', $date);
        $result = strtotime($str_replace) !== FALSE ? date('Y-m-d', strtotime($str_replace)) : '';
        return $result;
    }

    /**
     * Send HTTP request
     * 
     * @param string $method GET, POST, PUT,...
     * @param string $path
     * @param array|string $requestBody
     * @param array $header
     * @param array $curlOptions
     * 
     * @return array [status, httpCode, message, data, description]
     */
    protected function sendRequest($method, $path, $requestBody = null, $header = [], $curlOptions = []) {
        $url = $this->ENDPOINT . $path;

        try {
            $curl = curl_init();
            if ($curl === false) {
                LoggerHelper::error("$method $url cURL failed to initialize");
                return [
                    "error" => 1,
                    "httpCode" => 500,
                    "message" => "System error",
                    "data" => null,
                    "description" => "cURL failed to initialize in BM"
                ];
            }
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            if(!is_null($requestBody)) curl_setopt($curl, CURLOPT_POSTFIELDS, $requestBody);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_FAILONERROR, 1);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 12);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 20);
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->TIMEOUT);
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
                return [
                    "error" => 1,
                    "httpCode" => 500,
                    "message" => "Can not connect to $this->ENDPOINT",
                    "data" => null,
                    "description" => "cURL error $errorNo: $error"
                ];
            }

            $responseArr = json_decode($response, true);
            LoggerHelper::info("$method $url $httpCode", [
                'request' => is_array($requestBody) ? $requestBody : (json_decode($requestBody, true) ?? $requestBody),
                'reponse' => $responseArr ?? $response
            ]);

            // Success
            if(200 <= $httpCode && $httpCode < 300 && isset($responseArr['isSuccess']) && $responseArr['isSuccess'] === true) {
                return [
                    "error"     => 0,
                    "httpCode"  => $httpCode,
                    "message"   => "Success",
                    "data"      => $responseArr['data'] ?? []
                ]; 
            }
            
            return [
                "status" => 0,
                "httpCode" => $httpCode,
                "message" => $responseArr["Message"] ?? trim("Failed to handle $path"),
                "data" => null,
                "description" => $responseArr
            ]; 
        }
        catch (Throwable $th) {
            $message = "Exception error {$th->getCode()}: {$th->getMessage()} on line {$th->getLine()}";
            LoggerHelper::error("$method $url $message");
            return [
                "error" => 1,
                "httpCode" => 500,
                "message" => $message,
                "data" => null
            ];
        }
        finally {
            if (isset($curl) && is_resource($curl)) curl_close($curl);
        }
    }
}