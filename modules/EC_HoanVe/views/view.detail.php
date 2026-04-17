<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.detail.php');

class EC_HoanVeViewDetail extends ViewDetail {

	function display(){

		$this->displayJS();
		$this->populateCustomButtons();
		$this->populateLineItems();
		parent::display();
	}
	
	function displayJS(){
		$js = '<script>
			var tinhtrang="'.$this->bean->tinhtrang.'";
		</script>';

		echo $js;
	}
	
	function populateCustomButtons(){
		global $app_list_strings, $timedate;
		$date_format = $timedate->get_date_format();
		
		// tình trạng hoàn vé
		// 2: mới tạo
		// 1: đã hoàn
		// 0: đang hoàn
		
		if( ACLController::checkAccess('EC_Payment_Voucher', 'edit', true) 
			&& ($this->bean->tinhtrang == '0' || $this->bean->tinhtrang == '2')
		){
			$phieuchi = '</form>
			<form action="index.php" method="post" id="frmPhieuChi" name="frmPhieuChi">
				<input type="hidden" name="module" value="EC_Payment_Voucher" />
				<input type="hidden" name="action" value="EditView" />
				<input type="hidden" name="hoanve" value="'.$this->bean->name.'" />
				<input type="hidden" name="hoanve_id" value="'.$this->bean->id.'" />
				<input type="hidden" name="amount" value="'.format_number($this->bean->tongtienkhach).'" />
				<input type="submit" class="btn btn-primary" id="btnPhieuChi" name="btnPhieuChi" title="Chi tiền" value="Chi tiền" style="font-weight:bold;" />
			</form>';
			$this->ss->assign('PHIEUCHI', $phieuchi);
		}
		
		if(ACLController::checkAccess('EC_HoanVe', 'edit', true)){
			$doitt = '</form>
			<form action="index.php" method="post" id="frmDoiTT" name="frmDoiTT">
				<input type="hidden" name="module" value="EC_HoanVe" />
				<input type="hidden" name="action" value="Save" />
				<input type="hidden" name="record" value="'.$this->bean->id.'" />
				<input type="hidden" name="return_module" value="EC_HoanVe" />
				<input type="hidden" name="return_action" value="Save" />
				<input type="hidden" name="return_id" value="'.$this->bean->id.'" />
				<select class="box-select" id="tinhtrang" name="tinhtrang">'.get_select_options_with_id($app_list_strings['tinhtranghoanve_list'], (int)$this->bean->tinhtrang).'</select>
				<input type="submit" class="btn btn-warning" id="btnDoiTT" name="btnDoiTT" title="Đổi tình trạng" value="Đổi tình trạng" style="font-weight:bold;" />
			</form>';
			$this->ss->assign('DOITT', $doitt);
		}
		
	}
	
	/*function isVoucherCompleted(){
		
		$tongchi = 0;
		$sql = "SELECT SUM(IFNULL(amount,0))
				FROM ec_payment_voucher
				WHERE deleted=0 
				AND hoanve_id='".$this->bean->id."' 
				AND pv_status='3' ";
		
		$tongchi += $this->bean->db->getOne($sql);
		if($this->bean->tongtienkhach > $tongchi)
			return false;
		return true; 
	}*/
	
	function populateLineItems(){
		global $app_list_strings, $timedate;
		$date_format = $timedate->get_date_format();
		$html = '';
		$html .= '<table cellpadding="0" cellspacing="0" border="0" class="table-vertical__mobile table-details__booking table-details__hoanve" >';
		$html .= '<thead><tr>
					<th scope="col" width="7%" class="text-center">Loại HK</th>
					<th scope="col" width="5%" class="text-center">Danh xưng</th>
					<th scope="col" width="15%" class="text-center">Họ tên</th>
					<th scope="col" width="8%" class="text-center">Ngày sinh</th>
					<th scope="col" width="7%" class="text-center">Chiều</th>
					<th scope="col" width="7%" class="text-center">Mã hãng</th>
					<th scope="col" width="4%" class="text-center">Nơi đi</th>
					<th scope="col" width="4%" class="text-center">Nơi đến</th>
					<th scope="col" width="4%" class="text-center">Số vé</th>
					<th scope="col" width="7%" class="text-center">PNR</th>
					<th scope="col" width="7%" class="text-center">NCC</th>
					<th scope="col" width="7%" class="text-center">Tiền hãng hoàn</th>
					<th scope="col" width="7%" class="text-center">Tiền hoàn khách</th>
					<th scope="col" width="7%" class="text-center">Phí DV</th>
					<th scope="col" width="4%" class="text-center">Đã hoàn</th>
				  </tr></thead>';
		
		$sql = "SELECT c.id AS detail_id
					  ,c.name AS hoten
					  ,c.loaihk
					  ,c.danhxung
					  ,c.ngaysinh
					  ,c.chieubay
					  ,c.airline_code
					  ,c.noidi
					  ,c.noiden
					  ,c.sove
					  ,c.pnr
					  ,(SELECT a.name FROM accounts a WHERE a.deleted=0 AND a.id=c.nhacc_id) AS nhacc
					  ,c.nhacc_id
					  ,c.dahoan
					  ,c.sotienhang
					  ,c.sotienkhach
					  ,c.phidichvu
				FROM ec_chitiethoanve c
				WHERE c.deleted=0 AND c.hoanve_id='".$this->bean->id."' ";
			
		$res = $this->bean->db->query($sql);
		$i = 0;
		while($row = $this->bean->db->fetchByAssoc($res)){

			$html .= '<tr>
						<td data-label="Loại HK" class="text-center">'.$app_list_strings['passenger_type_list'][(int)$row['loaihk']].'</td>
						<td data-label="Danh xưng" class="text-center">'.$app_list_strings['passenger_salutation_list'][(int)$row['danhxung']].'</td>
						<td data-label="Họ tên" class="text-start">'.$row['hoten'].'</td>
						<td data-label="Ngày sinh" class="text-center">'.(trim($row['ngaysinh']) != '' ? date($date_format, strtotime($row['ngaysinh'])) : '').'</td>
						<td data-label="Chiều" class="text-center">'.$app_list_strings['bk_direction_list'][(int)$row['chieubay']].'</td>
						<td data-label="Mã hãng" class="text-center">'.$app_list_strings['ma_hang'][$row['airline_code']].' ('.$row['airline_code'].')</td>
						<td data-label="Nơi đi" class="text-center">'.$row['noidi'].'</td>
						<td data-label="Nơi đến" class="text-center">'.$row['noiden'].'</td>
						<td data-label="Số vé" class="text-center">'.$row['sove'].'</td>
						<td data-label="PNR" class="text-center">'.$row['pnr'].'</td>
						<td data-label="NCC" class="text-start">
							<a href="index.php?module=Accounts&action=DetailView&record='.$row['nhacc_id'].'" target="_blank">'.$row['nhacc'].'</a>
							<input type="hidden" name="ct_nhacc_id[]" id="ct_nhacc_id'.$i.'" value="'.$row['nhacc_id'].'" />
						</td>
						<td data-label="Tiền hãng hoàn" class="text-end">'.format_number($row['sotienhang']).'</td>
						<td data-label="Tiền hoàn khách" class="text-end">'.format_number($row['sotienkhach']).'</td>
						<td data-label="Phí DV<" class="text-end">'.format_number($row['phidichvu']).'</td>
						<td data-label="Đã hoàn" class="text-center align-middle"><input name="ct_dahoan[]" id="ct_dahoan'.$i.'" type="checkbox" disabled="disabled" '.($row['dahoan'] ? 'checked="checked"' : '').' value="'.$row['dahoan'].'" /></td>
					</tr>';
			
			$i++;
		}
				  
		$html .= '<tr class="footer-tr">
					<td data-label="" colspan="10" class="text-start">Số dòng = '.$i.'</td>
					<td class="text-end fw-bold hide-mobile show-landscape">&nbsp;</td>
					<td data-label="Tổng tiền hãng hoàn"  class="text-end fw-bold">'.format_number($this->bean->tongtienhang).'</td>
					<td data-label="Tổng Tiền hoàn khách"  class="text-end fw-bold">'.format_number($this->bean->tongtienkhach).'</td>
					<td data-label="Tổng phí DV"  class="text-end fw-bold">'.format_number($this->bean->tongtiendv).'</td>
					<td class="text-end fw-bold hide-mobile show-landscape">&nbsp;</td>
				</tr>';
					
		$html .= '</table>';
		$this->ss->assign('LINE_ITEMS', $html);
	}
}
?>