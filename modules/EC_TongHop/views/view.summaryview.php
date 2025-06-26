<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");
class Viewsummaryview extends SugarView {
    
    public static $domain_list = [
        'domain1' => 'https://timchuyenbay.com/analytics',
        'domain2' => 'https://timchuyenbay.vn/analytics',
        'domain3' => 'https://vietjet.net/analytics',
        'domain4' => 'https://timchuyenbay.com/analytics',
    ];

    public function __construct() {
    }
    function display(){
        $smarty = new Sugar_Smarty();
        $from_date_value = date('Y-m-d 00:00:00');
        $to_date_value = date('Y-m-d 23:59:59');
        $new_domain_list = [];
        foreach (Viewsummaryview::$domain_list as $key => $domain) {
            $domain_name = $this->getDomainFromURL($domain);
            $new_domain_list[$domain_name] = $domain;
        }
        $smarty->assign('NEW_DOMAIN_LIST', $new_domain_list);
        $smarty->assign('DOMAIN_LIST', $this->domain_list);
        $smarty->assign('FROM_DATE_VALUE', $from_date_value);
        $smarty->assign('TO_DATE_VALUE', $to_date_value);
        $smarty->display('modules/EC_TongHop/tpls/view_summary_view.tpl');
    }

    function getDomainFromURL($url){
        $parseUrl = parse_url($url);
        return $parseUrl['host']?? null;
    }
}