<link type="text/css" rel="stylesheet" href="modules/EC_TongHop/css/ec_tonghop.css?v=1.0.3">
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
					<option value = ""></option>
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
							Calendar.setup ({
							inputField : "from_date",
							daFormat : "%d-%m-%Y",
							button : "fdate_trigger",
							singleClick : true,
							dateStr : "",
							step : 1,
							position: [230, 202],
							}
							);
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
							Calendar.setup ({
							inputField : "to_date",
							daFormat : "%d-%m-%Y",
							button : "tdate_trigger",
							singleClick : true,
							dateStr : "",
							step : 2
							}
							);
						</script>
						{/literal}
					</div>
				 </div>
			</div>
			<div class="d-flex align-items-center">
				<input type="radio" value="yesterday" id="yesterday" class="rd_time form-check-input" name="optionRadio" fromdate="{$YESTERDAY_FROMDATE}" todate="{$YESTERDAY_TODATE}">
				<label class="cursor-pointer" for="yesterday">Hôm qua</label> 

				<input type="radio" value="daybefore" id="daybefore" class="rd_time form-check-input" name="optionRadio" fromdate="{$DAYBEFORE_FROMDATE}" todate="{$DAYBEFORE_TODATE}">
				<label class="cursor-pointer" for="daybefore">Hôm trước</label> 

				<input type="radio" value="current_week" id="current_week" class="rd_time form-check-input" name="optionRadio" fromdate="{$CURRENT_WEEK_FROMDATE}" todate="{$CURRENT_WEEK_TODATE}">
				<label class="cursor-pointer" for="current_week">Tuần này</label>

				<input type="radio" value="previous_week" id="previous_week" class="rd_time form-check-input" name="optionRadio" fromdate="{$PREVIOUS_WEEK_FROMDATE}" todate="{$PREVIOUS_WEEK_TODATE}"> 
				<label class="cursor-pointer" for="previous_week">Tuần trước</label> 
			</div>
            </div>
			<div class="button-action--wrap">
			{* <input type="button" id="btnView" name="btnView" class="btn btn-primary button-action" value="Xem" title="Xem" /> *}
				<button class="search_button btn btn-primary" type="button">Xem</button>
				<button class="reset_button btn btn-warning" type="button">Reset</button>
				<input type="button" id="btnSearch_cancel" value="Hủy bỏ" name="search_cancel" class="btn btn-secondary button-action--cancel d-xl-none d-lg-none d-block" title="Hủy bỏ"/>
			</div>
		</form>
    </div>
</div>
<div class="box-section">
	<div class="online_data row">
		<div class = "mini_title_wrap col-lg-3 col-sm-12">
			<h1 class="domain title">vietjet.net</h1>
			<span class="online_user">Khách online:
				<span class="online_user_value">
					<div class="spinner online_user_waiting"></div>
				</span>
			</span>
			
		</div>
		<div class = "mini_title_wrap col-lg-3 col-sm-12">
			<h1 class="domain title">timchuyenbay.com</h1>
			<span class="online_user">Khách online:
				<span class="online_user_value">
					<div class="spinner online_user_waiting"></div>
				</span>
			</span>
			
		</div>
		<div class = "mini_title_wrap col-lg-3 col-sm-12">
			<h1 class="domain title">timchuyenbay.vn</h1>
			<span class="online_user">Khách online:
				<span class="online_user_value">
					<div class="spinner online_user_waiting"></div>
				</span>
			</span>
			
		</div>
		<div class = "mini_title_wrap col-lg-3 col-sm-12">
			<h1 class="domain title">vemaybay.com</h1>
			<span class="online_user">Khách online:
				<span class="online_user_value">
					<div class="spinner online_user_waiting"></div>
				</span>
			</span>
			
		</div>
		<div class = "mini_title_wrap col-lg-3 col-sm-12">
			<h1 class="domain title">vemaybaygiare.com</h1>
			<span class="online_user">Khách online:
				<span class="online_user_value">
					<div class="spinner online_user_waiting"></div>
				</span>
			</span>
			
		</div>
		<div class = "mini_title_wrap col-lg-3 col-sm-12">
			<h1 class="domain title">vemaybaygiare.com</h1>
			<span class="online_user">Khách online:
				<span class="online_user_value">
					<div class="spinner online_user_waiting"></div>
				</span>
			</span>
			
		</div>
		<div class = "mini_title_wrap col-lg-3 col-sm-12">
			<h1 class="domain title">vemaybaygiare.com</h1>
			<span class="online_user">Khách online:
				<span class="online_user_value">
					<div class="spinner online_user_waiting"></div>
				</span>
			</span>
			
		</div>
		<div class = "mini_title_wrap col-lg-3 col-sm-12">
			<h1 class="domain title">vemaybaygiare.com</h1>
			<span class="online_user">Khách online:
				<span class="online_user_value">
					<div class="spinner online_user_waiting"></div>
				</span>
			</span>
			
		</div>
		<div class = "mini_title_wrap col-lg-3 col-sm-12">
			<h1 class="domain title">vemaybaygiare.com</h1>
			<span class="online_user">Khách online:
				<span class="online_user_value">
					<div class="spinner online_user_waiting"></div>
				</span>
			</span>
			
		</div>
		<div class = "mini_title_wrap col-lg-3 col-sm-12">
			<h1 class="domain title">vemaybaygiare.com</h1>
			<span class="online_user">Khách online:
				<span class="online_user_value">
					<div class="spinner online_user_waiting"></div>
				</span>
			</span>	
		</div>
		<div class = "mini_title_wrap col-lg-3 col-sm-12">
			<h1 class="domain title">vemaybaygiare.com</h1>
			<span class="online_user">Khách online:
				<span class="online_user_value">
					<div class="spinner online_user_waiting"></div>
				</span>
			</span>	
		</div>
		<div class = "mini_title_wrap col-lg-3 col-sm-12">
			<h1 class="domain title">vemaybaygiare.com</h1>
			<span class="online_user">Khách online:
				<span class="online_user_value">
					<div class="spinner online_user_waiting"></div>
				</span>
			</span>	
		</div>
	</div>
</div>
<div class="box-section">
	<h1 class="title table">Tổng Quát</h1>
	<table class="table summary table-striped table-borderless table-sm two-column-table mb-0">
		<thead>
			<tr>
				<th>Người dùng mới</th>
				<th>Người dùng cũ</th>
				<th>Bot</th>
				<th>Page view</th>
				<th>Truy cập</th>
				<th>tương tác</th>
				<th>Loại thiết bị</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>30</td>
				<td>87</td>
				<td>12</td>
				<td>http://vietjet.net</td>
				<td>http://vietjet.net</td>
				<td>80%</td>
				<td>Mobile</td>
			</tr>
			<tr>
				<td>30</td>
				<td>87</td>
				<td>12</td>
				<td>http://vietjet.net</td>
				<td>http://vietjet.net</td>
				<td>80%</td>
				<td>Mobile</td>
			</tr>
			<tr>
				<td>30</td>
				<td>87</td>
				<td>12</td>
				<td>http://vietjet.net</td>
				<td>http://vietjet.net</td>
				<td>80%</td>
				<td>Mobile</td>
			</tr>
			<tr>
				<td>30</td>
				<td>87</td>
				<td>12</td>
				<td>http://vietjet.net</td>
				<td>http://vietjet.net</td>
				<td>80%</td>
				<td>Mobile</td>
			</tr>
			<tr>
				<td>30</td>
				<td>87</td>
				<td>12</td>
				<td>http://vietjet.net</td>
				<td>http://vietjet.net</td>
				<td>80%</td>
				<td>Mobile</td>
			</tr>
			<tr>
				<td>30</td>
				<td>87</td>
				<td>12</td>
				<td>http://vietjet.net</td>
				<td>http://vietjet.net</td>
				<td>80%</td>
				<td>Mobile</td>
			</tr>
			<tr>
				<td>30</td>
				<td>87</td>
				<td>12</td>
				<td>http://vietjet.net</td>
				<td>http://vietjet.net</td>
				<td>80%</td>
				<td>Mobile</td>
			</tr>
			<tr>
				<td>30</td>
				<td>87</td>
				<td>12</td>
				<td>http://vietjet.net</td>
				<td>http://vietjet.net</td>
				<td>80%</td>
				<td>Mobile</td>
			</tr>
		</tbody>
	</table>
</div>
<div class="box-section">
	<h1 class="title detail">Số liệu thống kê</h1>
	<div class="entrance_content">					
		<div class="total_entrance">
			<h1 class="entrance_heading title">Số lượng truy cập</h1>
			<div class="total_entrance_detail row">
				<div class="access_wrap col-lg-6 col-sm-12">			
					<div class="user_access">
						<svg width="50" height="50" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M8 7C9.65685 7 11 5.65685 11 4C11 2.34315 9.65685 1 8 1C6.34315 1 5 2.34315 5 4C5 5.65685 6.34315 7 8 7Z" fill="#000000"/>
							<path d="M14 12C14 10.3431 12.6569 9 11 9H5C3.34315 9 2 10.3431 2 12V15H14V12Z" fill="#000000"/>
						</svg>
					</div>
					<span class="access_title user">LƯỢNG NGƯỜI THỰC TRUY CẬP: <span class="access_value user">50000</span></span>
					<span class="access_title user">TRUY CẬP NHIỀU NHẤT: <span class="access_value user">https://timchuyenbay.com</span></span>
					<span class="access_title user">TÌNH TRẠNG TRUY CẬP: <span class="access_value user">TỐT</span></span>
					<span class="access_title user">THIẾT BỊ TRUY CẬP NHIỀU NHẤT: <span class="access_value user">DI ĐỘNG</span></span>

				</div>
				<div class="access_wrap col-lg-6 col-sm-12">
					<div class="bot_access">
						<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50"viewBox="0 0 24 24"fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<rect x="3" y="11" width="18" height="10" rx="2" />
							<circle cx="12" cy="5" r="2" />
							<path d="M12 7v4" />
							<line x1="8" y1="16" x2="8" y2="16" />
							<line x1="16" y1="16" x2="16" y2="16" />
						</svg>			
					</div>
					<span class="access_title bot">LƯỢNG BOT TRUY CẬP: <span class="access_value bot">1857491222</span></span>
					<span class="access_title bot">TRUY CẬP NHIỀU NHẤT: <span class="access_value bot">https://timchuyenbay.com</span></span>
					<span class="access_title bot">TÌNH TRẠNG TRUY CẬP: <span class="access_value bot">TRUNG BÌNH</span></span>
					<span class="access_title bot">THIẾT BỊ TRUY CẬP NHIỀU NHẤT: <span class="access_value bot">DESKTOP</span></span>
				</div>
			</div>
		</div>
	</div>
	<div class="chart_content">
		<div class="total_chart">
			<h1 class="chart_heading title">Biểu đồ thống kê</h1>
			<div class="total_chart_detail">
				<div class="access_chart wrap">
					<div class="access_chart_by_year">
						<div class="chart_title_wrap">
							<h1 class="title" id="chartTitle">Biểu đồ lượt truy cập trong năm 2024</h1>
						</div>	
						<canvas id="access_year"></canvas>
					</div>
					<div class="access_chart_by_month">
					<h1 class="title" id="chartTitle">Biểu đồ lượt truy cập trong tháng 1-2025</h1>
						<canvas id="access_month"></canvas>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="chart_content">
		<div class="total_chart">
			<h1 class="chart_heading title">Chi tiết truy cập</h1>
			<div class="total_chart_detail">
				<div class="access_chart wrap">
					<div class="access_chart_by_year">
						<div class="chart_title_wrap">
							<h1 class="title" id="chartTitle">Platform</h1>
						</div>	
						<canvas id="access_platform"></canvas>
					</div>
					<div class="access_chart_by_month">
						<h1 class="title" id="chartTitle">Operating System</h1>
					</div>
					<canvas id="access_os"></canvas>
				</div>
			</div>
		</div>
	</div>
	<div class="chart_content">
		<div class="total_chart">
			<h1 class="chart_heading title">Chi tiết bot</h1>
			<div class="total_chart_detail">
				<div class="access_chart wrap">
					<div class="access_chart_by_year">
						<div class="chart_title_wrap">
							<h1 class="title" id="chartTitle">loại bot</h1>
						</div>	
						<canvas id="access_bot_type"></canvas>
					</div>
					<div class="access_chart_by_month">
					<h1 class="title" id="chartTitle">số lượng request</h1>
						<canvas id="access_bot_request"></canvas>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="chart_content">
		<div class="total_chart">
			<h1 class="chart_heading title">Chi tiết thiết bị</h1>
			<div class="total_chart_detail">
				<div class="access_chart wrap">
					<div class="access_chart_by_year">
						<div class="chart_title_wrap">
							<h1 class="title" id="chartTitle">Phân giải</h1>
						</div>	
						<canvas id="access_resolution"></canvas>
					</div>
					<div class="access_chart_by_month">
					<h1 class="title" id="chartTitle">Loại thiết bị</h1>
						<canvas id="access_device"></canvas>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript" src="modules/EC_TongHop/js/ec_tonghop.js?v=1.0.8"></script>