<?php

class EC_CashFlow extends Basic
{
    public $new_schema = true;
    public $module_dir = 'EC_CashFlow';
    public $object_name = 'EC_CashFlow';
    public $table_name = 'ec_cashflow';
    public $importable = false;

    public $id;
    public $name;
    public $date_entered;
    public $date_modified;
    public $modified_user_id;
    public $modified_by_name;
    public $created_by;
    public $created_by_name;
    public $description;
    public $deleted;
    public $created_by_link;
    public $modified_user_link;
    public $assigned_user_id;
    public $assigned_user_name;
    public $assigned_user_link;
    public $SecurityGroups;
	
    public function bean_implements($interface)
    {
        switch($interface)
        {
            case 'ACL':
                return true;
        }

        return false;
    }

    function handle() {
		global $db;

		// Permission
		$dep_arr = SecurityGroup::getAllSecurityGroups();
		if(is_array($dep_arr)){
			$dep_arr = array_values($dep_arr);
		}
		$dep_id = $dep_arr[0]['id'];
		// End permission

		$report_year = date('Y');
		for ($i = 2; $i >= 0; $i--) {
			$arr = array();
			$from_date 	= date('Y-01-01');
			$to_date 	= date('Y-m-d', strtotime("-".$i." day"));
			$sql_search = " AND DATE(p.ngayhachtoan) >= '".date('Y-01-01')."' ";
			$sql_search .= " AND DATE(p.ngayhachtoan) <= '".$to_date."' ";

			// TK ngân hàng
			$bank_account_list = $this->getBankAccountList($report_year, $from_date, $to_date, $sql_search, $dep_id, 0);
			foreach($bank_account_list['data'] as $key => $value) {
				foreach($value as $element) {
					$arr[] = array(
						'account_number' 	=> $element['account_number'],
						'account_name' 		=> $element['account_name'],
						'amount' 			=> $element['amount']
					);
				}
			}

			// Nhà cung cấp 
			$supplier_list = $this->getSupplierTotalDebt($report_year, $from_date, $to_date);
			foreach($supplier_list as $value) {
				foreach($value as $key => $element) {
					$arr[] = array(
						'account_number' 	=> $key,
						'account_name' 		=> $element['name'],
						'amount' 			=> $element['amount']*(-1)
					);
				}
			}

			// Tiền mặt
			$total_cash = $this->getTotalCash($report_year, $sql_search, $dep_id);
			$arr[] = array(
				'account_number' 	=> 'CASH',
				'account_name' 		=> 'Tiền mặt',
				'amount' 			=> $total_cash
			);

			// Công nợ phải thu
			$total_debt = $this->getAgentList($report_year, '131', $from_date, $to_date);
			$arr[] = array(
				'account_number' 	=> 'RECEIVABLE',
				'account_name' 		=> 'Công nợ phải thu',
				'amount' 			=> $total_debt
			);

			// Tạm ứng
			$total_advance = $this->getAdvanceAmt($to_date);
			$arr[] = array(
				'account_number' 	=> 'ADVANCE',
				'account_name' 		=> 'Nhân viên tạm ứng',
				'amount' 			=> $total_advance
			);

			// Chưa xuất vé
			$not_exported = $this->getNotExportedTicket($to_date);
			$arr[] = array(
				'account_number' 	=> 'NOT_EXPORTED',
				'account_name' 		=> 'Chưa xuất vé',
				'amount' 			=> $not_exported
			);

			// Tiền nợ khách
			$refund_amt = $this->getReturnAmount($to_date);
			$arr[] = array(
				'account_number' 	=> 'REFUND',
				'account_name' 		=> 'Tiền nợ khách',
				'amount' 			=> $refund_amt
			);

			// Save or update
			$report_date = date('d-m-Y', strtotime($to_date));
			foreach($arr as $row) {
				if($i == 0) {
					$this->id = "";
					$this->report_date 		= $report_date;
					$this->account_number 	= $row['account_number'];
					$this->account_name 	= $row['account_name'];
					$this->amount 			= $row['amount'];
					$this->save();
				}
				else {
					$sql_exist = '
						SELECT IF(COUNT(id) > 0, id, 0)
						FROM ec_cashflow
						WHERE deleted = 0
							AND report_date = "' . $to_date . '" 
							AND account_number = "' . $row['account_number'] . '"';
					$id = $db->getOne($sql_exist);

					if ($id == "0") {
						$this->id = "";
						$this->report_date 		= $report_date;
						$this->account_number 	= $row['account_number'];
						$this->account_name 	= $row['account_name'];
						$this->amount 			= $row['amount'];
						$this->save();
					}
					else {
						$sql = 'UPDATE ec_cashflow 
								SET amount = '.$row['amount'].'
								WHERE id = "'.$id.'"';
						$db->query($sql);
					}
				}
			}
		}

		return 1;
	}

	function getBankAccountList($report_year, $post_from_date, $post_to_date, $sql_search, $dep_id){
		global $db;
		
		$sql = "SELECT SUM(IFNULL(tmp.thutien,0))-SUM(IFNULL(tmp.chitien,0)) AS sotien
					  ,tmp.tknganhang_id
					  ,tmp.tknganhang
					  ,tmp.sotaikhoan 
				FROM (
					-- OPENING AMOUNT
					SELECT p.id
						  ,(IFNULL(p.dunodau,0)-IFNULL(p.ducodau,0)) AS thutien
						  ,0 AS chitien
						  ,p.parent_id AS tknganhang_id
						  ,(SELECT t.name FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.parent_id LIMIT 1) AS tknganhang
						  ,(SELECT t.sotaikhoan FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.parent_id LIMIT 1) AS sotaikhoan
						  ,(SELECT t.ngungtheodoi FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.parent_id LIMIT 1) AS ngungtheodoi
						  ,(SELECT t.assigned_user_id FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.parent_id LIMIT 1) AS assigned_user_id
					FROM ec_chitiettaikhoan".$report_year." p
					WHERE p.deleted=0
					AND p.parent_type IN ('EC_TaiKhoanNganHang', 'EC_Bank_Account') 
					AND p.parent_id IS NOT NULL

					-- RECEIPT VOUCHER
					UNION
					SELECT p.id
						  ,p.amount_converted AS thutien
						  ,0 AS chitien
						  ,p.tknganhang_id
						  ,(SELECT t.name FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.tknganhang_id LIMIT 1) AS tknganhang
						  ,(SELECT t.sotaikhoan FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.tknganhang_id LIMIT 1) AS sotaikhoan
						  ,(SELECT t.ngungtheodoi FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.tknganhang_id LIMIT 1) AS ngungtheodoi
						  ,(SELECT t.assigned_user_id FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.tknganhang_id LIMIT 1) AS assigned_user_id
					FROM ec_receipt_voucher p
					WHERE p.deleted=0 
					AND p.receipt_type='credit_transfer' 
					AND p.amount IS NOT NULL 
					AND p.rv_status='1' 
					AND p.is_margin=0 ".$sql_search."
					
					-- PAYMENT VOUCHER
					UNION
					SELECT p.id
						  ,0 AS thutien
						  ,p.amount AS chitien
						  ,p.tknganhang_id
						  ,(SELECT t.name FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.tknganhang_id LIMIT 1) AS tknganhang
						  ,(SELECT t.sotaikhoan FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.tknganhang_id LIMIT 1) AS sotaikhoan
						  ,(SELECT t.ngungtheodoi FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.tknganhang_id LIMIT 1) AS ngungtheodoi
						  ,(SELECT t.assigned_user_id FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.tknganhang_id LIMIT 1) AS assigned_user_id
					FROM ec_payment_voucher p
					WHERE p.deleted=0 
					AND p.hinhthucchi='credit_transfer' 
					AND p.amount IS NOT NULL 
					AND p.pv_status='3' ".$sql_search."
					
					-- TRANSFER FROM
					UNION
					SELECT p.id
						  ,0 AS thutien
						  ,IFNULL(p.sotien,0) AS chitien
						  ,p.tutknganhang_id AS tknganhang_id
						  ,(SELECT t.name FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.tutknganhang_id LIMIT 1) AS tknganhang
						  ,(SELECT t.sotaikhoan FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.tutknganhang_id LIMIT 1) AS sotaikhoan
						  ,(SELECT t.ngungtheodoi FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.tutknganhang_id LIMIT 1) AS ngungtheodoi
						  ,(SELECT t.assigned_user_id FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.tutknganhang_id LIMIT 1) AS assigned_user_id
					FROM ec_chuyentiennoibo p
					WHERE p.deleted=0 
					AND p.ghiso=1 
					AND p.tutienmat=0 ".$sql_search."
					
					-- TRANSFER TO
					UNION
					SELECT p.id
						  ,IFNULL(p.sotien,0) AS thutien
						  ,0 AS chitien
						  ,p.dentknganhang_id AS tknganhang_id
						  ,(SELECT t.name FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.dentknganhang_id LIMIT 1) AS tknganhang
						  ,(SELECT t.sotaikhoan FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.dentknganhang_id LIMIT 1) AS sotaikhoan
						  ,(SELECT t.ngungtheodoi FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.dentknganhang_id LIMIT 1) AS ngungtheodoi
						  ,(SELECT t.assigned_user_id FROM ec_bank_account t WHERE t.deleted=0 AND t.id=p.dentknganhang_id LIMIT 1) AS assigned_user_id
					FROM ec_chuyentiennoibo p
					WHERE p.deleted=0 
					AND p.ghiso=1 
					AND p.dentienmat=0 ".$sql_search."
						
				) AS tmp
				WHERE tmp.ngungtheodoi=0
				-- AND u.department_id='".$dep_id. "'
				AND tmp.tknganhang_id NOT IN (
					'a56c866c-8476-949d-96b5-60cb1818fc55'
				  , '2cd3be12-1ddf-6bc9-158e-5ff97562f721' 
				)
				GROUP BY tmp.tknganhang_id 
				ORDER BY CONVERT(tknganhang USING UTF8) COLLATE utf8_unicode_ci";
		$res = $db->query($sql);

		$total_amount = 0;
		$data = array('plus'=>array(), 'minus'=>array());
		while($row = $db->fetchByAssoc($res)){
			if($row['sotien'] > 0) {
				$data['plus'][] = array(
					'account_number' => $row['sotaikhoan'],
					'account_name' 	 => $row['tknganhang'],
					'amount' 		 => $row['sotien']
				);
				$total_amount += $row['sotien'];
			} 
			else {
				$data['minus'][] = array(
					'account_number' => $row['sotaikhoan'],
					'account_name' 	 => $row['tknganhang'],
					'amount' 		 => $row['sotien']
				);
			}
		}

		return array('total_amount' => $total_amount, 'data' => $data);
	}

	function getSupplierTotalDebt($opening_year, $post_fdate, $post_tdate) {
		global $db;
		$sql = "SELECT a.ticker_symbol AS supplier_code
					  ,a.name AS supplier_name
					  ,SUM(IFNULL(tmp.debt_amount, 0)) - SUM(IFNULL(tmp.pay_amount, 0)) AS total_debt
				FROM (
					-- START TERM
					SELECT p.id
						,p.parent_id AS supplier_id
						,SUM(IFNULL(p.dunodau, 0) - IFNULL(p.ducodau, 0)) AS debt_amount
						,0 AS pay_amount
						,p.sotaikhoan AS accounting_code
					FROM ec_chitiettaikhoan" . $opening_year . " p
					WHERE p.deleted = 0
					AND p.parent_type = 'Accounts'
					AND p.sotaikhoan IN ('144','331')
					AND p.parent_id IS NOT NULL
					GROUP BY p.parent_id

					-- BOOKING DETAILS
					UNION
					SELECT d.id
						,d.supplier_id
						,SUM(IFNULL(d.total_bought_price, 0)) AS debt_amount
						,0 AS pay_amount
						,'' AS accounting_code
					FROM ec_booking_details d
					LEFT JOIN ec_flight_bookings p ON d.booking_id = p.id AND p.deleted = 0
					WHERE d.deleted = 0
					AND p.booking_status IN ('7', '8')
					AND p.is_ticket_exported = 1
					AND p.date_ticket_issue >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
					AND p.date_ticket_issue <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
					AND d.total_bought_price > 0
					AND d.supplier_id IS NOT NULL
					GROUP BY d.supplier_id

					-- BOOKING PAXS OUTBOUND
					UNION
					SELECT CONCAT(d.id, '-OUTBOUND') AS id
						,d.supplier_id
						,SUM(IFNULL(d.luggage_purchase, 0)) AS debt_amount
						,0 AS pay_amount
						,'' AS accounting_code
					FROM ec_booking_passengers d
					LEFT JOIN ec_flight_bookings p ON d.booking_id = p.id AND p.deleted = 0
					WHERE d.deleted = 0
					AND p.booking_status IN ('7', '8')
					AND p.is_ticket_exported = 1
					AND p.date_ticket_issue >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
					AND p.date_ticket_issue <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
					AND d.luggage_price > 0
					AND d.luggage_purchase > 0
					AND d.supplier_id IS NOT NULL
					AND d.add_type IS NULL
					GROUP BY d.supplier_id

					-- BOOKING PAXS INBOUND
					UNION
					SELECT CONCAT(d.id, '-INBOUND') AS id
						,d.supplier_inbound_id AS supplier_id
						,SUM(IFNULL(d.luggage_purchase_inbound, 0)) AS debt_amount
						,0 AS pay_amount
						,'' AS accounting_code
					FROM ec_booking_passengers d
					LEFT JOIN ec_flight_bookings p ON d.booking_id = p.id AND p.deleted = 0
					WHERE d.deleted = 0
					AND p.booking_status IN ('7', '8')
					AND p.is_ticket_exported = 1
					AND p.date_ticket_issue >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
					AND p.date_ticket_issue <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
					AND d.luggage_price_inbound > 0
					AND d.luggage_purchase_inbound > 0
					AND d.supplier_inbound_id IS NOT NULL
					AND d.add_type IS NULL
					GROUP BY d.supplier_inbound_id

					-- SUPPLIER 1
					UNION
					SELECT CONCAT(p.id, '-SUPPLIER1') AS id
						,p.supplier_id
						,IFNULL(p.bought_amount, 0) AS debt_amount
						,0 AS pay_amount
						,'' AS accounting_code
					FROM ec_receipt_voucher p
					WHERE p.deleted = 0
					AND p.loai_thu IN ('4', '5')
					AND p.supplier_id IS NOT NULL
					AND p.bought_amount IS NOT NULL
					AND DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
					AND DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
					AND p.supplier_id IS NOT NULL

					-- SUPPLIER 2
					UNION
					SELECT CONCAT(p.id, '-SUPPLIER2') AS id
						,p.supplier2_id AS supplier_id
						,IFNULL(p.bought_amount2, 0) AS debt_amount
						,0 AS pay_amount
						,'' AS accounting_code
					FROM ec_receipt_voucher p
					WHERE p.deleted = 0
					AND p.loai_thu IN ('4', '5')
					AND p.supplier2_id IS NOT NULL
					AND p.bought_amount2 IS NOT NULL
					AND DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
					AND DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
					AND p.supplier2_id IS NOT NULL

					-- SUPPLIER 3
					UNION
					SELECT CONCAT(p.id, '-SUPPLIER3') AS id
						,p.supplier3_id AS supplier_id
						,IFNULL(p.bought_amount3, 0) AS debt_amount
						,0 AS pay_amount
						,'' AS accounting_code
					FROM ec_receipt_voucher p
					WHERE p.deleted = 0
					AND p.loai_thu IN ('4', '5')
					AND p.supplier3_id IS NOT NULL
					AND p.bought_amount3 IS NOT NULL
					AND DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
					AND DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
					AND p.supplier3_id IS NOT NULL

					-- TICKET REFUND
					UNION
					SELECT p.id
						,c.nhacc_id AS supplier_id
						,-SUM(IFNULL(c.sotienhang, 0)) AS debt_amount
						,0 AS pay_amount
						,'' AS accounting_code
					FROM ec_chitiethoanve c
					LEFT JOIN ec_hoanve p ON c.hoanve_id = p.id AND p.deleted = 0
					WHERE c.deleted = 0
					AND c.dahoan = 1
					AND p.tinhtrang = '1'
					AND p.ngayhachtoan >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
					AND p.ngayhachtoan <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
					AND c.sotienhang > 0
					AND c.nhacc_id IS NOT NULL
					GROUP BY c.nhacc_id

					-- PAYMENT VOUCHER
					UNION
					SELECT p.id
						,p.supplier_id
						,0 AS debt_amount
						,IFNULL(p.amount, 0) AS pay_amount
						,'' AS accounting_code
					FROM ec_payment_voucher p
					WHERE p.deleted = 0
					AND p.pv_status = '3'
					AND DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
					AND DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
					AND p.supplier_id IS NOT NULL
				) AS tmp
				LEFT JOIN accounts a ON tmp.supplier_id = a.id AND a.deleted = 0
				WHERE a.is_stop_tracking = 0
				AND a.account_type = 'Supplier'
				AND a.is_reported = 1
				GROUP BY tmp.supplier_id
				-- HAVING total_debt <> 0
				ORDER BY supplier_code";

		$res = $db->query($sql);
		$total_BBA = $total_VJA = $total_VU = $total_JPA = 0;
		$total_debt = array();
		while($row = $db->fetchByAssoc($res)) {
			if((float)$row['total_debt'] <> 0) {
				if($row['supplier_code'] == 'BBATMTP' || $row['supplier_code'] == 'BBATPOLD') {
					unset($total_debt['plus']['BBA']);
					unset($total_debt['minus']['BBA']);
					$total_BBA += $row['total_debt'];
					if($total_BBA < 0) $type = 'minus';
					else $type = 'plus';
					$total_debt[$type]['BBA'] = array(
						'amount' => $total_BBA,
						'name' => 'Bamboo',
					); 
				}
				else if($row['supplier_code'] == 'VJATMTP1' || $row['supplier_code'] == 'VJTPOLD') {
					unset($total_debt['plus']['VJA']);
					unset($total_debt['minus']['VJA']);
					$total_VJA += $row['total_debt'];
					if($total_VJA < 0) $type = 'minus';
					else $type = 'plus';
					$total_debt[$type]['VJA'] = array(
						'amount' => $total_VJA,
						'name' => 'Vietjet',
					);
				}
				else if($row['supplier_code'] == 'JPA' || $row['supplier_code'] == 'JPATP') {
					unset($total_debt['plus']['JET']);
					unset($total_debt['minus']['JET']);
					$total_JPA += $row['total_debt'];
					if($total_JPA < 0) $type = 'minus';
					else $type = 'plus';
					$total_debt[$type]['JET'] = array(
						'amount' => $total_JPA,
						'name' => 'Jetstar',
					);
				} 
				else if ($row['supplier_code'] == 'VUTMTP' || $row['supplier_code'] == 'VUOLD') {
					unset($total_debt['plus']['VU']);
					unset($total_debt['minus']['VU']);
					$total_VU += $row['total_debt'];
					if ($total_VU < 0) $type = 'minus';
					else $type = 'plus';
					$total_debt[$type]['VU'] = array(
						'amount' => $total_VU,
						'name' => 'Vietravel',
					);
				}
	 			else {
					if((float)$row['total_debt'] < 0) $type = 'minus';
					else $type = 'plus';
					$total_debt[$type][$row['supplier_code']] = array(
						'amount' => (float)$row['total_debt'],
						'name' => $row['supplier_name'],
					);
	 			}
			}
		}

		return $total_debt;
	}

	function getTotalCash($report_year, $sql_search, $dep_id){
		global $db;
		$total_amount = 0;
		// Cả nam phương và tralvelpass
		$location_np = $this->getLocationByDep('8df43570-09de-d2b3-b2fd-506eca7522f7');
		$location_tp = $this->getLocationByDep('48840c01-3a4f-c430-f703-56f32c7cd8a4');
		$sql_search .= " AND p.com_location_id IN ('".implode("','", $location_np).'\',\''.implode("','", $location_tp)."') ";
		
		$sql = "SELECT SUM(IFNULL(tmp.thutien,0)) - SUM(IFNULL(tmp.chitien,0))
				FROM (
					-- OPENING AMOUNT
					SELECT p.id
						   ,(IFNULL(p.dunodau,0)-IFNULL(p.ducodau,0)) AS thutien
						   ,0 AS chitien
						   ,p.assigned_user_id
					FROM ec_chitiettaikhoan".$report_year." p
					WHERE p.deleted=0 
					AND SUBSTRING(p.sotaikhoan, 1, 4)='1111'
					AND p.location_id IN ('".implode("','", $location_np).'\',\''.implode("','", $location_tp)."')
					
					-- RECEIPT VOUCHER
					UNION
					 SELECT p.id
						   ,p.amount_converted AS thutien
						   ,0 AS chitien
						   ,p.assigned_user_id
					 FROM ec_receipt_voucher p
					 WHERE p.deleted=0 
					 AND p.receipt_type='cash' 
					 AND p.amount_converted IS NOT NULL 
					 AND p.rv_status='1' 
					 AND p.is_margin=0 ".$sql_search."
					 
					 -- PAYMENT VOUCHER
					 UNION
					 SELECT p.id
						   ,0 AS thutien
						   ,p.amount AS chitien
					 	   ,p.assigned_user_id
					 FROM ec_payment_voucher p
					 WHERE p.deleted=0 
					 AND p.hinhthucchi='cash' 
					 AND p.amount IS NOT NULL 
					 AND p.pv_status='3' ".$sql_search."
					 
					 -- TRANSFER FROM
					 UNION
					 SELECT p.id
						   ,0 AS thutien
						   ,p.sotien AS chitien
					 	   ,p.assigned_user_id
					 FROM ec_chuyentiennoibo p
					 WHERE p.deleted=0 
					 AND p.ghiso=1 
					 AND p.tutienmat=1 ".str_replace('p.com_location_id', 'p.tudiadiem_id', $sql_search)."
					 
					 -- TRANSFER TO
					 UNION
					 SELECT p.id
						   ,p.sotien AS thutien
						   ,0 AS chitien
					 	   ,p.assigned_user_id
					 FROM ec_chuyentiennoibo p
					 WHERE p.deleted=0 
					 AND p.ghiso=1 
					 AND p.dentienmat=1 ".str_replace('p.com_location_id', 'p.dendiadiem_id', $sql_search)."
				 
				 ) AS tmp ";
		
		$total_amount += $db->getOne($sql);
		return $total_amount;
	}

	function getAgentList($opening_year, $accounting_code, $post_fdate, $post_tdate) {
        global $db;

        $sql = "SELECT a.ticker_symbol AS agent_code
					  ,a.id AS agent_id
					  ,a.name AS agent_name
					  ,a.billing_address_street AS address
					  ,a.phone_office AS phone
					  ,SUM(IFNULL(tmp.debt_amount, 0)) - SUM(IFNULL(tmp.pay_amount, 0)) AS total_debt
				FROM (
					-- OPENING AMOUNT
					SELECT p.id
						,p.parent_id AS agent_id
						,SUM(IFNULL(p.dunodau, 0) - IFNULL(p.ducodau, 0)) AS debt_amount
						,0 AS pay_amount
					FROM ec_chitiettaikhoan" . $opening_year . " p
					WHERE p.deleted = 0
					AND p.sotaikhoan = '" . $accounting_code . "'
					AND p.parent_type = 'Accounts'
					GROUP BY p.parent_id

					-- BOOKING
					UNION
					SELECT p.id
						,p.agent_id
						,SUM(IFNULL(p.total_amount, 0)) AS debt_amount
						,0 AS pay_amount
					FROM ec_flight_bookings p
					WHERE p.deleted = 0
					AND p.booking_status IN ('7', '8')
					AND p.is_agent = 1
					AND p.date_ticket_issue >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
					AND p.date_ticket_issue <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
					GROUP BY p.agent_id

					-- PAYMENT VOUCHER
					UNION
					SELECT p.id
						,p.supplier_id AS agent_id
						,SUM(IFNULL(p.amount, 0)) AS debt_amount
						,0 AS pay_amount
					FROM ec_payment_voucher p
					LEFT JOIN ec_payment_types pt 
					ON p.ec_payment_types_id_c = pt.id AND pt.deleted = 0
					WHERE p.deleted = 0
					AND p.pv_status = '3'
					AND pt.is_receipt_debt = 1
					AND p.ngaychungtu >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
					AND p.ngaychungtu <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
					GROUP BY p.id

					-- RECEIPT VOUCHER
					UNION
					SELECT p.id
						,p.account_id_c AS agent_id
						,SUM(IF(p.is_debt = 1 AND p.loai_thu IN (4, 5), IFNULL(p.amount_converted, 0), 0)) AS debt_amount
						,SUM(IF(p.rv_status = 1 AND p.is_debt = 0, IFNULL(p.amount_converted, 0), 0)) AS pay_amount
					FROM ec_receipt_voucher p
					WHERE p.deleted = 0
					AND p.rv_status IN (1, 2)
					AND p.account_id_c IS NOT NULL
					AND p.ngaychungtu >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
					AND p.ngaychungtu <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
					GROUP BY p.id

					-- RECEIPT VOUCHER FOR DEBT
					UNION
					SELECT p.id
						,p.account_id_c AS agent_id
						,0 AS debt_amount
						,SUM(IFNULL(p.amount_converted, 0)) AS pay_amount
					FROM ec_receipt_voucher p
					WHERE p.deleted = 0
					AND p.rv_status = 1 AND p.is_debt = 1
					AND p.account_id_c IS NOT NULL
					AND DATE_FORMAT(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR), '%Y-%m-%d') >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
					AND DATE_FORMAT(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR), '%Y-%m-%d') <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
					GROUP BY p.id
				) AS tmp
				LEFT JOIN accounts a ON tmp.agent_id = a.id AND a.deleted = 0
				WHERE a.is_stop_tracking = 0
				AND a.account_type IS NOT NULL
				AND a.account_type <> 'Supplier'
				GROUP BY tmp.agent_id
				-- HAVING total_debt <> 0
				ORDER BY agent_code ";

        $res = $db->query($sql);
        $total_debt = 0;
        while ($row = $db->fetchByAssoc($res)) {
            $total_debt += $row['total_debt'];
        }

        return $total_debt;
    }

	function getAdvanceAmt($to_date) {
		global $db;
		$from_date = '2021-01-01';
		$to_date = date('Y-m-d', strtotime($to_date));
		$sql = '
			SELECT SUM(t.tam_ung - t.hoan_ung)
			FROM (
				-- sodauky
				SELECT u.id AS employee_id
						, CONCAT(IFNULL(u.last_name, ""), " ", IFNULL(u.first_name, "")) AS full_name
						, SUM(IFNULL(sdk.dunodau, 0)) AS tam_ung
						, SUM(IFNULL(sdk.ducodau, 0)) AS hoan_ung
						, "SDK" AS parent_type
						, "" AS ngayhachtoan
				FROM ec_chitiettaikhoan sdk
				INNER JOIN users u ON u.id = sdk.parent_id
				WHERE sdk.deleted = 0 AND sdk.sotaikhoan = "141"
				AND sdk.parent_type = "Users"
				AND sdk.date_entered >= "' . $from_date . '" 
				AND sdk.date_entered <= "' . $to_date . '"
				GROUP BY u.id
				
				-- tam ung tu phieu chi
				UNION
				SELECT u.id AS employee_id
						, CONCAT(IFNULL(u.last_name, ""), " ", IFNULL(u.first_name, "")) AS full_name
						, SUM(t.amount) AS tam_ung
						, 0 AS hoan_ung 
						, "" AS parent_type
						, t.ngayhachtoan
				FROM ec_payment_voucher t
				INNER JOIN users u ON u.id = t.employee_id
				WHERE t.deleted = 0 AND t.pv_status = 3
				AND t.ngayhachtoan >= "' . $from_date . '" 
				AND t.ngayhachtoan <= "' . $to_date . '"
				GROUP BY u.id
				
				-- hoan ung tru luong
				UNION
				SELECT u.id AS employee_id
						, CONCAT(IFNULL(u.last_name, ""), " ", IFNULL(u.first_name, "")) AS full_name
						, 0 AS tam_ung
						, SUM(hu.minus_amount) AS hoan_ung
						, "" AS parent_type 
						, hu.voucher_date AS ngayhachtoan
				FROM ec_salary_details hu
				INNER JOIN users u ON u.id = hu.assigned_user_id
				WHERE hu.deleted = 0 AND hu.type = "minus"
				AND hu.reason = "TamUng" 
				AND hu.voucher_date >= "' . $from_date . '" 
				AND hu.voucher_date <= "' . $to_date . '"
				GROUP BY u.id

				-- hoan ung phieu thu
				UNION
				SELECT u.id AS employee_id
						, CONCAT(IFNULL(u.last_name, ""), " ", IFNULL(u.first_name, "")) AS full_name
						, 0 AS tam_ung
						, SUM(r.amount) AS hoan_ung
						, "" AS parent_type
						, r.ngayhachtoan
				FROM ec_receipt_voucher r
				INNER JOIN users u ON u.id = r.employee_id
				WHERE r.deleted = 0 AND r.rv_status = 1
				AND r.loai_thu = 9
				AND r.ngayhachtoan >= "' . $from_date . '" 
				AND r.ngayhachtoan <= "' . $to_date . '"
				GROUP BY u.id	 			
			) AS t
			LEFT JOIN users u ON u.id = t.employee_id';
		return $db->getOne($sql);
	}

	function getNotExportedTicket($to_date) {
    	global $db;
    	$sql = 'SELECT
					 SUM(d.total_bought_price) AS total_not_exported
				FROM
					ec_booking_details d
					LEFT JOIN ec_flight_bookings b ON b.id = d.booking_id 
					AND b.deleted = 0 
				WHERE
					d.deleted = 0 
					AND b.booking_status = "3"  
					AND b.is_paid = 1
					AND b.date_entered >= "'.date('Y').'-01-01"
				ORDER BY b.date_entered DESC';
    	$res = $db->query($sql);
    	$row = $db->fetchByAssoc($res);
    	return $row['total_not_exported'];
    }

	// Tiền hoàn vé nhưng chưa trả khách
	function getReturnAmount($to_date) {
		global $db;
		$from_date = '2021-01-01';
		$to_date = date('Y-m-d', strtotime($to_date));

		$sql = '
			SELECT (
				SUM(hv.tongtienkhach) 
              - SUM((
                    SELECT SUM(IFNULL(p.amount,0))
				    FROM ec_payment_voucher p
				    WHERE p.deleted = 0 
				    AND p.pv_status = 3
				    AND p.hoanve_id = hv.id
                ))
			) AS return_amt
            FROM ec_hoanve hv
            INNER JOIN ec_flight_bookings bk 
            ON bk.id = hv.booking_id
            AND bk.deleted = 0
            WHERE hv.deleted = 0
			AND hv.tinhtrang = 1
			AND DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR))>="'.$from_date. '"
			AND DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR))<="'.$to_date.'"';
		return $db->getOne($sql);
	}

	function getLocationByDep($dep_id) {
		global $db;
		$arr = array();

		$sql = "SELECT id
				FROM ec_location
				WHERE deleted = 0
				AND company_id = '".$dep_id."' ";

		$res = $db->query($sql);
		while($row = $db->fetchByAssoc($res)){
			$arr[] = $row['id'];
		}

		return $arr;
	}
	
}
