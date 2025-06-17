
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.5/css/dataTables.bootstrap5.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.bootstrap5.css">
{literal}
<style>
    .nav-link {color: var(--bs-nav-tabs-link-active-color);}
    .nav-link.active{
        color: var(--bs-nav-link-color) !important;
        isolation: isolate;
    }

    .ip-nav-tabs button{
        border-radius: unset;
        min-width: 100px;
    }

    .ip-tab-content {
        width: 100%;
        border: 1px solid #dee2e6;
        padding: 15px;
    }

    .table-iplist tbody tr td {vertical-align: middle;}
    .table-iplist tbody tr.whitelist .btn-group__wrap {display: none;}

    .table-iplist tbody tr.whitelist td:first-child > a > span {color: #42b32e;}
    .table-iplist tbody tr.alert-danger {background-color: #ffeeef;}
    .table-iplist tbody tr.alert-danger td:first-child > a > span {color: red;}

    .table-iplist tbody tr:hover {background-color: #f2f2f2 !important;}
    .table-iplist tbody tr.whitelist:hover {background-color: #ebffdf !important;}
    .table-iplist tbody tr.alert-danger:hover {background-color: #ffced3 !important;}

    .table-iplist a.dropdown-item, .handle-ip a.dropdown-item {
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
    }

    .btn-group__wrap, .search-journey-wrap {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .search-journey-wrap .journey {
        font-size: 13px;
        font-weight: 500;
        padding: 3px 6px;
    }

    .btn-group__wrap{justify-content: center;}
    .btn-group__wrap .btn-group{flex: 1;}

    div.dt-container div.dt-length label{display: none;}

    .handle-ip {
        position: absolute;
        top: 16px;
        right: 16px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .overview__wrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .call-statistics__total {
        width: 70%;
        height: 400px;
    }

    .call-statistics__total canvas {
        width: 70% !important;
    }

    .tag {
        border-radius: 4px;
        box-shadow: rgba(0, 0, 0, 0.15) 0px 2px 8px;
        padding: 2px 6px;
        margin-right: 5px;
    }
    .tag-whitelist {
        background-color: #42b32e;
        color: #fff;
        letter-spacing: 1px;
    }
    .tag-block {
        background-color: #ff0000;
        color: #fff;
    }

    @media screen and (max-width: 575px), (orientation: landscape) and (max-width: 950px){
        .tcb-wrap{gap: 0.25rem;}
        .tcb-wrap span{font-size: 12px;}

        .call-statistics__total,
        .call-statistics__total canvas {
            width: 100% !important;
        }

        .handle-ip,
        div.dt-container > .row:first-child {
            display: none;
        }
        
        table#iplist_tbl thead th{white-space: nowrap;}

        ul.dtr-details{width: 100%;}
        ul.dtr-details li{
            display: flex;
            justify-content: space-between;
            padding: 0.25rem 10px !important;
        }
        ul.dtr-details li:last-child .dtr-title{display: none !important;}
        ul.dtr-details li:last-child .dtr-data{flex: 1;}
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
            <input type="button" id="btnSearch_cancel" value="Hủy bỏ" name="search_cancel" class="btn btn-secondary button-action--cancel d-xl-none d-lg-none d-block" title="Hủy bỏ"/>
        </div>
    </form>
  
    {$IP_LIST_TBL}
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.3/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.1.0"></script>
<script src="https://cdn.datatables.net/2.0.5/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.5/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.2/js/responsive.bootstrap5.js"></script>
{literal}
<script>
    var ENDPOINT = 'index.php?entryPoint=entryPointFlightBookings';

    $(document).ready(function() {
        $('.table-iplist').each(function(index){
            $(this).DataTable(
                {
                    responsive: true,
                    pageLength: 50,
                    order: [[2, 'desc']],
                }
            );
        });

        Calendar.setup({
            inputField: "from_date",
            daFormat: "%d-%m-%Y %H:%M",
            button: "fdate_trigger",
            singleClick: true,
            dateStr: "",
            step: 1,
            position: [230, 202],
        });
        Calendar.setup({
            inputField: "to_date",
            daFormat: "%d-%m-%Y %H:%M",
            button: "tdate_trigger",
            singleClick: true,
            dateStr: "",
            step: 2
        });

        $(document).on("click", ".btn-block", function(e) {
            let ip = $(this).attr("ip");
            let duration = $(this).attr("duration");
            let domain = $(this).attr("domain");

            if(ip.length*domain.length*duration.length == 0) {
                showModalNotify(0, 'Dữ liệu không hợp lệ, vui lòng thử lại sau!');
                return false;
            }

            $.ajax({
                url: ENDPOINT,
                type: "POST",
                data: {
                    for: "block_ip",
                    ip: ip,
                    duration: duration,
                    domain: domain,
                },
                beforeSend: function () {
                    $('.container-waiting').show();
                },
                success: function(response) {
                    $('.container-waiting').hide();

                    if(response && response.length > 3) {
                        res = JSON.parse(response);
                        if(res.error == 0) {
                            showModalNotify(1, `Đã chặn truy cập ${ip}`);
                            return true;
                        }
                    }

                    console.log(response);
                    showModalNotify(0, 'Thao tác thất bại');
                    return false;
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $('.container-waiting').hide();
                    showModalNotify(0, `ERROR ${textStatus}: ${errorThrown}`);
                    console.error(XMLHttpRequest);
                }
            });
        });

        $(document).on("click", ".btn-unblock", function(e) {
            let ip = $(this).attr("ip");
            let domain = $(this).attr("domain");
            if($(this).attr("id") == 'unblock_ip') ip = $('#input_ip').val();

            if(ip.length*domain.length == 0) {
                showModalNotify(0, 'Dữ liệu không hợp lệ, vui lòng thử lại sau!');
                return false;
            }

            $.ajax({
                url: ENDPOINT,
                type: "POST",
                data: {
                    for: 'unblock_ip',
                    domain: domain,
                    ip: ip
                },
                beforeSend: function () {
                    $('.container-waiting').show();
                },
                success: function(response) {
                    $('.container-waiting').hide();

                    if(response && response.length > 3) {
                        res = JSON.parse(response);
                        if(res.error == 0) {
                            showModalNotify(1, `Đã mở truy cập ${ip}`);
                            return true;
                        }
                    }

                    console.log(response);
                    showModalNotify(0, 'Thao tác thất bại');
                    return false;
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $('.container-waiting').hide();
                    showModalNotify(0, `ERROR ${textStatus}: ${errorThrown}`);
                    console.error(XMLHttpRequest);
                }
            });
        });

        $(document).on("click", ".btn-history-block", function(e) {
            let str = $(this).attr("data");
            let site = $(this).attr("site");

            if(!str.length || str.length == 0) {
                showModalNotify(0, 'Không có dữ liệu');
                return false;
            }

            let tbody = '';
            let data_block = JSON.parse(atob(str));

            data_block.forEach(function(blocks, index) {
                let date_start = new Date(blocks.start_time * 1000);
                date_start.setHours(date_start.getHours() + 7);
                let duration = new Date(blocks.duration * 1000).toISOString().substr(11, 8);

                tbody += `<tr>
                    <td>${res.ip}</td>
                    <td>${date_start.toISOString().slice(0, 19).replace('T', ' ')}</td>
                    <td>${blocks.block_to}</td>
                    <td>${duration}</td>
                </tr>`;
            });

            let html = `<table class="table-details__booking">
                <thead>
                    <tr>
                        <th>IP</th>    
                        <th>Bắt đầu chặn</th>    
                        <th>Kết thúc chặn</th>
                        <th>Thời gian chặn</th>    
                    </tr>
                </thead>
                <tbody>${tbody}</tbody>
            </table>`;

            $(`#modal_history-block-${site} #content-history`).html(html);
        });

        $(document).on("click", ".handle-ip-item", function(e) {
            let ip = $('#input_ip').val();
            let domain = $(this).attr("domain");
            let duration = $(this).attr("duration");
            let for_ = $(this).hasClass('block-item') ? 'block_ip' : 'whitelist_ip';

            if(ip.length*domain.length*duration.length == 0) {
                showModalNotify(0, 'Dữ liệu không hợp lệ, vui lòng thử lại sau!');
                e.preventDefault();
                return false;
            }

            $.ajax({
                url: ENDPOINT,
                type: "POST",
                data: {
                    for: for_,
                    ip: ip,
                    domain: domain,
                    duration: duration,
                },
                beforeSend: function () {
                    $('.container-waiting').show();
                },
                success: function(response) {
                    $('.container-waiting').hide();

                    if(response && response.length > 3) {
                        res = JSON.parse(response);
                        if(res.error == 0) {
                            console.warn(for_);
                            let m = for_ == 'block_ip' ? `Đã chặn ${ip}` : `Đã cho phép ${ip}`;
                            showModalNotify(1, m);
                            return true;
                        }
                    }

                    console.error(response);
                    showModalNotify(0, 'Thao tác thất bại');
                    return false;
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $('.container-waiting').hide();
                    showModalNotify(0, `ERROR ${textStatus}: ${errorThrown}`);
                    console.error(XMLHttpRequest);
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
        const date_select_id = document.getElementById('date_select');
        const savedSelectdate = sessionStorage.getItem('date_select_ip');

        if (savedSelectdate) {
            date_select_id.value = savedSelectdate;
        }
    });
</script>
{/literal}