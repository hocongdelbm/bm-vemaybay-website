{literal}
    <style>
        .data-list thead tr th {
            position: sticky;
            top: -1px; 
        }

        .data-list thead tr:nth-child(2) th{
            top: 34px; 
        }

        .data-list thead tr.total-line th {
            background-color: #fff2cc;
            color: #000;
        }

        .data-list tbody tr.main-line td {
            background-color: #dee2e6;
            color: var(--text-black);
        }

        .data-list tbody tr:hover {
            background-color: #cfeafe;
        }

        .location-booking--wrap,
        .airport__statistics--wrap{
            height: 65vh;
            overflow-y: scroll;
        }

        #chartjs__journey,
        #chartjs__journey-inter{
            width: 100% !important;
            max-height: 450px !important;
        }
    </style>

    <script type="text/javascript">
	$(document).ready(function() {	
        $(document).on("change", "input[type=radio][name=optionRadio]", function(e) {
			$('#from_date').val($('input[name=optionRadio]:checked').attr('fromdate'));
			$('#to_date').val($('input[name=optionRadio]:checked').attr('todate'));

			sessionStorage.setItem('optionRadio_statistics', $(this).val());
			sessionStorage.removeItem('date_select_statistics');
		});

        $(document).on("change", "#date_select", function(e) {
            $("#from_date").val($(this).find("option:selected").attr("fromdate"));
            $("#to_date").val($(this).find("option:selected").attr("todate"));

			sessionStorage.setItem('date_select_statistics', $(this).val());
			sessionStorage.removeItem('optionRadio_statistics');
        });

        // Check sessionStorage - js
		const selectOption 		= document.getElementById('date_select');
		const radioOptions 		= document.getElementsByName('optionRadio');
		const savedSelectOption 	= sessionStorage.getItem('date_select_statistics');
		if (savedSelectOption) {
			selectOption.value = savedSelectOption;
		} else {
			const savedRadioOption = sessionStorage.getItem('optionRadio_statistics');
			if (savedRadioOption) {
				radioOptions.forEach(radio => {
					if (radio.value === savedRadioOption) {
						radio.checked = true;
					}
				});
			}
		}
	});

    </script>
{/literal}

<div class="title-wrap d-flex align-items-center justify-content-between gap-2">
    <h1 class="title">Phân tích hành trình</h1>
    <svg xmlns="http://www.w3.org/2000/svg" id="filter_report" width="32" height="32" fill="currentColor" class="bi bi-filter d-xxl-none d-xl-none d-lg-none d-block hide-landscape" viewBox="0 0 16 16">
		<path d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5"/>
	</svg>
</div>

<div class="box-section position-relative">
	<div class="overlay-mobile"></div>
    <form action="index.php" method="post" name="search_form" id="ec_search_form">
        <input type="hidden" name="module" value="EC_Flight_Bookings"/>
        <input type="hidden" name="action" value="airportstatistics"/>

        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-dash-lg search_form--dash d-xl-none d-lg-none d-md-none d-block" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8"></path>
        </svg>

        <div class="action--wrap flex-wrap d-flex gap-4 align-items-center">
            <select class="box-select" id="date_select" name="date_select">{$DATE_OPTION}</select>

            <div class="from-to-date--wrap d-inline-flex gap-3 align-items-center">
                <div class="d-flex gap-2 align-items-center date_trigger--wrap fdate_trigger--wrap">
                    <span class="sublabel">Từ ngày: </span>    
                    <div class="dateTime d-flex gap-2 position-relative">
                        <input class="date_input box-input" type="text" maxlength="10" size="11" tabindex="103" title="" value="{$FROM_DATE}" id="from_date" name="from_date" autocomplete="off">
                        <button class="icon_dateTime" type="button" id="fdate_trigger" onclick="return false;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                                <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                            </svg>
                        </button>
                        {literal}
                        <script type="text/javascript">
                            Calendar.setup({
                                    inputField: "from_date",
                                    daFormat: "%d-%m-%Y",
                                    button: "fdate_trigger",
                                    singleClick: true,
                                    dateStr: "",
                                    step: 1
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
                    <input  class="date_input box-input" type="text" maxlength="10" size="11" title="" value="{$TO_DATE}" id="to_date" name="to_date" autocomplete="off">
                        <button class="icon_dateTime" type="button" id="tdate_trigger" onclick="return false;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                                <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                            </svg>
                        </button>
                        {literal}
                        <script type="text/javascript">
                            Calendar.setup({
                                    inputField: "to_date",
                                    daFormat: "%d-%m-%Y",
                                    button: "tdate_trigger",
                                    singleClick: true,
                                    dateStr: "",
                                    step: 2
                                }
                            );
                        </script>
                        {/literal}
                    </div>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-2 journey--wrap">
                <span class="sublabel">Hành trình: </span>    
                <input class="box-input" type="text" size="11" title="" value="{$DEPARTURE}" id="departure" name="departure" autocomplete="off" placeholder="Nơi đi">
                
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

                <input class="box-input" type="text" size="11" title="" value="{$ARRIVAL}" id="arrival" name="arrival" autocomplete="off" placeholder="Nơi đến">
            </div>

            <div class="d-flex align-items-center gap-2 optionRadio--wrap">
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
            <input type="submit" class="btn btn-primary button-action" name="btnSearch" id="btnSearch" value="Xem thống kê" title="Xem thống kê" />
            <input type="button" id="btnSearch_cancel" name="search" class="btn btn-secondary button-action--cancel d-xl-none d-lg-none d-block" value="Hủy bỏ" title="Hủy bỏ"/>
        </div>
    </form>

    <ul class="nav-tabs admin_tabs-list" id="airport_statistics" role="tablist">
        <!-- hành trình -->
        <li class="admin_tabs-item" role="presentation">
            <a class="active" id="chartjs__journey-tab" data-bs-toggle="tab" data-bs-target="#chartjs__journey-pane" type="button" role="tab" aria-controls="chartjs__journey-pane" aria-selected="false">Nội địa</a>
        </li>

        <li class="admin_tabs-item" role="presentation">
            <a id="chartjs__journey-inter-tab" data-bs-toggle="tab" data-bs-target="#chartjs__journey-inter-pane" type="button" role="tab" aria-controls="chartjs__journey-inter-pane" aria-selected="false">Quốc tế</a>
        </li>

        <!-- Nơi đặt vé -->
        <!-- <li class="admin_tabs-item" role="presentation">
            <a id="chartjs__location-tab" data-bs-toggle="tab" data-bs-target="#chartjs__location-pane" type="button" role="tab" aria-controls="chartjs__location-pane" aria-selected="false">Báo cáo nơi đặt vé</a>
        </li> -->
    </ul>
    <div class="tab-content" id="airport_statistics--content">

        <!-- HÀNH TRÌNH NỘI ĐỊA -->
        <div class="tab-pane fade show active" id="chartjs__journey-pane" role="tabpanel" aria-labelledby="chartjs__journey-tab" tabindex="0">
            <div class="box-tabs mb-0">
                <div class="chartjs__journey--wrap w-100">
                   <canvas id="chartjs__journey" class="mx-auto"></canvas>
                </div>

                <div class="airport__statistics--wrap mt-3 pb-2">
                    <table class="data-list table-airport__statistics table-details__booking" cellspacing="0" cellpadding="0">
                        <thead>
                            <tr>
                                <th width="3%">STT</th>
                                <th width="14%">Nơi đi</th>
                                <th width="14%">Nơi đến</th>
                                <th width="10%">Booking</th>
                                <th width="10%">Số vé</th>
                                <th width="15%">Doanh số</th>
                                <th width="10%">% DS</th>
                                <th>Hãng bay</th>
                            </tr>
                            <tr class="total-line footer-tr">
                                <th colspan="3"><i>Thông tin tổng hợp</i></th>
                                <th>{$TOTAL_QTY}</th>
                                <th style="text-align: center;">{$TOTAL_TICKET}</th>
                                <th style="text-align: center;">{$TOTAL_PROFIT}</th>
                                <th style="text-align: center; ">100%</th>
                                <th>Hoàn BK: {$RETURN_BK}; Vé: {$RETURN_TICKET}<br>DS hoàn: {$RETURN_AMT}đ</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$DATA}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- HÀNH TRÌNH QUỐC TẾ -->
        <div class="tab-pane fade" id="chartjs__journey-inter-pane" role="tabpanel" aria-labelledby="chartjs__journey-inter-tab" tabindex="0">
            <div class="box-tabs mb-0">
                <div class="chartjs__journey-inter--wrap w-100">
                   <canvas id="chartjs__journey-inter" class="mx-auto"></canvas>
                </div>

                <div class="airport__statistics--wrap mt-3 pb-2">
                    <table class="data-list table-airport__statistics table-details__booking" cellspacing="0" cellpadding="0">
                        <thead>
                            <tr>
                                <th width="3%">STT</th>
                                <th width="14%">Nơi đi</th>
                                <th width="14%">Nơi đến</th>
                                <th width="10%">Booking</th>
                                <th width="10%">Số vé</th>
                                <th width="15%">Doanh số</th>
                                <th width="10%">% DS</th>
                                <th>Hãng bay</th>
                            </tr>
                            <tr class="total-line footer-tr">
                                <th colspan="3"><i>Thông tin tổng hợp</i></th>
                                <th>{$TOTAL_QTY_INTER}</th>
                                <th style="text-align: center;">{$TOTAL_TICKET_INTER}</th>
                                <th style="text-align: center;">{$TOTAL_PROFIT_INTER}</th>
                                <th style="text-align: center; ">100%</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            {$DATA_INTER}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
       
        <!-- NƠI ĐẶT VÉ CONTENT -->
        <!-- <div class="tab-pane fade" id="chartjs__location-pane" role="tabpanel" aria-labelledby="chartjs__location-tab" tabindex="0">
            <div class="box-tabs mb-0">
                <div class="chartjs__location--wrap w-100">
                   <canvas id="chartjs__location" class="mx-auto"></canvas>
                </div>

                <div class="location-booking--wrap mt-3">
                    <table class="data-list table-location-booking table-details__booking" cellspacing="0" cellpadding="0">
                        <thead>
                            <tr>
                                <th width="3%">STT</th>
                                <th width="14%">Nơi đặt vé</th>
                                <th width="14%">Booking</th>
                                <th width="10%">Số vé</th>
                                <th width="15%">Doanh số</th>
                                <th width="10%">% DS</th>
                            </tr>
                            {$DATA_LOCATION_TOTAL}
                        </thead>
                        <tbody>
                            {$DATA_LOCATION}
                        </tbody>
                    </table>
                </div>
            </div>
        </div> -->
    </div>
</div>

{literal}
    <script>
        // Lấy tham chiếu đến canvas
        const chart_journey         = document.getElementById('chartjs__journey');
        const chart_journey_inter    = document.getElementById('chartjs__journey-inter');

        // Dữ liệu cho biểu đồ - domestic
        const data = {
            labels: label_journey,
            datasets: [
                {
                    type: 'line',
                    data: data_total_ticket,
                    label: 'Số vé',
                    backgroundColor: 'rgb(75, 192, 192)',
                    borderColor: 'rgb(75, 192, 192)',
                    pointBorderWidth: 3,
                    tension: 0.2,
                    yAxisID: 'line-y-axis',
                    datalabels: {
                        align: 'end',
                        anchor: 'end',
                    }
                }, 
                {
                    type: 'bar',
                    data: data_journey,
                    label: 'Doanh số',
                    backgroundColor: ['#FFD95A'], // Màu nền cho các phần tử
                    // hoverBackgroundColor: ['#FFD95A'], // Màu nền khi hover
                    borderWidth: 1, // Độ rộng viền
                    yAxisID: 'bar-y-axis',
                    // Cấu hình hiển thị %Ds trên các cột doanh số
                    datalabels: {
                        align: 'center',
                        anchor: 'center',
                        // formatter: function(value, context) {
                        //     const total = data_total_profit_new.map(Number).reduce((a, b) => a + b, 0);
                        //     const percentage  = (value / total * 100).toFixed(2);
                        //     return `${percentage}%`;
                        // }
                        formatter: function(value, context) {
                            const config = { style: 'currency', currency: 'VND', maximumFractionDigits: 9}
                            const formated__value = new Intl.NumberFormat('vi-VN', config).format(value);
                            return `${formated__value}`;
                        }
                    }
                },
            ]
        };

        // Dữ liệu cho biểu đồ - inter
        const data_inter = {
            labels: label_journey_inter,
            datasets: [
                {
                    type: 'line',
                    data: data_total_ticket_inter,
                    label: 'Số vé',
                    backgroundColor: 'rgb(75, 192, 192)',
                    borderColor: 'rgb(75, 192, 192)',
                    pointBorderWidth: 3,
                    tension: 0.2,
                    yAxisID: 'line-y-axis',
                    datalabels: {
                        align: 'end',
                        anchor: 'end',
                    }
                }, 
                {
                    type: 'bar',
                    data: data_journey_inter,
                    label: 'Doanh số',
                    backgroundColor: ['#FFD95A'], // Màu nền cho các phần tử
                    // hoverBackgroundColor: ['#FFD95A'], // Màu nền khi hover
                    borderWidth: 1, // Độ rộng viền
                    yAxisID: 'bar-y-axis',
                    // Cấu hình hiển thị %Ds trên các cột doanh số
                    datalabels: {
                        align: 'center',
                        anchor: 'center',
                        // formatter: function(value, context) {
                        //     const total = data_total_profit_new_inter.map(Number).reduce((a, b) => a + b, 0);
                        //     const percentage  = (value / total * 100).toFixed(2);
                        //     return `${percentage}%`;
                        // }
                        formatter: function(value, context) {
                            const config = { style: 'currency', currency: 'VND', maximumFractionDigits: 9}
                            const formated__value = new Intl.NumberFormat('vi-VN', config).format(value);
                            return `${formated__value}`;
                        }
                    }
                },
            ]
        };

        // Tùy chọn cấu hình cho biểu đồ - domestic
        const options = {
            responsive: true, // Để biểu đồ thích ứng với kích thước của vùng chứa
            plugins: {
                title: {
                    display: true,
                    text: 'Top 10 hành trình có doanh số cao nhất',
                    padding: {
                        top: 10,
                        bottom: 20
                    },
                    color: '#000',
                    font: {
                        size: 14,
                        weight: 'bold',
                    }
                },
                // Cấu hình plugin datalabels
                datalabels: {
                    color: '#000', // Màu sắc của văn bản
                    font: {
                        weight: 'bold'
                    }
                }
            },
            scales: {
                'bar-y-axis': {
                    type: 'linear',
                    position: 'left',
                    beginAtZero: true
                },
                'line-y-axis': {
                    type: 'linear',
                    position: 'right',
                    beginAtZero: true
                }
            }
        };
        const options_inter = {
            responsive: true, // Để biểu đồ thích ứng với kích thước của vùng chứa
            plugins: {
                title: {
                    display: true,
                    text: 'Top 10 hành trình có doanh số cao nhất',
                    padding: {
                        top: 10,
                        bottom: 20
                    },
                    color: '#000',
                    font: {
                        size: 14,
                        weight: 'bold',
                    }
                },
                // Cấu hình plugin datalabels
                datalabels: {
                    color: '#000', // Màu sắc của văn bản
                    font: {
                        weight: 'bold'
                    }
                }
            },
            scales: {
                'bar-y-axis': {
                    type: 'linear',
                    position: 'left',
                    beginAtZero: true
                },
                'line-y-axis': {
                    type: 'linear',
                    position: 'right',
                    beginAtZero: true
                }
            }
        };

        // Tạo biểu đồ - domestic 
        const mixedChart = new Chart(chart_journey, {
            data: data, // Dữ liệu
            plugins: [ChartDataLabels],
            options: options // Tùy chọn cấu hình
        });

        // Tạo biểu đồ - inter 
        const mixedChart_inter = new Chart(chart_journey_inter, {
            data: data_inter, // Dữ liệu
            plugins: [ChartDataLabels],
            options: options_inter // Tùy chọn cấu hình
        });
    </script>

    <!-- <script>
        const chart_location = document.getElementById('chartjs__location');

        // Dữ liệu cho biểu đồ
        const data_location = {
            labels: label_location,
            datasets: [
                {
                    type: 'line',
                    data: data_ticket_location,
                    label: 'Số vé',
                    backgroundColor: 'rgb(75, 192, 192)',
                    borderColor: 'rgb(75, 192, 192)',
                    pointBorderWidth: 3,
                    tension: 0.2,
                    yAxisID: 'line-y-axis',
                    datalabels: {
                        align: 'end',
                        anchor: 'end',
                    }
                }, 
                {
                    type: 'bar',
                    data: data_profit_location,
                    label: 'Doanh số',
                    backgroundColor: ['#FF6384', '#0688f9 ', '#FFD95A', '#1F8A70', '#DB005B'], // Màu nền cho các phần tử
                    hoverBackgroundColor: ['#FF6384', '#0688f9 ', '#FFD95A', '#1F8A70', '#DB005B'], // Màu nền khi hover
                    borderWidth: 1, // Độ rộng viền
                    yAxisID: 'bar-y-axis',
                    // Cấu hình hiển thị %Ds trên các cột doanh số
                    datalabels: {
                        align: 'center',
                        anchor: 'center',
                        formatter: function(value, context) {
                            const total = data_total_profit_location.map(Number).reduce((a, b) => a + b, 0);
                            const percentage  = (value / total * 100).toFixed(2);
                            return `${percentage}%`;
                        }
                    }
                },
            ]
        };

        // Tùy chọn cấu hình cho biểu đồ
        const options_location = {
            responsive: true, // Để biểu đồ thích ứng với kích thước của vùng chứa
            plugins: {
                title: {
                    display: true,
                    text: 'Top 5 nơi đặt vé có doanh số cao nhất',
                    padding: {
                        top: 10,
                        bottom: 20
                    },
                    color: '#000',
                    font: {
                        size: 14,
                        weight: 'bold',
                    }
                },
                // Cấu hình plugin datalabels
                datalabels: {
                    color: '#000', // Màu sắc của văn bản
                    font: {
                        weight: 'bold'
                    }
                }
            },
            scales: {
                'bar-y-axis': {
                    type: 'linear',
                    position: 'left',
                    beginAtZero: true
                },
                'line-y-axis': {
                    type: 'linear',
                    position: 'right',
                    beginAtZero: true
                }
            }
        };

         // Tạo biểu đồ
         const mixedChart_location = new Chart(chart_location, {
            data: data_location, // Dữ liệu
            plugins: [ChartDataLabels],
            options: options_location // Tùy chọn cấu hình
        });

    </script> -->
{/literal}
