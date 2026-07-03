<script src="custom/jqueryui/plugins/formatNumber.js"></script>
<script src="modules/EC_TongHop/js/report_sales_issue.js"></script>

<div class="title-wrap d-flex align-items-center justify-content-between gap-2">
	<h1 class="title d-flex gap-2 align-items-center">
		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#012970" class="bi bi-trophy" viewBox="0 0 16 16">
			<path d="M2.5.5A.5.5 0 0 1 3 0h10a.5.5 0 0 1 .5.5c0 .538-.012 1.05-.034 1.536a3 3 0 1 1-1.133 5.89c-.79 1.865-1.878 2.777-2.833 3.011v2.173l1.425.356c.194.048.377.135.537.255L13.3 15.1a.5.5 0 0 1-.3.9H3a.5.5 0 0 1-.3-.9l1.838-1.379c.16-.12.343-.207.537-.255L6.5 13.11v-2.173c-.955-.234-2.043-1.146-2.833-3.012a3 3 0 1 1-1.132-5.89A33.076 33.076 0 0 1 2.5.5zm.099 2.54a2 2 0 0 0 .72 3.935c-.333-1.05-.588-2.346-.72-3.935zm10.083 3.935a2 2 0 0 0 .72-3.935c-.133 1.59-.388 2.885-.72 3.935zM3.504 1c.007.517.026 1.006.056 1.469.13 2.028.457 3.546.87 4.667C5.294 9.48 6.484 10 7 10a.5.5 0 0 1 .5.5v2.61a1 1 0 0 1-.757.97l-1.426.356a.5.5 0 0 0-.179.085L4.5 15h7l-.638-.479a.501.501 0 0 0-.18-.085l-1.425-.356a1 1 0 0 1-.757-.97V10.5A.5.5 0 0 1 9 10c.516 0 1.706-.52 2.57-2.864.413-1.12.74-2.64.87-4.667.03-.463.049-.952.056-1.469H3.504z"/>
		</svg>
		DS THEO NGÀY XUẤT VÉ
	</h1>
	<svg xmlns="http://www.w3.org/2000/svg" id="filter_report" width="32" height="32" fill="currentColor" class="bi bi-filter d-xxl-none d-xl-none d-lg-none d-block" viewBox="0 0 16 16">
		<path d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5"/>
	</svg>
</div>

<ul class="currentsales-note alert alert-info text-dark fw-semibold">
	<li>- Thống kê các Booking đã <span class="fw-semibold" style="color:#0a58ca;">Hoàn tất</span></li>
	<li>- Doanh số lấy theo <span class="fw-semibold text-danger">ngày xuất vé</span></li>
	<li>- Doanh số & Phiếu thu: Cột "tổng doanh số" bên BC <span class="fw-semibold text-danger">doanh thu trong ngày</span></li>
	{if $CAN_EDIT_AD_COST}
		<li>- Chi phí quảng cáo: Nhập <span class="fw-semibold">theo từng ngày</span>. Chỉ được <span class="fw-semibold" style="color:#0a58ca;">Sửa</span> trong <span class="fw-semibold text-danger">3 ngày gần nhất</span>. Thời gian là khoảng nhiều ngày thì hiển thị tổng chi phí qc các ngày trong khoảng đó.</li>
	{/if}
	<li>- Hover chuột vào ô Header để xem ý nghĩa của cột đang xem.</li>
</ul>

<div class="box-section position-relative">
	<div class="overlay-mobile"></div>

	<form action="index.php" method="post" name="search_form" id="ec_search_form">
		<input type="hidden" name="module" value="{$MODULE_NAME}" />
		<input type="hidden" name="action" value="{$MODULE_ACTION}" />
		<input type="hidden" id="grp_seperator" name="grp_seperator" value="{$GRP_SEPERATOR}" />
		<input type="hidden" id="dec_seperator" name="dec_seperator" value="{$DEC_SEPERATOR}" />
		<input type="hidden" id="sig_digits" name="sig_digits" value="{$SIG_DIGITS}" />

		<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-dash-lg search_form--dash d-xl-none d-lg-none d-block" viewBox="0 0 16 16">
			<path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8"></path>
		</svg>

		<div class="action--wrap d-flex align-items-center gap-4">
			<select class="box-select" id="date_select" name="date_select">{$DATE_OPTION}</select>
			<div class="from-to-date--wrap d-inline-flex gap-2 align-items-center">
				<div class="d-flex gap-2 align-items-center date_trigger--wrap fdate_trigger--wrap">
					<span class="text-label">Từ ngày: </span>    
					<div class="dateTime d-flex gap-2 position-relative">
						<input class="date_input box-input" type="text" maxlength="10" size="11" tabindex="103" title="" value="{$FROM_DATE_VALUE}" id="from_date" name="from_date" autocomplete="off">
						<button class="icon_dateTime" type="button" id="fdate_trigger" onclick="return false;">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
								<path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
								<path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
							</svg>
						</button>
						{literal}
							<script type="text/javascript">
								Calendar.setup({
										inputField: "from_date",
										daFormat: "%d-%m-%Y",
										button: "fdate_trigger",
										singleClick: true,
										dateStr: "",
										step: 1,
										position: [230, 202],
									}
								);
							</script>
						{/literal}
					</div>
				</div>
		
				<svg width="40" height="20" fill="none">
				<g clip-path="url(#icon_arrow_flight_long_svg__clip0)" stroke="#718096" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
					<path d="M33.5 8.5L36 11M4 11h32"></path>
				</g>
				<defs>
					<clipPath id="icon_arrow_flight_long_svg__clip0">
						<path fill="#fff" d="M0 0h40v20H0z"></path>
					</clipPath>
				</defs>
				</svg>
		
				<div class="d-flex gap-2 align-items-center date_trigger--wrap tdate_trigger--wrap">
					<span class="text-label">Đến ngày: </span>    
					<div class="dateTime d-flex gap-2 position-relative">
						<input  class="date_input box-input" type="text" maxlength="10" size="11" title="" value="{$TO_DATE_VALUE}" id="to_date" name="to_date" autocomplete="off">
						<button class="icon_dateTime" type="button" id="tdate_trigger" onclick="return false;">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
								<path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
								<path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
							</svg>
						</button>
						{literal}
							<script type="text/javascript">
								Calendar.setup({
										inputField: "to_date",
										daFormat: "%d-%m-%Y",
										button: "tdate_trigger",
										singleClick: true,
										dateStr: "",
										step: 2
									}
								);
							</script>
						{/literal}
					</div>
				</div>
			</div>
			<div class="button-action--wrap">
				<input type="submit" id="btnView" name="btnView" class="btn btn-primary button-action" value="Tìm kiếm" title="Tìm kiếm" />
				<input type="button" id="btnSearch_cancel" name="search_cancel" class="btn btn-secondary button-action--cancel d-xl-none d-lg-none d-block" value="Hủy bỏ" title="Hủy bỏ"/>
			</div>	
		</div>
	</form>

	<table id="tbl-doanhsohientai" class="table-current-sales table-details__booking mt-3" border="0" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th width="12%" style="background-color: #068FFF; color: #fff" title="Thời gian">Thời gian</th>
                <th width="8%" style="background-color: #068FFF; color: #fff" title="Doanh số nội địa">D/s Nội địa</th>
                <th width="8%" style="background-color: #068FFF; color: #fff" title="Doanh số quốc tế">D/s Quốc tế</th>
                <th width="8%" style="background-color: #068FFF; color: #fff" title="Doanh số vé">Doanh số vé</th>
                <th width="8%" style="background-color: #068FFF; color: #fff" title="Doanh số & phiếu thu">Doanh số & Phiếu thu</th>
                <th width="8%" style="background-color: #068FFF; color: #fff" title="Chi phí quảng cáo">Chi phí QC</th>
                <th width="5%" style="background-color: #068FFF; color: #fff" title="Tổng số lượng BK hoàn tất theo ngày xuất vé">Booking</th>
                <th width="5%" style="background-color: #068FFF; color: #fff" title="Tổng số lượng vé hoàn tất theo ngày xuất vé">Số vé</th>
                <th width="5%" style="background-color: #068FFF; color: #fff" title="Số lượng BK 2-3 vé hoàn tất theo ngày xuất vé">BK 2-3 vé</th>
                <th width="5%" style="background-color: #068FFF; color: #fff" title="Số lượng BK 4-6 vé hoàn tất theo ngày xuất vé">BK 4-6 vé</th>
                <th width="5%" style="background-color: #068FFF; color: #fff" title="Số lượng BK quốc tế hoàn tất theo ngày xuất vé">BK <br> Quốc tế</th>
                <th width="8%" style="background-color: #068FFF; color: #fff" title="Số lượng BK tham khảo hoàn tất / Tổng số lượng BK tham khảo">BK Tham khảo</th>
                <th width="8%" style="background-color: #068FFF; color: #fff" title="BK hoàn tất / Gọi đến tạo BK / Tổng cuộc gọi đến. VD: 3 / 4 / 5 = có 5 cuộc gọi đến, 4 cuộc tạo BK, và 3 BK đã hoàn tất từ 4 BK đó">Cuộc gọi đến</th>
                <th width="5%" style="background-color: #068FFF; color: #fff">Gọi nhỡ</th>
            </tr>
        </thead>
        <tbody>
            {$DATA}
        </tbody>
	</table>
</div>

{if $CAN_EDIT_AD_COST}
<div class="modal fade" id="modalDailyAdCost" tabindex="-1" aria-labelledby="modalDailyAdCostLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<form id="frmDailyAdCost" name="frmDailyAdCost">
				<div class="modal-header">
					<h2 class="modal-title fs-5" id="modalDailyAdCostLabel">Chi phí quảng cáo — ngày <span id="modal_daily_ad_cost_date_label"></span></h2>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<input type="hidden" id="modal_daily_ad_cost_date" name="cost_date" value="" />
					<div class="mb-2">
						<label for="modal_daily_ad_cost_amount" class="form-label">Số tiền (VNĐ)</label>
						<input type="text" class="form-control box-input text-end" id="modal_daily_ad_cost_amount" name="amount" autocomplete="off" placeholder="0" />
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
					<button type="submit" class="btn btn-primary">Lưu</button>
				</div>
			</form>
		</div>
	</div>
</div>
{/if}

{if $DATA2 != ''}
<h1 class="title my-3">Booking chưa xuất vé</h1>
<p class="alert alert-info text-dark fw-semibold">Lưu ý: Booking ở tình trạng <span class="fw-semibold" style="color:#26A86A">Xác nhận</span> hoặc <span class="fw-semibold" style="color:#CF822E">Xuất vé</span> <strong>CHƯA</strong> được ghi nhận doanh số theo ngày xuất vé. Vui lòng <span class="fw-semibold" style="color:#0a58ca;">Hoàn tất</span> booking!</p>

<div class="box-section">
    <table id="tbl-chuaxuatve" class="table-chuaxuatve table-details__booking" border="0" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th width="6%" align="center" style="background-color: #068FFF; color: #fff">Booking</th>
                <th width="9%" align="center" style="background-color: #068FFF; color: #fff">Hành trình</th>
                <th width="5%" align="center" style="background-color: #068FFF; color: #fff" class="hide-mobile">Hãng</th>
                <th width="8%" align="center" style="background-color: #068FFF; color: #fff" class="hide-mobile">Ngày bay</th>
                <th width="7%" align="center" style="background-color: #068FFF; color: #fff" class="hide-mobile">Tình trạng</th>
                <th width="3%" align="center" style="background-color: #068FFF; color: #fff">Vé</th>
                <th width="8%" align="center" style="background-color: #068FFF; color: #fff">Doanh số</th>
                <th width="8%" align="center" style="background-color: #068FFF; color: #fff" class="hide-mobile">Doanh thu</th>
                <th width="15%" align="center" style="background-color: #068FFF; color: #fff" class="hide-mobile">Mô tả</th>
                <th width="6%" align="center" style="background-color: #068FFF; color: #fff" class="hide-mobile">Giao cho</th>
                <th width="8%" align="center" style="background-color: #068FFF; color: #fff" class="hide-mobile">Ngày tạo</th>
            </tr>
        </thead>
        {$DATA2}
        <tr class="footer-tr">
			<td align="left" colspan="2" class="text-start fw-bold">Số dòng = {$SODONG}</td>
            <td align="center" colspan="3" class="hide-mobile">&nbsp;</td>
			<td align="center">{$TONGSOVECHUAXUAT}</td>
			<td align="right" class="text-end fw-bold color-red">{$TONGTIENDOANHSO}</td>
			<td align="right" class="text-end fw-bold color-red hide-mobile">{$TONGTIENCHUAXUAT}</td>
            <td align="center" class="hide-mobile">&nbsp;</td>
            <td align="center" class="hide-mobile">&nbsp;</td>
            <td align="center" class="hide-mobile">&nbsp;</td>
        </tr>
    </table>
</div>
{/if}

<div id="infor_booking_inter__title"></div>
<div id="infor_booking_inter__content"></div>
