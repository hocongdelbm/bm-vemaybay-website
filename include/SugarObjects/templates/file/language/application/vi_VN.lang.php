<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$app_list_strings = array(
    strtolower($object_name) . '_category_dom' => array(
        '' => '',
        'Marketing' => 'Marketing',
        'Knowledege Base' => 'Trung tâm hỗ trợ',
        'Sales' => 'Bán hàng',
    ),

    strtolower($object_name) . '_subcategory_dom' => array(
        '' => '',
        'Marketing Collateral' => 'Marketing tài sản thế chấp',
        'Product Brochures' => 'Tài liệu quảng cáo sản phẩm',
        'FAQ' => 'Hỏi đáp',
    ),

    strtolower($object_name) . '_status_dom' => array(
        'Active' => 'Đang hoạt động',
        'Draft' => 'Bản nháp',
        'FAQ' => 'Hỏi đáp',
        'Expired' => 'hết hạn',
        'Under Review' => 'Đang xem xét',
        'Pending' => 'Trì hoãn',
    ),
);
