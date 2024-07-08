{literal}
<style>
	#usedleaveday_tbl th, #usedleaveday_tbl td {
		border: 1px solid #ccc;
		padding: 5px;
	}

	#usedleaveday_tbl thead th {
		background-color: #FCF3CC;
	}

	#usedleaveday_tbl tbody tr:hover {
		background-color: #cfeafe;
	}

	#search_frm {
		margin-bottom: 5px;
	}

	#year_search {
		padding: 3px;
	}

	.right {
		text-align: right;
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
<div id="usedleaveday">
	<form method="post" action="index.php" id="search_frm">
		<input type="hidden" name="module" value="EC_LeaveAbsences">
		<input type="hidden" name="action" value="usedleaveday">
		<h2>Số ngày phép đã sử dụng năm 
			<select id="year_search" name="year_search">
				{$YEAR_OPTION}
			</select>
		</h2>
	</form>
	<table id="usedleaveday_tbl" cellpadding="0" cellspacing="0" width="100%">
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