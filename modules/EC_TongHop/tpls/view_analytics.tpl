

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.3/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.1.0"></script>

{literal}
    <script>
        $(document).ready(function() {
            AnalyticsOnline();
            setInterval(AnalyticsOnline, 60000);
        });

        function AnalyticsOnline(){
            $.ajax({
                url: "index.php?entryPoint=entryPointAnalytics",
                type: "POST",
                cache: false,
                data: {
                    type: 'AnalyticsOnline',
                },
                success: function (response) {
                    $("#box-analytics").html(response);
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    console.error(XMLHttpRequest);
                    console.error("Status: " + textStatus);
                    console.error("Error: " + errorThrown);
                }
            });
        }
    </script>
    <style>
        .analytics-tab-content{
            padding: 15px;
            border: 1px solid #dee2e6;
        }

        .analytics-nav-tabs button{
            border-radius: unset;
            min-width: 100px;
        }

        .analytistics__total {
            display: flex;
            gap: 20px;
            align-items: end;
            height: 400px;
            flex-wrap: wrap;
        }

        .analytistics__domestic{
            width: 60%;
        }

        .analytistics__inter{
            flex: 1;
        }

        .analytistics__total canvas {
            width: 80% !important;
        }

    </style>
{/literal}

<div class="title-wrap d-flex align-items-center justify-content-between gap-2">
    <h1 class="report_title title">Analytics Tìm chuyến bay</h1>
    <svg xmlns="http://www.w3.org/2000/svg" id="filter_report" width="32" height="32" fill="currentColor" class="bi bi-filter d-xxl-none d-xl-none d-lg-none d-block" viewBox="0 0 16 16">
        <path d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5"/>
    </svg>
</div>

<div class="box-section position-relative mt-0" id="box-analytics"></div>