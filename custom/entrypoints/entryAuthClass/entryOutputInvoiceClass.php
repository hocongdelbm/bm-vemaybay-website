<?php
require_once "custom/entrypoints/entryClass.php";
require_once "custom/include/helpers/api/WinInvoice.php";

use custom\services\Notification\NotificationService;

/**
 * Class entryOutputInvoiceClass
 */
class entryOutputInvoiceClass extends entryClass
{
    /**
     * Create invoice
     * 
     * @param array $params [recordId, invoiceData, buyerData, itemData]
     * @return array
     */
    public function set($params = [])
    {
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
                        ,date_modified = NOW()
                    WHERE id = '$recordId' AND deleted = 0";
                if (!$db->query($sqlUpdate)) $this->sendSQLErrorNotification($sqlUpdate);

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
    public function sign($params = [])
    {
        $invRef     = $this->cleanInput($params['invRef'] ?? ''); // HD-250603-020
        $recordId   = $this->cleanInput($params['recordId'] ?? ''); // Record ID in database

        if (empty($invRef) || empty($recordId)) {
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
                    if (!empty($invNumber)) break;
                    sleep(12); // Pending to get invoice number

                    $json = $winInv->get($invRef);
                    if ($winInv->checkResponse($json)) {
                        $arr = json_decode($json, true);
                        $invoiceData = $arr['data'][0] ?? [];
                        $invNumber = $invoiceData['invNumber'] ?? ''; // Số hóa đơn (0000001)
                        $invSerial = $invoiceData['invSerial'] ?? ''; // Ký hiệu hóa đơn (C25THV)
                        $invDate   = $invoiceData['invDate'] ?? ''; // Ngày hóa đơn (Y-m-d)

                        if (empty($invNumber)) continue;

                        global $db;
                        $jsonSafe       = str_replace("'", "\'", $json);
                        $invNumberSafe  = $db->quote($invNumber);
                        $invSerialSafe  = $db->quote($invSerial);
                        $invDateSafe    = $db->quote($invDate);
                        $recordIdSafe   = $db->quote($recordId);
                        $invRefSafe     = $db->quote($invRef);
                        $userIdSafe     = $db->quote($this->currentUser->id);

                        $sqlUpdate =
                            "UPDATE ec_hoadonban
                            SET tinhtrang = '2',
                                is_signed = 1,
                                sohoadon = '$invNumberSafe',
                                kyhieuhd = '$invSerialSafe',
                                ngayhoadon = '$invDateSafe',
                                invoice_data = '$jsonSafe',
                                modified_user_id = '$userIdSafe',
                                date_modified = NOW()
                            WHERE id = '$recordIdSafe' AND name = '$invRefSafe' AND deleted = 0";

                        if ($db->query($sqlUpdate)) {
                            // Save working process & note (KPI)
                            $arrBookingId = [];
                            $sql = 
                                "SELECT ct.booking_id
                                    , ct.booking
                                    , bk.booking_status
                                    , MAX(CASE WHEN wp.id IS NOT NULL THEN 1 ELSE 0 END) AS wp_invoice_issued
                                FROM ec_chitiethoadon ct
                                    LEFT JOIN ec_flight_bookings bk ON bk.id = ct.booking_id
                                    LEFT JOIN ec_working_process wp ON wp.parent_id = ct.booking_id
                                        AND wp.parent_type = 'EC_Flight_Bookings'
                                        AND wp.invoice_issued = 1
                                        AND wp.deleted = 0
                                WHERE ct.parent_id = '$recordId'
                                    AND ct.parent_type = 'EC_HoaDonBan'
                                    AND ct.deleted = 0
                                GROUP BY ct.booking_id, ct.booking, bk.booking_status";

                            try {
                                $res = $db->query($sql);

                                while ($row = $db->fetchByAssoc($res)) {
                                    $booking_id = $row['booking_id'] ?? '';
                                    $booking = $row['booking'] ?? '';
                                    $booking_status = $row['booking_status'] ?? '';
                                    $workProcessExists = !empty($row['wp_invoice_issued']);

                                    if (!empty($booking_id) && !empty($booking) && !$workProcessExists) {
                                        $work = new EC_Working_Process();
                                        $work->id               = '';
                                        $work->name             = $booking;
                                        $work->description      = trim("Đã xuất hoá đơn đầu ra số: $invNumber");
                                        $work->parent_type      = "EC_Flight_Bookings";
                                        $work->parent_id        = $booking_id;
                                        $work->invoice_issued   = 1;
                                        $work->assigned_user_id = $this->currentUser->id;
                                        $workId = $work->save();

                                        if (is_string($workId)) {
                                            $note = new Note();
                                            $note->id                   = '';
                                            $note->name                 = $booking;
                                            $note->description          = trim("Đã xuất hoá đơn đầu ra số: $invNumber");
                                            $note->parent_type          = "EC_Flight_Bookings";
                                            $note->parent_id            = $booking_id;
                                            $note->booking_status       = $booking_status;
                                            $note->working_process_id   = $workId;
                                            $note->assigned_user_id     = $this->currentUser->id;
                                            $note->save();
                                            $arrBookingId[] = $booking_id;
                                        }
                                        else {
                                            $message = "SAVE WORKING PROCESS FOR KPI FAIL (SIGN INVOICE)";
                                            $message .= "\nInvoice number is $invNumber";
                                            NotificationService::sendErrorMessage($message, '', ['threadKey' => 'logs']);
                                        }
                                    }
                                }

                                if (!empty($arrBookingId)) {
                                    $listBookingId = "'" . implode("','", $arrBookingId) . "'";
                                    $db->query("UPDATE ec_flight_bookings
                                        SET is_invoice_export = 1
                                            ,modified_user_id = '{$this->currentUser->id}'
                                            ,date_modified = NOW()
                                        WHERE id IN ($listBookingId) AND deleted = 0");
                                }
                            } catch (Throwable $th) {
                                $message = "SAVE WORKING PROCESS & NOTE FOR KPI FAIL (SIGN INVOICE)";
                                $message .= "\nException error {$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}";
                                NotificationService::sendErrorMessage($message, '', ['threadKey' => 'logs']);
                            }
                        } else {
                            $this->sendSQLErrorNotification($sqlUpdate);
                        }
                    }
                }

                return [
                    "status" => 1,
                    "message" => "Đã ký số: $invNumber",
                    "data" => null,
                ];
            } else {
                $responseSignArr = json_decode($responseSign, true);
                return [
                    "status" => 0,
                    "message" => $responseSignArr["message"] ?? $responseSign ?? "Thao tác chưa thành công",
                    "data" => null,
                    "description" => $responseSignArr
                ];
            }
        } catch (Throwable $th) {
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
    public function delete($params = [])
    {
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
                    $sqlUpdate = "UPDATE ec_hoadonban
                        SET tinhtrang = '0'
                            ,invoice_data = ''
                            ,modified_user_id = '{$this->currentUser->id}'
                            ,date_modified = NOW()
                        WHERE id = '$recordId' AND deleted = 0";
                    if (!$db->query($sqlUpdate)) $this->sendSQLErrorNotification($sqlUpdate);

                    $sqlUpdate = "UPDATE ec_chitiethoadon
                        SET deleted = 1
                            ,description = 'Đã hủy {$invRef}'
                            ,modified_user_id = '{$this->currentUser->id}'
                            ,date_modified = NOW()
                        WHERE parent_id = '$recordId'
                            AND parent_type = 'EC_HoaDonBan'
                            AND deleted = 0";
                    if (!$db->query($sqlUpdate)) $this->sendSQLErrorNotification($sqlUpdate);
                } catch (Throwable $th) {
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

    /**
     * Liên kết phiếu thu với hóa đơn bán.
     *
     * @param array $params [hoadon_id, receipt_id]
     * @return array
     */
    public function saveHoaDonReceipt($params = [])
    {
        global $db;

        $hoadon_id = global_test_input($params['hoadon_id'] ?? '');
        $receipt_id = global_test_input($params['receipt_id'] ?? '');

        if (strlen($hoadon_id) !== 36 || strlen($receipt_id) !== 36) {
            return ['error' => true, 'message' => 'Invalid hoadon_id or receipt_id'];
        }

        $hoadon = BeanFactory::getBean('EC_HoaDonBan', $hoadon_id);
        if (!$hoadon || empty($hoadon->id)) {
            return ['error' => true, 'message' => 'HoaDonBan not found'];
        }

        $receipt = BeanFactory::getBean('EC_Receipt_Voucher', $receipt_id);
        if (!$receipt || empty($receipt->id)) {
            return ['error' => true, 'message' => 'Receipt voucher not found'];
        }

        $new_id = create_guid();
        $sql = "INSERT INTO hoadonban_receiptvouchers (id, hoadon_id, receipt_id, date_modified, deleted)
                VALUES ('{$new_id}', '{$hoadon_id}', '{$receipt_id}', NOW(), 0)
                ON DUPLICATE KEY UPDATE deleted = 0, date_modified = NOW()";

        if (!$db->query($sql)) {
            $this->sendSQLErrorNotification($sql);
            return ['error' => true, 'message' => 'Relationship saved failed'];
        }

        return ['error' => false, 'message' => 'Relationship saved successfully'];
    }

    /**
     * Xóa liên kết phiếu thu khỏi hóa đơn bán.
     *
     * @param array $params [hoadon_id, receipt_id]
     * @return array
     */
    public function removeHoaDonReceipt($params = [])
    {
        global $db;

        $hoadon_id = global_test_input($params['hoadon_id'] ?? '');
        $receipt_id = global_test_input($params['receipt_id'] ?? '');

        if (strlen($hoadon_id) !== 36 || strlen($receipt_id) !== 36) {
            return ['error' => true, 'message' => 'Invalid hoadon_id or receipt_id'];
        }

        $row = $db->fetchByAssoc($db->query(
            "SELECT id FROM hoadonban_receiptvouchers
             WHERE hoadon_id = '{$hoadon_id}' AND receipt_id = '{$receipt_id}' AND deleted = 0
             LIMIT 1"
        ));

        if (!$row) {
            return ['error' => true, 'message' => 'Relationship not found'];
        }

        $sql = "UPDATE hoadonban_receiptvouchers
                SET deleted = 1, date_modified = NOW()
                WHERE id = '{$row['id']}' AND deleted = 0";

        if (!$db->query($sql)) {
            $this->sendSQLErrorNotification($sql);
            return ['error' => true, 'message' => 'Relationship removed failed'];
        }

        return ['error' => false, 'message' => 'Relationship removed successfully'];
    }

    /**
     * Lấy thông tin hiển thị phiếu thu để render ở bảng liên kết hóa đơn.
     *
     * @param array $params [receipt_id]
     * @return array
     */
    public function getReceiptVoucherInfo($params = [])
    {
        global $db, $app_list_strings;

        $receipt_id = global_test_input($params['receipt_id'] ?? '');
        if (strlen($receipt_id) !== 36) {
            return ['error' => true, 'message' => 'Invalid receipt_id'];
        }

        $receipt_id_safe = $db->quote($receipt_id);
        $sql = "SELECT rv.id, rv.name, rv.amount, rv.amount_type, rv.loai_thu, rv.description,
                   rv.ngaychungtu, rv.rv_status,
                       u.user_name AS assigned_user_name
                FROM ec_receipt_voucher rv
                LEFT JOIN users u ON u.id = rv.assigned_user_id
                WHERE rv.id = '{$receipt_id_safe}' AND rv.deleted = 0
                LIMIT 1";

        $row = $db->fetchByAssoc($db->query($sql));
        if (!$row) {
            return ['error' => true, 'message' => 'Receipt voucher not found'];
        }

        $loai_thu_key = (string)($row['loai_thu'] ?? '');
        $loai_thu_text = $app_list_strings['loai_thu_list'][$loai_thu_key]
            ?? ($app_list_strings['loai_thu_list'][(int)$loai_thu_key] ?? $loai_thu_key);

        $status_key = (string)($row['rv_status'] ?? '');
        $status_text = $app_list_strings['receipt_voucher_status_list'][$status_key]
            ?? ($app_list_strings['receipt_voucher_status_list'][(int)$status_key] ?? $status_key);
        $status_color = $app_list_strings['receipt_voucher_status_color_list'][$status_key]
            ?? ($app_list_strings['receipt_voucher_status_color_list'][(int)$status_key] ?? '');

        return [
            'error' => false,
            'message' => 'OK',
            'data' => [
                'id' => $row['id'] ?? '',
                'name' => $row['name'] ?? '',
                'amount' => $row['amount'] ?? 0,
                'amount_type' => $row['amount_type'] ?? 'VND',
                'ngaychungtu' => $row['ngaychungtu'] ?? '',
                'loai_thu' => $loai_thu_key,
                'loai_thu_text' => $loai_thu_text,
                'rv_status' => $status_key,
                'rv_status_text' => $status_text,
                'rv_status_color' => $status_color,
                'assigned_user_name' => $row['assigned_user_name'] ?? '',
                'description' => $row['description'] ?? '',
            ]
        ];
    }

    /**
     * Tìm kiếm phiếu thu để dùng cho autocomplete.
     *
     * @param array $params [term]
     * @return array
     */
    public function searchReceiptVouchers($params = [])
    {
        global $db, $app_list_strings;

        $raw_term = $params['term'] ?? '';
        $term = global_test_input($raw_term);

        // Tạm thời log để debug
        $GLOBALS['log']->fatal("searchReceiptVouchers raw=[$raw_term] after_sanitize=[$term]");

        if (mb_strlen($term) < 2) {
            return ['error' => false, 'data' => []];
        }

        $termEscaped = $db->quote($term); // trả về: 'PT-231108-322810' (có nháy đơn)
        $termInner = substr($termEscaped, 1, -1); // bỏ nháy đơn 2 đầu → PT-231108-322810
        $likeSafe = "'%" . $termInner . "%'"; // → '%PT-231108-322810%'

        $sql = "SELECT rv.id, rv.name, rv.amount, rv.loai_thu,
               rv.ngaychungtu, rv.rv_status, rv.description,
               u.user_name AS assigned_user_name
        FROM ec_receipt_voucher rv
        LEFT JOIN users u ON u.id = rv.assigned_user_id
        WHERE rv.deleted = 0
          AND rv.name LIKE {$likeSafe}
        ORDER BY rv.date_entered DESC
        LIMIT 10";

        $res = $db->query($sql);
        $data = [];
        while ($row = $db->fetchByAssoc($res)) {
            $loai_thu_key = (string)($row['loai_thu'] ?? '');
            $loai_thu_text = $app_list_strings['loai_thu_list'][$loai_thu_key]
                ?? ($app_list_strings['loai_thu_list'][(int)$loai_thu_key] ?? $loai_thu_key);

            $status_key  = (string)($row['rv_status'] ?? '');
            $status_text = $app_list_strings['receipt_voucher_status_list'][$status_key]
                ?? ($app_list_strings['receipt_voucher_status_list'][(int)$status_key] ?? $status_key);
            $status_color = $app_list_strings['receipt_voucher_status_color_list'][$status_key]
                ?? ($app_list_strings['receipt_voucher_status_color_list'][(int)$status_key] ?? '');

            $data[] = [
                'id'                  => $row['id'],
                'label'               => $row['name'],
                'name'                => $row['name'],
                'amount'              => $row['amount'] ?? 0,
                'loai_thu'            => $row['loai_thu'] ?? '',
                'loai_thu_text'       => $loai_thu_text,
                'ngaychungtu'         => $row['ngaychungtu'] ?? '',
                'rv_status'           => $status_key,
                'rv_status_text'      => $status_text,
                'rv_status_color'     => $status_color,
                'assigned_user_name'  => $row['assigned_user_name'] ?? '',
                'description'         => $row['description'] ?? '',
            ];
        }

        return ['error' => false, 'data' => $data];
    }
}
