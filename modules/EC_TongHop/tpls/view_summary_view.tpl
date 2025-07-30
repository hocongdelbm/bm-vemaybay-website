<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<link type="text/css" rel="stylesheet" href="modules/EC_TongHop/css/ec_tonghop.css?v=1.0.5">
<div class="container-waiting">
    <div id="waiting-loading">
        <div class="spinner"></div>
    </div>
</div>
<div class = "title-wrap d-flex align-items-center justify-content-between gap-2">
    <h1 class="title">Chỉ số website</h1>
	<svg xmlns="http://www.w3.org/2000/svg" id="filter_report" width="32" height="32" fill="currentColor" class="bi bi-filter d-xxl-none d-xl-none d-lg-none d-block" viewBox="0 0 16 16">
		 <path d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5"/>
	 </svg>
</div>
<div class = "box-section overflow-auto position-relative mt-0">
    <div class = "search_wrap">
		<div class="overlay-mobile"></div>
        <form class="normal_search_form" name="search_form" id="ec_search_form" method="POST" action="index.php">
			<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-dash-lg search_form--dash d-xl-none d-lg-none d-block" viewBox="0 0 16 16">
				<path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8"></path>
	 		</svg>
			<div class = "select_option">
				<label>Website</label>
				<select class="box-select" name="url_selected" default = "">
					{* <option value = ""></option> *}
					{foreach from=$NEW_DOMAIN_LIST item=new_domain key=domain_name}
						<option value ={$new_domain}>{$domain_name}</option>
					{/foreach}
				</select>
			</div>
			<div class = "normal_search">
                <div class="from-to-date--wrap d-inline-flex gap-2 align-items-center">
				<div class="d-flex gap-2 align-items-center date_trigger--wrap fdate_trigger--wrap">
					<span class="sublabel">Từ ngày: </span>    
					<div class="dateTime d-flex gap-2 position-relative">
						<input class="date_input box-input" type="text" maxlength="10" size="11" tabindex="103" title="" value="{$FROM_DATE_VALUE}" id="from_date" name="from_date" autocomplete="off">
					    <button class="icon_dateTime" type="button" id="fdate_trigger" onclick="return false;">
						   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
							  <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
							  <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
							</svg>
					    </button>
					    {literal}
						<script type="text/javascript">
							document.addEventListener("DOMContentLoaded", function () {
							const now = new Date();

							// Tạo thời gian bắt đầu là 00:00:00 hôm nay
							const startOfDay = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0);

							// Tạo thời gian kết thúc là 23:59:59 hôm nay
							const endOfDay = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59);

							let fromPicker = flatpickr("#from_date", {
								enableTime: true,
								time_24hr: true,
								dateFormat: "d-m-Y H:i:S",
								defaultDate: startOfDay,
								maxDate: now,
								onChange: function (selectedDates) {
									toPicker.set("minDate", selectedDates[0]);
								}
							});

							let toPicker = flatpickr("#to_date", {
								enableTime: true,
								time_24hr: true,
								dateFormat: "d-m-Y H:i:S",
								defaultDate: endOfDay,
								maxDate: now,
							});
						});
						</script>
						{/literal}
					</div>
				 </div>
	
				 <svg width="40" height="20" fill="none">
					<g clip-path="url(#icon_arrow_flight_long_svg__clip0)" stroke="#718096" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
					    <path d="M33.5 8.5L36 11M4 11h32"></path>
					</g>
					<defs>
					    <clipPath id="icon_arrow_flight_long_svg__clip0">
						   <path fill="#fff" d="M0 0h40v20H0z"></path>
					    </clipPath>
					</defs>
				 </svg>
	
				 <div class="d-flex gap-2 align-items-center date_trigger--wrap tdate_trigger--wrap">
					<span class="sublabel">Đến ngày: </span>    
					<div class="dateTime d-flex gap-2 position-relative">
						<input class="date_input box-input" type="text" maxlength="10" size="11" tabindex="103" title="" value="{$TO_DATE_VALUE}" id="to_date" name="to_date" autocomplete="off">
					    <button class="icon_dateTime" type="button" id="tdate_trigger" onclick="return false;">
						   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
							  <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
							  <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
							</svg>
					    </button>
					    {literal}
						<script type="text/javascript">
						document.addEventListener("DOMContentLoaded", function () {
								const now = new Date();

								// Tạo thời gian bắt đầu là 00:00:00 hôm nay
								const startOfDay = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0);

								// Tạo thời gian kết thúc là 23:59:59 hôm nay
								const endOfDay = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59);

								let fromPicker = flatpickr("#from_date", {
									enableTime: true,
									time_24hr: true,
									dateFormat: "d-m-Y H:i:S",
									defaultDate: startOfDay,
									maxDate: now,
									onChange: function (selectedDates) {
										toPicker.set("minDate", selectedDates[0]);
									}
								});

								let toPicker = flatpickr("#to_date", {
									enableTime: true,
									time_24hr: true,
									dateFormat: "d-m-Y H:i:S",
									defaultDate: endOfDay,
									maxDate: now,
								});
							});
						</script>
						{/literal}
					</div>
				 </div>
				</div>
				<div class="d-flex align-items-center">
					<div class = "select_option">
						<label>Thời gian:</label>
						<select class="box-select" name="time_selected" default = "">
							<option value = ""></option>
							<option value = "yesterday">Hôm qua</option>
							<option value = "daybefore">Hôm trước</option>
							<option value = "this_week">Tuần này</option>
							<option value = "last_week">Tuần trước</option>
							<option value = "this_month">Tháng này</option>
							<option value = "last_month">Tháng trước</option>
						</select>
					</div>
				</div>
            </div>
			<div class="button-action--wrap">
				<button class="search_button btn btn-primary" type="button">Xem</button>
				<button class="reset_button btn btn-warning" type="button">Reset</button>
				{* <input type="button" id="btnSearch_cancel" value="Hủy bỏ" name="search_cancel" class="btn btn-secondary button-action--cancel d-xl-none d-lg-none d-block" title="Hủy bỏ"/> *}
			</div>
		</form>
    </div>
</div>
<div class = "box-section insight_traffic">
</div>
<div class="box-section traffic">
	<div class="online_data row">
	</div>
	<div class="extend_accordion mb-3">
        <button class="extend_btn hide" id="extend_accordion">&#x25BC;</button>
    </div>
	<div class="traffic filter_tag">
	</div>
</div>
<div class="box-section">
	<h3 class="access_log title pb-3">Truy cập gần nhất</h3>
	<div class="entrance_content">					
		<div class="total_entrance">
			{* <h1 class="entrance_heading title">Số lượng truy cập</h1> *}
			<div class="total_entrance_detail row">
				<table id="visitor_table" class="visitor-table">
				<thead>
					<tr>
					<th class="col-lg-3">Time</th>
					<th class="col-lg-3">IP</th>
					<th class="col-lg-3">Name</th>
					<th class="col-lg-3">Path</th>
					</tr>
				</thead>
				<tbody id="visitor_tbody">
					<!-- Dữ liệu sẽ được thêm ở đây -->
				</tbody>
				</table>
			</div>
			<div class="pagination-controls px-2">
				<div class="page-info">
					<span id="itemRange"></span>
					<label>
					Items per page:
					<select id="itemsPerPage">
						<option value="25">25</option>
						<option value="50">50</option>
						<option value="100">100</option>
						{* <option value="max">Tối đa</option> *}
					</select>
					</label>
				</div>
				<div class="page-nav">
					<button id="firstPage">«</button>
					<button id="prevPage">‹</button>
					<span>Page <span id="currentPage">1</span> of <span id="totalPages">1</span></span>
					<button id="nextPage">›</button>
					<button id="lastPage">»</button>
				</div>
				</div>
		</div>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript" src="modules/EC_TongHop/js/ec_tonghop.js?v=1.0.3"></script>