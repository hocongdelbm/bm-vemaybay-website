<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/**
 * Related receipt/refund voucher rendering.
 *
 * Used by EC_Flight_BookingsViewDetail. Methods are kept close to the
 * legacy implementation to preserve the old business behavior.
 */
trait VoucherTrait
{
	private function renderRelatedVoucherTableHeader()
	{
		return '<table id="tbl_pax" border="0" cellpadding="0" cellspacing="0" class="table-config table-details__booking">
                    <thead>
                        <tr>
                            <th scope="col" width="5%">STT</th>
                            <th scope="col" width="12%">Ngày chứng từ</th>
                            <th scope="col" width="12%">Loại phiếu</th>
                            <th scope="col" width="12%">Tên phiếu</th>
                            <th scope="col" width="12%">Tình trạng</th>
                            <th scope="col" width="12%">Số tiền</th>
                            <th scope="col">Ghi chú</th>
                        </tr>
                    </thead>';
	}

	private function queryRelatedVoucherRows($db)
	{
		$sql = "SELECT id, name, rv_status, amount, description, ngaychungtu, guest_name, loai_thu
				FROM ec_receipt_voucher
				WHERE booking_id = '" . $this->bean->id . "'
				AND deleted = 0		
		";

		$booking_id = $db->quote($this->bean->id);

		$sql = "(
					SELECT
						id,
						name,
						rv_status as status,
						'receipt_voucher_status_list' AS status_list,
						'receipt_voucher_status_color_list' AS status_list_color,
						amount,
						description,
						ngaychungtu,
						'EC_Receipt_Voucher' AS parent_type
					FROM ec_receipt_voucher
					WHERE booking_id = '$booking_id'
					AND deleted = 0
				)
				UNION ALL
				(
					SELECT
						id,
						name,
						tinhtrang AS status,
						'tinhtranghoanve_list' AS status_list,
						'tinhtranghoanvecolor_list' AS status_list_color,
						tongtienkhach AS amount,
						description,
						ngaychungtu,
						'EC_HoanVe' AS parent_type
					FROM ec_hoanve
					WHERE booking_id = '$booking_id'
					AND deleted = 0
					)
					ORDER BY ngaychungtu DESC";

		return $db->query($sql);
	}

	private function renderRelatedVoucherRow($row, $i)
	{
		$loai_phieu = '';
		if ($row['parent_type'] == 'EC_HoanVe') {
			$loai_phieu = 'Phiếu hoàn';
		} else if ($row['parent_type'] == 'EC_Receipt_Voucher') {
			$loai_phieu = 'Phiếu thu';
		}

		return '<tr>
					<td data-label="STT" class="text-center">' . $i . '</td>
					<td data-label="Ngày chứng từ" class="text-center">
							' . date('d-m-Y', strtotime($row['ngaychungtu'])) . '
					</td>
					<td data-label="Loại phiếu" class="text-center">
						' . $loai_phieu . '
					</td>
					<td data-label="Tên phiếu" class="text-center">
						<a href="index.php?module=' . $row['parent_type'] . '&action=DetailView&record=' . $row['id'] . '" target="_blank">
							' . $row['name'] . '
						</a>
					</td>
					<td data-label="Tình trạng" class="text-center fw-semibold" style="color:' . $GLOBALS['app_list_strings'][$row['status_list_color']][$row['status']] . ';">' . $GLOBALS['app_list_strings'][$row['status_list']][$row['status']] . '</td>
					<td data-label="Số tiền" class="text-center">' . format_number($row['amount']) . '</td>
					<td data-label="Ghi chú" class="text-start text-wrap">' . $row['description'] . '</td>
				</tr>';
	}

	private function renderEmptyRelatedVoucherRow()
	{
		return '<tr>
					<td colspan="8" class="text-start fw-semibold">Không có chứng từ liên quan.</td>
				</tr>';
	}


	public function getVoucherApplied($booking_id = '')
	{
		// Lấy danh sách voucher đã áp dụng cho booking hiện tại hoặc booking được truyền vào.
		if (!$booking_id || empty($booking_id))
			$booking_id = $this->bean->id;

		$sql = "SELECT 
					bv.booking_id,
					bv.voucher_id,
					v.name AS code,
					v.type,
					v.status,
					v.campaign_name,
					bv.discount_amount
				FROM bookings_vouchers bv
					LEFT JOIN ec_vouchers v ON v.id = bv.voucher_id
				WHERE bv.booking_id = '$booking_id'
					AND bv.deleted = 0";

		$res = $this->bean->db->query($sql);
		$results = [];
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$results[] = $row;
		}
		return $results;
	}

}
