<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
global $app_list_strings, $app_strings, $mod_strings, $db, $current_user;

if(!empty($_SESSION['authenticated_user_id'])) {
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
          WHERE c.booking_id = '".$call_id_booking."'
          AND c.deleted = 0
		ORDER BY c.date_entered DESC";
	
	// pr($sql);

	$res = $db->query($sql);
     $i = 0;
     $html ='<div class="list-calls-history">
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

     while($row = $db->fetchByAssoc($res)){
		$data_log 	= json_decode(html_entity_decode($row['log']), true);
		$call_from 	= strlen($row['call_from']) < 15 ? formatPhoneNumber($row['call_from']) : 'Zalo';
		$call_to 		= strlen($row['call_to']) < 15 ? formatPhoneNumber($row['call_to']) : 'Zalo';

          $html .= '
				<tr>
					<td align="center" class="hide-mobile fw-bold">'.($i + 1).'</td>
					<td align="center"><a target="_blank" href="index.php?module=Calls&return_module=Calls&action=DetailView&record='.$row['id'].'">'.$row['name'].'</a></td>
					<td align="center" class="fw-semibold '.$style_call[$row['direction']].'">'.$app_list_strings['calls_direction_list'][$row['direction']].'</td>
					<td align="center">'.$call_from.'</td>
					<td align="center">'.$call_to.'</td>
					<td align="center">'.date('d-m-Y H:i:s', strtotime($data_log['call_start'])).'</td>
					<td class="hide-mobile"><audio controls="" style="height: 40px;"><source src="'.$row['record_file'].'" type="audio/wav"></audio></td>
                    </tr>';
          $i++;
     }

     echo $html;
}