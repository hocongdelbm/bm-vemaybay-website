<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");
		
class Viewdebttopay extends SugarView {

	function display() {
		$smartyCont= new Sugar_Smarty();
		$this->populateContent($smartyCont);
		$smartyCont->display('modules/EC_Debts/tpls/debttopay.tpl');
	}
	
	function populateContent($smartyobj){
		global $app_list_strings, $db, $current_user;
		$tk_congno = '331';
		$loaichi_id = '3361ac47-2254-701a-55d1-508abcb90f50'; // công nợ phải trả
		$sql_search = "";
		$html = '';
		// tu ngay
		if(isset($_POST['tungay']) && !empty($_POST['tungay'])){
			$sql_search .= " AND DATE(p.ngayhachtoan) >= '".date('Y-m-d',strtotime($_POST['tungay']))."' ";
			$post_tungay = $_POST['tungay'];
		} else {
			$sql_search .= " AND DATE(p.ngayhachtoan) >= '".date('Y-m-01')."' ";
			$post_tungay = date('01-m-Y');
		}
		// den ngay
		if(isset($_POST['denngay']) && !empty($_POST['denngay'])){
			$sql_search .= " AND DATE(p.ngayhachtoan) <= '".date('Y-m-d',strtotime($_POST['denngay']))."' ";
			$post_denngay = $_POST['denngay'];
		} else {
			$sql_search .= " AND DATE(p.ngayhachtoan) <= '".date('Y-m-t')."' ";
			$post_denngay = date('t-m-Y');
		}
		
		if(isset($_POST['btnDongY'])){
			
			$acc_count = count($_POST['khachhang_id']);
			for($i=0; $i<$acc_count; $i++){
				
				$voucher_arr = $this->getListVoucher($tk_congno, $loaichi_id, $_POST['khachhang_id'][$i], $post_tungay, $sql_search);
				
				$html .= '<table width="100%" border="0" cellspacing="0" cellpadding="0" id="tbl-wrapper">
			  <tr>
				<td width="30%">
				'.$app_list_strings['company_info_list']['name'].'<br />
				'.$app_list_strings['company_info_list']['address'].'<br />
				ĐT: '.$app_list_strings['company_info_list']['tel'].' - Fax: '.$app_list_strings['company_info_list']['fax'].'
				</td>
				<td width="40%">&nbsp;</td>
				<td width="30%">&nbsp;</td>
			  </tr>
			  <tr>
				<td colspan="3" align="center" style="font-weight:bold;"><label style="font-size:15pt;">CHI TIẾT CÔNG NỢ PHẢI TRẢ</label><br /><label style="font-style:italic;">Từ ngày '.$post_tungay.' đến ngày '.$post_denngay.'</label><br /><br /></td>
			  </tr>
			  <tr>
				<td colspan="3" style="font-weight:bold;">Mã nhà cung cấp: '.$_POST['makh_'.$_POST['khachhang_id'][$i]].' - Tên nhà cung cấp: '.$_POST['tenkh_'.$_POST['khachhang_id'][$i]].'</td>
			  </tr>
			  <tr>
				<td colspan="3" style="font-weight:bold;">Tài khoản: '.$tk_congno.'</td>
			  </tr>
			  <tr>
				<td colspan="3">
					<table width="100%" border="0" cellspacing="0" cellpadding="0" id="tbl-details">
						<tr>
							<td width="10%"><div align="center"><strong>Ngày ghi sổ</strong></div></td>
							<td width="15%"><div align="center"><strong>Số chứng từ</strong></div></td>
							<td width="10%"><div align="center"><strong>Hạn thanh toán</strong></div></td>
							<td width="20%"><div align="center"><strong>Diễn giải</strong></div></td>
							<td width="15%"><div align="center"><strong>Số tiền nợ</strong></div></td>
							<td width="15%"><div align="center"><strong>Tiền đã trả</strong></div></td>
							<td width="15%"><div align="center"><strong>còn lại</strong></div></td>
						</tr>
						<tr>
							<td><div align="center"><strong>A</strong></div></td>
							<td><div align="center"><strong>B</strong></div></td>
							<td><div align="center"><strong>C</strong></div></td>
							<td><div align="center"><strong>D</strong></div></td>
							<td><div align="center"><strong>1</strong></div></td>
							<td><div align="center"><strong>2</strong></div></td>
							<td><div align="center"><strong>3</strong></div></td>
						</tr>
						'.$voucher_arr['html'].'
						<tr>
							<td colspan="4"><strong>Cộng</strong></td>
							<td style="font-weight:bold;" align="right">'.format_number($voucher_arr['tongtienno']).'</td>
							<td style="font-weight:bold;" align="right">'.format_number($voucher_arr['tongdatra']).'</td>
							<td style="font-weight:bold;" align="right">'.format_number($voucher_arr['tongsodu']).'</td>
						</tr>
					</table>
				</td>
			  </tr>
			  <tr>
				<td align="center"><br /><label style="font-weight:bold;">Người lập</label><br /><label style="font-style:italic;">(Ký, họ tên)</label></td>
				<td align="center"><br /><label style="font-weight:bold;">Kế toán trưởng</label><br /><label style="font-style:italic;">(Ký, họ tên)</label></td>
				<td align="center"><br /><label style="font-weight:bold;">Giám đốc</label><br /><label style="font-style:italic;">(Ký, họ tên, đóng dấu)</label></td>
			  </tr>
			</table>';
			} // end for
			$smartyobj->assign('DATA', $html);
			return;
		} // end if
		
		$acc_arr = $this->getListAccount($tk_congno, $loaichi_id, $post_tungay, $sql_search);
		$smartyobj->assign('DS_KHACHHANG', $acc_arr['html']);
		$smartyobj->assign('TONGTIENNO', format_number($acc_arr['tongtienno']));
		$smartyobj->assign('POST_TUNGAY', $post_tungay);
		$smartyobj->assign('POST_DENNGAY', $post_denngay);
	}
	
	// lấy số đầu kỳ
	function getTheOpeningDebtAmount($tk_congno, $loaichi_id, $khachhang_id, $post_tungay){
		global $db;
		$sodauky = 0;
		$nam = date('Y', strtotime($post_tungay)) == date('Y') ? '' : date('Y', strtotime($post_tungay));
		
		$sql_search = " AND DATE(p.ngayhachtoan) >= '".date('Y-01-01', strtotime($post_tungay))."' 
						AND DATE(p.ngayhachtoan) < '".date('Y-m-d', strtotime($post_tungay))."' ";
						
		$sql = "SELECT SUM(sotienno)-SUM(tiendatra) FROM (
					SELECT p.name, (IFNULL(p.dunodau,0)-IFNULL(p.ducodau,0)) AS sotienno, 0 AS tiendatra FROM ec_chitiettaikhoan".$nam." p
					WHERE p.deleted=0 AND p.parent_type='Accounts' AND p.parent_id='".$khachhang_id."' AND p.sotaikhoan='".$tk_congno."'
					UNION
					SELECT p.name, p.debt_amount AS sotienno, 0 AS tiendatra
					FROM ec_debts p WHERE p.deleted=0 AND p.debt_type='Buy' AND p.supplier_id='".$khachhang_id."' ".$sql_search." 
					UNION
					SELECT p.name, 0 AS sotienno, p.amount AS tiendatra
					FROM ec_payment_voucher p WHERE p.deleted=0 AND p.pv_status='3' AND ec_payment_types_id_c='".$loaichi_id."' 
					AND p.supplier_id='".$khachhang_id."' ".$sql_search."
				) AS t ";
				
		$sodauky += $db->getOne($sql);
		return $sodauky;
	}
	
	// lấy danh sách chứng từ liên quan
	function getListVoucher($tk_congno, $loaichi_id, $khachhang_id, $post_tungay, $sql_search){
		global $db;
		$arr = array();
		
		$sql = "SELECT p.ngayhachtoan, p.date_limit AS hanthanhtoan, p.name AS sochungtu, 'EC_Debts' AS parent_type, p.id AS parent_id 
					  ,p.description AS diengiai, p.debt_amount AS sotien, '+' AS pheptoan
				FROM ec_debts p WHERE p.deleted=0 AND p.debt_type='Buy' AND p.supplier_id='".$khachhang_id."' ".$sql_search." 
				UNION
				SELECT p.ngayhachtoan, '' AS hanthanhtoan, p.name AS sochungtu, 'EC_Payment_Voucher' AS parent_type, p.id AS parent_id 
					  ,p.description AS diengiai, p.amount AS sotien, '-' AS pheptoan
				FROM ec_payment_voucher p WHERE p.deleted=0 AND p.pv_status='3' AND p.ec_payment_types_id_c='".$loaichi_id."' 
				AND p.supplier_id='".$khachhang_id."' ".$sql_search."
				ORDER BY ngayhachtoan ";
		
		/*echo $sql;
		echo '<br><br>';*/
		
		$res = $db->query($sql);
		$html = '';
		$tongtienno = 0;
		$tongdatra = 0;
		$tongsodu = 0;
		$i = 0;
		$tongsodu += $this->getTheOpeningDebtAmount($tk_congno, $loaichi_id, $khachhang_id, $post_tungay);
		
		$html .= '<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td style="font-weight:bold;">Số đầu kỳ</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td style="font-weight:bold;" align="right">'.format_number($tongsodu).'</td>
		</tr>';
		
		while($row = $db->fetchByAssoc($res)){
			
			if($row['pheptoan']=='+'){
				$lbl_sotienno = format_number($row['sotien']);
				$lbl_tiendatra = '&nbsp;';
				$tongtienno += $row['sotien'];
				$tongsodu += $row['sotien'];
			} else {
				$lbl_sotienno = '&nbsp;';
				$lbl_tiendatra = format_number($row['sotien']);
				$tongdatra += $row['sotien'];
				$tongsodu -= $row['sotien'];
			}
			
			$html .= '<tr>
			<td align="center">'.date('d/m/Y', strtotime($row['ngayhachtoan'])+7*3600).'</td>
			<td><a href="index.php?module='.$row['parent_type'].'&action=DetailView&record='.$row['parent_id'].'" target="_blank">'.$row['sochungtu'].'</a></td>
			<td align="center">'.(trim($row['hanthanhtoan'])!=''?date('d/m/Y', strtotime($row['hanthanhtoan'])):'&nbsp;').'</td>
			<td>'.$row['diengiai'].'</td>
			<td align="right">'.$lbl_sotienno.'</td>
			<td align="right">'.$lbl_tiendatra.'</td>
			<td align="right">'.format_number($tongsodu).'</td>
					</tr>';	
			$i++;
		} // while
		$arr['html'] = $html;
		$arr['tongtienno'] = $tongtienno;
		$arr['tongdatra'] = $tongdatra;
		$arr['tongsodu'] = $tongsodu;
		return $arr;
	}
	
	// lấy danh sách nhà cung cấp
	function getListAccount($tk_congno, $loaichi_id, $post_tungay, $sql_search){
		global $db;
		$arr = array();
		$sql = "SELECT id, ticker_symbol AS makh, name AS tenkh, sic_code AS masothue, billing_address_street AS diachi
				FROM accounts WHERE deleted=0 AND account_type='Supplier' ";
		
		//echo $sql;		
		
		$res = $db->query($sql);
		$html = '';
		$i = 0;
		$tongtienno = 0;
		while($row = $db->fetchByAssoc($res)){
			$arrin = $this->getListVoucher($tk_congno, $loaichi_id, $row['id'], $post_tungay, $sql_search);
			//if($arrin['tongsodu'] > 0){
				$html .= '<tr>
					<td align="center">
						<input type="checkbox" name="khachhang_id[]" id="khachhang_id'.$i.'" value="'.$row['id'].'" />
						<input type="hidden" name="makh_'.$row['id'].'" value="'.$row['makh'].'" />
						<input type="hidden" name="tenkh_'.$row['id'].'" value="'.$row['tenkh'].'" />
					</td>
					<td align="left">'.$row['makh'].'</td>
					<td align="left">'.$row['masothue'].'</td>
					<td align="left">'.$row['tenkh'].'</td>
					<td align="left">'.$row['diachi'].'</td>
					<td align="right">'.format_number($arrin['tongsodu']).'</td>
				</tr>';
			//}
			$i++;
			$tongtienno += $arrin['tongsodu'];
		}
		$arr['html'] = $html;
		$arr['tongtienno'] = $tongtienno;
		return $arr;		
	}
}
	
?>