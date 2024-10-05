{literal}
	<script>
		$(document).ready(function() {
			Calendar.setup ({
				inputField : "from_date",
				daFormat : "%d-%m-%Y",
				button : "from_date_trigger",
				singleClick : true,
				dateStr : "",
				step : 1,
				weekNumbers:false
			});
			Calendar.setup ({
				inputField : "to_date",
				daFormat : "%d-%m-%Y",
				button : "to_date_trigger",
				singleClick : true,
				dateStr : "",
				step : 1,
				weekNumbers:false
			});
			// REPORT TERM LIST CHANGE
			$('#report_term_list').on('change', function () {
				var reportTermList = $('#report_term_list :selected');
				$('#from_date').val(reportTermList.data('fromdate'));
				$('#to_date').val(reportTermList.data('todate'));
				$('#report_term').val(reportTermList.data('term'));
				$('#report_year').val(reportTermList.data('year'));
			});
		});			
	</script>
{/literal}
<h1 class="title">BÁO CÁO CHI PHÍ</h1>

<div class="box-section">
<form action="index.php" method="post" name="frmSearch" id="frmSearch" class="payment-report">
	<select id="report_term_list" class="box-select me-2" name="report_term_list">{$REPORT_TERM_LIST}</select>

	<input type="hidden" name="module" value="EC_Payment_Voucher" />
    	<input type="hidden" name="action" value="paymentreport" />
    	<input type="hidden" id="grp_seperator" name="grp_seperator" value="{$GRP_SEPERATOR}" />
	<input type="hidden" id="dec_seperator" name="dec_seperator" value="{$DEC_SEPERATOR}" />
	<input type="hidden" id="sig_digits" name="sig_digits" value="{$SIG_DIGITS}" />

	<div class="d-inline-flex gap-2 align-items-center">
		
		<span class="dateTime d-flex gap-2 position-relative">
			<input autocomplete="off" class="date_input box-input flex-fill" type="text" name="from_date" id="from_date" value="{$POST_FROM_DATE}" title="" size="11" maxlength="10" />
			<button class="icon_dateTime" type="button" id="from_date_trigger" onclick="return false;">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
					<path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
					<path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
				</svg>
			</button>
		</span>

		<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
			<path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"></path>
		</svg>

		<span class="dateTime d-flex gap-2 position-relative">
			<input autocomplete="off" class="date_input box-input flex-fill" type="text" name="to_date" id="to_date" value="{$POST_TO_DATE}" title="" size="11" maxlength="10" />
			<button class="icon_dateTime" type="button" id="to_date_trigger" onclick="return false;">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
					<path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
					<path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
				</svg>
			</button>
		</span>
		<input type="submit" name="btnSearch" class="btn btn-primary" id="btnSearch" value="Tìm" title="Tìm" />
	</div>
</form>
<table id="payment-list" class="table-details__booking table-expense-report mt-2" cellspacing="0" cellpadding="0">
	<thead>
		<th width="10%">STT</th>
		<th width="50%">Loại chi</th>
		<th width="40%">Số tiền</th>
	</thead>
	<tbody>
		{$PAYMENT_LIST_TBL}
		<tr class="footer-tr">
			<td></td>
			<td class="text-start"><b>Tổng</b></td>
			<td class="text-end text-danger fw-bold"><b>{$TOTAL_PAID}</b></td>
		</tr>
	</tbody>
</table>
</div>