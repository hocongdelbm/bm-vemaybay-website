{*<script src="custom/jqueryui/plugins/TableHeaderFixed/x.js"></script>
<script src="custom/jqueryui/plugins/TableHeaderFixed/xtableheaderfixed.js"></script>
<script src="modules/EC_Employee_Salary/js/ec_employee_salary.js"></script>*}

<script src="custom/jqueryui/plugins/jquery.number.min.js"></script>
<script src="custom/jqueryui/plugins/formatNumber.js"></script>
{literal}
	<style>
		#extramoney_frm select{
			min-width: unset;
		}
	</style>
	<script>
		$(document).ready(function() {
			$("#grp_seperator").val(num_grp_sep);
			$("#dec_seperator").val(dec_sep);
			$(".allow-number-only").number(true, 0, dec_sep, num_grp_sep);

			$('input:text').bind("keypress", function(e) {
				if (e.keyCode == 13) return false;
			});
		});

		function calculateExtra(ln) {
			$("#actual_salary"+ln).text(
				formatNumber(
					unformatNumber($("#salary"+ln).text()) 
					+ unformatNumber($("#extra_amount"+ln).val()) 
					- unformatNumber($("#minus_amount"+ln).val())
				)
			);
			$("#actual_salary_val"+ln).val(
				formatNumber(
					unformatNumber($("#salary"+ln).text()) 
					+ unformatNumber($("#extra_amount"+ln).val())
					- unformatNumber($("#minus_amount"+ln).val())
				)
			);
			$("#extra_amount"+ln).val(formatNumber(unformatNumber($("#extra_amount"+ln).val())));
			$("#minus_amount"+ln).val(formatNumber(unformatNumber($("#minus_amount"+ln).val())));
		}
	</script>
{/literal}
<h1 class="title">BẢNG THU NHẬP</h1>
<div class="box-section">
	<form id="extramoney_frm" method="post" action="index.php">
		<input type="hidden" name="module" value="EC_Employee_Salary">
		<input type="hidden" name="action" value="extramoney">
		<div class="d-flex gap-2 align-items-center mb-3 flex-wrap">
			<h2 class="d-flex align-items-center gap-2 change-title mb-0">
				<span>Bảng thu nhập</span>
				<select class="box-select" name="month">{$MONTH}</select>
				<span>/</span>
				<select class="box-select" name="year">{$YEAR}</select>
			</h2>
			<div class="button-wrap">
				<input type="submit" class="btn btn-primary" value="Tìm kiếm">
				<input type="submit" class="btn btn-primary" value="Lưu" name="btnSaveExtra">
			</div>
		</div>

		<table id="extramoney_tbl" class="extramoney_tbl table-details__booking" cellpadding="0" cellspacing="0">
			<thead>
				<th width="3%">STT</th>
				<th width="15%">Họ tên</th>
				<th width="8%">Thu nhập</th>
				<th width="8%">Phụ cấp</th>
				<th width="10%">Thu nhập<br>tạm tính</th>
				<th width="10%">Tổng thưởng</th>
				<th width="10%">Tổng trừ</th>
				<th width="10%">Thực lãnh</th>
				<th width="8%">Khoản cộng</th>
				<th width="8%">Khoản trừ</th>
				<th width="10%">Thực nhận</th>
			</thead>
			<tbody>
				{$DATA}
			</tbody>
		</table>
	</form>
</div>