{literal}
<style>
	#usedleaveday_tbl tbody tr:hover {
		background-color: #cfeafe;
	}
</style>
<script>
	$(document).ready(function() {
		$("#year_search").change(function() {
			$("#search_frm").submit();
		});
	});
</script>
{/literal}

<h1 class="title">SỐ NGÀY PHÉP</h1>
<div id="usedleaveday" class="box-section overflow-auto">
	<form method="post" action="index.php" id="search_frm">
		<input type="hidden" name="module" value="EC_Employee_Salary">
		<input type="hidden" name="action" value="usedleaveday">
		<h2 class="change-title">Số ngày phép đã sử dụng năm 
			<select class="box-select" id="year_search" name="year_search">
				{$YEAR_OPTION}
			</select>
		</h2>
	</form>
	<table id="usedleaveday_tbl" class="table-usedleaveday table-details__booking" cellpadding="0" cellspacing="0">
		<thead>
			<th width="15%">Họ và tên</th>
			<th width="5%">Tổng</th>
			<th width="5%">T1</th>
			<th width="5%">T2</th>
			<th width="5%">T3</th>
			<th width="5%">T4</th>
			<th width="5%">T5</th>
			<th width="5%">T6</th>
			<th width="5%">T7</th>
			<th width="5%">T8</th>
			<th width="5%">T9</th>
			<th width="5%">T10</th>
			<th width="5%">T11</th>
			<th width="5%">T12</th>
			<th width="9%">Phép còn lại</th>
			<th width="11%">Ghi chú</th>
		</thead>
		<tbody>
			{$USEDLEAVEDAY_TBL}
		</tbody>
	</table>
</div>