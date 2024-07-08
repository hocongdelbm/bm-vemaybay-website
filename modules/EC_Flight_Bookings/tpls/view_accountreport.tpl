{literal}
	<style>
	#account_list{
        font-size:12px;
		font-family:Arial, Helvetica, sans-serif;
		border-collapse:collapse;
		line-height:20px;
		margin-top:10px;
   	}
    #account_list td{padding:3px; border:1px solid #000;}
	</style>
    <script>
		$(document).ready(function(){
			$('#checkall').on('change',function(){
				if($('#checkall').is(':checked')){
					$('#frmSearch input:checkbox[name="phone[]"]').attr('checked',true);
				} else {
					$('#frmSearch input:checkbox[name="phone[]"]').attr('checked',false);
				}
			});
			$('#btnView').on('click',function(){
				$('#button_selected').val('btnView');
			});
			$('#btnCreateAccount').on('click',function(){
				$('#button_selected').val('btnCreateAccount');
			});
			$('#frmSearch').on('submit',function(){
				if($('#button_selected').val() == 'btnCreateAccount' && $('#frmSearch input:checkbox[name="phone[]"]:checked').length == 0){
					alert('Bạn chưa chọn liên hệ.');
					return false;
				}
			});
		});
    </script>
{/literal}

<form action="index.php" method="post" name="frmSearch" id="frmSearch">
    <input type="hidden" name="module" value="EC_Flight_Bookings" />
    <input type="hidden" name="action" value="accountreport" />
    
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="font-family:Arial, Helvetica, sans-serif; font-size:12px; line-height:20px;">	
        <tr>
            <td align="left">
                <h3>DOANH SỐ KHÁCH HÀNG</h3>
            </td>
        </tr>
        <tr>
            <td align="left">
            <p>
            Từ ngày <input type="text" maxlength="10" size="11" tabindex="103" title="" value="{$FROM_DATE_VALUE}" id="from_date" name="from_date" autocomplete="off">
            <img border="0" align="absmiddle" id="fdate_trigger" alt="input date" src="themes/default/images/jscalendar.gif">
            {literal}
            <script type="text/javascript">
            Calendar.setup ({
            inputField : "from_date",
            daFormat : "%d-%m-%Y",
            button : "fdate_trigger",
            singleClick : true,
            dateStr : "",
            step : 1
            }
            );
            </script>
            {/literal}
            &nbsp;&nbsp;
            Đến ngày <input type="text" maxlength="10" size="11" tabindex="103" title="" value="{$TO_DATE_VALUE}" id="to_date" name="to_date" autocomplete="off">
            <img border="0" align="absmiddle" id="tdate_trigger" alt="input date" src="themes/default/images/jscalendar.gif">
            {literal}
            <script type="text/javascript">
            Calendar.setup ({
            inputField : "to_date",
            daFormat : "%d-%m-%Y",
            button : "tdate_trigger",
            singleClick : true,
            dateStr : "",
            step : 2
            }
            );
            </script>
            {/literal}
            <input type="hidden" name="button_selected" id="button_selected" value="btnView" />
            <input type="submit" id="btnView" value="Xem" name="btnView" class="button" title="Xem" />
            <input type="submit" id="btnCreateAccount" value="Tạo khách hàng" name="btnCreateAccount" class="button" title="Tạo khách hàng" />
            </p>
            </td>
        </tr>
    </table>

    <table width="100%" border="0" cellpadding="0" cellspacing="0" id="account_list">
        <tr>
            <td width="3%" align="center" style="background:#ccc;"><input type="checkbox" id="checkall" /></td>
            <td width="15%" align="center" style="font-weight:bold; background:#ccc;">Tên liên hệ</td>
            <td width="10%" align="center" style="font-weight:bold; background:#ccc;">Điện thoại</td>
            <td width="10%" align="center" style="font-weight:bold; background:#ccc;">Email</td>
            <td width="26%" align="center" style="font-weight:bold; background:#ccc;">Địa chỉ</td>
            <td width="6%" align="center" style="font-weight:bold; background:#ccc;">SL vé</td>
            <td width="10%" align="center" style="font-weight:bold; background:#ccc;">Doanh thu</td>
            <td width="10%" align="center" style="font-weight:bold; background:#ccc;">Giá vốn</td>
            <td width="10%" align="center" style="font-weight:bold; background:#ccc;">Doanh số</td>
        </tr>
        {$DATA}
        <tr>
        	<td colspan="5" align="left">Số dòng = {$TONGSD}</td>
            <td align="center" style="font-weight:bold;">{$TONGSL}</td>
            <td align="right" style="font-weight:bold;">{$TONGDT}</td>
            <td align="right" style="font-weight:bold;">{$TONGGV}</td>
            <td align="right" style="font-weight:bold;">{$TONGLG}</td>
        </tr>
    </table>

</form>
