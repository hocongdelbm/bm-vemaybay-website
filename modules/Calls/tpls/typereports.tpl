<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.3/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.1.0"></script>
{literal}
<style>
    .chart-box{
        height: 400px;
    }

    .chart-box canvas{
        width: 90% !important;
        height: 400px;
    }
</style>
<script>
    $(document).ready(function() {
        createOverviewChart('chart_overview_calls', 'chart_label_calls', 'chart_data_calls');

        $("#month_select").change(function() {
            $("#from_date").val($(this).find("option:selected").data("from-date"));
            $("#to_date").val($(this).find("option:selected").data("to-date"));

            $(".container-waiting").show();
            $("#ec_search_form").submit();
        });

        function createOverviewChart(chartId, labelId, dataId){
            const chart_label   = $('#'+labelId).text().split("|") || [];
            const chart_data    = $('#'+dataId).text().split("|").map(val => parseFloat(val)) || [];
    
            if(chart_label.length > 0) {
                const total = chart_data.reduce((sum, val) => !isNaN(val) ? sum + val : sum, 0);
    
                const data = {
                    labels: chart_label,
                    datasets: [
                        {
                            data: chart_data,
                            borderSkipped: false,
                        },
                    ],
                };
    
                const config_chart = {
                    type: 'doughnut',
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
                                // display: function(context) {
                                //     const value = context.dataset.data[context.dataIndex];
                                //     const percentage = (value / total) * 100;
                                //     return percentage >= 5;
                                // },
                                align: "center",
                                anchor: "center",
                                color: '#000',
                                font: {
                                    weight: 'bold',
                                    size: 14
                                },
                                // formatter: function(value) {
                                //     let total = chart_data.map(Number).reduce((a, b) => a + b, 0);
                                //     let percentage  = (value == 0) ? '' : (value / total * 100).toFixed(2) + '%';
                                //     return `${percentage}`;
                                // }
                            }
                        }
                    },
                    plugins: [ChartDataLabels]
                };
    
                const chart_overview_calls = new Chart(document.getElementById(chartId), config_chart);
            }
        }
    });
</script>
{/literal}

<div class="call-type-report">
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
        <span>Loại cuộc gọi</span>
    </div>

    <form action="index.php" method="post" id="ec_search_form">
        <input type="hidden" name="module" value="{$MODULE_NAME}">
        <input type="hidden" name="action" value="typereports"/>
        <input type="hidden" name="type_call" value="{$TYPE_CALL}">
            
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

        <input type="submit" class="btn btn-primary button-action" name="btnSearch" id="btnSearch" value="Xem thống kê" title="Xem thống kê" />
    </form> 
</div>

<div class="box-section box-chart">
    <div class="chart-box">
        <canvas id="chart_overview_calls" class="chart-overview-calls"></canvas>
        <div class="chart-label d-none" id="chart_label_calls">{$LIST_CALL_REASON_LABEL}</div>
        <div class="chart-data d-none" id="chart_data_calls">{$LIST_CALL_REASON_DATA}</div>
    </div>
</div>

<div class="box-section box-data">
    {$LIST_CALL_DATA}
</div>