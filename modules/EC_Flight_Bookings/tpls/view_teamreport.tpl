<h1 class="title">DOANH SỐ NHÓM</h1>
<div class="box-section">

<form action="index.php" method="post" name="frmSearch" id="frmSearch">
	<input type="hidden" name="module" value="EC_Flight_Bookings" />
	<input type="hidden" name="action" value="teamreport" />
	Tháng: <select class="box-select" id="month_list" name="month_list">{$MONTH_LIST}</select>
	 - Năm: <select class="box-select" id="year_list" name="year_list">{$YEAR_LIST}</select>
	<input type="submit" id="btnView" name="btnView" class="btn btn-primary" value="Xem" title="Xem" />
</form>

	
<table class="table-details__booking data-list mt-3" cellpadding="0" cellspacing="0" border="0">
	<thead>
		<tr>
			<th width="18%">Team</th>
			<th width="11%">Booking</th>
			<th width="11%">Hoàn tất</th>
			<th width="11%">Hiệu suất</th>
			<th width="11%">Số vé</th>
			<th width="11%">Doanh số</th>
			<th width="11%">Thưởng</th>
			<th width="16%">Đánh giá</th>
		</tr>
	</thead>
	{$DATA}
	<tr class="footer-tr">
		<td>&nbsp;</td>
		<td style="text-align:right; font-weight:bold">{$TTL_BOOKING}</td>
		<td style="text-align:right; font-weight:bold">{$TTL_COMPLETE}</td>
		<td style="text-align:right; font-weight:bold">{$TTL_EFFICIENCY}</td>
		<td style="text-align:right; font-weight:bold">{$TTL_TICKET}</td>
		<td style="text-align:right; font-weight:bold">{$TTL_PROFIT}</td>
		<td style="text-align:right; font-weight:bold">{$TTL_REWARD}</td>
		<td style="text-align:right; font-weight:bold">&nbsp;</td>
	</tr>
</table>
<table class="table-details__booking sale-target-list mt-5" cellpadding="0" cellspacing="0" border="0">
	<thead>
		<tr>
			<th colspan="2">Cơ chế khoán doanh số</th>
		</tr>
		<tr>
			<th style="width:60%">&nbsp;</th>
			<th style="width:40%">% DS được hưởng</th>
		</tr>
	</thead>
	{$DATA2}
</table>

</div>
