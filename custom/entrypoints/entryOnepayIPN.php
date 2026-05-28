<?php
use custom\services\Notification\NotificationService;

try {
    if(isset($_GET['vpc_TxnResponseCode'])) {
        global $db;
        require_once "custom/include/helpers/api/Onepay.php";

        // Transaction ID
        $transId  = $_GET['vpc_MerchTxnRef'] ?? ''; // Booking name + time();
        $transArr = explode("_", $transId);
        $bookingName = trim($_GET['vpc_OrderInfo'] ?? $transArr[0] ?? '');
        $bookingNameLength = strlen($bookingName);

        $onepay = new Onepay();
        $result = $onepay->handleResponse($_GET);

        $status = $result['status'] ?? false;
        $verifyStatus = $result['verifyStatus'] ?? false;
        $data = $result['data'] ?? []; // Get Onepay data

        if ($verifyStatus) {
            if($bookingNameLength > 8 && $bookingNameLength < 16 && !empty($data)) {
                // Remove entryPoint key
                unset($data['entryPoint']);

                // Add custom data
                $data['payment_date'] = date("Y-m-d H:i:s");

                // Get payment history of booking
                $sql = "SELECT bk.id, bk.nganluong_info
                    FROM ec_flight_bookings bk
                    WHERE bk.name = '$bookingName' AND bk.deleted = 0";
                $res = $db->query($sql);
                $row = $db->fetchByAssoc();

                $bookingId = $row['id'] ?? '';

                if(empty($bookingId)) {
                    $logId = LoggerHelper::generateLogId();
                    $GLOBALS['log']->error("[{$logId}] Not found booking to save Onepay payment info: " . json_encode($result, JSON_UNESCAPED_UNICODE));
                    NotificationService::sendErrorMessage("Not found booking $bookingName to save Onepay payment info\n<i>{$logId}</i>", "default", ['threadKey' => 'logs']);
                }
                else {
                    $bookingPaymentInfoArr = json_decode(html_entity_decode($row['nganluong_info']), true);
                    if(!is_array($bookingPaymentInfoArr) && !isset($bookingPaymentInfoArr[0])) {
                        $bookingPaymentInfoArr = [];
                    }
                    array_push($bookingPaymentInfoArr, $data);

                    $bookingPaymentInfo = json_encode($bookingPaymentInfoArr, JSON_UNESCAPED_UNICODE);
                    $sqlUpdate = 
                        "UPDATE ec_flight_bookings
                        SET bk.nganluong_info = '{$bookingPaymentInfo}'
                        WHERE bk.id = '{$bookingId}'";
                        
                    if(!$db->query($sqlUpdate)) {
                        $logId = LoggerHelper::generateLogId();
                        $GLOBALS['log']->error("[{$logId}] Save Onepay payment info failed: " . json_encode($result, JSON_UNESCAPED_UNICODE));
                        NotificationService::sendErrorMessage("Save Onepay payment info failed\n<i>{$logId}</i>", "default", ['threadKey' => 'logs']);
                    }
                }

                echo "responsecode=1&desc=confirm-success";
                exit();
            }
        }

        $logId = LoggerHelper::generateLogId();
        $GLOBALS['log']->error("[{$logId}] Confirm response from Onepay failed: " . json_encode($result, JSON_UNESCAPED_UNICODE));
        NotificationService::sendErrorMessage("Confirm response from Onepay failed\n<i>{$logId}</i>", "default", ['threadKey' => 'logs']);

        echo "responsecode=0&desc=confirm-failure";
        exit();
    }
    else {
        echo "responsecode=0&desc=no-data";
        exit();
    };
}
catch (Throwable $th) {
    echo "responsecode=0&desc=error-handling";
    exit();
}
