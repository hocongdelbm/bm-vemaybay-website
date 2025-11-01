<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.edit.php');

class EC_ZaloViewEdit extends ViewEdit {
	public function __construct() {
		parent::__construct();
	}

    public function display() {
        parent::display();

        // // Init oa info timchuyenbay
        // global $current_user;
        // if($current_user->id == '1') {
        //     $bean = new EC_Zalo();
        //     $bean->new_with_id = true;
        //     $bean->id = '2941581384627345950';
        //     $bean->name = 'Travelpass tìm chuyến bay';
        //     $bean->description = "Tìm chuyến bay theo cách của bạn với Travelpass. Hệ thống đặt vé máy bay trực tuyến hàng đầu hiện nay. Mẹo săn vé máy bay giá rẻ khuyến mãi. Giá vé máy bay thời gian thực, đội ngũ nhân viên chuyên nghiệp.Tổng đài vé máy bay phục vụ 24/7. Cần là có, tìm là thấy giá ! Đặt vé đoàn công ty du lịch, lập kế hoạch nghĩ hè cho cả nhóm. Hãy đến với hệ thống \"tìm chuyến bay\", bạn sẽ tận hưởng dịch vụ 5 sao hàng đầu hiện nay trong mảng dịch vụ du lịch hàng không.";
        //     $bean->oa_alias = "timchuyenbay";
        //     $bean->oa_type = 2;
        //     $bean->cate_name = "Du lịch & Lưu trú";
        //     $bean->is_verified = 1;
        //     $bean->num_follower = 8818;
        //     $bean->avatar = "https://s160-ava-talk.zadn.vn/2/a/6/5/6/160/d1e73995ecd8b564c029fe5005462c47.jpg";
        //     $bean->cover = "https://cover-talk.zadn.vn/f/d/f/8/10/d1e73995ecd8b564c029fe5005462c47.jpg";
        //     $bean->package_name = "Premium";
        //     $bean->package_valid_through_date = "12-07-2026";
        //     $bean->package_auto_renew_date = "13-07-2026";
        //     $bean->linked_zca = "ZBA-122636";
        //     $bean->api_oauth_info = '{"access_token":"t_rs0p1z8oJNzdq5CNue8j_-SmOoQMTbgv5-AZ9lSMs9fbOk02r3UwVu6ZuA5ozijkWj5Z5X8JE9lIzc2MWg8QAv5HmtQWfAekPHDYaEIagqxrOYBm9i1eJgRIvi2sS8vefRJ6y3NJVvy0aVH3y5PjhX73HmCHr6wUee0cbY4YJykIzhTJ1l4StYEqL53JDiqz4j6tavEKFapZmhSJ4cIS6z0Y5JRGvB_E4sOaKICHlntZriPIWYCD7s8nXzAIDFy-Xu3s4wOIVIiK5UV4T40l6EI7b9JtqOmx12IYf4M3AZkd9pD6aoDu_W9HSaRorBbfSGTWX3F0k4lm12C6a-EfQJ6LWAO4KOfxDBPKraRK3AibS8RKniLTI4KtHETsume8CpGHPjRnVuf5r847jYBFZwVWHkCD-vRH0qQYeg","refresh_token":"IUOuCiIhR111xJCqnQXd5adbFJV6loHX4w025yYH7aaPiWGPu8myGc-96mYgZX8lGBSoJxQEFGLuYH1Tiw8aEaIRE4V0ZGOnCAi-Q_NcA0yzsH9WkDyX8M7JV02GncHIPuDy4-E1GsK8hsS9vvzaAGM8HLlqgtmRUveaNehOBYLud2nNbe8w2KIW6b2_lnGERQeqHAcS2YTMcIXWkvWdFc_S4Ko2t2e02UCF9Vt6041owoa-bwiCKN-9K6cxpsKmJSWaSxk9FrDjiHGsi_CFQ7tn9HQBy7X1RB8aFOcbEneDwJfTnzmRAHRg0adjpXmxC_ad6UNK3aikaX9pqQLB3GcvT6hSgMmA4xvlBPglG4z4jKqkWUnf3bxCR5-kZKSRIwncHwQJRXLGeKbBjviL2RrqR1N0l6KU","expires_in":"90000","expires_at":1762072881,"expires_at_format":"02-11-2025 15:41:21"}';
        //     $bean->quota_info = '[{"asset_id":"3f635ea3a7e94eb717f8","product_type":"cs","quota_type":"sub_quota","valid_through":"30/11/2025","total":9000,"remain":8885}]';
        //     $bean->secret_key = "1d37k0CkMuvdVLItxWq1";
        //     var_dump($bean->save());
        // }
    }
}
?>