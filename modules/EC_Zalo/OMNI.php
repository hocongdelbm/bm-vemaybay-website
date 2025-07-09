<?php
global $sugar_config;
define("SUGAR_CONFIG", $sugar_config);

class OMNI {
    private $endpoint;
    private $username;
    private $password;

    public function __construct() {
        $this->endpoint = SUGAR_CONFIG['zalo_config']['omni']['endpoint'] ?? '';
        $this->username = SUGAR_CONFIG['zalo_config']['omni']['username'] ?? '';
        $this->password = SUGAR_CONFIG['zalo_config']['omni']['password'] ?? '';
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

        $url = "$this->endpoint/api/OmniMessage/SendMessage";
        $requestBody = [
            "username"    => $this->username,
            "password"    => $this->password,
            "phonenumber" => $this->formatPhoneNumber84($phoneNumber),
            "routerule"   => ["1", "2", "3"], // Hard code
            "templatecode"=> $templateCode,
            "list_param"  => $listParam
        ];

        try {
            $curl = curl_init();
            if ($curl === false) {
                return json_encode(["status" => 0, "message" => "cURL Failed to initialize"]);
            }

            curl_setopt_array($curl, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_SSL_VERIFYPEER => 1,
                CURLOPT_CUSTOMREQUEST  => "POST",
                CURLOPT_POSTFIELDS     => json_encode($requestBody),
                CURLOPT_TIMEOUT        => 0,
                CURLOPT_HTTPHEADER     => [
                    "Content-Type: application/json",
                ]
            ]);
            $res = curl_exec($curl);
            $err = curl_error($curl);
            $errNo = curl_errno($curl);
            curl_close($curl);

            if($res  === false || $errNo) {
                return json_encode(["status" => 0, "message" => "cURL error $errNo: $err"]);
            }

            return $res;
        }
        catch(Exception $e) {
            return json_encode(["status" => 0, "message" => "Exception {$e->getCode()}: {$e->getMessage()} on line {$e->getLine()}"]);
        }
        finally {
            if(isset($curl) && is_resource($curl)) curl_close($curl);
        }
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
            case 'points':
                return "411270"; // Thông báo tích điểm
                break;
            case 'share-phone':
                return "433046"; // Gửi thông tin chương trình chia sẻ SĐT
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
            case '411270':
                return "Thông báo tích điểm";
                break;
            case '433046':
                return "Gửi thông tin chương trình chia sẻ SĐT";
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
            "-114" => "Người dùng không nhận được ZNS vì các lý do: Trạng thái tài khoản, Tùy chọn nhận ZNS, Sử dụng Zalo phiên bản cũ, hoặc các lỗi nội bộ khác",
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

        return $errors[(string)$errorCode] ?? "Mã lỗi không xác định";
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