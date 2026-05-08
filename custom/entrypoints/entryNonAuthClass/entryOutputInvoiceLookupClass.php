<?php
require_once 'custom/entrypoints/entryClass.php';
require_once 'custom/include/helpers/api/WinInvoice.php';

class entryOutputInvoiceLookupClass extends entryClass {
    public function lookupByRef($params = []) {
        $invRef = $params["invRef"] ?? "";

        try {
            $winInv = new WinInvoice();
            $jsonResult = $winInv->get($invRef);
            $arrResult = json_decode($jsonResult, true);

            $arrResult['status'] = (int) !$arrResult['error'];
            unset($arrResult['error']);
            return $arrResult;
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

    public function lookupByPrivateCode($params = []) {
        $privateCode = $params["privateCode"] ?? "";
        try {
            $winInv = new WinInvoice();
            $jsonResult = $winInv->getByPrivateCode($privateCode);
            $arrResult = json_decode($jsonResult, true);

            $arrResult['status'] = (int) !$arrResult['error'];
            unset($arrResult['error']);
            return $arrResult;
        }
        catch(Throwable $th) {
            $logId = LoggerHelper::generateLogId();
            $GLOBALS['log']->error("[{$logId}] {$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
            return [
                "status" => 0,
                "message" => "Exception error $logId",
                "errorId" => $logId,
                "data" => null
            ];
        }
    }
}
