<?php
require_once "custom/entrypoints/entryClass.php";
require_once "custom/include/helpers/api/WinInvoice.php";

use Custom\Services\Notification\NotificationService;

/**
 * Class entryOutputInvoiceClass
 */
class entryOutputInvoiceClass extends entryClass {
    /**
     * Create invoice
     * 
     * @param array $params [recordId, invoiceData, buyerData, itemData]
     * @return array
     */
    public function set($params = []) {
        $recordId    = global_test_input($params['recordId'] ?? '');
        $invoiceData = $params['invoiceData'] ?? [];
        $buyerData   = $params['buyerData'] ?? [];
        $itemData    = $params['itemData'] ?? [];

        if (!empty($recordId) && !empty($invoiceData) && !empty($buyerData) && !empty($itemData)) {
            $items = [];
            for ($i = 0; $i < count($itemData['itemName']); $i++) {
                $items[$i] = [
                    'itemCode'          => trim($itemData['itemCode'][$i]),
                    'itemName'          => trim($itemData['itemName'][$i]),
                    'itemUnit'          => $itemData['itemUnit'][$i],
                    'itemQuantity'      => $itemData['itemQuantity'][$i],
                    'itemPrice'         => $itemData['itemPrice'][$i],
                    'itemVatRate'       => $itemData['itemVatRate'][$i],
                    'itemVatAmnt'       => $itemData['itemVatAmnt'][$i],
                    'itemAmountNoVat'   => $itemData['itemAmountNoVat'][$i]
                ];
            }

            $winInv = new WinInvoice();
            $responseSet = $winInv->set($invoiceData, $buyerData, $items); // JSON

            // Update status
            if ($winInv->checkResponse($responseSet)) {
                global $db;
                $sqlUpdate = "UPDATE ec_hoadonban
                    SET tinhtrang = '1'
                        ,invoice_data = '$responseSet'
                        ,modified_user_id = '{$this->currentUser->id}'
                        ,date_modified = '" . date('Y-m-d H:i:s', time() - 7*60*60) . "'
                    WHERE id = '$recordId' AND deleted = 0";
                if(!$db->query($sqlUpdate)) $this->sendSQLErrorNotification($sqlUpdate);

                return [
                    "status" => 1,
                    "message" => "Ghi sổ thành công",
                    "data" => null,
                ];
            }

            return [
                "status" => 0,
                "message" => "Thao tác chưa thành công",
                "data" => null,
                "description" => json_decode($responseSet, true)
            ];
        }

        return [
            "status" => 0,
            "message" => "Hóa đơn thiếu thông tin",
            "data" => null
        ];
    }

    /**
     * Sign invoice
     * 
     * @param array $params [invRef, recordId]
     * @return array
     */
    public function sign($params = []) {
        $invRef     = global_test_input($params['invRef'] ?? ''); // HD-250603-020
        $recordId   = global_test_input($params['recordId'] ?? ''); // Record ID in database

        if(empty($invRef) || empty($recordId)) {
            return [
                "status" => 0,
                "message" => "Hóa đơn thiếu thông tin",
                "data" => null
            ];
        }

        try {
            $winInv = new WinInvoice();
            $responseSign = $winInv->sign($invRef); // JSON

            // Update status
            if ($winInv->checkResponse($responseSign)) {
                $invNumber = '';
                for ($i = 0; $i < 3; $i++) {
                    if(!empty($invNumber)) break;
                    sleep(12); // Pending to get invoice number

                    $json = $winInv->get($invRef);
                    if ($winInv->checkResponse($json)) {
                        $arr = json_decode($json, true);
                        $invoiceData = $arr['data'][0] ?? [];
                        $invNumber = $invoiceData['invNumber'] ?? ''; // Số hóa đơn (0000001)
                        $invSerial = $invoiceData['invSerial'] ?? ''; // Ký hiệu hóa đơn (C25THV)
                        $invDate   = $invoiceData['invDate'] ?? ''; // Ngày hóa đơn (Y-m-d)

                        if(empty($invNumber)) continue;

                        global $db;
                        $jsonSafe       = str_replace("'", "\'", $json);
                        $invNumberSafe  = $db->quote($invNumber);
                        $invSerialSafe  = $db->quote($invSerial);
                        $invDateSafe    = $db->quote($invDate);
                        $recordIdSafe   = $db->quote($recordId);
                        $invRefSafe     = $db->quote($invRef);
                        $userIdSafe     = $db->quote($this->currentUser->id);
                        $dateModified   = $db->quote(date('Y-m-d H:i:s', time() - 7*60*60));

                        $sqlUpdate =
                           "UPDATE ec_hoadonban
                            SET tinhtrang = '2',
                                is_signed = 1,
                                sohoadon = '$invNumberSafe',
                                kyhieuhd = '$invSerialSafe',
                                ngayhoadon = '$invDateSafe',
                                invoice_data = '$jsonSafe',
                                modified_user_id = '$userIdSafe',
                                date_modified = '$dateModified'
                            WHERE id = '$recordIdSafe' AND name = '$invRefSafe' AND deleted = 0";

                        if($db->query($sqlUpdate)) {
                            // Save working process & note (KPI)
                            try {
                                $arrBookingId = [];
                                $sql = "SELECT DISTINCT ct.booking_id, ct.booking, bk.booking_status
                                    FROM ec_chitiethoadon ct
                                        LEFT JOIN ec_flight_bookings bk ON bk.id = ct.booking_id
                                    WHERE ct.parent_id = '$recordId'
                                        AND ct.parent_type = 'EC_HoaDonBan'
                                        AND ct.deleted = 0";
                                $res = $db->query($sql);

                                while ($row = $db->fetchByAssoc($res)) {
                                    $booking_id = $row['booking_id'] ?? '';
                                    $booking = $row['booking'] ?? '';
                                    $booking_status = $row['booking_status'] ?? '';

                                    if(!empty($booking_id) && !empty($booking)) {
                                        $work = new EC_Working_Process();
                                        $work->id               = '';
                                        $work->name             = $booking;
                                        $work->description      = trim("Đã xuất hoá đơn đầu ra số: $invNumber");
                                        $work->parent_type      = "EC_Flight_Bookings";
                                        $work->parent_id        = $booking_id;
                                        $work->invoice_issued   = 1;
                                        $work->assigned_user_id = $this->currentUser->id;
                                        $workId = $work->save();

                                        if(is_string($workId)) {
                                            $note = new Note();
                                            $note->id                   = '';
                                            $note->name 			    = $booking;
                                            $note->description 		    = trim("Đã xuất hoá đơn đầu ra số: $invNumber");
                                            $note->parent_type 		    = "EC_Flight_Bookings";
                                            $note->parent_id 		    = $booking_id;
                                            $note->booking_status 	    = $booking_status;
                                            $note->working_process_id   = $workId;
                                            $note->assigned_user_id     = $this->currentUser->id;
                                            $note->save();
                                            $arrBookingId[] = $booking_id;
                                        }
                                    }  
                                }

                                if(!empty($arrBookingId)) {
                                    $listBookingId = "'" . implode("','", $arrBookingId) . "'";
                                    $db->query("UPDATE ec_flight_bookings
                                        SET is_invoice_export = 1
                                            ,modified_user_id = '{$this->currentUser->id}'
                                            ,date_modified = '" . date('Y-m-d H:i:s', time() - 7*60*60) . "'
                                        WHERE id IN ($listBookingId) AND deleted = 0");
                                }
                                else {
                                    $message = "SAVE WORKING PROCESS & NOTE FOR KPI FAIL (SIGN INVOICE)";
                                    $message .= "\n<pre>$sql</pre>";
                                    NotificationService::sendErrorMessage($message, '', ['threadKey' => 'logs']);
                                }
                            }
                            catch(Throwable $th) {
                                $message = "SAVE WORKING PROCESS & NOTE FOR KPI FAIL (SIGN INVOICE)";
                                $message .= "\nException error {$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}";
                                NotificationService::sendErrorMessage($message, '', ['threadKey' => 'logs']);
                            }
                        }
                        else {
                            $this->sendSQLErrorNotification($sqlUpdate);
                        }
                    }
                }

                return [
                    "status" => 1,
                    "message" => "Đã ký số: $invNumber",
                    "data" => null,
                ];
            }
            else {
                $responseSignArr = json_decode($responseSign, true);
                return [
                    "status" => 0,
                    "message" => $responseSignArr["message"] ?? $responseSign ?? "Thao tác chưa thành công",
                    "data" => null,
                    "description" => $responseSignArr
                ];
            }
        }
        catch(Throwable $th) {
            return [
                "status" => 0,
                "message" => "{$th->getMessage()} on line {$th->getLine()}",
                "data" => null,
            ];
        }
    }

    /**
     * Delete invoice which is not signed yet
     * 
     * @param array $params
     * @return array
     */
    public function delete($params = []) {
        $recordId   = global_test_input($params['recordId'] ?? '');
        $invRef     = global_test_input($params['invRef'] ?? '');
        $invSerial  = global_test_input($params['invSerial'] ?? ''); // Ký hiệu hóa đơn

        if (!empty($recordId) && !empty($invRef) && !empty($invSerial)) {
            $winInv = new WinInvoice();
            $responseDelete = $winInv->delete([
                'invRef' => $invRef,
                'invcSign' => $invSerial
            ], 0); // Chưa ký
            
            if ($winInv->checkResponse($responseDelete)) {
                global $db;

                // Update status
                try {
                    $date_modified = date('Y-m-d H:i:s', time() - 7*3600);
                    $sqlUpdate = "UPDATE ec_hoadonban
                        SET tinhtrang = '0'
                            ,invoice_data = ''
                            ,modified_user_id = '{$this->currentUser->id}'
                            ,date_modified = '$date_modified'
                        WHERE id = '$recordId' AND deleted = 0";
                    if(!$db->query($sqlUpdate)) $this->sendSQLErrorNotification($sqlUpdate);

                    $sqlUpdate = "UPDATE ec_chitiethoadon
                        SET deleted = 1
                            ,description = 'Đã hủy {$invRef}'
                            ,modified_user_id = '{$this->currentUser->id}'
                            ,date_modified = '$date_modified'
                        WHERE parent_id = '$recordId'
                            AND parent_type = 'EC_HoaDonBan'
                            AND deleted = 0";
                    if(!$db->query($sqlUpdate)) $this->sendSQLErrorNotification($sqlUpdate);
                }
                catch(Throwable $th) {
                    $GLOBALS['log']->fatal("{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
                }

                return [
                    "status" => 1,
                    "message" => "Hủy hóa đơn thành công",
                    "data" => null,
                ];
            }

            return [
                "status" => 0,
                "message" => "Thao tác hủy hóa đơn chưa thành công",
                "data" => null,
                "description" => json_decode($responseDelete, true)
            ];
        }

        return [
            "status" => 0,
            "message" => "Hóa đơn thiếu thông tin để hủy $invRef, $invSerial, $recordId",
            "data" => null,
        ];
    }
}