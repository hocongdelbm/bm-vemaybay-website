{literal}
<style>
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
	#timesheet_tbl thead tr {
		position: sticky;
		top: 0px; 
		z-index: 2;
	}
	#timesheet_tbl thead tr.date-name {
		position: sticky;
		background-color: #fff;
		z-index: 2;
	}
	#timesheet_tbl tbody tr:hover td, 
	td.active {
		background-color: #cfeafe !important;
	}
	#timesheet_tbl .disabled {
		background-color: #F0F0F0;
	}

	#timesheet_tbl a:not(.bonus_day){
		text-decoration: none !important;
		font-size: 11px;
	}

	.today {
		background-color: #F0F0F0;
	}

	#show_detail {
		display: none;
	}
</style>
<script>
	const url = "index.php?entryPoint=entryPointAbsence";
	$(document).ready(function() {
		var td = $('#timesheet_tbl td');
		$("#timesheet_tbl td").mouseover(function() {
			var index = $(this).parent('tr').find('td').index($(this));
			if($(this).parent().hasClass("date-name")) {
				index = index + 2;
			}
			$('#timesheet_tbl tr:not(".date-name") td:nth-child(' + (index+1) + ')').addClass('active');
			$('#timesheet_tbl tr.date-name td:nth-child(' + (index-1) + ')').addClass('active');
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
		$(".bonus_day").click(function() {
			var ln = $(this).attr("ln");
			$("#show_detail").dialog({
				width: 600,
				title: "Chi tiết làm ngoài giờ hoặc bổ sung công",
				resizable: false,
				modal: true,
			});
			$("#dt_title").html("CHI TIẾT CÔNG NGOÀI GIỜ HOẶC BỔ SUNG<br>THÁNG " + $("#my").val() + "<br>Nhân viên: " + $("#p_name"+ln).text());
			$.ajax({
				type: "POST",
				url: url,
				data: {
					usr: $("#p_inf"+ln).val(),
					month: $("select[name=month_search]").val(),
					year: $("select[name=year_search]").val(),
					for: "getUserBonusDaysDetail",
				},
				beforeSend: function() {
					$("#show_detail>tbody").html("");
				},
				success: function(response) {
					$("#show_detail>tbody").html(response);
				}
			});
		});
	});
</script>
{/literal}

<h1 class="title">
	Bảng chấm công
</h1>
<div class="box-section overflow-auto">
<div id="timesheets">
	<form id="search_frm" method="post" action="index.php">
		<input type="hidden" name="module" value="EC_Employee_Salary">
		<input type="hidden" name="action" value="timesheets">
		<input type="hidden" id="my" value="{$MY}">
		<div class="d-flex gap-2 align-items-center mb-3">
			<h2 class="change-title mb-0 d-flex align-items-center gap-2">
				<select class="box-select" name="month_search">{$MONTH_OPTION}</select>
				<select  class="box-select" name="year_search">{$YEAR_OPTION}</select>
			</h2>
			<input class="btn btn-primary" type="submit" value="Tìm kiếm" id="search_btn">
		</div>
	</form>
	<table cellpadding="0" cellspacing="0" width="100%" id="timesheet_tbl" class="table-details__timesheet table-details__booking">
		<thead>
			<tr>
				<th rowspan="2" class="text-center">STT</th>
				<th rowspan="2" class="text-start">Họ tên nhân viên</th>
				{$DAY_COLS}
				<th class="text-center">Thường</th>
				<th class="text-center">Lễ/CN</th>
				<th class="text-center">Tổng công</th>
			</tr>
			<tr class="date-name">
				{$DATE_COLS}
				<th></th>
				<th></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{$TIMESHEET}
		</tbody>
	</table>
</div>

<table id="show_detail" cellpadding="0" cellspacing="0" class="table-details__booking w-100">
	<caption id="dt_title" class="text-center caption-top"></caption>
	<thead>
		<tr>
			<th class="text-center"><b>Ngày tính công</b></th>
			<th class="text-center"><b>Bổ sung cho tháng</b></th>
			<th class="text-center"><b>Tên phiếu</b></th>
			<th class="text-center"><b>Công bổ sung</b></th>
		</tr>
	</thead>
	<tbody></tbody>
</table>

</div>