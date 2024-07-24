{literal}
<style>
	#timesheet_tbl td {
		border: 1px solid #ccc;
		padding: 5px;
		position: relative;
	}
	#timesheet_tbl td span {
		position: absolute;
		display: none;
	    	z-index: 2;
		top: 100%;
		left: 0;
		width: 150px;
		padding: 15px 5px;
		background-color: cornflowerblue;
	}
	#timesheet_tbl td a {
		text-decoration: none;
	}
	#timesheet_tbl tbody tr:hover, .active {
		background-color: #cfeafe !important;
	}
	#search_btn {
		font-weight: bold;
		padding: 3px 5px;
		margin-left: 3px;
		font-size: 10pt;
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
	.today {
		background-color: #F0F0F0;
	}
</style>
<script>
	$(document).ready(function() {
		var td = $('#timesheet_tbl td');
		$("#timesheet_tbl td").mouseover(function() {
			var index = $(this).parent('tr').find('td').index($(this));
			$('#timesheet_tbl tr:not(".date-name") td:nth-child(' + (index+1) + ')').addClass('active');
			$('#timesheet_tbl tr.date-name td:nth-child(' + (index) + ')').addClass('active');
		}).mouseleave(function() {
			td.removeClass('active');
		});

		$(".detail").click(function() {
			$(".detail span").hide();
			if($(this).children("span").is(":hidden")) {
				$(this).children("span").show();
			} else {
				$(this).children("span").hide();
			}
		});
	});
</script>
{/literal}
<div id="timesheets">
	<form id="search_frm" method="post" action="index.php">
		<input type="hidden" name="module" value="EC_LeaveAbsences">
		<input type="hidden" name="action" value="timesheets">
		<h2>Bảng chấm công tháng <select name="month_search">{$MONTH_OPTION}</select> năm <select name="year_search">{$YEAR_OPTION}</select></h2>
		<input type="submit" value="Tìm kiếm" id="search_btn">
	</form>
	<table cellpadding="0" cellspacing="0" width="100%" id="timesheet_tbl">
		<thead>
			<tr>
				<td rowspan="2" class="left">Họ tên nhân viên</td>
				{$DAY_COLS}
				<td class="center">Thường</td>
				<td class="center">Lễ/CN</td>
				<td class="center">Tổng số ngày công</td>
			</tr>
			<tr class="date-name">
				{$DATE_COLS}
				<td></td>
				<td></td>
				<td></td>
			</tr>
		</thead>
		<tbody>
			{$TIMESHEET}
		</tbody>
	</table>
</div>