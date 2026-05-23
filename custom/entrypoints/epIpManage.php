<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

header('Content-Type: application/json');

global $current_user;
if (empty($current_user) || empty($current_user->id)) {
    http_response_code(401);
    die(json_encode(['error' => 1, 'message' => 'Unauthorized']));
}

$body = json_decode(file_get_contents('php://input'), true);

$site_key = isset($body['site_key']) ? trim($body['site_key']) : '';
$action   = isset($body['action'])   ? trim($body['action'])   : '';
$ip       = isset($body['ip'])       ? trim($body['ip'])       : '';
$dur      = isset($body['dur'])      ? (int) $body['dur']      : 0;

// Actions that require a valid IP param
$ip_actions   = ['block_ip', 'allow_ip', 'unblock_ip', 'disallow_ip'];
// Actions that only need site_key (list fetchers)
$list_actions = ['get_blocked_ips', 'get_allowed_ips'];

$all_actions = array_merge($ip_actions, $list_actions);
if (!in_array($action, $all_actions, true)) {
    http_response_code(400);
    die(json_encode(['error' => 1, 'message' => 'Invalid action']));
}

if (in_array($action, $ip_actions, true) && !filter_var($ip, FILTER_VALIDATE_IP)) {
    http_response_code(400);
    die(json_encode(['error' => 1, 'message' => 'Invalid IP address']));
}

require_once 'modules/EC_TongHop/views/view.summaryview.php';
$domain_list = Viewsummaryview::$domain_list;

if (empty($site_key) || !isset($domain_list[$site_key])) {
    http_response_code(400);
    die(json_encode(['error' => 1, 'message' => 'Unknown site_key']));
}

$conf     = $domain_list[$site_key];
$page_api = rtrim($conf['page_api'] ?? '', '/');
$api_key  = $conf['api_key'] ?? '';

if (empty($page_api)) {
    http_response_code(400);
    die(json_encode(['error' => 1, 'message' => 'page_api not configured for this site']));
}

$params = ['action' => $action, 'apikey' => $api_key];
if (in_array($action, $ip_actions, true)) {
    $params['ip']  = $ip;
    $params['dur'] = $dur;
}

$url = $page_api . '?' . http_build_query($params);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER     => ['Accept: application/json'],
]);

$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err  = curl_error($ch);
curl_close($ch);

if ($response === false || !empty($curl_err)) {
    http_response_code(502);
    die(json_encode(['error' => 1, 'message' => 'Upstream request failed: ' . $curl_err]));
}

http_response_code($http_code >= 200 && $http_code < 300 ? 200 : $http_code);
echo $response;
exit;
