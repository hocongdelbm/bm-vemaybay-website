<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

// Bắt buộc phải có đăng nhập
global $current_user;
if (empty($current_user) || empty($current_user->id)) {
    header('HTTP/1.1 401 Unauthorized');
    die(json_encode(['error' => 'Unauthorized']));
}

// Lấy payload JSON từ request
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (empty($data) || !is_array($data) || empty($data['cities'])) {
    die(json_encode(['error' => 'Invalid data payload']));
}

global $db;

$results = [];
$from_date = !empty($data['from_date']) ? $db->quote($data['from_date']) : '';
$to_date = !empty($data['to_date']) ? $db->quote($data['to_date']) : '';

$system_booker_names = ['Panda Po', 'Bao Gia Khach', 'Báo Giá Khách', 'Khach Hang Hoi', 'Khách Hàng Hỏi', 'Tham Khao', 'Tham Khảo'];

// BƯỚC 1: Tiền xử lý tất cả IP vào một mảng phẳng duy nhất để thực thi 1 query duy nhất
$all_clean_ips = [];
$ip_to_city_map = [];

foreach ($data['cities'] as $city => $ips) {
    // Khởi tạo sẵn giá trị mặc định cho City này
    $results[$city] = ['ThamKhao' => 0, 'Booking' => 0, 'HoanTat' => 0, 'Booker' => 0];

    if (!is_array($ips) || empty($ips)) {
        continue;
    }

    foreach ($ips as $ip) {
        $ip_trimmed = trim((string)$ip);
        if (!empty($ip_trimmed)) {
            $all_clean_ips[] = $db->quote($ip_trimmed);
            $ip_to_city_map[$ip_trimmed] = $city;
        }
    }
}

// BƯỚC 2: Truy vấn một lần duy nhất nếu có IP
if (!empty($all_clean_ips)) {
    try {
        $all_clean_ips = array_unique($all_clean_ips);
        $ip_list_str = "'" . implode("','", $all_clean_ips) . "'";

        $site_domain = !empty($data['site_domain']) ? $db->quote(trim($data['site_domain'])) : '';

        // Cấu trúc Truy vấn SQL (Gộp tất cả IP)
        $sql = "SELECT b.id, b.ip_address, b.is_reference, b.booking_status, b.contact_name AS current_contact_name,
                       (SELECT before_value_string FROM ec_flight_bookings_audit a 
                        WHERE a.parent_id = b.id AND a.field_name = 'contact_name' 
                        ORDER BY a.date_created ASC LIMIT 1) AS initial_contact_name
                FROM ec_flight_bookings b";
                
        if ($site_domain) {
            $sql .= " INNER JOIN users u ON b.created_by = u.id AND u.deleted = 0";
        }
        
        $sql .= " WHERE b.ip_address IN ($ip_list_str) AND b.deleted = 0";

        if ($site_domain) {
            $sql .= " AND u.last_name = '{$site_domain}'";
        }

        // Build Time filter convert UTC to Local (GMT+7)
        if ($from_date && $to_date) {
            $sql .= " AND (DATE_ADD(b.date_entered, INTERVAL 7 HOUR) BETWEEN '{$from_date} 00:00:00' AND '{$to_date} 23:59:59')";
        } else if ($from_date) {
            $sql .= " AND DATE_ADD(b.date_entered, INTERVAL 7 HOUR) >= '{$from_date} 00:00:00'";
        } else if ($to_date) {
             $sql .= " AND DATE_ADD(b.date_entered, INTERVAL 7 HOUR) <= '{$to_date} 23:59:59'";
        }
        // Thực thi query
        $query = $db->query($sql, false);
        if (!$query) {
            throw new Exception("Database Query Error: " . $db->lastDbError());
        }
        // BƯỚC 3: Xử lý và phân bổ kết quả về đúng mảng City
        $matched_ip_set = [];
        while ($row = $db->fetchByAssoc($query)) {
            $ip = trim((string)$row['ip_address']);
            if (!isset($ip_to_city_map[$ip])) {
                continue;
            }
            $matched_ip_set[$ip] = true;
            $city = $ip_to_city_map[$ip];

            // Tên liên hệ gốc (Nếu đã từng đổi tên thì lấy tên cũ nhất từ bảng Audit, không có audit thì lấy hiện hành)
            $initial_contact = !empty($row['initial_contact_name']) ? $row['initial_contact_name'] : $row['current_contact_name'];
            $initial_contact = trim((string) $initial_contact);

            $is_ref = (int)$row['is_reference'] === 1;
            $status = (int)$row['booking_status'];

            $is_booker = in_array($initial_contact, $system_booker_names);

            if ($is_ref) {
                $results[$city]['ThamKhao']++;
            }

            if($is_booker && !$is_ref && $status !== 8) {
                $results[$city]['Booker']++;
            }

            // Điều kiện Booking do Khách Đặt
            if (!$is_ref && !$is_booker) {
                $results[$city]['Booking']++;
            }

            if ($status === 8) {
                $results[$city]['HoanTat']++;
            }
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        die(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
    } catch (Error $e) {
        header('Content-Type: application/json');
        die(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
    }
}

echo json_encode(['status' => 'success', 'data' => $results]);
exit();
