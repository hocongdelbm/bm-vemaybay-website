{literal}
    <style>

        #invoice_report h4 { 
            padding: 1px;
            margin: 0;
            text-align: center;
            font-size: 10pt;
        }   

        #invoice_report #invoice_table tbody tr:hover {
            background-color: #cfeafe;
        }

        #invoice_report #invoice_table th {
            position: sticky;
        }

        #invoice_report #invoice_table thead tr:first-child th {
            top: 75px;
        }

        #invoice_report #invoice_table thead tr:not(first-child) th {
            top: 110px;
        }

        .pagination {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .pagination a {
            padding: 8px 12px;
            text-decoration: none;
            transition: background-color .3s;
            min-width: 40px;
            text-align: center;
            border-radius: 0.375rem;
        }

        .pagination a.active {
            background-color: var(--primary-color);
            color: white;
        }

        .pagination a:hover:not(.active) {
            background-color: #ddd;
        }

    </style>
    <script>
        $(document).ready(function() {
            addToValidate('search_form', 'from_date', 'date', false, 'Ngày phải nhập theo cú pháp: 28-02-2022');
            addToValidate('search_form', 'to_date', 'date', false, 'Ngày phải nhập theo cú pháp: 28-02-2022');
            $("#search_form").submit(function() {
                if (!check_form('search_form')) {
                    return false;
                }
            });

            // PHÂN TRANG
            var rowsPerPage = 15;
            var table = $('#invoice_table');
            var tableRows = table.find('tbody tr');
            var totalPages = Math.ceil(tableRows.length / rowsPerPage);
            var currentPage = 1;

            function showPage(page) {
                tableRows.hide();
                var startIndex = (page - 1) * rowsPerPage;
                var endIndex = startIndex + rowsPerPage - 1;
                tableRows.slice(startIndex, endIndex + 1).show();
            }

            function updatePagination() {
                var pagination = $('.pagination');
                pagination.empty();

                var maxPagesToShow = 10; // Số trang tối đa hiển thị

                var startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
                var endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

                if (currentPage > 1) {
                    pagination.append('<a href="#" class="first">First</a>');
                    pagination.append('<a href="#" class="prev">Previous</a>');
                }

                for (var i = startPage; i <= endPage; i++) {
                    var link = $('<a href="#">' + i + '</a>');
                    link.click(function () {
                        var pageNumber = parseInt($(this).text());
                        currentPage = pageNumber;
                        showPage(pageNumber);
                        updatePagination();
                    });

                    if (i === currentPage) {
                        link.addClass('active');
                    }

                    pagination.append(link);
                }

                if (currentPage < totalPages) {
                    pagination.append('<a href="#" class="next">Next</a>');
                    pagination.append('<a href="#" class="last">Last</a>');
                }
            }

            showPage(currentPage);
            updatePagination();

            // Xử lý khi bấm nút "Next"
            $('.pagination').on('click', '.next', function () {
                if (currentPage < totalPages) {
                    currentPage++;
                    showPage(currentPage);
                    updatePagination();
                }
            });

            // Xử lý khi bấm nút "Previous"
            $('.pagination').on('click', '.prev', function () {
                if (currentPage > 1) {
                    currentPage--;
                    showPage(currentPage);
                    updatePagination();
                }
            });

            // Xử lý khi bấm nút "First"
            $('.pagination').on('click', '.first', function () {
                currentPage = 1;
                showPage(currentPage);
                updatePagination();
            });

            // Xử lý khi bấm nút "Last"
            $('.pagination').on('click', '.last', function () {
                currentPage = totalPages;
                showPage(currentPage);
                updatePagination();
            });
        });
    </script>
{/literal}
<div class="box-section">
<div id="invoice_report" class="bill-of-sale">
    <form name="search_form" id="search_form" method="post" action="index.php">
        <input type="hidden" name="module" value="EC_HoaDonBan">
        <input type="hidden" name="action" value="invoicereport">

        <div class="from-to-date--wrap d-inline-flex gap-2 mb-3 align-items-center">
            <div class="d-flex gap-2 align-items-center fdate_trigger--wrap">
                <span class="sublabel">Từ ngày: </span>    
                <div class="dateTime d-flex gap-2 position-relative">
                    <input class="date_input w-100 box-input" type="text" maxlength="10" size="11" title="" value="{$FROM_DATE}" id="from_date" name="from_date" autocomplete="off">
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

            <div class="d-flex gap-2 align-items-center tdate_trigger--wrap">
                <span class="sublabel">Đến ngày: </span>    
                <div class="dateTime d-flex gap-2 position-relative">
                   <input  class="date_input w-100 box-input" type="text" maxlength="10" size="11" title="" value="{$TO_DATE}" id="to_date" name="to_date" autocomplete="off">
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

            <div class="supplier-wrap d-flex align-items-center gap-1">
                <span class="sublabel">Nhà cung cấp:</span>
                <select class="box-select" name="supplier">{$SUPPLIER_OPT}</select>
            </div>
            <div class="quantity-ticket--wrap d-flex align-items-center gap-1">
                <span class="sublabel">Số vé:</span>
                <input type="text" class="box-input" name="ticket_number" id="ticket_number" value="{$TICKET_NUMBER}">
            </div>
            <div class="submit-button-wrap d-flex gap-2">
                <input type="submit" class="btn btn-primary" value="Tìm kiếm">
                <input type="submit" class="btn btn-danger" name="clear" value="Xoá">
            </div>
        </div>
    </form>
    <h2>Tồn kho tổng hợp</h2>
    <h4><i>Nhà cung cấp: {$SUPPLIER}</i></h4>
    <h4><i>Từ ngày: {$FROM_DATE} - Đến ngày: {$TO_DATE}</i></h4>
    <br>
    <div class="pagination"></div>
    <table id="invoice_table" class="table-details__booking table-invoice-report" cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <th width="5%" rowspan="2">STT</th>
                <th width="12%" rowspan="2">Ngày hoá đơn</th>
                <th width="15%" rowspan="2">Số vé</th>
                <th width="17%" colspan="2">Đầu kỳ</th>
                <th width="17%" colspan="2">Mua vào</th>
                <th width="17%" colspan="2">Bán ra</th>
                <th width="17%" colspan="2">Cuối kỳ</th>
            </tr>
            <tr>
                <th width="5%">SL</th>
                <th width="12%">Giá trị</th>
                <th width="5%">SL</th>
                <th width="12%">Giá trị</th>
                <th width="5%">SL</th>
                <th width="12%">Giá trị</th>
                <th width="5%">SL</th>
                <th width="12%">Giá trị</th>
            </tr>
        </thead>
        <tbody>
            {$INV_REPORT_TBL}
        </tbody>
    </table>
</div>
</div>