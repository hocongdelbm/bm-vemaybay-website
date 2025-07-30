<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.3/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.1.0"></script>
<link type="text/css" rel="stylesheet" href="./themes/SuiteP/libs/css/select2.min.css">

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

    .modal.modal-history-activity.modal-cskh,
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
        $("#year_select").change(function() {
            $("#from_date").val($(this).find("option:selected").attr("fromdate"));
            $("#to_date").val($(this).find("option:selected").attr("todate"));

            $(".container-waiting").show();
            $("#ec_search_form").submit();
        });

        $('#checkall').change(function () {
            $('input:checkbox[name="contact_id[]"]').prop('checked', this.checked);
        });

        $('#employee-select-list').select2();

        $('.assign-contact-list').on('click', function () {
            $(this).hide(); // Ẩn nút assign-contact
            $('#dialog-assign-contact').show(); // Hiển thị dialog-assign-contact
        });
        $('#dialog-assign-contact').on('click', '.hide-assign-contact', function () {
            $('#dialog-assign-contact').hide(); 
            $('.assign-contact-list').show(); 
        });

        $('.view-detail-contact').on('click', function() {
            let contact_id = $(this).data('contact-id');

            if (contact_id.length > 0) {
                $.ajax({
                    cache: false,
                    type: 'post',
                    data: {
                        contact_id: contact_id,
                        purpose: "get_completed_status",
                        for: "showHistoryBookingContact"
                    },
                    async: false,
                    url: 'index.php?entryPoint=entryPointFlightBookings',
                    beforeSend: function () {
                        $('#dialog-history-bookings').html('');
                    },
                    success: function (output) {
                        $('#dialog-history-bookings').html(output);
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        console.error(XMLHttpRequest);
                        console.error("Status: " + textStatus);
                        console.error("Error: " + errorThrown);
                    }
                });
            } else {
                $('#dialog-history-bookings').html(`Thông tin khách hàng không xác định`);
            }
        });

        $('.view-activity-contact').on('click', function() {
            let phone   = $(this).data('phone');
            let type    = $(this).data('type');

            if (phone.trim().length > 0 && type.trim().length > 0) {
                let title = (type === 'get_history_activity_contacts') ? 'Tương tác khách hàng' : 'Cuộc gọi CSKH';
                let modal_class = (type === 'get_history_activity_contacts') ? 'modal-activity' : 'modal-cskh';

                $.ajax({
                    url: "index.php?entryPoint=entryPointCallContact",
                    data: {
                        type: type,
                        phone: phone,
                    },
                    type: 'POST',
                    cache: false,
                    dataType: 'html',
                    beforeSend: function () {
                        $('#modalViewHistoryActivityLabel').html(title);
                        $('#modalViewHistoryActivity').removeClass('modal-activity modal-cskh').addClass(modal_class);
                        $('#dialog-history-activity').html('');
                    },
                    success: function (output) {
                        $('#dialog-history-activity').html(output);
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        console.error(XMLHttpRequest);
                        console.error("Status: " + textStatus);
                        console.error("Error: " + errorThrown);
                    }
                });
            } else {
                $('#dialog-history-activity').html(`Thông tin khách hàng / Phone không xác định`);
            }
        });

        $('.assign-contact').on('click', function() {
            let contact_id = $(this).data('contact_id');
            let contact_name = $(this).data('contact_name');

            if (contact_id.length > 0) {
               $("#frmAssignContactsEmp #contact_id").val(contact_id);
               $("#frmAssignContactsEmp #contact_name").val(contact_name);
            } else {
                showToastNotify('danger', 'Nhân viên được phân giao không xác định!')
                return false;
            }
        });
        
    });
</script>
{/literal}

<div class="emp-Contacts-report">
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
        <span>Loại khách hàng</span>
    </div>
    <div class="report-header-area my-3">
        <h2 class="report-title">Khách hàng {$TEXT_TYPE_CUSTOMER} <strong>(Tổng Khách hàng: {$TOTAL_CUSTOMER})</strong></h2>
        <form id="ec_search_form" method="post" action="index.php">
            <input type="hidden" name="module" value="{$MODULE_NAME}">
            <input type="hidden" name="action" value="typereports">
            <input type="hidden" name="type_customer" value="{$TYPE_CUSTOMER}">
            <input type="hidden" name="from_date" id="from_date" value="{$FROM_DATE}">
            <input type="hidden" name="to_date" id="to_date" value="{$TO_DATE}">
            <select class="box-select" id="year_select" name="year_select">{$YEAR_SELECT}</select>
        </form>
    </div>
    <div class="report-main-area box-section">
        <form method="post" action="index.php" name="frmAssignContacts" id="frmAssignContacts">
            <input type="hidden" name="module" id="module" value="{$MODULE_NAME}">
            <input type="hidden" name="action" id="action" value="typereports">
            <input type="hidden" name="type_customer" value="{$TYPE_CUSTOMER}">
            <input type="hidden" name="from_date" id="from_date" value="{$FROM_DATE}">
            <input type="hidden" name="to_date" id="to_date" value="{$TO_DATE}">

            {if $IS_ADMIN}
                <button type="button" class="btn btn-success fw-medium assign-contact-list">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-person-add" viewBox="0 0 16 16">
                        <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 0 1-1 0v-1h-1a.5.5 0 0 1 0-1h1v-1a.5.5 0 0 1 1 0m-2-6a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/>
                        <path d="M8.256 14a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
                    </svg>
                    <span>Giao cho nhân viên</span>
                </button>
                <div id="dialog-assign-contact" class="flex-start text-nowrap" style="display: none;">
                    <div class="flex-start">
                        <label for="employee-select-list" class="form-label m-0">Nhân viên: </label>
                        <select class="box-select" id="employee-select-list" name="employee_id[]" multiple>
                            {$EMLOYEE_OPTION}
                        </select>
                    </div>
                    <div class="action-assign flex-start">
                        <button type="button" class="btn btn-secondary hide-assign-contact">Đóng</button>
                        <button type="submit" class="btn btn-primary" name="btnSaveAssignContact" id="btnSaveAssignContact">Giao</button>
                    </div>
                </div>
            {/if}

            {$HTML_LIST_CUSTOMER}
        </form>
    </div>

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
                    <h1 class="modal-title fs-5 text-white" id="modalViewHistoryActivityLabel"></h1>
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

    <!-- MODAL ASSIGN USER -->
    <form method="post" action="index.php" name="frmAssignContactsEmp" id="frmAssignContactsEmp">
        <input type="hidden" name="module" id="module" value="{$MODULE_NAME}">
        <input type="hidden" name="action" id="action" value="typereports">
        <input type="hidden" name="type_customer" value="{$TYPE_CUSTOMER}">
        <input type="hidden" name="contact_id" id="contact_id" value="">
        <input type="hidden" name="contact_name" id="contact_name" value="">
        <input type="hidden" name="from_date" id="from_date" value="{$FROM_DATE}">
        <input type="hidden" name="to_date" id="to_date" value="{$TO_DATE}">

        <div class="modal-assign-contact detail-view-field modal fade" id="modalAssignContacts" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalAssignContactsLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5 text-white" id="modalAssignContactsLabel">Giao cho nhân viên</h1>
                        <button type="button" class="btn-close me-2" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="dialog-assign-contact">
                            <div class="flex-start">
                                <label for="employee-select" class="form-label m-0">Nhân viên: </label>
                                <select class="box-select" id="employee-select" name="employee_id">
                                    {$EMLOYEE_OPTION}
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer flex-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary" name="btnSaveAssignContactEmp" id="btnSaveAssignContactEmp">Giao</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>