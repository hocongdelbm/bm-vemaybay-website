<?php

class Onepay {
    private string $hashCode;

    public function __construct() {
        global $sugar_config;
        $this->hashCode = $sugar_config['onepay']['hash_code'] ?? '';
    }

    public function handleResponse(array $responseData): array {
        $vpc_TxnResponseCode = $responseData["vpc_TxnResponseCode"] ?? '';

        $status = false;
        $message = $description = '';
        $hashValidated = false;

        try {
            $hashValidated  = $this->verifyResponseHash($responseData);
            $resDescription = $this->getResponseDescription($vpc_TxnResponseCode);

            if($vpc_TxnResponseCode === "0") {
                if($hashValidated) {
                    $status = true;
                    $message = "Giao dịch thành công";
                }
                else {
                    $message = "Giao dịch đang tiến hành";
                    $description = $resDescription['description']['vi'];
                }
            }
            else {
                $message = "Giao dịch thất bại";
                $description = $resDescription['description']['vi'];
            }

            return [
                'status' => $status,
                'verifyStatus' => $hashValidated,
                'message' => $message,
                'data' => $responseData,
                'description' => $description,
            ];
        }
        catch(Throwable $th) {
            return [
                'status' => false,
                'verifyStatus' => $hashValidated,
                'message' => "Lỗi xử lý",
                'data' => $responseData ?? null,
                'description' => "{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}",
            ];
        }
    }

    public static function getResponseDescription(string $code): array {
        $responses = [
            '0' => [
                'code' => '0',
                'name' => 'Approved',
                'description' => [
                    'vi' => 'Giao dịch thành công',
                    'en' => 'Successful Transaction',
                ],
            ],
            '1' => [
                'code' => '1',
                'name' => 'Unspecified Failure',
                'description' => [
                    'vi' => 'Ngân hàng từ chối cấp phép giao dịch.',
                    'en' => 'Unspecified failure in the authorization process of your Card Issuer.',
                ],
            ],
            '2' => [
                'code' => '2',
                'name' => 'Declined',
                'description' => [
                    'vi' => 'Ngân hàng phát hành thẻ từ chối cấp phép giao dịch.',
                    'en' => 'Card Issuer declined to authorize the transaction.',
                ],
            ],
            '3' => [
                'code' => '3',
                'name' => 'Timed Out',
                'description' => [
                    'vi' => 'Không nhận được kết quả phản hồi từ Tổ chức phát hành thẻ.',
                    'en' => 'No response from Card Issuer.',
                ],
            ],
            '4' => [
                'code' => '4',
                'name' => 'Expired Card',
                'description' => [
                    'vi' => 'Tháng/Năm hết hạn của thẻ không đúng hoặc thẻ đã hết hạn sử dụng.',
                    'en' => 'Invalid Expiration Date or your card is now expired.',
                ],
            ],
            '5' => [
                'code' => '5',
                'name' => 'Insufficient Funds',
                'description' => [
                    'vi' => 'Số dư/Hạn mức của thẻ không đủ để thanh toán.',
                    'en' => 'Your card credit limit/account balance was not enough to cover the payment.',
                ],
            ],
            '6' => [
                'code' => '6',
                'name' => 'Error Communicating with Bank',
                'description' => [
                    'vi' => 'Không nhận được kết quả phản hồi từ Tổ chức phát hành thẻ.',
                    'en' => 'No response from Card Issuer.',
                ],
            ],
            '7' => [
                'code' => '7',
                'name' => 'System Error',
                'description' => [
                    'vi' => 'Lỗi trong quá trình xử lý giao dịch của Ngân hàng.',
                    'en' => 'System error while processing transaction.',
                ],
            ],
            '8' => [
                'code' => '8',
                'name' => 'Not Supported',
                'description' => [
                    'vi' => 'Ngân hàng phát hành thẻ không hỗ trợ thanh toán trực tuyến.',
                    'en' => 'Card Issuer does not support online payment.',
                ],
            ],
            '9' => [
                'code' => '9',
                'name' => 'Invalid Card Name',
                'description' => [
                    'vi' => 'Tên chủ thẻ/tài khoản không hợp lệ.',
                    'en' => 'Card Issuer declined to authorize the transaction.',
                ],
            ],
            '10' => [
                'code' => '10',
                'name' => 'Expired Card',
                'description' => [
                    'vi' => 'Thẻ hết hạn/Thẻ bị khóa.',
                    'en' => 'Your card is now expired or deactivated.',
                ],
            ],
            '11' => [
                'code' => '11',
                'name' => 'Not Registered',
                'description' => [
                    'vi' => 'Thẻ/Tài khoản chưa đăng ký dịch vụ hỗ trợ thanh toán trực tuyến.',
                    'en' => 'Your card/account was not activated features supporting for online payment.',
                ],
            ],
            '12' => [
                'code' => '12',
                'name' => 'Invalid Card Date',
                'description' => [
                    'vi' => 'Tháng/Năm phát hành hoặc hết hạn của thẻ không hợp lệ.',
                    'en' => 'Invalid Issue Date or Expiration Date.',
                ],
            ],
            '13' => [
                'code' => '13',
                'name' => 'Exist Amount',
                'description' => [
                    'vi' => 'Giao dịch vượt quá hạn mức thanh toán trực tuyến theo quy định của Ngân hàng.',
                    'en' => "Your transaction was exceeded online payment limit in accordance with your Bank's regulations.",
                ],
            ],
            '14' => [
                'code' => '14',
                'name' => 'Invalid Card number',
                'description' => [
                    'vi' => 'Số thẻ không hợp lệ.',
                    'en' => 'Invalid card number.',
                ],
            ],
            '21' => [
                'code' => '21',
                'name' => 'Insufficient Fund',
                'description' => [
                    'vi' => 'Số dư tài khoản không đủ để thanh toán.',
                    'en' => 'Your account balance was not enough to cover the payment.',
                ],
            ],
            '22' => [
                'code' => '22',
                'name' => 'Invalid Account',
                'description' => [
                    'vi' => 'Thông tin tài khoản không hợp lệ.',
                    'en' => 'Invalid Account Information.',
                ],
            ],
            '23' => [
                'code' => '23',
                'name' => 'Account Lock',
                'description' => [
                    'vi' => 'Thẻ/Tài khoản bị khóa hoặc chưa được kích hoạt.',
                    'en' => 'Your card/account is now blocked or not activated.',
                ],
            ],
            '24' => [
                'code' => '24',
                'name' => 'Invalid Card Info',
                'description' => [
                    'vi' => 'Thông tin thẻ/tài khoản không hợp lệ.',
                    'en' => 'Invalid Card/Account Information.',
                ],
            ],
            '25' => [
                'code' => '25',
                'name' => 'Invalid OTP',
                'description' => [
                    'vi' => 'Mã xác thực OTP không hợp lệ.',
                    'en' => 'Invalid OTP.',
                ],
            ],
            '26' => [
                'code' => '26',
                'name' => 'Expired OTP',
                'description' => [
                    'vi' => 'Mã xác thực OTP đã hết hiệu lực.',
                    'en' => 'OTP has expired.',
                ],
            ],
            '98' => [
                'code' => '98',
                'name' => 'Authentication Cancelled',
                'description' => [
                    'vi' => 'Xác thực giao dịch bị hủy.',
                    'en' => 'Authentication was cancelled.',
                ],
            ],
            '99' => [
                'code' => '99',
                'name' => 'User Cancel',
                'description' => [
                    'vi' => 'Người dùng hủy giao dịch.',
                    'en' => 'User cancelled transaction.',
                ],
            ],
            'B' => [
                'code' => 'B',
                'name' => 'Transaction Blocked',
                'description' => [
                    'vi' => 'Lỗi trong quá trình xác thực giao dịch của Ngân hàng phát hành thẻ.',
                    'en' => 'Authentication failed.',
                ],
            ],
            'D' => [
                'code' => 'D',
                'name' => 'Awaiting Processing',
                'description' => [
                    'vi' => 'Lỗi trong quá trình xác thực giao dịch của Ngân hàng phát hành thẻ.',
                    'en' => 'Authentication failed.',
                ],
            ],
            'F' => [
                'code' => 'F',
                'name' => '3D Secure Failure',
                'description' => [
                    'vi' => 'Xác thực giao dịch không thành công.',
                    'en' => 'Transaction authentication was not successful.',
                ],
            ],
            'U' => [
                'code' => 'U',
                'name' => 'Card Security Code Failed',
                'description' => [
                    'vi' => 'Xác thực mã CSC không thành công.',
                    'en' => 'CSC authentication was not successful.',
                ],
            ],
            'Z' => [
                'code' => 'Z',
                'name' => 'Cannot Process Card',
                'description' => [
                    'vi' => 'Giao dịch bị từ chối.',
                    'en' => 'Your transaction was declined.',
                ],
            ],
            '253' => [
                'code' => '253',
                'name' => 'Expired',
                'description' => [
                    'vi' => 'Hết thời hạn nhập thông tin thanh toán.',
                    'en' => 'Your session has expired.',
                ],
            ],
        ];

        return $responses[$code] ?? [
            'code' => $code,
            'name' => 'Other',
            'description' => [
                'vi' => 'Lỗi không xác định.',
                'en' => 'Unspecified failure.'
            ]
        ];
    }

    /**
     * Verify OnePAY response hash integrity
     */
    private function verifyResponseHash(array $responseData): bool {
        if (empty($responseData['vpc_SecureHash']) || empty($this->hashCode)) {
            return false;
        }

        $receivedHash = $responseData['vpc_SecureHash'];

        // Remove hash before generating new hash
        unset($responseData['vpc_SecureHash']);

        // Keep only vpc_ and user_ params with non-empty values
        $filteredData = [];

        foreach ($responseData as $key => $value) {
            if (strlen($value) > 0 && (str_starts_with($key, 'vpc_') || str_starts_with($key, 'user_'))) {
                $filteredData[$key] = $value;
            }
        }

        // Sort by key alphabetically
        ksort($filteredData);

        // Build hash data string
        $hashData = urldecode(http_build_query($filteredData, '', '&'));

        // Generate secure hash
        $generatedHash = hash_hmac('SHA256', $hashData, pack('H*', $this->hashCode));

        return strtoupper($receivedHash) === strtoupper($generatedHash);
    }
}