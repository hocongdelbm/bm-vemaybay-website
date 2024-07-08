<form action="" method="post" name="frmSearch" id="frmSearch">
<table cellpadding="0" cellspacing="0" style="width:98%; margin:0px 8px 8px 8px; font-family:Arial, Helvetica, sans-serif; font-size:12px">	
	<tr>
		<td align="left">
			<h3>BÁO CÁO BÁN HÀNG</h3>
		</td>
	</tr>
	<tr><td>&nbsp;</td></tr>
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
               
		<input type="submit" id="btnExcelExport" value="Xuất Excel" name="btnExcelExport" class="button" title="Xuất Excel" />
		</p>
        
		</td>
	</tr>
    <tr>
    	<td height="1">&nbsp;</td>
    </tr>
</table>
</form>