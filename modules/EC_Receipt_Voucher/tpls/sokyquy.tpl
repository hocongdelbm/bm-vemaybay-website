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
        }
        #table-details td{
            border:1px solid #000000;
			padding:3px;
        }
        #table-signed td{
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
					$('input:checkbox[name="doituong_id[]"]').attr('checked', true);
				} else {
					$('input:checkbox[name="doituong_id[]"]').attr('checked', false);
				}
			});
			
			// xu ly su kien nhan nut dong y
			$('#frmConditions').submit(function(){
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
				if($('input:checkbox[name="doituong_id[]"]:checked').length == 0){
					alert('Bạn chưa chọn đối tượng');
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
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-wallet" viewBox="0 0 16 16">
        <path d="M0 3a2 2 0 0 1 2-2h13.5a.5.5 0 0 1 0 1H15v2a1 1 0 0 1 1 1v8.5a1.5 1.5 0 0 1-1.5 1.5h-12A2.5 2.5 0 0 1 0 12.5V3zm1 1.732V12.5A1.5 1.5 0 0 0 2.5 14h12a.5.5 0 0 0 .5-.5V5H2a1.99 1.99 0 0 1-1-.268zM1 3a1 1 0 0 0 1 1h12V2H2a1 1 0 0 0-1 1z"/>
      </svg>
    Sổ theo dõi ký quỹ
</h1>

<div class="box-section">
<form name="frmConditions" id="frmConditions" action="index.php" method="post" target="_blank">
    <input type="hidden" name="module" value="EC_Receipt_Voucher" />
    <input type="hidden" name="action" value="sokyquy" />
    <input type="hidden" name="print" value="true" />

    <div class="from-to-date--wrap d-inline-flex gap-2 align-items-center mb-3">
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
        <table id="accounting_object_list" class="table-details__booking table__accounting_object_list" cellpadding="0" cellspacing="0" border="0">
            <thead>
                <tr>
                    <th width="3%" align="center"><input type="checkbox" id="check_all" /></th>
                    <th width="10%" align="left">Mã đối tượng</th>
                    <th width="10%" align="left">Mã số thuế</th>
                    <th width="35%" align="left">Tên đối tượng</th>
                    <th width="10%" align="left">Điện thoại</th>
                    <th align="left">Địa chỉ</th>
                </tr>
            </thead>
            <tbody>
                {$ACCOUNTING_OBJECT_LIST}
            </tbody>
        </table>
    </div>

    <div class="d-flex align-items-center gap-2 mt-3">
        <input class="btn btn-primary" type="submit" name="btnDongY" id="btnDongY" value="Đồng ý" title="Đồng ý" />
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

