<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
global $app_list_strings, $app_strings, $mod_strings, $db, $current_user;

if (!empty($_SESSION['authenticated_user_id'])) {
	$call_phone 	     = $_POST['call_phone'];
	$call_id_booking    = $_POST['call_id_booking'];
	$style_call = array(
		'inbound' => 'text-success',
		'outbound' => 'text-primary',
		'missed' => 'text-danger',
		'spam' => 'text-spam',
		'suddenly' => 'text-warning',
	);

	$sql = "
          SELECT c.id, c.name, c.status, c.direction, c.call_from, c.call_to, c.call_type, c.record_file, c.log, c.date_entered 
          FROM calls c
          WHERE c.booking_id = '" . $call_id_booking . "'
          AND c.deleted = 0
		ORDER BY c.date_entered DESC";

	$res = $db->query($sql);
	$row_count = $db->getRowCount($res);

	$html = '';
	if ($row_count > 0) {
		$i = 0;
		$html .= '<div class="list-calls-history">
				<table class="tbl-check-calls-history table-details__booking">
					<thead>
						<tr>
							<th class="hide-mobile" width="3%">STT</th>
							<th width="15%">Mã cuộc gọi</th>
							<th width="10%">Loại</th>
							<th width="12%">Gọi từ</th>
							<th width="12%">Gọi đến</th>
							<th width="15%">Bắt đầu</th>
							<th class="hide-mobile">Ghi âm</th>
						</tr>
					</thead>';
	
		while ($row = $db->fetchByAssoc($res)) {
			$data_log 	= json_decode(html_entity_decode($row['log']), true);
			$call_from 	= strlen($row['call_from']) < 15 ? formatPhoneNumber($row['call_from']) : 'Zalo';
			$call_to 		= strlen($row['call_to']) < 15 ? formatPhoneNumber($row['call_to']) : 'Zalo';
	
			$html .= '
					<tr>
						<td align="center" class="hide-mobile fw-bold">' . ($i + 1) . '</td>
						<td align="center"><a target="_blank" href="index.php?module=Calls&return_module=Calls&action=DetailView&record=' . $row['id'] . '">' . $row['name'] . '</a></td>
						<td align="center" class="fw-semibold ' . $style_call[$row['direction']] . '">' . $app_list_strings['calls_direction_list'][$row['direction']] . '</td>
						<td align="center">' . $call_from . '</td>
						<td align="center">' . $call_to . '</td>
						<td align="center">' . date('d-m-Y H:i:s', strtotime($data_log['call_start'])) . '</td>
						<td class="hide-mobile"><audio controls="" style="height: 40px;"><source src="' . $row['record_file'] . '" type="audio/wav"></audio></td>
						</tr>';
			$i++;
		}
	} else {
		$html .= '<div class="calls-body d-flex flex-column gap-3 p-2 align-items-center">
					<svg fill="#b3b3b3" width="70" height="70" viewBox="0 0 846.66 846.66" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd" version="1.1" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" stroke="#b3b3b3"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <defs> <style type="text/css">  .fil0 {fill:black;fill-rule:nonzero}  </style> </defs> <g id="Layer_x0020_1"> <path class="fil0" d="M93.28 100.89l69.12 0 0 -71.08c0,-11.44 9.28,-20.71 20.72,-20.71l414.03 0c97.36,0 176.94,79.58 176.94,176.94l0 539.02c0,11.44 -9.27,20.71 -20.71,20.71l-69.12 0 0 71.08c0,11.44 -9.28,20.71 -20.72,20.71l-570.26 0c-11.44,0 -20.71,-9.27 -20.71,-20.71l0 -695.25c0,-11.44 9.27,-20.71 20.71,-20.71zm148.42 178.12c-27.24,0 -27.24,-41.42 0,-41.42l273.42 0c27.24,0 27.24,41.42 0,41.42l-273.42 0zm0 216.78c-27.24,0 -27.24,-41.42 0,-41.42l273.42 0c27.24,0 27.24,41.42 0,41.42l-273.42 0zm0 -108.39c-27.24,0 -27.24,-41.42 0,-41.42l273.42 0c27.24,0 27.24,41.42 0,41.42l-273.42 0zm-37.87 -286.51l303.48 0c97.36,0 176.95,79.58 176.95,176.94l0 426.52 48.41 0 0 -518.31c0,-74.49 -61.03,-135.52 -135.52,-135.52l-393.32 0 0 50.37zm11.51 478.47l326.15 0c11.43,0 20.71,9.28 20.71,20.71l0 105.46c0,11.44 -9.28,20.72 -20.71,20.72l-326.15 0c-11.44,0 -20.71,-9.28 -20.71,-20.72l0 -105.46c0,-11.43 9.27,-20.71 20.71,-20.71zm305.43 41.42l-284.72 0 0 64.04 284.72 0 0 -64.04zm-13.46 -478.47l-393.32 0 0 653.83 528.84 0 0 -518.31c0,-74.49 -61.02,-135.52 -135.52,-135.52z"></path> </g> </g></svg>
					<p>Booking chưa có thông tin cuộc gọi!</p>
				</div>';
	}

	echo $html;
	exit;
}
