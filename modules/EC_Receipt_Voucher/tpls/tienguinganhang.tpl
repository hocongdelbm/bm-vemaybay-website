{literal}
	<style>
        #table-wrapper tr td{
            font-family:"Times New Roman", Times, serif;
            font-size:10pt;
			page-break-after:always;
        }
        #table-details tr td{
            border-collapse:collapse;
            line-height:16px;
            font-family:"Times New Roman", Times, serif;
            font-size:10pt;
            border:1px solid #000000;
			padding:3px;
        }
        #table-signed tr td{
            line-height:20px;
            font-family:"Times New Roman", Times, serif;
            font-size:10pt;
        }
    </style>
	<script>
		$(document).ready(function() {
			// check all
			$('#check_all').change(function(){
				var is_check_all = $(this).is(':checked');
				if(is_check_all){
					$('input:checkbox[name="tknganhang_id[]"]').attr('checked', true);
				} else {
					$('input:checkbox[name="tknganhang_id[]"]').attr('checked', false);
				}
			});
			
			// xu ly su kien nhan nut dong y
            $(document).on('submit', '#frmConditions', function () {
			// $('#frmConditions').submit(function(){
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
				
				// kiem tra da chon vat tu hang hoa chua
				var count_check = 0;
				$('input:checkbox[name="tknganhang_id[]"]').each(function(){
					if($(this).is(':checked')){
						count_check++;
					}
				});
				
				if(count_check == 0){
					alert('Bạn chưa chọn tài khoản ngân hàng. Vui lòng chọn lại.');
					return false;
				}
				
				Set_Cookie('showLeftCol','false',30,'/','','');
			});
		});
    </script>
{/literal}


{php}
	if(!isset($_POST['btnDongY'])){
{/php}

<h1 class="title sokyquy-title d-flex align-items-center gap-2">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-bank" viewBox="0 0 16 16">
        <path d="m8 0 6.61 3h.89a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5H15v7a.5.5 0 0 1 .485.38l.5 2a.498.498 0 0 1-.485.62H.5a.498.498 0 0 1-.485-.62l.5-2A.501.501 0 0 1 1 13V6H.5a.5.5 0 0 1-.5-.5v-2A.5.5 0 0 1 .5 3h.89L8 0ZM3.777 3h8.447L8 1 3.777 3ZM2 6v7h1V6H2Zm2 0v7h2.5V6H4Zm3.5 0v7h1V6h-1Zm2 0v7H12V6H9.5ZM13 6v7h1V6h-1Zm2-1V4H1v1h14Zm-.39 9H1.39l-.25 1h13.72l-.25-1Z"/>
      </svg>
    Tiền gửi ngân hàng
</h1>

<div class="box-section">
<form name="frmConditions" id="frmConditions" action="index.php" method="post" target="_blank">
 
<input type="hidden" name="module" value="EC_Receipt_Voucher" />
<input type="hidden" name="action" value="tienguinganhang" />
<input type="hidden" name="print" value="true" />

<div class="duration-wrap d-inline-flex gap-2 align-items-center mb-3">
    <div class="d-flex gap-2 align-items-center fdate_trigger--wrap">
        <span class="sublabel">Từ ngày: </span>    
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
                    Calendar.setup({
                            inputField: "tungay",
                            daFormat: "%d-%m-%Y",
                            button: "tungay_trigger",
                            singleClick: true,
                            dateStr: "",
                            step: 1,
                            weekNumbers:false
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
            <input class="date_input box-input" type="text" maxlength="10" size="11" tabindex="103" title="" value="{$POST_DENNGAY}" id="denngay" name="denngay" autocomplete="off">
            <button class="icon_dateTime" type="button" id="denngay_trigger" onclick="return false;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                    <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                    <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                </svg>
            </button>
            {literal}
                <script type="text/javascript">
                    Calendar.setup({
                            inputField: "denngay",
                            daFormat: "%d-%m-%Y",
                            button: "denngay_trigger",
                            singleClick: true,
                            dateStr: "",
                            step: 1
                        }
                    );
                </script>
            {/literal}
        </div>
    </div>
</div>

<div class="box-details">
    <table id="bank_account_list" cellpadding="0" cellspacing="0" border="0" class="table__sticky table-details__booking table-bank_account_list">
        <thead>
            <tr>
                <th width="3%" align="center"><input type="checkbox" id="check_all" /></th>
                <th width="25%" align="left">Số tài khoản</th>
                <th align="left">Tên ngân hàng</th>
            </tr>
        </thead>
        <tbody>
            {$BANK_ACCOUNT_LIST}
        </tbody>
    </table>
</div>

<div class="d-flex align-items-center gap-2 mt-3">
    <input class="btn btn-primary" type="submit" name="btnDongY" id="btnDongY" value="Đồng ý" title="Đồng ý" />
    {php}
        if(is_admin($GLOBALS['current_user'])){
    {/php}
    <input class="btn btn-success" type="submit" name="btnXuatSoVe" id="btnXuatSoVe" value="Xuất số vé" title="Xuất số vé" />
    {php}
        }
    {/php}
    <input class="btn btn-danger" type="button" name="btnHuyBo" id="btnHuyBo" value="Hủy bỏ" title="Hủy bỏ" onclick="window.location='index.php?module=EC_Receipt_Voucher&action=index'" />
</div>

</form>
</div>
{php}
	}
{/php}


{php}
	if(isset($_POST['btnDongY'])){
{/php}

	{$DATA}

{php}
	}
{/php}

