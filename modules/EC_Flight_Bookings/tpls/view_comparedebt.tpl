{literal}
    <script>
		$(document).ready(function(){
			$('#btnCreateDebt').on('click',function(){
				$('#frmCompareDebt input:hidden[name="module"]').val('EC_Debts');
				$('#frmCompareDebt input:hidden[name="action"]').val('EditView');
			});
			$('#frmCompareDebt').on('submit',function(){
				var reg = /^[a-zA-Z]+$/;
				var err=0;
				$('.excel_col').each(function(){
					if($(this).val()=='' || !reg.test($(this).val())){
						err++;
					}
				});
				if( err > 0 || $('#sheet_name').val()=='' || parseInt($('#start_row').val()) > parseInt($('#end_row').val()) ){
					alert('Các trường dữ liệu không hợp lệ');
					return false;
				}
			});
			$('input:text').on('click',function(){
				$(this).select();
			});
		});
    </script>
{/literal}

<h1 class="title">ĐỐI CHIẾU CÔNG NỢ</h1>

<div class="box-section">
<form action="index.php" method="post" name="frmCompareDebt" id="frmCompareDebt" enctype="multipart/form-data">
	<input type="hidden" name="module" value="EC_Flight_Bookings" />
	<input type="hidden" name="action" value="comparedebt" />

    <table id="tbl_compare_form" cellpadding="0" cellspacing="0" class="table-details__booking" border="0">
        <!--<tr>
        	<td width="10%">Khoảng thời gian:</td>
            <td width="90%">
            	<input type="text" maxlength="10" size="11" tabindex="103" title="" value="{$FROM_DATE_VALUE}" id="from_date" name="from_date" autocomplete="off">
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
					});
                </script>
                {/literal}
                &nbsp;-&nbsp;
                <input type="text" maxlength="10" size="11" tabindex="103" title="" value="{$TO_DATE_VALUE}" id="to_date" name="to_date" autocomplete="off">
                <img border="0" align="absmiddle" id="tdate_trigger" alt="input date" src="themes/default/images/jscalendar.gif">
                {literal}
                <script type="text/javascript">
					Calendar.setup ({
						inputField : "to_date",
						daFormat : "%d-%m-%Y",
						button : "tdate_trigger",
						singleClick : true,
						dateStr : "",
						step : 1
					});
                </script>
                {/literal}
            </td>
        </tr>-->
        <tr>
        	<td width="10%">File (*.xls)</td>
            <td width="90%"><input type="file" name="upload_file" id="upload_file" /></td>
        </tr>
        <tr>
        	<td></td>
            <td>
            	<label for="pax_col">Cột hành khách: <input class="excel_col box-input text-center" type="text" size="3" maxlength="1" name="pax_col" id="pax_col" value="{$PAX_COL}" /></label>
                <label for="eticket_col">Cột số vé: <input class="excel_col box-input text-center" type="text" size="3" maxlength="1" name="eticket_col" id="eticket_col" value="{$ETICKET_COL}" /></label>
                <label for="pnr_col">Cột PNR: <input class="excel_col box-input text-center" type="text" size="3" maxlength="1" name="pnr_col" id="pnr_col" value="{$PNR_COL}" /></label>
                <label for="iti_col">Cột chặng bay: <input class="excel_col box-input text-center" type="text" size="3" maxlength="1" name="iti_col" id="iti_col" value="{$ITI_COL}" /></label>
                <label for="amount_col">Cột tổng tiền: <input class="excel_col box-input text-center" type="text" size="3" maxlength="1" name="amount_col" id="amount_col" value="{$AMOUNT_COL}" /></label> 
                <label for="discount_col">Cột hoa hồng: <input class="excel_col box-input text-center" type="text" size="3" maxlength="1" name="discount_col" id="discount_col" value="{$DISCOUNT_COL}" /></label> 
                <label for="airline_col">Cột hãng: <input class="excel_col box-input text-center" type="text" size="3" maxlength="1" name="airline_col" id="airline_col" value="{$AIRLINE_COL}" /></label>
            </td>
        </tr>
        <tr>
        	<td></td>
            <td>
            	<label for="start_row">Dòng bắt đầu: <input type="text" class="box-input text-center" size="5" maxlength="20" name="start_row" id="start_row" value="{$START_ROW}" /></label>
                <label for="end_row">Dòng kết thúc: <input type="text" class="box-input text-center" size="5" maxlength="20" name="end_row" id="end_row" value="{$END_ROW}" /></label>
            	<label for="sheet_name">Tên sheet: <input type="text" class="box-input text-center" size="5" maxlength="20" name="sheet_name" id="sheet_name" value="{$SHEET_NAME}" /></label>
            </td>
        </tr>
        <tr>
        	<td>&nbsp;</td>
            <td>
            	<input type="submit" name="btnCompare" id="btnCompare" class="btn btn-warning" value="Đối chiếu" title="Đối chiếu" />
                {if $TOTAL_AMT > 0 && $HAS_ACCESS_DEBT}
                
                <input type="hidden" name="debt_amount" value="{$TOTAL_AMOUNT}" />
                <input type="hidden" name="debt_type" value="Buy" />
                <input type="submit" name="btnCreateDebt" id="btnCreateDebt" value="Tạo công nợ" title="Tạo công nợ" />
                
                {/if}
            	<input type="button" name="btnCancel" id="btnCancel" class="btn btn-secondary" value="Hủy bỏ" title="Hủy bỏ" onclick="window.location='index.php?module=EC_Flight_Bookings&action=index'" />
            </td>
        </tr>
    </table>
</form>
</div>

{php} if(isset($_POST['btnCompare'])){ {/php}
<table id="tbl_details" cellpadding="0" cellspacing="0" border="0" width="100%">
	
    {if $TOTAL_AMT > 0}
    <tr>
    	<td colspan="10">
        	<form action="index.php" method="post">
            	<input type="hidden" name="module" value="EC_Flight_Bookings" />
                <input type="hidden" name="action" value="comparedebt" />
            	{$UPDATE_HIDDEN}
        		<input type="submit" name="btnUpdateDiscount" id="btnUpdateDiscount" value="Cập nhật hoa hồng" title="Cập nhật hoa hồng" />
            </form>
        </td>
    </tr>
    {/if}
    
    <tr>
    	<td width="8%" align="center" style="font-weight:bold; background:#ccc;">Ngày</td>
        <td width="15%" align="center" style="font-weight:bold; background:#ccc;">Hành khách</td>
        <td width="8%" align="center" style="font-weight:bold; background:#ccc;">Số vé</td>
        <td width="8%" align="center" style="font-weight:bold; background:#ccc;">PNR</td>
        <td width="12%" align="center" style="font-weight:bold; background:#ccc;">Chặng bay</td>
        <td width="9%" align="center" style="font-weight:bold; background:#ccc;">Giá mua</td>
        <td width="9%" align="center" style="font-weight:bold; background:#ccc;">Hoa hồng</td>
        <td width="9%" align="center" style="font-weight:bold; background:#ccc;">Thanh toán</td>
        <td width="10%" align="center" style="font-weight:bold; background:#ccc;">Hãng</td>
        <td width="10%" align="center" style="font-weight:bold; background:#ccc;">Ghi chú</td>
    </tr>
    
    {$DATA}
    
    <tr>
    	<td colspan="5">Số dòng&nbsp;=&nbsp;{$TOTAL_ROW}</td>
        <td align="right" style="font-weight:bold;">{$TOTAL_BOUGHT_AMOUNT}</td>
        <td align="right" style="font-weight:bold;">{$TOTAL_DISCOUNT_AMOUNT}</td>
        <td align="right" style="font-weight:bold;">{$TOTAL_AMOUNT}</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
	
</table>
{php} } {/php}