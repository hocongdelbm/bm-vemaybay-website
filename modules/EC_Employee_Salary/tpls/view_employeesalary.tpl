<script src="custom/jqueryui/plugins/jquery.number.min.js"></script>
<script src="custom/jqueryui/plugins/formatNumber.js"></script>

{literal}
	<style>
		#employee_salary select{
			min-width: unset;
		}
	</style>
	<script>
		const url = "index.php?entryPoint=entryPointAbsence";
		$(document).ready(function() {
			var typingTimer;
			var doneTypingInterval = 10000;

			$(".allow-number-only").number(true, 0, dec_sep, num_grp_sep);
			$("#value_frm>table tbody tr.row-odd").css("background-color", "#f2f2f2");

			$("#btnAddRow").click(function() {
				var row_count = $("#row_count").val();
				row 	= insertCommissionRow(row_count);

				$("#commission>tbody").append(row);
				$(".allow-number-only").number(true, 0, dec_sep, num_grp_sep);
				row_count++;
				$("#row_count").val(row_count);
				markOrder("commission_deleted", "commission_order");
			});

			$("#save_btn").click(function() {
				$("#value_frm").submit();
				return false;
			});

			$(".show_detail").click(function() {
				var dt_type = $(this).attr("type");
				$("#assigned_user").val($(this).attr("employee"));
				$("#employee_full_name, #employee_full_name_allowance").text($(this).attr("employee_name"));
				
				$.ajax({
					type: "POST",
					url: url,
					data: {
						assigned_user_id: $("#assigned_user").val(),
						month: $("#month_detail").val(), 
						year: $("#year_detail").val(),
						detail: $("#is_detail").val(),
						type: $(this).attr("type"),
						overnight: $(this).attr("overnight"),
						delivery: $(this).attr("delivery"),
						view: $(this).attr("view"),
						for: "showAmountDetail",
					},
					success: function(response) {
						response = JSON.parse(response);
						if(response.hasOwnProperty("count")) {
							$("#detail_amt_total").text(response.total);
							// $("#amount_detail>tbody>tr:not(:first-child)").remove();
							$("#amount_detail>tbody>tr").remove();
							$("#row_count_detail").val(response.count);
							$("#amount_detail>tbody").append(response.html);
						} else {
							$("#dt_allowance_tbl>tbody").html(response.body);
						}
					}
				});
				if($(this).attr("type") == "allowance") {
					dialog_title = "Chi tiết phụ cấp";
				} else if($(this).attr("type") == "minus") {
					dialog_title = "Chi tiết giảm trừ";
					reason_col_name = "Lý do trừ";
					reason_option = "reason_minus_option";
				} else {
					dialog_title = "Chi tiết nỗ lực";
					reason_col_name = "Lý do thưởng";
					reason_option = "reason_bonus_option";
				}
				$("#title_line").text(dialog_title);
				if(typeof reason_col_name != 'undefined') {
					$("#reason_col").text(reason_col_name);
				}
				$("#type_save").val($(this).attr("type"));
				if($(this).attr("type") != 'allowance') {
					$("#detail_amount_frm").dialog({
						title: dialog_title,
						width: "1024",
						resizable: false,
						modal: true,
					});
				} else {
					// $("#dt_allowance_tbl").dialog({
					$("#dt_allowance_tbl--wrap").dialog({
						title: dialog_title,
						width: "300",
						resizable: false,
						modal: true,
					});
				}
			});

			$("#add_row_btn").click(function() {
				var rowCount 			= $("#row_count_detail").val();
				var created_user_name 	= $("#created_user_name").val();

				$("#amount_detail tbody").append(`<tr id='detail_line${rowCount}'> 
					<td id='detail_order${rowCount}' class='text-center'>${rowCount}</td> 
					<td class="text-center">
						<div class="d-flex align-items-center gap-2">
							<input type='text' class='date_input box-input' id='date_input${rowCount}' name='date_detail[]'>
							<img border='0' src='themes/SuiteP/images/Calendar.svg' alt='Enter Date' id='date_detail_trigger${rowCount}' align='absmiddle'>
						</div>
					</td> 
					<td><input type='text' class='allow-number-only box-input text-end' name='detail_amount[]' id='detail_amount${rowCount}' oninput='calculateTotalDetailAmount()'></td> 
					<td class='reason'><select class='reason_select box-select' name='reason[]' ln='${rowCount}'>${$("#"+reason_option).val()}</select></td> 
					<td><textarea class='description' name='detail_description[]'></textarea></td> 
					<td class='text-center'>${created_user_name}</td> 
					<td> 
						<button title='Xóa' type='button' class="button-remove-in-edit" onclick="markRowDeleted('detail_line', 'detail_deleted', 'detail_order', ${rowCount})"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg></button> 
						<input type='hidden' id='detail_deleted${rowCount}' name='detail_deleted[]' class='detail_deleted' value='0'> 
					</td>
				</tr>`
				);

				Calendar.setup ({
					inputField : 'date_input' + rowCount,
					daFormat : "%d-%m-%Y",
					button : 'date_detail_trigger' + rowCount,
					singleClick : true,
					dateStr : '',
					step : 1,
					weekNumbers : false
				});
				rowCount++;
				$("#row_count_detail").val(rowCount);
				$(".allow-number-only").number(true, 0, dec_sep, num_grp_sep);
				markOrder("detail_deleted", "detail_order");
			});

			$(".excerpt_salary").click(function() {
				$(".search_area").toggle();
			});

			// thay đổi lý do thưởng
			$(document).on("change", ".reason_select", function() {
				$(".reason_ext").remove();
				if($(this).val() == 'Thuong_DS') {
					$(this).parent().append(`
						<div class='reason_ext'>Chọn BK thưởng: 
							<input type='text' class='box-input' name='bk_name[]' id='bonus_bk_name${$(this).attr("ln")}'>
							<input type='hidden' name='bk_id[]' id='bonus_bk_id${$(this).attr("ln")}'>
							<button title='Tìm' type='button' class="button-pick" onclick='open_popup("EC_Flight_Bookings", 600, 400, "", true, false, {"call_back_function":"set_return","form_name":"EditView","field_to_name_array":{"id":"bonus_bk_id${$(this).attr("ln")}","name":"bonus_bk_name${$(this).attr("ln")}"}}, "single", true);'>
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M10 18a7.952 7.952 0 0 0 4.897-1.688l4.396 4.396 1.414-1.414-4.396-4.396A7.952 7.952 0 0 0 18 10c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8zm0-14c3.309 0 6 2.691 6 6s-2.691 6-6 6-6-2.691-6-6 2.691-6 6-6z"></path><path d="M11.412 8.586c.379.38.588.882.588 1.414h2a3.977 3.977 0 0 0-1.174-2.828c-1.514-1.512-4.139-1.512-5.652 0l1.412 1.416c.76-.758 2.07-.756 2.826-.002z"></path></svg>
							</button>
						</div>
					`);
				} 
			});
			
			// thay đổi cột không đạt ds
			$(".minusinc_edit").keyup(function() {
				var ln = $(this).attr("ln");
				var user = $(this).attr("user");
				var month = $(this).attr("month");
				var year = $(this).attr("year");
				var minus_inc = parseInt($(this).val()) || 0;
				var final_inc = parseInt($("#final_inc" + ln).attr("init_final_inc")) || 0;
				var new_final_inc = final_inc - minus_inc;
				$("#final_inc" + ln).text(formatNumber(new_final_inc));
				
				// tính tổng không đạt ds và tổng thực lãnh
				calTotalFinalInc();
				
				clearTimeout(typingTimer);
				typingTimer = setTimeout(doneTyping(user, month, year, ln, minus_inc, final_inc), doneTypingInterval);
			});
			
			$(".minusinc_edit").keydown(function () {
				clearTimeout(typingTimer);
			});
		});
		
		function doneTyping(user_id = '', month = '', year = '', ln = '', minus_inc = 0, final_inc = 0) {
			if(user_id != '') {
				$.ajax({
					type: "POST",
					url: url,
					data: {
						assigned_user_id: user_id,
						month: month, 
						year: year,
						minus_inc: minus_inc,
						for: "updateMinusIncome",
					},
					success: function(response) {
						
					}
				});
			}
		}
		
		function calTotalFinalInc() {
			var tt_minus_inc = tt_final_inc = 0;
			for(var i = 1; i <= $(".minusinc_edit").length; i++) {
				tt_minus_inc += unformatNumber($("#minus_inc" + i).val());
				tt_final_inc += unformatNumber($("#final_inc" + i).text());
			}
			$("#total_minus_inc").text(formatNumber(tt_minus_inc));
			$("#total_final_inc").text(formatNumber(tt_final_inc));
		}

		function calculateIncome(ln, is_reset_sal = 0) {
			var working_days 		= $("#working_days"+ln).val() || 0;
			var basic_salary 		= $("#basic_salary"+ln).val() || 0;
			var effective_salary 	= $("#efficient_wage"+ln).val() || 0;
			var allowance 			= unformatNumber($("#allowance"+ln).text());
			var minus 				= unformatNumber($("#minus"+ln).text());
			var government 			= unformatNumber($("#government"+ln).text());
			var minus_income 		= unformatNumber($("#minus_income"+ln).val()) || 0;
			var total 				= unformatNumber($("#total"+ln).val()) || 0;
			$("#salary"+ln).text(formatNumber(parseInt(basic_salary) + parseInt(effective_salary)));
			var total_new = (parseInt(basic_salary) + parseInt(effective_salary) + allowance) / 26 * working_days - parseInt(minus) - government - parseInt(minus_income);
			if(Math.round(total_new) != total && is_reset_sal) {
				setSalary(ln);
			} else {
				$("#total"+ln).val(formatNumber(total_new));
				$("#cusSalary"+ln).val(0);
			}
		}

		function calculateTotalAllowance(ln) {
			var gas_allowance 			= $("#gas_allowance"+ln).val() || 0;
			var lunch_allowance 		= $("#lunch_allowance"+ln).val() || 0;
			var tele_allowance 			= $("#tele_allowance"+ln).val() || 0;
			var responsible_allowance 	= $("#responsible_allowance"+ln).val() || 0;
			var seniority_allowance 		= $("#seniority_allowance"+ln).val() || 0;
			var other_allowance1 		= $("#other_allowance1"+ln).val() || 0;
			var other_allowance2 		= $("#other_allowance2"+ln).val() || 0;
			$("#total_allowance"+ln).text(formatNumber(parseInt(gas_allowance) + parseInt(lunch_allowance) + parseInt(tele_allowance) + parseInt(responsible_allowance) + parseInt(seniority_allowance) + parseInt(other_allowance1) + parseInt(other_allowance2)));
			$("#allowance"+ln).text(formatNumber(parseInt(gas_allowance) + parseInt(lunch_allowance) + parseInt(tele_allowance) + parseInt(responsible_allowance) + parseInt(seniority_allowance) + parseInt(other_allowance1) + parseInt(other_allowance2)));

			calculateIncome(ln);
		}

		function calculateTotalInsurance(ln) {
			var insurance_rate = $("#insurance_rate"+ln).val() || 0;
			$("#com_social_insurance"+ln).text(formatNumber(insurance_rate * 0.175));
			$("#com_health_insurance"+ln).text(formatNumber(insurance_rate * 0.03));
			$("#com_accident_insurance"+ln).text(formatNumber(insurance_rate * 0.01));
			$("#emp_social_insurance"+ln).text(formatNumber(insurance_rate * 0.08));
			$("#emp_health_insurance"+ln).text(formatNumber(insurance_rate * 0.015));
			$("#emp_accident_insurance"+ln).text(formatNumber(insurance_rate * 0.01));
			$("#government"+ln).text(formatNumber(insurance_rate * 0.08 + insurance_rate * 0.015 + insurance_rate * 0.01));
			calculateIncome(ln);
		}

		function insertCommissionRow(ln) {
			row = `<tr id="commission_line${ln}"> 
				<td class="text-center" id="commission_order${ln}">${ln}</td> 
				<td><input type="text" class="allow-number-only box-input" name="commission_from_amt[]"></td> 
				<td><input type="text" class="allow-number-only box-input" name="commission_to_amt[]"></td> 
				<td><input type="text" class="allow-number-only box-input" name="commission_percentage[]"></td> 
				<td class="text-center"> 
					<button title="Xóa" type="button" class="button-remove-in-edit" onclick="markRowDeleted('commission_line', 'commission_deleted', 'commission_order', ${ln})">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
					</button> 
					<input type="hidden" name="commission_deleted[]" id="commission_deleted${ln}" value="0" class="commission_deleted"> 
					<input type="hidden" name="commission_order[]" value="${ln}"> 
				</td> 
			</tr>`;
			return row;
		}

		function markRowDeleted(deleted_line, deleted_input, order_input, ln) {
			$("#"+deleted_line+ln).hide();
			$("#"+deleted_input+ln).val(1);
			markOrder(deleted_input, order_input);
			calculateTotalDetailAmount();
		}

		function markOrder(deleted_input, order_input) {
			var deleted = $("input[class=\'"+deleted_input+"\']");
			k = 1;
			for(i = 1; i <= deleted.length; i++) {
				if($("#"+deleted_input+i).val() == 0) {
					$("#"+order_input+i).text(k);
					k++;
				}
			}
		}

		function calculateTotalDetailAmount() {
			var deleted = $("input[class=\'detail_deleted\']");
			k = 1;
			total = 0;
			for(i = 1; i <= deleted.length; i++) {
				if($("#detail_deleted"+i).val() == 0) {
					total += unformatNumber($("#detail_amount"+i).val());
					k++;
				}
			}
			$("#detail_amt_total").text(formatNumber(total));
		}

		function setSalary(ln) {
			$("#basic_salary"+ln).val(0);
			$("#efficient_wage"+ln).val(0);
			$("#salary"+ln).text(0);
			$("#allowance"+ln).text(0);
			$("#sales"+ln).text(0);
			$("#government"+ln).text(0);
			$("#cusSalary"+ln).val(1);
		}
	</script>
{/literal}

{if $isEdit && !$isEffortDetail && !$isDetail}
<h1 class="title">Bảng tính lương tháng {$MONTH} năm {$YEAR}</h1>

<form id="search_frm" method="post" action="index.php">
	{$SAVE_BTN}
</form>

<div id="employee_salary">
	<input type="hidden" id="reason_minus_option" value="{$REASON_MINUS_OPTION}">
	<input type="hidden" id="reason_bonus_option" value="{$REASON_BONUS_OPTION}">
	
	<form id="value_frm" method="post" action="index.php">
		<input type="hidden" name="module" value="EC_Employee_Salary">
		<input type="hidden" name="action" value="employeesalary">
		<input type="hidden" name="for" value="Save">
		<input type="hidden" name="month" value="{$MONTH}">
		<input type="hidden" name="year" value="{$YEAR}">
		<input type="hidden" id="sig_digits" value="0">
		<input type="hidden" id="dec_seperator" value=".">
		<input type="hidden" id="grp_seperator" value=",">

		<div class="box-section box-employee_salary">
			<table cellpadding="0" cellspacing="0" id="employee_salary_tbl" class="employee_salary_tbl table-details__booking">
				<thead>
					<tr>
						<th width="5%">STT</th>
						<th width="20%">Họ tên NV</th>
						<th colspan="6">Thông tin chi tiết lương nhân viên</th>
					</tr>
				</thead>
				<tbody>
					{$SALARY_EDIT}
				</tbody>
			</table>
		</div>

		<!-- THƯỞNG DOANH SỐ -->
		<div class="box-section box-commission d-none">
			<h2 class="change-title">Thưởng doanh số áp dụng từ tháng <select class="box-select" name="month_search">{$MONTH_OPTION}</select> năm <select class="box-select" name="year_search">{$YEAR_OPTION}</select></h2>
			<table cellpadding="0" cellspacing="0" id="commission" class="table-details__booking table-commission">
				<thead>
					<th width="5%">Mức</th>
					<th width="30">Từ khoảng</th>
					<th width="30">Đến khoảng</th>
					<th width="20%">Phần trăm thưởng</th>
					<th width="5%"></th>
				</thead>
				<tbody>
					{$COMMISSION}
				</tbody>
				<tfoot>
					<tr class="footer-tr">
						<td colspan="5" class="text-start">
							<input type="button" id="btnAddRow" class="btn btn-primary" value="Thêm dòng">
							<input type="hidden" id="row_count" class="btn btn-primary" value="{$ROW_COUNT}">
						</td>
					</tr>
				</tfoot>
			</table>
		</div>

		<!-- PHỤ CẤP -->
		<div class="box-section box-employee_allowance">
			<h2 class="change-title">Bảng phụ cấp tháng {$MONTH} năm {$YEAR}</h2>
			<table cellpadding="0" cellspacing="0" id="employee_allowance" class="table-details__booking table-employee_allowance">
				<thead>
					<th width="3%">STT</th>
					<th width="16%">Họ tên NV</th>
					<th width="10%">Phụ cấp xăng xe</th>
					<th width="10%">Phụ cấp cơm trưa</th>
					<th width="10%">Phụ cấp điện thoại</th>
					<th width="11%">Phụ cấp trách nhiệm</th>
					<th width="10%">Phụ cấp thâm niên</th>
					<th width="10%">Phụ cấp khác 1</th>
					<th width="10%">Phụ cấp khác 2</th>
					<th width="10%">Tổng</th>
				</thead>
				<tbody>
					{$EMPLOYEE_ALLOWANCE}
				</tbody>
			</table>
		</div>

		<!-- BẢO HIỂM -->
		<div class="box-section box-employee_insurance">
			<h2 class="change-title">Tiền bảo hiểm tháng {$MONTH} năm {$YEAR}</h2>
			<table cellpadding="0" cellspacing="0" id="employee_insurance" class="table-employee_insurance table-details__booking">
				<thead>
					<th width="5%">STT</th>
					<th width="20%">Họ tên NV</th>
					<th width="15%">Mức đóng BHXH</th>
					<th width="10%">BHXH (17.5%)</th>
					<th width="10%">BHYT (3%)</th>
					<th width="10%">BHTN (1%)</th>
					<th width="10%">BHXH (8%)</th>
					<th width="10%">BHYT (1.5%)</th>
					<th width="10%">BHTN (1%)</th>
				</thead>
				<tbody>
					{$EMPLOYEE_INSURANCE}
				</tbody>
			</table>
		</div>
	</form>

	<form id="detail_amount_frm" method="post" action="index.php">
		<input type="hidden" name="module" value="EC_Employee_Salary">
		<input type="hidden" name="action" value="employeesalary">
		<input type="hidden" name="for" value="SaveAmountDetail">
		<input type="hidden" name="type" id="type_save">
		<input type="hidden" name="edit_btn">
		<input type="hidden" name="month" value="{$MONTH}" id="month_detail">
		<input type="hidden" name="year" value="{$YEAR}" id="year_detail">
		<h2 class="change-title text-center"><b><span id="title_line"></span> tháng {$MONTH} năm {$YEAR} <br>Nhân viên: <span id="employee_full_name"></span></b></h2>
		<table id="amount_detail" class="table-amount__detail table-details__booking" cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<th width="5%" class="text-center"><b>STT</b></th>
					<th width="20%" class="text-center"><b>Ngày chứng từ</b></th>
					<th width="15%" class="text-center"><b>Số tiền</b></th>
					<th width="20%" class="text-center"><b id="reason_col"></b></th>
					<th width="25%" class="text-center"><b>Ghi chú</b></th>
					<th width="10%" class="text-center"><b>Người tạo</b></th>
					<th width="5%" class="text-center"></th>
				</tr>
			</thead>
			<tbody>
			</tbody>
			<tfoot>
				<tr class="footer-tr">
					<td class=""></td>
					<td class="text-end"><b>Tổng</b></td>
					<td class="text-end" id="detail_amt_total"></td>
					<td class=""></td>
					<td class=""></td>
					<td class=""></td>
					<td class=""></td>
				</tr>
				<tr>
					<td colspan="5" class="no-padding-left">
						<div class="d-flex align-items-center gap-2 mt-2">
							<input type="button" class="btn btn-primary" value="Thêm dòng" id="add_row_btn">
							<input type="submit" class="btn btn-primary" value="Lưu" id="save_detail_btn">
							<input type="hidden" id="row_count_detail" value="{$ROW_COUNT_DETAIL}">
							<input type="hidden" id="created_user_name" value="{$CREATED_USER}">
							<input type="hidden" id="assigned_user" name="assigned_user">
						</div>
					</td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>
{/if}

{if !$isEdit && !$isEffortDetail && !$isDetail}
<div class="box-section">
	<div id="employee_salary">
		<form id="search_frm" method="post" action="index.php">
			<input type="hidden" name="module" value="EC_Employee_Salary">
			<input type="hidden" name="action" value="employeesalary">
			<input type="hidden" id="sig_digits" value="0">
			<input type="hidden" id="dec_seperator" value=".">
			<input type="hidden" id="grp_seperator" value=",">
			<span class="search_area">
				<h2 class="change-title d-flex align-items-center gap-2 flex-wrap">
					<span>Bảng tính lương tháng</span>
					<select class="box-select" name="month_search">{$MONTH_OPTION}</select>
					<span>/</span>
					<select class="box-select" name="year_search">{$YEAR_OPTION}</select> 
					<span>({$STATUS})</span>
				</h2>
				
				<div class="d-flex mb-3 align-items-center gap-2">
					<input type="submit" class="btn btn-primary" value="Tìm kiếm" id="search_btn">
					{$EDIT_BTN}
					{$APPROVED_BTN}
					{$EXCEL_BTN}
					{$EXCERPT_SALARY}
					{$UPDATE_SALARY}
				</div>
			</span>
			<span class="search_area hide">
				{$BACK_BTN}
				{$EXCERPT_SEARCH}
			</span>
		</form>
		<table cellpadding="0" cellspacing="0" id="employee_salary_tbl" class="table-details__booking employee_salary_tbl">
			<thead>
				<th class="hide-mobile">STT</th>
				<th>Họ tên NV</th>
				<th>Lương cứng</th>
				<th>Công</th>
				<th class="hide-mobile">Tạm tính</th>
				<th>Giảm trừ TN</th>
				<th class="hide-mobile">Khoản trừ</th>
				<th class="hide-mobile">Khoản cộng</th>
				<th class="hide-mobile">Thưởng DS</th>
				<th class="hide-mobile">BHXH NV</th>
				<th>Thực lãnh</th>
			</thead>
			<tbody>
				{$SALARY}
			</tbody>
		</table>
		<form id="detail_amount_frm" method="post" action="index.php">
			<input type="hidden" name="month" value="{$MONTH}" id="month_detail">
			<input type="hidden" name="year" value="{$YEAR}" id="year_detail">
			<input type="hidden" id="assigned_user" name="assigned_user">
			<input type="hidden" id="is_detail" value="1">
			<h2 class="change-title">
				<b>Chi tiết giảm trừ tháng {$MONTH} năm {$YEAR} <br>Nhân viên: <span id="employee_full_name"></span></b>
			</h2>
			<table id="amount_detail" class="table-amount__detail table-details__booking" cellpadding="0" cellspacing="0">
				<thead>
					<tr>
						<th width="5%" class="text-center"><b>STT</b></th>
						<th width="20%" class="text-center"><b>Ngày chứng từ</b></th>
						<th width="15%" class="text-center"><b>Số tiền</b></th>
						<th width="20%" class="text-center"><b>Lý do trừ</b></th>
						<th width="25%" class="text-center"><b>Ghi chú</b></th>
						<th width="15%" class="text-center"><b>Người tạo</b></th>
					</tr>
				</thead>
				<tbody>
				</tbody>
				<tfoot>
					<tr class="footer-tr">
						<td class=""></td>
						<td class="text-end"><b>Tổng</b></td>
						<td class="text-end" id="detail_amt_total"></td>
						<td class=""></td>
						<td class=""></td>
						<td class=""></td>
					</tr>
				</tfoot>
			</table>
		</form>
		<div id="dt_allowance_tbl--wrap">
			<table id="dt_allowance_tbl" class="table-details__booking table-allowance_dt w-100" cellpadding="0" cellspacing="0">
				<thead>
					<tr class="text-center">
						<th colspan="6"><b>Chi tiết phụ cấp tháng {$MONTH} năm {$YEAR} <br>Nhân viên: <span id="employee_full_name_allowance"></span></b></th>
					</tr>
				</thead>
				<tbody></tbody>
				<tfoot></tfoot>
			</table>
		</div>
	</div>
</div>
{/if}

{if $isDetail}
<h1 class="title">Bảng lương tháng {$MONTH} năm {$YEAR} {$SAVE_BTN} <br>Nhân viên: {$EMPLOYEE_NAME}</h1>
<div class="box-section">
<div id="employee_salary">
	<form name="index.php" method="post">
		<input type="hidden" name="module" value="EC_Employee_Salary">
		<input type="hidden" name="action" value="employeesalary">
		<input type="hidden" name="user_id" value="{$USER}">
		<input type="hidden" name="month" value="{$MONTH}">
		<input type="hidden" name="year" value="{$YEAR}">
		<table cellpadding="0" cellspacing="0" class="table-details__booking table-employee_salary w-50" id="detail_tbl">
			<thead>
				<tr>
					<th width="60%">Họ và tên</th>
					<th width="40%">{$EMPLOYEE_NAME}</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Ngày công thực tế</td>
					<td class="text-end">{$WORKING_DAYS}</td>
				</tr>
				<tr>
					<td>Lương cơ bản</td>
					<td class="text-end">{$BASIC_SALARY}</td>
				</tr>
				<tr>
					<td>Lương hiệu quả</td>
					<td class="text-end">{$EFFICIENT_WAGE}</td>
				</tr>
				<tr>
					<td>Phụ cấp các loại</td>
					<td class="text-end">{$ALLOWANCE}</td>
				</tr>
				<tr>
					<td><b class="text-primary">Lương cứng</b></td>
					<td class="text-end">{$INCOME}</td>
				</tr>	
				<tr>
					<td><b>Các khoản có</b></td>
					<td class="text-end"><b class="text-primary">{$TOTAL_BONUS}</b></td>
				</tr>
				<tr>
					<td class="salary_dt">Lương tạm tính</td>
					<td class="text-end">{$TEMP_SALARY}</td>
				</tr>
				<tr>
					<td class="salary_dt">Thưởng doanh số</td>
					<td class="text-end">{$SALES}</td>
				</tr>
				<tr>
					<td class="salary_dt">Hỗ trợ</td>
					<td class="text-end">{$OVERNIGHT_DELIVERY}</td>
				</tr>
				{$BONUS_DETAIL}
				<tr>
					<td><b>Các khoản trừ</b></td>
					<td class="text-end"><b class="color-red">{$TOTAL_MINUS}</b></td>
				</tr>
				<tr>
				    <td class="salary_dt">Không đạt doanh số</td>
					<td class="text-end">{$MINUS_INCOME}</td>
				</tr>
				{$MINUS_DETAIL}
				<tr>
					<td class="salary_dt">BHXH NLĐ đóng</td>
					<td class="text-end">{$EMP_SOCAIL_INSURANCE}</td>
				</tr>
				<tr>
					<td><b>Tổng thu nhập</b></td>
					<td class="text-end"><b style="color: var(--green-color);">{$TOTAL_INCOME}</b></td>
				</tr>
				
				<tr>
					<td>Bút phê</td>
					<td>{$REVIEW}</td>
				</tr>
				<tr>
					<td>BHXH DN đóng</td>
					<td class="text-end">{$COM_SOCIAL_INSURANCE}</td>
				</tr>
			</tbody>
		</table>
	</form>
</div>
</div>
{/if}

{if $isEffortDetail}
<h1 class="title">
	Chi tiết nỗ lực tháng {$MONTH} năm {$YEAR} của Nhân viên: {$EMPLOYEE_NAME}
</h1>
<div class="box-section">
	<div id="employee_salary">
		<form method="post" action="index.php" class="inline">
			<input type="hidden" name="module" value="EC_Employee_Salary">
			<input type="hidden" name="action" value="employeesalary">
			<input type="hidden" name="for" value="effortdetail">
			<input type="hidden" name="user_id" value="{$EMPLOYEE}">
			<input type="hidden" name="month" value="{$MONTH}">
			<input type="hidden" name="year" value="{$YEAR}">

			<div class="d-flex align-items-center gap-2 mb-2">
				<select name="detail_type" class="box-select">
					{$EFFORT_OPTION}
				</select>
				<input type="submit" class="btn btn-primary" value="Lọc">
			</div>
		</form>
		<table cellpadding="0" cellspacing="0" width="100%" class="table-details__booking table-detail_effort" id="detail_effort_tbl">
			<thead>
				<th>STT</th>
				<th>Ngày giờ bắt đầu xử lý<br>(Thời điểm bấm nút "Đã gọi")</th>
				<th>Ngày giờ CK</th>
				<th>Tên phiếu</th>
				<th>Doanh số</th>
				<th>SL vé</th>
				<!-- <th>Cú đêm</th> -->
				<th>Giao vé</th>
				<!-- <th>Booking xử lý</th> -->
				<th>Thưởng</th>
			</thead>
			<tbody>
				{$DETAIL_TBL}
			</tbody>
		</table>
	</div>
</div>
{/if}