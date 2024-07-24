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

			// lưu ghi chú từ bảng doanh số
			$(".save-note-btn").click(function() {
				var note_val = $(this).prev().val();
				if(note_val != '') {
					$.ajax({
						url: "index.php",
						data: {}
					});
				}
			});
		});
	</script>
{/literal}

<h1 id="report-title" class="title">
	HIỆU QUẢ CÔNG VIỆC
</h1>

{if $DETAIL}
<div class="d-flex align-items-center gap-2">
	<h4 class="sub-heading-title">
		Từ ngày: {$FROM_DATE} 
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
		Đến ngày: {$TO_DATE}</h4>
	<h4 class="sub-heading-title">Nhân viên: {$EMPOYEE_NAME}</h4>
</div>
{/if}

<div id="bk-sale-report" class="box-section">
	{if !$DETAIL}
	<form action="index.php" method="post">
		<input type="hidden" name="module" value="EC_Flight_Bookings">
		<input type="hidden" name="action" value="bksalereport">

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
	{/if}

	<table id="sale-report-tbl" cellpadding="0" cellspacing="0" class="table-sale_report_tbl table-details__booking">
		{$DOANHSO}
	</table>
</div>