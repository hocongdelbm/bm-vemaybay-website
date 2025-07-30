<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.3/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.1.0"></script>

<link type="text/css" rel="stylesheet" href="modules/{$MODULE_NAME}/css/report_call.css">
{literal}
<style>
    .breadscrumb-area {
        display: flex;
        align-items: center;
        gap: 5px;
        opacity: 0.7;
        font-size: 14px;
    }

    .report-header-area {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .report-title {
        font-size: 1rem;
        font-weight: bold;
        color: #36454F;
        margin-bottom: 0;
    }

    /* ITEM EMP */
    .card-emp {
        background-color: #011522;
        border-radius: 0.375rem;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .card-emp .tools {
        display: flex;
        align-items: center;
    }

    .card-emp .circle {
        padding: 0 4px;
    }

    .card-emp .box {
        display: inline-block;
        align-items: center;
        width: 10px;
        height: 10px;
        padding: 1px;
        border-radius: 50%;
    }

    .card-emp .red {
        background-color: #ff605c;
    }

    .card-emp .yellow {
        background-color: #ffbd44;
    }

    .card-emp .green {
        background-color: #00ca4e;
    }

    .card-emp .card-emp-icon{
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .card-emp .card-emp-icon .emp-avatar{
        height: 2.5rem;
        width: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

</style>
<script>
    $(document).ready(function() {
        $("#month_select").change(function() {
            $("#from_date").val($(this).find("option:selected").data("from-date"));
            $("#to_date").val($(this).find("option:selected").data("to-date"));

            $(".container-waiting").show();
            $("#ec_search_form").submit();
        });

        // VIEW LIST CALLS OF EMPLOYEE
        $('.view-detail-emp').on('click', function() {
            let employee_id = $(this).data('employee-id');
            let view_type   = $(this).data('type');

            let for_post = '';
            if(view_type == 'view-statistics'){
                for_post = 'viewStatisticsCallsEmployees';
            } else if (view_type == 'view-list'){
                for_post = 'viewListCallsEmployees';
            }

            $.ajax({
                url: "index.php?entryPoint=entryPointStatisticsCall",
                type: "POST",
                cache: false,
                data: {
                    employee_id: employee_id,
                    from_date: $("#from_date").val(),
                    to_date: $("#to_date").val(),
                    for: for_post,
                },
                beforeSend: function () {
                    $(".modal_spinner").show();
                    $("#response__detail-result").html('');
                },
                success: function (response) {
                    $(".modal_spinner").hide();
                    $("#response__detail-result").html(response);

                    if(view_type == 'view-statistics'){
                        const arrayColumns = [
                            {key: 'direction', label: 'Loại cuộc gọi'},
                            {key: 'avg_duration', label: 'Thời lượng trung bình'},
                            {key: 'status', label: 'Trạng thái cuộc gọi'},
                            {key: 'avg_dir', label: 'Thời lượng trung bình theo loại'},
                        ];
                        arrayColumns.forEach(column => {
                            const chartId       = `chart-emp-${column.key}`;
                            const labelId       = `chart-emp-${column.key}-label`;
                            const dataId        = `chart-emp-${column.key}-data`;
                            const typeChartId   = `chart-emp-${column.key}-type`;

                            createOverviewEmployeeChart(chartId, labelId, dataId, typeChartId);
                        });
                    } 
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    console.error(XMLHttpRequest);
                    console.error("Status: " + textStatus);
                    console.error("Error: " + errorThrown);
                }
            });
        });

        function createOverviewEmployeeChart(chartId, labelId, dataId, typeChartId){
            const chart_label   = $('#'+labelId).text().split("|") || [];
            const chart_type    = $('#'+typeChartId).text() || 'bar';

            if(chart_type == 'bar-combo'){
                var chart_data = $('#'+dataId).text().split('|');
            } else {
                var chart_data  = $('#'+dataId).text().split("|").map(val => parseFloat(val)) || [];
            }


            if(chart_label.length > 0) {
                const total = chart_data.reduce((sum, val) => !isNaN(val) ? sum + val : sum, 0);

                if(chart_type == 'doughnut'){
                    const data = {
                        labels: chart_label,
                        datasets: [
                            {
                                data: chart_data,
                                borderSkipped: false,
                            },
                        ],
                    };

                    var config_chart = {
                        type: chart_type,
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: "right",
                                    align: "middle",
                                },
                                datalabels: {
                                    display: function(context) {
                                        const value = context.dataset.data[context.dataIndex];
                                        const percentage = (value / total) * 100;
                                        return percentage >= 5;
                                    },
                                    align: "center",
                                    anchor: "center",
                                    color: '#000',
                                    font: {
                                        weight: 'bold',
                                        size: 14
                                    },
                                    formatter: function(value) {
                                        let total = chart_data.map(Number).reduce((a, b) => a + b, 0);
                                        let percentage  = (value == 0) ? '' : (value / total * 100).toFixed(2) + '%';
                                        return `${percentage}`;
                                    }
                                }
                            }
                        },
                        plugins: [ChartDataLabels]
                    };

                } else if (chart_type == 'bar'){
                    const data = {
                        labels: chart_label,
                        datasets: [
                            {
                                label: '(Đơn vị: giây)',
                                data: chart_data,
                                borderWidth: 0,
                                borderRadius: 8,
                                borderSkipped: false,
                                backgroundColor: '#36a2eb',
                            },
                        ],
                    };

                    var config_chart = {
                        type: chart_type,
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false,
                                    position: "top",
                                    align: "middle",
                                },
                                title: {
                                        display: true,
                                        text: 'Giây (s)',
                                        align:'start',
                                        position:'top',
                                        font: {
                                            style: 'italic'
                                        }
                                },
                                datalabels: {
                                    align: "center",
                                    anchor: "center",
                                    color: '#000',
                                    font: {
                                        weight: 'bold',
                                        size: 14
                                    },
                                    // formatter: function(value) {
                                        //     const nFormat = new Intl.NumberFormat();
                                        //     return `${nFormat.format(Math.round(value))}`;
                                    // }
                                }
                            }
                        },
                        plugins: [ChartDataLabels]
                    };

                } else if (chart_type == 'bar-combo'){
                    const data = {
                        labels: chart_label,
                        datasets: [
                            {
                                label: 'Thời lượng',
                                data: chart_data[0].split(',').map(Number),
                                backgroundColor: '#A4D9D7',
                                borderColor: '#A4D9D7',
                                borderWidth: 1,
                                borderRadius: 8,
                            },
                            {
                                label: 'Hội thoại',
                                data: chart_data[1].split(',').map(Number),
                                backgroundColor: '#5DADE2',
                                borderColor: '#5DADE2',
                                borderWidth: 1,
                                borderRadius: 8,
                            },
                            {
                                label: 'Thời gian chờ',
                                data: chart_data[2].split(',').map(Number),
                                backgroundColor: 'rgba(255, 160, 63, 1)',
                                borderColor: 'rgba(255, 159, 64, 1)',
                                borderWidth: 1,
                                borderRadius: 8,
                            }
                        ]
                    };

                    var config_chart = {
                        type: 'bar',
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: "top",
                                    align: "middle",
                                },
                                title: {
                                        display: true,
                                        text: 'Giây (s)',
                                        align:'start',
                                        position:'top',
                                        font: {
                                            style: 'italic'
                                        }
                                },
                                datalabels: {
                                    align: "center",
                                    anchor: "center",
                                    color: '#000',
                                    font: {
                                        weight: 'bold',
                                        size: 14
                                    },
                                }
                            },
                        },
                        plugins: [ChartDataLabels]
                    };

                }

                // create chart_overview_calls
                const chart_overview_calls = new Chart(document.getElementById(chartId), config_chart);
            }
        }
    });
</script>
{/literal}

<div class="emp-calls-report">
    <div class="breadscrumb-area mb-2 mx-2">
        <a href="index.php?module={$MODULE_NAME}&action=summary&return_module={$MODULE_NAME}&return_action=summary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house" viewBox="0 0 16 16">
                <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5z"/>
            </svg>
        </a>
        <span class="breadscrumb-sep">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/>
            </svg>
        </span>
        <span>Cuộc gọi nhân viên</span>
    </div>
    <div class="report-header-area">
        <h2 class="report-title">Báo cáo cuộc gọi nhân viên {$REPORT_TIME}</h2>
        <form id="ec_search_form" method="post" action="index.php">
            <input type="hidden" name="module" value="{$MODULE_NAME}">
            <input type="hidden" name="action" value="employee_report">
            <input type="hidden" name="from_date" id="from_date" value="{$FROM_DATE}">
            <input type="hidden" name="to_date" id="to_date" value="{$TO_DATE}">
            <select class="box-select" id="month_select" name="month_select">{$MONTH_SELECT}</select>
        </form>
    </div>
    <div class="report-main-area box-section">
        {$REPORT_EMPLOYEE}
    </div>

    <!-- MODAL DETAIL RESULT -->
    <div class="modal-detailview__result modal fade" id="modalViewDetail" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalViewDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <!-- <div class="modal-header bg-primary text-white">
                    <h2 class="modal-title fs-5" id="modalViewDetailLabel">Chi tiết cuộc gọi nhân viên <span class="detail-full-name"></span></h2>
                </div> -->
                <div class="modal-body">
                    <div class="modal_spinner"></div>
                    <div id="response__detail-result" class="response__detail-result box-table table-responsive"></div>
                </div>
                <div class="modal-footer flex-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
</div>