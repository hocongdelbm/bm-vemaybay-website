{literal}
	<script>
		$(document).ready(function() {
			$('input[type=radio][name=optionRadio]').change(function() {
				const $r = $('input[name=optionRadio]:checked');
  				setDateInputs($r.attr('fromdate'), $r.attr('todate'));

				// vô hiệu hóa select để nó không post
				$('#date_select').prop('selectedIndex', 0); 
			});

			$(document).on("change", "#date_select", function(e) {
				const $opt = $(this).find('option:selected');
  				setDateInputs($opt.attr('fromdate'), $opt.attr('todate'));
				$('input[name=optionRadio]').prop('checked', false)
			});

			function setDateInputs(from, to) {
				$('#from_date').val(from || '');
				$('#to_date').val(to   || '');
			}

			$(".show_detail_bk").click(function() {
				if ($(this).hasClass('displayed')) {
					$("#warning_note").text("");
					$(".detail_bk--wrap").remove();
					$(this).removeClass('displayed');
				} else {
					$(".show_detail_bk").removeClass('displayed');
					$(".show_detail_total").removeClass('displayed');
					$(this).addClass('displayed');

					if ($(this).attr("type") == 'show_prior_bk') {
						getPriorBK($(this).attr("user"), $(this).attr("sname"), $(this).attr("from_date"), $(this).attr("to_date"));
					} else if ($(this).attr("type") == 'show_2ticket_bk') {
						get2TicketBK($(this).attr("user"), $(this).attr("sname"), $(this).attr("from_date"), $(this).attr("to_date"));
					} else if ($(this).attr("type") == 'show_3ticket_bk') {
						get3TicketBK($(this).attr("user"), $(this).attr("sname"), $(this).attr("from_date"), $(this).attr("to_date"));
					} else if ($(this).attr("type") == 'show_4to8ticket_bk') {
						get4To8TicketBK($(this).attr("user"), $(this).attr("sname"), $(this).attr("from_date"), $(this).attr("to_date"));
					} else if ($(this).attr("type") == 'show_booker_bk') {
						getBookerBK($(this).attr("user"), $(this).attr("sname"), $(this).attr("from_date"), $(this).attr("to_date"));
					} else if ($(this).attr("type") == 'show_khachhang_bk') {
						getKhachHangBK($(this).attr("user"), $(this).attr("sname"), $(this).attr("from_date"), $(this).attr("to_date"));
					} else if ($(this).attr("type") == 'show_thamkhao_bk') {
						getThamKhaoBK($(this).attr("user"), $(this).attr("sname"), $(this).attr("from_date"), $(this).attr("to_date"));
					} else if ($(this).attr("type") == 'show_inter_bk') {
						getBookingInter($(this).attr("user"), $(this).attr("sname"), $(this).attr("from_date"), $(this).attr("to_date"))
					} else if ($(this).attr("type") == 'show_detail_call') {
						getDetailCallBookingQtyReport($(this).attr("user"), $(this).attr("sname"), $(this).attr("direction"), $(this).attr("from_date"), $(this).attr("to_date"));
					}
				}
			});

			$(".show_detail_total").click(function() {
				var title_tbl = $(this).attr("title");
				$(".show_detail_bk").removeClass('displayed');

				if ($(this).hasClass('displayed')) {
					$("#warning_note").text("");
					$(".detail_bk--wrap").remove();
					$(this).removeClass('displayed');
				} else {
					$(this).addClass('displayed');
					$.ajax({
						url: "index.php?entryPoint=entryPointFlightBookings",
						type: "POST",
						data: {
							fdate: $(this).attr("from_date"),
							tdate: $(this).attr("to_date"),
							for: "getToTalBKInOneDay",
						},
						beforeSend: function() {
							$(".container-waiting").show();
							$(".detail_bk--wrap").remove();
						},
						success: function(response) {
							$(".container-waiting").hide();
							$("#warning_note").html(
								'<div class="d-flex justify-content-center align-items-center gap-2"><h3 class="sub-title mb-0">' +
								title_tbl +
								'</h3> <input type="button" class="hide_detail_btn btn btn-dark" id="hide_detail_btn" value="Ẩn"></div>'
							);
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

		function getPriorBK(user_id, user_name, from_date, to_date) {
			$.ajax({
				url: "index.php?entryPoint=entryPointFlightBookings",
				type: "POST",
				data: {
					fdate: from_date,
					tdate: to_date,
					user: user_id,
					for: "getPriorBooking",
				},
				beforeSend: function() {
					$(".container-waiting").show();
					$(".detail_bk--wrap").remove();
				},
				success: function(response) {
					$(".container-waiting").hide();
					$("#warning_note").html(
						'<div class="d-flex justify-content-center align-items-center gap-2"><h3 class="sub-title mb-0">Danh sách booking vé cận site ' +
						user_name +
						' </h3> <input type="button" class="hide_detail_btn btn btn-dark" id="hide_detail_btn" value="Ẩn"></div>'
					);
					$(".detail_bk--wrap").remove();
					$("#warning_note").after(response);
				}
			});
		}

		function get2TicketBK(user_id, user_name, from_date, to_date) {
			$.ajax({
				url: "index.php?entryPoint=entryPointFlightBookings",
				type: "POST",
				data: {
					fdate: from_date,
					tdate: to_date,
					user: user_id,
					for: "get2TicketBooking",
				},
				beforeSend: function() {
					$(".container-waiting").show();
					$(".detail_bk--wrap").remove();
				},
				success: function(response) {
					$(".container-waiting").hide();
					$("#warning_note").html(
						'<div class="d-flex justify-content-center align-items-center gap-2"><h3 class="sub-title mb-0">Danh sách booking có 2 vé site ' +
						user_name +
						' </h3> <input type="button" class="hide_detail_btn btn btn-dark" id="hide_detail_btn" value="Ẩn"></div>'
					);
					$(".detail_bk--wrap").remove();
					$("#warning_note").after(response);
				}
			});
		}

		function get3TicketBK(user_id, user_name, from_date, to_date) {
			$.ajax({
				url: "index.php?entryPoint=entryPointFlightBookings",
				type: "POST",
				data: {
					fdate: from_date,
					tdate: to_date,
					user: user_id,
					for: "get3TicketBooking",
				},
				beforeSend: function() {
					$(".container-waiting").show();
					$(".detail_bk--wrap").remove();
				},
				success: function(response) {
					$(".container-waiting").hide();
					$("#warning_note").html(
						'<div class="d-flex justify-content-center align-items-center gap-2"><h3 class="sub-title mb-0">Danh sách booking 3 vé trở xuống site ' +
						user_name +
						' </h3> <input type="button" class="hide_detail_btn btn btn-dark" id="hide_detail_btn" value="Ẩn"></div>'
					);
					$(".detail_bk--wrap").remove();
					$("#warning_note").after(response);
				}
			});
		}

		function get4To8TicketBK(user_id, user_name, from_date, to_date) {
			$.ajax({
				url: "index.php?entryPoint=entryPointFlightBookings",
				type: "POST",
				data: {
					fdate: from_date,
					tdate: to_date,
					user: user_id,
					for: "get4To8TicketBooking",
				},
				beforeSend: function() {
					$(".container-waiting").show();
					$(".detail_bk--wrap").remove();
				},
				success: function(response) {
					$(".container-waiting").hide();
					$("#warning_note").html(
						'<div class="d-flex justify-content-center align-items-center gap-2"><h3 class="sub-title mb-0">Danh sách booking từ 4-8 vé site ' +
						user_name +
						' </h3> <input type="button" class="hide_detail_btn btn btn-dark" id="hide_detail_btn" value="Ẩn"></div>'
					);
					$(".detail_bk--wrap").remove();
					$("#warning_note").after(response);
				}
			});
		}

		function getBookerBK(user_id, user_name, from_date, to_date) {
			$.ajax({
				url: "index.php?entryPoint=entryPointFlightBookings",
				type: "POST",
				data: {
					fdate: from_date,
					tdate: to_date,
					user: user_id,
					for: "getBookerBooking",
				},
				beforeSend: function() {
					$(".container-waiting").show();
					$(".detail_bk--wrap").remove();
				},
				success: function(response) {
					$(".container-waiting").hide();
					$("#warning_note").html(
						'<div class="d-flex justify-content-center align-items-center gap-2"><h3 class="sub-title mb-0">Danh sách booking do booker đặt trên site ' +
						user_name +
						' </h3> <input type="button" class="hide_detail_btn btn btn-dark" id="hide_detail_btn" value="Ẩn"></div>'
					);
					$(".detail_bk--wrap").remove();
					$("#warning_note").after(response);
				}
			});
		}

		function getKhachHangBK(user_id, user_name, from_date, to_date) {
			$.ajax({
				url: "index.php?entryPoint=entryPointFlightBookings",
				type: "POST",
				data: {
					fdate: from_date,
					tdate: to_date,
					user: user_id,
					for: "getKhachHangBooking",
				},
				beforeSend: function() {
					$(".container-waiting").show();
					$(".detail_bk--wrap").remove();
				},
				success: function(response) {
					$(".container-waiting").hide();
					$("#warning_note").html(
						'<div class="d-flex justify-content-center align-items-center gap-2"><h3 class="sub-title mb-0">Danh sách booking KH đặt site ' +
						user_name +
						' </h3> <input type="button" class="hide_detail_btn btn btn-dark" id="hide_detail_btn" value="Ẩn"></div>'
						);
					$(".detail_bk--wrap").remove();
					$("#warning_note").after(response);
				}
			});
		}

		function getThamKhaoBK(user_id, user_name, from_date, to_date) {
			$.ajax({
				url: "index.php?entryPoint=entryPointFlightBookings",
				type: "POST",
				data: {
					fdate: from_date,
					tdate: to_date,
					user: user_id,
					for: "getThamKhaoBooking",
				},
				beforeSend: function() {
					$(".container-waiting").show();
					$(".detail_bk--wrap").remove();
				},
				success: function(response) {
					$(".container-waiting").hide();
					$("#warning_note").html(
						'<div class="d-flex justify-content-center align-items-center gap-2"><h3 class="sub-title mb-0">Danh sách booking Tham khảo site ' +
						user_name +
						' </h3> <input type="button" class="hide_detail_btn btn btn-dark" id="hide_detail_btn" value="Ẩn"></div>'
						);
					$(".detail_bk--wrap").remove();
					$("#warning_note").after(response);
				}
			});
		}

		function getBookingInter(user_id, user_name, from_date, to_date) {
			$.ajax({
				url: "index.php?entryPoint=entryPointFlightBookings",
				type: "POST",
				data: {
					fdate: from_date,
					tdate: to_date,
					user: user_id,
					for: "getInterBooking",
				},
				beforeSend: function() {
					$(".container-waiting").show();
					$(".detail_bk--wrap").remove();
				},
				success: function(response) {
					$(".container-waiting").hide();
					$("#warning_note").html(
						'<div class="d-flex justify-content-center align-items-center gap-2"><h3 class="sub-title mb-0">Danh sách booking Quốc tế đặt site ' +
						user_name +
						'</h3> <input type="button" class="hide_detail_btn btn btn-dark" id="hide_detail_btn" value="Ẩn"></div>'
					);
					$(".detail_bk--wrap").remove();
					$("#warning_note").after(response);
				}
			});
		}

		function getDetailCallBookingQtyReport(user_id, user_name, direction, from_date, to_date) {
			$.ajax({
				url: "index.php?entryPoint=entryPointFlightBookings",
				type: "POST",
				data: {
					fdate: from_date,
					tdate: to_date,
					direction: direction,
					user: user_id,
					for: "getDetailCallBookingQtyReport",
				},
				beforeSend: function() {
					$(".container-waiting").show();
					$(".detail_bk--wrap").remove();
					$("#warning_note").html("");
				},
				success: function(response) {
					$(".container-waiting").hide();
					$("#warning_note").html(
						'<div class="d-flex justify-content-center align-items-center gap-2"><h3 class="sub-title mb-0">Danh sách chi tiết cuộc gọi ' +
						direction + ' site ' + user_name +
						'</h3> <input type="button" class="hide_detail_btn btn btn-dark" id="hide_detail_btn" value="Ẩn"></div>'
					);
					$(".detail_bk--wrap").remove();
					$("#warning_note").after(response);
				}
			});
		}
	</script>
{/literal}

<div class="title-wrap d-flex align-items-center justify-content-between gap-2">
	<h1 class="title">Ds theo ngày tạo Booking</h1>
	<svg xmlns="http://www.w3.org/2000/svg" id="filter_report" width="32" height="32" fill="currentColor" class="bi bi-filter d-xxl-none d-xl-none d-lg-none d-block" viewBox="0 0 16 16">
		<path d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5" />
	</svg>
</div>

<div class="box-section overflow-auto position-relative mt-0">
	<div class="overlay-mobile"></div>
	<form id="ec_search_form" name="search_form" method="POST" action="index.php?module=EC_TongHop&action=report_sales_create">
		<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor"
			class="bi bi-dash-lg search_form--dash d-xl-none d-lg-none d-block" viewBox="0 0 16 16">
			<path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8"></path>
		</svg>
		<div class="action--wrap d-flex gap-4 align-items-center">
			<select class="box-select" id="date_select" name="date_select">{$DATE_OPTION}</select>

			<div class="from-to-date--wrap d-inline-flex gap-2 align-items-center">
				<div class="d-flex gap-2 align-items-center date_trigger--wrap fdate_trigger--wrap">
					<span class="text-label">Từ ngày: </span>
					<div class="dateTime d-flex gap-2 position-relative">
						<input class="date_input box-input" type="text" maxlength="10" size="8" tabindex="103" title="" value="{$FROM_DATE_VALUE}" id="from_date" name="from_date" autocomplete="off">
						<button class="icon_dateTime" type="button" id="from_date_trigger" onclick="return false;">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
								class="bi bi-calendar2" viewBox="0 0 16 16">
								<path
									d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z" />
								<path
									d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z" />
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
								});
							</script>
						{/literal}
					</div>
				</div>

				<svg width="40" height="20" fill="none">
					<g clip-path="url(#icon_arrow_flight_long_svg__clip0)" stroke="#718096" stroke-width="1.5"
						stroke-linecap="round" stroke-linejoin="round">
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
						<input class="date_input box-input" type="text" maxlength="10" size="8" title="" value="{$TO_DATE_VALUE}" id="to_date" name="to_date" autocomplete="off">
						<button class="icon_dateTime" type="button" id="to_date_trigger" onclick="return false;">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
								class="bi bi-calendar2" viewBox="0 0 16 16">
								<path
									d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z" />
								<path
									d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z" />
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
								});
							</script>
						{/literal}
					</div>
				</div>
			</div>
		</div>

		<div class="button-action--wrap">
			<input type="submit" id="btnSearch" name="search" class="btn btn-primary button-action" value="Tìm kiếm"
				title="Tìm kiếm" />
			<input type="button" id="btnSearch_cancel" name="search"
				class="btn btn-secondary button-action--cancel d-xl-none d-lg-none d-block" value="Hủy bỏ"
				title="Hủy bỏ" />
		</div>

		<ul class="bookingqtyreport-note m-0">
			<li class="fst-italic"><i>Lưu ý:</i> Doanh số lấy theo ngày tạo của booking. Booking đã hoàn tất</li>
			<li class="fst-italic">Doanh số Vé Quốc tế đã bao gồm trong cái Tổng</li>
		</ul>
	</form>

	<table id="booking_qty" class="table-details__booking table-booking_qty mt-3" cellpadding="0" cellspacing="0">
		<thead>
			<tr class="text-nowrap">
				<th rowspan="2" style="width: 10%;">Trang web</th>
				<th colspan="4" style="width: 12%;">Doanh số</th>
				<th rowspan="2" colspan="2" style="width: 5%;">Tổng BK</th>
				<th rowspan="2" style="width: 5%;">Booker đặt</th>
				<th rowspan="2" style="width: 5%;">KH đặt</th>
				<th rowspan="2" style="width: 5%;">Tham khảo</th>
	
				<th colspan="2" style="width: 8%; background-color: #068FFF; color: #fff">Cuộc gọi</th>
	
				<th colspan="3" style="width: 5%;">BK Vé cận</th>
				<th colspan="3" style="width: 5%;">BK dưới 3 vé</th>
				<th colspan="3" style="width: 5%;">BK 4-8 vé</th>
				<th colspan="3" style="width: 8%; background-color: #8BE8E5;">BK Quốc tế</th>
			</tr>
			<tr class="text-nowrap">
				<th colspan="2" style="width: 5%;">Số tiền</th>
				<th style="width: 3%;">Vé</th>
				<th style="width: 3%;">BK OK</th>
	
				<th style="width: 3%; background-color: #068FFF; color: #fff">Gọi đến /<br> Tạo BK</th>
				<th style="width: 4%; background-color: #068FFF; color: #fff">Gọi nhỡ</th>
	
				<!-- vé cận -->
				<th style="width: 3%;">BK</th>
				<th colspan="2" style="width: 4%;">DS</th>
	
				<!-- bk 3 vé -->
				<th style="width: 3%;">BK</th>
				<th colspan="2" style="width: 4%;">DS</th>
	
				<!-- bk 4-8 vé -->
				<th style="width: 3%;">BK</th>
				<th colspan="2" style="width: 4%;">DS</th>
	
				<!-- INTER -->
				<th style="width: 3%; background-color: #8BE8E5;">BK</th>
				<th colspan="2" style="width: 4%; background-color: #8BE8E5;">DS</th>
			</tr>
		</thead>
        <tbody>
			{$rpt_body_compare}
        </tbody>
	</table>
</div>

<div id="warning_note"></div>