<link type="text/css" rel="stylesheet" href="./themes/SuiteP/libs/css/select2.min.css">

{literal}
<style>
    .modal.modal-history-bookings {
        --bs-modal-width: 1000px;
    }

    /* TIMELINE */
    .vertical-timeline-element {
        position: relative;
        margin: 0;
        padding: 1.5rem 0 1rem;
    }

    .vertical-timeline-element::before {
        content: '';
        position: absolute;
        top: 0;
        left: 30%;
        height: 100%;
        width: 5px;
        background: #e9ecef;
        border-radius: .25rem;
    }

    .vertical-timeline-element-content {
        position: relative;
        font-size: .8rem;
    }
    .vertical-timeline-element-content .timeline-title {
        font-size: .8rem;
        text-transform: uppercase;
        margin: 0 0 .5rem;
        padding: 2px 0 0;
        font-weight: bold;
    }

    .vertical-timeline-element-date {
        display: block;
        padding-right: 10px;
        text-align: right;
        font-size: .8rem;
        white-space: nowrap;
    }
    .vertical-timeline-element-content:after {
        content: "";
        display: table;
        clear: both;
    }

    .vertical-timeline-element-icon .badge-dot-xl {
        box-shadow: 0 0 0 5px #fff;
    }

    .badge-dot-xl {
        width: 18px;
        height: 18px;
        position: relative;
    }

    .badge-dot-xl::before {
        content: '';
        width: 10px;
        height: 10px;
        border-radius: .25rem;
        position: absolute;
        left: 50%;
        top: 50%;
        margin: -5px 0 0 -5px;
        background: #fff;
    }
</style>
<script>
    $(document).ready(function() {
        $("#date_select").change(function() {
            $("#from_date").val($(this).find("option:selected").attr("fromdate"));
            $("#to_date").val($(this).find("option:selected").attr("todate"));

            // $(".container-waiting").show();
            // $("#frmSearch").submit();
        });

        $('#checkall').change(function () {
            $('input:checkbox[name="contact_id[]"]').prop('checked', this.checked);
        });

        $('#employee-select').select2();

        $('.assign-contact').on('click', function () {
            $(this).hide(); // Ẩn nút assign-contact
            $('#dialog-assign-contact').show(); // Hiển thị dialog-assign-contact
        });

        $('#dialog-assign-contact').on('click', '.hide-assign-contact', function () {
            $('#dialog-assign-contact').hide(); 
            $('.assign-contact').show(); 
        });

        $('.view-detail-contact').on('click', function() {
            let contact_id = $(this).data('contact-id');

            if (contact_id.length > 0) {
                $.ajax({
                    cache: false,
                    type: 'post',
                    data: {
                        contact_id: contact_id,
                        purpose: "get_all_status",
                        for: "showHistoryBookingContact"
                    },
                    async: false,
                    url: 'index.php?entryPoint=entryPointFlightBookings',
                    beforeSend: function () {
                        $('.container-waiting').show();
                    },
                    success: function (output) {
                        $('.container-waiting').hide();
                        $('#dialog-history-bookings').html(output);
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        console.error(XMLHttpRequest);
                        console.error("Status: " + textStatus);
                        console.error("Error: " + errorThrown);
                    }
                });
            } else {
                $('#dialog-history-bookings').html(`
                    <tr>
                        <td colspan="8">Thông tin khách hàng không xác định</td>
                    </tr>
                `);
            }
        });

        $('.view-activity-contact').on('click', function() {
            let phone = $(this).data('phone');

            if (phone.length > 0) {
                $.ajax({
                    url: "index.php?entryPoint=entryPointCallContact",
                    data: {
                        type: "get_history_activity_contacts",
                        phone: phone,
                    },
                    type: 'POST',
                    cache: false,
                    success: function (output) {
                        $('#dialog-history-activity').html(output);
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        console.error(XMLHttpRequest);
                        console.error("Status: " + textStatus);
                        console.error("Error: " + errorThrown);
                    }
                });
            } 
        });
    });
</script>
{/literal}

<div class="telesales-report">
    <h1 class="title my-3">Tele Sales</h1>
</div>

<div class="box-section">
    <form action="index.php" method="post" name="frmSearch" id="frmSearch" class="mb-3">
        <input type="hidden" name="module" value="Contacts">
        <input type="hidden" name="action" value="telesales">

        {if $IS_ADMIN}
            <div class="from-to-date--wrap flex-start mb-3">
                <select class="box-select" id="date_select" name="date_select">{$DATE_OPTION}</select>
                
                <div class="d-flex gap-2 align-items-center fdate_trigger--wrap">
                    <span class="sublabel">Từ ngày: </span>    
                    <div class="dateTime d-flex gap-2 position-relative">
                        <input class="date_input box-input" type="text" maxlength="10" size="11" title="" value="{$FROM_DATE}" id="from_date" name="from_date" autocomplete="off">
                        <button class="icon_dateTime" type="button" id="fdate_trigger" onclick="return false;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
                                <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
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
                    <g clip-path="url(#icon_arrow_flight_long_svg__clip0)" stroke="#718096" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M33.5 8.5L36 11M4 11h32"></path></g>
                    <defs>
                        <clipPath id="icon_arrow_flight_long_svg__clip0">
                        <path fill="#fff" d="M0 0h40v20H0z"></path>
                        </clipPath>
                    </defs>
                </svg>

                <div class="d-flex gap-2 align-items-center tdate_trigger--wrap">
                    <span class="sublabel">Đến ngày: </span>    
                    <div class="dateTime d-flex gap-2 position-relative">
                        <input class="date_input box-input" type="text" maxlength="10" size="11" title="" value="{$TO_DATE}" id="to_date" name="to_date" autocomplete="off">
                        <button class="icon_dateTime" type="button" id="tdate_trigger" onclick="return false;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
                            <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
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

            <div class="group-button__checkbox flex__wrap mb-3">
                <input type="checkbox" name="uncompleted_booking" id="uncompleted_booking" {$checked_uncompleted_bk}> 
                <label for="uncompleted_booking">Booking chưa hoàn tất</label>
            </div>

            <div class="action--wrap flex-start mb-3">
                <input type="submit" id="btnView" name="btnView" class="btn btn-primary" value="Lọc danh sách" title="Lọc danh sách" />
                <button type="button" class="btn btn-success fw-medium assign-contact">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-person-add" viewBox="0 0 16 16">
                        <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 0 1-1 0v-1h-1a.5.5 0 0 1 0-1h1v-1a.5.5 0 0 1 1 0m-2-6a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/>
                        <path d="M8.256 14a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
                    </svg>
                    <span>Giao cho nhân viên</span>
                </button>
                <div id="dialog-assign-contact" class="flex-start text-nowrap" style="display: none;">
                    {$EMLOYEE_SELECT}
                    <div class="action-assign flex-start">
                        <button type="button" class="btn btn-secondary hide-assign-contact">Đóng</button>
                        <button type="submit" class="btn btn-primary" name="btnSaveAssignContact" id="btnSaveAssignContact">Giao</button>
                    </div>
                </div>
            </div>
        {/if}
        
        <div id="telesales_tbl" style="max-height: 70vh; overflow: auto;">
            <table class="table-telesales table__sticky table-details__booking text-nowrap" cellpadding="0" cellspacing="0" border="0">
                <thead>
                    <tr>
                        <th width="3%"><input type="checkbox" id="checkall" value="0"></th>
                        <th width="5%">STT</th>
                        <th width="10%">Họ và tên</th>
                        <th width="10%">Điện thoại</th>
                        <th>Ghi chú</th>
                        <th width="12%">Giao cho</th>
                        <!-- <th width="12%">Ngày tạo</th> -->
                        <th width="10%">Xem thêm</th>
                    </tr>
                </thead>
                <tbody>
                    {$LIST_CONTACTS}
                </tbody>
            </table>
        </div>
    </form>

    <!-- MODAL HISTORY BOOKINGS -->
    <div class="modal-history-bookings detail-view-field modal fade" id="modalViewDetail" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalViewDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5 text-white" id="modalViewDetailLabel">Lịch sử booking của khách hàng</h1>
                    <button type="button" class="btn-close me-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="dialog-history-bookings"></div>
                </div>
                <div class="modal-footer flex-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL HISTORY ACTIVITY -->
    <div class="modal-history-activity detail-view-field modal fade" id="modalViewHistoryActivity" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalViewHistoryActivityLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5 text-white" id="modalViewHistoryActivityLabel">Tương tác khách hàng</h1>
                    <button type="button" class="btn-close me-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="dialog-history-activity" class="max-vh-70 overflow-x-hidden overflow-y-auto"></div>
                </div>
                <div class="modal-footer flex-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
</div>