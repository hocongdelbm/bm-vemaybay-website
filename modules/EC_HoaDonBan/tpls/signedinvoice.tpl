{literal}
    <script>
          $(document).ready(function() {
               Calendar.setup({
                    inputField: "from_date",
                    daFormat: "%d-%m-%Y",
                    button: "fdate_trigger",
                    singleClick: true,
                    dateStr: "",
                    step: 1,
                    position: [230, 202],
               });
               Calendar.setup({
                    inputField: "to_date",
                    daFormat: "%d-%m-%Y",
                    button: "tdate_trigger",
                    singleClick: true,
                    dateStr: "",
                    step: 2
               });

               $('#checkall').change(function () {
                    let is_check_all = $(this).is(':checked');
                    if (is_check_all) {
                         $('input:checkbox[name="sochungtu_id[]"]').attr('checked', true);
                    } else {
                         $('input:checkbox[name="sochungtu_id[]"]').attr('checked', false);
                    }
               });

               $('#btnView').on('click', function () {
                    let from_date   = $('#from_date').val();
                    let to_date     = $('#to_date').val();

                    if (from_date.length == 0) {
                         let text_warning = 'Vui lòng chọn từ ngày.';
                         showToastWarning(text_warning);
                         $('#from_date').focus();
                         return false;
                    }
                    if (to_date.length == 0) {
                         let text_warning = 'Vui lòng chọn đến ngày.';
                         showToastWarning(text_warning);
                         $('#to_date').focus();
                         return false;
                    }
                    $('#frmSearch').attr('target', '_self');
                    $('#frmSearch').attr('action', 'index.php');
                    $('#frmSearch input:hidden[name="print"]').remove();
               });

               $('#btnExportInvoice').on('click', function () {
                    let from_date = $('#from_date').val();
                    let to_date = $('#to_date').val();
                    if (from_date.length == 0) {
                         let text_warning = 'Vui lòng chọn từ ngày.';
                         showToastWarning(text_warning);
                         $('#from_date').focus();
                         return false;
                    }
                    if (to_date.length == 0) {
                         let text_warning = 'Vui lòng chọn đến ngày.';
                         showToastWarning(text_warning);
                         $('#to_date').focus();
                         return false;
                    }
                    
                    if ($('input:checkbox[name="sochungtu_id[]"]:checked').length == 0) {
                         let text_warning = 'Chọn hóa đơn cần xuất.';
                         showToastWarning(text_warning);
                         return false;
                    } 
               });
          });
    </script>
{/literal}

{php}
    if(isset($_POST['btnExportInvoice'])) {
{/php}
<form action="index.php" method="post" class="overflow-auto">
     <input type="hidden" name="module" value="EC_HoaDonBan" />
     <input type="hidden" name="action" value="signedinvoice" />
     <input type="hidden" name="sochungtu_id[]" value="{$SOCHUNGTU_ID}"/>
</form>
{php}
    } else {
{/php}
<h1 class="report_title title">Danh sách hóa đơn ghi sổ</h1>

<div class="box-section">
     <div id="signed_invoice">
          <form action="index.php" method="post" name="frmSearch" id="frmSearch" class="mb-3">
               <input type="hidden" name="module" value="EC_HoaDonBan" />
               <input type="hidden" name="action" value="signedinvoice" />

               <div class="from-to-date--wrap d-inline-flex gap-2 align-items-center mb-3">
                    <div class="d-flex gap-2 align-items-center fdate_trigger--wrap">
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

                    <div class="d-flex gap-2 align-items-center tdate_trigger--wrap">
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

                    <input type="submit" id="btnView" name="btnView" class="btn btn-primary" value="Xem" title="Xem"/>
                    <input type="submit" id="btnExportInvoice" name="btnExportInvoice" class="btn btn-success" value="Xuất hóa đơn" title="Xuất hóa đơn" />
               </div>

               <table id="signed_invoice_tbl" class="table-signed__invoice table-details__sticky table-details__booking" cellpadding="0" cellspacing="0" border="0">
                    <thead>
                         <th width="3%"><input type="checkbox" id="checkall" value="0"/></th>
                         <th width="8%">Ngày hóa đơn</th>
                         <!-- <th width="8%">Ngày ký</th>  -->
                         <th width="10%">Số chứng từ</th>
                         <th width="8%">Số hóa đơn</th> 
                         <th width="20%">Tên khách hàng / Cty</th>
                         <th width="25%">Địa chỉ</th>
                         <th width="10%">Mã số thuế</th>
                         <th width="10%">Thành tiền</th>
                         <th>Đơn vị</th>
                    </thead>
                    <tbody>
                         {$SIGNED_INVOICE}
                    </tbody>
               </table>
          </form>
     </div>
</div>
{php}
    }
{/php}