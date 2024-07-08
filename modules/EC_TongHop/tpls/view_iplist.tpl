
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.5/css/dataTables.bootstrap5.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.bootstrap5.css">

<script src="https://cdn.datatables.net/2.0.5/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.5/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.2/js/responsive.bootstrap5.js"></script>

{literal}
    <script>
        $(document).ready(function() {
            $('.table-iplist').each(function(index){
                $(this).DataTable(
                    {
                        responsive: true,
                        "pageLength": 25,
                        order: [[2, 'desc']],
                    } 
                );
            });

            // $('.table-iplist').DataTable(
            //     {
            //         responsive: true,
            //         "pageLength": 25,
            //         order: [[2, 'desc']],
            //     } 
            // );

            Calendar.setup({
                inputField: "from_date",
                // daFormat: "%d-%m-%Y",
                daFormat: "%d-%m-%Y %H:%M",
                button: "fdate_trigger",
                singleClick: true,
                dateStr: "",
                step: 1,
                position: [230, 202],
            });
            Calendar.setup({
                inputField: "to_date",
                // daFormat: "%d-%m-%Y",
                daFormat: "%d-%m-%Y %H:%M",
                button: "tdate_trigger",
                singleClick: true,
                dateStr: "",
                step: 2
            });

            $(document).on("click", ".block-item", function(e) {
                let ip = $(this).attr("ip");
                let time_block = $(this).attr("time-block");
                let domain = $(this).attr("domain");

                $.ajax({
                    url: "index.php?entryPoint=entryPointFlightBookings",
                    type: "POST",
                    data: {
                        ip: ip,
                        domain: domain,
                        time: time_block,
                        for: "blockIP",
                    },
                    beforeSend: function () {
                        $('.container-waiting').show();
                    },
                    success: function(response) {
                        $('.container-waiting').hide();
				        res = JSON.parse(response);

                        if(res.code == 200){
                            showModalNotify(1, 'Block IP thành công');
                        } else {
                            showModalNotify(0, 'Block IP không thành công');
                        }
                    }
                });
            });

            $(document).on("click", ".white-list-item", function(e) {
                let ip = $("#white-list").val().trim();
                let time_whitelist = $(this).attr("time-wl");
                let domain = $(this).attr("domain");

                if(ip.length === 0){
                    showModalNotify(0, 'IP không hợp lệ!');
                } else {
                    $.ajax({
                        url: "index.php?entryPoint=entryPointFlightBookings",
                        type: "POST",
                        data: {
                            ip: ip,
                            domain: domain,
                            time: time_whitelist,
                            for: "whitelistIP",
                        },
                        beforeSend: function () {
                            $('.container-waiting').show();
                        },
                        success: function(response) {
                            $('.container-waiting').hide();
                            res = JSON.parse(response);
    
                            if(res.code == 200){
                                showModalNotify(1, 'White list IP thành công');
                            } else {
                                showModalNotify(0, 'White list IP không thành công');
                            }
                        }
                    });
                }
            });

            $(document).on("click", ".btn-allow", function(e) {
                let ip = $(this).attr("ip");
                let domain = $(this).attr("domain");

                $.ajax({
                    url: "index.php?entryPoint=entryPointFlightBookings",
                    type: "POST",
                    data: {
                        ip: ip,
                        domain: domain,
                        for: "allowIP",
                    },
                    beforeSend: function () {
                        $('.container-waiting').show();
                    },
                    success: function(response) {
                        $('.container-waiting').hide();
				        res = JSON.parse(response);

                        if(res.code == 200){
                            showModalNotify(1, 'Unblock IP thành công');
                        } else {
                            showModalNotify(0, 'Block IP không thành công');
                        }
                        // $('.modal-overlay, .btn-modal-close').addClass('reload');
                    }
                });
            });

            $(document).on("click", ".btn-history-block", function(e) {
                let ip = $(this).attr("ip");
                let domain = $(this).attr("domain");
                let site = $(this).attr("site");
                let from_date = $(this).attr("data-fromdate");
                let to_date = $(this).attr("data-todate");

                $.ajax({
                    url: "index.php?entryPoint=entryPointFlightBookings",
                    type: "POST",
                    data: {
                        ip: ip,
                        domain: domain,
                        from_date: from_date,
                        to_date: to_date,
                        for: "HistoryBlockIP",
                    },
                    beforeSend: function () {
                        $("#modal_history-block-"+site+" #content-history").html('');
                    },
                    success: function(response) {
				        res = JSON.parse(response);
                        
                        if(res.code == 200){
                            if(res.data.length > 0){
                                let html = `<table class="table-details__booking">
                                            <thead>
                                                <tr>
                                                    <th>IP</th>    
                                                    <th>Bắt đầu Block</th>    
                                                    <th>Thời gian</th>    
                                                    <th>Kết thúc Block</th>    
                                                </tr>
                                            </thead>
                                            <tbody>`;
                                        res.data.forEach(function(blocks, index) {
                                            let date_start = new Date(blocks.start_time * 1000);
                                            date_start.setHours(date_start.getHours() + 7);
                                            
                                            let duration = new Date(blocks.duration * 1000).toISOString().substr(11, 8);

                                            html += `<tr>
                                                        <td>${res.ip}</td>
                                                        <td>${date_start.toISOString().slice(0, 19).replace('T', ' ')}</td>
                                                        <td>${duration}</td>
                                                        <td>${blocks.block_to}</td>
                                                    </tr>`;
                                        });

                                    html += `</tbody></table>`;

                                $("#modal_history-block-"+site+" #content-history").append(html);
                            } else {
                                $("#modal_history-block-"+site+" #content-history").append(
                                    `<div class="d-flex flex-column gap-2 align-items-center">
                                        <svg fill="#b3b3b3" width="70" height="70" viewBox="0 0 846.66 846.66" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd" version="1.1" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" stroke="#b3b3b3"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <defs> <style type="text/css">  .fil0 {fill:black;fill-rule:nonzero}  </style> </defs> <g id="Layer_x0020_1"> <path class="fil0" d="M93.28 100.89l69.12 0 0 -71.08c0,-11.44 9.28,-20.71 20.72,-20.71l414.03 0c97.36,0 176.94,79.58 176.94,176.94l0 539.02c0,11.44 -9.27,20.71 -20.71,20.71l-69.12 0 0 71.08c0,11.44 -9.28,20.71 -20.72,20.71l-570.26 0c-11.44,0 -20.71,-9.27 -20.71,-20.71l0 -695.25c0,-11.44 9.27,-20.71 20.71,-20.71zm148.42 178.12c-27.24,0 -27.24,-41.42 0,-41.42l273.42 0c27.24,0 27.24,41.42 0,41.42l-273.42 0zm0 216.78c-27.24,0 -27.24,-41.42 0,-41.42l273.42 0c27.24,0 27.24,41.42 0,41.42l-273.42 0zm0 -108.39c-27.24,0 -27.24,-41.42 0,-41.42l273.42 0c27.24,0 27.24,41.42 0,41.42l-273.42 0zm-37.87 -286.51l303.48 0c97.36,0 176.95,79.58 176.95,176.94l0 426.52 48.41 0 0 -518.31c0,-74.49 -61.03,-135.52 -135.52,-135.52l-393.32 0 0 50.37zm11.51 478.47l326.15 0c11.43,0 20.71,9.28 20.71,20.71l0 105.46c0,11.44 -9.28,20.72 -20.71,20.72l-326.15 0c-11.44,0 -20.71,-9.28 -20.71,-20.72l0 -105.46c0,-11.43 9.27,-20.71 20.71,-20.71zm305.43 41.42l-284.72 0 0 64.04 284.72 0 0 -64.04zm-13.46 -478.47l-393.32 0 0 653.83 528.84 0 0 -518.31c0,-74.49 -61.02,-135.52 -135.52,-135.52z"></path> </g> </g></svg>
                                        <p class="fs-6 fw-semibold">IP này chưa có lịch sử block!</p>
                                    </div>`
                                );
                            }
                        } else {
                            showModalNotify(0, 'ERROR: response undefined!');
                        }
                    }
                });
            });

            $(document).on("change", "#date_select", function(e) {
                $("#from_date").val($(this).find("option:selected").attr("from_date"));
                $("#to_date").val($(this).find("option:selected").attr("to_date"));
                sessionStorage.setItem('date_select_ip', $(this).val());
            });
            
            $(document).on("change", "#site_select", function(e) {
                sessionStorage.setItem('site_select_ip', $(this).val());
            });

            // Check sessionStorage - js
            const date_select_id 		= document.getElementById('date_select');
            const savedSelectdate = sessionStorage.getItem('date_select_ip');

            if (savedSelectdate) {
                date_select_id.value = savedSelectdate;
            }
        });
    </script>
    <style>
        #ip_report .dateTime input.date_input{
            min-width: 155px;
            width: fit-content;
        }

        .ip-nav-tabs button{
            border-radius: unset;
            min-width: 100px;
        }

        .ip-tab-content{
            padding: 15px;
            border: 1px solid #dee2e6;
        }

        .white-list-ip a.dropdown-item,
        #iplist_tbl a.dropdown-item{
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
        }

        .btn-group__wrap,
        .tcb-wrap{
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn-group__wrap{
            justify-content: center;
        }

        .btn-group__wrap .btn-group{
            flex: 1;
        }

        div.dt-container div.dt-length label{
            display: none;
        }

        .white-list-ip{
            position: absolute;
            top: 16px;
            right: 16px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .call-statistics__wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .call-statistics__total {
            width: 70%;
            height: 400px;
        }

        .call-statistics__total canvas {
            width: 70% !important;
        }

        @media screen and (max-width: 575px),
        (orientation: landscape) and (max-width: 950px){
            .tcb-wrap{
                gap: 0.25rem;
            }

            .tcb-wrap span{
                font-size: 12px;
            }

            .call-statistics__total,
            .call-statistics__total canvas {
                width: 100% !important;
            }

            .white-list-ip,
            div.dt-container > .row:first-child {
                display: none;
            }
            
            table#iplist_tbl thead th{
                white-space: nowrap;
            }

            ul.dtr-details{
                width: 100%;
            }

            ul.dtr-details li{
                display: flex;
                justify-content: space-between;
                padding: 0.25rem 10px !important;
            }

            ul.dtr-details li:last-child .dtr-title{
                display: none !important;
            }

            ul.dtr-details li:last-child .dtr-data{
                flex: 1;
            }

        }
    </style>
{/literal}

<div class="title-wrap d-flex align-items-center justify-content-between gap-2">
    <h1 class="report_title title">Danh sách IP Tìm chuyến bay</h1>
    <svg xmlns="http://www.w3.org/2000/svg" id="filter_report" width="32" height="32" fill="currentColor" class="bi bi-filter d-xxl-none d-xl-none d-lg-none d-block" viewBox="0 0 16 16">
        <path d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5"/>
    </svg>
</div>

<div class="box-section position-relative mt-0" id="ip_report">
    <div class="overlay-mobile"></div>

    <form action="index.php" method="post" name="search_form" id="ec_search_form">
        <input type="hidden" name="module" value="EC_TongHop" />
        <input type="hidden" name="action" value="iplist" />

        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-dash-lg search_form--dash d-xl-none d-lg-none d-block" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8"></path>
        </svg>

        <div class="action--wrap d-flex align-items-center gap-4">
            <select class="box-select" id="date_select" name="date_select">
                <option value="" from_date="" to_date="">--Trống--</option>
                <option value="THREE_HOURS_AGO" from_date="{$THREE_HOURS_AGO}" to_date="{$CURRENT_DATE}">3 giờ trước</option>
                <option value="SIX_HOURS_AGO" from_date="{$SIX_HOURS_AGO}" to_date="{$CURRENT_DATE}">6 giờ trước</option>
                <option value="TWELVE_HOURS_AGO" from_date="{$TWELVE_HOURS_AGO}" to_date="{$CURRENT_DATE}">12 giờ trước</option>
                <option value="TODAY" from_date="{$TODAY}" to_date="{$TODAY}">Hôm nay</option>
                <option value="YESTERDAY" from_date="{$YESTERDAY}" to_date="{$YESTERDAY}">Hôm qua</option>
                <option value="THREEDAY_AGO" from_date="{$THREEDAY_AGO}" to_date="{$TODAY}">Cách 3 ngày</option>
                <option value="SEVENDAY_AGO" from_date="{$SEVENDAY_AGO}" to_date="{$TODAY}">Cách 7 ngày</option>
                <option value="THIRTYDAY_AGO" from_date="{$THIRTYDAY_AGO}" to_date="{$TODAY}">Cách 30 ngày</option>
            </select>

            <div class="from-to-date--wrap d-inline-flex gap-2 align-items-center">
                <div class="d-flex gap-2 align-items-center date_trigger--wrap fdate_trigger--wrap">
                    <span class="sublabel">Từ ngày: </span>    
                    <div class="dateTime d-flex gap-2 position-relative">
                        <input class="date_input box-input" type="text" maxlength="10" size="11" title="" value="{$FROM_DATE_VALUE}" id="from_date" name="from_date" autocomplete="off">
                        <button class="icon_dateTime" type="button" id="fdate_trigger" onclick="return false;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                                <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                            </svg>
                        </button>
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
                    <input  class="date_input box-input" type="text" maxlength="10" size="11" title="" value="{$TO_DATE_VALUE}" id="to_date" name="to_date" autocomplete="off">
                        <button class="icon_dateTime" type="button" id="tdate_trigger" onclick="return false;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                                <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="button-action--wrap d-flex align-items-center">
            <input type="submit" id="btnView" name="btnView" class="btn btn-primary button-action" value="Xem" title="Xem"/>
            <!-- <input type="submit" id="btnExport" name="btnExport" class="btn btn-danger" value="List Deny" title="List Deny" /> -->
            <input type="button" id="btnSearch_cancel" value="Hủy bỏ" name="search_cancel" class="btn btn-secondary button-action--cancel d-xl-none d-lg-none d-block" title="Hủy bỏ"/>
        </div>
    </form>
  
    {$IP_LIST_TBL}
</div>