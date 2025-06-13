<?php
global $db, $app_list_strings, $current_user;

// Lấy số vé trong bảng hoá đơn đầu vào
if(isset($_REQUEST['for']) && $_REQUEST['for'] == 'getInputInvoice') {
    // $sql = '
    //     SELECT 
    //           id, name, supplier, qty, itinerary, ticket_code
    //         , (IFNULL(cost, 0) + IFNULL(authorized_fee, 0)) AS total
    //         , authorized_fee
    //         , (
    //             SELECT IFNULL(SUM(soluong), 0)
    //             FROM ec_chitiethoadon
    //             WHERE ticket_number_id = ec_input_invoices.id
    //             AND deleted = 0
    //         ) AS used_qty
    //     FROM ec_input_invoices 
    //     WHERE deleted = 0 
    //     AND status = 1
    //     AND (
    //         name LIKE "%' . $_REQUEST['term'] . '%"
    //         OR ticket_code LIKE "%' . $_REQUEST['term'] . '%"
    //     )
    //     GROUP BY id';
    $sql = '
        SELECT 
            b.name AS booking, b.company_name, b.tax_code, b.company_address, b.shipping_address,
            i.id, i.name, i.supplier, i.qty, i.itinerary, i.ticket_code, i.booking_id,
            IFNULL(i.cost_no_vat, 0) AS cost_no_vat,
            IFNULL(i.vat, 0) AS vat,
            i.authorized_fee,
            (IFNULL(i.cost, 0) + IFNULL(i.authorized_fee, 0)) AS total,
            (
                SELECT IFNULL(SUM(soluong), 0)
                FROM ec_chitiethoadon
                WHERE ticket_number_id = i.id AND deleted = 0
            ) AS used_qty
        FROM ec_input_invoices i
            LEFT JOIN ec_flight_bookings b ON i.booking_id = b.id
        WHERE i.status = 1 AND i.deleted = 0
            AND (i.name LIKE "' . $_REQUEST['term'] . '%"  OR  i.ticket_code LIKE "' . $_REQUEST['term'] . '%")
        GROUP BY i.id';

    $res = $db->query($sql);
    $result_arr = array();
    
    while($row = $db->fetchByAssoc($res)) {
        $buyerInfo = json_decode(html_entity_decode($row['shipping_address']), true);

        if ($row['qty'] - $row['used_qty'] > 0) {
            $id = $row['id'];
            $description = '<span class="fw-semibold color-green">OK</span>';
        } else {
            $id = '';
            $description = '<span class="fw-semibold color-red">Đã xuất hết</span>';
        }

        $result_arr[] = array(
            'id'                => $id,
            'booking_id'        => $row['booking_id'],
            'booking'           => $row['booking'],
            'label'             => $row['name'],
            'ticket_code'       => $row['ticket_code'],
            'supplier'          => $app_list_strings['supplier_invoice_list'][$row['supplier']],
            'iti'               => (empty($row['itinerary'])?'&nbsp;':$row['itinerary']),
            'total'             => format_number($row['total'] * $row['qty']), // Tổng giá bán
            'authorized_fee'    => format_number($row['authorized_fee'] / $row['qty']), // Phí thu hộ (sân bay + admin)
            // 'cost'              => $row['cost'], // Giá bán
            // 'vat'               => $row['vat']/$row['qty'], // VAT trên giá bán
            'qty'               => $row['qty'],
            'max_qty'           => ($row['qty'] - $row['used_qty']),
            'desc'              => $description,
            'buyer'             => [
                'lienhe'     => $buyerInfo['iv_account_name'],
                'tencongty'  => $row['company_name'],
                'masothue'   => $row['tax_code'],
                'email'      => $buyerInfo['iv_email'],
                'sotaikhoan' => $buyerInfo['iv_bank_account'],
                'hinhthuctt' => $buyerInfo['iv_payment_method'],
                'diachi'     => $row['company_address']
            ]
        );
    }
    
    echo json_encode($result_arr);
}

// Lấy thông tin khách hàng
if(isset($_REQUEST['for']) && $_REQUEST['for'] == 'getAccountInf') {
    $sql = '
        SELECT a.id, a.name, a.ticker_symbol, a.sic_code, a.shipping_address_street
                , email.email_address
        FROM accounts a
        LEFT JOIN email_addr_bean_rel rel 
        ON rel.bean_id = a.id
        AND rel.deleted = 0
        LEFT JOIN email_addresses email 
        ON email.id = rel.email_address_id
        AND email.deleted = 0
        WHERE a.deleted = 0 
        AND (a.ticker_symbol LIKE "%' . $_REQUEST['term'] . '%"
        OR a.name LIKE "%' . $_REQUEST['term'] . '%"
        OR a.sic_code LIKE "%' . $_REQUEST['term'] . '%")
    ';
    $res        = $db->query($sql);
    $result_arr = array();

    while($row = $db->fetchByAssoc($res)) {
        $result_arr[] = array (
            'label'             => $row['name'],
            'ticker_symbol'     => $row['ticker_symbol'],
            'sic_code'          => $row['sic_code'],
            'address'           => $row['shipping_address_street'],
            'email'             => $row['email_address'],
        );
    }
    echo json_encode($result_arr);
}

// Lấy thông tin công ty dựa vào mã số thuế
if(isset($_REQUEST['for']) && $_REQUEST['for'] == 'getConpanyInfo') {
    require_once('modules/EC_HoaDonBan/WinInvoice.php');
    $tax_code = $_REQUEST['mst'];
    $inv = new WinInvoice();
    echo $inv->get_company_info($tax_code);
    exit();
}

// Lấy thông tin số vé 
if(isset($_POST['for']) && $_POST['for'] == 'getTicketCodeInf') {
    $input_inv = new EC_Input_Invoices;
    $input_inv->retrieve($_POST['ticket_code']);
    echo json_encode(
        array(
            'invoice_date'              => $input_inv->invoice_date,
            'invoice_number'            => $input_inv->invoice_number,
            'invoice_serial'            => $input_inv->invoice_serial,
            'invoice_iti'               => $input_inv->itinerary,
            'invoice_booking'           => $input_inv->booking,
            'invoice_booking_id'        => $input_inv->booking_id,
            'invoice_supplier'          => $input_inv->supplier,
            'invoice_qty'               => $input_inv->qty,
            'invoice_cost'              => $input_inv->cost_no_vat,
            'invoice_vat'               => $input_inv->vat,
            'invoice_cost_vat'          => $input_inv->cost,
            'invoice_authorized'        => $input_inv->authorized_fee,
            'invoice_total'             => $input_inv->total,
            'invoice_accounting_date'   => $input_inv->accounting_date,
        )
    );
}

// Lấy thông tin sl tồn hiện tại của số vé
if(isset($_POST['for']) && $_POST['for'] == 'getMaxQty') {
    $ticket_num_arr = explode(",", $_POST['ticket_num']);

    if(isset($_POST['invoice']) && !empty($_POST['invoice'])) {
        $exist_invoice = ' AND parent_id <> "' . $_POST['invoice'] . '"';
    }

    $sql = '
        SELECT id, qty, 
            (
                SELECT IFNULL(SUM(soluong), 0)
                FROM ec_chitiethoadon
                WHERE ticket_number_id = ec_input_invoices.id AND deleted = 0 ' . $exist_invoice . '
            ) AS used_qty
        FROM ec_input_invoices 
        WHERE id IN ("' . implode('","', $ticket_num_arr)  . '") 
            AND status = 1 
            AND deleted = 0
        GROUP BY id';

    $res = $db->query($sql);
    $ticket_num_leftqty = array();
    while($row = $db->fetchByAssoc($res)) {
        $ticket_num_leftqty[$row['id']] = $row['qty'] - $row['used_qty'];
    }

    echo json_encode($ticket_num_leftqty);
}

// Hủy hóa đơn
if(isset($_POST['for']) && $_POST['for'] == 'reasonCancelInvoice') {
    // Lấy note hiện tại của HD đó
    if(!empty($_POST['hd_record'])) {
        if(trim($_POST['company_unit']) == 'MHV'){
            require_once('modules/EC_HoaDonBan/WinInvoice.php');
            $inv = new WinInvoice();
            $params = [
                'invRef' => $_POST['hd_record_name'],
                'note' => $_POST['description']
            ];
            $inv->delete($params, (int)$_POST['is_cancel_invoive']);
        }

        $sql_detail = '
            UPDATE ec_chitiethoadon 
            SET deleted = 1
            WHERE parent_id = "' . test_input($_POST['hd_record']) . '" AND parent_type = "EC_HoaDonBan"
        ';
        $db->query($sql_detail);

        $sql = '
            UPDATE ec_hoadonban 
            SET tinhtrang = -1, description = "'.test_input($_POST['description']).'"
            WHERE id = "' . test_input($_POST['hd_record']) . '"
        ';
        $db->query($sql);
        echo 1;
    } 
    else {
        echo 0;
    }
    exit();
}

// Nhân bản hóa đơn
if(isset($_POST['for']) && $_POST['for'] == 'duplicateInvoice') {
    try {
        unset($_POST['for']);
        $notRequiredFields = ['booking_id'];

        // Sanitize all POST data
        $params = $errors = [];
        foreach ($_POST as $key => $value) {
            // Skip validation for fields in the "not required" list
            if (in_array($key, $notRequiredFields)) {
                $params[$key] = $value;
                continue;
            }

            // For arrays, check if all elements are empty (optional)
            if (is_array($value)) {
                $allEmpty = true;
                foreach ($value as $item) {
                    if ($item !== '' && $item !== null) {
                        $allEmpty = false;
                        break;
                    }
                }
                if ($allEmpty) {
                    $errors[$key] = "Field $key is required.";
                } else {
                    $params[$key] = array_map('test_input', $value);
                }
            }
            // For single values
            elseif ($value === '' || $value === null) {
                $errors[$key] = "Field $key is required.";
            } else {
                $params[$key] = test_input($value);
            }
        }

        if (!empty($errors)) {
            echo json_encode(['error' => 1, 'message' => 'Invalid param', 'description' => $errors]);
            exit;
        }

        // Get booking id from booking name
        if(!isset($params['booking_id']) || !$params['booking_id']) {
            $booking_id = $db->getOne("SELECT id FROM ec_flight_bookings WHERE name = '". $params['booking'] ."' AND deleted=0");
            $params['booking_id'] = $booking_id;

            if(!$booking_id || !is_string($booking_id)) {
                echo json_encode(['error' => 1, 'message' => 'No booking found', 'data' => $params]);
                exit;
            }
        }

        $input_inv = new EC_Input_Invoices();
        $input_inv->id = '';
        foreach(array_keys($params) as $field) {
            if(strpos($field , "_date") !== false) $params[$field] = date('d-m-Y', strtotime(str_replace('/', '-', $params[$field])));
            $input_inv->$field = $params[$field] ?? null;
        }
        // Calculate total price
        $input_inv->total = $input_inv->cost + $input_inv->authorized_fee;
        $input_inv->status = '1';
        $input_inv->deleted = 0;
        $id = $input_inv->save2();
        $params['id'] = $id;

        if(!$id) echo json_encode(['error' => 1, 'message' => 'Thao tác thất bại', 'data' => $params]);
        else echo json_encode(['error' => 0, 'message' => 'Success', 'data' => $params]);
        exit;
    }
    catch(Throwable $th) {
        echo json_encode(['error' => 1, 'message' => $th->getMessage()]);
        exit;
    }
}


function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}