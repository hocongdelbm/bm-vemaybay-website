<?php
require 'custom/application/Ext/Api/V8/Controller/CustomController.php';

// $app->get('/hello', function() {return 'Hello World!';});

$app->post('/save_call', 'Api\V8\Controller\CustomController:save_call');
$app->post('/save_booking', 'Api\V8\Controller\CustomController:save_booking');
$app->post('/save_contacts', 'Api\V8\Controller\CustomController:save_contacts');
$app->post('/save_voucher', 'Api\V8\Controller\CustomController:save_voucher');
$app->post('/save_hoadonban_receipt', 'Api\V8\Controller\CustomController:save_hoadonban_receipt');
$app->post('/remove_hoadonban_receipt', 'Api\V8\Controller\CustomController:remove_hoadonban_receipt');
$app->post('/save_location_booking', 'Api\V8\Controller\CustomController:save_location_booking');

$app->post('/get_info_voucher', 'Api\V8\Controller\CustomController:get_info_voucher');

$app->post('/send_zns', 'Api\V8\Controller\CustomController:send_zns');