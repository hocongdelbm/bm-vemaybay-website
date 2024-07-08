
<?php
require_once("include/Sugar_Smarty.php");

class Viewaddbonus extends SugarView {
	function display() {
		if(ACLController::checkAccess('EC_Flight_Bookings', 'edit', true)){
			$smartyCont= new Sugar_Smarty();
			$this->populateContent($smartyCont);
			$smartyCont->display('modules/EC_TongHop/tpls/view_addbonus.tpl');
		} else {
			header("Location: index.php?module=EC_TongHop&action=Error&error_string=".urlencode("Bạn không được quyền truy cập vào mục này"));
			exit();
		}
	}
	
	function populateContent($smartyobj) {
		global $db, $app_list_strings, $current_user, $locale, $timedate;
		
		$readonly 		= is_admin($current_user) ? '' : 'readonly="readonly"';
		$readonly_bg 	= $readonly != '' ? 'background:#e2e2e2' : '';
		$disabled 		= is_admin($current_user) ? '' : 'disabled="disabled"';
		$date_time_format 	= $timedate->get_date_time_format();
		$sql_search 		= "";
		
		// Từ ngày
		if(isset($_REQUEST['from_date']) && !empty($_REQUEST['from_date'])){
			$sql_search .= " AND DATE(DATE_ADD(w.date_entered, INTERVAL 7 HOUR)) >= '".date('Y-m-d', strtotime($_REQUEST['from_date']))."' ";
			$from_date_value = $_REQUEST['from_date'];
		} else {
			$sql_search .= " AND DATE(DATE_ADD(w.date_entered, INTERVAL 7 HOUR)) >= '".date('Y-m-01')."' ";
			$from_date_value = date('01-m-Y');
		}
		
		// Đến ngày
		if(isset($_REQUEST['to_date']) && !empty($_REQUEST['to_date'])){
			$sql_search .= " AND DATE(DATE_ADD(w.date_entered, INTERVAL 7 HOUR)) <= '".date('Y-m-d', strtotime($_REQUEST['to_date']))."' ";
			$to_date_value = $_REQUEST['to_date'];
		} else {
			$sql_search .= " AND DATE(DATE_ADD(w.date_entered, INTERVAL 7 HOUR)) <= '".date('Y-m-t')."' ";
			$to_date_value = date('t-m-Y');
		}
		
		// Phân quyền dữ liệu
		if(!is_admin($current_user)){
			$sql_search .= " AND w.assigned_user_id='".$current_user->id."' ";
		}
		
		// Kiểm tra xem trong khoảng thời gian 2 tháng
		$date_diff = (abs(strtotime($to_date_value) - strtotime($from_date_value)) / 60 / 60 / 24) + 1;
		if(!is_admin($current_user) && $date_diff > 60){
			echo 'Vui lòng chọn khoảng thời gian 60 ngày';
			exit;
		}
		
		$sql = "SELECT w.id AS detail_id,
					w.name AS booking,
					w.parent_id AS booking_id,
					w.parent_type AS module,
					b.contact_name,
					w.description,
					IFNULL(b.total_amount, 0) AS total_amount,
					(	
						(
							SELECT SUM(IFNULL(d.total_bought_price, 0))
							FROM ec_booking_details d
							WHERE d.booking_id = b.id AND d.deleted = 0
						)
						+ 
						(
							SELECT IF(b.flight_type = '0',
										SUM(
											IF(p.luggage_price > 0, IFNULL(p.luggage_purchase, 0), 0) 
											+
											IF(p.luggage_price_inbound > 0, IFNULL(p.luggage_purchase_inbound,0), 0)
										),
									 	SUM(IF(p.luggage_price > 0, IFNULL(p.luggage_purchase, 0), 0))
									)
							FROM ec_booking_passengers p
							WHERE p.booking_id = b.id AND p.deleted = 0
						)
					) AS total_purchase,
					(IFNULL(b.total_amount,0) - (SELECT total_purchase)) AS total_profit,
					DATE_ADD(w.date_entered, INTERVAL 7 HOUR) AS date_entered,
					w.assigned_user_id,
					CONCAT(IFNULL(u.last_name, ''), IF(u.first_name IS NOT NULL, ' ', ''), IFNULL(u.first_name, '')) AS assigned_user,
					IFNULL(w.bonus, 0) AS bonus,
					w.is_approved,
					w.approved_by_id,
					CONCAT(IFNULL(u1.last_name, ''), IF(u1.first_name IS NOT NULL, ' ', ''), IFNULL(u1.first_name, '')) AS approved_by,
					w.approved_note
				FROM ec_working_process w
					LEFT JOIN ec_flight_bookings b ON w.parent_id = b.id AND b.deleted = 0
					LEFT JOIN users u ON w.assigned_user_id = u.id AND u.deleted = 0
					LEFT JOIN users u1 ON w.approved_by_id = u1.id AND u1.deleted = 0
				WHERE w.parent_type = 'EC_Flight_Bookings' 
					AND w.bonus IS NOT NULL 
					".$sql_search."
					AND	w.deleted = 0";

        
		// if($current_user->user_name == 'nponline' || $current_user->user_name == 'hungnh') {
		// 	echo $sql;
        // }
		
		$res = $db->query($sql);
		$html = '';
		$i = 0;
		while($row = $db->fetchByAssoc($res)){
			$approved_readonly = ($row['is_approved'] && !is_admin($current_user)) ? 'readonly="readonly"' : '';
			$approved_disabled = ($row['is_approved'] && !is_admin($current_user)) ? 'disabled="disabled"' : '';
			$approved_readonly_bg = $approved_readonly != '' ? 'background:#e2e2e2' : '';
			
			$html .= '<tr id="ct_line'.$i.'">';
			
			$html .= '<td align="center"><input '.$approved_readonly.' ln="'.$i.'" class="ac_booking box-input" type="text" name="ct_booking[]" id="ct_booking'.$i.'" value="'.$row['booking'].'" maxlength="255" style="width:100%; '.$approved_readonly_bg.'" /><input type="hidden" name="ct_booking_id[]" id="ct_booking_id'.$i.'" value="'.$row['booking_id'].'" /></td>';
			
			$html .= '<td align="left"><a title="Xem chi tiết booking '.$row['booking'].'" href="index.php?module=EC_Flight_Bookings&action=DetailView&record='.$row['booking_id'].'" target="_blank" id="ct_contact_name'.$i.'">'.$row['contact_name'].'</a></td>';
			
			$html .= '<td align="center"><input '.$approved_readonly.' type="text" style="width:100%; '.$approved_readonly_bg.'" maxlength="255"  class="box-input" name="ct_desc[]" id="ct_desc'.$i.'" value="'.$row['description'].'" /></td>';
			
			$html .= '<td align="right"><label id="ct_total_amount'.$i.'">'.format_number($row['total_amount']).'</label></td>';
			
			$html .= '<td align="right"><label id="ct_total_purchase'.$i.'">'.format_number($row['total_purchase']).'</label></td>';
			
			$html .= '<td align="right"><label id="ct_total_profit'.$i.'">'.format_number($row['total_profit']).'</label></td>';
			
			$html .= '<td align="center">'.date($date_time_format, strtotime($row['date_entered'])).'</td>';
			
			$html .= '<td align="center">
						<div class="d-flex align-items-center gap-1">
							<input '.$approved_readonly.' class="ac_user box-input text-start" tbl="users" fld=\'{"id":"ct_assigned_user_id'.$i.'", "name":"ct_assigned_user'.$i.'"}\' style="'.$approved_readonly_bg.'" ln="'.$i.'" type="text" name="ct_assigned_user[]" id="ct_assigned_user'.$i.'" value="'.$row['assigned_user'].'" maxlength="255" autocomplete="off" />
							<input type="hidden" name="ct_assigned_user_id[]" id="ct_assigned_user_id'.$i.'" value="'.$row['assigned_user_id'].'" />
							<button '.$approved_disabled.' title="Tìm" class="button-search-in-edit" type="button" onclick="openAssignUserPopup('.$i.')">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M10 18a7.952 7.952 0 0 0 4.897-1.688l4.396 4.396 1.414-1.414-4.396-4.396A7.952 7.952 0 0 0 18 10c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8zm0-14c3.309 0 6 2.691 6 6s-2.691 6-6 6-6-2.691-6-6 2.691-6 6-6z"></path><path d="M11.412 8.586c.379.38.588.882.588 1.414h2a3.977 3.977 0 0 0-1.174-2.828c-1.514-1.512-4.139-1.512-5.652 0l1.412 1.416c.76-.758 2.07-.756 2.826-.002z"></path></svg>
							</button>
						</div>
					</td>';
			
			$html .= '<td align="center">
						<input '.$readonly.' type="text" maxlength="25" name="ct_bonus[]" id="ct_bonus'.$i.'" value="'.format_number($row['bonus']).'" class="box-input text-end" style="'.$readonly_bg.'" />
					</td>';
			
			$html .= '<td align="center">
						<input '.$disabled.' type="checkbox" name="ct_chk_is_approved[]" id="ct_chk_is_approved'.$i.'" '.($row['is_approved'] ? 'checked="checked"' : '').' />
						<input type="hidden" name="ct_is_approved[]" id="ct_is_approved'.$i.'" value="'.$row['is_approved'].'" />
					</td>';
			
			$html .= '<td align="left">
						<div class="d-flex align-items-center gap-1">
							<input '.$readonly.' class="ac_user box-input text-start" tbl="users" fld=\'{"id":"ct_approved_by_id'.$i.'", "name":"ct_approved_by'.$i.'"}\' style="'.$readonly_bg.'" ln="'.$i.'" type="text" name="ct_approved_by[]" id="ct_approved_by'.$i.'" value="'.$row['approved_by'].'" maxlength="255" autocomplete="off" />
							<input type="hidden" name="ct_approved_by_id[]" id="ct_approved_by_id'.$i.'" value="'.$row['approved_by_id'].'" />
							<button '.$disabled.' title="Tìm" type="button" class="button-search-in-edit" onclick="openApprovedByPopup('.$i.')">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M10 18a7.952 7.952 0 0 0 4.897-1.688l4.396 4.396 1.414-1.414-4.396-4.396A7.952 7.952 0 0 0 18 10c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8zm0-14c3.309 0 6 2.691 6 6s-2.691 6-6 6-6-2.691-6-6 2.691-6 6-6z"></path><path d="M11.412 8.586c.379.38.588.882.588 1.414h2a3.977 3.977 0 0 0-1.174-2.828c-1.514-1.512-4.139-1.512-5.652 0l1.412 1.416c.76-.758 2.07-.756 2.826-.002z"></path></svg>
							</button>
						</div>
					</td>';
			
			$html .= '<td align="center">
						<input '.$readonly.' class="box-input" type="text" style="'.$readonly_bg.'" maxlength="255" name="ct_approved_note[]" id="ct_approved_note'.$i.'" value="'.$row['approved_note'].'" />
					</td>';
			
			$html .= '<td align="center">
						<button '.$approved_disabled.' title="Xóa" class="button-remove-in-edit" type="button" onclick="markRowDeleted('.$i.')">
							<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
						</button>
						<input type="hidden" value="0" name="ct_deleted[]" id="ct_deleted'.$i.'" />
						<input type="hidden" name="ct_detail_id[]" id="ct_detail_id'.$i.'" value="'.$row['detail_id'].'" />
					</td>';
			
			$html .= '</tr>';
			
			$i++;
		}

		$sep = my_get_number_separators();
		$smartyobj->assign('GRP_SEPERATOR', $sep[0]);
		$smartyobj->assign('DEC_SEPERATOR', $sep[1]);
		$smartyobj->assign('SIG_DIGITS', $locale->getPrecision());
		
		$smartyobj->assign('FROM_DATE_VALUE', $from_date_value);
		$smartyobj->assign('TO_DATE_VALUE', $to_date_value);
		$smartyobj->assign('DATA', $html);
		$smartyobj->assign('ROW_COUNT', $i);
		$smartyobj->assign('CURRENT_USER', $current_user->full_name);
		$smartyobj->assign('CURRENT_USER_ID', $current_user->id);
		$smartyobj->assign('IS_ADMIN', is_admin($current_user) ? 1 : 0);
		
		if(isset($_POST['btnSaveBonus'])) {
			$row_count = count($_POST['ct_assigned_user_id']);

			for($i = 0; $i < $row_count; $i++){
                if(!empty($_POST['ct_assigned_user_id'][$i]) && !empty($_POST['ct_assigned_user'][$i]) && !empty($_POST['ct_bonus'][$i])) {
					$wp 				= new EC_Working_Process();
                    $wp->id 			= trim(stripslashes($_POST['ct_detail_id'][$i]));
                    $wp->name 			= trim(stripslashes(!empty($_POST['ct_booking'][$i]) ? $_POST['ct_booking'][$i] : $_POST['ct_assigned_user'][$i]));
                    $wp->parent_type 	= 'EC_Flight_Bookings';
                    $wp->parent_id 		= trim(stripslashes($_POST['ct_booking_id'][$i]));
                    $wp->description 		= trim(stripslashes($_POST['ct_desc'][$i]));
                    $wp->assigned_user_id 	= trim(stripslashes($_POST['ct_assigned_user_id'][$i]));

                    if (is_admin($current_user)) {
                        $wp->bonus 			= unformat_number($_POST['ct_bonus'][$i]);
                        $wp->is_approved 	= (int)$_POST['ct_is_approved'][$i];
                        $wp->approved_by_id = trim(stripslashes($_POST['ct_approved_by_id'][$i]));
                        $wp->approved_note 	= trim(stripslashes($_POST['ct_approved_note'][$i]));
                    } else {
                        $wp->bonus 			= 0;
                        $wp->is_approved 	= 0;
                        $wp->approved_by_id = NULL;
                        $wp->approved_note	= NULL;
                    }

                    $check_is_approved 	= $this->checkBonusIsApproved($wp->id);
                    $wp->deleted		= $_POST['ct_deleted'][$i];

                    if ($wp->deleted == 1) {
                        if (is_admin($current_user) || $check_is_approved == 0)
                            $wp->mark_deleted($wp->id);
                    }
					else {
                        if ($wp->name != '' && $wp->assigned_user_id != '' && (is_admin($current_user) || $check_is_approved == 0))
                            $wp->save();
                    }
                }
			}
			header('Location: index.php?module=EC_TongHop&action=addbonus&from_date='.$from_date_value.'&to_date='.$to_date_value);
			exit;
		}
	}
	
	function checkBonusIsApproved($detail_id) {
		global $db;
		if(empty($detail_id)) return 0;
		$sql = "SELECT is_approved
				FROM ec_working_process
				WHERE id = '".$detail_id."' AND	deleted = 0";
		$is_approved = $db->getOne($sql);
		return $is_approved;
	}
	
	/*function checkBonusExist($parent_id, $parent_type, $assigned_user_id){
		global $db;
		$total = 0;
		$sql = "SELECT COUNT(id)
				FROM ec_working_process
				WHERE deleted = 0
				AND assigned_user_id = '".$assigned_user_id."'
				AND parent_id = '".$parent_id."'
				AND parent_type = '".$parent_type."'
				AND bonus IS NOT NULL ";
		$total += $db->getOne($sql);
		return $total > 0 ? true : false;
	}*/
}	
