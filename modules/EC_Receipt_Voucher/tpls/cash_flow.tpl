<script src="custom/jqueryui/plugins/formatNumber.js"></script>
{literal}
<script>	
	function calculateTotalCredit(){
		var total_amount = 0;
		$('.total-credit').each(function(index){
			total_amount += unformatNumber($(this).text());
		});
		$('#total-amount').text(formatNumber(total_amount));
		$('.total-debt').each(function(index){
			total_amount -= unformatNumber($(this).text());
		});
		$('#total-amount-final').text(formatNumber(total_amount));
		return total_amount;
	}
</script>
{/literal}

<div class="title-wrap d-flex align-items-center justify-content-between gap-2">
	<h1 class="title d-flex align-items-center gap-2">
		<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-graph-up-arrow" viewBox="0 0 16 16">
			<path fill-rule="evenodd" d="M0 0h1v15h15v1H0V0Zm10 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V4.9l-3.613 4.417a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61L13.445 4H10.5a.5.5 0 0 1-.5-.5Z"/>
		</svg>
		BÁO CÁO DÒNG TIỀN
	</h1>
	<svg xmlns="http://www.w3.org/2000/svg" id="filter_report" width="32" height="32" fill="currentColor" class="bi bi-filter d-xxl-none d-xl-none d-lg-none d-block hide-landscape" viewBox="0 0 16 16">
		<path d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5"/>
	</svg>
</div>

<div class="box-section position-relative mt-0">
	<div class="overlay-mobile"></div>

	<form action="index.php" method="post" name="search_form" id="ec_search_form">
		<input type="hidden" name="module" value="EC_Receipt_Voucher" />
		<input type="hidden" name="action" value="cashflow" />
		<input type="hidden" id="grp_seperator" name="grp_seperator" value="{$GRP_SEPERATOR}" />
		<input type="hidden" id="dec_seperator" name="dec_seperator" value="{$DEC_SEPERATOR}" />
		<input type="hidden" id="sig_digits" name="sig_digits" value="{$SIG_DIGITS}" />
		<input type="hidden" name="vj_agent_id" id="vj_agent_id" value="{$VJ_AGENT_ID}" />
		<input type="hidden" name="vj_agent_pwd" id="vj_agent_pwd" value="{$VJ_AGENT_PWD}" />
		<input type="hidden" name="bl_agent_id" id="bl_agent_id" value="{$BL_AGENT_ID}" />
		<input type="hidden" name="bl_agent_pwd" id="bl_agent_pwd" value="{$BL_AGENT_PWD}" />
		
		<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-dash-lg search_form--dash d-xl-none d-lg-none d-md-none d-block" viewBox="0 0 16 16">
			<path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8"></path>
		</svg>

		<div class="action--wrap d-flex align-items-center gap-4">
			<div class="from-to-date--wrap d-inline-flex gap-2 align-items-center">
				<div class="d-flex gap-2 align-items-center date_trigger--wrap fdate_trigger--wrap">
					<span class="sublabel">Từ ngày: </span>    
					<div class="dateTime d-flex gap-2 position-relative">
						<input class="date_input box-input" type="text" maxlength="10" size="11" tabindex="103" title="" value="{$POST_FROM_DATE}" id="from_date" name="from_date" autocomplete="off">
						<button class="icon_dateTime" type="button" id="from_date_trigger" onclick="return false;">
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
										button: "from_date_trigger",
										singleClick: true,
										dateStr: "",
										step: 1,
										weekNumbers:false
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
					<span class="sublabel">Đến ngày: </span>    
					<div class="dateTime d-flex gap-2 position-relative">
						<input class="date_input box-input" type="text" maxlength="10" size="11" tabindex="103" title="" value="{$POST_TO_DATE}" id="to_date" name="to_date" autocomplete="off">
						<button class="icon_dateTime" type="button" id="to_date_trigger" onclick="return false;">
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
										button: "to_date_trigger",
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
	
			{*{if $DEPARTMENT <> ''}
				<select class="box-select" name="dep_id" id="dep_id" class="box-select">
				{$DEPARTMENT}
				</select>
			{/if}
			<label for="realtime" class="text-label d-flex gap-1 mx-2 align-items-center">
				<input type="checkbox" id="realtime" name="realtime" value="1" checked>
				Realtime
			</label> *}
		</div>

		<div class="button-action--wrap">
			<input type="submit" id="btnSearch" name="btnSearch" class="btn btn-primary" value="Xem" title="Xem"/>
			<input type="button" id="btnSearch_cancel" value="Hủy bỏ" name="search_cancel" class="btn btn-secondary button-action--cancel d-xl-none d-lg-none d-block" title="Hủy bỏ"/>
		</div>
	</form>

	<table class="bank-account-list table-details__booking" cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<th class="bg-yellow hide-mobile" width="5%">STT</th>
				<th class="bg-yellow" width="25%">Số tài khoản</th>
				<th class="bg-yellow" width="20%">Tên tài khoản</th>
				<th class="bg-yellow hide-mobile" width="30%">Tài khoản ngân hàng</th>
				<th class="bg-yellow">Số tiền</th>
			</tr>
		</thead>
		<tbody>
			{$DATA}
		</tbody>
	</table>
</div>