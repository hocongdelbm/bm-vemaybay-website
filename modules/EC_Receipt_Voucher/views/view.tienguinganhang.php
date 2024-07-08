<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");
		
class Viewtienguinganhang extends SugarView {
	function display() {
		$smartyCont= new Sugar_Smarty();
		$this->populateCont($smartyCont);
		$smartyCont->display('modules/EC_Receipt_Voucher/tpls/tienguinganhang.tpl');
	}
	
	function populateCont($smartyobj){
		global $app_list_strings, $db, $current_user;
		
		$sql_search = "";
		// Từ ngày
		if(isset($_POST['tungay']) && !empty($_POST['tungay'])){
			$sql_search .= " AND DATE(p.ngayhachtoan) >= '".date('Y-m-d', strtotime($_POST['tungay']))."' ";
			$post_tungay = $_POST['tungay'];
		} else {
			$sql_search .= " AND DATE(p.ngayhachtoan) >= '".date('Y-m-d')."' ";
			$post_tungay = date('d-m-Y');
		}
		
		// Đến ngày
		if(isset($_POST['denngay']) && !empty($_POST['denngay'])){
			$sql_search .= " AND DATE(p.ngayhachtoan) <= '".date('Y-m-d',strtotime($_POST['denngay']))."' ";
			$post_denngay = $_POST['denngay'];
		} else {
			$sql_search .= " AND DATE(p.ngayhachtoan) <= '".date('Y-m-d')."' ";
			$post_denngay = date('d-m-Y');
		}
		
		// Xử lý hiển thị
		$tknganhang_arr_cnt = isset($_POST['tknganhang_id']) ? count($_POST['tknganhang_id']) : 0;
		$html = '';
		$xls = "<html xmlns:o=\"urn:schemas-microsoft-com:office:office\"
					xmlns:x=\"urn:schemas-microsoft-com:office:excel\"
					xmlns=\"http://www.w3.org/TR/REC-html40\">
						
					<head>
						<meta http-equiv=Content-Type content=\"text/html; charset=windows-1252\">
						<meta name=ProgId content=Excel.Sheet>
						<meta name=Generator content=\"Microsoft Excel 12\">
						<link rel=File-List href=\"eticket_pnr_export_files/filelist.xml\">
						<style id=\"eticket_pnr_export_15117_Styles\">
						<!--table
							{mso-displayed-decimal-separator:\"\.\";
							mso-displayed-thousand-separator:\"\,\";}
						.xl6315117
							{padding-top:1px;
							padding-right:1px;
							padding-left:1px;
							mso-ignore:padding;
							color:black;
							font-size:10.0pt;
							font-weight:700;
							font-style:normal;
							text-decoration:none;
							font-family:Arial, sans-serif;
							mso-font-charset:0;
							mso-number-format:General;
							text-align:center;
							vertical-align:middle;
							mso-background-source:auto;
							mso-pattern:auto;
							white-space:nowrap;}
						.xl6415117
							{padding-top:1px;
							padding-right:1px;
							padding-left:1px;
							mso-ignore:padding;
							color:black;
							font-size:10.0pt;
							font-weight:400;
							font-style:normal;
							text-decoration:none;
							font-family:Arial, sans-serif;
							mso-font-charset:0;
							mso-number-format:General;
							text-align:general;
							vertical-align:middle;
							mso-background-source:auto;
							mso-pattern:auto;
							white-space:nowrap;}
						.xl6515117
							{padding-top:1px;
							padding-right:1px;
							padding-left:1px;
							mso-ignore:padding;
							color:black;
							font-size:10.0pt;
							font-weight:700;
							font-style:normal;
							text-decoration:none;
							font-family:Arial, sans-serif;
							mso-font-charset:0;
							mso-number-format:\"\@\";
							text-align:center;
							vertical-align:middle;
							mso-background-source:auto;
							mso-pattern:auto;
							white-space:nowrap;}
						.xl6615117
							{padding-top:1px;
							padding-right:1px;
							padding-left:1px;
							mso-ignore:padding;
							color:black;
							font-size:10.0pt;
							font-weight:400;
							font-style:normal;
							text-decoration:none;
							font-family:Arial, sans-serif;
							mso-font-charset:0;
							mso-number-format:\"\@\";
							text-align:general;
							vertical-align:middle;
							mso-background-source:auto;
							mso-pattern:auto;
							white-space:nowrap;}
						.xl6715117
							{padding-top:1px;
							padding-right:1px;
							padding-left:1px;
							mso-ignore:padding;
							color:black;
							font-size:10.0pt;
							font-weight:400;
							font-style:normal;
							text-decoration:none;
							font-family:Arial, sans-serif;
							mso-font-charset:0;
							mso-number-format:General;
							text-align:center;
							vertical-align:middle;
							mso-background-source:auto;
							mso-pattern:auto;
							white-space:nowrap;}
						.xl6815117
							{padding-top:1px;
							padding-right:1px;
							padding-left:1px;
							mso-ignore:padding;
							color:black;
							font-size:12.0pt;
							font-weight:700;
							font-style:normal;
							text-decoration:none;
							font-family:Arial, sans-serif;
							mso-font-charset:0;
							mso-number-format:General;
							text-align:center;
							vertical-align:middle;
							mso-background-source:auto;
							mso-pattern:auto;
							white-space:nowrap;}
						-->
						</style>
					</head>
						
					<body>
						<div id=\"eticket_pnr_export_15117\" align=center x:publishsource=\"Excel\">
						
						<table border=0 cellpadding=0 cellspacing=0 width=622 class=xl6415117 style='border-collapse:collapse;table-layout:fixed;width:468pt'>
							<col class=xl6415117 width=44 style='mso-width-source:userset;mso-width-alt:1609;width:33pt'>
						 	<col class=xl6615117 width=102 style='mso-width-source:userset;mso-width-alt:3730;width:77pt'>
						 	<col class=xl6615117 width=140 style='mso-width-source:userset;mso-width-alt:5120;width:105pt'>
						 	<col class=xl6615117 width=126 style='mso-width-source:userset;mso-width-alt:4608;width:95pt'>
						 	<col class=xl6615117 width=112 style='mso-width-source:userset;mso-width-alt:4096;width:84pt'>
						 	<col class=xl6615117 width=98 style='mso-width-source:userset;mso-width-alt:3584;width:74pt'>";		
		
		for($i = 0; $i < $tknganhang_arr_cnt; $i++) {
			// Xuất số vé
			if(isset($_POST['btnXuatSoVe'])){
				$xls .= "<tr height=21 style='height:15.75pt'>
						  	<td colspan=6 height=21 class=xl6815117 width=622 style='height:15.75pt;
						  		width:468pt'>".$_POST['tennh_'.$_POST['tknganhang_id'][$i]]."</td>
						</tr>
						<tr height=33 style='mso-height-source:userset;height:24.75pt'>
							<td height=33 class=xl6315117 style='height:24.75pt'>STT</td>
							<td class=xl6515117>BOOKING</td>
							<td class=xl6515117>ETICKET OUTBOUND</td>
							<td class=xl6515117>ETICKET INBOUND</td>
							<td class=xl6515117>PNR OUTBOUND</td>
							<td class=xl6515117>PNR INBOUND</td>
						</tr>";
				
				$sql = "SELECT bk.name AS booking,
							psg.eticket_outbound,
							psg.eticket_inbound,
							psg.pnr_outbound,
							psg.pnr_inbound
						FROM ec_booking_passengers psg
							LEFT JOIN ec_flight_bookings bk ON psg.booking_id=bk.id AND bk.deleted=0 
						WHERE psg.deleted=0 
							AND psg.booking_id IN (
								SELECT p.booking_id 
								FROM ec_receipt_voucher p 
								WHERE p.deleted = 0 
									AND p.receipt_type = 'credit_transfer' 
									AND p.amount IS NOT NULL
									AND p.rv_status = '1'
									AND p.tknganhang_id = '".$_POST['tknganhang_id'][$i]."' ".$sql_search.") ";
				
				$res = $db->query($sql);
				$stt = 1;
				while($row = $db->fetchByAssoc($res)){	
					$xls .= "<tr height=17 style='height:12.75pt'>
						<td height=17 class=xl6715117 style='height:12.75pt'>".$stt."</td>
						<td class=xl6615117>".$row['booking']."</td>
						<td class=xl6615117>".$row['eticket_outbound']."</td>
						<td class=xl6615117>".$row['eticket_inbound']."</td>
						<td class=xl6615117>".$row['pnr_outbound']."</td>
						<td class=xl6615117>".$row['pnr_inbound']."</td>
					</tr>";
					$stt++;
				}
			}
			
			$voucher_arr = $this->getVoucherList('1121', $_POST['tknganhang_id'][$i], $post_tungay, $sql_search);

            // if ($current_user->user_name == 'nponline' && $post_tungay == '01-01-2019' && $post_denngay == '31-12-2019' && !empty($voucher_arr['tongton'])) {
            //     $cttk = new EC_ChiTietTaiKhoan();
            //     $cttk->id = '';
            //     $cttk->name = $_POST['tennh_' . $_POST['tknganhang_id'][$i]];
            //     $cttk->sotaikhoan = '1121';
            //     $cttk->dunodau = $voucher_arr['tongton'];
            //     $cttk->parent_type = 'EC_TaiKhoanNganHang';
            //     $cttk->parent_id = $_POST['tknganhang_id'][$i];
            //     //$cttk->company_id = '48840c01-3a4f-c430-f703-56f32c7cd8a4';
            //     //$cttk->location_id = $location_id;
            //     $cttk->save();
            // }
			
			$html .= '<table id="table-wrapper" cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr>
					<td valign="top">
						<p>
						'.$app_list_strings['company_info_list']['name'].'<br />
						'.$app_list_strings['company_info_list']['address'].'<br />
						Mã số thuế: '.$app_list_strings['company_info_list']['taxcode'].'
						</p>
					</td>
					<td valign="top" align="center">
						<p>
						<label style="font-weight:bold">Mẫu số S08-DN</label><br />
						<label style="font-style:italic">(Ban hành theo QĐ số: 15/2006/QĐ-BTC ngày<br /> 20/03/2006 của Bộ trưởng BTC)</label>
						</p>
					</td>
				</tr>
			  
				<tr>
					<td colspan="2" align="center">
					<br />
						<label style="font-weight:bold; font-size:15pt">TIỀN GỬI NGÂN HÀNG</label><br />
						<label style="font-weight:bold; font-style:italic">Từ ngày '.$post_tungay.' đến ngày '.$post_denngay.'</label>
					<br />
					<br />
					</td>
				</tr>
			  
				<tr>
					<td colspan="2" align="left">
						<label style="font-weight:bold;">Tài khoản: 1121</label><br />
						<label style="font-weight:bold;">Số tài khoản: '.$_POST['sotk_'.$_POST['tknganhang_id'][$i]].'</label><br />
						<label style="font-weight:bold;">Tên ngân hàng: '.$_POST['tennh_'.$_POST['tknganhang_id'][$i]].'</label><br />
						<label style="font-weight:bold;">Địa chỉ nơi mở: '.$_POST['diachi_'.$_POST['tknganhang_id'][$i]].'</label>
						<br />
						<br />
					</td>
				</tr>
			  
				<tr>
					<td colspan="2">
						<table id="table-details" width="100%" border="0" cellspacing="0" cellpadding="0">
							<tr>
							<td rowspan="2"><div align="center"><strong>Ngày, tháng<br /> ghi sổ</strong></div></td>
							<td rowspan="2"><div align="center"><strong>Ngày, tháng<br /> chứng từ</strong></div></td>
							<td colspan="2"><div align="center"><strong>Số hiệu chứng từ</strong></div></td>
							<td rowspan="2"><div align="center"><strong>Diễn giải</strong></div><div align="center"></div></td>
							<td colspan="3"><div align="center"><strong>Số tiền</strong></div></td>
						</tr>
							<tr>
							<td><div align="center"><strong>Thu</strong></div></td>
							<td><div align="center"><strong>Chi</strong></div></td>
							<td><div align="center"><strong>Thu</strong></div></td>
							<td><div align="center"><strong>Chi</strong></div></td>
							<td><div align="center"><strong>Tồn</strong></div></td>
						</tr>
						<tr>
							<td width="11%"><div align="center"><strong>A</strong></div></td>
							<td width="11%"><div align="center"><strong>B</strong></div></td>
							<td width="12%"><div align="center"><strong>C</strong></div></td>
							<td width="12%"><div align="center"><strong>D</strong></div></td>
							<td width="24%"><div align="center"><strong>E</strong></div></td>
							<td width="10%"><div align="center"><strong>1</strong></div></td>
							<td width="10%"><div align="center"><strong>2</strong></div></td>
							<td width="10%"><div align="center"><strong>3</strong></div></td>
						</tr>
						'.$voucher_arr['html'].'
						<tr>
							<td colspan="5" ><label style="font-weight:bold">Tổng cộng:</label></td>
							<td align="right" ><label style="font-weight:bold">'.format_number($voucher_arr['tongthu']).'</label></td>
							<td align="right" ><label style="font-weight:bold">'.format_number($voucher_arr['tongchi']).'</label></td>
							<td align="right" ><label style="font-weight:bold">'.format_number($voucher_arr['tongton']).'</label></td>
						</tr>
					</table>
					</td>
				</tr>
			  
				<tr>
					<td colspan="2">
						<br />
						<table id="table-signed" width="100%" border="0" cellspacing="0" cellpadding="0">
							<tr>
								<td width="30%" align="center">&nbsp;</td>
								<td width="30%" align="center">&nbsp;</td>
								<td width="40%" align="center"><label style="font-style:italic">Ngày '.date('d').' tháng '.date('m').' năm '.date('Y').'</label></td>
							</tr>
							<tr>
								<td width="30%" align="center"><label style="font-weight:bold">Thủ Quỹ</label><br />
								<label style="font-style:italic">(Ký, họ tên)</label></td>
								<td width="30%" align="center"><label style="font-weight:bold">Kế toán trưởng</label><br />
								<label style="font-style:italic">(Ký, họ tên)</label></td>
								<td width="40%" align="center">
								<label style="font-weight:bold">Giám đốc</label><br />
								<label style="font-style:italic">(Ký, họ tên, đóng dấu)</label></td>
							</tr>
						</table>
					</td>
				</tr>
		  	</table>';
		}
		
		$xls .= "<![if supportMisalignedColumns]>
					<tr height=0 style='display:none'>
						<td width=44 style='width:33pt'></td>
						<td width=102 style='width:77pt'></td>
						<td width=140 style='width:105pt'></td>
						<td width=126 style='width:95pt'></td>
						<td width=112 style='width:84pt'></td>
						<td width=98 style='width:74pt'></td>
					</tr>
				<![endif]>
			</table>		
		</div></body></html>";
		
		// Xuất CSV
		if(isset($_POST['btnXuatSoVe'])){
			ob_clean();
			$xls = chr(255).chr(254).mb_convert_encoding($xls, "UTF-16LE", "UTF-8");
			header("Content-type: application/x-msdownload");
			header("Content-disposition: xls; filename=eticket_pnr_export.xls; size=".strlen($xls));
			echo $xls;
			exit();
		}
		
		$smartyobj->assign('POST_TUNGAY', $post_tungay);
		$smartyobj->assign('POST_DENNGAY', $post_denngay);
		$smartyobj->assign('BANK_ACCOUNT_LIST', $this->getBankAccountList());
		$smartyobj->assign('DATA', $html);
	}
	
	// Lấy danh sách các tài khoản ngân hàng
	function getBankAccountList() {
		global $db;
		$sql = "SELECT ba.id, ba.account_number, ba.name, ba.branch
				FROM ec_bank_account ba
				WHERE ba.deleted = 0 AND ba.unfollow = 0 
				ORDER BY CONVERT(ba.name USING UTF8) COLLATE utf8_unicode_ci";

		$res = $db->query($sql);
		$html = '';
		$i = 0;
		while($row = $db->fetchByAssoc($res)){
			$html .= '<tr>
				<td align="center">
					<input type="checkbox" name="tknganhang_id[]" id="tknganhang_id'.$i.'" value="'.$row['id'].'" />
					<input type="hidden" name="tennh_'.$row['id'].'" value="'.$row['name'].'" />
					<input type="hidden" name="sotk_'.$row['id'].'" value="'.$row['account_number'].'" />
					<input type="hidden" name="diachi_'.$row['id'].'" value="'.$row['branch'].'" />
				</td>
				<td align="left">'.$row['account_number'].'</td>
				<td align="left">'.$row['name'].'</td>
			</tr>';
			$i++;
		}
		return $html;
	}
	
	// Lấy số tồn đầu kỳ của tài khoản tiền mặt
	function getTheOpeningAccount($sotk, $tknganhang_id, $post_tungay){
		global $db, $current_user;
		$sodauky = 0;
		$year = date('Y', strtotime($post_tungay));
		
		$sql_add = " 
			AND DATE(p.ngayhachtoan) >= '".date('Y-01-01', strtotime($post_tungay))."' 
			AND DATE(p.ngayhachtoan) < '".date('Y-m-d', strtotime($post_tungay))."' 
		";
		
		$sql = "
		SELECT SUM(IFNULL(t.thutien,0))-SUM(IFNULL(t.chitien,0))
		FROM (
			SELECT  p.id AS parent_id,
				(IFNULL(p.dunodau,0)-IFNULL(p.ducodau,0)) AS thutien,
				0 AS chitien
			FROM ec_chitiettaikhoan".$year." p 
			WHERE p.deleted=0 
				AND p.sotaikhoan='".$sotk."' 
				AND p.parent_id='".$tknganhang_id."' 
				AND p.parent_type IN('EC_TaiKhoanNganHang', 'EC_Bank_Account')
			
			UNION

			SELECT p.id AS parent_id,
				p.amount_converted AS thutien,
				0 AS chitien
			FROM ec_receipt_voucher p 
			WHERE p.deleted=0 
				AND p.receipt_type='credit_transfer' 
				AND p.amount IS NOT NULL 
				AND p.rv_status='1' 
				AND p.is_margin=0 
				AND p.tknganhang_id='".$tknganhang_id."' ".$sql_add."
			
			UNION

			SELECT p.id AS parent_id,
				0 AS thutien,
				p.amount AS chitien
			FROM ec_payment_voucher p 
			WHERE p.deleted=0 
				AND p.hinhthucchi='credit_transfer' 
				AND p.amount IS NOT NULL
				AND p.pv_status='3' 
				AND p.tknganhang_id='".$tknganhang_id."' ".$sql_add." 
			
			UNION

			SELECT p.id AS parent_id,
				0 AS thutien,
				p.sotien AS chitien
			FROM ec_chuyentiennoibo p 
			WHERE p.deleted=0 
				AND p.ghiso=1 
				AND p.tutienmat=0 
				AND p.tutknganhang_id='".$tknganhang_id."' ".$sql_add."
			
			UNION

			SELECT p.id AS parent_id,
				p.sotien AS thutien,
				0 AS chitien
			FROM ec_chuyentiennoibo p 
			WHERE p.deleted=0 
				AND p.ghiso=1 
				AND p.dentienmat=0 
				AND p.dentknganhang_id='".$tknganhang_id."' ".$sql_add."
		) AS t ";
	
		$sodauky += $db->getOne($sql);

		// if($current_user->user_name == 'hungnh'){
		// 	pr($sql);
		// };
		
		return $sodauky;
	}
	
	// Lấy danh sách tất cả các chứng từ phát sinh
	function getVoucherList($sotk, $tknganhang_id, $post_tungay, $sql_search){
		global $db, $current_user;
		$arr = array();
		// main query
		$sql = "SELECT p.ngayhachtoan AS ngayghiso, p.ngaychungtu, p.name AS sochungtu, p.description AS diengiai,
					IF(p.amount_type = 'USD', p.amount_converted, p.amount) AS sotien,
					'+' AS pheptoan,
					'EC_Receipt_Voucher' AS parent_type,
					p.id AS parent_id
				FROM ec_receipt_voucher p 
				WHERE p.deleted = 0 
					AND p.receipt_type = 'credit_transfer'
					AND p.amount IS NOT NULL
					AND p.rv_status='1'
					AND p.is_margin=0
					AND p.tknganhang_id='".$tknganhang_id."' ".$sql_search."
				
				UNION

				SELECT p.ngayhachtoan AS ngayghiso, p.ngaychungtu, p.name AS sochungtu, p.description AS diengiai, 
					p.amount AS sotien,
					'-' AS pheptoan,
					'EC_Payment_Voucher' AS parent_type,
					p.id AS parent_id
				FROM ec_payment_voucher p 
				WHERE p.deleted = 0 
					AND p.hinhthucchi = 'credit_transfer'
					AND p.amount IS NOT NULL
					AND p.pv_status = '3'
					AND p.tknganhang_id = '".$tknganhang_id."' ".$sql_search." 
				
				UNION
				SELECT p.ngayhachtoan AS ngayghiso, p.ngaychungtu, p.name AS sochungtu, p.description AS diengiai,
					IFNULL(p.sotien,0) AS sotien,
					'-' AS pheptoan,
					'EC_ChuyenTienNoiBo' AS parent_type,
					p.id AS parent_id
				FROM ec_chuyentiennoibo p 
				WHERE p.deleted = 0 
					AND p.ghiso = 1
					AND p.tutienmat = 0
					AND p.tutknganhang_id = '".$tknganhang_id."' ".$sql_search."
				
				UNION
				SELECT p.ngayhachtoan AS ngayghiso, p.ngaychungtu, p.name AS sochungtu, p.description AS diengiai,
					IFNULL(p.sotien,0) AS sotien,
					'+' AS pheptoan,
					'EC_ChuyenTienNoiBo' AS parent_type,
					p.id AS parent_id
				FROM ec_chuyentiennoibo p 
				WHERE p.deleted = 0
					AND p.ghiso = 1
					AND p.dentienmat = 0
					AND p.dentknganhang_id = '".$tknganhang_id."' ".$sql_search."
	
				ORDER BY ngayghiso ";

		// if($current_user->user_name == 'hungnh'){
		// 	pr($sql);
		// };

		$res = $db->query($sql);
		$html = '';
		$tongthu = 0;
		$tongchi = 0;
		$tongton = 0;
		$sodauky = $this->getTheOpeningAccount($sotk, $tknganhang_id, $post_tungay);
		
		// Số đầu kỳ
		$html .= '<tr>
			<td align="center"><label>&nbsp;</label></td>
			<td align="center"><label>&nbsp;</label></td>
			<td><label>&nbsp;</label></td>
			<td><label>&nbsp;</label></td>
			<td><label style="font-weight:bold">Số đầu kỳ</label></td>
			<td align="right"><label>&nbsp;</label></td>
			<td align="right"><label>&nbsp;</label></td>
			<td align="right"><label>'.format_number($sodauky).'</label></td>
		</tr>';
		
		$tongton += $sodauky;
		
		while($row = $db->fetchByAssoc($res)){
			$chungtuthu = '';
			$chungtuchi = '';
			$sotienthu = '';
			$sotienchi = '';

			if($row['pheptoan']=='+'){
				$chungtuthu .= '<a title="Xem chi tiết" target="_blank"';
				$chungtuthu .= ' href="index.php?module='.$row['parent_type'].'&action=DetailView&record='.$row['parent_id'].'">'.$row['sochungtu'].'</a>';
				$chungtuchi .= '&nbsp;';
				$sotienthu .= format_number($row['sotien']);  
				$sotienchi .= '&nbsp;'; 
				$tongthu += $row['sotien'];
				$tongton += $row['sotien'];
			}
			else {
				$chungtuthu .= '&nbsp;';
				$chungtuchi .= '<a title="Xem chi tiết" target="_blank"';
				$chungtuchi .= ' href="index.php?module='.$row['parent_type'].'&action=DetailView&record='.$row['parent_id'].'">'.$row['sochungtu'].'</a>';
				$sotienthu .= '&nbsp;';
				$sotienchi .= format_number($row['sotien']);
				$tongchi += $row['sotien'];
				$tongton -= $row['sotien'];
			}
			
			$html .= '<tr>
				<td align="center">'.date('d/m/Y', strtotime($row['ngayghiso'])).'</td>
				<td align="center">'.date('d/m/Y', strtotime($row['ngaychungtu'])).'</td>
				<td>'.$chungtuthu.'</td>
				<td>'.$chungtuchi.'</td>
				<td>'.$row['diengiai'].'</td>
				<td align="right">'.$sotienthu.'</td>
				<td align="right">'.$sotienchi.'</td>
				<td align="right">'.format_number($tongton).'</td>
           	</tr>';
					
		}
		
		$arr['html'] = $html;
		$arr['tongthu'] = $tongthu;
		$arr['tongchi'] = $tongchi;
		$arr['tongton'] = $tongton;
		
		return $arr;
	}
}
