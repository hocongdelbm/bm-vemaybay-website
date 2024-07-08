<script src="custom/jqueryui/plugins/jquery.number.min.js"></script>
<script src="custom/jqueryui/plugins/formatNumber.js"></script>
{literal}
	<style>
		#employee_salary_tbl tbody tr:hover,
		#employee_allowance tbody tr:hover,
		#employee_insurance tbody tr:hover,
		#commission tbody tr:hover,
		#detail_effort_tbl tr:hover,
		#detail_tbl tbody tr:hover {
			background-color: #cfeafe;
		}

		#employee_salary_tbl th, #employee_salary_tbl td
		, #employee_allowance th, #employee_allowance td
		, #employee_insurance th, #employee_insurance td
		, #commission thead th, #commission tbody td
		, #detail_tbl th, #detail_tbl td
		, #detail_effort_tbl th, #detail_effort_tbl td {
			border: 1px solid #ccc;
			padding: 5px;
		}

		#employee_salary_tbl input,
		#employee_allowance input,
		#employee_insurance input {
			text-align: right;
		}

		#commission input {
			text-align: right;
		}

		#search_frm * {
			display: inline-block;
		}
		.center {
			text-align: center;
		}
		.right {
			text-align: right;
		}

		.show_detail {
			text-decoration: underline;
    		color: #4e8ccf;
		}

		.show_detail:hover {
			color: red;
		}

		#minus_frm {
			display: none;
		}

		#minus_detail {
			border-collapse: collapse;
			width: 100% !important;
			font-size: 11pt;
			font-family: Calibri, sans-serif;
		}

		#minus_detail thead tr:first-child {
			font-size: 12pt;
		}

		#minus_detail td {
			padding: 5px;
		}

		#minus_detail tbody {
			border: 1px solid #000;
		}

		#minus_detail tbody td, #minus_detail .border {
			border: 1px dashed #000;
		}

		#minus_detail tbody td input {
			width: 97%;
		}

		#minus_total {
			font-weight: bold;
		}

		.no-padding-left {
			padding-left: 0 !important;
		}

		.date_input {
		    width: 75% !important;
			margin-right: 5px;
    			vertical-align: middle;
		}
	</style>
	<script>
		const url = "index.php?entryPoint=entryPointAbsence";
		$(document).ready(function() {
			$(".allow-number-only").number(true, 0, dec_sep, num_grp_sep);
			$("#btnAddRow").click(function() {
				var row_count = $("#row_count").val();
				row = insertCommissionRow(row_count);
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
				$("#assigned_user").val($(this).attr("employee"));
				$("#employee_full_name").text($(this).attr("employee_name"));
				$("#minus_total").text($(this).attr("minus"));
				$.ajax({
					type: "POST",
					url: url,
					data: {
						assigned_user_id: $("#assigned_user").val(),
						month: $("#month_minus").val(), 
						year: $("#year_minus").val(),
						detail: $("#is_detail").val(),
						for: "showMinusDetail",
					},
					success: function(response) {
						response = JSON.parse(response);
						$("#minus_detail>tbody>tr:not(:first-child)").remove();
						$("#minus_detail>tbody").append(response.html);
						$("#row_count_minus").val(response.count);
					}
				});
				$("#minus_frm").dialog({
					title: "Chi tiết giảm trừ",
					width: "800",
					resizable: false,
					modal: true,
				});

				
			});

			$("#add_row_btn").click(function() {
				var rowCount = $("#row_count_minus").val();
				var created_user_name = $("#created_user_name").val();
				$("#minus_detail tbody").append("<tr id=\'minus_line"+rowCount+"\'> \
					<td id=\'minus_order"+rowCount+"\' class=\'center\'>"+rowCount+"</td> \
					<td><input type=\'text\' class=\'date_input\' id=\'date_input"+rowCount+"\' name=\'date_minus[]\'><img border=\'0\' src=\'themes/default/images/jscalendar.gif\' alt=\'Enter Date\' id=\'minus_date_trigger"+rowCount+"\' align=\'absmiddle\'></td> \
					<td><input type=\'text\' class=\'allow-number-only right\' name=\'minus_amount[]\'></td> \
					<td class=\'reason\'><select name=\'reason[]\'>"+$("#reason_option").val()+"</select></td> \
					<td><textarea class=\'description\' name=\'minus_description[]\'></textarea></td> \
					<td class=\'center\'>"+created_user_name+"</td> \
					<td> \
						<button title=\'Xóa\' type=\'button\' onclick=\"markRowDeleted(\'minus_line\', \'minus_deleted\', \'minus_order\', "+rowCount+")\" style=\'background:transparent; border:0;\'><img src=\'custom/themes/default/images/delete_16x16.png\'></button> \
						<input type=\'hidden\' id=\'minus_deleted"+rowCount+"\' name=\'minus_deleted[]\' value=\'0\'> \
					</td> \
				</tr>");
				Calendar.setup ({
					inputField : 'date_input' + rowCount,
					daFormat : "%d-%m-%Y",
					button : 'minus_date_trigger' + rowCount,
					singleClick : true,
					dateStr : '',
					step : 1,
					weekNumbers : false
				});
				rowCount++;
				$("#row_count_minus").val(rowCount);
				$(".allow-number-only").number(true, 0, dec_sep, num_grp_sep);
				markOrder("minus_deleted", "minus_order");
			});
		});

		function calculateIncome(ln) {
			var basic_salary = $("#basic_salary"+ln).val() || 0;
			var effective_salary = $("#efficient_wage"+ln).val() || 0;
			var allowance = unformatNumber($("#allowance"+ln).text());
			var minus = $("#minus"+ln).val() || 0;
			var government = unformatNumber($("#government"+ln).text());
			$("#salary"+ln).text(formatNumber(parseInt(basic_salary) + parseInt(effective_salary)));
			$("#total"+ln).text(formatNumber(parseInt(basic_salary) + parseInt(effective_salary) + allowance - parseInt(minus) - government));
		}

		function calculateTotalAllowance(ln) {
			var gas_allowance = $("#gas_allowance"+ln).val() || 0;
			var lunch_allowance = $("#lunch_allowance"+ln).val() || 0;
			var tele_allowance = $("#tele_allowance"+ln).val() || 0;
			var responsible_allowance = $("#responsible_allowance"+ln).val() || 0;
			var seniority_allowance = $("#seniority_allowance"+ln).val() || 0;
			var other_allowance1 = $("#other_allowance1"+ln).val() || 0;
			var other_allowance2 = $("#other_allowance2"+ln).val() || 0;
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
			row = '<tr id="commission_line'+ln+'"> \
				<td class="center" id="commission_order'+ln+'">'+ln+'</td> \
				<td><input type="text" class="allow-number-only" name="commission_from_amt[]"></td> \
				<td><input type="text" class="allow-number-only" name="commission_to_amt[]"></td> \
				<td><input type="text" class="allow-number-only" name="commission_percentage[]"></td> \
				<td class="center"> \
					<button title="Xóa" type="button" onclick="markRowDeleted("commission_line", "commission_deleted", "commission_order", '+ln+')" style="background:transparent; border:0;"><img src="custom/themes/default/images/delete_16x16.png"></button> \
					<input type="hidden" name="commission_deleted[]" id="commission_deleted'+ln+'" value="0"> \
					<input type="hidden" name="commission_order[]" value="'+ln+'"> \
				</td> \
			</tr>';
			return row;
		}

		function markRowDeleted(deleted_line, deleted_input, order_input, ln) {
			$("#"+deleted_line+ln).hide();
			$("#"+deleted_input+ln).val(1);
			markOrder(deleted_input, order_input);
		}

		function markOrder(deleted_input, order_input) {
			var deleted = $("input[name=\'"+deleted_input+"[]\']");
			k = 1;
			for(i = 1; i <= deleted.length; i++) {
				if($("#"+deleted_input+i).val() == 0) {
					$("#"+order_input+i).text(k);
					k++;
				}
			}
		}
	</script>
{/literal}

{if $isEdit && !$isEffortDetail && !$isDetail}
<div id="employee_salary">
	<form id="search_frm" method="post" action="index.php">
		<h2 class="change-title">Bảng tính lương tháng {$MONTH} năm {$YEAR}</h2>
		{$SAVE_BTN}
	</form>
	<input type="hidden" id="reason_option" value="{$REASON_OPTION}">
	<form id="value_frm" method="post" action="index.php">
		<input type="hidden" name="module" value="EC_LeaveAbsences">
		<input type="hidden" name="action" value="employeesalary">
		<input type="hidden" name="for" value="Save">
		<input type="hidden" name="month" value="{$MONTH}">
		<input type="hidden" name="year" value="{$YEAR}">
		<input type="hidden" id="sig_digits" value="0">
		<input type="hidden" id="dec_seperator" value=".">
		<input type="hidden" id="grp_seperator" value=",">
		<table class="table-details__booking" cellpadding="0" cellspacing="0" id="employee_salary_tbl">
			<thead>
				<th width="3%">STT</th>
				<th width="15%">Họ tên NV</th>
				<th width="8%">Lương CB</th>
				<th width="8%">Lương HQ</th>
				<th width="8%">Thu nhập</th>
				<th width="8%">Phụ cấp</th>
				<th width="10%">Giảm trừ</th>
				<th width="10%">Nỗ lực</th>
				<th width="10%">Thưởng DS</th>
				<th width="10%%">Nhà nước</th>
				<th width="10%%">Thực lãnh</th>
			</thead>
			<tbody>
				{$SALARY_EDIT}
			</tbody>
		</table>
		<br></br>
		<h2 class="change-title">Thưởng doanh số áp dụng từ tháng <select class="box-select" name="month_search">{$MONTH_OPTION}</select> năm <select class="box-select" name="year_search">{$YEAR_OPTION}</select></h2>
		<table cellpadding="0" cellspacing="0" id="commission">
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
				<tr>
					<td>
						<input type="button" id="btnAddRow" value="Thêm dòng">
						<input type="hidden" id="row_count" value="{$ROW_COUNT}">
					</td>
				</tr>
			</tfoot>
		</table>
		<br><br>
		<h2>Bảng phụ cấp tháng {$MONTH} năm {$YEAR}</h2>
		<table cellpadding="0" cellspacing="0" id="employee_allowance">
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
		<br></br>
		<h2>Tiền bảo hiểm tháng {$MONTH} năm {$YEAR}</h2>
		<table cellpadding="0" cellspacing="0" id="employee_insurance">
			<thead>
				<th width="4%">STT</th>
				<th width="15%">Họ tên NV</th>
				<th width="15%">Mức đóng BHXH</th>
				<th width="11%">BHXH (17.5%)</th>
				<th width="11%">BHYT (3%)</th>
				<th width="11%">BHTN (1%)</th>
				<th width="11%">BHXH (8%)</th>
				<th width="11%">BHYT (1.5%)</th>
				<th width="11%">BHTN (1%)</th>
			</thead>
			<tbody>
				{$EMPLOYEE_INSURANCE}
			</tbody>
		</table>
	</form>
	<form id="minus_frm" method="post" action="index.php">
		<input type="hidden" name="module" value="EC_LeaveAbsences">
		<input type="hidden" name="action" value="employeesalary">
		<input type="hidden" name="for" value="SaveMinus">
		<input type="hidden" name="edit_btn">
		<input type="hidden" name="month" value="{$MONTH}" id="month_minus">
		<input type="hidden" name="year" value="{$YEAR}" id="year_minus">
		<table id="minus_detail" cellpadding="0" cellspacing="0">
			<thead>
				<tr class="center">
					<td colspan="6"><b>Chi tiết giảm trừ tháng {$MONTH} năm {$YEAR} <br>Nhân viên: <span id="employee_full_name"></span></b></td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td width="5%" class="center border"><b>STT</b></td>
					<td width="20%" class="center border"><b>Ngày chứng từ</b></td>
					<td width="15%" class="center border"><b>Số tiền</b></td>
					<td width="20%" class="center border"><b>Lý do trừ</b></td>
					<td width="25%" class="center border"><b>Ghi chú</b></td>
					<td width="10%" class="center border"><b>Người tạo</b></td>
					<td width="5%" class="center border"></td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="5" class="no-padding-left">
						<input type="button" value="Thêm dòng" id="add_row_btn">
						<input type="submit" value="Lưu" id="save_minus_btn">
						<input type="hidden" id="row_count_minus" value="{$ROW_COUNT_MINUS}">
						<input type="hidden" id="created_user_name" value="{$CREATED_USER}">
						<input type="hidden" id="assigned_user" name="assigned_user">
					</td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>
{/if}

{if !$isEdit && !$isEffortDetail && !$isDetail}
<div id="employee_salary">
	<form id="search_frm" method="post" action="index.php">
		<input type="hidden" name="module" value="EC_LeaveAbsences">
		<input type="hidden" name="action" value="employeesalary">
		<h2 class="change-title">Bảng tính lương tháng <select class="box-select" name="month_search">{$MONTH_OPTION}</select> năm <select class="box-select" name="year_search">{$YEAR_OPTION}</select></h2>
		<input class="btn btn-primary" type="submit" value="Tìm kiếm" id="search_btn">
		{$EDIT_BTN}
		{$APPROVED_BTN}
	</form>
	<table class="table-details__booking" cellpadding="0" cellspacing="0" id="employee_salary_tbl">
		<thead>
			<th>STT</th>
			<th>Họ tên NV</th>
			<th>Thu nhập</th>
			<th>Phụ cấp</th>
			<th>Ngày công</th>
			<th>Số ngày nghỉ</th>
			<th>Ngoài giờ</th>
			<th>Giảm trừ</th>
			<th>Nỗ lực</th>
			<th>Thưởng DS</th>
			<th>Nhà nước</th>
			<th>Thực lãnh</th>
		</thead>
		<tbody>
			{$SALARY}
		</tbody>
	</table>
	<form id="minus_frm" method="post" action="index.php">
		<input type="hidden" name="month" value="{$MONTH}" id="month_minus">
		<input type="hidden" name="year" value="{$YEAR}" id="year_minus">
		<input type="hidden" id="assigned_user" name="assigned_user">
		<input type="hidden" id="is_detail" value="1">
		<table id="minus_detail" cellpadding="0" cellspacing="0">
			<thead>
				<tr class="center">
					<td colspan="6"><b>Chi tiết giảm trừ tháng {$MONTH} năm {$YEAR} <br>Nhân viên: <span id="employee_full_name"></span></b></td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td width="5%" class="center border"><b>STT</b></td>
					<td width="20%" class="center border"><b>Ngày chứng từ</b></td>
					<td width="15%" class="center border"><b>Số tiền</b></td>
					<td width="20%" class="center border"><b>Lý do trừ</b></td>
					<td width="25%" class="center border"><b>Ghi chú</b></td>
					<td width="15%" class="center border"><b>Người tạo</b></td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td class="border"></td>
					<td class="right border"><b>Tổng</b></td>
					<td class="right border" id="minus_total"></td>
					<td class="border"></td>
					<td class="border"></td>
					<td class="border"></td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>
{/if}

{if $isDetail}
<div id="employee_salary">
	<form name="index.php" method="post">
		<h2>Chi tiết bảng lương tháng {$MONTH} năm {$YEAR} {$SAVE_BTN} <br>Nhân viên: {$EMPLOYEE_NAME}</h2>
		<input type="hidden" name="module" value="EC_LeaveAbsences">
		<input type="hidden" name="action" value="employeesalary">
		<input type="hidden" name="user_id" value="{$USER}">
		<input type="hidden" name="month" value="{$MONTH}">
		<input type="hidden" name="year" value="{$YEAR}">
		<table cellpadding="0" cellspacing="0" id="detail_tbl">
			<tbody>
				<tr>
					<td width="60%">Họ và tên</td>
					<td width="40%" class="right">{$EMPLOYEE_NAME}</td>
				</tr>
				<tr>
					<td>Công được tính</td>
					<td class="right">{$WORKING_DAYS}</td>
				</tr>
				<tr>
					<td>Phép sử dụng</td>
					<td class="right">{$USED_LEAVE}</td>
				</tr>
				<tr>
					<td>Lương cơ bản</td>
					<td class="right">{$BASIC_SALARY}</td>
				</tr>
				<tr>
					<td>Lương hiệu quả</td>
					<td class="right">{$EFFICIENT_WAGE}</td>
				</tr>

				<tr>
					<td><b><font color="blue">Thu nhập (Lương CB + Lương hiệu quả)</font></b></td>
					<td class="right">{$INCOME}</td>
				</tr>
				<tr>
					<td>BHXH DN đóng</td>
					<td class="right">{$COM_SOCIAL_INSURANCE}</td>
				</tr>
				<tr>
					<td><b>Các khoản có</b></td>
					<td class="right"><b><font color="blue">{$TOTAL_BONUS}</font></b></td>
				</tr>
				<tr>
					<td>Lương tạm tính</td>
					<td class="right">{$TEMP_SALARY}</td>
				</tr>
				<tr>
					<td>Phụ cấp</td>
					<td class="right">{$ALLOWANCE}</td>
				</tr>
				<tr>
					<td>Thưởng DS</td>
					<td class="right">{$BONUS}</td>
				</tr>
				<tr>
					<td>Hỗ trợ / làm đêm</td>
					<td class="right">{$OVERNIGHT}</td>
				</tr>
				<tr>
					<td>Hỗ trợ / giao vé</td>
					<td class="right">{$DELIVERY}</td>
				</tr>
				<tr>
					<td><b>Các khoản trừ</b></td>
					<td class="right"><b><font color="red">{$TOTAL_MINUS}</font></b></td>
				</tr>
				{$MINUS_DETAIL}
				<tr>
					<td>BHXH NLĐ đóng</td>
					<td class="right">{$EMP_SOCAIL_INSURANCE}</td>
				</tr>
				<tr>
					<td><b>Tổng thu nhập</b></td>
					<td class="right"><b><font size="2" color="green">{$TOTAL_INCOME}</font></b></td>
				</tr>
				<tr>
					<td>Thực nhận</td>
					<td>{$ACTUAL_SALARY}</td>
				</tr>
				<tr>
					<td>Bút phê</td>
					<td>{$REVIEW}</td>
				</tr>
			</tbody>
		</table>
	</form>
{/if}

{if $isEffortDetail}
<div id="employee_salary">
	<h2>Chi tiết nỗ lực tháng {$MONTH} năm {$YEAR} Nhân viên: {$EMPLOYEE_NAME}</h2>
	<table cellpadding="0" cellspacing="0" width="100%" id="detail_effort_tbl">
		<thead>
			<th>STT</th>
			<th>Ngày giờ tạo</th>
			<th>Ngày giờ CK</th>
			<th>Booking</th>
			<th>Số lượng vé</th>
			<th>Cú đêm</th>
			<th>Giao vé</th>
		</thead>
		<tbody>
			{$DETAIL_TBL}
		</tbody>
	</table>
</div>
{/if}