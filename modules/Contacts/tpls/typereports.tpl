<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.3/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.1.0"></script>

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

        $('.assign-contact').on('click', function() {
            let contact_id = $(this).data('contact_id');
            let contact_name = $(this).data('contact_name');

            if (contact_id.length > 0) {
               $("#frmAssignContacts #contact_id").val(contact_id);
               $("#frmAssignContacts #contact_name").val(contact_name);
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
        <h2 class="report-title">Khách hàng {$TEXT_TYPE_CUSTOMER} theo chu kỳ <strong>(Tổng Booking: {$TOTAL_BOOKINGS})</strong></h2>
    </div>
    <div class="report-main-area box-section">
        {$HTML_LIST_CUSTOMER}
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

    <!-- MODAL ASSIGN USER -->
    <form method="post" action="index.php" name="frmAssignContacts" id="frmAssignContacts">
        <input type="hidden" name="module" id="module" value="Contacts">
        <input type="hidden" name="action" id="action" value="typereports">
        <input type="hidden" name="typereports" value="{$TYPE_CUSTOMER}">
        <input type="hidden" name="contact_id" id="contact_id" value="">
        <input type="hidden" name="contact_name" id="contact_name" value="">

        <div class="modal-assign-contact detail-view-field modal fade" id="modalAssignContacts" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalAssignContactsLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5 text-white" id="modalAssignContactsLabel">Giao cho nhân viên</h1>
                        <button type="button" class="btn-close me-2" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="dialog-assign-contact">
                            {$EMLOYEE_SELECT}
                        </div>
                    </div>
                    <div class="modal-footer flex-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary" name="btnSaveAssignContact" id="btnSaveAssignContact">Giao</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>