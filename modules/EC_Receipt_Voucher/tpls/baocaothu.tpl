<link rel="stylesheet" type="text/css" href="modules/{$MODULE_NAME}/css/view.baocaothu.css?v={$STYLE_VERSION}" />

<h1 class="title d-flex align-items-center gap-2">
	<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
		<path d="M4 11H2v3h2v-3zm5-4H7v7h2V7zm5-5v12h-2V2h2zm-2-1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1h-2zM6 7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7zm-5 4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3z"/>
	</svg>
	Báo cáo thu
</h1>

<form name="frmSearchBaoCaoThu" id="frmSearchBaoCaoThu" action="index.php" method="post" class="box-section">
	<input type="hidden" name="module" value="{$MODULE_NAME}" />
	<input type="hidden" name="action" value="baocaothu" />
	<input type="hidden" name="location_name" id="location_name" value="{$LOCATION_NAME}" />

	<div id="bct_hidden_selects" style="display:none">
		<select name="loai_thu[]"     id="sel_loai_thu"     multiple>{$LOAI_THU_OPTS}</select>
		<select name="receipt_type[]" id="sel_receipt_type" multiple>{$RECEIPT_TYPE_OPTS}</select>
		<select name="rv_status[]"    id="sel_rv_status"    multiple>{$RV_STATUS_OPTS}</select>
		<select name="group_by"       id="sel_group_by">{$GROUP_BY_OPTS}</select>
		<select name="location_id[]"  id="sel_location_id"  multiple>{$LOCATION_ID}</select>
	</div>

	<div class="bct-chips-row" id="bct_chips_row">

		<!-- Chip: Kỳ / Ngày -->
		<div class="bct-chip-wrap" id="chip_date">
			<button type="button" class="bct-chip" id="chip_date_btn">
				<span class="lbl" id="chip_date_lbl">Kỳ / Ngày</span>
				<span class="bct-caret">&#9660;</span>
			</button>
			<div class="bct-dropdown" id="dd_date">
				<div class="bct-dd-title">Chọn kỳ</div>
				<div class="bct-term-row">
					<select id="bct_report_term" name="report_term">{$REPORT_TERMS}</select>
				</div>
				<div class="bct-date-pair">
					<div class="bct-dp">
						<label>Từ ngày</label>
						<div class="dateTime d-flex gap-2 position-relative">
							<input class="date_input box-input" type="text" maxlength="10" size="11" value="{$POST_FDATE}" id="from_date" name="from_date" autocomplete="off">
							<button class="icon_dateTime" type="button" id="from_date_trigger" onclick="return false;">
								<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/><path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/></svg>
							</button>
							{literal}<script type="text/javascript">Calendar.setup({inputField:"from_date",daFormat:"%d-%m-%Y",button:"from_date_trigger",singleClick:true,dateStr:"",step:1,weekNumbers:false});</script>{/literal}
						</div>
					</div>
					<div class="bct-dp">
						<label>Đến ngày</label>
						<div class="dateTime d-flex gap-2 position-relative">
							<input class="date_input box-input" type="text" maxlength="10" size="11" value="{$POST_TDATE}" id="to_date" name="to_date" autocomplete="off">
							<button class="icon_dateTime" type="button" id="to_date_trigger" onclick="return false;">
								<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/><path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/></svg>
							</button>
							{literal}<script type="text/javascript">Calendar.setup({inputField:"to_date",daFormat:"%d-%m-%Y",button:"to_date_trigger",singleClick:true,dateStr:"",step:1,weekNumbers:false});</script>{/literal}
						</div>
					</div>
				</div>
				<div class="bct-dd-footer">
					<button type="button" class="w-50 btn btn-sm btn-light bct-btn-cancel">Đóng</button>
					<button type="button" class="flex-fill btn btn-sm btn-primary bct-btn-xem">Xem kết quả</button>
				</div>
			</div>
		</div>

		<!-- Chip: Địa điểm -->
		<div class="bct-chip-wrap" id="chip_location">
			<button type="button" class="bct-chip" data-selid="sel_location_id" data-deflabel="Địa điểm" data-filterlabel="Địa điểm">
				<span class="lbl">Địa điểm</span><span class="bct-caret">&#9660;</span>
			</button>
			<div class="bct-dropdown">
				<div class="bct-dd-title">Địa điểm</div>
				<div class="bct-dd-opts"></div>
				<div class="bct-dd-footer">
					<button type="button" class="w-50 btn btn-sm btn-light bct-btn-cancel">Đóng</button>
					<button type="button" class="flex-fill btn btn-sm btn-primary bct-btn-xem">Xem kết quả</button>
				</div>
			</div>
		</div>

		<!-- Chip: Loại thu -->
		<div class="bct-chip-wrap" id="chip_loai_thu">
			<button type="button" class="bct-chip" data-selid="sel_loai_thu" data-deflabel="Loại thu" data-filterlabel="Loại thu">
				<span class="lbl">Loại thu</span><span class="bct-caret">&#9660;</span>
			</button>
			<div class="bct-dropdown">
				<div class="bct-dd-title">Loại thu</div>
				<div class="bct-dd-opts"></div>
				<div class="bct-dd-footer">
					<button type="button" class="w-50 btn btn-sm btn-light bct-btn-cancel">Đóng</button>
					<button type="button" class="flex-fill btn btn-sm btn-primary bct-btn-xem">Xem kết quả</button>
				</div>
			</div>
		</div>

		<!-- Chip: Hình thức -->
		<div class="bct-chip-wrap" id="chip_receipt_type">
			<button type="button" class="bct-chip" data-selid="sel_receipt_type" data-deflabel="Hình thức" data-filterlabel="Hình thức">
				<span class="lbl">Hình thức</span><span class="bct-caret">&#9660;</span>
			</button>
			<div class="bct-dropdown">
				<div class="bct-dd-title">Hình thức thu</div>
				<div class="bct-dd-opts"></div>
				<div class="bct-dd-footer">
					<button type="button" class="w-50 btn btn-sm btn-light bct-btn-cancel">Đóng</button>
					<button type="button" class="flex-fill btn btn-sm btn-primary bct-btn-xem">Xem kết quả</button>
				</div>
			</div>
		</div>

		<!-- Chip: Trạng thái -->
		<div class="bct-chip-wrap" id="chip_rv_status">
			<button type="button" class="bct-chip" data-selid="sel_rv_status" data-deflabel="Trạng thái" data-filterlabel="Trạng thái">
				<span class="lbl">Trạng thái</span><span class="bct-caret">&#9660;</span>
			</button>
			<div class="bct-dropdown">
				<div class="bct-dd-title">Trạng thái</div>
				<div class="bct-dd-opts"></div>
				<div class="bct-dd-footer">
					<button type="button" class="w-50 btn btn-sm btn-light bct-btn-cancel">Đóng</button>
					<button type="button" class="flex-fill btn btn-sm btn-primary bct-btn-xem">Xem kết quả</button>
				</div>
			</div>
		</div>

		<!-- Chip: Nhóm theo -->
		<div class="bct-chip-wrap" id="chip_group_by">
			<button type="button" class="bct-chip" data-selid="sel_group_by" data-deflabel="Nhóm theo" data-filterlabel="Nhóm theo">
				<span class="lbl">Nhóm theo</span><span class="bct-caret">&#9660;</span>
			</button>
			<div class="bct-dropdown">
				<div class="bct-dd-title">Nhóm theo</div>
				<div class="bct-dd-opts"></div>
				<div class="bct-dd-footer">
					<button type="button" class="w-50 btn btn-sm btn-light bct-btn-cancel">Đóng</button>
					<button type="button" class="flex-fill btn btn-sm btn-primary bct-btn-xem">Xem kết quả</button>
				</div>
			</div>
		</div>

	</div><!-- end bct-chips-row -->

	<!-- Active filter tags -->
	<div class="bct-active-bar" id="bct_active_bar"></div>

	<!-- Action buttons -->
	<div class="bct-action-row">
		<button type="button" class="bct-btn-view" id="bct_btn_view">Xem báo cáo</button>
		{if $SHOW_REPORT}
		<button type="button" class="bct-btn-excel" id="bct_btn_excel">Xuất Excel</button>
		{/if}
	</div>

</form>

{if $SHOW_REPORT}
	{$REPORT_HTML}
{/if}

<script type="text/javascript" src="modules/{$MODULE_NAME}/js/view.baocaothu.js?v={$STYLE_VERSION}"></script>