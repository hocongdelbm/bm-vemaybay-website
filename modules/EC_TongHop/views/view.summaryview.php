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
     *  - page_api    : URL của page-api.php template (thường là {home}/api)
     *  - api_key     : API_KEY trong wp-config.php của site đó
     *
     * Authentication: WordPress Application Password (Basic Auth)
     * Header: Authorization: Basic base64(username:app_password)
     */
    // public static $domain_list = [
    //     'vietjet.net' => [
    //         'label' => 'vietjet.net',
    //         'api_base' => 'https://vietjet.net/wp-json/uat/v1',
    //         'username' => 'datlnt',
    //         'app_password' => 'YFa@jrA5XW4DnDw6q!N0zZRL',
    //         'page_api' => 'https://vietjet.net/api',
    //         'api_key' => '4F3yBy83DIRuHaFp6ealBkSsb3T3kvZ8MqM',
    //     ],
    //     'timchuyenbay.vn' => [
    //         'label' => 'timchuyenbay.vn',
    //         'api_base' => 'https://timchuyenbay.vn/wp-json/uat/v1',
    //         'username' => 'datlnt',
    //         'app_password' => 'hVr3$43qSskCAg@U7Xq@PJYG',
    //         'page_api' => 'https://timchuyenbay.vn/api',
    //         'api_key' => '4F3yBy83DIRuHaFp6e@alBkS-sb3T3)kvZ8$-MqM',
    //     ],
    //     'timchuyenbay.com' => [
    //         'label' => 'timchuyenbay.com',
    //         'api_base' => 'https://timchuyenbay.com/wp-json/uat/v1',
    //         'username' => 'datlnt',
    //         'app_password' => 'hVr3$43qSskCAg@U7Xq@PJYG',
    //         'page_api' => 'https://timchuyenbay.com/api',
    //         'api_key' => '4F3yBy83DIRuHaFp6e@alBkS-sb3T3)kvZ8$-MqM',
    //     ],
    // ];


    public function __construct()
    {
    }

    function display()
    {
        global $sugar_config;
        $domain_list = $sugar_config['domain_list'] ?? [];
        $smarty = new Sugar_Smarty();

        // Build JS config: label + api_base + pre-encoded auth token
        $select_options = [];
        $domain_config = [];

        foreach ($domain_list as $key => $info) {
            $select_options[$key] = $info['label'];

            // Encode credentials server-side → không lộ plain-text password trong HTML
            $raw_token = base64_encode($info['username'] . ':' . $info['app_password']);

            $domain_config[$key] = [
                'label'    => $info['label'],
                'api_base' => $info['api_base'],
                'token'    => $raw_token,
                'page_api' => $info['page_api'] ?? '',
                'api_key'  => $info['api_key'] ?? '',
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