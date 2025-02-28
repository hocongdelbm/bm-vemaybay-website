<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.3/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.1.0"></script>

{literal}
<style></style>
<script>
    $(document).ready(function () {
        fetchDashboardData();
    });

    async function fetchDashboardData() {
        try {
            let response = await $.ajax({
                url: "index.php?entryPoint=entryPointOverviewDashBoard",
                type: "POST",
                data: { type: "GET_TOTAL_OF_WEEK" },
                async: true // Đảm bảo async
            });

            let result = JSON.parse(response);

            $('#chart-calls-data-y').text(result.calls.this_week);
            $('#chart-calls-data-y1').text(result.calls.last_week);
            $('#chart-bookings-data-y').text(result.bookings.this_week);
            $('#chart-bookings-data-y1').text(result.bookings.last_week);
            $("#user_topkpi").text(result.kpi.full_name);
            $("#qty_topkpi").text(result.kpi.total_kpi);

            // 🔥 Chạy function sau 300ms để tránh làm chậm UI
            setTimeout(() => {
                getChartCalls();
                getChartBookings();
            }, 300);
        } catch (error) {
            console.warn("Lỗi AJAX:", error);
        }
    }

    // CHART BOOKINGS
    function getChartCalls() {
        const thisWeekData = $('#chart-calls-data-y').text().split(",") || [];
        const lastWeekData = $('#chart-calls-data-y1').text().split(",") || [];

        const data_chart_calls = {
            labels: ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'],
            datasets: [
                {
                    label: 'Tuần này',
                    data: thisWeekData,
                    borderColor: 'rgb(138, 188, 60)',
                    backgroundColor: 'rgb(138, 188, 60)',
                    // yAxisID: 'y',
                    fill: false,
                    cubicInterpolationMode: 'monotone',
                    tension: 0.4
                },
                {
                    label: 'Tuần trước',
                    data: lastWeekData,
                    borderColor: 'rgb(236, 178, 16)',
                    backgroundColor: 'rgb(236, 178, 16)',
                    // yAxisID: 'y1',
                    fill: false,
                    cubicInterpolationMode: 'monotone',
                    tension: 0.4
                }
            ]
        }

        let delayed;
        const config_chart_calls = {
            type: 'line',
            data: data_chart_calls,
            options: {
                animation: {
                    onComplete: () => {
                        delayed = true;
                    },
                    delay: (context) => {
                        let delay = 0;
                        if (context.type === 'data' && context.mode === 'default' && !delayed) {
                            delay = context.dataIndex * 150 + context.datasetIndex * 50;
                        }
                        return delay;
                    },
                },
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                stacked: false,
                plugins: {
                    title: {
                        display: true,
                        text: '(Số lượng)',
                        align:'start',
                        position:'top',
                        font: {
                            style: 'italic'
                        }
                    },
                },
                // scales: {
                //     y: {
                //         type: 'linear',
                //         display: true,
                //         position: 'left',
                //         stacked: true,
                //         grid: {
                //             drawOnChartArea: false, 
                //         },
                //     },
                //     y1: {
                //         type: 'linear',
                //         display: true,
                //         position: 'right',
                //         stacked: true,
                //         grid: {
                //             drawOnChartArea: false, 
                //         },
                //     },
                // }
            },
        };

        new Chart(
            document.getElementById('chart-calls'),
            config_chart_calls
        );
    }

    // CHART BOOKINGS
    function getChartBookings() {
        const thisWeekData = $('#chart-bookings-data-y').text().split(",") || [];
        const lastWeekData = $('#chart-bookings-data-y1').text().split(",") || [];

        const data_chart_bookings = {
            labels: ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'],
            datasets: [
                {
                    label: 'Tuần này',
                    data: thisWeekData,
                    borderColor: 'rgb(138, 188, 60)',
                    backgroundColor: 'rgb(138, 188, 60)',
                    // yAxisID: 'y',
                    fill: false,
                    cubicInterpolationMode: 'monotone',
                    tension: 0.4
                },
                {
                    label: 'Tuần trước',
                    data: lastWeekData,
                    borderColor: 'rgb(236, 178, 16)',
                    backgroundColor: 'rgb(236, 178, 16)',
                    // yAxisID: 'y1',
                    fill: false,
                    cubicInterpolationMode: 'monotone',
                    tension: 0.4
                }
            ]
        }

        let delayed;
        const config_chart_bookings = {
            type: 'line',
            data: data_chart_bookings,
            options: {
                animation: {
                    onComplete: () => {
                        delayed = true;
                    },
                    delay: (context) => {
                        let delay = 0;
                        if (context.type === 'data' && context.mode === 'default' && !delayed) {
                            delay = context.dataIndex * 150 + context.datasetIndex * 50;
                        }
                        return delay;
                    },
                },
                responsive: true,
                interaction: {
                mode: 'index',
                    intersect: false,
                },
                stacked: false,
                plugins: {
                    title: {
                        display: true,
                        text: '(Số lượng)',
                        align:'start',
                        position:'top',
                        font: {
                            style: 'italic'
                        }
                    },
                },
                // scales: {
                //     y: {
                //         type: 'linear',
                //         display: true,
                //         position: 'left',
                //         grid: {
                //             drawOnChartArea: false, 
                //         },
                //     },
                //     y1: {
                //         type: 'linear',
                //         display: true,
                //         position: 'right',
                //         grid: {
                //             drawOnChartArea: false, 
                //         },
                //     },
                // }
            },
        };

        new Chart(
            document.getElementById('chart-bookings'),
            config_chart_bookings
        );
    }
</script>
{/literal}

<div class="dashboard-home">
    <div class="row">
        <div class="col-md-6">
            <div class="card box-section h-100 m-0">
                <div class="d-flex align-items-start row">
                    <div class="col-sm-7">
                        <div class="card-body p-0">
                            <h5 class="card-title text-primary mb-2">{$USER_NAME}! 🎉</h5>
                            <p class="mb-6">
                                🌟 Hôm nay là một ngày tuyệt vời dành cho bạn. Cảm ơn sự nỗ lực
                                không ngừng nghỉ của bạn!
                                <br />
                                Hãy tiếp tục bứt phá và chinh phục những đỉnh cao mới. 🚀
                            </p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body py-0 px-0 px-md-6">
                            <img src="themes/SuiteP/images/home/congratulations-bm.png" height="150" alt="congratulations bm" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card box-section h-100 m-0">
                <div class="d-flex align-items-start row">
                    <div class="col-7">
                        <div class="card-body">
                            <h5 class="card-title text-nowrap">Chúc mừng <span id="user_topkpi"></span>! 🎉</h5>
                            <p class="card-subtitle text-nowrap mb-2">Nhân viên chăm chỉ hôm nay</p>

                            <h5 class="card-title text-primary mb-0"><span id="qty_topkpi">0</span> điểm KPI</h5>
                        </div>
                    </div>
                    <div class="col-5">
                        <div class="card-body py-0 text-end">
                            <img src="themes/SuiteP/images/home/prize-light.png" width="91" height="150" class="rounded-start">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card box-section h-100">
                <div class="card-header flex-between p-1">
                    <h5 class="card-title m-0">Cuộc gọi</h5>
                    <div class="dropdown">
                        <button class="btn btn-light p-0" type="button" id="customerCalls" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 12c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"></path></svg>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="customerCalls" style="">
                            <a class="dropdown-item" href="javascript:void(0);">Xem chi tiết</a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-2">
                    <canvas id="chart-calls"></canvas>
                </div>
                <div class="card-footer d-none">
                    <div id="chart-calls-data-y"></div>
                    <div id="chart-calls-data-y1"></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card box-section h-100">
                <div class="card-header flex-between p-1">
                    <h5 class="card-title m-0">Booking</h5>
                    <div class="dropdown">
                        <button class="btn btn-light p-0" type="button" id="customerCalls" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 12c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"></path></svg>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="customerCalls" style="">
                            <a class="dropdown-item" href="javascript:void(0);">Xem chi tiết</a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-2">
                    <canvas id="chart-bookings"></canvas>
                </div>
                <div class="card-footer d-none">
                    <div id="chart-bookings-data-y"></div>
                    <div id="chart-bookings-data-y1"></div>
                </div>
            </div>
        </div>
    </div>
</div>