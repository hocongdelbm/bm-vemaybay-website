<?php
if (!defined('sugarEntry') || !sugarEntry)
    die('Not A Valid Entry Point');

// Các domain cần đồng bộ Booker IP (phải cài plugin ua-tracker)
$domains = [
    'timchuyenbay.vn', // Active
    'vietjet.net'
];

$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['action'] ?? ''; // Hỗ trợ workaround POST action=delete

if ($method === 'GET') {
    $active_only = isset($_GET['active_only']) ? $_GET['active_only'] : 1;
    $all_ips = [];

    foreach ($domains as $domain) {
        $url = "https://{$domain}/wp-json/uat/v1/booker-ips?active_only=" . $active_only;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $res = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_status === 200 && $res) {
            $json = json_decode($res, true);
            if (isset($json['data']) && is_array($json['data'])) {
                foreach ($json['data'] as $item) {
                    $ip = $item['ip'];
                    if (!isset($all_ips[$ip])) {
                        $all_ips[$ip] = $item;
                        $all_ips[$ip]['domains'] = [$domain];
                    } else {
                        $all_ips[$ip]['domains'][] = $domain;
                        // Nếu 1 domain báo IP này chưa bị xoá (hoặc còn hiệu lực), thì ưu tiên trạng thái đó cho toàn IP
                        if (isset($item['is_deleted']) && !$item['is_deleted']) {
                            $all_ips[$ip]['is_deleted'] = false;
                            $all_ips[$ip]['is_active'] = $all_ips[$ip]['is_active'] || $item['is_active'];
                        }
                    }
                }
            }
        }
    }
    $merged_data = array_values($all_ips);
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode(['data' => $merged_data]);
    exit;
}

if ($method === 'POST') {
    if ($action === 'delete') {
        $target_ip = $_POST['ip'] ?? '';
        $last_res = json_encode(['deleted' => false, 'error' => 'No IP provided']);
        $last_status = 400;

        if ($target_ip) {
            $deleted_any = false;
            foreach ($domains as $domain) {
                // 1. Get List to find exact ID of this IP on this specific domain
                $url_list = "https://{$domain}/wp-json/uat/v1/booker-ips?active_only=0";
                $ch_list = curl_init($url_list);
                curl_setopt($ch_list, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch_list, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch_list, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch_list, CURLOPT_CONNECTTIMEOUT, 5);
                $res_list = curl_exec($ch_list);
                curl_close($ch_list);

                if ($res_list) {
                    $json_list = json_decode($res_list, true);
                    if (isset($json_list['data'])) {
                        foreach ($json_list['data'] as $item) {
                            if ($item['ip'] === $target_ip) {
                                // 2. Delete by matched ID
                                $id = $item['id'];
                                $url_del = "https://{$domain}/wp-json/uat/v1/booker-ips/" . $id;
                                $ch_del = curl_init($url_del);
                                curl_setopt($ch_del, CURLOPT_CUSTOMREQUEST, "DELETE");
                                curl_setopt($ch_del, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch_del, CURLOPT_SSL_VERIFYPEER, false);
                                curl_setopt($ch_del, CURLOPT_TIMEOUT, 10);
                                curl_setopt($ch_del, CURLOPT_CONNECTTIMEOUT, 5);
                                $last_res = curl_exec($ch_del);
                                $last_status = curl_getinfo($ch_del, CURLINFO_HTTP_CODE);
                                curl_close($ch_del);
                                $deleted_any = true;
                                break; // Xoá xong thoát vòng lặp tìm IP
                            }
                        }
                    }
                }
            }
            if ($deleted_any) {
                $last_res = json_encode(['deleted' => true]);
                $last_status = 200;
            }
        }

        http_response_code($last_status);
        header('Content-Type: application/json');
        echo $last_res;
        exit;
    } else {
        // Thực hiện Khai báo IP trên TẤT CẢ các domain
        $input = file_get_contents('php://input'); // JSON payload
        $last_res = null;
        $last_status = 200;

        foreach ($domains as $domain) {
            $url = "https://{$domain}/wp-json/uat/v1/booker-ips";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $last_res = curl_exec($ch);
            $last_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
        }

        http_response_code($last_status ?: 500);
        header('Content-Type: application/json');
        echo $last_res;
        exit;
    }
}
