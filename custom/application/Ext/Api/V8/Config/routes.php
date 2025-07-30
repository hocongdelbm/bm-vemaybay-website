<?php
require 'custom/application/Ext/Api/V8/Controller/CustomController.php';

// $app->get('/hello', function() {return 'Hello World!';});

$app->post('/save_call', 'Api\V8\Controller\CustomController:save_call');
$app->post('/save_booking', 'Api\V8\Controller\CustomController:save_booking');
$app->post('/save_contacts', 'Api\V8\Controller\CustomController:save_contacts');
$app->post('/get_info_voucher', 'Api\V8\Controller\CustomController:get_info_voucher');
$app->post('/send_zns', 'Api\V8\Controller\CustomController:send_zns');