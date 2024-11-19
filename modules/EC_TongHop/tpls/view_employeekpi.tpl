<script src="custom/jqueryui/plugins/formatNumber.js"></script>
<script src="custom/jqueryui/plugins/jquery.number.min.js"></script>
{literal}
	<style>
	table.list-data-all tbody tr:nth-child(1) td a.admin-view-detail,
	table.list-data-all tbody tr:nth-child(1) td span{
		color: #4154f1;
		font-weight: 600;
	}

	table.list-data-all tbody tr:nth-last-child(2) td a.admin-view-detail,
	table.list-data-all tbody tr:nth-last-child(2) td span{
		color: var(--red-vj-color);
		font-weight: 600;
	}

	table.list-data-all a.showdt:hover {
		color: var(--yellow-color);
	}

	table.list-data-all thead th{
		white-space: nowrap;
	}

	.remaining-credit span.credit{
		color: var(--red-vj-color);
		font-size:16px;
	}

	a.view-detail{
		float:right;
	}

	#dlgViewDetail{
		display:none;
		font-family:Arial, Helvetica, sans-serif;
		font-size:11px;
		overflow: auto;
	}

	#dlgViewDetailContent{
		overflow-y: scroll;
		max-height: 500px;
	}

	table.detail-data-list{
		line-height:20px;
		border-collapse:collapse;
	}

	table.detail-data-list th{
		position: sticky;
		top: -1px;
	}

	table.detail-data-list tr:last-child td{
		background:#e2e2e2;
		font-weight:bold;
	}

	.calendar {
		width: 300px;
		height: 250px;
		top: 202px !important;
	}

	.calendar table {
		width: 100%;
		height: 100%;
	}

	/* #mark_detail_tbl { */
	#mark_detail_tbl--wrap {
		display: none;
		position: relative;
	}

	#mark-loading {
		padding-top: 8px;
	}
	.showall {
		position: relative;
	}

	.list-data tbody tr:not(:first-child, :last-child):hover {
		background-color: #cfeafe !important;
	}

	/* NOTES */
	ul.kpi-notes{
		list-style: none;
		border-left: 2px solid #ffc107;
		padding: 0 10px;
		font-size: 13px;
		line-height: 2;
		margin: 15px 0 0;
		text-align: left;
	}

	</style>
    <script>
		$(document).ready(function(){
			// Xem chi tiết các chỉ số trên báo cáo
			$('.view-detail, .admin-view-detail').click(function(){
				let from_date 	= $('#from_date').val();
				let to_date 	= $('#to_date').val();		

				$('#load_type').val($(this).attr('load_type'));
				$('#user_id').val($(this).attr('user_id'));
				$('#dlgViewDetail').dialog({
					height: 450,
					width: 1300,
					modal: true,
					resizable: false,
					closeOnEscape: false,
					title: 'Xem chi tiết '+ $(this).attr('load_name') +' từ ' + from_date + ' đến ' + to_date,
					position: { my: "center", at: "center", of: window } // Căn giữa màn hình
				});
			});
			
			// When dialog open
			$('#dlgViewDetail').on('dialogopen', function(event, ui){					
				var load_type 	= $.trim($('#load_type').val());
				var user_id 	= $.trim($('#user_id').val());
				var from_date 	= $('#from_date').val();
				var to_date 	= $('#to_date').val();

				if(load_type != ''){
					$.ajax({	
						cache: false,
						type: 'post',
						data: 'load_type=' + load_type + '&from_date='+ from_date +'&to_date=' + to_date + '&user_id=' + user_id,
						async: false,
						url: 'index.php?entryPoint=entryPointLoadWorkingProcessDetail',
						success: function(output){
							$('#dlgViewDetailContent').html(output);
						}
					});
				}
			});

			// Filter by employee kpi type
			$('#btnSearchViewDetail').on('click', function(){
				var kpi_type 	= $.trim($('#employee_kpi_type :selected').val());
				var load_type 	= $.trim($('#load_type').val());
				var user_id 	= $.trim($('#user_id').val());
				var from_date 	= $('#from_date').val();
				var to_date 	= $('#to_date').val();

				$.ajax({	
					cache: false,
					type: 'post',
					data: 'load_type=' + load_type + '&from_date='+ from_date +'&to_date=' + to_date + '&user_id=' + user_id + '&kpi_type=' + kpi_type,
					async: false,
					url: 'index.php?entryPoint=entryPointLoadWorkingProcessDetail',
					success: function(output){
						$('#dlgViewDetailContent').html(output);
					}
				});
			});
			
			// Xem số dư của hãng vietjet và jetstar
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
						console.log(data);
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
			
			$('.allow-number-only2').on('keydown', function (event) {
				$(this).allowNumberOnly(event);
			});
			
			$(".showdt").click(function() {
				$.ajax({
					url: "index.php?entryPoint=entryPointFlightBookings",
					type: "POST",
					data: {
						employee: $(this).attr("employee"),
						type: $(this).attr("type"),
						date_search: $("#from_date").val(),
						line: $(this).attr("id"),
						for: "populateDetailMark",
					},
					beforeSend: function() {
						$("#mark_detail_tbl>tbody").html('...Loading');
					},
					success: function(response) {
						$("#mark_detail_tbl>tbody").html(response);
					}
				});

				// $("#mark_detail_tbl").dialog({
				$("#mark_detail_tbl--wrap").dialog({
					width: 700,
					title: "Chi tiết chấm điểm",
					modal: true,
					resizable: false,
					close: function() {
						$(".showhidehis").text("Xem lịch sử");
						$("#history_tbl").html("");
					},
				});
			});
			
			// jQuery plugin definition
			$.fn.allowNumberOnly = function(event) {
				if(event.shiftKey)
					return event.preventDefault();
				if (event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 110 || event.keyCode == 9 || event.keyCode == 190 || event.keyCode == 13 || event.keyCode == 189 || event.keyCode == 109) {
				}
				else {
					if (event.keyCode < 95) {
						if (event.keyCode < 48 || event.keyCode > 57) {
							if (event.keyCode >= 37 && event.keyCode <= 40) {  
							}
							else
							{
								return event.preventDefault();
							}
						}
					} 
					else {
						if (event.keyCode < 96 || event.keyCode > 105) {
							return event.preventDefault();
						}
					}
				}
			};
			
			$(document).on("click", "#done_btn", function() {
				$.ajax({
					url: "index.php?entryPoint=entryPointFlightBookings",
					type: "POST",
					data: {
						mark: $("#left_mark").text(),
						type: $("#mark_type").val(),
						mark_date: $("#from_date").val(),
						assigned_user: $("#assigned_user").val(),
						remark: $("#user_remark").val(),
						for: "saveMark",
					},
					beforeSend: function() {
						$("#mark-loading").show();
						$("#mark-loading").text("Đang lưu...");
					},
					success: function(response) {
						$("#mark-loading").hide();
						// $("#mark_detail_tbl").dialog("close");

						$("#mark_detail_tbl--wrap").dialog("close");
						$("#"+$("#line").val()).text($("#left_mark").text());
					},
				})
			});

			$(document).on("click", ".showhidehis", function() {
				if($(this).text() == "Xem lịch sử") {
					$(this).text("Rút gọn");
					$.ajax({
						url: "index.php?entryPoint=entryPointFlightBookings",
						type: "POST",
						data: {
							date: $("#from_date").val(),
							type: $("#mark_type").val(),
							assigned_user: $("#assigned_user").val(),
							for: "findingHistory",
						},
						beforeSend: function() {
							$("#mark-loading").show();
							$("#mark-loading").text("Đang tìm...");
						},
						success: function(response) {
							$("#mark-loading").hide();
							$("#history_tbl").html(response);
						},
					})
				} else {
					$(this).text("Xem lịch sử");
					$("#history_tbl").html("");
				}
			});

			$("#date_select").change(function() {
				$("#from_date").val($(this).find("option:selected").attr("fromdate"));
				$("#to_date").val($(this).find("option:selected").attr("todate"));

				sessionStorage.setItem('date_select_kpi', $(this).val());
				sessionStorage.removeItem('optionRadio_kpi');
			});

			$('input[type=radio][name=optionRadio]').change(function() {
				$('#from_date').val($('input[name=optionRadio]:checked').attr('fromdate'));
				$('#to_date').val($('input[name=optionRadio]:checked').attr('todate'));

				sessionStorage.setItem('optionRadio_kpi', $(this).val());
				sessionStorage.removeItem('date_select_kpi');
			});

			// Check sessionStorage - js
			const selectOption 		= document.getElementById('date_select');
			const radioOptions 		= document.getElementsByName('optionRadio');
			const savedSelectOption 	= sessionStorage.getItem('date_select_kpi');
			if (savedSelectOption) {
				selectOption.value = savedSelectOption;
			} else {
				const savedRadioOption = sessionStorage.getItem('optionRadio_kpi');
				if (savedRadioOption) {
					radioOptions.forEach(radio => {
						if (radio.value === savedRadioOption) {
							radio.checked = true;
						}
					});
				}
			}
		});

		function calculateLeftMark() {
			if($("#mark").val() != '' && $("#mark").val() != "-") {
				$("#left_mark").text(parseInt($("#curr_mark").text()) + parseInt($("#mark").val()));
			}
		}

    </script>
{/literal}

<div class="title-wrap d-flex align-items-center justify-content-between gap-2">
	<h1 class="title report-title">Phong Thần Bảng</h1>
	 <svg xmlns="http://www.w3.org/2000/svg" id="filter_report" width="32" height="32" fill="currentColor" class="bi bi-filter d-xxl-none d-xl-none d-lg-none d-block" viewBox="0 0 16 16">
		 <path d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5"/>
	 </svg>
 </div>

<div class="box-section position-relative mt-0 overflow-auto" id="top-area">
	<div class="overlay-mobile"></div>

	<form action="index.php" method="post" name="search_form" id="ec_search_form">
		<input type="hidden" name="module" value="EC_TongHop" />
		<input type="hidden" name="action" value="employeekpi" />
		<input type="hidden" name="load_type" id="load_type" value="" />
		<input type="hidden" name="user_id" id="user_id" value="" />

		<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-dash-lg search_form--dash d-xl-none d-lg-none d-block" viewBox="0 0 16 16">
			<path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8"></path>
		 </svg>

		<div class="action--wrap d-flex align-items-center gap-4">
			<select class="box-select" id="date_select" name="date_select">{$DATE_OPTION}</select>
			<div class="from-to-date--wrap d-inline-flex gap-2 align-items-center">
				<div class="d-flex gap-2 align-items-center date_trigger--wrap fdate_trigger--wrap">
					<span class="sublabel">Từ ngày: </span>    
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
							Calendar.setup ({
							inputField : "from_date",
							daFormat : "%d-%m-%Y",
							button : "fdate_trigger",
							singleClick : true,
							dateStr : "",
							step : 1,
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
					<span class="sublabel">Đến ngày: </span>    
					<div class="dateTime d-flex gap-2 position-relative">
						<input class="date_input box-input" type="text" maxlength="10" size="11" tabindex="103" title="" value="{$TO_DATE_VALUE}" id="to_date" name="to_date" autocomplete="off">
					    <button class="icon_dateTime" type="button" id="tdate_trigger" onclick="return false;">
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
							button : "tdate_trigger",
							singleClick : true,
							dateStr : "",
							step : 2
							}
							);
						</script>
						{/literal}
					</div>
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
		</div>
		
		
		<div class="button-action--wrap">
			{if !$IS_ADMIN}
				<select class="box-select" name="report_type" id="report_type">
					{$REPORT_TYPE_OPTION}
				</select>
			{/if}
			<input type="submit" id="btnView" name="btnView" class="btn btn-primary button-action" value="Xem" title="Xem" />
			<input type="button" id="btnSearch_cancel" value="Hủy bỏ" name="search_cancel" class="btn btn-secondary button-action--cancel d-xl-none d-lg-none d-block" title="Hủy bỏ"/>
		 </div>
	</form>

{if $IS_ADMIN || $ALL}
<table class="list-data list-data-all table-details__booking my-3" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th width="3%">STT</th>
			<th width="12%">Họ tên</th>
			<th width="5%" align="center"><span title="Called">CAL</span></th>
			<th width="5%" align="center"><span title="Booking hoàn tất">COM</span></th>
			<th width="5%" align="center"><span title="Đã thanh toán / Đã thu">DTT</span></th>
			<th width="5%" align="center"><span title="Recheck booking">RCE</span></th>
			<th width="5%" align="center"><span title="Recall">RCA</span></th>
			<th width="5%" align="center"><span title="Hóa đơn đầu vào">HDV</span></th>
			<th width="5%" align="center"><span title="Hóa đơn đầu ra">HDR</span></th>
			<th width="5%" align="center"><span title="Giao vé">GVE</span></th>
			<th width="5%" align="center"><span title="Đối chiếu công nợ">DCN</span></th>
			<th width="5%" align="center"><span title="Tạo phiếu hoàn vé">THV</span></th>
			<th width="5%" align="center"><span title="Lập phiếu chi">LPC</span></th>
			<th width="5%" align="center"><span title="Lập phiếu thu">LPT</span></th>
			<th width="5%" align="center"><span title="Lập phiếu điều chuyển tiền">DCT</span></th>
			<th width="5%" align="center"><span title="Hỗ trợ khác">SDL</span></th>
			<!-- <th width="5%" align="center"><span title="Chuyên môn">Chuyên môn</span></th>
			<th width="5%" align="center"><span title="Hiệu quả">Hiệu quả</span></th>
			<th width="5%" align="center"><span title="Ý thức">Ý thức</span></th>
			<th width="5%" align="center"><span title="Bị trừ">Bị trừ</span></th> -->
			<th align="center"><span title="Tổng cộng">Tổng cộng</span></th>
		</tr>
	</thead>
    {$ADMIN_DATA}
	<tr class="footer-tr">
		<td colspan="2" align="center">Tổng cộng</label></td>
		<td align="center"><span title="Called"></span></td>
		<td align="center"><span title="Completed">{$TTL_COMPLETED}</span></td>
		<td align="center"><span title="Đã thanh toán / Đã thu">{$TTL_PAID}</span></td>
		<td align="center"><span title="Recheck">{$TTL_RECHECK}</span></td>
		<td align="center"><span title="Recall">{$TTL_RECALL}</span></td>
		<td align="center"><span title="Hóa đơn đầu vào">{$TTL_INV_IN_ISSUED}</span></td>
		<td align="center"><span title="Hóa đơn đầu ra">{$TTL_INV_ISSUED}</span></td>
		<td align="center"><span title="Giao vé">{$TTL_DELIVERY}</span></td>
		<td align="center"><span title="Đối chiếu công nợ">{$TTL_COMDEBT}</span></td>
		<td align="center"><span title="Lập phiếu hoàn vé">{$TTL_NEW_REPAID}</span></td>
		<td align="center"><span title="Lập phiếu chi">{$TTL_PAYMENT}</span></td>
		<td align="center"><span title="Lập phiếu thu">{$TTL_RECEIPT}</span></td>
		<td align="center"><span title="Lập phiếu điều chuyển tiền">{$TTL_TRANSFER}</span></td>
		<td align="center"><span title="Hỗ trợ khác">{$TTL_SUPPORT}</span></td>
		<!-- <td align="center"><span title="Chuyên môn">{$TTL_MANNER}</span></td>
		<td align="center"><span title="Hiệu quả">{$TTL_EFFECTED}</span></td>
		<td align="center"><span title="Ý thức">{$TTL_AWARENESS}</span></td>
		<td align="center"><span title="Bị trừ">{$TTL_MINUS}</span></td> -->
		<td align="center"><span title="Tổng cộng">{$TTL_FINAL}</span></td>
	</tr>
</table>

<div id="mark_detail_tbl--wrap" class="p-2">
	<table id="mark_detail_tbl" class="table-details__booking" cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<th width="20%" align="center">Hiện tại</th>
				<th width="20%" align="center">Chấm điểm</th>
				<th width="20%" align="center">Kết quả</th>
				<th width="40%">Nhận xét</th>
			</tr>
		</thead>
		<tbody></tbody> 
		<tfoot>
			<tr>
				<td id="mark-loading" align="center" colspan="5"></td>
			</tr>
			<tr><td colspan="5" id="history_tbl"></td></tr>
		</tfoot>
	</table>
</div>

{elseif $OWNER}
<table class="summary-report table-details__booking my-3" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th>Booking</th>
			<th>Ticket</th>
			<th>Target</th>
			<th>% Achieved</th>
			<th>Bonus</th>
			<th>KPI</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="text-center"><img src="themes/SuiteP/images/modules/ec_flight_booking/flight_booking.svg" alt="booking" border="0" width="96" /></td>
			<td class="text-center"><img src="themes/SuiteP/images/modules/ec_flight_booking/ticket.svg" alt="ticket" border="0" width="96" /></td>
			<td class="text-center"><img src="themes/SuiteP/images/modules/ec_flight_booking/target.svg" alt="target" border="0" width="96" /></td>
			<td class="text-center"><img src="themes/SuiteP/images/modules/ec_flight_booking/percent.svg" alt="percent" border="0" width="96" /></td>
			<td class="text-center"><img src="themes/SuiteP/images/modules/ec_flight_booking/bonus.svg" alt="check" border="0" width="96" /></td>
			<td class="text-center"><img src="themes/SuiteP/images/modules/ec_flight_booking/kpi.svg" alt="plus" border="0" width="96" /></td>
		</tr>
		<tr class="footer-tr">
			<td>{$BOOKING_COUNT}</td>
			<td>{$TICKET_COUNT}<!--<a class="view-detail" load_type="total_ticket" load_name="số lượng vé đã xuất" href="#" title="Xem chi tiết"></a>--></td>
			<td>{$TICKET_TARGET}</td>
			<td>{$PERCENT_ACHIEVED}</td>
			<td>{$TOTAL_BONUS}<a class="view-detail" load_type="total_bonus" load_name="bonus" href="#" title="Xem chi tiết"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
				<path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2Z"/>
			   </svg></a></td>
			<td>{$TOTAL_KPI}<a class="view-detail" load_type="total_kpi" load_name="điểm KPI" href="#" title="Xem chi tiết"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
				<path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2Z"/>
			   </svg></a></td>
		</tr>
	</tbody>
</table>
{/if}

<div class="flex-start">
	<ul class="kpi-notes flex-fill">
		<li>
			<strong>CAL</strong>: <span>Cuộc gọi <span class="fw-bold text-dark">(Hoàn tất, có mô tả và hội thoại từ 20s trở lên (đi) hoặc có thoại (đến))</span></span>
		</li>
		<li>
			<strong>COM</strong>: <span>Booking <span class="fw-bold text-primary">hoàn tất</span></span>
		</li>
		<li>
			<strong>DTT</strong>: <span>Đã thanh toán / đã thu</span>
		</li>
		<li>
			<strong>RCE</strong>: <span>Recheck thông tin</span>
		</li>
		<li>
			<strong>RCA</strong>: <span>Recall cuộc gọi / Nhắc lịch bay khách hàng</span>
		</li>
		<li>
			<strong>HDV</strong>: <span>Xuất hóa đơn đầu vào</span>
		</li>
		<li>
			<strong>HDR</strong>: <span>Xuất hóa đơn đầu ra</span>
		</li>
	</ul>
	<ul class="kpi-notes flex-fill">
		<li>
			<strong>GVE</strong>: <span>Giao vé / giao thực phẩm</span>
		</li>
		<li>
			<strong>DCN</strong>: <span>Đối chiếu công nợ</span>
		</li>
		<li>
			<strong>THV</strong>: <span>Tạo phiếu hoàn vé</span>
		</li>
		<li>
			<strong>LPC</strong>: <span>Lập phiếu chi <span class="fw-bold text-primary">(Đã chi)</span></span>
		</li>
		<li>
			<strong>LPT</strong>: <span>Lập phiếu thu / PT đổi giờ bay, hành trình, tên khách / PT tiền hành lý <span class="fw-bold text-primary">(Đã thu)</span></span>
		</li>
		<li>
			<strong>DCT</strong>: <span>Lập phiếu điều chuyển tiền</span>
		</li>
		<li>
			<strong>SDL</strong>: <span>Hỗ trợ delay chuyến bay / Tư vấn qua Zalo OA / Hỗ trợ khác</span>
		</li>
	</ul>
</div>

</div>

<div id="dlgViewDetail" title="Xem chi tiết">
	<div id="dlgViewDetailFilter">
		<div class="d-flex align-items-center gap-2 pb-2 mb-2 border-bottom">
			<span class="label">Loại KPI:</span>
			<select class="box-select" id="employee_kpi_type">
				<option value="">Tất cả</option>
				{$EMPLOYEE_KPI_TYPE_LIST}
			</select>
			<input class="btn btn-primary" type="button" id="btnSearchViewDetail" value="Tìm" title="Tìm"></div>
		</div>
		<div id="dlgViewDetailContent"></div>
	</div>
</div>