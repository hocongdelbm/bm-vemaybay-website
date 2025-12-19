<link rel="stylesheet" type="text/css" href="modules/EC_HoaDonBan/css/view.inputinvoice.css?v=1.5">

<div class="box-section">
    <div id="import_invoice">
        <ul class="nav nav-tabs admin_tabs-list input-invoice-tabs" id="input-invoice-tabs" role="tablist">
            <li class="admin_tabs-item" role="presentation">
                <a class="active" id="search-tab" data-bs-toggle="tab" data-bs-target="#search-tab-pane" type="button" role="tab" aria-controls="search-tab-pane" aria-selected="true">Tìm kiếm</a>
            </li>
            <li class="admin_tabs-item" role="presentation">
                <a class="" id="import-tab" data-bs-toggle="tab" data-bs-target="#import-tab-pane" type="button" role="tab" aria-controls="import-tab-pane" aria-selected="false">Import</a>
            </li>
            <li class="admin_tabs-item" role="presentation">
                <a class="" id="delete-invoice-tab" data-bs-toggle="tab" data-bs-target="#delete-invoice-tab-pane" type="button" role="tab" aria-controls="delete-invoice-tab-pane" aria-selected="false">Xoá hoá đơn đã nạp</a>
            </li>
            <li class="admin_tabs-item" role="presentation">
                <a class="" id="edit-invoice-tab" data-bs-toggle="tab" data-bs-target="#edit-invoice-tab-pane" type="button" role="tab" aria-controls="edit-invoice-tab-pane" aria-selected="false">Bổ sung / Chỉnh sửa số vé</a>
            </li>
        </ul>
        <div class="tab-content" id="input-invoice-tabs__content">
            <div class="tab-pane fade show active" id="search-tab-pane" role="tabpanel" aria-labelledby="search-tab" tabindex="0">
                <div class="box-tabs">
                    <form id="search_frm" name="search_frm" method="post" action="index.php">
                        <input type="hidden" name="module" value="EC_HoaDonBan">
                        <input type="hidden" name="action" value="inputinvoice">

                        <table cellpadding="0" cellspacing="0" class="table-tabs table-invoice table-search__frm">
                            <tbody>
                                <tr>
                                    <td width="12%"><span class="label">Ngày hoá đơn:</span></td>
                                    <td width="21%">
                                        <div class="d-flex align-items-center gap-2">
                                            
                                            <span class="dateTime d-flex position-relative">
                                                <input autocomplete="off" type="text" class="date_input box-input" name="from_date" id="from_date" value="{$FROM_DATE}" size="7" maxlength="10"> 
                                                <button class="icon_dateTime" type="button" id="from_date_trigger" onclick="return false;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                                                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
                                                        <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
                                                        </svg>
                                                </button>
                                            </span>
                                            
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-dash-lg" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8Z"/>
                                            </svg>

                                            <span class="dateTime d-flex position-relative">
                                                <input autocomplete="off" type="text" class="date_input box-input" name="to_date" id="to_date" value="{$TO_DATE}" size="7" maxlength="10">
                                                <button class="icon_dateTime" type="button" id="to_date_trigger" onclick="return false;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                                                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
                                                        <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
                                                        </svg>
                                                </button>
                                            </span>
                                        </div>
                                    </td>
                                    <td width="12%"><span class="label">Số vé:</span></td>
                                    <td width="21%">
                                        <input class="box-input" type="text" name="ticket_code" value="{$TICKET_CODE}" />
                                    </td>
                                    <td width="12%"><span class="label">Số hoá đơn:</span></td>
                                    <td width="21%">
                                        <input type="text" class="box-input" name="invoice_number" id="invoice_number" value="{$INVOICE_NUMBER}" />
                                    </td>
                                </tr>
                                <tr>
                                    <td width="12%"><span class="label">Ngày hạch toán:</span></td>
                                    <td width="21%">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="dateTime d-flex position-relative">
                                                <input autocomplete="off" type="text" class="date_input box-input" name="from_date_accounting" id="from_date_accounting" value="{$FROM_DATE_ACCOUNTING}" size="7" maxlength="10"> 
                                                <button class="icon_dateTime" type="button" id="from_date_accounting_trigger" onclick="return false;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                                                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
                                                        <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
                                                        </svg>
                                                </button>
                                            </span>
                                            
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-dash-lg" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8Z"/>
                                            </svg>

                                            <span class="dateTime d-flex position-relative">
                                                <input autocomplete="off" type="text" class="date_input box-input" name="to_date_accounting" id="to_date_accounting" value="{$TO_DATE_ACCOUNTING}" size="7" maxlength="10">
                                                <button class="icon_dateTime" type="button" id="to_date_accounting_trigger" onclick="return false;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                                                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
                                                        <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
                                                        </svg>
                                                </button>
                                            </span>

                                        </div>  
                                    </td>
                                    <td width="12%"><span class="label">PNR:</span></td>
                                    <td width="21%">
                                        <input type="text" class="box-input" name="ticket_c" value="{$TICKET_C}">
                                    </td>
                                    <td width="12%"><span class="label">KHHĐ:</span></td>
                                    <td width="21%">
                                        <input type="text" class="box-input" name="invoice_serial" id="invoice_serial" value="{$INVOICE_SERIAL}">
                                    </td>
                                </tr>
                                <tr>
                                    <td width="12%"><span class="label">Booking</span></td>
                                    <td width="21%">
                                        <input type="text" class="box-input" name="booking_search" id="booking_search" value="{$BOOKING_SEARCH}" />
                                    </td>

                                    <td width="12%"><span class="label">Thiếu booking:</span></td>
                                    <td width="21%">
                                        <select class="box-select" name="missing_bk">{$MISSING_BK}</select>
                                    </td>
                                    <td width="12%"><span class="label">Thiếu SL:</span></td>
                                    <td width="21%">
                                        <select class="box-select" name="missing_qty">{$MISSING_QTY}</select>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="12%"><span class="label">Tình trạng tồn:</span></td>
                                    <td width="21%">
                                        <select class="box-select" name="stock_stt">{$STOCK_STT}</select>
                                    </td>
                                    <td width="12%"><span class="label">Xuất dư:</span></td>
                                    <td width="21%">
                                        <select class="box-select" name="over_qty">{$OVER_QTY}</select>
                                    </td>  
                                    <td width="12%"><span class="label">Nhà cung cấp:</span></td>
                                    <td width="21%">
                                        <select class="box-select" name="supplier">{$SUPPLIER_OPTION}</select>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="12%"><span class="label">Đơn vị:</span></td>
                                    <td width="21%">
                                        <select class="box-select" name="company_unit">{$COMPANY_UNIT_OPTION}</select>
                                    </td>
                                    <td colspan="6"></td> 
                                </tr>

                                {if !$PREVIEW}
                                <tr>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center gap-2 mt-2">
                                            <input type="hidden" id="page_number_hid" name="page_number" value="{$CURRENT_PAGE}">
                                            <input type="submit" class="btn btn-primary" value="Tìm kiếm" id="search_btn">
                                            <input type="submit" class="btn btn-secondary" value="Xóa trắng" id="clear_btn" name="clear_search">
                                            <!-- <input type="button" class="btn btn-success" value="Xuất Excel" id="export_btn"> -->
                                        </div>
                                    </td>
                                </tr>
                                {/if}
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
            <div class="tab-pane fade" id="import-tab-pane" role="tabpanel" aria-labelledby="import-tab" tabindex="0">
                <div class="box-tabs">
                    <form id="import_invoice_frm" name="import_invoice_frm" method="post" enctype='multipart/form-data'>
                        <input type="hidden" name="module" value="EC_HoaDonBan">
                        <input type="hidden" name="action" value="inputinvoice">
                        <input type="hidden" name="importfile">

                        <table cellpadding="0" cellspacing="0" class="table-tabs table-invoice table-import">
                            <tbody>
                                <tr>
                                    <td width="13%"><span class="label">Nhà cung cấp</span></td>
                                    <td width="20%">
                                        <select class="box-select" name="supplier" id="supplier">
                                            <option value="HNH"
                                                data-col-ticket-code="D"
                                                data-col-pass-qty=""
                                                data-col-ticket-price="F"
                                                data-col-vat="H"
                                                data-col-authorized-collection="L"
                                                data-col-other-charge="P"
                                                data-col-total="S"
                                                data-col-itinerary="C">
                                                Hồng Ngọc Hà
                                            </option>
                                            <option value="PNA"
                                                data-col-ticket-code="B"
                                                data-col-pass-qty="E"
                                                data-col-ticket-price="G"
                                                data-col-vat="I"
                                                data-col-authorized-collection="J"
                                                data-col-other-charge="K"
                                                data-col-total="Q"
                                                data-col-itinerary="D">
                                                Phương Nam
                                            </option>
                                            <option value="VJA"
                                                data-col-ticket-code="B"
                                                data-col-pass-qty="F"
                                                data-col-ticket-price="L"
                                                data-col-vat="M"
                                                data-col-authorized-collection="N"
                                                data-col-other-charge=""
                                                data-col-total="O"
                                                data-col-itinerary="G">
                                                VietjetAir
                                            </option>
                                        </select>
                                        <input type="hidden" name="supplier_name" id="supplier_name" value="Hồng Ngọc Hà">
                                    </td>
                                    <td><span class="label">File</span></td>
                                    <td><input type="file" name="from_file"></td>
                                    <td class="column-name">
                                        <label class="label" for="col_ticket_code">Cột số vé</label>
                                        <input type="text" name="col_ticket_code" id="col_ticket_code" class="box-input" size="1" maxlength="1" />
                                    </td>
                                    <td class="column-name">
                                        <label class="label" for="col_pass_qty">Cột số lượng</label>
                                        <input type="text" name="col_pass_qty" id="col_pass_qty" class="box-input" size="1" maxlength="1" />
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="label">Số hoá đơn</span><span class="required">*</span></td>
                                    <td><input type="text" class="box-input" name="invoice_number"></td>
                                    <td><span class="label">Ký hiệu hoá đơn</span><span class="required">*</span></td>
                                    <td><input type="text" class="box-input" name="invoice_serial"></td>
                                    <td class="column-name">
                                        <label class="label" for="col_ticket_price">Cột giá vé</label>
                                        <input type="text" name="col_ticket_price" id="col_ticket_price" class="box-input" size="1" maxlength="1" />
                                    </td>
                                    <td class="column-name">
                                        <label class="label" for="col_vat">Cột VAT</label>
                                        <input type="text" name="col_vat" id="col_vat" class="box-input" size="1" maxlength="1" />
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="label">Ngày hạch toán</span><span class="required">*</span></td>
                                    <td><input type="text" class="box-input" name="accounting_date" id="accounting_date"></td>
                                    <td><span class="label">Ngày hoá đơn</span><span class="required">*</span></td>
                                    <td><input type="text" class="box-input" name="invoice_date" id="invoice_date" value="{$INVOICE_DATE}"></td>
                                    <td class="column-name">
                                        <label class="label" for="col_authorized_collection">Cột thu hộ</label>
                                        <input type="text" name="col_authorized_collection" id="col_authorized_collection" class="box-input" size="1" maxlength="1" />
                                    </td>
                                    <td class="column-name">
                                        <label class="label" for="col_other_charge">Cột phí khác</label>
                                        <input type="text" name="col_other_charge" id="col_other_charge" class="box-input" size="1" maxlength="1" />
                                    </td>
                                </tr>
                                <tr>
                                    <td width="13%"><span class="label">Đơn vị</span></td>
                                    <td width="20%">
                                        <select class="box-select" name="company_unit" id="company_unit">
                                            <option value="MHV">Minh Hồng Võ</option>
                                            <option value="TRAVELPASS">Travelpass</option>
                                        </select>
                                        <input type="hidden" name="company_unit_name" id="company_unit_name" value="">
                                    </td>
                                    <td colspan="2"></td>
                                    <td class="column-name">
                                        <label class="label" for="col_itinerary">Cột hành trình</label>
                                        <input type="text" name="col_itinerary" id="col_itinerary" class="box-input" size="1" maxlength="1" />
                                    </td>
                                    <td>
                                        <label class="label" for="col_total">Cột tổng cộng</label>
                                        <input type="text" name="col_total" id="col_total" class="box-input" size="1" maxlength="1" />
                                    </td>
                                </tr>
                                {if !$PREVIEW}
                                    <tr>
                                        <td colspan="6" class="text-center pb-0">
                                            <input type="submit" class="btn btn-primary" value="Import" id="import_btn">
                                        </td>
                                    </tr>
                                {/if}
                                <tr>
                                    <td colspan="6" id="noti_line" class="text-center pb-0"></td>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
            <div class="tab-pane fade" id="delete-invoice-tab-pane" role="tabpanel" aria-labelledby="delete-invoice-tab" tabindex="0">
                <div class="box-tabs">
                    <form id="remove_frm" name="remove_frm" method="post" action="index.php">
                        <input type="hidden" name="module" value="EC_HoaDonBan">
                        <input type="hidden" name="action" value="inputinvoice">

                        <table cellpadding="0" cellspacing="0" class="table-tabs table-invoice table-delete-invoice">
                            <tbody>
                                <tr>
                                    <td width="12%"><span class="label">Số hoá đơn</span>(<span class="required">*</span>)</td>
                                    <td width="21%">
                                        <input type="text" class="box-input" name="rm_invoice_number" id="rm_invoice_number" value="">
                                    </td>
                                    <td width="12%"><span class="label">KHHĐ</span>(<span class="required">*</span>)</td>
                                    <td width="21%">
                                        <input type="text" class="box-input" name="rm_invoice_serial" id="rm_invoice_serial" value="">
                                    </td>
                                    <td width="12%"><span class="label">Nhà cung cấp</span></td>
                                    <td width="21%">
                                        <select class="box-select" name="rm_supplier">{$SUPPLIER_OPTION}</select>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="center">
                                        <input type="submit" value="Xoá HĐ" class="btn btn-danger" id="remove_btn" name="remove">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
            <div class="tab-pane fade" id="edit-invoice-tab-pane" role="tabpanel" aria-labelledby="edit-invoice-tab" tabindex="0">
                <div class="box-tabs">
                    <form id="implement_invoice_frm" name="implement_invoice_frm" action="index.php" method="post">
                        <input type="hidden" name="module" value="EC_Input_Invoices">
                        <input type="hidden" name="action" value="Save"> 

                        <table cellspacing="0" cellpadding="0" class="table-tabs table-invoice table-edit-invoice">
                            <tbody>
                                <tr>
                                    <td width="13%"><span class="label">Ngày hoá đơn</span></td>
                                    <td width="20%"><input type="text" class="box-input" id="im_invoice_date" name="im_invoice_date"></td>
                                    <td width="13%"><span class="label">Chuyến bay</span></td>
                                    <td width="20%">
                                        <select class="box-select" id="im_flight_type">
                                            <option value="0">Một chiều</option>
                                            <option value="1">Khứ hồi</option>
                                        </select>
                                    </td>
                                    <td width="13%"><span class="label">Số lượng</span></td>
                                    <td width="20%"><input type="text" class="box-input decimal-only" id="im_qty" name="im_qty" oninput="calculateTicketPrice();"></td>
                                   
                                </tr>
                                <tr>
                                    <td><span class="label">Số hoá đơn</span></td>
                                    <td><input type="text" class="box-input" id="im_invoice_number" name="im_invoice_number"></td>
                                    <td><span class="label">Hành trình</span></td>
                                    <td><input type="text" class="box-input" id="im_iti" name="im_iti"></td>
                                    <td><span class="label">Giá vốn</span></td>
                                    <td><input type="text" class="box-input decimal-only" id="im_cost" name="im_cost" oninput="calculateTicketPrice(1);"></td>
                                </tr>
                                <tr>
                                    <td><span class="label">KHHĐ</span></td>
                                    <td><input type="text" class="box-input" id="im_invoice_serial" name="im_invoice_serial"></td>
                                    <td><span class="label">Số vé</span></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <input type="text" class="box-input" id="im_ticket_code" name="im_ticket_code">
                                            <input type="hidden" id="im_ticket_id" name="record">
                                            <input type="button" name="btn_ticket_code" id="btn_ticket_code" title="Chọn [Alt+T]" accesskey="T" class="btn btn-primary" value="Chọn">
                                            <input type="button" name="btn_clr_ticket_code" id="btn_clr_ticket_code" title="Xóa [Alt+C]" accesskey="C" class="btn btn-danger" value="Xóa">
                                        </div>
                                    </td>
                                    <td><span class="label">VAT</span></td>
                                    <td>
                                        <div class="input-group mb-1">
                                            <input type="text" name="im_vat" id="im_vat" class="box-input form-control" style="width:40%;" oninput="calculateTicketPrice();" />
                                            <select name="im_vat_percent" id="im_vat_percent" class="box-select form-control" onchange="calculateTicketPrice(1);">
                                                <option value="0.08" selected>8%</option>
                                                <option value="0.1">10%</option>
                                                <option value="0">0</option>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="label">Nhà cung cấp</span></td>
                                    <td><select class="box-select" id="im_supplier" name="im_supplier">{$SUPPLIER_OPTION}</select></td>
                                    <td><span class="label">Booking</span></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <input type="text" class="box-input" id="im_booking" name="im_booking">
                                            <input type="button" name="btn_booking" id="btn_booking" title="Chọn [Alt+T]" accesskey="T" class="btn btn-primary" value="Chọn">
                                            <input type="button" name="btn_clr_booking" id="btn_clr_booking" title="Xóa [Alt+C]"  accesskey="C" class="btn btn-danger" value="Xóa">
                                            <input type="hidden" id="im_booking_id" name="im_booking_id">
                                        </div>
                                    </td>
                                    <td><span class="label">Giá vốn (VAT)</span></td>
                                    <td><input type="text" class="box-input" id="im_cost_vat" name="im_cost_vat" oninput="calculateTicketPrice();"></td>
                                </tr>
                                <tr>
                                    <td><span class="label">Ngày hạch toán</span></td>
                                    <td><input class="box-input" type="text" id="im_accounting_date" name="im_accounting_date"></td>
                                    <td><span class="label">Tổng</span></td>
                                    <td><input class="box-input" type="text" class="allow_number_only" id="im_total" name="im_total" oninput="calculateTicketPrice();"></td>
                                    <td><span class="label">Thu hộ</span></td>
                                    <td><input type="text" class="box-input decimal-only" id="im_authorized" name="im_authorized" oninput="calculateTicketPrice();"></td>
                                </tr>
                                <tr>
                                    <td width="13%"><span class="label">Đơn vị</span></td>
                                    <td width="20%">
                                        <select class="box-select" id="im_company_unit" name="im_company_unit">
                                            {$COMPANY_UNIT_OPTION}
                                        </select>
                                    </td>
                                    <td><span class="label">Loại vé</span></td>
                                    <td>
                                        <select class="box-select" id="im_ticket_type" name="im_ticket_type">
                                            {$TICKET_TYPE_OPTION}
                                        </select>
                                    </td>
                                    <td>
                                        <span class="label" title="Tách riêng phí xuất vé với số vé">Phí xuất vé</span>
                                    </td>
                                    <td>
                                        <input type="text" name="im_ticketing_fee" id="im_ticketing_fee" class="box-input decimal-only mb-1" placeholder="Giá vốn" />    
                                        <div class="input-group mb-1">
                                            <input type="text" name="im_ticketing_fee_vat" id="im_ticketing_fee_vat" class="box-input form-control" placeholder="VAT" style="width:40%;" readonly />
                                            <select name="im_ticketing_fee_vat_percent" id="im_ticketing_fee_vat_percent" class="box-select form-control">
                                                <option value="0.08" selected>8%</option>
                                                <option value="0.1">10%</option>
                                                <option value="0">0</option>
                                            </select>
                                        </div>
                                        <input type="text" name="im_ticketing_fee_no_vat" id="im_ticketing_fee_no_vat"  class="box-input" placeholder="Giá vốn chưa VAT" readonly />
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-center pb-0">
                                        <input type="submit" class="btn btn-primary" id="save_btn" value="Lưu">
                                    </td>
                                </tr>
                            </tbody>
                        </table>        
                    </form>
                </div>
            </div>

            <input type="hidden" id="grp_seperator" value=",">
            <input type="hidden" id="dec_seperator" value=".">
            <input type="hidden" id="sig_digits" value="0">
        </div>

        <h1 class="title text-center">Hoá đơn đầu vào{$SUPPLIER}</h1>
        {if $PREVIEW}
            {$CONFIRM_FRM}
        {else}
            <table id="pagination_tbl">
                <tbody>
                    <tr>
                        <td class="center">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <span class="pagination_txt lpagi d-flex align-items-center gap-2 {$LDISABLED}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-double-left" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M8.354 1.646a.5.5 0 0 1 0 .708L2.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                                        <path fill-rule="evenodd" d="M12.354 1.646a.5.5 0 0 1 0 .708L6.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                                    </svg>
                                    First
                                </span>
                                <span class="pagination_txt lpagi d-flex align-items-center gap-2 {$LDISABLED}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                                    </svg> 
                                Prev
                                </span>
                                <input id="page_number" class="allow_number_only box-input" value="{$CURRENT_PAGE}" max="{$MAX_PAGE}"> / <span class="allow_number_only">{$MAX_PAGE}</span>
                                <input type="button" class="btn btn-primary" id="search_page_btn" value="GO">
                                <span class="pagination_txt rpagi d-flex align-items-center gap-2 {$RDISABLED}">
                                    Next
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                                    </svg>
                                </span>
                                <span class="pagination_txt rpagi d-flex align-items-center gap-2 {$RDISABLED}">
                                    Last
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-double-right" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M3.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L9.293 8 3.646 2.354a.5.5 0 0 1 0-.708z"/>
                                        <path fill-rule="evenodd" d="M7.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L13.293 8 7.646 2.354a.5.5 0 0 1 0-.708z"/>
                                    </svg>
                                </span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>
            <table id="data_tbl" cellspacing="0" cellpadding="0" class="table-input__invoice table-details__booking">
                <thead>
                    <tr class="line_1">
                        <th width="2%">STT</th>
                        <th width="7%">Ngày<br>hạch toán</th>
                        <th width="7%">Ngày HĐ</th>
                        <th width="7%">Số HĐ</th>
                        <th width="5%">KHHĐ</th>
                        <th width="8%">Số vé</th>
                        <!-- <th width="7%">PNR</th> -->
                        <th width="3%">SL</th>
                        <th width="3%">Xuất</th>
                        <th width="3%">Còn</th>
                        <th width="6%">Hành trình</th>
                        <th width="7%">Giá vốn</th>
                        <th width="7%">VAT</th>
                        <th width="7%">Giá vốn (VAT)</th>
                        <th width="6%">Thu hộ</th>
                        <th width="7%">Tổng</th>
                        <th>Loại</th>
                        <th width="6%">Booking</th>   
                        <th width="3%">NCC</th>
                        <th>Đơn vị</th>
                    </tr>
                    <tr class="line_2">
                        <th class="right" colspan="6"><b>Tổng</b></th>
                        <th class="center">{$TOTAL_QTY}</th>
                        <th class="center">{$TOTAL_EXPORT}</th>
                        <th class="center">{$TOTAL_LEFT}</th>
                        <th></th>
                        <th class="right">{$TOTAL_COST}</th>
                        <th class="right">{$TOTAL_VAT}</th>
                        <th class="right">{$TOTAL_COST_VAT}</th>
                        <th class="right">{$TOTAL_AUTHORIZED}</th>
                        <th class="right">{$TOTAL}</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    {$DATA}
                </tbody>
            </table>
        {/if}
    </div>
</div>

<script src="custom/jqueryui/plugins/jquery.number.min.js"></script>
<script src="custom/jqueryui/plugins/formatNumber.js"></script>
<script src="modules/EC_HoaDonBan/js/view.inputinvoice.js?v=2.0"></script>
