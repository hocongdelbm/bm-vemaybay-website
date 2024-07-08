<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.detail.php');

class EC_HoaDonBanViewDetail extends ViewDetail {
	function display() {
		$this->populateLineItems();
		parent::display();
	}
	
	 function populateLineItems(){
		 global $app_list_strings, $app_strings, $mod_strings, $timedate;
		 
		 $html = '';
		 $html .= '<table border="0" style="width:100%; border:1px solid #F6F6F6;line-height:25px;margin-top:-5px;" cellpadding="0" cellspacing="0">';
		 $html .= '<tr>';
		 $html .= '<td width="5%" scope="row" style="text-align: center; background:#F6F6F6; color:#000; border-top:1px solid #ccc"><b>'.$mod_strings['LBL_SOTT'].'</b></td>';
		 $html .= '<td width="15%" scope="row" style="text-align: center; background:#F6F6F6; color:#000; border-top:1px solid #ccc"><b>'.$mod_strings['LBL_SOVE'].'</b></td>';
		 $html .= '<td width="15%" scope="row" style="text-align: center; background:#F6F6F6; color:#000; border-top:1px solid #ccc"><b>'.$mod_strings['LBL_HANHTRINH'].'</b></td>';
		 $html .= '<td width="5%" scope="row" style="text-align: center; background:#F6F6F6; color:#000; border-top:1px solid #ccc"><b>'.$mod_strings['LBL_SOLUONG'].'</b></td>';
		 $html .= '<td width="15%" scope="row" style="text-align: right; background:#F6F6F6; color:#000; border-top:1px solid #ccc"><b>'.$mod_strings['LBL_DONGIA'].'&nbsp;</b></td>';
		 $html .= '<td width="15%" scope="row" style="text-align: right; background:#F6F6F6; color:#000; border-top:1px solid #ccc"><b>'.$mod_strings['LBL_THUEVAT'].'&nbsp;</b></td>';
		 $html .= '<td width="15%" scope="row" style="text-align: right; background:#F6F6F6; color:#000; border-top:1px solid #ccc"><b>'.$mod_strings['LBL_PHISANBAY'].'&nbsp;</b></td>';
		 $html .= '<td width="15%" scope="row" style="text-align: right; background:#F6F6F6; color:#000; border-top:1px solid #ccc"><b>'.$mod_strings['LBL_THANHTIEN'].'&nbsp;</b></td>';
		 $html .= '</tr>';
		 
		$sql = "SELECT name
					   ,soluong
					   ,dongia
					   ,thuesuat
					   ,thuevat
					   ,phisanbay
					   ,thanhtien
					   ,hanhtrinh
					   ,description
				 FROM ec_chitiethoadon
				 WHERE deleted = 0
				 AND parent_id = '".$this->bean->id."' 
				 AND parent_type = 'EC_HoaDonMua' ";
		 $res = $this->bean->db->query($sql);
		 $i = 0;
		 while($row = $this->bean->db->fetchByAssoc($res)){
			 if($i % 2 > 0) $bgcolor = 'background:#f9f9f9;'; else $bgcolor = 'background:#fff;';
			 $html .= '<tr>';
			 $html .= '<td class="dataLabel" style="text-align: center;'.$bgcolor.'"><label style="margin:5px">'.($i+1).'</label></td>';
			 $html .= '<td class="dataLabel" style="text-align: center;'.$bgcolor.'"><label style="margin:5px">'.$row['name'].'</label></td>';
			 $html .= '<td class="dataLabel" style="text-align: center;'.$bgcolor.'"><label style="margin:5px">'.$row['hanhtrinh'].'</label></td>';
			 $html .= '<td class="dataLabel" style="text-align: center;'.$bgcolor.'"><label style="margin:5px">'.format_number($row['soluong']).'</label></td>';
			 $html .= '<td class="dataLabel" style="text-align: right;'.$bgcolor.'"><label style="margin:5px">'.format_number($row['dongia']).'</label></td>';
			 $html .= '<td class="dataLabel" style="text-align: right;'.$bgcolor.'"><label style="margin:5px">'.format_number($row['thuevat']).'</label></td>';
			 $html .= '<td class="dataLabel" style="text-align: right;'.$bgcolor.'"><label style="margin:5px">'.format_number($row['phisanbay']).'</label></td>';
			 $html .= '<td class="dataLabel" style="text-align: right;'.$bgcolor.'"><label style="margin:5px">'.format_number($row['thanhtien']).'</label></td>';
			 $html .= '</tr>';
			 $i++;
		 }
		 
		 $html .= '<tr>';
		 $html .= '<td class="dataLabel" style="text-align: left;'.$bgcolor.'; border-top:1px solid #ccc;"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: left;'.$bgcolor.'; border-top:1px solid #ccc;"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: left;'.$bgcolor.'; border-top:1px solid #ccc;"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: center;'.$bgcolor.'; border-top:1px solid #ccc;"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: center;'.$bgcolor.'; border-top:1px solid #ccc;"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: center;'.$bgcolor.'; border-top:1px solid #ccc;"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: right;'.$bgcolor.'; border-top:1px solid #ccc;"><label style="margin:5px">'.$mod_strings['LBL_PHIHANHLY'].'</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: right;'.$bgcolor.'; border-top:1px solid #ccc;"><label style="margin:5px">'.format_number($this->bean->phihanhly).'</label></td>';
		 $html .= '</tr>';
		 
		 $html .= '<tr>';
		 $html .= '<td class="dataLabel" style="text-align: left;'.$bgcolor.'"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: left;'.$bgcolor.'"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: left;'.$bgcolor.'"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: center;'.$bgcolor.'"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: center;'.$bgcolor.'"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: center;'.$bgcolor.'"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: right;'.$bgcolor.'"><label style="margin:5px">'.$mod_strings['LBL_PHIHOANDOIVE'].'</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: right;'.$bgcolor.'"><label style="margin:5px">'.format_number($this->bean->phihoandoive).'</label></td>';
		 $html .= '</tr>';
		 
		 $html .= '<tr>';
		 $html .= '<td class="dataLabel" style="text-align: left;'.$bgcolor.'"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: left;'.$bgcolor.'"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: left;'.$bgcolor.'"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: center;'.$bgcolor.'"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: center;'.$bgcolor.'"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: center;'.$bgcolor.'"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: right;'.$bgcolor.'"><label style="margin:5px">'.$mod_strings['LBL_PHIKHAC'].'</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: right;'.$bgcolor.'"><label style="margin:5px">'.format_number($this->bean->phikhac).'</label></td>';
		 $html .= '</tr>';
		 
		 $html .= '<tr>';
		 $html .= '<td class="dataLabel" style="text-align: left;'.$bgcolor.'"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: left;'.$bgcolor.'"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: left;'.$bgcolor.'"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: center;'.$bgcolor.'"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: center;'.$bgcolor.'"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: center;'.$bgcolor.'"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: right;'.$bgcolor.'"><label style="margin:5px">'.$mod_strings['LBL_GIAMGIA'].'</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: right;'.$bgcolor.'"><label style="margin:5px">'.format_number($this->bean->giamgia).'</label></td>';
		 $html .= '</tr>';
		 
		 $html .= '<tr>';
		 $html .= '<td class="dataLabel" style="text-align: left;'.$bgcolor.'; border-top:1px solid #ccc;"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: left;'.$bgcolor.'; border-top:1px solid #ccc;"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: left;'.$bgcolor.'; border-top:1px solid #ccc;"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: center;'.$bgcolor.'; border-top:1px solid #ccc;"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: center;'.$bgcolor.'; border-top:1px solid #ccc;"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: center;'.$bgcolor.'; border-top:1px solid #ccc;"><label style="margin:5px">&nbsp;</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: right;'.$bgcolor.'; border-top:1px solid #ccc;"><label style="margin:5px; font-weight:bold">'.$mod_strings['LBL_TONGTIEN'].'</label></td>';
		 $html .= '<td class="dataLabel" style="text-align: right;'.$bgcolor.'; border-top:1px solid #ccc;"><label style="margin:5px; font-weight:bold">'.format_number($this->bean->tongtien).'</label></td>';
		 $html .= '</tr>';
		 
		 $html .= '</table>';
		 $this->ss->assign('LINE_ITEMS', $html);
		 
	 }
}
