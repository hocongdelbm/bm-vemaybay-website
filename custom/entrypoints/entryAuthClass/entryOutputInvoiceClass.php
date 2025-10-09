<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once "custom/entrypoints/entryAuthClass/entryClass.php";
require_once "custom/include/helpers/api/WinInvoice.php";

/**
 * Class entryAutoBookDatacomClass
 */
class entryOutputInvoiceClass extends entryClass {
    /**
     * Execute digital signing
     * 
     * @param array $params [invRef, recordId]
     * @return array
     */
    public function sign($params = []) {
        $invRef     = global_test_input($params['invRef'] ?? ''); // HD-250603-020
        $recordId   = global_test_input($params['recordId'] ?? ''); // Record ID in database

        if(empty($invRef) || empty($recordId )) {
            return [
                "status" => 0,
                "message" => "Hóa đơn thiếu thông tin",
                "data" => null
            ];
        }

        $winInv = new WinInvoice();
        $responseSign = json_decode($winInv->sign($invRef), true);

        // Update status
        if ($winInv->checkResponse($responseSign)) {
            $invNumber = '';
            for ($i = 0; $i < 3; $i++) {
                if(!empty($invNumber)) break;
                sleep(12); // Pending to get invoice number

                $json = $winInv->get($invRef);
                $arr  = json_decode($json, true);

                if ($winInv->checkResponse($arr)) {
                    $invoiceData = $arr['data'][0] ?? [];
                    $invNumber = $invoiceData['invNumber'] ?? ''; // Số hóa đơn (0000001)
                    $invSerial = $invoiceData['invSerial'] ?? ''; // Ký hiệu hóa đơn (C25THV)
                    $invDate   = $invoiceData['invDate'] ?? ''; // Ngày hóa đơn (Y-m-d)

                    if(empty($invNumber)) continue;

                    global $db, $current_user;
                    $sqlUpdate = "UPDATE ec_hoadonban
                        SET tinhtrang = '2'
                            ,is_signed = 1
                            ,sohoadon = '$invNumber'
                            ,kyhieuhd = '$invSerial'
                            ,ngayhoadon = '$invDate'
                            ,invoice_data = '$json'
                        WHERE id = '$recordId' AND name = '$invRef' AND deleted = 0";

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
                                    $work->assigned_user_id = $current_user->id;
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
                                        $note->assigned_user_id     = $current_user->id;
                                        $note->save();
                                        $arrBookingId[] = $booking_id;
                                    }
                                }  
                            }

                            if(!empty($arrBookingId)) {
                                $listBookingId = "'" . implode("','", $arrBookingId) . "'";
                                $db->query("UPDATE ec_flight_bookings SET is_invoice_export = 1 WHERE id IN ($listBookingId) AND deleted = 0");
                            }
                        }
                        catch(Throwable $th) {
                            $botToken   = $this->telegramConfig['bot_token'] ?? '';
                            $chatId     = $this->telegramConfig['chat_id'] ?? '';
                            $threadId   = $this->telegramConfig['thread_id_logs'] ?? '';
                            $message = "<b>[ERROR] SAVE WORKING PROCESS & NOTE FOR KPI FAIL (SIGN INVOICE)</b>";
                            $message .= "\nException error {$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}";
                            Telegram::sendMessage($message, $botToken, $chatId, $threadId);
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
            return [
                "status" => 0,
                "message" => "Thao tác chưa thành công",
                "data" => null,
                "description" => $responseSign
            ];
        }
    }
}