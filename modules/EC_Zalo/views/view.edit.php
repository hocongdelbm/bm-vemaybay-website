<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.edit.php');

class EC_ZaloViewEdit extends ViewEdit {
	public function __construct() {
		parent::__construct();
	}

    public function display() {
        parent::display();

        // Init oa info timchuyenbay
        global $current_user;
        if($current_user->id == '1') {
            $bean = new EC_Zalo();
            $bean->new_with_id = true;
            $bean->id = '2941581384627345950';
            $bean->name = 'Travelpass tìm chuyến bay';
            $bean->description = "Tìm chuyến bay theo cách của bạn với Travelpass. Hệ thống đặt vé máy bay trực tuyến hàng đầu hiện nay. Mẹo săn vé máy bay giá rẻ khuyến mãi. Giá vé máy bay thời gian thực, đội ngũ nhân viên chuyên nghiệp.Tổng đài vé máy bay phục vụ 24/7. Cần là có, tìm là thấy giá ! Đặt vé đoàn công ty du lịch, lập kế hoạch nghĩ hè cho cả nhóm. Hãy đến với hệ thống \"tìm chuyến bay\", bạn sẽ tận hưởng dịch vụ 5 sao hàng đầu hiện nay trong mảng dịch vụ du lịch hàng không.";
            $bean->oa_alias = "timchuyenbay";
            $bean->oa_type = 2;
            $bean->cate_name = "Du lịch & Lưu trú";
            $bean->is_verified = 1;
            $bean->num_follower = 8806;
            $bean->avatar = "https://s160-ava-talk.zadn.vn/2/a/6/5/6/160/d1e73995ecd8b564c029fe5005462c47.jpg";
            $bean->cover = "https://cover-talk.zadn.vn/f/d/f/8/10/d1e73995ecd8b564c029fe5005462c47.jpg";
            $bean->package_name = "Premium";
            $bean->package_valid_through_date = "12-07-2026";
            $bean->package_auto_renew_date = "13-07-2026";
            $bean->linked_zca = "ZBA-122636";
            $bean->api_oauth_info = '{"access_token":"8cJ3S7LRSLqgRyXsT5XwMo9dndLtJm1U9W7m1pLtFsquElC1Bre8SZfkkWSu0a4a2KYpS3exSWKbJ8X99WnM9mL5wsuDTHWW9XN0RNbI4YSp1UzJ7sKg9XiIpcXEGdT1GtMw55SoHLvpHPmbPWrtM5WcbYPHS5yAO4-jHYeJTJ5u9QjuIL5G26OPZc19NLi3HIsWTrGRUY01B8Hu7abTKHmbWdCf4syxEqFGLJKpD0aRTkb94YGi2rXQp3njD1PNGbVO3cyY2oPCQh5cVWXP6amscqXuQYaqNWNNIrDLRo00Aevh2nvrFLnhi6zS3747M6c-P6u813fCGlnQKJT1Bbm1aaDOOd4tMGUvT1DPGG8N9VXhIL8p8nqBoLurDNGJ2bQESre7OY5kIAOEQ2qQLbPxmGfZ3NDUE6S6YXfnJ7XJ","refresh_token":"GGG1DDpq0br93meotVS9Mq47137Uu5O28MLUCU_wN5ubGoP4tyCeVKTU8qgRu7eYMs4M7u3iHN9uP3frcCHAMLPbGKMtWK9PNZWfO9B5Q5z6EZP6lAbvG7qISrA1embKTZC5LO-8R7D31X1KWxLTGNOL2oYvbbuxNMmaFOJ0IGvmO2CVhEnP4cH6BYhmrtW7RNfkReZ4E5z2Q39RlSKyDdTfGn3Zc4ixF1y_D_IzNJum1p5Npf4dRIaDJtJ1foPY60PCHe6Y9rvTHbLhfCeQU3X8MM75i0fb62nX2TBNOs4CBMfkyT9cSZLo4rJjobzv8NeENllwNd4d1WeVlOO50ty7OHcz-ceC9N8X5k-FV39o3Wedh8vU73Cn4NJIgNz381CcI-gAAbzsCqbLzfyJI6864NM6_rX5Kba0KNPAhSaxNjZZ2rC","expires_in":"90000","expires_at":1761886344,"expires_at_format":"31-10-2025 11:52:24"}';
            $bean->quota_info = '[{"asset_id":"3f635ea3a7e94eb717f8","product_type":"cs","quota_type":"sub_quota","valid_through":"31/10/2025","total":9000,"remain":8886}]';
            $bean->secret_key = "1d37k0CkMuvdVLItxWq1";
            var_dump($bean->save());
        }
    }
}
?>