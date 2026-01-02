<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class APIOMNI {
    private $endpoint;
    private $username;
    private $password;

    public function __construct() {
        global $sugar_config;
        $this->endpoint = $sugar_config['zalo_config']['omni']['endpoint'] ?? '';
        $this->username = $sugar_config['zalo_config']['omni']['username'] ?? '';
        $this->password = $sugar_config['zalo_config']['omni']['password'] ?? '';
    }

    /**
     * Send message (ZNS, Autocall, SMS)
     * 
     * @param string $phoneNumber
     * @param string $templateCode
     * @param array $listParam
     * @return string JSON {status, message}
     */
    public function sendMessage($phoneNumber, $templateCode, $listParam) {
        if(!$phoneNumber || !$templateCode || strlen($phoneNumber) < 10 || empty($templateCode)) {
            return json_encode(["status" => 0, "message" => "Invalid params"]);
        }

        $url = "$this->endpoint/OmniMessage/SendMessage";
        $header = ["Content-Type: application/json"];
        $requestBody = [
            "username"    => $this->username,
            "password"    => $this->password,
            "phonenumber" => $this->formatPhoneNumber84($phoneNumber),
            "routerule"   => ["1", "2", "3"], // Hard code
            "templatecode"=> $templateCode,
            "list_param"  => $listParam
        ];

        return $this->sendRequest("POST", $url, $header, json_encode($requestBody));
    }

    /** 
     * Get template code
     * 
     * @param string $type
     * @return string
     */
    public function getTemplateCode($type) {
        if(is_null($type) || empty($type)) return "";

        switch ($type) {
            case 'journey-one-way':
                return "466986"; // Hành trình một chiều
                break;
            case 'journey-round-trip':
                return "466988"; // Hành trình khứ hồi
                break;
            case 'payment':
                return "466992"; // Thông tin thanh toán
                break;
            case 'code-one-way':
                return "466996"; // Code vé một chiều
                break;
            case 'code-round-trip':
                return "466998"; // Code vé khứ hồi
                break;
            case 'after-call-sale': 
                return "467009"; // CSKH sau khi gọi
                break;
            case 'delay':
                return "467001"; // Thông báo delay
                break;
            case 'remind-flight':
                return "467004"; // Nhắc nhở giờ bay
                break;
            case 'points':
                return "467010"; // Thông báo tích điểm
                break;
            case 'share-phone':
                return "467011"; // Gửi thông tin chương trình chia sẻ SĐT
                break;
            case 'otp':
                return "518686"; // Gửi OTP qua SĐT
                break;
            default:
                return "";
        }
    }

    /** 
     * Get template name
     * 
     * @param string $templateCode
     * @return string
     */
    public function getTemplateName($templateCode = null) {
        if(is_null($templateCode) || empty($templateCode)) return "";
    
        switch ($templateCode) {
            case '466986':
            case '466988':
                return "Thông tin hành trình";
                break;
            case '466992':
                return "Thông tin thanh toán";
                break;
            case '466996':
            case '466998':
                return "Thông tin code vé";
                break;
            case '467001':
                return "Thông báo delay";
                break;
            case '467004':
                return "Nhắc nhở giờ bay";
                break;
            case '467009':
                return "Chăm sóc khách hàng (Call sale)";
                break;
            case '467010':
                return "Thông báo tích điểm";
                break;
            case '467011':
                return "Gửi thông tin chương trình chia sẻ SĐT";
                break;
            case '518686':
                return "Gửi OTP";
                break;
            default:
                return "";
        }
    }

    /** 
     * Get error description when sending ZNS fail
     * 
     * @param int|string $errorCode
     * @return string
     */
    public function getErrorDescription($errorCode) {
        $errors = [
            "-100" => "Xảy ra lỗi không xác định, vui lòng thử lại sau",
            "-101" => "Ứng dụng gửi ZNS không hợp lệ",
            "-102" => "Ứng dụng gửi ZNS không tồn tại",
            "-103" => "Ứng dụng chưa được kích hoạt",
            "-104" => "Secret key của ứng dụng không hợp lệ",
            "-105" => "Ứng dụng gửi ZNS chưa đươc liên kết với OA nào",
            "-106" => "Phương thức không được hỗ trợ",
            "-107" => "ID thông báo không hợp lệ",
            "-108" => "Số điện thoại không hợp lệ",
            "-109" => "ID mẫu ZNS không hợp lệ",
            "-1091" => "Template không có trạng thái Reject hoặc Template được tạo từ Admin tool",
            "-110" => "Phiên bản Zalo app không được hỗ trợ. Người dùng cần cập nhật phiên bản mới nhất",
            "-111" => "Mẫu ZNS không có dữ liệu",
            "-112" => "Nội dung mẫu ZNS không hợp lệ",
            "-1121" => "Dữ liệu tham số vượt quá giới hạn ký tự",
            "-1122" => "Dữ liệu mẫu ZNS thiếu tham số ",
            "-1123" => "Không thể tạo QR code, vui lòng kiểm tra lại",
            "-1124" => "Dữ liệu tham số không đúng format",
            "-113" => "Button không hợp lệ",
            "-1131" => "Đường dẫn liên kết không đúng định dạng",
            "-114" => "Người dùng không nhận được ZNS vì các lý do: Trạng thái tài khoản, tùy chọn nhận ZNS, sử dụng Zalo phiên bản cũ, hoặc các lỗi khác",
            "-115" => "Tài khoản ZNS không đủ số dư",
            "-116" => "Nội dung không hợp lệ",
            "-117" => "OA hoặc ứng dụng gửi ZNS chưa được cấp quyền sử dụng mẫu ZNS này",
            "-118" => "Tài khoản Zalo không tồn tại hoặc đã bị vô hiệu hoá",
            "-119" => "Tài khoản không thể nhận ZNS",
            "-120" => "OA chưa được cấp quyền sử dụng tính năng này",
            "-1201" => "OA chưa có quyền tạo template tag 3",
            "-1202" => "OA không có quyền sử dụng media resources (image/logo)",
            "-121" => "Mẫu ZNS không có nội dung",
            "-122" => "Body request không đúng định dạng JSON",
            "-123" => "Giải mã nội dung thông báo RSA thất bại",
            "-124" => "Mã truy cập không hợp lệ",
            "-1241" => "appsecret_proof không hợp lệ",
            "-125" => "ID Official Account không hợp lệ",
            "-126" => "Ví (development mode) không đủ số dư",
            "-127" => "Template test chỉ có thể được gửi cho quản trị viên",
            "-128" => "Mã encoding key không tồn tại",
            "-129" => "Không thể tạo RSA key, vui lòng thử lại sau",
            "-130" => "Nội dung mẫu ZNS vượt quá giới hạn kí tự",
            "-131" => "Mẫu ZNS chưa được phê duyệt",
            "-132" => "Tham số không hợp lệ",
            "-133" => "Mẫu ZNS này không được phép gửi vào ban đêm (từ 22h-6h)",
            "-134" => "Người dùng chưa phản hồi gợi ý nhận ZNS từ OA",
            "-135" => "OA chưa có quyền gửi ZNS (chưa được xác thực, đang sử dụng gói miễn phí)",
            "-1351" => "OA không có quyền gửi ZNS (Hệ thống chặn do phát hiện vi phạm) ",
            "-136" => "Cần kết nối với ZCA để sử dụng tính năng này",
            "-137" => "Thanh toán ZCA thất bại (ví không đủ số dư, …)",
            "-138" => "Ứng dụng gửi ZNS chưa có quyền sử dụng tính năng này",
            "-1381" => "OA chưa cấp quyền cho Extension về quyền sử dụng ZCA của OA",
            "-139" => "Người dùng từ chối nhận loại ZNS này",
            "-140" => "OA chưa được cấp quyền gửi ZNS hậu mãi cho người dùng này",
            "-141" => "Người dùng từ chối nhận ZNS từ Official Account",
            "-142" => "RSA key không tồn tại, vui lòng gọi API tạo RSA key",
            "-143" => "RSA key đã tồn tại, vui lòng gọi API lấy RSA key",
            "-144" => "OA đã vượt giới hạn gửi ZNS trong ngày",
            "-1441" => "OA request gửi vượt ngưỡng monthly promotion quota",
            "-145" => "OA không được phép gửi loại nội dung ZNS này",
            "-146" => "Mẫu ZNS này đã bị vô hiệu hoá do chất lượng gửi thấp",
            "-147" => "Mẫu ZNS đã vượt giới hạn gửi trong ngày",
            "-1471" => "OA đã vượt giới hạn gửi tin ZNS hậu mãi cho người dùng này trong tháng.",
            "-148" => "Không tìm thấy ZNS journey token",
            "-149" => "ZNS journey token không hợp lệ",
            "-1491" => "ZNS journey token type không tương thích với template",
            "-150" => "ZNS journey token đã hết hạn",
            "-151" => "Không phải mẫu ZNS E2EE",
            "-152" => "Lấy E2EE key thất bại",
            "-153" => "Dữ liệu truyền vào sai quy định",
            "-158" => "Dung lượng file vượt qua dung lượng cho phép",
            "-159" => "Định dạng file upload không được cho phép",
            "-160" => "Số lượng tạo/edit template hoặc upload attachment vượt quá daily quota",
            "-161" => "sending_mode truyền sai giá trị cho phép",
            "-162" => "Chế độ Gửi vượt hạn mức (sending_mode = 3) không hỗ trợ để gửi tin tag 1, 2",
        ];

        return $errors[(string)$errorCode] ?? trim("Mã lỗi không xác định $errorCode");
    }

    /***************  Utils  ***************/
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
    private function sendRequest($method, $url, $header = [], $requestBody = null, $curlOptions = []) {
        try {
            $curl = curl_init();
            if ($curl === false) {
                LoggerHelper::error("$method $url cURL failed to initialize");
                return json_encode([
                    "status" => 0,
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
            curl_setopt($curl, CURLOPT_MAXREDIRS, 16);
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
                LoggerHelper::error("$method $url cURL error $errorNo: $error");
                return json_encode([
                    "status" => 0,
                    "httpCode" => 500,
                    "message" => "Can't connect to Vinacis API",
                    "data" => null,
                    "description" => "cURL error $errorNo: $error"
                ]);
            }

            $responseArr = json_decode($response, true);

            if ($httpCode < 200 || $httpCode >= 300) {
                return json_encode([
                    "status" => 0,
                    "httpCode" => $httpCode,
                    "message" => $responseArr["message"] ?? "Error $httpCode: Failed to handle request",
                    "data" => null,
                    "description" => $responseArr
                ]);
            }

            $responseArr['status'] = (int)$responseArr['status'];
            return json_encode($responseArr);
        }
        catch (Throwable $th) {
            $message = "Exception error {$th->getCode()}: {$th->getMessage()} on line {$th->getLine()}";
            LoggerHelper::error("$method $url $message");
            return json_encode([
                "status" => 0,
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

    public function formatPhoneNumber84($phone) {
        $phoneFormat = trim($phone);
        $phoneFormat = str_replace(' ', '', $phoneFormat);
        $phoneFormat = preg_replace('/^\+84/', 0, $phoneFormat);
        $phoneFormat = preg_replace('/^84/', 0, $phoneFormat);
        $phoneFormat = preg_replace('/^00/', 0, $phoneFormat);
        $phoneFormat = preg_replace('/^0/', '84', $phoneFormat);
        return $phoneFormat;
    }
}