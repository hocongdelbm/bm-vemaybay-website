<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.edit.php'); 

class EC_HoaDonBanViewEdit extends ViewEdit {
	function __construct() {
		parent::__construct();
	}
	
	function display(){
		$this->displayJS();
		$this->customButtons();
	    $this->populateLineItems();
		$this->populateCustomFields();
		parent::display();
	}
	
	function populateCustomFields(){
		global $mod_strings;
		// field name
		$name_val = !empty($this->bean->name) ? $this->bean->name : 'HDM'.$this->getNumberOfHoaDon();
		$custom_name = '<input type="text" name="name" id="name" size="30" maxlength="" value="'.$name_val.'" title="" tabindex="100" />';
		$this->ss->assign('CUSTOM_NAME', $custom_name);
		
		// nhận hóa đơn
		$nhd = '<p><input type="checkbox" id="nhanhoadon" name="nhanhoadon" value="0" title="" tabindex="104" />';
		$nhd .= '<span style="display:none" id="span_sohoadon"><label style="margin-left:10px">'.$mod_strings['LBL_SOHOADON'].':</label>&nbsp;<input type="text" name="sohoadon" id="sohoadon" value="'.$this->bean->sohoadon.'" tabindex="104" /></span></p>';
		$this->ss->assign('NhanHoaDon', $nhd);
	}
	
	function getNumberOfHoaDon(){
		$sql = "SELECT COUNT(id) FROM ec_hoadonmua ";
		$rowcount = $this->bean->db->getOne($sql);
		return str_pad($rowcount+1, 5, '0', STR_PAD_LEFT);
	}
	
	function customButtons(){
		global $app_strings;
		$custom_save = '<input title="'.$app_strings['LBL_SAVE_BUTTON_TITLE'].'" accesskey="'.$app_strings['LBL_SAVE_BUTTON_KEY'].'" type="button" class="button" name="btnSave" id="btnSave" value="'.$app_strings['LBL_SAVE_BUTTON_LABEL'].'" />';
		$this->ss->assign('CUSTOM_SAVE', $custom_save);
	}
	
	function populateLineItems(){
		global $app_list_strings, $app_strings, $mod_strings, $timedate, $locale;
		$html = '';
		
		$sql = "SELECT name
					 , description
					 , hanhtrinh
					 , soluong
					 , dongia
					 , thuesuat
					 , thuevat
					 , phisanbay
					 , thanhtien
					 , id
				FROM ec_chitiethoadon 
				WHERE parent_id = '".$this->bean->id."' 
				AND parent_type = 'EC_HoaDonMua' 
				AND deleted = 0 ";
		
		$html .= '<table id="tbl_ChiTietHoaDon" width="100%" cellpadding="0" cellspacing="0" border="0" style="line-height:25px">
				 	<tr>
						<td width="15%">'.$mod_strings['LBL_SOVE'].'</td>
						<td width="15%">'.$mod_strings['LBL_HANHTRINH'].'</td>
						<td width="5%">'.$mod_strings['LBL_SOLUONG'].'</td>
						<td width="15%">'.$mod_strings['LBL_DONGIA'].'</td>
						<td width="15%">'.$mod_strings['LBL_THUEVAT'].'</td>
						<td width="15%">'.$mod_strings['LBL_PHISANBAY'].'</td>
						<td width="15%">'.$mod_strings['LBL_THANHTIEN'].'</td>
						<td width="5%">&nbsp;</td>
					</tr>';
		$i = 0;
		$res = $this->bean->db->query($sql);
		// $countLine = $this->bean->db->getRowCount($res);
		$countLine = $this->bean->db->countRows($res);
		
		while($row = $this->bean->db->fetchByAssoc($res)){
			
			$ct_id = isset($_POST['isDuplicate']) && $_POST['isDuplicate'] == 'true' ? '' : $row['id'];
			
			$html .= '<tr id="cthd_line'.$i.'">
						<td><input tabindex="116" type="text" size="22" name="ct_sove[]" id="ct_sove'.$i.'" maxlength="255" value="'.$row['name'].'" /></td>
						<td><input tabindex="116" type="text" size="22" name="ct_hanhtrinh[]" id="ct_hanhtrinh'.$i.'" maxlength="150" value="'.$row['hanhtrinh'].'" /></td>
						<td align="center"><input tabindex="116" onclick="select()" onblur="TinhCTHD('.$i.')" type="text" size="2" name="ct_soluong[]" id="ct_soluong'.$i.'" maxlength="2" value="'.format_number($row['soluong']).'" style="text-align:right" /></td>
						<td><input tabindex="116" onclick="select()" onblur="TinhCTHD('.$i.')" type="text" size="22" name="ct_dongia[]" id="ct_dongia'.$i.'" maxlength="15" value="'.format_number($row['dongia']).'" style="text-align:right" /></td>
						<td><p><select id="ct_thuesuat'.$i.'" name="ct_thuesuat[]" onchange="TinhCTHD('.$i.')" tabindex="116">'.get_select_options_with_id($app_list_strings['thuesuat_list'], $row['thuesuat'] != '' ? $row['thuesuat'] : '10').'</select>%<input tabindex="116" onclick="select()" onblur="TinhCTHD('.$i.')" type="text" size="10" name="ct_thuevat[]" id="ct_thuevat'.$i.'" maxlength="15" value="'.format_number($row['thuevat']).'" style="text-align:right" /></p></td>
						<td><input tabindex="116" onclick="select()" onblur="TinhCTHD('.$i.')" type="text" size="22" name="ct_phisanbay[]" id="ct_phisanbay'.$i.'" maxlength="15" value="'.format_number($row['phisanbay']).'" style="text-align:right" /></td>
						<td><input tabindex="116" onclick="select()" onblur="TinhCTHD('.$i.')" type="text" size="25" name="ct_thanhtien[]" id="ct_thanhtien'.$i.'" maxlength="15" value="'.format_number($row['thanhtien']).'" style="text-align:right" /></td>
						<td><input tabindex="116" onclick="XoaDongCTHD(\'cthd_line'.$i.'\',\'ct_deleted'.$i.'\')" type="button" name="btnXoaDong" id="btnXoaDong" value="'.$mod_strings['LBL_XOA_DONG'].'" title="'.$mod_strings['LBL_XOA_DONG'].'" /><input type="hidden" name="ct_deleted[]" id="ct_deleted'.$i.'" value="0" /><input type="hidden" name="ct_id[]" id="ct_id'.$i.'" value="'.$ct_id.'" /></td>
					  </tr>';
			
			$i++;
		}// end while
		
		$sep = get_number_seperators();

		$html .= '<tr>
					<td><input type="hidden" id="grp_seperator" name="grp_seperator" value="'.$sep[0].'" />
		<input type="hidden" id="dec_seperator" name="dec_seperator" value="'.$sep[1].'" />
		<input type="hidden" id="sig_digits" name="sig_digits" value="'.$locale->getPrecision().'" />
		<input type="button" tabindex="116" class="button" id="btnThemDong" name="btnThemDong" value="'.$mod_strings['LBL_THEM_DONG'].'" title="'.$mod_strings['LBL_THEM_DONG'].'" onclick="ThemDongCTHD('.$countLine.')" /></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td align="right"><label style="margin-right:5px">'.$mod_strings['LBL_PHIHANHLY'].':</label></td>
					<td>
					<input tabindex="116" onclick="select()" onblur="TinhTongHoaDon()" type="text" name="phihanhly" id="phihanhly" value="'.($_POST['phihanhly'] != '' ? format_number($_POST['phihanhly']) : format_number($this->bean->phihanhly)).'" size="25" style="text-align:right"  /></td>
					<td>&nbsp;</td>
				  </tr>';
		
		$html .= '<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td align="right"><label style="margin-right:5px">'.$mod_strings['LBL_PHIHOANDOIVE'].':</label></td>
					<td>
					<input tabindex="116" onclick="select()" onblur="TinhTongHoaDon()" type="text" name="phihoandoive" id="phihoandoive" value="'.($_POST['phihoandoive'] != '' ? format_number($_POST['phihoandoive']) : format_number($this->bean->phihoandoive)).'" size="25" style="text-align:right"  /></td>
					<td>&nbsp;</td>
				  </tr>';
		
		$html .= '<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td align="right"><label style="margin-right:5px">'.$mod_strings['LBL_PHIKHAC'].':</label></td>
					<td>
					<input tabindex="116" onclick="select()" onblur="TinhTongHoaDon()" type="text" name="phikhac" id="phikhac" value="'.($_POST['phikhac'] != '' ? format_number($_POST['phikhac']) : format_number($this->bean->phikhac)).'" size="25" style="text-align:right"  /></td>
					<td>&nbsp;</td>
				  </tr>';
				  
		$html .= '<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td align="right"><label style="margin-right:5px">'.$mod_strings['LBL_GIAMGIA'].':</label></td>
					<td><p><select id="ptram_giamgia" name="ptram_giamgia" tabindex="116" onchange="TinhTongHoaDon()" >'.get_select_options_with_id($app_list_strings['discount_percent_list'], $this->bean->ptram_giamgia).'</select>%<input tabindex="116" onclick="select()" onblur="TinhTongHoaDon()" type="text" name="giamgia" id="giamgia" value="'.($_POST['giamgia'] != '' ? format_number($_POST['giamgia']) : format_number($this->bean->giamgia)).'" style="text-align:right; width:98px"  /></p></td>
					<td>&nbsp;</td>
				  </tr>';
		
		$html .= '<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td align="right"><label style="margin-right:5px">'.$mod_strings['LBL_TONGTIEN'].':</label></td>
					<td>
					<input tabindex="116" onclick="select()" onblur="TinhTongHoaDon()" type="text" name="tongtien" id="tongtien" value="'.($_POST['tongtien'] != '' ? format_number($_POST['tongtien']) : format_number($this->bean->tongtien)).'" size="25" style="text-align:right"  /></td>
					<td>&nbsp;</td>
				  </tr>';
		
		$html .= '</table>';
		
		$this->ss->assign('LINE_ITEMS', $html);
	}
	
	function displayJS(){
		global $mod_strings, $app_list_strings;
		
		$thuesuat_list = get_select_options_with_id($app_list_strings['thuesuat_list'], '10');
		$thuesuat_list = str_replace("\n", "", $thuesuat_list);
		$thuesuat_list = str_replace("'", "\"", $thuesuat_list);
		
		echo "<script>
				var LBL_XOA_DONG = '".$mod_strings['LBL_XOA_DONG']."';
				var thuesuat_list = '".$thuesuat_list."';
			  </script>";
	}
}
