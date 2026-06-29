<link rel="stylesheet" href="./themes/SuiteP/libs/css/select2.min.css">
<link rel="stylesheet" href="./modules/EC_Flight_Bookings/css/checkflydate.css?v={$VERSION}">

{literal}
    <script>
        $(document).ready(function() {
            $("#airlines, #user_id").select2({
                width: '100%',
                minimumResultsForSearch: 5
            });

            // Quick date range selector
            $("#quick_date_range").change(function() {
                var fromDate = $(this).children("option:selected").attr("from_date");
                var toDate = $(this).children("option:selected").attr("to_date");

                if(fromDate && fromDate !== "") {
                    $("#tungay").val(fromDate);
                }
                if(toDate && toDate !== "") {
                    $("#denngay").val(toDate);
                }
            });

            // Reset filters
            $("#btnReset").click(function(e) {
                e.preventDefault();

                // Reset date fields to today
                var today = new Date();
                var todayStr = String(today.getDate()).padStart(2, '0') + '-' +
                               String(today.getMonth() + 1).padStart(2, '0') + '-' +
                               today.getFullYear();

                // Clear all filter fields
                $("#quick_date_range").val("").trigger("change");
                $("#tungay").val(todayStr);
                $("#denngay").val(todayStr);
                $("#airlines").val("").trigger("change");
                $("#user_id").val("").trigger("change");
                $("#search_phone").val("");
                $("#search_passenger").val("");

                // Submit form to refresh with default filters
                $("#ec_search_form").submit();
            });
        });
    </script>
{/literal}

<h1 class="title">Kiểm tra ngày bay</h1>

<!-- Filters Section - Separated & Responsive -->
<div class="checkfly-filters">
    <form id="ec_search_form" name="ec_search_form" method="post" action="index.php">
        <input type="hidden" name="module" value="EC_Flight_Bookings" />
        <input type="hidden" name="action" value="checkflydate" />

        <!-- All Filters in One Row -->
        <div class="checkfly-grid">
            <!-- Quick Selector -->
            <div class="filter-group quick-date-section">
                <label for="quick_date_range">Chọn nhanh</label>
                <select id="quick_date_range" class="box-select">
                    <option from_date="" to_date="">--Khoảng--</option>
                    <option from_date="{$TODAY}" to_date="{$TODAY}">Hôm nay</option>
                    <option from_date="{$YESTERDAY}" to_date="{$YESTERDAY}">Hôm qua</option>
                    <option from_date="{$THISWEEK_FROMDATE}" to_date="{$THISWEEK_TODATE}">Tuần này</option>
                    <option from_date="{$LAST7_FROMDATE}" to_date="{$LAST7_TODATE}">7 ngày</option>
                    <option from_date="{$THISMONTH_FROMDATE}" to_date="{$THISMONTH_TODATE}">Tháng này</option>
                    <option from_date="{$PREVMONTH_FROMDATE}" to_date="{$PREVMONTH_TODATE}">Tháng trước</option>
                </select>
            </div>

            <!-- Từ ngày -->
            <div class="filter-group">
                <label for="tungay">Từ ngày</label>
                <div class="dateTime d-flex gap-2 position-relative">
                   <input class="date_input box-input" type="text" maxlength="10" size="11" title="" value="{$POST_TUNGAY}" id="tungay" name="tungay" autocomplete="off">
                    <button class="icon_dateTime" type="button" id="tungay_trigger" onclick="return false;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
                            <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
                        </svg>
                    </button>
                    {literal}<script type="text/javascript">Calendar.setup({inputField: "tungay", daFormat: "%d-%m-%Y", button: "tungay_trigger", singleClick: true, step: 1});</script>{/literal}
                </div>
            </div>

            <!-- Đến ngày -->
            <div class="filter-group">
                <label for="denngay">Đến ngày</label>
                <div class="dateTime d-flex gap-2 position-relative">
                   <input class="date_input box-input" type="text" maxlength="10" size="11" title="" value="{$POST_DENNGAY}" id="denngay" name="denngay" autocomplete="off">
                    <button class="icon_dateTime" type="button" id="denngay_trigger" onclick="return false;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
                            <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
                        </svg>
                    </button>
                    {literal}<script type="text/javascript">Calendar.setup({inputField: "denngay", daFormat: "%d-%m-%Y", button: "denngay_trigger", singleClick: true, step: 2});</script>{/literal}
                </div>
            </div>

            <!-- Hãng bay -->
            <div class="filter-group">
                <label for="airlines">Hãng</label>
                <select id="airlines" class="box-select" name="airlines">{$AIRLINES}</select>
            </div>

            <!-- Người xử lý -->
            <div class="filter-group">
                <label for="user_id">User</label>
                <select id="user_id" class="box-select" name="user_id"><option value="">-không-</option>{$USER_LIST}</select>
            </div>

            <!-- Số điện thoại -->
            <div class="filter-group">
                <label for="search_phone">Điện thoại</label>
                <input type="text" class="box-input" id="search_phone" name="search_phone" placeholder="0123456789" value="{$SEARCH_PHONE}" maxlength="20">
            </div>

            <!-- Tên khách -->
            <div class="filter-group">
                <label for="search_passenger">Tên khách</label>
                <input type="text" class="box-input" id="search_passenger" name="search_passenger" placeholder="Nguyễn Văn A" value="{$SEARCH_PASSENGER}" maxlength="100">
            </div>
            
            <!-- Action Buttons -->
            <div class="filter-actions">
                <input type="submit" id="btnSearch" name="btnSearch" class="btn btn-primary btn-search" value="Tìm kiếm">
                <input type="button" id="btnReset" name="btnReset" class="btn btn-secondary btn-reset" value="Reset">
            </div>
        </div>

    </form>
</div>

<!-- Results Section -->
<div class="box-section position-relative">
    <table class="table-details__booking" cellpadding="0" cellspacing="0" border="0">
        <thead>
            <tr>
                <th width="2%" class="hide-mobile">STT</th>
                <th width="7%">Booking</th>
                <th width="10%">Liên hệ</th>
                <th width="10%">Checkin</th>
                <th width="7%">Điện thoại</th>
                <th width="7%" class="hide-mobile">Hãng</th>
                <th width="7%" class="hide-mobile">Mã chuyến</th>
                <th width="7%" class="hide-mobile">Hành trình</th>
                <th width="11%">Ngày giờ bay</th>
                <th width="6%" class="hide-mobile">Hạng vé</th>
                <th width="6%" class="hide-mobile">Giá cơ bản</th>
                <th width="3%" class="hide-mobile">SL</th>
                <th width="6%" class="hide-mobile">Ngày xuất</th>
                <th width="6%" class="hide-mobile">TG hoàn tất</th>
            </tr>
        </thead>
        <tbody>
            {$DATA}
        </tbody>
    </table>
</div>

<!-- Notes Modal -->
<div class="modal fade" id="notesModal" tabindex="-1" aria-labelledby="notesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notesModalLabel">Ghi chú hành trình</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <textarea id="notesText" class="form-control" rows="5" placeholder="Nhập ghi chú..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="btnSaveNotes">Lưu</button>
            </div>
        </div>
    </div>
</div>

{literal}
<script>
$(document).ready(function() {
    var currentItineraryId = null;
    const notesModal = new bootstrap.Modal(document.getElementById('notesModal'));
    const textarea = $('#notesText');

    $(document).on('click', '.btn-notes', function(e) {
        e.preventDefault();
        currentItineraryId = $(this).data('itinerary-id');
        const notes = $('tr[data-itinerary-id="' + currentItineraryId + '"]').data('notes') || '';
        textarea.val(notes);
        notesModal.show();
    });

    $('#btnSaveNotes').on('click', function() {
        const notes = textarea.val();
        $.ajax({
            type: 'POST',
            url: 'index.php?entryPoint=entryPointFlightBookings',
            data: { for: 'saveItineraryNotes', itinerary_id: currentItineraryId, notes: notes },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('tr[data-itinerary-id="' + currentItineraryId + '"]').data('notes', notes);
                    const noteDisplay = $('.note-display[data-itinerary-id="' + currentItineraryId + '"]');
                    noteDisplay.text(notes).removeClass('note-empty');
                    notesModal.hide();
                } else {
                    alert('Lỗi: ' + response.message);
                }
            },
            error: function() {
                alert('Lỗi khi lưu ghi chú!');
            }
        });
    });
});
</script>
{/literal}
