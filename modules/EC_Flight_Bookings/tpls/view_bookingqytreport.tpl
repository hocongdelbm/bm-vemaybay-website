{literal}
<script>
	$(document).ready(function() {	
		$('input[type=radio][name=optionRadio]').change(function() {
			$('#from_date').val($('input[name=optionRadio]:checked').attr('fromdate'));
			$('#to_date').val($('input[name=optionRadio]:checked').attr('todate'));

			sessionStorage.setItem('optionRadio_bkreport', $(this).val());
			sessionStorage.removeItem('date_select_bkreport');
		});

		$(document).on("change", "#date_select", function(e) {
			$("#from_date").val($(this).find("option:selected").attr("fromdate"));
			$("#to_date").val($(this).find("option:selected").attr("todate"));

			sessionStorage.setItem('date_select_bkreport', $(this).val());
			sessionStorage.removeItem('optionRadio_bkreport');
		});

		// Check sessionStorage - js
		const selectOption 		= document.getElementById('date_select');
		const radioOptions 		= document.getElementsByName('optionRadio');
		const savedSelectOption 	= sessionStorage.getItem('date_select_bkreport');
		if (savedSelectOption) {
			selectOption.value = savedSelectOption;
		} else {
			const savedRadioOption = sessionStorage.getItem('optionRadio_bkreport');
			if (savedRadioOption) {
				radioOptions.forEach(radio => {
					if (radio.value === savedRadioOption) {
						radio.checked = true;
					}
				});
			}
		}


		$(".show_detail_bk").click(function() {

			if($(this).hasClass('displayed')){
				$("#warning_note").text("");
				$(".detail_bk--wrap").remove();
				$(this).removeClass('displayed');
			} else {
				$(".show_detail_bk").removeClass('displayed');
				$(".show_detail_total").removeClass('displayed');
				$(this).addClass('displayed');
			
				if($(this).attr("type") == 'show_prior_bk') {
					getPriorBK($(this).attr("user"), $(this).attr("sname"));
				} else if($(this).attr("type") == 'show_2ticket_bk') {
					get2TicketBK($(this).attr("user"), $(this).attr("sname"));
				} else if($(this).attr("type") == 'show_3ticket_bk') {
					get3TicketBK($(this).attr("user"), $(this).attr("sname"));
				} else if($(this).attr("type") == 'show_4to8ticket_bk') {
					get4To8TicketBK($(this).attr("user"), $(this).attr("sname"));
				} else if($(this).attr("type") == 'show_9ticket_bk') {
					get9TicketBK($(this).attr("user"), $(this).attr("sname"));
				} else if($(this).attr("type") == 'show_booker_bk') {
					getBookerBK($(this).attr("user"), $(this).attr("sname"));
				} else if ($(this).attr("type") == 'show_inter_bk'){
					getBookingInter($(this).attr("user"), $(this).attr("sname"))
				}
			}
		});

		$(".show_detail_total").click(function() {
			var title_tbl = $(this).attr("title");
			$(".show_detail_bk").removeClass('displayed');

			if($(this).hasClass('displayed')){
				$("#warning_note").text("");
				$(".detail_bk--wrap").remove();
				$(this).removeClass('displayed');
			} else {
				$(this).addClass('displayed');
				$.ajax({
					url: "index.php?entryPoint=entryPointFlightBookings",
					type: "POST",
					data: {
						fdate: $('#from_date').val(),
						tdate: $('#to_date').val(),
						for: "getToTalBKInOneDay",
					},
					beforeSend: function() {
						$(".container-waiting").show();
						$(".detail_bk--wrap").remove();
					},
					success: function(response) {
						$(".container-waiting").hide();
						$("#warning_note").html('<div class="d-flex justify-content-center align-items-center gap-2"><h3 class="sub-title mb-0">' + title_tbl + '</h3> <input type="button" class="hide_detail_btn btn btn-dark" id="hide_detail_btn" value="Ẩn"></div>');
						$(".detail_bk--wrap").remove();
						$("#warning_note").after(response);
					}
				});
			}
		});

		$(document).on("click", "#hide_detail_btn", function() {
			$("#warning_note").text("");
			$(".detail_bk--wrap").remove();
			$(".show_detail_bk").removeClass('displayed');
			$(".show_detail_total").removeClass('displayed');
		});
	});

	function getPriorBK(user_id, user_name) {
		$.ajax({
			url: "index.php?entryPoint=entryPointFlightBookings",
			type: "POST",
			data: {
				fdate: $('#from_date').val(),
				tdate: $('#to_date').val(),
				user: user_id,
				for: "getPriorBooking",
			},
			beforeSend: function() {
				$(".container-waiting").show();
				$(".detail_bk--wrap").remove();
			},
			success: function(response) {
				$(".container-waiting").hide();
				$("#warning_note").html('<div class="d-flex justify-content-center align-items-center gap-2"><h3 class="sub-title mb-0">Danh sách booking vé cận site ' + user_name + ' </h3> <input type="button" class="hide_detail_btn btn btn-dark" id="hide_detail_btn" value="Ẩn"></div>');
				$(".detail_bk--wrap").remove();
				$("#warning_note").after(response);
			}
		});
	}

	function get2TicketBK(user_id, user_name) {
		$.ajax({
			url: "index.php?entryPoint=entryPointFlightBookings",
			type: "POST",
			data: {
				fdate: $('#from_date').val(),
				tdate: $('#to_date').val(),
				user: user_id,
				for: "get2TicketBooking",
			},
			beforeSend: function() {
				$(".container-waiting").show();
				$(".detail_bk--wrap").remove();
			},
			success: function(response) {
				$(".container-waiting").hide();
				$("#warning_note").html('<div class="d-flex justify-content-center align-items-center gap-2"><h3 class="sub-title mb-0">Danh sách booking có 2 vé site ' + user_name + ' </h3> <input type="button" class="hide_detail_btn btn btn-dark" id="hide_detail_btn" value="Ẩn"></div>');
				$(".detail_bk--wrap").remove();
				$("#warning_note").after(response);
			}
		});
	}

	function get3TicketBK(user_id, user_name) {
		$.ajax({
			url: "index.php?entryPoint=entryPointFlightBookings",
			type: "POST",
			data: {
				fdate: $('#from_date').val(),
				tdate: $('#to_date').val(),
				user: user_id,
				for: "get3TicketBooking",
			},
			beforeSend: function() {
				$(".container-waiting").show();
				$(".detail_bk--wrap").remove();
			},
			success: function(response) {
				$(".container-waiting").hide();
				$("#warning_note").html('<div class="d-flex justify-content-center align-items-center gap-2"><h3 class="sub-title mb-0">Danh sách booking 3 vé trở xuống site ' + user_name + ' </h3> <input type="button" class="hide_detail_btn btn btn-dark" id="hide_detail_btn" value="Ẩn"></div>');
				$(".detail_bk--wrap").remove();
				$("#warning_note").after(response);
			}
		});
	}

	function get4To8TicketBK(user_id, user_name) {
		$.ajax({
			url: "index.php?entryPoint=entryPointFlightBookings",
			type: "POST",
			data: {
				fdate: $('#from_date').val(),
				tdate: $('#to_date').val(),
				user: user_id,
				for: "get4To8TicketBooking",
			},
			beforeSend: function() {
				$(".container-waiting").show();
				$(".detail_bk--wrap").remove();
			},
			success: function(response) {
					$(".container-waiting").hide();
				$("#warning_note").html('<div class="d-flex justify-content-center align-items-center gap-2"><h3 class="sub-title mb-0">Danh sách booking từ 4-8 vé site ' + user_name + ' </h3> <input type="button" class="hide_detail_btn btn btn-dark" id="hide_detail_btn" value="Ẩn"></div>');
				$(".detail_bk--wrap").remove();
				$("#warning_note").after(response);
			}
		});
	}

	function get9TicketBK(user_id, user_name) {
		$.ajax({
			url: "index.php?entryPoint=entryPointFlightBookings",
			type: "POST",
			data: {
				fdate: $('#from_date').val(),
				tdate: $('#to_date').val(),
				user: user_id,
				for: "get9TicketBooking",
			},
			beforeSend: function() {
				$(".container-waiting").show();
				$(".detail_bk--wrap").remove();
			},
			success: function(response) {
				$(".container-waiting").hide();
				$("#warning_note").html('<div class="d-flex justify-content-center align-items-center gap-2"><h3 class="sub-title mb-0">Danh sách booking trên 9 vé site ' + user_name + ' </h3> <input type="button" class="hide_detail_btn btn btn-dark" id="hide_detail_btn" value="Ẩn"></div>');
				$(".detail_bk--wrap").remove();
				$("#warning_note").after(response);
			}
		});
	}

	function getBookerBK(user_id, user_name) {
		$.ajax({
			url: "index.php?entryPoint=entryPointFlightBookings",
			type: "POST",
			data: {
				fdate: $('#from_date').val(),
				tdate: $('#to_date').val(),
				user: user_id,
				for: "getBookerBooking",
			},
			beforeSend: function() {
				$(".container-waiting").show();
				$(".detail_bk--wrap").remove();
			},
			success: function(response) {
				$(".container-waiting").hide();
				$("#warning_note").html('<div class="d-flex justify-content-center align-items-center gap-2"><h3 class="sub-title mb-0">Danh sách booking do booker đặt trên site ' + user_name + ' </h3> <input type="button" class="hide_detail_btn btn btn-dark" id="hide_detail_btn" value="Ẩn"></div>');
				$(".detail_bk--wrap").remove();
				$("#warning_note").after(response);
			}
		});
	}

	function getBookingInter(user_id, user_name){
		$.ajax({
			url: "index.php?entryPoint=entryPointFlightBookings",
			type: "POST",
			data: {
				fdate: $('#from_date').val(),
				tdate: $('#to_date').val(),
				user: user_id,
				for: "getInterBooking",
			},
			beforeSend: function() {
				$(".container-waiting").show();
				$(".detail_bk--wrap").remove();
			},
			success: function(response) {
				$(".container-waiting").hide();
				$("#warning_note").html('<div class="d-flex justify-content-center align-items-center gap-2"><h3 class="sub-title mb-0">Danh sách booking Quốc tế đặt site ' + user_name + '</h3> <input type="button" class="hide_detail_btn btn btn-dark" id="hide_detail_btn" value="Ẩn"></div>');
				$(".detail_bk--wrap").remove();
				$("#warning_note").after(response);
			}
		});
	}
</script>
{/literal}

<h1 class="title">Doanh số theo ngày tạo Booking</h1>

<div class="box-section overflow-auto">
<form name="search_form" method="POST" action="index.php?module=EC_Flight_Bookings&action=bookingqtyreport">
	<div class="action--wrap d-flex gap-4 align-items-center mb-3">
		<select class="box-select" id="date_select" name="date_select">{$DATE_OPTION}</select>

		<div class="from-to-date--wrap d-inline-flex gap-2 align-items-center">
			<div class="d-flex gap-2 align-items-center fdate_trigger--wrap">
			<span class="text-label">Từ ngày: </span>    
			<div class="dateTime d-flex gap-2 position-relative">
				<input class="date_input box-input" type="text" maxlength="10" size="8" tabindex="103" title="" value="{$from_date}" id="from_date" name="from_date" autocomplete="off">
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
								step: 1
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
			<div class="dateTime d-flex gap-2 position-relative">
				<input  class="date_input box-input" type="text" maxlength="10" size="8" title="" value="{$to_date}" id="to_date" name="to_date" autocomplete="off">
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
  
			<input type="submit" id="btnSearch" name="search" class="btn btn-primary button-action" value="Tìm" title="Tìm"/>
	 	</div>

		<div class="d-flex align-items-center gap-2">
			<input type="radio" value="yesterday" id="yesterday" class="rd_time form-check-input" name="optionRadio" fromdate="{$YESTERDAY_FROMDATE}" todate="{$YESTERDAY_TODATE}">
			<label class="cursor-pointer" for="yesterday">Hôm qua</label> 

			<input type="radio" value="daybefore" id="daybefore" class="rd_time form-check-input" name="optionRadio" fromdate="{$DAYBEFORE_FROMDATE}" todate="{$DAYBEFORE_TODATE}">
			<label class="cursor-pointer" for="daybefore">Hôm trước</label> 

			<input type="radio" value="current_week" id="current_week" class="rd_time form-check-input" name="optionRadio" fromdate="{$CURRENT_WEEK_FROMDATE}" todate="{$CURRENT_WEEK_TODATE}">
			<label class="cursor-pointer" for="current_week">Tuần này</label>

			<input type="radio" value="previous_week" id="previous_week" class="rd_time form-check-input" name="optionRadio" fromdate="{$PREVIOUS_WEEK_FROMDATE}" todate="{$PREVIOUS_WEEK_TODATE}"> 
			<label class="cursor-pointer" for="previous_week">Tuần trước</label> 
		</div>

		<div class="bookingqtyreport-note">Doanh số Vé Quốc tế đã bao gồm trong cái Tổng</div>
	</div>
</form>

<table id="booking_qty" class="table-details__booking table-booking_qty mt-3" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th rowspan="2" style="width: 3%;">STT</th>
			<th rowspan="2" style="width: 5%;">Trang web</th>
			<th colspan="4" style="width: 15%;">Doanh số</th>
			<th rowspan="2" colspan="2" style="width: 5%;">Tổng BK</th>
			<th rowspan="2" style="width: 5%;">Booker</th>

			<th colspan="3" style="width: 5%;">BK Vé cận</th>
			<th colspan="3" style="width: 5%;">BK dưới 3 vé</th>
			<th colspan="3" style="width: 5%;">BK 4-8 vé</th>
			<!-- <th colspan="3" style="width: 5%;">BK trên 9 vé</th> -->
			<th colspan="3" style="width: 8%; background-color: #8BE8E5;">BK Quốc tế</th>

			<!-- <th colspan="2" style="width: 7%; background-color: #1B9C85; color: #fff">Hoàn tất</th>
			<th colspan="2" style="width: 7%; background-color: #068FFF; color: #fff">Xác nhận</th> -->
			<th colspan="2" style="width: 8%; background-color: #068FFF; color: #fff">Hoàn tất</th>
			<th colspan="2" style="width: 8%; background-color: #BBD6B8;">Đã gọi</th>
			<th colspan="2" style="width: 8%; background-color: #E94560; color: #fff">Hủy</th>
		</tr>
		<tr>
			<th colspan="2" style="width: 7%;">Số tiền</th>
			<th style="width: 3%;">Vé</th>	
			<th style="width: 3%;">BK</th>

			<!-- vé cận -->
			<th style="width: 3%;">BK</th>
			<th colspan="2" style="width: 4%;">DS</th>

			<!-- bk 3 vé -->
			<th style="width: 3%;">BK</th>
			<th colspan="2" style="width: 4%;">DS</th>

			<!-- bk 4-8 vé -->
			<th style="width: 3%;">BK</th>
			<th colspan="2" style="width: 4%;">DS</th>

			<!-- bk từ 9 vé -->
			<!-- <th style="width: 3%;">BK</th>
			<th colspan="2" style="width: 4%;">DS</th> -->

			<!-- INTER -->
			<th style="width: 3%; background-color: #8BE8E5;">BK</th>
			<th colspan="2" style="width: 4%; background-color: #8BE8E5;">DS</th>
			
			<!-- <th style="width: 3%; background-color: #1B9C85; color: #fff">SL</th>
			<th style="width: 4%; background-color: #1B9C85; color: #fff">%</th> -->

			<th style="width: 3%; background-color: #068FFF; color: #fff">SL</th>
			<th style="width: 4%; background-color: #068FFF; color: #fff">%</th>

			<th style="width: 3%; background-color: #BBD6B8;">SL</th>
			<th style="width: 4%; background-color: #BBD6B8;">%</th>

			<th style="width: 3%; background-color: #E94560; color: #fff;">SL</th>
			<th style="width: 4%; background-color: #E94560; color: #fff;">%</th>
		</tr>	
	</thead>
	{$rpt_body}
</table>
</div>

<div id="warning_note"></div>
