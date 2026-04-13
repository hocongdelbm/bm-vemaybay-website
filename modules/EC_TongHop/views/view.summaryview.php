<?php
if (!defined('sugarEntry') || !sugarEntry)
    die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");

class Viewsummaryview extends SugarView
{
    /**
     * Cấu hình các website cần theo dõi.
     *
     * Mỗi domain cần:
     *  - label       : Tên hiển thị
     *  - api_base    : URL gốc của REST API (wp-json/uat/v1)
     *  - username    : WordPress username (admin)
     *  - app_password: WordPress Application Password
     *                  (Tạo tại: WP Admin → Users → Profile → Application Passwords)
     *
     * Authentication: WordPress Application Password (Basic Auth)
     * Header: Authorization: Basic base64(username:app_password)
     */
    public static $domain_list = [
        'timchuyenbay.vn' => [
            'label' => 'timchuyenbay.vn',
            'api_base' => 'https://timchuyenbay.vn/wp-json/uat/v1',
            'username' => 'datlnt', // ← WordPress username
            'app_password' => 'hVr3$43qSskCAg@U7Xq@PJYG', // ← Application Password
        ],
        'timchuyenbay.com' => [
            'label' => 'timchuyenbay.com',
            'api_base' => 'https://timchuyenbay.com/wp-json/uat/v1',
            'username' => 'datlnt',
            'app_password' => 'hVr3$43qSskCAg@U7Xq@PJYG',
        ],
        'vietjet.net' => [
            'label' => 'vietjet.net',
            'api_base' => 'https://vietjet.net/wp-json/uat/v1',
            'username' => 'datlnt',
            'app_password' => 'Y3rcC1Uo*!&9JcRjO&',
        ],
    ];

    public function __construct()
    {
    }

    function display()
    {
        $smarty = new Sugar_Smarty();

        // Build JS config: label + api_base + pre-encoded auth token
        $select_options = [];
        $domain_config = [];

        foreach (self::$domain_list as $key => $info) {
            $select_options[$key] = $info['label'];

            // Encode credentials server-side → không lộ plain-text password trong HTML
            $raw_token = base64_encode($info['username'] . ':' . $info['app_password']);

            $domain_config[$key] = [
                'label' => $info['label'],
                'api_base' => $info['api_base'],
                'token' => $raw_token, // Authorization: Basic {token}
            ];
        }

        $smarty->assign('SELECT_OPTIONS', $select_options);
        $smarty->assign('DOMAIN_CONFIG_JSON', json_encode($domain_config));
        $smarty->display('modules/EC_TongHop/tpls/view_summary_view.tpl');
    }

    function getDomainFromURL($url)
    {
        $parseUrl = parse_url($url);
        return $parseUrl['host'] ?? null;
    }
}