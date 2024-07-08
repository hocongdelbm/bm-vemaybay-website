{literal}
	<script>
		$(document).ready(function() {
			$(".save_note").click(function() {
				$.ajax({
					url: 'index.php?entryPoint=entryPointAbsence',
					type: "POST",
					data: {
						note: $(".advance_note").eq($(this).attr("ln") - 1).val(),
						user: $(this).attr("user"),
						for: "saveAdvanceNote",
					},
					success: function(res) {
						location.reload();
					}
				});
			});
		});
	</script>
{/literal}
{if !$isDetail}
<h1 class="title">BÁO CÁO HOÀN TẠM ỨNG</h1>
<div class="box-section">
	<form id="advance_frm" method="post" action="index.php">
		<input type="hidden" name="module" value="EC_Employee_Salary">

		<input type="hidden" name="action" value="advance">
		<div class="d-flex align-items-center gap-2 mb-3">
			<h2 class="change-title mb-0">Báo cáo hoàn tạm ứng đến năm <select class="box-select d-inline" name="year" id="year_select">{$YEAR}</select></h2>
			<input type="submit" name="search_btn" class="btn btn-primary" value="Tìm kiếm">
		</div>
		<table id="advance_tbl" class="table-advance table-details__booking" cellpadding="0" cellspacing="0">
			<thead>
				<th class="hide-mobile" width="5%">STT</th>
				<th width="20%">Họ tên</th>
				<th width="15%">Còn tạm ứng</th>
				<th>Ghi chú</th>
			</thead>
			<tbody>
				{$ADVANCE}
			</tbody>
		</table>
	</form>
</div>
{/if}

{if $isDetail}
	<h1 class="title">Chi tiết hoàn tạm ứng</h1>
	<div id="advance_detail" class="box-section">
		<form id="advance_frm" method="post" action="index.php">
			<input type="hidden" name="module" value="EC_Employee_Salary">
			<input type="hidden" name="action" value="advance">
			<input type="hidden" name="for" value="showdetail">
			<input type="hidden" name="user" value="{$USER}">
			<div class="d-flex align-items-center gap-2">
				<h2 class="change-title mb-0">Chi tiết tạm hoàn ứng từ năm <select class="box-select d-inline" name="from_year" id="from_year_select">{$FROM_YEAR}</select> đến năm <select class="box-select d-inline" name="to_year" id="to_year_select">{$TO_YEAR}</select></h2>
				<input type="submit" name="search_btn" class="btn btn-primary" value="Tìm kiếm">
			</div>
			<p class="mb-2"><b><i>Nhân viên: {$EMPLOYEE_NAME}</i></b></p>
		</form>
		<table id="advance_tbl" class="table-details__booking advance_detail_tbl" cellpadding="0" cellspacing="0">
			<thead>
				<th width="20%">Ngày</th>
				<th width="20%">Số phiếu</th>
				<th width="20%">Tạm ứng</th>
				<th width="20%">Hoàn ứng</th>
				<th width="20%">Còn lại</th>
			</thead>
			<tbody>
				{$ADVANCE_DETAIL}
			</tbody>
		</table>
	</div>
{/if}