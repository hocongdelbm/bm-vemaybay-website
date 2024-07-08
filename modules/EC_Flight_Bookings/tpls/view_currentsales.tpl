<script src="custom/jqueryui/plugins/formatNumber.js"></script>
{literal}
<style>
	.bg-yellow{
		background: #fff2cc !important;
	}
</style>
<script>
	$(document).ready(function(){
		$(".detail_inter").click(function() {
			$(".infor-booking__domestic--wrap").remove();
			$('.detail_domestic').removeClass('displayed');
			if($(this).hasClass('displayed')){
				$("#infor_booking_inter__title h1.title").text("");
				$(".infor-booking__inter--wrap").remove();
				$(this).removeClass('displayed');
			} else {
				$(this).addClass('displayed');
				$.ajax({
					url: "index.php?entryPoint=entryPointFlightBookings",
					type: "POST",
					data: {
						fdate: $('#from_date').val(),
						tdate: $('#to_date').val(),
						for: "getInfoBookingInter",
					},
					beforeSend: function() {
						$(".container-waiting").show();
					},
					success: function(response) {
						$(".container-waiting").hide();
						$("#infor_booking_inter__title").html('<h1 class="title">Thông tin chi tiết vé quốc tế</h1>');
						$("#infor_booking_inter__title").after(response);
					}
				});
			}
		});

		$(".detail_domestic").click(function() {
			$(".infor-booking__inter--wrap").remove();
			$('.detail_inter').removeClass('displayed');
			if($(this).hasClass('displayed')){
				$("#infor_booking_inter__title h1.title").text("");
				$(".infor-booking__domestic--wrap").remove();
				$(this).removeClass('displayed');
			} else {
				$(this).addClass('displayed');
				$.ajax({
					url: "index.php?entryPoint=entryPointFlightBookings",
					type: "POST",
					data: {
						fdate: $('#from_date').val(),
						tdate: $('#to_date').val(),
						for: "getInfoBookingDomestic",
					},
					beforeSend: function() {
						$(".container-waiting").show();
					},
					success: function(response) {
						$(".container-waiting").hide();
						$("#infor_booking_inter__title").html('<h1 class="title">Thông tin chi tiết vé nội địa</h1>');
						$("#infor_booking_inter__title").after(response);
					}
				});
			}
		});

		$('#btnCheckRemainingCredit').on('click',function(){
			var supplier_select 	= $('#aircode :selected');
			var aircode 			= supplier_select.val();
			var agent_id 			= supplier_select.attr('agent_id');
			var agent_pwd 			= supplier_select.attr('agent_pwd');
			var supplier 			= supplier_select.text();
			$.ajax({
				url:'index.php?entryPoint=entryPointGetRemainingCredit',
				data: 'aircode='+ aircode +'&agent_id='+ agent_id +'&agent_pwd=' + agent_pwd,
				type: 'POST',
				cache: false,
				beforeSend:function(){
					$('#total_credit').text('');
					$('#total_credit').addClass('loading');
				},
				error:function(data){
				},
				success:function(data){
					data = $.parseJSON(data);
					$('#total_credit').text(supplier + ' : ' + formatNumber(data.data.total_credit));
				},
				complete:function(){
					$('#total_credit').removeClass('loading');
				}
			});
		});

		$(document).on("change", "#date_select", function(e) {
			$("#from_date").val($(this).find("option:selected").attr("fromdate"));
			$("#to_date").val($(this).find("option:selected").attr("todate"));

			sessionStorage.setItem('date_select_currentsales', $(this).val());
			sessionStorage.removeItem('optionRadio_currentsales');
		});
		
		$(document).on("change", "input[type=radio][name=optionRadio]", function(e) {
			$('#from_date').val($('input[name=optionRadio]:checked').attr('fromdate'));
			$('#to_date').val($('input[name=optionRadio]:checked').attr('todate'));
			sessionStorage.setItem('optionRadio_currentsales', $(this).val());
			sessionStorage.removeItem('date_select_currentsales');
		});

		// Check sessionStorage - js
		const selectOption 		= document.getElementById('date_select');
		const radioOptions 		= document.getElementsByName('optionRadio');
		const savedSelectOption 	= sessionStorage.getItem('date_select_currentsales');
		if (savedSelectOption) {
			selectOption.value = savedSelectOption;
		} else {
			const savedRadioOption = sessionStorage.getItem('optionRadio_currentsales');
			if (savedRadioOption) {
				radioOptions.forEach(radio => {
					if (radio.value === savedRadioOption) {
						radio.checked = true;
					}
				});
			}
		}
	});
</script>
{/literal}

<h1 class="title d-flex gap-2 align-items-center">
	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#012970" class="bi bi-trophy" viewBox="0 0 16 16">
		<path d="M2.5.5A.5.5 0 0 1 3 0h10a.5.5 0 0 1 .5.5c0 .538-.012 1.05-.034 1.536a3 3 0 1 1-1.133 5.89c-.79 1.865-1.878 2.777-2.833 3.011v2.173l1.425.356c.194.048.377.135.537.255L13.3 15.1a.5.5 0 0 1-.3.9H3a.5.5 0 0 1-.3-.9l1.838-1.379c.16-.12.343-.207.537-.255L6.5 13.11v-2.173c-.955-.234-2.043-1.146-2.833-3.012a3 3 0 1 1-1.132-5.89A33.076 33.076 0 0 1 2.5.5zm.099 2.54a2 2 0 0 0 .72 3.935c-.333-1.05-.588-2.346-.72-3.935zm10.083 3.935a2 2 0 0 0 .72-3.935c-.133 1.59-.388 2.885-.72 3.935zM3.504 1c.007.517.026 1.006.056 1.469.13 2.028.457 3.546.87 4.667C5.294 9.48 6.484 10 7 10a.5.5 0 0 1 .5.5v2.61a1 1 0 0 1-.757.97l-1.426.356a.5.5 0 0 0-.179.085L4.5 15h7l-.638-.479a.501.501 0 0 0-.18-.085l-1.425-.356a1 1 0 0 1-.757-.97V10.5A.5.5 0 0 1 9 10c.516 0 1.706-.52 2.57-2.864.413-1.12.74-2.64.87-4.667.03-.463.049-.952.056-1.469H3.504z"/>
	</svg>
	DOANH SỐ THEO NGÀY XUẤT VÉ
</h1>

<div class="box-section">
<form action="index.php" method="post" name="frmSearch" id="frmSearch">
	<input type="hidden" name="module" value="EC_Flight_Bookings" />
	<input type="hidden" name="action" value="currentsales" />
    	<input type="hidden" id="grp_seperator" name="grp_seperator" value="{$GRP_SEPERATOR}" />
	<input type="hidden" id="dec_seperator" name="dec_seperator" value="{$DEC_SEPERATOR}" />
	<input type="hidden" id="sig_digits" name="sig_digits" value="{$SIG_DIGITS}" />

	<div class="d-flex align-items-center gap-3 action--wrap mb-3">
		<select class="box-select" id="date_select" name="date_select">{$DATE_OPTION}</select>
		<div class="from-to-date--wrap d-inline-flex gap-2 align-items-center">
			<div class="d-flex gap-2 align-items-center fdate_trigger--wrap">
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
	
			<div class="d-flex gap-2 align-items-center tdate_trigger--wrap">
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
	
			<div class="button-wrap">
				<input type="submit" id="btnView" name="btnView" class="btn btn-primary button-action" value="Xem" title="Xem" />
			</div>	
		</div>
		<div class="option-datetime d-flex gap-2 align-items-center">
			<input type="radio" value="yesterday" id="yesterday" class="rd_time form-check-input" name="optionRadio" fromdate="{$YESTERDAY_FROMDATE}" todate="{$YESTERDAY_TODATE}">
			<label class="cursor-pointer" for="yesterday">Hôm qua</label> 

			<input type="radio" value="daybefore" id="daybefore" class="rd_time form-check-input" name="optionRadio" fromdate="{$DAYBEFORE_FROMDATE}" todate="{$DAYBEFORE_TODATE}">
			<label class="cursor-pointer" for="daybefore">Hôm trước</label> 

			<input type="radio" value="current_week" id="current_week" class="rd_time form-check-input" name="optionRadio" fromdate="{$CURRENT_WEEK_FROMDATE}" todate="{$CURRENT_WEEK_TODATE}"> 
			<label class="cursor-pointer" for="current_week">Tuần này</label>

			<input type="radio" value="previous_week" id="previous_week" class="rd_time form-check-input" name="optionRadio" fromdate="{$PREVIOUS_WEEK_FROMDATE}" todate="{$PREVIOUS_WEEK_TODATE}">
			<label class="cursor-pointer" for="previous_week">Tuần trước</label> 
		</div>
		<div class="currentsales-note">Doanh số Vé Quốc tế được tách riêng hoàn toàn</div>
	</div>
</form>

<table id="tbl-doanhsohientai" class="table-current-sales table-details__booking" border="0" cellpadding="0" cellspacing="0">
    {$DATA}
</table>

</div>


{if $DATA2 != ''}
<h1 class="title my-3">Booking chưa xuất vé</h1>

<div class="box-section">
<table id="tbl-chuaxuatve" class="table-chuaxuatve table-details__booking" border="0" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th width="6%" align="center">Booking</th>
			<th width="9%" align="center">Hành trình</th>
			<th width="5%" align="center" class="hide-mobile">Hãng</th>
			<th width="8%" align="center" class="hide-mobile">Ngày bay</th>
			<th width="7%" align="center" class="hide-mobile">Tình trạng</th>
			<th width="11%" align="center">Liên hệ</th>
			<th width="7%" align="center">Điện thoại</th>
			<!-- <th width="12%" align="center">Email</th> -->
			<th width="12%" align="center">Ghi chú</th>
			<th width="3%" align="center">Vé</th>
			<th width="8%" align="center">Doanh số</th>
			<th width="8%" align="center" class="hide-mobile">Doanh thu</th>
			<th width="6%" align="center" class="hide-mobile">Giao cho</th>
			<th width="8%" align="center" class="hide-mobile">Ngày tạo</th>
		</tr>
	</thead>
    {$DATA2}
    <tr class="footer-tr">
    		<td colspan="5" class="text-start fw-bold">Số dòng = {$SODONG}</td>
		<td align="center" class="hide-mobile">&nbsp;</td>
		<td align="center" class="hide-mobile">&nbsp;</td>
		<td align="center" class="hide-mobile">&nbsp;</td>
        	<td align="center">{$TONGSOVECHUAXUAT}</td>
        	<td align="right" class="text-end fw-bold color-red">{$TONGTIENDOANHSO}</td>
        	<td align="right" class="text-end fw-bold color-red hide-mobile">{$TONGTIENCHUAXUAT}</td>
		<td align="center" class="hide-mobile">&nbsp;</td>
		<td align="center" class="hide-mobile">&nbsp;</td>
    </tr>
</table>
</div>
{/if}

<div id="infor_booking_inter__title"></div>
