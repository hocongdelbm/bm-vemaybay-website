<script type="text/javascript" src="custom/jqueryui/plugins/chromatable/jquery.chromatable.js"></script>
<link rel="stylesheet" href="custom/jqueryui/plugins/chromatable/css/style.css">

{literal}
    <script>
		$(document).ready(function() {
			// onload
			$('#account_list').chromatable({
				width: "100%", // 450px
				height: "450px",
				scrolling: "yes"	
			});
			
			// check all
			$('#check_all').change(function(){
				var is_check_all = $(this).is(':checked');
				if(is_check_all){
					$('input:checkbox[name="khachhang_id[]"]').attr('checked', true);
				} else {
					$('input:checkbox[name="khachhang_id[]"]').attr('checked', false);
				}
			});
			
			// xu ly su kien nhan nut tim
			$('#btnTim').on('click',function(){
				var tungay = $('#tungay').val();
				var denngay = $('#denngay').val(); 
				if(tungay.length == 0) {
					alert('Vui lòng chọn từ ngày');
					$('#tungay').focus();
					return false;
				} 
				if(denngay.length == 0) {
					alert('Vui lòng chọn đến ngày');
					$('#denngay').focus();
					return false;
				}
				$('#frmConditions').attr('target','_self');
				$('#frmConditions').attr('action','index.php');
				$('#frmConditions input:hidden[name="print"]').remove();
			});
			
			// xu ly su kien nhan nut dong y
			$('#btnDongY').on('click',function(){
				var tungay = $('#tungay').val();
				var denngay = $('#denngay').val(); 
				if(tungay.length == 0) {
					alert('Vui lòng chọn từ ngày');
					$('#tungay').focus();
					return false;
				} 
				if(denngay.length == 0) {
					alert('Vui lòng chọn đến ngày');
					$('#denngay').focus();
					return false;
				}
				if($('input:checkbox[name="khachhang_id[]"]:checked').length == 0){
					alert('Bạn chưa chọn khách hàng');
					return false;
				}
			});
			
		});
    </script>
{/literal}

{php}
	if(isset($_POST['btnDongY'])) {
{/php}
	
    {literal}
    <script>
		$(document).ready(function(){
			Set_Cookie('showLeftCol','false',30,'/','','');
		});
    </script>
    {/literal}
    {$DATA}
    
{php} 

} else {
		
{/php}
	
<h1 class="title">Chi tiết công nợ phải trả</h1>
<div class="box-section">
   <form name="frmConditions" id="frmConditions" method="post" action="index.php" target="_blank" >
    <input type="hidden" name="module" value="EC_Debts"  />
    <input type="hidden" name="action" value="debttopay"  />
    <input type="hidden" name="print" value="true"  />
    
    <table id="tbl-conditions" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td>
              <div class="from-to-date--wrap d-inline-flex gap-2 align-items-center mb-3">
                <div class="d-flex gap-2 align-items-center fdate_trigger--wrap">
                    <span class="text-label">Từ ngày: </span>    
                    <div class="dateTime d-flex gap-2 position-relative">
                        <input class="date_input box-input" type="text" maxlength="10" size="11" tabindex="103" title="" value="{$POST_TUNGAY}" id="tungay" name="tungay" autocomplete="off">
                        <button class="icon_dateTime" type="button" id="tungay_trigger" onclick="return false;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                                <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                              </svg>
                        </button>
                        {literal}
                        <script type="text/javascript">
                            Calendar.setup ({
                            inputField : "tungay",
                            daFormat : "%d-%m-%Y",
                            button : "tungay_trigger",
                            singleClick : true,
                            dateStr : "",
                            step : 1,
                            weekNumbers:false
                        });
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
                      <input  class="date_input box-input" type="text" maxlength="10" size="11" title="" value="{$POST_DENNGAY}" id="denngay" name="denngay" autocomplete="off">
                        <button class="icon_dateTime" type="button" id="denngay_trigger" onclick="return false;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                                <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                              </svg>
                        </button>
                        {literal}
                          <script type="text/javascript">
                              Calendar.setup ({
                              inputField : "denngay",
                              daFormat : "%d-%m-%Y",
                              button : "denngay_trigger",
                              singleClick : true,
                              dateStr : "",
                              step : 1,
                              weekNumbers:false
                          });
                          </script>
                          {/literal}
                    </div>
                </div>
                
                <input type="submit" name="btnTim" id="btnTim" class="btn btn-primary" value="Tìm" title="Tìm" />
              </div>
          </td>
        </tr>
        <tr>
            <td>
                <!-- bat dau danh sach khach hang -->
                <div class="chromatable">
                  <table id="account_list" class="table-details__booking table-debts__topay" cellpadding="0" cellspacing="0" border="0">
                      <thead>
                          <tr>
                              <th width="5%" align="center"><input type="checkbox" id="check_all" /></th>
                              <th width="10%" align="left">Mã NCC</th>
                              <th width="10%" align="left">Mã số thuế</th>
                              <th width="35%" align="left">Tên NCC</th>
                              <th width="25%" align="left">Địa chỉ</th>
                              <th width="15%" align="left">Số tiền</th>
                          </tr>
                          <tr>
                              <th align="center">&nbsp;</th>
                              <th align="left">&nbsp;</th>
                              <th align="left">&nbsp;</th>
                              <th align="left">&nbsp;</th>
                              <th align="left">&nbsp;</th>
                              <th align="right">{$TONGTIENNO}</th>
                          </tr>
                      </thead>
                      <tbody>
                          {$DS_KHACHHANG}
                      </tbody>
                  </table>
                </div>
                <!-- ket thuc danh sach khach hang -->
            </td>
        </tr>
        <tr>
            <td>
              <div class="d-flex align-items-center gap-2 mt-3">
                <input type="submit" class="btn btn-primary" name="btnDongY" id="btnDongY" value="Đồng ý" />
                <input type="button" class="btn btn-danger" name="btnCancel" id="btnCancel" value="Hủy bỏ" onclick="window.location='index.php?module=EC_Debts&action=index'" />
              </div>
            </td>
        </tr>
    </table>
    </form>
</div>

{php} 		
    } 
{/php}