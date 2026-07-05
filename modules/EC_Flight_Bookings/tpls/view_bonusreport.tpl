<link rel="stylesheet" href="modules/EC_Flight_Bookings/css/view_bonusreport.css?v=1.0.6">
<script src="modules/EC_Flight_Bookings/js/view_bonusreport.js?v=1.0.9"></script>

<h1 id="report-title" class="title">BÁO CÁO THƯỞNG</h1>

<div id="bk-bonus-report" class="box-section">
	<form action="index.php" method="get">
		<input type="hidden" name="module" value="EC_Flight_Bookings">
		<input type="hidden" name="action" value="bonusreport">

		<div class="from-to-date--wrap d-inline-flex gap-2 align-items-center mb-3">
			<select class="box-select" id="report_term_list" name="report_term_list">{$REPORT_TERM_LIST}</select>
			
			<input type="hidden" name="report_term" id="report_term" value="{$REPORT_TERM}"/>
			<input type="hidden" name="report_year" id="report_year" value="{$REPORT_YEAR}"/>

			<div class="d-flex gap-2 align-items-center fdate_trigger--wrap">
				<span class="date-search sublabel">Từ ngày: </span>
				<div class="dateTime d-flex gap-2 position-relative">
				    <input class="date_input box-input"type="text" maxlength="10" size="11" tabindex="103" title="" value="{$FROM_DATE}" id="from_date" name="from_date" autocomplete="off">
				    <button class="icon_dateTime" type="button" id="from_date_trigger" onclick="return false;">
					   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
						  <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
						  <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
						</svg>
				    </button>
				    {literal}
					<script type="text/javascript">
						Calendar.setup ({
							inputField : "from_date",
							daFormat : "%d-%m-%Y",
							button : "from_date_trigger",
							singleClick : true,
							dateStr : "",
							step : 1
						});
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

			<div class="d-flex gap-2 align-items-center tdate_trigger--wrap">
				<span class="date-search sublabel">Đến ngày: </span>
				<div class="dateTime d-flex gap-2 position-relative">
					<input  class="date_input box-input" type="text" maxlength="10" size="11" title="" value="{$TO_DATE}" id="to_date" name="to_date" autocomplete="off">
					<button class="icon_dateTime" type="button" id="to_date_trigger" onclick="return false;">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
							<path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
							<path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
						</svg>
					</button>
					{literal}
						<script type="text/javascript">
							Calendar.setup ({
								inputField : "to_date",
								daFormat : "%d-%m-%Y",
								button : "to_date_trigger",
								singleClick : true,
								dateStr : "",
								step : 1
							});
						</script>
					{/literal}
				</div>
			</div>

			<div class="d-flex gap-2 align-items-center">
				<span class="sublabel">Booking: </span>
				<input type="text" id="booking_search" class="box-input" placeholder="Tìm booking..." autocomplete="off">
			</div>

			<input type="submit" class="cus-button btn btn-primary" name="btnViewReport" id="btnViewReport" value="Xem báo cáo" title="Xem báo cáo"/>
		</div>
	</form>

	<table id="bonus-report-tbl" cellpadding="0" cellspacing="0" class="table-sale_report_tbl table-details__booking">
		{$BONUS_DATA}
	</table>

	<div id="parent-bonus-modal" class="bonus-modal-overlay" style="display: none;">
		<div class="bonus-modal">
			<div class="bonus-modal-header">
				<span id="parent-bonus-name"></span>
				<button type="button" class="bonus-modal-close" title="Đóng">&times;</button>
			</div>
			<div class="bonus-modal-body">
				<table class="bonus-modal-tbl" cellpadding="0" cellspacing="0">
					<tr>
						<td>Số vé</td>
						<td class="text-end" id="parent-bonus-qty"></td>
					</tr>
					<tr>
						<td>
							Tổng doanh thu
							<span class="formula-note">
								<span class="formula-desc">Không tính chiết khấu</span>
							</span>
						</td>
						<td class="text-end" id="parent-bonus-revenue"></td>
					</tr>
					<tr>
						<td>
							Tổng giá vốn
							<span class="formula-note">
								<span class="formula-desc">Gồm phí xuất vé</span>
							</span>
						</td>
						<td class="text-end" id="parent-bonus-cost"></td>
					</tr>
					<tr>
						<td>
							Tổng doanh số
						</td>
						<td class="text-end" id="parent-bonus-profit"></td>
					</tr>
					<tr>
						<td>
							Doanh số bình quân/vé
							<span class="formula-note">
								<span class="formula-num" id="parent-bonus-avgprofit-note"></span>
							</span>
						</td>
						<td class="text-end" id="parent-bonus-avgprofit"></td>
					</tr>
					<tr>
						<td>
							Thưởng mỗi vé
							<span class="formula-note">
								<span class="formula-num" id="parent-bonus-perticket-note"></span>
							</span>
						</td>
						<td class="text-end" id="parent-bonus-per-ticket"></td>
					</tr>
					<tr>
						<td>
							Tổng thưởng
							<span class="formula-note">
								<span class="formula-num" id="parent-bonus-total-note"></span>
							</span>
						</td>
						<td class="text-end" id="parent-bonus-total"></td>
					</tr>
					<tr>
						<td>
							Tổng thưởng trực tiếp (70%)
							<span class="formula-note">
								<span class="formula-num" id="parent-bonus-direct-note"></span>
							</span>
						</td>
						<td class="text-end" id="parent-bonus-direct"></td>
					</tr>
					<tr>
						<td>
							Tổng thưởng gián tiếp (30%)
							<span class="formula-note">
								<span class="formula-num" id="parent-bonus-indirect-note"></span>
							</span>
						</td>
						<td class="text-end" id="parent-bonus-indirect"></td>
					</tr>
					<tr>
						<td>
							Tổng KPI gián tiếp
						</td>
						<td class="text-end" id="parent-bonus-indirectkpi"></td>
					</tr>
					<tr>
						<td>
							Thưởng gián tiếp mỗi KPI
							<span class="formula-note">
								<span class="formula-num" id="parent-bonus-indirect-perkpi-note"></span>
							</span>
						</td>
						<td class="text-end" id="parent-bonus-indirect-perkpi"></td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>
