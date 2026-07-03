{literal}
<script>
	$(document).ready(function() {
		// REPORT TERM LIST CHANGE
		$('#report_term_list').on('change', function () {
			var reportTermList = $('#report_term_list :selected');
			$('#from_date').val(reportTermList.data('fromdate'));
			$('#to_date').val(reportTermList.data('todate'));
			$('#report_term').val(reportTermList.data('term'));
			$('#report_year').val(reportTermList.data('year'));
		});

		// TOGGLE PER-USER BONUS DETAIL ROW
		$('#bonus-report-tbl').on('click', '.js-toggle-bonus-detail', function () {
			$('#bonus-detail-' + $(this).data('user')).toggle();
		});

		// SEARCH BY BOOKING NAME: highlight matches and expand their owner's
		// detail row; nothing is hidden
		$('#booking_search').on('input', function () {
			var keyword = $(this).val().toLowerCase().trim();

			$('.bonus-booking-row').removeClass('booking-search-hit');

			if (keyword === '') {
				return;
			}

			var $matches = $('.bonus-booking-row').filter(function () {
				return String($(this).data('booking-name')).indexOf(keyword) !== -1;
			});

			$matches.addClass('booking-search-hit')
				.closest('.bonus-detail-row').show();

			if ($matches.length) {
				$matches[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
			}
		});
	});
</script>
<style>
	.booking-search-hit td {
		background-color: #fff3cd !important;
	}
</style>
{/literal}

<h1 id="report-title" class="title">BÁO CÁO THƯỞNG</h1>

<div id="bk-bonus-report" class="box-section">
	<form action="index.php" method="post">
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

			<input type="submit" class="cus-button btn btn-primary" name="btnViewReport" id="btnViewReport" value="Xem báo cáo" title="Xem báo cáo"/>
		</div>
	</form>

	<div class="d-inline-flex gap-2 align-items-center mb-3">
		<span class="sublabel">Booking: </span>
		<input type="text" id="booking_search" class="box-input" placeholder="Tìm booking..." autocomplete="off">
	</div>

	<table id="bonus-report-tbl" cellpadding="0" cellspacing="0" class="table-sale_report_tbl table-details__booking">
		{$BONUS_DATA}
	</table>
</div>
