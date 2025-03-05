<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.3/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.1.0"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.3/moment.min.js"></script>
<script src="themes/SuiteP/libs/js/moment-timezone.js"></script>

{literal}
<style>
    .location.hcm {
        color: #1d1e22;
        background: #f2fbff;
        background: -webkit-linear-gradient(to right, #FFFFFF, #f2fbff, #ffffff);
        background: linear-gradient(to right, #FFFFFF, #f2fbff, #ffffff);
    }

    .colon {
        animation: timing 1.75s infinite;
        margin-bottom: 5px;
    }
    @keyframes timing {
        0% {
            opacity: 1;
        }
        45% {
            opacity: 0;
        }
        55% {
            opacity: 0;
        }
        100% {
            opacity: 1;
        }
    }


</style>
<script>
    $(document).ready(function () {
        // fetchDashboardData();
        fetchDashboardDataCdrCalls();

        updateCurrentTime();
        setInterval(updateCurrentTime, 1000);
    });

    const updateCurrentTime = () => {
        // currentTime = moment(new Date());
        let hcmTimeZone = moment.tz("Asia/Ho_Chi_Minh");

        $(".location-hcm .time-24 .hour").html(hcmTimeZone.format("HH"));
        $(".location-hcm .time-24 .minute").html(hcmTimeZone.format("mm"));
        $(".location-hcm .time-24 .second").html(hcmTimeZone.format("ss"));
        $(".location-hcm .time-12").html(hcmTimeZone.format("LT"));
        $(".location-hcm .day").html(hcmTimeZone.format("ddd."));
        $(".location-hcm .date").html(hcmTimeZone.format("Do"));
    }

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

    async function fetchDashboardDataCdrCalls() {
        try {
            let response = await $.ajax({
                url: "index.php?entryPoint=entryPointOverviewDashBoard",
                type: "POST",
                data: { type: "GET_DATA_CDR_CALLS" },
                async: true // Đảm bảo async
            });

            let result = JSON.parse(response);

            $('#data_cdr_volume').text(JSON.stringify(result.volume));
            $('#data_cdr_minutes').text(JSON.stringify(result.minutes));
            $('#data_cdr_cpm').text(JSON.stringify(result.call_per_min));
            $('#data_cdr_missed').text(JSON.stringify(result.missed));
            $('#data_cdr_asr').text(JSON.stringify(result.asr));
            $('#data_cdr_aloc').text(JSON.stringify(result.aloc));

            setTimeout(() => {
                createCdr_Stats_Chart();
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

    function createCdr_Stats_Chart() {
        const ctx = document.getElementById("cdr_stats_chart");

        const data_cdr_volume = JSON.parse($("#data_cdr_volume").html() || "[]");
        const data_cdr_minutes = JSON.parse($("#data_cdr_minutes").html() || "[]");
        const data_cdr_cpm = JSON.parse($("#data_cdr_cpm").html() || "[]");
        const data_cdr_missed = JSON.parse($("#data_cdr_missed").html() || "[]");
        const data_cdr_asr = JSON.parse($("#data_cdr_asr").html() || "[]");
        const data_cdr_aloc = JSON.parse($("#data_cdr_aloc").html() || "[]");

        const cdr_stats_data = {
            datasets: [{
                        label: "Volume",
                        data: data_cdr_volume,
                        backgroundColor: "#EDC240",
                        borderColor: "#EDC240",
                        fill: false
                    },
                    {
                        label: "Minutes",
                        data: data_cdr_minutes,
                        backgroundColor: "#AFD8F8",
                        borderColor: "#AFD8F8",
                        fill: false
                    },
                    {
                        label: "Calls Per Min",
                        data: data_cdr_cpm,
                        backgroundColor: "#CB4B4B",
                        borderColor: "#CB4B4B",
                        fill: false
                    },
                    {
                        label: "Missed",
                        data: data_cdr_missed,
                        backgroundColor: "#4DA74D",
                        borderColor: "#4DA74D",
                        fill: false
                    },
                    {
                        label: "ASR",
                        data: data_cdr_asr,
                        backgroundColor: "#9440ED",
                        borderColor: "#9440ED",
                        fill: false
                    },
                    {
                        label: "ALOC",
                        data: data_cdr_aloc,
                        backgroundColor: "#BD9B33",
                        borderColor: "#BD9B33",
                        fill: false
                    }
            ]
        };

        const cdr_stats_config = {
            type: 'line',
            data: cdr_stats_data,
            options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'rect',
                                color: '#444',
                                boxWidth: 15
                            }
                        }
                    },
                    scales: {
                        x: {
                            type: "time",
                        },
                        y: {
                            min: 0
                        }
                    },
                    elements: {
                        line: {
                            tension: 0.3
                        }
                    }
            },
            scales: {
                    xAxes: {type: "time",timeFormat: "%d:%H",minTickSize: [1, "hour"]},
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
            }
        };

        const cdr_stats_chart = new Chart(ctx, cdr_stats_config);
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
                <div class='flex-center p-3 position-relative overflow-hidden location hcm'>
                    <div class='location-hcm position-relative text-center w-100'>
                      <h2 class='time-24 lh-1 mb-0'>
                        <span class='hour fw-semibold'></span>
                        <span class='colon'>:</span>
                        <span class='minute fw-medium'></span>
                        <span class='colon'>:</span>
                        <span class='second fw-medium'></span>
                      </h2>
                      <span class='time-12 fw-medium d-block mb-2'></span>
                      <h4 class='fw-semibold'>
                        <span class='day me-1'></span>
                        <span class='date'></span>
                      </h4>
                      <hr class='w-100'>
                      <h3 class='fw-medium lh-1 fs-5'>Hồ Chí Minh, Việt Nam</h3>
                    </div>
                </div>
                <div class="d-flex align-items-start row d-none">
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
    <div class="row d-none">
        <div class="col-md-6">
            <div class="card box-section h-100">
                <div class="card-header flex-between p-1">
                    <h5 class="card-title m-0">Cuộc gọi</h5>
                    <div class="dropdown">
                        <button class="btn btn-light p-0" type="button" id="customerCalls" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 12c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"></path></svg>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="customerCalls">
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
                        <button class="btn btn-light p-0" type="button" id="customerBookings" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 12c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"></path></svg>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="customerBookings">
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
    <div class="row">
        <div class="col-md-12">
            <div class="card box-section h-100">
                <div class="card-header flex-between p-1">
                    <h5 class="card-title m-0">Thống kê chi tiết cuộc gọi</h5>
                    <div class="dropdown">
                        <button class="btn btn-light p-0" type="button" id="statisticsCdr" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 12c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"></path></svg>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="statisticsCdr">
                            <a class="dropdown-item" href="index.php?module=Calls&action=summary&return_module=Calls&return_action=summary">Xem chi tiết</a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-2">
                    <canvas id="cdr_stats_chart" class="w-90 m-auto" style="max-height: 500px;"></canvas>
                </div>
                <div class="card-footer p-0">
                    <div id="data_cdr_volume" class="d-none">[[1741150800000,0],[1741147200000,56],[1741143600000,113],[1741140000000,65],[1741136400000,14],[1741132800000,1],[1741129200000,0],[1741125600000,0],[1741122000000,0],[1741118400000,0],[1741114800000,0],[1741111200000,0],[1741107600000,0],[1741104000000,0],[1741100400000,0],[1741096800000,0],[1741093200000,2],[1741089600000,31],[1741086000000,29],[1741082400000,28],[1741078800000,36],[1741075200000,114],[1741071600000,83]]</div>
                    <div id="data_cdr_minutes" class="d-none">[[1741150800000,0],[1741147200000,14.6],[1741143600000,35.5],[1741140000000,17.5],[1741136400000,7.4],[1741132800000,0],[1741129200000,0],[1741125600000,0],[1741122000000,0],[1741118400000,0],[1741114800000,0],[1741111200000,0],[1741107600000,0],[1741104000000,0],[1741100400000,0],[1741096800000,0],[1741093200000,1],[1741089600000,6.5],[1741086000000,9.5],[1741082400000,11.7],[1741078800000,7.9],[1741075200000,25.4],[1741071600000,18.2]]</div>
                    <div id="data_cdr_cpm" class="d-none">[[1741150800000,0],[1741147200000,0],[1741143600000,0],[1741140000000,0],[1741136400000,0],[1741132800000,0],[1741129200000,0],[1741125600000,0],[1741122000000,0],[1741118400000,0],[1741114800000,0],[1741111200000,0],[1741107600000,0],[1741104000000,0],[1741100400000,0],[1741096800000,0],[1741093200000,0],[1741089600000,0],[1741086000000,0],[1741082400000,0],[1741078800000,0],[1741075200000,0],[1741071600000,0]]</div>
                    <div id="data_cdr_missed" class="d-none">[[1741150800000,0],[1741147200000,26],[1741143600000,42],[1741140000000,27],[1741136400000,4],[1741132800000,1],[1741129200000,0],[1741125600000,0],[1741122000000,0],[1741118400000,0],[1741114800000,0],[1741111200000,0],[1741107600000,0],[1741104000000,0],[1741100400000,0],[1741096800000,0],[1741093200000,0],[1741089600000,18],[1741086000000,15],[1741082400000,9],[1741078800000,25],[1741075200000,58],[1741071600000,46]]</div>
                    <div id="data_cdr_asr" class="d-none">[[1741150800000,0],[1741147200000,0.5357],[1741143600000,0.6283],[1741140000000,0.5846],[1741136400000,0.7143],[1741132800000,0],[1741129200000,0],[1741125600000,0],[1741122000000,0],[1741118400000,0],[1741114800000,0],[1741111200000,0],[1741107600000,0],[1741104000000,0],[1741100400000,0],[1741096800000,0],[1741093200000,1],[1741089600000,0.4194],[1741086000000,0.4828],[1741082400000,0.6786],[1741078800000,0.3056],[1741075200000,0.49119999999999997],[1741071600000,0.4458]]</div>
                    <div id="data_cdr_aloc" class="d-none">[[1741150800000,0],[1741147200000,0.49],[1741143600000,0.5],[1741140000000,0.46],[1741136400000,0.74],[1741132800000,0],[1741129200000,0],[1741125600000,0],[1741122000000,0],[1741118400000,0],[1741114800000,0],[1741111200000,0],[1741107600000,0],[1741104000000,0],[1741100400000,0],[1741096800000,0],[1741093200000,0.48],[1741089600000,0.5],[1741086000000,0.68],[1741082400000,0.61],[1741078800000,0.72],[1741075200000,0.45],[1741071600000,0.49]]</div>
                </div>
            </div>
        </div>
    </div>
</div>