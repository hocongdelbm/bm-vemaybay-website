<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.3/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.1.0"></script>

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

     .card.card-border-shadow-secondary::after {
          border-bottom-color: #ced3da
     }

     .card.card-border-shadow-secondary:hover::after {
          border-bottom-color: #8592a3
     }

     .card.card-border-shadow-light:hover::after {
          border-bottom-color: #dbdee0
     }

     .card.card-border-shadow-light::after {
          border-bottom-color: #f1f2f3
     }

     .card.card-border-shadow-light:hover::after {
          border-bottom-color: #dbdee0
     }

     .card.card-border-shadow-dark::after {
          border-bottom-color: #aaabb3;
     }

     .card.card-border-shadow-dark:hover::after {
          border-bottom-color: #2b2c40;
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

     .box-contacts__type .card p{
          font-size: 1rem;
          color: var(--text-color);
          font-weight: bold;
     }

     /* .month-chart-area canvas{
          width: 550px;
          margin: 0 auto;
     } */
</style>
<script>
     $(document).ready(function() {
          $("#year_select").change(function() {
               $("#from_date").val($(this).find("option:selected").attr("fromdate"));
               $("#to_date").val($(this).find("option:selected").attr("todate"));

               $(".container-waiting").show();
               $("#ec_search_form").submit();
          });
          
          createContactsChartDoughnut();
          createContactsChartCombo();

          function createContactsChartDoughnut() {
               const chart_label   = $("#chart-donut-label").text().split(",") || [];
               const chart_data    = $("#chart-donut-value").text().split("|") || [];
               const chartColors   = $("#chart-donut-colors").text().split(",") || [];

               // chart data
               const data_test = {
                    labels: chart_label,
                    datasets: [
                         {
                              data: chart_data,
                              borderSkipped: false,
                              // backgroundColor: chartColors,
                         },
                    ],
               };
               
               const config = {
                    type: 'doughnut',
                    data: data_test,
                    options: {
                         responsive: true,
                         plugins: {
                              legend: {
                                   position: 'top',
                              },
                              title: {
                                   display: false,
                                   text: ''
                              },
                              datalabels: {
                                   align: "start", 
                                   anchor: "end", 
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

               // create chart
               new Chart(
                    document.getElementById('contact-chart-doughnut'),
                    config
               );
          }

          function createContactsChartCombo() {
               const chart_labels       = $("#chart-data-label").text().split(",") || [];
               const chart_profit_0       = $("#chart-data-profit-0").text().split("|") || [];
               const chart_profit_1       = $("#chart-data-profit-1").text().split("|") || [];
               const chart_profit_2       = $("#chart-data-profit-2").text().split("|") || [];

               const data = {
                    labels: chart_labels,
                    datasets: [
                         {
                              label: 'Chu kỳ hiện tại', 
                              data: chart_profit_0,
                              borderWidth: 2,
                              borderRadius: 8,
                         },
                         {
                              label: 'Chu kỳ 1 năm trước', 
                              data: chart_profit_1,
                              borderWidth: 2,
                              borderRadius: 8,
                         },
                         {
                              label: 'Chu kỳ 2 năm trước', 
                              data: chart_profit_2,
                              borderWidth: 2,
                              borderRadius: 8,
                         },
                    ]
               };

               const config = {
                    type: 'bar',
                    data: data,
                    options: {
                         responsive: true,
                         plugins: {
                              legend: {
                                   position: 'top',
                              },
                              title: {
                                   display: true,
                                   text: '(Doanh số)',
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
                                   formatter: function(value, context) {
                                        // Làm tròn giá trị theo tỉ lệ 1 triệu
                                        const roundedValue = Math.round(value / 1000000);

                                        const config = { style: 'currency', currency: 'VND', maximumFractionDigits: 0 };
                                        const formattedValue = new Intl.NumberFormat('vi-VN', config).format(roundedValue);  // Lấy số tròn, rồi format về tiền tệ

                                        return formattedValue.replace('₫', ''); // Xóa ký tự '₫' từ chuỗi định dạng
                                   }
                              }
                         },
                    },
                    plugins: [ChartDataLabels]
               };

               // Create chart
               new Chart(
                    document.getElementById('contact-chart-combo'),
                    config
               );
          }
     });
</script>
{/literal}

<div class="report-date__call">
     <div class="title-wrap flex-start mb-3">
          <h1 class="title m-0">Báo cáo khách hàng</h1>
          <form id="ec_search_form" method="post" action="index.php">
               <input type="hidden" name="module" value="{$MODULE_NAME}">
               <input type="hidden" name="action" value="summary">
               <input type="hidden" name="from_date" id="from_date" value="{$FROM_DATE}">
               <input type="hidden" name="to_date" id="to_date" value="{$TO_DATE}">
               <select class="box-select" id="year_select" name="year_select">{$YEAR_SELECT}</select>
          </form>
     </div>
     
     <div class="box-contacts__type">
          <div class="row">
               {foreach from=$DATA_TYPE_CONTACTS item=itemType}
                    <div class="col-lg-2 col-sm-6">
                         <div class="card card-border-shadow-{$itemType.class} h-100">
                              <div class="card-body p-3">
                                   <div class="flex-between mb-2">
                                        <div class="box-left flex-start">
                                             <div class="box-icon">
                                                  <span class="box-icon-initial rounded bg-label-{$itemType.class}">
                                                       <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-bounding-box" viewBox="0 0 16 16">
                                                            <path d="M1.5 1a.5.5 0 0 0-.5.5v3a.5.5 0 0 1-1 0v-3A1.5 1.5 0 0 1 1.5 0h3a.5.5 0 0 1 0 1zM11 .5a.5.5 0 0 1 .5-.5h3A1.5 1.5 0 0 1 16 1.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 1-.5-.5M.5 11a.5.5 0 0 1 .5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 1 0 1h-3A1.5 1.5 0 0 1 0 14.5v-3a.5.5 0 0 1 .5-.5m15 0a.5.5 0 0 1 .5.5v3a1.5 1.5 0 0 1-1.5 1.5h-3a.5.5 0 0 1 0-1h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 1 .5-.5"/>
                                                            <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
                                                       </svg>
                                                  </span>
                                             </div>
                                             <h5 class="mb-0">{$itemType.data_cnt}</h5>
                                        </div>
                                        <div class="box-right flex-start">
                                             <h6 class="mb-0 fw-semibold text-dark">{$itemType.data_profit}</h6>
                                        </div>
                                   </div>
                                   <div class="flex-between">
                                        <p class="mb-0">{$itemType.label}</p>
                                        <a class="text-{$itemType.class}" href="index.php?module=Contacts&action=typereports&type_customer={$itemType.type|upper}&year_select={$OPTION_SELECTED}&from_date={$FROM_DATE}&to_date={$TO_DATE}">
                                             <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                                                  <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8"></path>
                                             </svg>
                                         </a>
                                   </div>
                                   <p class="mb-0 d-none">
                                        <code class="text-muted">{$itemType.desc}</code>
                                   </p>
                              </div>
                         </div>
                    </div>
               {/foreach}
          </div>
     </div>
     <div class="box-section row flex-nowrap gap-5 align-items-center">
          <div class="chart-box w-70">
               <div class="month-contact-chart">
                    <div class="chart-data d-none">
                         <div id="chart-data-label">{$CHART_LABELS_BARCOMBO}</div>
                         <div id="chart-data-profit-0">{$CHART_DATA_PROFIT_0}</div>
                         <div id="chart-data-profit-1">{$CHART_DATA_PROFIT_1}</div>
                         <div id="chart-data-profit-2">{$CHART_DATA_PROFIT_2}</div>
                    </div>
                    <section class="month-chart-area h-100">
                         <canvas id="contact-chart-combo"></canvas>
                    </section>
               </div>
          </div>
          <div class="chart-box flex-fill">
               <div class="month-contact-chart">
                    <div class="chart-data d-none">
                         <div id="chart-donut-label">{$CHART_LABELS_DONUT}</div>
                         <div id="chart-donut-value">{$CHART_DATA_DONUT}</div>
                         <div id="chart-donut-colors">{$CHART_COLORS_DONUT}</div>
                    </div>
                    <section class="month-chart-area h-100">
                         <canvas id="contact-chart-doughnut"></canvas>
                    </section>
               </div>
          </div>
     </div>
     <div class="box-section box-desc">
          <h3 class="sub-title">Mô tả loại khách hàng</h3>
          <div class="accordion row" id="accordionPanelsStayOpenExample">
               {foreach from=$DATA_TYPE_CONTACTS item=itemType name=groupType}
               <div class="col-md-6 {if not $smarty.foreach.groupType.last}mb-3{/if}">
                    <div class="accordion-item">
                         <h2 class="accordion-header">
                              <button class="accordion-button w-100 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accor-{$itemType.type}" aria-expanded="true" aria-controls="accor-{$itemType.type}">
                                   {$itemType.label}
                              </button>
                         </h2>
                         <div id="accor-{$itemType.type}" class="accordion-collapse collapse">
                              <div class="accordion-body">
                                   {$itemType.desc}
                              </div>
                         </div>
                    </div>
               </div>
               {/foreach}
          </div>
     </div>
</div>    