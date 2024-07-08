{literal}
	<style>
		.inline {
			display: inline-block;
		}
		.bold {
			font-weight: bold;
		}
	</style>
	<script>
		$(document).ready(function() {
			$("#year_select").change(function() {
				$("#backupfund_frm").submit();
			});
		});
	</script>
{/literal}
<form id="backupfund_frm" method="post" action="index.php">
	<input type="hidden" name="module" value="EC_Employee_Salary">
	<input type="hidden" name="action" value="backupfund">
	<h2 class="inline">Quỹ dự phòng năm</h2>
	<select class="inline" name="year" id="year_select">{$YEAR}</select>
	<table id="backupfund_tbl" class="table-details__booking table-backupfund" cellpadding="0" cellspacing="0">
		<thead>
			<th width="10%">Tháng</th>
			<th>Dự phòng</th>
			<th>Giảm trừ khác</th>
			<th>Tổng</th>
		</thead>
		<tbody>
			{$BACKUPFUND}
		</tbody>
	</table>
</form>