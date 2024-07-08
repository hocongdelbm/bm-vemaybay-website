<script type="text/javascript" src="custom/jqueryui/plugins/mcautocomplete.js"></script>
<script type="text/javascript" src="custom/jqueryui/plugins/formatNumber.js"></script>
<script type="text/javascript" src="custom/jqueryui/plugins/fromPopupReturn.js"></script>

{literal}
<style>
	.table-bonus input{
		max-width: 120px;
	}
</style>
    <script>
		$(document).ready(function(){
			// marck checked
			$('input:checkbox[name="ct_chk_is_approved[]"]').on('change',function(){
				if($(this).is(':checked')){
					$(this).next('input:hidden[name="ct_is_approved[]"]').val(1);
				} else {
					$(this).next('input:hidden[name="ct_is_approved[]"]').val(0);
				}
			});
			
			// auto complete
			$(".ac_booking").on("keydown.autocomplete", function() {
				$(this).mcautocomplete({
					showHeader: true,
					columns: [{
						name: 'Booking',
						width: '180px',
						valueField: 'booking'},
					{
						name: 'Liên hệ',
						width: '250px',
						valueField: 'contact_name'},
					{
						name: 'Tổng',
						width: '100px',
						valueField: 'total_amount'}
					],
					source: "ac_bookings.php",
					minLength: 2,
					select: function(event, ui) {
						var ln = $(this).attr('ln');
						$('#ct_booking_id' + ln).val(ui.item.booking_id);
						$('#ct_booking' + ln).val(ui.item.booking);
						
						$('#ct_contact_name' + ln).attr('title','Xem chi tiết booking' + ui.item.booking);
						$('#ct_contact_name' + ln).attr('href','index.php?module=EC_Flight_Bookings&action=DetailView&record=' + ui.item.booking_id);
						$('#ct_contact_name' + ln).text(ui.item.contact_name);
						
						$('#ct_total_amount' + ln).text(formatNumber(ui.item.total_amount));
						$('#ct_total_purchase' + ln).text(formatNumber(ui.item.total_purchase));
						$('#ct_total_profit' + ln).text(formatNumber(ui.item.total_profit));
					}
				});
			});
			
			// auto complete user
			$(".ac_user").on("keydown.autocomplete", function() {
				var tbl = $(this).attr('tbl');
				var fld = $(this).attr('fld');
				$(this).autocomplete({
					source: "ac_all.php?tbl="+ tbl +"&fld=" + fld,
					minLength: 2,
					select: function(event, ui) {
						var obj = $.parseJSON(fld);
						for (var prop in obj) {
						  $('#' + obj[prop]).val(ui.item[prop]);
						}
					}
				});       
			});
			
			// add row
			$('#btnAddRow').click(function(){
				var ln = parseInt($('#row_count').val());
				$('#last_row').before(addRow(ln));
				ln++;
				$('#row_count').val(ln);
				$('#lbl_row_count').text(parseInt($('#lbl_row_count').text()) + 1);
			});
		});
		
		// add row
		function addRow(ln){
			var html 			= '';
			var current_user 	= $('#current_user').val();
			var current_user_id = $('#current_user_id').val();
			var readonly 		= $('#is_admin').val() == 1 ? '' : 'readonly="readonly"'; 
			var readonly_bg 	= readonly != '' ? 'background:#e2e2e2' : '';
			var disabled 		= $('#is_admin').val() == 1 ? '' : 'disabled="disabled"'; 
			
			html += '<tr id="ct_line'+ ln +'">';
			
			html += '<td align="center"><input ln="'+ ln +'" class="ac_booking box-input" type="text" name="ct_booking[]" id="ct_booking'+ ln +'" value="" maxlength="255" /> <input type="hidden" name="ct_booking_id[]" id="ct_booking_id'+ ln +'" value="" /></td>';
			
			html += '<td align="left"><a title="" href="" target="_blank" id="ct_contact_name'+ ln +'"></a></td>';
			
			html += '<td align="center"><input type="text" class="box-input" maxlength="255" name="ct_desc[]" id="ct_desc'+ ln +'" value="" /></td>';
			
			html += '<td align="right"><label id="ct_total_amount'+ ln +'"></label></td>';
			
			html += '<td align="right"><label id="ct_total_purchase'+ ln +'"></label></td>';
			
			html += '<td align="right"><label id="ct_total_profit'+ ln +'"></label></td>';
			
			html += '<td align="center">&nbsp;</td>';
			
			html += '<td align="center"><div class="d-flex align-items-center gap-1"><input class="ac_user box-input" tbl="users" fld=\'{"id":"ct_assigned_user_id'+ ln +'", "name":"ct_assigned_user'+ ln +'"}\' ln="'+ ln +'" type="text" name="ct_assigned_user[]" id="ct_assigned_user'+ ln +'" value="'+ current_user +'" maxlength="255" autocomplete="off" /><input type="hidden" name="ct_assigned_user_id[]" id="ct_assigned_user_id'+ ln +'" value="'+ current_user_id +'" /><button title="Tìm" type="button" class="button-search-in-edit" onclick="openAssignUserPopup('+ ln +')"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M10 18a7.952 7.952 0 0 0 4.897-1.688l4.396 4.396 1.414-1.414-4.396-4.396A7.952 7.952 0 0 0 18 10c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8zm0-14c3.309 0 6 2.691 6 6s-2.691 6-6 6-6-2.691-6-6 2.691-6 6-6z"></path><path d="M11.412 8.586c.379.38.588.882.588 1.414h2a3.977 3.977 0 0 0-1.174-2.828c-1.514-1.512-4.139-1.512-5.652 0l1.412 1.416c.76-.758 2.07-.756 2.826-.002z"></path></svg></button></div></td>';
			
			html += '<td align="center"><input '+ readonly +' type="text" maxlength="25" class="box-input" name="ct_bonus[]" id="ct_bonus'+ ln +'" value="0" style="text-align:right; '+ readonly_bg +'" /></td>';
			
			html += '<td align="center"><input '+ disabled +' type="checkbox" name="ct_chk_is_approved[]" id="ct_chk_is_approved'+ ln +'" /><input type="hidden" name="ct_is_approved[]" id="ct_is_approved'+ ln +'" value="0" /></td>';
			
			html += '<td align="left"><div class="d-flex align-items-center gap-1"><input class="text-start box-input ac_user" '+ readonly +' tbl="users" fld=\'{"id":"ct_approved_by_id'+ ln +'", "name":"ct_approved_by'+ ln +'"}\' '+ readonly_bg +'" ln="'+ ln +'" type="text" name="ct_approved_by[]" id="ct_approved_by'+ ln +'" value="" maxlength="255" autocomplete="off" /><input type="hidden" name="ct_approved_by_id[]" id="ct_approved_by_id'+ ln +'" value="" /><button '+ disabled +' class="button-search-in-edit" title="Tìm" type="button" onclick="openApprovedByPopup('+ ln +')"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M10 18a7.952 7.952 0 0 0 4.897-1.688l4.396 4.396 1.414-1.414-4.396-4.396A7.952 7.952 0 0 0 18 10c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8zm0-14c3.309 0 6 2.691 6 6s-2.691 6-6 6-6-2.691-6-6 2.691-6 6-6z"></path><path d="M11.412 8.586c.379.38.588.882.588 1.414h2a3.977 3.977 0 0 0-1.174-2.828c-1.514-1.512-4.139-1.512-5.652 0l1.412 1.416c.76-.758 2.07-.756 2.826-.002z"></path></svg></button></div></td>';
			
			html += '<td align="center"><input '+ readonly +' type="text" class="box-input" style="'+ readonly_bg +'" maxlength="255" name="ct_approved_note[]" id="ct_approved_note'+ ln +'" value="" /></td>';
			
			html += '<td align="center"><button title="Xóa" type="button" class="button-remove-in-edit" onclick="markRowDeleted('+ ln +')"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg></button><input type="hidden" value="0" name="ct_deleted[]" id="ct_deleted'+ ln +'" /><input type="hidden" name="ct_detail_id[]" id="ct_detail_id'+ ln +'" value="" /></td>';
			
			html += '</tr>';
			return html;
		}
		
		// mark row deleted
		function markRowDeleted(ln){
			$('#ct_deleted' + ln).val(1);
			$('#ct_line' + ln).hide();
			$('#lbl_row_count').text(parseInt($('#lbl_row_count').text()) - 1);
		}
		
		// open assigned user popup
		function openAssignUserPopup(ln){ 
			var popupRequestData = {
				"call_back_function" : "setObjectReturn",
				"form_name" : "EditView",
				"field_to_name_array" : {
					"id" : "ct_assigned_user_id" + ln,
					"name" : "ct_assigned_user" + ln
				}
			};
			open_popup('Users', 650, 600, '', true, false, popupRequestData);
		}
		
		// open approved by user popup
		function openApprovedByPopup(ln){
			var popupRequestData = {
				"call_back_function" : "setObjectReturn",
				"form_name" : "EditView",
				"field_to_name_array" : {
					"id" : "ct_approved_by_id" + ln,
					"name" : "ct_approved_by" + ln
				}
			};
			open_popup('Users', 650, 600, '', true, false, popupRequestData);
		}
		
    </script>
{/literal}

<h1 class="title d-flex align-items-center gap-1">
	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-award" viewBox="0 0 16 16">
		<path d="M9.669.864 8 0 6.331.864l-1.858.282-.842 1.68-1.337 1.32L2.6 6l-.306 1.854 1.337 1.32.842 1.68 1.858.282L8 12l1.669-.864 1.858-.282.842-1.68 1.337-1.32L13.4 6l.306-1.854-1.337-1.32-.842-1.68L9.669.864zm1.196 1.193.684 1.365 1.086 1.072L12.387 6l.248 1.506-1.086 1.072-.684 1.365-1.51.229L8 10.874l-1.355-.702-1.51-.229-.684-1.365-1.086-1.072L3.614 6l-.25-1.506 1.087-1.072.684-1.365 1.51-.229L8 1.126l1.356.702 1.509.229z"/>
		<path d="M4 11.794V16l4-1 4 1v-4.206l-2.018.306L8 13.126 6.018 12.1 4 11.794z"/>
	   </svg>
	Bonus
</h1>

<div class="box-section">
<form action="index.php" method="post" name="frmSearch" id="frmSearch">

	<input type="hidden" name="module" value="EC_Flight_Bookings" />
	<input type="hidden" name="action" value="addbonus" />
	<input type="hidden" id="grp_seperator" name="grp_seperator" value="{$GRP_SEPERATOR}" />
	<input type="hidden" id="dec_seperator" name="dec_seperator" value="{$DEC_SEPERATOR}" />
	<input type="hidden" id="sig_digits" name="sig_digits" value="{$SIG_DIGITS}" />
	<input type="hidden" id="current_user" name="current_user" value="{$CURRENT_USER}" />
	<input type="hidden" id="current_user_id" name="current_user_id" value="{$CURRENT_USER_ID}" />
	<input type="hidden" id="is_admin" name="is_admin" value="{$IS_ADMIN}" />
     
	<div class="from-to-date--wrap d-inline-flex gap-2 align-items-center mb-3">
		<div class="d-flex gap-2 align-items-center fdate_trigger--wrap">
		    <span class="text-label">Từ ngày: </span>    
		    <div class="dateTime d-flex gap-2 position-relative">
			   <input class="date_input box-input" type="text" maxlength="10" size="11" tabindex="103" title="" value="{$FROM_DATE_VALUE}" id="from_date" name="from_date" autocomplete="off">
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
		    <span class="text-label">Đến ngày: </span>    
		    <div class="dateTime d-flex gap-2 position-relative">
			  <input  class="date_input box-input" type="text" maxlength="10" size="11" title="" value="{$TO_DATE_VALUE}" id="to_date" name="to_date" autocomplete="off">
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
  
		<input type="submit" id="btnView" name="btnView" class="btn btn-primary" value="Xem" title="Xem"/>
	 </div>

    <!-- DATA LIST --> 
    <table border="0" cellpadding="0" cellspacing="0" class="table-bonus table-details__booking">
		<thead>
		<tr id="first_row">
			<th width="6%">Booking</th>
			<th width="10%">Liên hệ</th>
			<th width="12%">Ghi chú</th>
			<th width="6%">Giá bán</th>
			<th width="6%">Giá mua</th>
			<th width="6%">Lợi nhuận</th>
			<th width="7%">Ngày tạo</th>
			<th width="9%">Nhân viên</th>
			<th width="4%">Bonus</th>
			<th width="4%">Duyệt</th>
			<th width="9%">Người duyệt</th>
			<th width="12%">Nhận xét</th>
			<th width="2%">&nbsp;</th>
		</tr>
		</thead>
		<tbody>
			{$DATA}
			<tr id="last_row" class="footer-tr">
				<td colspan="13">
					<div class="d-flex gap-2 align-items-center">
						<input type="button" class="btn btn-primary" id="btnAddRow" value="Thêm dòng" title="Thêm dòng" />&nbsp;
						<input type="submit" class="btn btn-primary" id="btnSaveBonus" name="btnSaveBonus" value="Lưu" title="Lưu" />
						<input type="hidden" name="row_count" id="row_count" value="{$ROW_COUNT}" />
						<span>Số dòng = </span>
						<span class="label fw-bold" id="lbl_row_count">{$ROW_COUNT}</span>
					</div>
				</td>
			</tr>
		</tbody>
    </table>
</form>
</div>