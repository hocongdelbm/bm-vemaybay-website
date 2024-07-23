<?php
class WinInvoice extends InvoiceLogs {
    private $ENDPOINT = 'https://quanly.wininvoice.vn/api/';
    private $USER;
    private $PASSWORD;
    private $INVOICE_NUMBER; // Mẫu số hóa đơn
    private $INVOICE_SERIAL; // Ký hiệu hóa đơn
    private $TIMEOUT = 30;

    function __construct($have_code = 1) {
        parent::__construct();
        $this->USER           = '0316735473';
        $this->PASSWORD       = 'wcwdcdg34f8jvu8d16adaa807f24f5c852e63cdddfb708c4';
        $this->INVOICE_NUMBER = '1';
        $this->INVOICE_SERIAL = 'C24THV';
        $this->FILENAME       = date('Y_m_d').'.log';
    }

    function header() {
        $code = base64_encode($this->USER . ':' . $this->PASSWORD);

        return array(
            'Content-Type: application/json',
            'Authorization: Basic ' . $code
        );
    }
    
    /** Tạo hóa đơn, Cập nhật hóa đơn (nếu đã tồn tại và chưa ký số)
     * @param $invoice : Thông tin hóa đơn
     * @param $buyer : Thông tin khách hàng 
     * @param $items : Thông tin sản phẩm/dịch vụ
     * @return json
     */
    public function set($invoice, $buyer, $items) {
        if (is_null($invoice) || is_null($buyer) || is_null($items) ||
            empty($invoice) || empty($buyer) || empty($items)
        ) return '';

        $post_data = array();
        /******  1. THÔNG TIN HÓA ĐƠN  ******/
        $post_data['invName']    = $this->INVOICE_NUMBER;
        $post_data['invSerial']  = $this->INVOICE_SERIAL;
        $post_data['invDate']    = isset($invoice['invDate']) ? $this->format_date($invoice['invDate']) : date('Y-m-d'); // YYYY-mm-dd
        $post_data['invRef']     = isset($invoice['invRef']) ? $invoice['invRef'] : ''; // Mã hóa đơn
        $post_data['invRefDate'] = isset($invoice['invRefDate']) ? $this->format_date($invoice['invRefDate']) : date('Y-m-d'); // YYYY-mm-dd
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
        $post_data['invCustomer'] = isset($invoice['invCustomer']) ? $invoice['invCustomer'] : '1'; // KH CÁ NHÂN HAY TỔ CHỨC

        /******  2. THÔNG TIN KHÁCH HÀNG  ******/
        $post_data['buyerName']      = isset($buyer['buyerName']) ? $buyer['buyerName'] : '';
        $post_data['buyerCompany']   = isset($buyer['buyerCompany']) ? $buyer['buyerCompany'] : '';
        $post_data['buyerEmail']     = isset($buyer['buyerEmail']) ? $buyer['buyerEmail'] : 'ngandtk@giaonhanh.net';
        // Optional
        $post_data['buyerCode']      = isset($buyer['buyerCode']) ? $buyer['buyerCode'] : '';
        $post_data['buyerTax']       = isset($buyer['buyerTax']) ? $buyer['buyerTax'] : '';
        $post_data['buyerAddress']   = isset($buyer['buyerAddress']) ? $buyer['buyerAddress'] : '';
        $post_data['buyerAcc']       = isset($buyer['buyerAcc']) ? $buyer['buyerAcc'] : '';
        $post_data['buyerBank']      = isset($buyer['buyerBank']) ? $buyer['buyerBank'] : '';
        $post_data['buyerPhone']     = isset($buyer['buyerPhone']) ? $buyer['buyerPhone'] : '';
        $post_data['buyerFax']       = isset($buyer['buyerFax']) ? $buyer['buyerFax'] : '';

        /******  3. THÔNG TIN SẢN PHẨM/DỊCH VỤ  ******/
        $post_data['items'] = array();
        $check_item = true;
        foreach ($items as $k => $i) {
            if(!isset($i['itemName']) || empty($i['itemName']) || $i['itemPrice'] == 0 || $i['itemAmountNoVat'] == 0) {
                $check_item = false;
                continue;
            }

            $post_data['items'][] = [
                'itemNo'          => isset($i['itemNo']) ? $i['itemNo'] : '',
                'itemCode'        => isset($i['itemCode']) ? $i['itemCode'] : '',
                'itemName'        => isset($i['itemName']) ? $i['itemName'] : '',
                'itemQuantity'    => isset($i['itemQuantity']) ? $i['itemQuantity'] : 1,
                'itemPrice'       => isset($i['itemPrice']) ? $i['itemPrice'] : 0, // GIÁ CHƯA VAT
                'itemVatRate'     => isset($i['itemVatRate']) ? $i['itemVatRate'] : 0, // VAT SẢN PHẨM
                'itemVatAmnt'     => isset($i['itemVatAmnt']) ? $i['itemVatAmnt'] : 0, // TIỀN VAT
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
        if(empty($post_data['items']) || $check_item === false) {
            return json_encode([
                'error' => 1,
                'message' => 'Sản phảm/Dịch vụ không hợp lệ',
                'description' => ''
            ]);
        }


        /******  5. CALL API  ******/
        $url = $this->ENDPOINT . 'invoice/add_type_2';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL             => $url,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_FAILONERROR     => true,
            CURLOPT_MAXREDIRS       => 10,
            CURLOPT_TIMEOUT         => $this->TIMEOUT,
            CURLOPT_CUSTOMREQUEST   => 'POST',
            CURLOPT_HTTPHEADER      => $this->header(),
            CURLOPT_POSTFIELDS      => json_encode($post_data),
            
        ));
        $json = curl_exec($curl);
        $this->log(json_encode($post_data) . "   " . $json, 'info', $url);
        if (curl_errno($curl)) {
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error_msg = curl_error($curl);
            $this->log($error_msg, 'error: '.$httpcode, $url);
        }
        curl_close($curl);

        $array = json_decode($json, true);
        if(isset($array['isSuccess']) && $array['isSuccess'] === true && isset($array['data']) && !empty($array['data'])) {
            return json_encode([
                'error' => 0,
                'message' => 'Ghi hóa đơn thành công',
                'description' => $array['data'],
            ]);
        }
        return json_encode([
            'error' => 1,
            'message' => $array['errorMessage'],
            'description' => $array['data']
        ]);
    }

    /** Ký số hóa đơn
     * @param $invRef : Số phiếu bán
     * @return bool
     */
    public function sign($invRef) {
        if(is_null($invRef) || empty($invRef)) return false;

        $data = $this->get($invRef, 'array');
        $post_data = [];
        if(!is_null($data) && !empty($data)) {
            /******  1. THÔNG TIN HÓA ĐƠN  ******/
            $post_data['invName']    = $data['invName'];
            $post_data['invSerial']  = $data['invSerial'];
            $post_data['invDate']    = $data['invDate'];
            $post_data['invRef']     = $data['invRef'];
            $post_data['invRefDate'] = $data['invRefDate'];
            $post_data['invSubTotal']    = $data['invSubTotal'];
            $post_data['invVatRate']     = $data['invVatRate'];
            $post_data['invVatAmount']   = $data['invVatAmount'];
            $post_data['invTotalAmount'] = $data['invTotalAmount'];
            // Tiền tệ (Optional)
            $post_data['invPayment']      = $data['invPayment'];
            $post_data['invCurrency']     = $data['invCurrency'];
            $post_data['invExchangeRate'] = $data['invExchangeRate'];
            // Chiết khấu (Optional)
            $post_data['invDscnAmnt'] = $data['invDscnAmnt'];
            $post_data['note'] = $data['note'];
            // Thông tin khác (Optional)
            $post_data['invAutoSign'] = '1'; // KÝ TỰ ĐỘNG
            $post_data['invCustomer'] = $data['invCustomer'];

            /******  2. THÔNG TIN KHÁCH HÀNG  ******/
            $post_data['buyerName']      = $data['buyerName'];
            $post_data['buyerCompany']   = $data['buyerCompany'];
            $post_data['buyerEmail']     = $data['buyerEmail'];
            // Optional
            $post_data['buyerCode']      = $data['buyerCode'];
            $post_data['buyerTax']       = $data['buyerTax'];
            $post_data['buyerAddress']   = $data['buyerAddress'];
            $post_data['buyerAcc']       = $data['buyerAcc'];
            $post_data['buyerBank']      = $data['buyerBank'];
            $post_data['buyerPhone']     = $data['buyerPhone'];
            $post_data['buyerFax']       = $data['buyerFax'];

            /******  3. THÔNG TIN SẢN PHẨM/DỊCH VỤ  ******/
            $post_data['items'] = $data['items'];

            /******  4. KIỂM TRA DỮ LIỆU  ******/
            if(empty($post_data['items'])) {
                return json_encode([
                    'error' => 1,
                    'message' => 'Sản phảm/Dịch vụ không hợp lệ',
                    'description' => ''
                ]);
            }
        }

        /******  5. CALL API  ******/
        $url = $this->ENDPOINT . 'invoice/add_type_2';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL             => $url,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_FAILONERROR     => true,
            CURLOPT_MAXREDIRS       => 10,
            CURLOPT_TIMEOUT         => $this->TIMEOUT,
            CURLOPT_CUSTOMREQUEST   => 'POST',
            CURLOPT_HTTPHEADER      => $this->header(),
            CURLOPT_POSTFIELDS      => json_encode($post_data),
            
        ));
        $json = curl_exec($curl);
        $this->log(json_encode($post_data) . "   " . $json, 'info', $url);
        if (curl_errno($curl)) {
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error_msg = curl_error($curl);
            $this->log($error_msg, 'error: '.$httpcode, $url);
        }
        curl_close($curl);

        $array = json_decode($json, true);
        if(isset($array['isSuccess']) && $array['isSuccess'] === true && isset($array['data']) && !empty($array['data'])) {
            return json_encode([
                'error' => 0,
                'message' => 'Ký số hóa đơn thành công',
                'description' => $array['data'],
            ]);
        }
        return json_encode([
            'error' => 1,
            'message' => $array['errorMessage'],
            'description' => $array['data']
        ]);
    }

    /** Lấy thông tin hóa đơn
     * @param $invRef : Số phiếu bán
     * @param $type : Loại dữ liệu (json, xml, array)
     * @return string
     */
    public function get($invRef, $type = 'json') {
        if(is_null($invRef) || empty($invRef)) return null;

        if($type == 'json' || $type == 'array') {
            $url = $this->ENDPOINT . 'invoice/get_inv';
            $post_data = ['invRef' => $invRef];
        }
        elseif($type == 'xml') {
            $url = $this->ENDPOINT . 'invoice/get_xml_content';
            $post_data = [
                'invSign'   => $this->INVOICE_SERIAL,
                'invSample' => $this->INVOICE_NUMBER,
                'invRef'    => $invRef
            ];
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL             => $url,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_FAILONERROR     => true,
            CURLOPT_MAXREDIRS       => 10,
            CURLOPT_TIMEOUT         => $this->TIMEOUT,
            CURLOPT_CUSTOMREQUEST   => 'POST',
            CURLOPT_POSTFIELDS      => json_encode($post_data),
            CURLOPT_HTTPHEADER      => $this->header(),
        ));
        $json = curl_exec($curl);
        $this->log($json, 'info', $url);
        if (curl_errno($curl)) {
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error_msg = curl_error($curl);
            $this->log($error_msg, 'error: '.$httpcode, $url);
        }
        curl_close($curl);

        $array = json_decode($json, true);
        if(isset($array['isSuccess']) && $array['isSuccess'] === true && !empty($array['data']) && isset($array['data'][0]) && !empty($array['data'][0])) {
            if($type == 'json' || $type == 'xml') return $json;
            elseif($type == 'array') return $array['data'][0];
        }
        return null;
    }

    /** Lấy danh sách hóa đơn đã kí theo khoảng thời gian
     * @param $from_date : yyyy/mm/dd
     * @param $to_date : yyyy/mm/dd
     * @return string
     */
    public function get_list($from_date, $to_date) {
        if(empty($from_date) || empty($to_date)) return '';

        $url = $this->ENDPOINT . 'invoice/get_signed_inv';
        $post_data = ["fromDate" => date('Y/m/d', strtotime($from_date)), "toDate" => date('Y/m/d', strtotime($to_date))];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL             => $url,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_FAILONERROR     => true,
            CURLOPT_MAXREDIRS       => 10,
            CURLOPT_TIMEOUT         => $this->TIMEOUT,
            CURLOPT_CUSTOMREQUEST   => 'POST',
            CURLOPT_HTTPHEADER      => $this->header(),
            CURLOPT_POSTFIELDS      => json_encode($post_data),
        ));
        $json = curl_exec($curl);
        $this->log($json, 'info', $url);
        if (curl_errno($curl)) {
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error_msg = curl_error($curl);
            $this->log($error_msg, 'error: '.$httpcode, $url);
        }
        curl_close($curl);

        $array = json_decode($json, true);
        if(isset($array['isSuccess']) && $array['isSuccess'] === true && !empty($array['data'])) {
            return json_encode($array['data']);
        }
        return '';
    }

    /** Lấy link thông tin hóa đơn
     * @param $params : Thông tin hóa đơn
     * @param $raw : Là bản nháp
     * @return string
     */
    public function get_link($params, $raw = 1) {
        if (is_null($params) || empty($params)) return '';

        $url = '';
        $post_data = [];
        if($raw == 0) {
            $url = $this->ENDPOINT . 'invoice/get_link';
            $post_data['invSign']   = $this->INVOICE_SERIAL;
            $post_data['invSample'] = $this->INVOICE_NUMBER;
            $post_data['invCode']   = isset($params['invCode']) ? $params['invCode'] : ''; // Số hóa đơn
            $post_data['cmpnKey']   = isset($params['cmpnKey']) ? $params['cmpnKey'] : ''; // Mã số thuế
        }
        elseif($raw == 1) {
            $url = $this->ENDPOINT . 'invoice/get_link_byref';
            $post_data['invSign']   = $this->INVOICE_SERIAL;
            $post_data['invSample'] = $this->INVOICE_NUMBER;
            $post_data['invRef']    = isset($params['invRef']) ? $params['invRef'] : ''; // Số phiếu bán
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL             => $url,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_FAILONERROR     => true,
            CURLOPT_MAXREDIRS       => 10,
            CURLOPT_TIMEOUT         => $this->TIMEOUT,
            CURLOPT_CUSTOMREQUEST   => 'POST',
            CURLOPT_HTTPHEADER      => $this->header(),
            CURLOPT_POSTFIELDS      => json_encode($post_data),
        ));
        $json = curl_exec($curl);
        $this->log($json, 'info', $url);
        if (curl_errno($curl)) {
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error_msg = curl_error($curl);
            $this->log($error_msg, 'error: '.$httpcode, $url);
        }
        curl_close($curl);

        $array = json_decode($json, true);
        if(isset($array['isSuccess']) && $array['isSuccess'] === true) {
            if(isset($array['data']['link'])) return $array['data']['link'];
            return '';
        }
        return '';
    }

    /** Lấy thông tin công ty dựa vào mã số thuế
     * @param $tax_code : Mã số thuế
     * @return string
     */
    public function get_company_info($tax_code) {
        if(is_null($tax_code) || empty($tax_code)) return null;
        $user_name = 'mhv-api';
        $password = 'mhv-67efa2e8-9ee7-49b5-9174-16670cb7f399';

        $curl = curl_init();
        curl_setopt_array($curl, array(
            // CURLOPT_URL => 'http://tracuunnt.wintvan.vn/api/Tracuu/tracuumst?mst=' . $tax_code,
            CURLOPT_URL => 'http://taxinfo.wintvan.vn/api/Tracuu/tracuumst?mst=' . $tax_code,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => $user_name . ":" . $password,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $json = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if($httpcode == 200) return $json;
        return null;
    }

    /** Hủy hóa đơn
     * @param $params : Thông tin hóa đơn
     * @param $is_signed : Đã ký số 
     * @return int
     */
    public function delete($params, $is_signed = 0) {
        if (is_null($params) || empty($params)) return 0;

        $url = '';
        $post_data = [];
        if($is_signed == 0) {
            $url = $this->ENDPOINT . 'invoice/delete_raw_inv';
            // $post_data['invcCode']  = isset($params['invcCode']) ? $params['invcCode'] : ''; // Số hóa đơn
            $post_data['invcSign']  = $this->INVOICE_SERIAL;
            $post_data['invRef']    = isset($params['invRef']) ? $params['invRef'] : ''; // Số phiếu bán
            $post_data['note']      = isset($params['note']) ? $params['note'] : '';
        }
        elseif($is_signed == 1) { // Hiện tại chỗ này chưa sử dụng vì còn nhiều thủ tục
            return 0;
            $url = $this->ENDPOINT . 'invoice/delete?client_id='.$this->USER;
            $post_data['invcCode']      = isset($params['invcCode']) ? $params['invcCode'] : ''; // Số phiếu bán
            $post_data['invcSign']      = $this->INVOICE_SERIAL;
            $post_data['invcSample']    = $this->INVOICE_NUMBER;
            $post_data['description']   = isset($params['description']) ? $params['description'] : '';
            $post_data['returnDocNo']   = isset($params['returnDocNo']) ? $params['returnDocNo'] : ''; // Số biên bản thu hồi/xóa hóa đơn
        }
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL             => $url,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_FAILONERROR     => true,
            CURLOPT_MAXREDIRS       => 10,
            CURLOPT_TIMEOUT         => $this->TIMEOUT,
            CURLOPT_CUSTOMREQUEST   => 'POST',
            CURLOPT_HTTPHEADER      => $this->header(),
            CURLOPT_POSTFIELDS      => json_encode($post_data),
        ));
        $json = curl_exec($curl);
        $this->log($json, 'info', $url);
        if (curl_errno($curl)) {
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error_msg = curl_error($curl);
            $this->log($error_msg, 'error: '.$httpcode, $url);
        }
        curl_close($curl);

        $array = json_decode($json, true);
        if(isset($array['isSuccess']) && $array['isSuccess'] === true && isset($array['data']) && !empty($array['data'])) {
            if($array['data'][0]['OK'] == 1) 
                return json_encode([
                    'error' => 0,
                    'message' => "Bỏ ghi sổ thành công",
                    'description' => ''
                ]);
        }
        
        return json_encode([
            'error' => 1,
            'message' => 'Bỏ ghi thất bại',
            'description' => $array['errorMessage']
        ]);
    }

    /** Kiểm tra hóa đơn đã được ký hay chưa
     * @param $invRef : Số phiếu bán
     * @return int
     */
    public function is_signed($invRef) {
        if (is_null($invRef) || empty($invRef)) return null;

        $url = $this->ENDPOINT . 'invoice/check_signed';
        $post_data = [
            'invRef'    => $invRef,
            'invName'   => $this->INVOICE_NUMBER,
            'invSerial' => $this->INVOICE_SERIAL
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL             => $url,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_FAILONERROR     => true,
            CURLOPT_MAXREDIRS       => 10,
            CURLOPT_TIMEOUT         => $this->TIMEOUT,
            CURLOPT_CUSTOMREQUEST   => 'POST',
            CURLOPT_HTTPHEADER      => $this->header(),
            CURLOPT_POSTFIELDS      => json_encode($post_data),
        ));
        $json = curl_exec($curl);
        $this->log($json, 'info', $url);
        if (curl_errno($curl)) {
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error_msg = curl_error($curl);
            $this->log($error_msg, 'error: '.$httpcode, $url);
        }
        curl_close($curl);

        $array = json_decode($json, true);
        if($array['isSuccess'] === true && !empty($array['data'])) {
            return isset($array['data']['signed']) ? $array['data']['signed'] : 0;
        }
        return 0;
    }

    // Format date to yyyy-mm-dd
    public function format_date($date) {
        if(is_null($date) || empty($date)) return '';

        $str_replace = str_replace('/', '-', $date);
        $result = strtotime($str_replace) !== FALSE ? date('Y-m-d', strtotime($str_replace)) : '';
        return $result;
    }
}

class InvoiceLogs {
    private $PATH;
    protected $FILENAME;

    function __construct() {
        $year = date('Y');
        $month = str_pad(date('m'), 2, "0", STR_PAD_LEFT );
        $this->PATH = "modules/EC_HoaDonBan/logs/$year/$month/";
        $this->FILENAME = date('Y_m_d').'.log';
    }

    public function log($content, $type = 'info', $more = '') {
        $row = date('Y-m-d H:i:s') . ' ['.strtoupper($type).'] ['.$more.'] ' . trim($content) . "\n";
        return $this->write_file($row);
    }

    protected function write_file($text) {
        if(empty($text)) return false;

        $file_name = $this->PATH . $this->FILENAME;
        $myfile = fopen($file_name, "a") or die("Error something !!!");
       
        fwrite($myfile, $text);
        fclose($myfile);
        return true;
    }
}