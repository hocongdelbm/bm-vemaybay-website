<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");
		
class Viewinhoadon extends SugarView {
	function display() {
		global $db;
		if(isset($_POST['record']) && !empty($_POST['record'])){
			
			if($_POST['solanin'] + 1 > 1){
				echo '<script>
					alert("Hóa đơn này đã in lớn hơn 1 lần");
				</script>';
			}
			
			// Update print counter
			$update = "UPDATE ec_hoadonban SET solanin = IFNULL(solanin,0) + 1 WHERE id='".$_POST['record']."' ";
			$db->query($update);
			
			$smartyCont= new Sugar_Smarty();
			$this->populateCont($smartyCont);
			$smartyCont->display('modules/EC_HoaDonBan/tpls/inhoadon.tpl');
		} 
		else {
			header("Location: index.php?module=EC_HoaDonBan&action=Error&error_string=".urlencode("Vui lòng chọn hóa đơn để in"));
		  	exit();
		}
	}
	
	function populateCont($smartyobj){
		global $db, $app_list_strings;
		
		require_once("ReadNumberInWords.php");
		$readnum = new ReadNumberInWords();
		
		$smartyobj->assign('COM_NAME', $app_list_strings['company_info_list']['name']);
		$smartyobj->assign('COM_ADDRESS', $app_list_strings['company_info_list']['name']);
		$smartyobj->assign('COM_TAXCODE', $app_list_strings['company_info_list']['taxcode']);
		$smartyobj->assign('COM_WEBSITE', $app_list_strings['company_info_list']['website']);
		$smartyobj->assign('COM_EMAIL', $app_list_strings['company_info_list']['email']);
		$smartyobj->assign('COM_TEL', $app_list_strings['company_info_list']['tel']);
		$smartyobj->assign('COM_MOBILE', $app_list_strings['company_info_list']['mobile']);
		
		$inv_d = date('d', strtotime($this->bean->ngayhoadon));
		$inv_m = date('m', strtotime($this->bean->ngayhoadon));
		$inv_y = date('Y', strtotime($this->bean->ngayhoadon));
		$smartyobj->assign('INV_CODE', $this->bean->kyhieuhd);
		$smartyobj->assign('INV_NUM', $this->bean->sohoadon);
		$smartyobj->assign('INV_DATE', 'Ngày '.$inv_d.' Tháng '.$inv_m.' Năm '.$inv_y);
		$smartyobj->assign('CONTACT', $this->bean->lienhe);
		$smartyobj->assign('ACC_NAME', $this->bean->doituong);
		$smartyobj->assign('ACC_TAXCODE', $this->bean->masothue);
		$smartyobj->assign('ACC_ADDRESS', $this->bean->diachi);
		$smartyobj->assign('PAYMENT_TYPE', $app_list_strings['hinhthucthanhtoan_list'][$this->bean->hinhthuctt]);
		
		$arr = $this->getDetails($this->bean->id);
		$smartyobj->assign('DATA', $arr['html']);
		$smartyobj->assign('NO_DATA_HEIGHT', $arr['total_height']);
		
		$smartyobj->assign('TAX_PERCENT', number_format(($this->bean->tongtienthue * 100 / $this->bean->tongtien), 0, ',', '.'));
		$smartyobj->assign('TOTAL_AMT', number_format($this->bean->tongtien, 0, ',', '.'));
		$smartyobj->assign('TOTAL_TAX_AMT', number_format($this->bean->tongtienthue, 0, ',', '.'));
		$smartyobj->assign('TOTAL_PAY_AMT', number_format($this->bean->tongthanhtoan, 0, ',', '.'));
		$smartyobj->assign('TOTAL_PAY_AMT_IN_WORDS', $readnum->docso($this->bean->tongthanhtoan).' đồng chẵn');
		
	}
	
	function getDetails($invoice_id){
		global $db;
		$arr = array();
		$total_height = 324;
		$sql = "SELECT h.description AS diengiai,
					h.donvitinh,
					h.soluong,
					h.dongia,
					h.thanhtien
				FROM ec_chitiethoadon h
				WHERE h.deleted=0 
					AND h.parent_type='EC_HoaDonBan'
					AND h.parent_id='".$invoice_id."' ";
		
		$res = $db->query($sql);
		$html = '';
		$i=1;
		while($row = $db->fetchByAssoc($res)){
			$html .= '<tr>
				<td style="text-align:center;">'.$i.'</td>
				<td>'.$row['diengiai'].'</td>
				<td>'.$row['donvitinh'].'</td>
				<td style="text-align:right;">'.number_format($row['soluong'], 2, ',', '.').'</td>
				<td style="text-align:right;">'.number_format($row['dongia'], 2, ',', '.').'</td>
				<td style="text-align:right;">'.number_format($row['thanhtien'], 0, ',', '.').'</td>
			</tr>';
			$total_height -= 27;
			$i++;
		}
		$arr['html'] = $html;
		$arr['total_height'] = $total_height;
		return $arr;
	}
}
