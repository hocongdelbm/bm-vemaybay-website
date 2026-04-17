<?php

if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

global $db, $app_list_strings;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$action = isset($_POST["action"]) ? trim($_POST["action"]) : '';

	if ($action == 'release_to_website') {
		$record_id = isset($_POST["record_id"]) ? trim($_POST["record_id"]) : '';

		if (empty($record_id) && strlen($record_id) != 36) {
			echo json_encode([
				'error' => 1,
				'message' => 'Dữ liệu không hợp lệ',
				'record_id' => $record_id
			]);
			exit();
		}

		$voucher = new EC_Vouchers();
		$voucher->retrieve($record_id);
		if ($voucher->id == $record_id) {
			$voucher_info = [
				'id' 			=> $record_id,
				'voucher_code' 	=> $voucher->name,
				'status' 		=> 'pending',
				'campaign_name' => $voucher->campaign_name,
				'campaign_id' 	=> $voucher->campaign_id,
				'max_discount' 	=> (int)$voucher->max_discount,
				'quantity' 		=> (int)$voucher->quantity,
				'start_time' 	=> date('Y-m-d H:i:00', strtotime($voucher->start_time) - 7*3600),
				'end_time' 		=> date('Y-m-d H:i:00', strtotime($voucher->end_time) - 7*3600),
				'condition_voucher' => html_entity_decode(trim($voucher->condition_voucher)),
				'is_hidden' => $voucher->is_hidden
			];
			if($voucher->reduce_amount > 0) $voucher_info['reduce_amount'] = (int)$voucher->reduce_amount;
			elseif($voucher->reduce_percent > 0) $voucher_info['reduce_percent'] = (int)$voucher->reduce_percent;

			$json_result = $voucher->uploadWebsite($voucher->website, $voucher_info);
			$arr_result = json_decode($json_result, true);
			if(isset($arr_result['error']) && $arr_result['error'] == 0) {
				$voucher->status = 'pending';
				$voucher->save2();
			}
			echo $json_result;
			exit();
		}

		echo json_encode([
			'error' => 1,
			'message' => 'Dữ liệu không hợp lệ',
			'record_id' => $record_id
		]);
		exit();
	}
}
