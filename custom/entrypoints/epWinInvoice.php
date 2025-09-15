<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once('modules/EC_HoaDonBan/WinInvoice.php');
    $type = isset($_POST['type']) ? $_POST['type'] : "";

    // Tạo hóa đơn
    if ($type == 1) {
        $invoice_id   = isset($_POST['invoice_id']) ? $_POST['invoice_id'] : '';
        $invoice_data = isset($_POST['invoice_data']) ? str_replace('&quot;', '"', $_POST['invoice_data']) : '';
        $buyer_data   = isset($_POST['buyer_data']) ? str_replace('&quot;', '"', $_POST['buyer_data']) : '';
        $item_data    = isset($_POST['item_data']) ? str_replace('&quot;', '"', $_POST['item_data']) : '';

        if (!empty($invoice_id) && !empty($invoice_data) && !empty($buyer_data) && !empty($item_data)) {
            $invoice_data = json_decode($invoice_data, true);
            $buyer_data   = json_decode($buyer_data, true);
            $item_data    = json_decode($item_data, true);

            $items = [];
            for ($i = 0; $i < count($item_data['itemName']); $i++) {
                $items[$i] = [
                    'itemCode'          => trim($item_data['itemCode'][$i]),
                    'itemName'          => trim($item_data['itemName'][$i]),
                    'itemUnit'          => $item_data['itemUnit'][$i],
                    'itemQuantity'      => $item_data['itemQuantity'][$i],
                    'itemPrice'         => $item_data['itemPrice'][$i],
                    'itemVatRate'       => $item_data['itemVatRate'][$i],
                    'itemVatAmnt'       => $item_data['itemVatAmnt'][$i],
                    'itemAmountNoVat'   => $item_data['itemAmountNoVat'][$i]
                ];
            }

            $Inv = new WinInvoice();
            $json = $Inv->set($invoice_data, $buyer_data, $items);
            $arr = json_decode($json, true);

            // Update status
            if ($arr['error'] == 0) {
                $json2 = $Inv->get($invoice_data['invRef']);
                $hoadonban = new EC_HoaDonBan();
                $hoadonban->retrieve($invoice_id);
                $hoadonban->tinhtrang = "1";
                $hoadonban->invoice_data = $json2;
                $hoadonban->save();
            }

            echo $json;
            exit();
        }

        echo json_encode([
            'error' => 1,
            'message' => 'ERROR: Thiếu dữ liệu hóa đơn',
            'description' => '',
        ]);
        exit();
    }
    // Ký số hóa đơn
    elseif ($type == 2) {
        $invoice_id = isset($_POST['invoice_id']) ? $_POST['invoice_id'] : '';
        $invRef     = isset($_POST['invRef']) ? $_POST['invRef'] : '';

        if (!empty($invoice_id) && !empty($invRef)) {
            $Inv = new WinInvoice();
            $json = $Inv->sign($invRef);
            $arr = json_decode($json, true);

            // Update status
            if ($arr['error'] == 0) {
                sleep(15); // Pending to get invoice number
                $json2 = $Inv->get($invoice_data['invRef']);
                $arr2 = json_decode($json2, true);
                $sohoadon = isset($arr2['data'][0]['invNumber']) ? $arr2['data'][0]['invNumber'] : '';

                $hoadonban = new EC_HoaDonBan();
                $hoadonban->retrieve($invoice_id);
                $hoadonban->tinhtrang = "2";
                $hoadonban->sohoadon = $sohoadon;
                $hoadonban->ngayhoadon = $arr2['data'][0]['invDate'] ?? '';
                $hoadonban->is_signed = 1;
                $hoadonban->invoice_data = $json2;

                // Save working process & note (KPI)
                if(is_string($hoadonban->save())) {
                    try {
                        global $db, $current_user;

                        $arrBookingId = [];
                        $sql = "SELECT DISTINCT ct.booking_id, ct.booking, bk.booking_status
                            FROM ec_chitiethoadon ct
                                LEFT JOIN ec_flight_bookings bk ON bk.id = ct.booking_id
                            WHERE ct.parent_id = '$invoice_id'
                                AND ct.parent_type = 'EC_HoaDonBan'
                                AND ct.deleted = 0";
                        $res = $db->query($sql);
                        while ($row = $db->fetchByAssoc($res)) {
                            $booking_id = $row['booking_id'] ?? '';
                            $booking = $row['booking'] ?? '';
                            $booking_status = $row['booking_status'] ?? '';

                            if(!empty($booking_id) && !empty($booking)) {
                                $work = new EC_Working_Process();
                                $work->id = '';
                                $work->name = $booking;
                                $work->description = trim("Đã xuất hoá đơn đầu ra số: $sohoadon");
                                $work->parent_type = "EC_Flight_Bookings";
                                $work->parent_id = $booking_id;
                                $work->invoice_issued = 1;
                                $work->assigned_user_id = $current_user->id;
                                $workId = $work->save();

                                if(is_string($workId)) {
                                    $note = new Note();
                                    $note->id = '';
                                    $note->name 			    = $booking;
                                    $note->description 		    = trim("Đã xuất hoá đơn đầu ra số: $sohoadon");
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
                            $db->query("UPDATE ec_flight_bookings
                                    SET is_invoice_export = 1
                                    WHERE id IN ($listBookingId) AND deleted = 0");
                        }
                    }
                    catch(Throwable $th) {}
                }
            }

            echo $json;
            exit();
        }

        echo json_encode([
            'error' => 1,
            'message' => 'ERROR: Thiếu dữ liệu hóa đơn',
            'description' => '',
        ]);
        exit();
    }
    // Hủy hóa đơn chưa ký 
    else if ($type == 0) {
        $invoice_id = isset($_POST['invoice_id']) ? $_POST['invoice_id'] : '';
        $invRef     = isset($_POST['invRef']) ? $_POST['invRef'] : '';

        if (!empty($invoice_id) && !empty($invRef)) {
            $Invoice = new WinInvoice();
            $json    = $Invoice->delete(['invRef' => $invRef], 0); // Chưa ký
            $arr     = json_decode($json, true);

            // Update status
            if ($arr['error'] == 0) {
                $hoadonban = new EC_HoaDonBan();
                $hoadonban->retrieve($invoice_id);
                $hoadonban->tinhtrang = "0";
                $hoadonban->save();
            }

            echo $json;
            exit();
        }

        echo json_encode([
            'error' => 1,
            'message' => 'ERROR: Thiếu dữ liệu hóa đơn',
            'description' => '',
        ]);
        exit();
    }

    exit();
}
