<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.3/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.1.0"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>

{literal}
<style>
     .card[class*=card-border-shadow-] {
          position: relative;
          border-bottom: none;
          transition: all .2s ease-in-out;
          z-index: 1;
     }

     .card[class*=card-border-shadow-]:hover {
          box-shadow: 0 .25rem .75rem 0 rgba(34, 48, 62, .14);
     }

     .card[class*=card-border-shadow-]:hover::after {
          border-bottom-width: 3px;
     }

     .card[class*=card-border-shadow-]::after {
          content: "";
          position: absolute;
          bottom: 0;
          left: 0;
          width: 100%;
          height: 100%;
          border-bottom-width: 2px;
          border-bottom-style: solid;
          border-radius: .375rem;
          transition: all .2s ease-in-out;
          z-index: -1;
     }

     .card {
          --bs-card-border-width: 0;
          --bs-card-border-color: #e4e6e8;
          background-clip: padding-box;
          box-shadow: 0 .1875rem .5rem 0 rgba(34, 48, 62, .1);
          border: var(--bs-card-border-width) solid var(--bs-card-border-color);
     }

     .card.card-border-shadow-primary::after {
          border-bottom-color: #c3c4ff;
     }

     .card.card-border-shadow-primary:hover::after {
          border-bottom-color: #696cff;
     }

     .card.card-border-shadow-success::after {
          border-bottom-color: #c6f1af;
     }

     .card.card-border-shadow-success:hover::after {
          border-bottom-color: #71dd37;
     }

     .card.card-border-shadow-warning::after {
          border-bottom-color: #fd9;
     }

     .card.card-border-shadow-warning:hover::after {
          border-bottom-color: #ffab00;
     }

     .card.card-border-shadow-danger::after {
          border-bottom-color: #ffb2a5;
     }

     .card.card-border-shadow-danger:hover::after {
          border-bottom-color: #ff3e1d;
     }

     .card.card-border-shadow-info::after {
          border-bottom-color: #9ae7f7;
     }

     .card.card-border-shadow-info:hover::after {
          border-bottom-color: #03c3ec;
     }

     .card.card-border-shadow-dark::after {
          border-bottom-color: #aaabb3;
     }

     .card.card-border-shadow-dark:hover::after {
          border-bottom-color: #2b2c40;
     }

     .bg-label-primary {
          background-color: #e7e7ff !important;
          color: #696cff !important;
     }

     .bg-label-success {
          background-color: #e8fadf !important;
          color: #71dd37 !important;
     }

     .bg-label-warning {
          background-color: #fff2d6 !important;
          color: #ffab00 !important;
     }

     .bg-label-danger {
          background-color: #ffe0db !important;
          color: #ff3e1d !important;
     }

     .bg-label-dark {
          background-color: #dddde0 !important;
          color: #2b2c40 !important;
     }

     .box-icon {
          position: relative;
          width: 2.375rem;
          height: 2.375rem;
          cursor: pointer;
     }

     .text-heading {
          --bs-text-opacity: 1;
          color: #384551 !important;
     }

     .text-muted {
          --bs-text-opacity: 1;
          color: #a7acb2 !important;
     }

     .box-icon .box-icon-initial {
          position: absolute;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          text-transform: uppercase;
          display: flex;
          align-items: center;
          justify-content: center;
          color: #fff;
          background-color: #eeedf0;
          font-size: .9375rem;
     }

     .box-call__direction .card p{
          font-size: 1rem;
          color: var(--text-color);
          font-weight: bold;
     }

     .chart-title{
          font-size: 1.25rem;
          color: #36454F;
     }

     /* REPORT EMPLOYEE */
     .month-employee-title{
          font-size: 1rem;
          font-weight: bold;
          display: flex;
          justify-content: space-between;
     }

     .month-employee-bottom{
          display: flex;
          flex-wrap: wrap;
     }

     li.month-emp-row:not(:last-child){
          border-bottom: 1px solid var(--border-color);
     }

     .month-emp-avatar{
          background: var(--border-color);
          padding: 5px;
          border-radius: 0.375rem;
     }


</style>
<script>
     $(document).ready(function() {
          createCallChart();
          createCdr_Stats_Chart();
          
          $("#month_select").change(function() {
               $("#from_date").val($(this).find("option:selected").data("from-date"));
               $("#to_date").val($(this).find("option:selected").data("to-date"));

               $(".container-waiting").show();
               $("#ec_search_form").submit();
          });

          // DATA CHART
          function createCallChart() {
               const y_data        = $("#chart-data-y").text().split("|") || [];
               const x_data        = $("#chart-data-x").text().split(",") || [];
               const chart_type    = $("#chart-type").text();

               if(chart_type == 'bar') {
                    background_cl = '#13678A';
               } else {
                    background_cl = '#fff';
               }

               // chart data
               const data = {
                    labels: x_data,
                    datasets: [
                         {
                              label: 'cuộc gọi',
                              data: y_data,
                              borderColor: '#012970',
                              backgroundColor: background_cl,
                              tension: 0.1,
                              borderRadius: 12,
                         }
                    ]
               };

               // chart config
               const config = {
                    type: $('#chart-type').text(),
                    data: data,
                    options: {
                         animations: {
                              radius: {
                                   duration: 400,
                                   easing: 'linear',
                                   loop: (context) => context.active
                              }
                         },
                         interaction: {
                              mode: 'nearest',
                              intersect: false,
                              axis: 'x'
                         },
                         plugins: {
                              tooltip: {
                                   enabled: true,
                              },
                              legend: {
                                   display: false,
                              },
                         },
                         elements: {
                              point:{
                                   radius: 5
                              }
                         }
                    },
               };

               // create chart
               new Chart(
                    document.getElementById('call-chart'),
                    config
               );
          }

          function createCdr_Stats_Chart() {
               const ctx = document.getElementById("cdr_stats_chart");

               const data_cdr_total = JSON.parse($("#data_cdr_total").html() || "[]");
               const data_cdr_minutes = JSON.parse($("#data_cdr_minutes").html() || "[]");
               const data_cdr_cpm = JSON.parse($("#data_cdr_cpm").html() || "[]");
               const data_cdr_failed = JSON.parse($("#data_cdr_failed").html() || "[]");
               const data_cdr_answered = JSON.parse($("#data_cdr_answered").html() || "[]");
               const data_cdr_asr = JSON.parse($("#data_cdr_asr").html() || "[]");
               const data_cdr_aloc = JSON.parse($("#data_cdr_aloc").html() || "[]");

               const cdr_stats_data = {
                    datasets: [{
                              label: "Total",
                              data: data_cdr_total,
                              backgroundColor: "#EDC240",
                              borderColor: "#EDC240",
                              fill: false,
                              cubicInterpolationMode: 'monotone',
                              tension: 0.4
                         },
                         {
                              label: "Failed",
                              data: data_cdr_failed,
                              backgroundColor: "#bd0000",
                              borderColor: "#bd0000",
                              fill: false,
                              cubicInterpolationMode: 'monotone',
                              tension: 0.4
                         },
                         {
                              label: "Answered",
                              data: data_cdr_answered,
                              backgroundColor: "#4DA74D",
                              borderColor: "#4DA74D",
                              fill: false,
                              cubicInterpolationMode: 'monotone',
                              tension: 0.4
                         },
                         {
                              label: "Minutes",
                              data: data_cdr_minutes,
                              backgroundColor: "#AFD8F8",
                              borderColor: "#AFD8F8",
                              fill: false,
                              cubicInterpolationMode: 'monotone',
                              tension: 0.4
                         },
                         {
                              label: "Calls Per Min",
                              data: data_cdr_cpm,
                              backgroundColor: "#CB4B4B",
                              borderColor: "#CB4B4B",
                              fill: false,
                              cubicInterpolationMode: 'monotone',
                              tension: 0.4
                         },
                         {
                              label: "ASR",
                              data: data_cdr_asr,
                              backgroundColor: "#9440ED",
                              borderColor: "#9440ED",
                              fill: false,
                              cubicInterpolationMode: 'monotone',
                              tension: 0.4
                         },
                         {
                              label: "ALOC",
                              data: data_cdr_aloc,
                              backgroundColor: "#BD9B33",
                              borderColor: "#BD9B33",
                              fill: false,
                              cubicInterpolationMode: 'monotone',
                              tension: 0.4
                         }
                    ]
               };

               let delayed;
               const cdr_stats_config = {
                    type: 'line',
                    data: cdr_stats_data,
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
                                   time: {
                                        unit: "hour",
                                        stepSize: 1,
                                        displayFormats: {
                                             hour: "h:mm a" 
                                        }
                                   },
                                   ticks: {
                                        source: 'auto'
                                   }
                              },
                              y: {
                                   beginAtZero: true,
                                   ticks: {
                                        stepSize: 10 
                                   }
                              }
                         },
                         elements: {
                              line: {
                                   tension: 0.3
                              }
                         }
                    },
               };

               const cdr_stats_chart = new Chart(ctx, cdr_stats_config);
          }
     });
</script>
{/literal}

<div class="report-date__call">
     <div class="chart-data d-none">
          <div id="chart-data-y">{$CHART_YAXIS}</div>
          <div id="chart-data-x">{$CHART_XAXIS}</div>
          <div id="chart-type">{$CHART_TYPE}</div>
     </div>
     
     <div class="title-wrap d-flex align-items-center justify-content-between gap-2">
          <h1 class="title">Báo cáo cuộc gọi {$REPORT_TIME}</h1>
          <svg xmlns="http://www.w3.org/2000/svg" id="filter_report" width="32" height="32" fill="currentColor" class="bi bi-filter d-xxl-none d-xl-none d-lg-none d-block hide-landscape" viewBox="0 0 16 16">
               <path d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5"/>
          </svg>
     </div>

     <div class="box-call__direction">
          <div class="row">
               {foreach from=$DATA_DIRECTION item=itemDirection}
                    <div class="col-lg-3 col-sm-6">
                         <div class="card card-border-shadow-{$itemDirection.class} h-100">
                              <div class="card-body">
                                   <div class="d-flex align-items-center mb-2">
                                        <div class="box-icon me-3">
                                             <span class="box-icon-initial rounded bg-label-{$itemDirection.class}">
                                                  {$itemDirection.icon}
                                             </span>
                                        </div>
                                        <h5 class="mb-0">{$itemDirection.data}</h5>
                                   </div>
                                   <div class="flex-between">
                                        <p class="mb-0">{$itemDirection.label}</p>
                                        <a class="text-{$itemDirection.class}" href="index.php?module=Calls&action=typereports&type_call={$itemDirection.type}&from_date={$FROM_DATE}&to_date={$TO_DATE}">
                                             <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                                                  <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8"></path>
                                             </svg>
                                         </a>
                                   </div>
                                   <p class="mb-0 d-none">
                                        <span class="text-heading fw-medium me-2">+18.2%</span>
                                        <span class="text-muted">So với hôm qua</span>
                                   </p>
                              </div>
                         </div>
                    </div>
               {/foreach}
          </div>
     </div>
     
     <div class="box-section mb-3 position-relative">
          <div class="chart-box">
               <div class="d-flex gap-4 month-call-chart">
                    <div class="chart-body flex-fill">
                         <section class="month-chart-area h-100">
                              <div class="chart-header flex-start mb-3">
                                   <div class="chart-title fw-bold d-inline-block">Tổng quan</div>
                                   <form action="index.php" method="post" id="ec_search_form">
                                        <input type="hidden" name="module" value="{$MODULE_NAME}">
                                        <input type="hidden" name="action" value="summary"/>

                                        <!-- <input type="hidden" name="from_date" id="from_date" value="{$FROM_DATE}">
                                        <input type="hidden" name="to_date" id="to_date" value="{$TO_DATE}"> -->

                                        <select class="box-select" id="month_select" name="month_select">{$MONTH_SELECT}</select>

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
                              <canvas id="call-chart"></canvas>
                         </section>
                    </div>
                    <div class="chart-sidebar w-25">
                         <section class="month-employee-area h-100">
                              <div class="month-employee-top flex-between mb-3">
                                   <p class="month-employee-title">Cuộc gọi nhân viên</p>
                                   <a href="index.php?module={$MODULE_NAME}&action=employee_report&from_date={$FROM_DATE}&to_date={$TO_DATE}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                                             <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8"/>
                                        </svg>
                                   </a>
                              </div>
                              <ul class="month-employee-bottom">
                                   {$EMPLOYEE_MONTH}
                              </ul>
                         </section>
                    </div>
               </div>
          </div>
     </div>

     <div class="box-section">
          <div class="flex-start">
               <div id="data_cdr_total" class="d-none">{$DATA_CDR_TOTAL}</div>
               <div id="data_cdr_failed" class="d-none">{$DATA_CDR_FAILED}</div>
               <div id="data_cdr_answered" class="d-none">{$DATA_CDR_ANSWERED}</div>
               <div id="data_cdr_minutes" class="d-none">{$DATA_CDR_MINUTES}</div>
               <div id="data_cdr_cpm" class="d-none">{$DATA_CDR_CPM}</div>
               <div id="data_cdr_asr" class="d-none">{$DATA_CDR_ASR}</div>
               <div id="data_cdr_aloc" class="d-none">{$DATA_CDR_ALOC}</div>

               <canvas id="cdr_stats_chart" class="w-90 m-auto" style="max-height: 500px;"></canvas>
          </div>
          {$CDR_STATS_TABLE}
     </div>
</div>    