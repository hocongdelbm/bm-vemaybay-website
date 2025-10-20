<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.detail.php');
require_once('custom/include/helpers/api/WinInvoice.php');

class EC_HoaDonBanViewDetail extends ViewDetail {
	function display() {
		$this->getStyles();
		$this->populateLineItems();

		// Cập nhật thông tin hóa đơn mới nhất từ hệ thống Wininvoice 
		if($this->bean->tinhtrang == '2' && $this->bean->company_unit == 'MHV' && (!$this->bean->sohoadon || empty($this->bean->sohoadon))) {
			$winInv = new WinInvoice();
			$json = $winInv->get($this->bean->name);

			if($winInv->checkResponse($json)) {
				$arr = json_decode($json, true);
				
				$hoadonban = new EC_HoaDonBan();
				$hoadonban->retrieve($this->bean->id);
				if(isset($arr['data'][0]['invNumber']) && $arr['data'][0]['invNumber'] != "0000000") {
					$hoadonban->sohoadon   = $arr['data'][0]['invNumber'];
					$hoadonban->ngayhoadon = $arr['data'][0]['invDate'];
					$hoadonban->kyhieuhd   = $arr['data'][0]['invSerial'];
				}
				$hoadonban->is_signed = isset($arr['data'][0]['invIsSigned']) ? $arr['data'][0]['invIsSigned'] : 0;
				$hoadonban->invoice_data = $json;
				$hoadonban->save();
			}
        }
		parent::display();
		$this->getScripts();
	}

	private function getStyles() {
		echo "<link type='text/css' rel='stylesheet' href='modules/{$this->bean->module_dir}/css/view.detail.css?v=1.0.0' />";
	}

	private function getScripts() {
		echo "<script src='modules/{$this->bean->module_dir}/js/view.detail.js?v=1.0.5'></script>";
	}
	
	public function populateLineItems() {
		if($this->bean->company_unit == 'MHV') {
			$custom_sohoadon = '<span>'.(int)$this->bean->sohoadon.'</span>';
		} else {
			$custom_sohoadon = '<span>'.$this->bean->sohoadon.'</span>';
		}
		if($this->bean->is_signed == 1) {
			$custom_sohoadon .= '<span class="text-success fw-semibold" style="float:right;">
				<svg width="18px" height="18px" stroke-width="1.75" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="currentColor" style="padding-bottom:2px;">
					<path d="M7 12.5L10 15.5L17 8.5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
				</svg>
				Đã ký số
			</span>';
		}
		$this->ss->assign('CUSTOM_SOHOADON', $custom_sohoadon);

		// CCCD/Passport
		$id_number = $this->bean->citizen_id ?? '';
		if($this->bean->passport_number && !empty($this->bean->passport_number)) {
			if(!empty($id_number)) $id_number .= " - {$this->bean->passport_number}";
			else $id_number .= $this->bean->passport_number;
		}
		$this->ss->assign('CUSTOM_ID_NUMBER', '<span class="sugar_field" id="identity_number">'.$id_number.'</span>');

		$html = '<div>
			<table class="table-details__booking" cellpadding="0" cellspacing="0" border="0">';
		
		if($this->bean->loaihoadon == 0) {
			$html .= '<thead>
				<tr>
					<th width="3%" align="center">STT</th>
					<th width="9%" align="left">Booking</th> 
					<th width="14%" align="left">Số vé</th> 
					<th width="3%" align="center">SL</th>
					<th width="9%" align="center">Giá bán</th> 
					<th width="8%" align="center">Phí DV</th>
					<th width="8%" align="center">VAT</th>
					<th width="6%" align="center">Thành tiền</th>
					<th width="12%" align="center">Thu hộ</th>
				</tr>
			</thead>';
			$colspan = 3;
		} 
		else if($this->bean->loaihoadon == 1) {
			$html .= '<thead>
				<tr>
					<th width="5%" class="text-center"><span>STT</span></td>
					<th width="35%" class="text-center"><span>Tên dịch vụ</span></td>
					<th width="7%" class="text-center"><span>Số lượng</span></td>
					<th width="15%" class="text-center"><span>Đơn giá</span></td>
					<th width="10%" class="text-center"><span>VAT</span></td>
					<th width="20%" class="text-center"><span>Thành tiền</span></td>
					<th width="10%" class="text-center text-muted"><span>Thu hộ</span></td>
				</tr>
			</thead>';
			$colspan = 2;
		}
		
		$deleted = $this->bean->tinhtrang != '-1' ? 0 : 1;
		$sql = "SELECT c.*
				,in_iv.ticket_code
				,IFNULL(re.name, '') AS receipt_voucher_name
			FROM ec_chitiethoadon c
				LEFT JOIN ec_input_invoices in_iv ON in_iv.id = c.ticket_number_id
				LEFT JOIN ec_receipt_voucher re ON re.id = c.receipt_voucher_id
			WHERE c.parent_id = '{$this->bean->id}'
				AND c.parent_type = 'EC_HoaDonBan'
				AND c.deleted = $deleted
			ORDER BY order_by_no";
			
		$res = $this->bean->db->query($sql);
		$i = $tonggiaban = $tongthue = $tongthuho = $phisanbay = $phikhac = $tongdv = 0;
		$array_item = [
			'items' 			=> [],
			'invSubTotal' 	 	=> '', // Tổng tiền hàng (chưa VAT & chưa chiết khấu)
			'invVatAmount' 	=> '', // Tổng tiền thuế
			'invTotalAmount' 	=> '' // Tổng cộng
		];
		while($row = $this->bean->db->fetchByAssoc($res)) {
			if($this->bean->loaihoadon == 0) {
				$receipt = '';
				if(!empty($row['receipt_voucher_id'])) {
					$receipt = '<br /><a href="index.php?module=EC_Receipt_Voucher&action=DetailView&record='.$row['receipt_voucher_id'].'" target="_blank">'.$row['receipt_voucher_name'].'</a>';
				}

				$bk = new EC_Flight_Bookings;
				$bk->retrieve($row['booking_id']);
				if($bk->name != $row['booking']) $error_txt = '<br><font color="red">(Chưa khớp thông tin bk)</font>';
				else $error_txt = '';

				$html .= '<tr>
					<td class="text-center">' . (++$i) . '</td>
					<td class="text-start">
						<a href="index.php?module=EC_Flight_Bookings&action=DetailView&record='.$row['booking_id'].'" target="_blank">'.$row['booking'].'</a>
						'.$error_txt.$receipt.'
					</td>
					<td class="text-start">' . $row['name'] . '</td>
					<td class="text-end">' . format_number($row['soluong']) . '</td>
					<td class="text-end">' . format_number($row['dongia']) . '</td>
					<td class="text-end">' . format_number($row['phidv']) . '</td>
					<td class="text-end">' . format_number($row['tienthue']) . '</td>
					<td class="text-end">' . format_number($row['thanhtien'] - $row['phithuho']*$row['soluong']) . '</td>
					<td class="text-end text-muted">' . format_number($row['phithuho']) . '</td>
				</tr>';
			}
			else if($this->bean->loaihoadon == 1) {
				$html .= '<tr>
					<td class="text-center">' . (++$i) . '</td>
					<td class="text-start">' . $row['name'] . '</td>
					<td class="text-end">' . format_number($row['soluong']) . '</td>
					<td class="text-end">' . format_number($row['dongia']) . '</td>
					<td class="text-end">' . format_number($row['tienthue']) . '</td>
					<td class="text-end">' . format_number($row['thanhtien'] - $row['phithuho']*$row['soluong']) . '</td>
					<td class="text-end text-muted">' . format_number($row['phithuho']) . '</td>
				</tr>';
			}

			$phisanbay 	+= $row['phisanbay'];
			$phikhac 	+= $row['phikhac'];
			$tonggiaban += $row['dongia'] * $row['soluong'];
			$tongdv 	+= $row['phidv'] * $row['soluong'];
			$tongthue 	+= $row['tienthue'];
			$tongthuho 	+= $row['phithuho'] * $row['soluong'];

			// Map input API
			$suffixItemName = ($row['mahang'] == 'PK') ? '' : ' ' . $row['name'];
			$array_item['items'][] = [
				'itemCode'			=> $row['mahang'],
				'itemName' 			=> $this->getItemName($row['mahang']) . $suffixItemName,
				'itemUnit' 			=> $this->bean->loaihoadon == 0 ? 'Vé' : '',
				'itemQuantity' 		=> $row['soluong'],
				'itemPrice' 		=> $row['dongia'],
				// 'itemVatRate' 	 	=> round($row['tienthue'] / $row['dongia'], 2) * 100,
				'itemVatRate' 	 	=> $row['thuesuat'] * 100,
				'itemVatAmnt' 	 	=> $row['tienthue'],
				'itemAmountNoVat' 	=> $row['thanhtien'] - $row['phithuho']*$row['soluong'] - $row['tienthue'],
			];
		}

		// Tách và hiển thị phí thu hộ (Phí khác hoặc Phí sân bay)
		$extra_qty = 0;
		if($this->bean->loaihoadon == 0 && $tongthuho > 0) {
			$phisanbay = $tongthuho - $phikhac;

			if($phisanbay > 0 ) {
				$extra_qty++;
				$html .= '<tr>
					<td class="text-center">' . (++$i) . '</td>
					<td class="text-start"><b>Phí sân bay</b></td>
					<td class="text-start"></td>
					<td class="text-end">1</td>
					<td class="text-end">'.format_number($phisanbay).'</td>
					<td class="text-end">X</td>
					<td class="text-end">X</td>
					<td class="text-end">'.format_number($phisanbay).'</td>
					<td class="text-end">X</td>
				</tr>';

				// Map input API
				$array_item['items'][] = [
					// 'itemCode'			=> 'PS',
					// 'itemName' 			=> 'Phí sân bay',
					'itemCode'			=> 'PK',
					'itemName' 			=> 'Phí khác',
					'itemUnit' 			=> 'Vé',
					'itemQuantity' 		=> 1,
					'itemPrice' 		=> $phisanbay,
					'itemVatRate' 	 	=> -1, // KCT (Không chịu thuế)
					'itemVatAmnt' 	 	=> '',
					'itemAmountNoVat' 	=> $phisanbay,
				];
			}

			if($phikhac > 0 ) {
				$extra_qty++;
				$html .= '<tr>
					<td class="text-center">' . (++$i) . '</td>
					<td class="text-start"><b>Phí khác</b></td>
					<td class="text-start"></td>
					<td class="text-end">1</td>
					<td class="text-end">'.format_number($phikhac).'</td>
					<td class="text-end">X</td>
					<td class="text-end">X</td>
					<td class="text-end">'.format_number($phikhac).'</td>
					<td class="text-end">X</td>
				</tr>';

				// Map input API
				$array_item['items'][] = [
					'itemCode'			=> 'PK',
					'itemName' 			=> 'Phí khác',
					'itemUnit' 			=> 'Vé',
					'itemQuantity' 		=> 1,
					'itemPrice' 		=> $phikhac,
					'itemVatRate' 	 	=> -1, // KCT (Không chịu thuế)
					'itemVatAmnt' 	 	=> '',
					'itemAmountNoVat' 	=> $phikhac,
				];
			}
		}
		elseif($this->bean->loaihoadon == 1 && $tongthuho > 0) {
			$extra_qty++;
			$html .= '<tr>
				<td class="text-center">' . (++$i) . '</td>
				<td class="text-start"><b>Phí khác</b></td>
				<td class="text-start"></td>
				<td class="text-end">1</td>
				<td class="text-end">'.format_number($tongthuho).'</td>
				<td class="text-end">X</td>
				<td class="text-end">'.format_number($tongthuho).'</td>
				<td class="text-end"></td>
			</tr>';

			// Map input API
			$array_item['items'][] = [
				'itemCode'			=> 'PK',
				'itemName' 			=> 'Phí khác',
				'itemUnit' 			=> 'Vé',
				'itemQuantity' 		=> 1,
				'itemPrice' 		=> $tongthuho,
				'itemVatRate' 	 	=> -1, // KCT (Không chịu thuế)
				'itemVatAmnt' 	 	=> '',
				'itemAmountNoVat' 	=> $tongthuho,
			];
		}

		$tonggiaban += $tongthuho;	  
		$html .= '
			<tr class="footer-tr">
				<td class="text-start" colspan="' . $colspan . '">Số dòng = ' . $i . '</td>
				<td class="text-end">' . format_number($this->bean->tongsl + $extra_qty) . '</td>
				<td class="text-end">' . format_number($tonggiaban) . '</td>
				<td class="text-end">' . format_number($tongdv) . '</td>
				<td class="text-end">' . format_number($tongthue) . '</td>
				<td class="text-end">' . format_number($this->bean->tongthanhtoan) . '</td>
				<td class="text-end text-muted">' . format_number($tongthuho) . '</td>
			</tr>';
				  
		$html .= '</table>
			<p style="font-style:italic; font-weight:500; color:red; margin-top:5px">
				Lưu ý: cột <b>Thu hộ</b> đã được tách riêng thành các loại phí, không tính vào cột <b>Thành tiền</b> của mỗi sản phẩm
			</p>
		</div>';
		$this->ss->assign('LINE_ITEMS', $html);

		// Map input API
		$array_item['invSubTotal'] 		= $tonggiaban;
		$array_item['invVatAmount'] 	= $tongthue;
		$array_item['invTotalAmount'] 	= $this->bean->tongthanhtoan;
		$this->populateCustomButtons($array_item);
	}

	public function populateCustomButtons($array_item_invoice) {
		// global $current_user;
		/**
		 * 0 : Mới tạo
		 * 1 : Đã ghi (Ghi trên hệ thống Invoice nhưng chưa ký số)
		 * 2 : Đã ký số
		 * -1 : Hủy (Có thể đã ký hoặc chưa ký)
		 */

		// MINH HỒNG VÕ
		if($this->bean->company_unit == 'MHV') {
			// Ghi/Cập nhật hóa đơn
			if($this->bean->tinhtrang == '0' || $this->bean->tinhtrang == '1') {
				$text = $text_button = '';
				if($this->bean->tinhtrang == '0') {
					$text = 'Tiến hành <b>tạo</b> hóa đơn tương ứng (chưa kí) trên hệ thống WinInvoice';
					$text_button = 'Ghi hóa đơn';
				}
				else {
					$text = 'Tiến hành <b>cập nhật</b> thông tin trên vào hóa đơn tương ứng (chưa kí) trên hệ thống WinInvoice';
					$text_button = 'Cập nhật hóa đơn';
				}

				$tr = $input = '';
				foreach($array_item_invoice['items'] as $index => $item) {
					$tr .= '<tr>
						<td>'.($index + 1).'</td>
						<td>'.$item['itemName'].'</td>
						<td class="text-center">'.$item['itemUnit'].'</td>
						<td class="text-end">'.$item['itemQuantity'].'</td>
						<td class="text-end">'.format_number($item['itemPrice']).'</td>
						<td class="text-end">'.format_number($item['itemAmountNoVat']).'</td>
						<td class="text-end">'.($item['itemVatRate'] < 0 ? 'X' : ($item['itemVatRate'] . '%')).'</td>
						<td class="text-end">'.format_number($item['itemVatAmnt']).'</td>
					</tr>';

					$input .= '
						<input type="hidden" name="itemCode[]" value="'.$item['itemCode'].'" />
						<input type="hidden" name="itemName[]" value="'.$item['itemName'].'" />
						<input type="hidden" name="itemUnit[]" value="'.$item['itemUnit'].'" />
						<input type="hidden" name="itemQuantity[]" value="'.$item['itemQuantity'].'" />
						<input type="hidden" name="itemPrice[]" value="'.$item['itemPrice'].'" />
						<input type="hidden" name="itemVatRate[]" value="'.$item['itemVatRate'].'" />
						<input type="hidden" name="itemVatAmnt[]" value="'.$item['itemVatAmnt'].'" />
						<input type="hidden" name="itemAmountNoVat[]" value="'.$item['itemAmountNoVat'].'" />
					';
				}

				$arr_ngayhoadon = explode('-', $this->bean->ngayhoadon);
				$identity_number = $this->bean->citizen_id ?? '';
				if(empty($identity_number)) $identity_number = $this->bean->passport_number ?? '';
				$create_invoice_button = '
					<button type="button" name="btnCreateInvoice" id="btnCreateInvoice" class="btn btn-warning" onclick="showDialog(\'dialog-create-invoice\')">'.$text_button.'</button>
					<dialog id="dialog-create-invoice" class="dialog-create-invoice">
						<form method="dialog" name="form-create-invoice">
							<div class="card">
								<div class="card-header">
									<p class="title">Thông tin hóa đơn</p>
									<p class="invDate p-0"><i>Ngày '.$arr_ngayhoadon[0].' tháng '.$arr_ngayhoadon[1].' năm '.$arr_ngayhoadon[2].'</i></p>
									<span class="invRef"><i>Số chứng từ: <b>'.$this->bean->name.'</b></i></span>
								</div>
								<div class="card-body">
									<p>Loại khách hàng <i>(Customer type)</i>: <b>'.($this->bean->loaikh == '1' ? 'Cá nhân' : 'Công ty/Tổ chức').'</b></p>
									<p>Họ tên người mua hàng <i>(Buyer)</i>: <b>'.$this->bean->lienhe.'</b></p>
									<p>Căn cước công dân <i>(ID)</i>: <b>'.$identity_number.'</b></p>
									<p>Tên đơn vị <i>(Company\'s name)</i>: <b>'.$this->bean->tencongty.'</b></p>
									<p>Mã số thuế <i>(Tax code)</i>: <b>'.$this->bean->masothue.'</b></p>
									<p>Địa chỉ <i>(Address)</i>: <b>'.$this->bean->diachi.'</b></p>
									<p>Hình thức thanh toán <i>(Payment method)</i>: <b>'.$this->bean->hinhthuctt.'</b></p>
									<div class="d-flex gap-4">
										<div class="flex-fill">
											<span>Ngân hàng <i>(Bank)</i>: </span>
											<b>'.$this->bean->nganhang.'</b>
											<input type="hidden" name="buyerBank" value="'.$this->bean->nganhang.'" />
										</div>
										<div class="flex-fill">
											<span>Số tài khoản <i>(Bank account)</i>: </span>
											<b>'.$this->bean->sotaikhoan.'</b>
											<input type="hidden" name="buyerAcc" value="'.$this->bean->sotaikhoan.'" />
										</div>
									</div>

									<table class="table table-bordered table-hover items-detail">
										<thead>
											<tr>
												<th>STT</th>
												<th>Tên hàng hóa, dịch vụ</th>
												<th>Đơn vị</th>
												<th>Số lượng</th>
												<th>Đơn giá</th>
												<th>Thành tiền</th>
												<th>Thuế VAT</th>
												<th>Tiền thuế VAT</th>
											</tr>
										</thead>
										<tbody>
											'.$tr.'
											<tr>
												<td colspan="5"><b>TỔNG HỢP</b></td>
												<td class="text-end"><b>'.format_number($array_item_invoice['invSubTotal']).'</b></td>
												<td></td>
												<td class="text-end"><b>'.format_number($array_item_invoice['invVatAmount']).'</b></td>
											</tr>
											<tr>
												<td colspan="5"><b>TỔNG CỘNG</b></td>
												<td class="text-end"><b>'.format_number($array_item_invoice['invTotalAmount']).'</b></td>
												<td colspan="2"></td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>

							<p class="text-confirm">'.$text.'</p>
							<div class="d-flex gap-2 justify-content-end mt-1">
								<input type="button" class="btn btn-primary" name="btn-confirm-create-invoice" id="btn-confirm-create-invoice" value="Xác nhận" title="Xác nhận" />
								<input type="button" class="btn btn-secondary" name="btn-cancel-create-invoice" value="Hủy" title="Hủy" onclick="closeDialog(\'dialog-create-invoice\')" />
								<input type="hidden" name="invID" value="'.$this->bean->id.'" />
								<input type="hidden" name="invRef" value="'.$this->bean->name.'" />
								<input type="hidden" name="invSerial" value="'.$this->bean->kyhieuhd.'" />
								<input type="hidden" name="invDate" value="'.$this->bean->ngayhoadon.'" />
								<input type="hidden" name="invRefDate" value="'.$this->bean->ngayhoadon.'" />
								<input type="hidden" name="invPayment" value="'.$this->bean->hinhthuctt.'" />
								<input type="hidden" name="invCustomer" value="'.$this->bean->loaikh.'" />

								<input type="hidden" name="buyerName" value="'.$this->bean->lienhe.'" />
								<input type="hidden" name="buyerCompany" value="'.$this->bean->tencongty.'" />
								<input type="hidden" name="buyerEmail" value="'.$this->bean->email.'" />
								<input type="hidden" name="buyerTax" value="'.$this->bean->masothue.'" />
								<input type="hidden" name="buyerAddress" value="'.$this->bean->diachi.'" />
								<input type="hidden" name="buyerCitizenIDNumber" value="'.($this->bean->citizen_id ?? '').'" />
								<input type="hidden" name="buyerPassportNumber" value="'.($this->bean->passport_number ?? '').'" />
								'.$input.'
								<input type="hidden" name="invSubTotal" value="'.$array_item_invoice['invSubTotal'].'" />
								<input type="hidden" name="invVatAmount" value="'.$array_item_invoice['invVatAmount'].'" />
								<input type="hidden" name="invTotalAmount" value="'.$array_item_invoice['invTotalAmount'].'" />
							</div>
						</form>
					</dialog>
				';
				$this->ss->assign('HANDLE', $create_invoice_button);
			}

			// Bỏ ghi (Xóa hóa đơn nháp)
			if($this->bean->tinhtrang == '1') {
				$remove_invoice_button = '
					<button type="button" name="btnRemoveInvoice" id="btnRemoveInvoice" class="btn btn-warning" onclick="showDialog(\'dialog-remove-invoice\')">Bỏ ghi sổ</button>
					<dialog id="dialog-remove-invoice" class="dialog-remove-invoice pt-3 pb-3 ps-4 pe-4">
						<form method="dialog" name="form-remove-invoice">
							<h5 style="font-size:1.2rem">Tiến hành bỏ ghi sổ hóa đơn <b>'.$this->bean->name.'</b></h4> 
							<div class="d-flex gap-2 justify-content-end mt-3">
								<input type="button" class="btn btn-primary" name="btn-confirm-remove-invoice" id="btn-confirm-remove-invoice" value="Xác nhận" title="Xác nhận"
									data-inv-ref="'.$this->bean->name.'" 
									data-inv-serial="'.$this->bean->kyhieuhd.'" 
									/>
								<input type="button" class="btn btn-secondary" name="btn-cancel-remove-invoice" value="Hủy" title="Hủy" onclick="closeDialog(\'dialog-remove-invoice\')" />
							</div>
						</form>
					</dialog>
				';
				$this->ss->assign('REMOVE', $remove_invoice_button);
			}

			// Ký số hóa đơn
			if($this->bean->tinhtrang == '1') {
				$winInv = new WinInvoice();
				$resLink = $winInv->get_link([
					'invRef' => $this->bean->name,
					'invSerial' => (!$this->bean->kyhieuhd || empty($this->bean->kyhieuhd)) ? $this->bean->genInvSerial() : $this->bean->kyhieuhd
				]);
				$arrLink = json_decode($resLink, true);

				$view_invoice_button = '<a class="btn btn-secondary" href="https://timchuyenbay.com/tra-cuu?invRef='.$this->bean->name.'" target="_blank">Xem hóa đơn</a>';
				$sign_invoice_button = '
					<button type="button" name="btnSignInvoice" id="btnSignInvoice" class="btn btn-success" onclick="showDialog(\'dialog-sign-invoice\')">Ký hóa đơn</button>
					<dialog id="dialog-sign-invoice" class="dialog-sign-invoice pt-3 pb-3 ps-4 pe-4">
						<form method="dialog" name="form-sign-invoice">
							<h5>Tiến hành ký số hóa đơn <b>'.$this->bean->name.'</b></h4> 
							<p style="font-size:14px; color:red;">
								Vui lòng kiểm tra kỹ lại thông tin 
								<a href="'. ($arrLink['data']['link'] ?? '#') .'" style="text-decoration:underline; " target="_blank">xem tại đây</a>
								trước khi thực hiện
							</p>
							<div class="d-flex gap-2 justify-content-end mt-3">
								<input type="button" class="btn btn-primary" name="btn-confirm-sign-invoice" id="btn-confirm-sign-invoice" value="Xác nhận" title="Xác nhận" />
								<input type="button" class="btn btn-secondary" name="btn-cancel-sign-invoice" value="Hủy" title="Hủy" onclick="closeDialog(\'dialog-sign-invoice\')" />
							</div>
						</form>
					</dialog>
				';
				$this->ss->assign('SIGN', $sign_invoice_button);
			}

			// Xem hóa đơn
			if($this->bean->tinhtrang == '1' || $this->bean->tinhtrang == '2') {
				$view_invoice_button = '<a class="btn btn-secondary" href="https://timchuyenbay.com/tra-cuu?invRef='.$this->bean->name.'" target="_blank">Xem hóa đơn</a>';
				$this->ss->assign('VIEW', $view_invoice_button);
			}
		}

		// Chuyển trạng thái Đã ký
		$sign_tp = '';
		if($this->bean->tinhtrang == '0' || $this->bean->tinhtrang == '1') {
			$sign_tp .= '</form>
			<form action="index.php" name="frmSignTP" id="frmSignTP" method="post">
				<input type="hidden" name="module" value="EC_HoaDonBan" />
				<input type="hidden" name="action" value="Save" />
				<input type="hidden" name="record" value="'.$this->bean->id.'" />
				<input type="hidden" name="return_module" value="EC_HoaDonBan" />
				<input type="hidden" name="return_action" value="DetailView" />
				<input type="hidden" name="return_id" value="'.$this->bean->id.'" />
				<input type="hidden" name="tinhtrang" value="2" />
				<input type="hidden" name="is_signed" value="1" />
				<input type="hidden" name="sohoadon" value="'.$this->bean->sohoadon.'" />
				<input type="submit" class="btn btn-success" name="btnSignTp" value="Đã kí" title="Đã kí" />
			</form>';
		}
		$this->ss->assign('SIGNTP', $sign_tp);
		
		// Hủy hóa đơn
		if($this->bean->tinhtrang != '-1') {
			$cancel_invoice_button = '
				<input type="button" name="btnCancelInvoice" id="btnCancelInvoice" class="btn btn-danger" value="Hủy hóa đơn" title="Hủy hóa đơn" />
			</form>
			<form class="frmCancelInvoice" action="index.php" method="post" name="frmCancelInvoice" id="frmCancelInvoice">
				<input type="hidden" name="record_name" value="'. $this->bean->name .'" />
				<input type="hidden" name="record_serial" value="'.$this->bean->kyhieuhd.'" />
				<input type="hidden" name="is_signed" value="'.$this->bean->is_signed.'" />
				<input type="hidden" name="company_unit" value="'.$this->bean->company_unit.'" />

				<div id="dlgCancelInvoice" style="display:none;" title="Hủy hóa đơn">
					<table cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td style="text-align:left; vertical-align:top;">
								<textarea id="txtCancelInvoice" name="txtCancelInvoice" rows="10" style="font-size:13px">'.$this->bean->description.'</textarea>
							</td>
						</tr>
						<tr>
							<td class="text-end">
								<div class="d-flex align-items-center gap-2 justify-content-end mt-2">
									<input type="button" class="btn btn-primary" name="btn-confirm-cancel__invoice" id="btn-confirm-cancel__invoice" value="Đồng ý" title="Đồng ý" />
									<input type="button" class="btn btn-danger" name="btn-cancel__invoice" id="btn-cancel__invoice" value="Hủy" title="Hủy" />
								</div>
							</td>
						</tr>
					</table>
				</div>
			</form>';
			$this->ss->assign('HUYHOADON', $cancel_invoice_button);
		}
	}
	
	public function getItemName($code) {
		$arr = [
			'VMB_QN' => 'Vé máy bay',
			'VMB_QT' => 'Vé máy bay Quốc tế',
			'PD' => 'Phí đổi vé máy bay',
			'PHL' => 'Phí mua hành lý',
			'PMG' => 'Phí mua ghế',
			'PK' => 'Phí khác'
		];
		return $arr[$code] ?? '';
	}

	/** 
	 * Tách phí thu hộ thành từng loại phí (Not use)
	 *
	 * @author DucPham
	 * @param int $amount : Phí thu hộ
	 * @param string $booking_id
	 * @param string $code : Số vé hoặc PNR
	 * @return array
	 */
	public function separateCollectionFees($amount, $booking_id, $code) {
		$result = array('airport_fee' => 0, 'other_fee' => 0);
		if(empty($amount) || empty($code) || empty($booking_id) || $amount == 0) return $result;
		$result['airport_fee'] = $amount;

		// PN, TH, HNH
		$supplier_id = '("7eafb1bc-6ac2-3816-3ea9-6455f638436e", "57c6cf4b-2f41-485d-636b-5f166aef2e92", "ebdf163a-7b85-30bf-62be-5a4af5a1166c")';

		// Kiểm tra code vé là chiều đi hay chiều về
		$sql1 = 'SELECT eticket_outbound, eticket_inbound, pnr_outbound, pnr_inbound 
				FROM ec_booking_passengers
				WHERE booking_id = "'.$booking_id.'" AND deleted = 0';
		$res1 = $this->bean->db->query($sql1);
		$direction = null;
		$is_round_trip = false;
		while($row = $this->bean->db->fetchByAssoc($res1)) {
			if($row['eticket_outbound'] == $code || $row['pnr_outbound'] == $code) $direction = '0'; // Lượt đi
			elseif($row['eticket_inbound'] == $code || $row['pnr_inbound'] == $code) $direction = '1'; // Lượt về

			// Kiểm tra code vé khứ hồi
			if(($row['eticket_outbound'] == $code || $row['pnr_outbound'] == $code) && ($row['eticket_inbound'] == $code || $row['pnr_inbound'] == $code))
				$is_round_trip = true;
		}

		// Lấy phí admin chiều tương ứng với code vé
		if(!is_null($direction)) {
			$objBooking = new EC_Flight_Bookings();
			$objBooking->retrieve($booking_id);

			$other_fee = 0;
			if(($direction == '0' && ($objBooking->airline == 'VNA' || $objBooking->airline == 'VNP'))
				|| ($direction == '1' && ($objBooking->airline_inbound == 'VNA' || $objBooking->airline_inbound == 'VNP'))
			) {
				$sql2 = 'SELECT DISTINCT admin_fee
						FROM ec_booking_details
						WHERE booking_id = "'.$booking_id.'" 
							AND direction = "'.$direction.'"
							AND supplier_id IN'.$supplier_id.'
							AND vat_admin = 0 AND admin_fee > 0
							AND deleted = 0';

				$res2 = $this->bean->db->query($sql2);
				while($row = $this->bean->db->fetchByAssoc($res2)) {
					$other_fee = $row['admin_fee'];
				}
			}

			if($is_round_trip) $other_fee *= 2;
			if($amount <= $other_fee) {
				$result['other_fee'] = $amount;
				$result['airport_fee'] = 0;
			}
			else {
				$result['other_fee'] = $other_fee;
				$result['airport_fee'] = $amount - $other_fee;
			}
		}
		
		return $result;
	}
}
