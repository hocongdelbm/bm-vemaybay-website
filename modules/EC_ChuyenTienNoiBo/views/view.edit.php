<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.edit.php');

class EC_ChuyenTienNoiBoViewEdit extends ViewEdit
{
	function __construct()
	{
		parent::__construct();
	}

	function display()
	{
		if ($this->bean->ghiso == 0 || (isset($_POST['isDuplicate']) && $_POST['isDuplicate'] == 'true')) {
			$this->populateCustomFields();
			parent::display();
		} else echo '<p class="error">Chứng từ đã ghi sổ</p>';
	}

	function populateCustomFields()
	{
		global $app_strings, $app_list_strings, $mod_strings, $locale, $timedate, $current_user;
		$date_format = $timedate->get_date_format();

		// Ngay hach toan
		// $this->bean->ngayhachtoan = (isset($this->bean->ngayhachtoan) && !empty($this->bean->ngayhachtoan)) ? date($date_format.' H:i', strtotime($this->bean->ngayhachtoan)+7*3600) : date($date_format.' H:i');
		$this->bean->ngayhachtoan = isset($this->bean->ngayhachtoan) && !empty($this->bean->ngayhachtoan) ? date($date_format . ' H:i', strtotime($this->bean->ngayhachtoan) - 7 * 3600) : date($date_format . ' H:i', strtotime(date('d-m-Y H:i')) + 7 * 3600);

		// Dinh dang so
		$sep = my_get_number_separators();
		$ghiso = '<input type="hidden" name="ghiso" id="ghiso" value="1" />
				  <input type="hidden" id="grp_seperator" name="grp_seperator" value="' . $sep[0] . '" />
				  <input type="hidden" id="dec_seperator" name="dec_seperator" value="' . $sep[1] . '" />
			      <input type="hidden" id="sig_digits" name="sig_digits" value="' . $locale->getPrecision() . '" />';

		$tudiadiem = '<select id="tudiadiem_id" name="tudiadiem_id" class="flex-fill" style="' . ($this->bean->tutienmat == 1 ? '' : 'display:none') . '">' . myGetLocationListByDepID($this->bean->tudiadiem_id) . '</select>';
		$tutienmat = '<div class="d-flex align-items-center gap-2 flex-fill"><label class="w-40" for="tutienmat_chk"><span>Từ tiền mặt</span> <input ' . ($this->bean->tutienmat == 1 ? 'checked="checked"' : '') . ' type="checkbox" id="tutienmat_chk" /><input type="hidden" name="tutienmat" id="tutienmat" value="' . (isset($this->bean->tutienmat) ? $this->bean->tutienmat : 0) . '" /></label>' . $tudiadiem . '</div>';

		$dendiadiem = '<select id="dendiadiem_id" name="dendiadiem_id" class="flex-fill" style="' . ($this->bean->dentienmat == 1 ? '' : 'display:none') . '">' . myGetLocationListByDepID($this->bean->dendiadiem_id) . '</select>';
		$dentienmat = '<div class="d-flex align-items-center gap-2 flex-fill"><label class="w-40" for="dentienmat_chk"><span>Đến tiền mặt</span> <input ' . ($this->bean->dentienmat == 1 ? 'checked="checked"' : '') . ' type="checkbox" id="dentienmat_chk" /><input type="hidden" name="dentienmat" id="dentienmat" value="' . (isset($this->bean->dentienmat) ? $this->bean->dentienmat : 0) . '" /></label>' . $dendiadiem . '</div>';

		// PHAN QUYEN
		$tknganhang_group = "";

		// TU TAI KHOAN NGAN HANG
		$tutknganhang_id = isset($_REQUEST['tutknganhang_id']) && !empty($_REQUEST['tutknganhang_id']) ? $_REQUEST['tutknganhang_id'] : $this->bean->tutknganhang_id;
		$this->ss->assign('TUTKNGANHANG', '<div class="d-flex gap-2 align-items-center"><select ' . ($this->bean->tutienmat == 1 ? 'disabled="disabled"' : '') . ' id="tutknganhang_id" name="tutknganhang_id" class="w-40" tabindex="104"><option></option>' . myGetBankAccountList($tutknganhang_id, $tknganhang_group) . '</select>' . $tutienmat . $ghiso . '</div>');

		// DEN TAI KHOAN NGAN HANG
		$dentknganhang_id = isset($_REQUEST['dentknganhang_id']) && !empty($_REQUEST['dentknganhang_id']) ? $_REQUEST['dentknganhang_id'] : $this->bean->dentknganhang_id;
		$this->ss->assign('DENTKNGANHANG', '<div class="d-flex gap-2 align-items-center"><select ' . ($this->bean->dentienmat == 1 ? 'disabled="disabled"' : '') . ' id="dentknganhang_id" name="dentknganhang_id" class="w-40" tabindex="105"><option></option>' . myGetBankAccountList($dentknganhang_id, $tknganhang_group) . '</select>' . $dentienmat . '</div>');
	}
}
