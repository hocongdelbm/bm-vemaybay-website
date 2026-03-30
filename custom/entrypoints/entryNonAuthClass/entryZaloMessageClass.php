<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once 'custom/entrypoints/entryClass.php';
require_once 'custom/include/helpers/api/APIZaloOA.php';

/**
 * Class entryZaloMessageClass
 * 
 * Gửi tin Zalo
 */
class entryZaloMessageClass extends entryClass {
    /**
     * Send ZNS
     * 
     * @param array $params
     * @return string JSON
     * @author DucPham
     */
    public function sendZNS($params = []) {
        try {
            $type = $params["type"] ?? ""; // ZBS type
            
            if(!in_array($type, ['otp', 'share-phone'])) {
                return [
                    "status" => 0,
                    "message" => "Loại tin không hỗ trợ",
                    "data" => null
                ];
            }

            $epFactory = new entryFactory();
            $entryZaloOAClass = $epFactory->create('entryZaloOAClass');
            return $entryZaloOAClass->sendTemplateMessage($params);
        }
        catch(Throwable $th) {
            $logId = LoggerHelper::generateLogId();
            $GLOBALS['log']->fatal("[{$logId}] {$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
            return [
                "status" => 0,
                "message" => "Exception error $logId",
                "errorId" => $logId,
                "data" => null
            ];
        }
    }
}