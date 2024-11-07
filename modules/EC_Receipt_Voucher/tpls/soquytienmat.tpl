{php}
	if(!isset($_POST['btnViewDetail'])){
{/php}
{literal}
	<script>
		$(document).ready(function(){
			$('#btnViewDetail').click(function(){
				if($('#location_id').length > 0){
					$('#location_name').val($('#location_id :selected').text());
				}
			});
		});
	</script>
{/literal}

<h1 class="title sokyquy-title d-flex align-items-center gap-2">
	<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-wallet" viewBox="0 0 16 16">
	    <path d="M0 3a2 2 0 0 1 2-2h13.5a.5.5 0 0 1 0 1H15v2a1 1 0 0 1 1 1v8.5a1.5 1.5 0 0 1-1.5 1.5h-12A2.5 2.5 0 0 1 0 12.5V3zm1 1.732V12.5A1.5 1.5 0 0 0 2.5 14h12a.5.5 0 0 0 .5-.5V5H2a1.99 1.99 0 0 1-1-.268zM1 3a1 1 0 0 0 1 1h12V2H2a1 1 0 0 0-1 1z"/>
	  </svg>
	Sổ quỹ tiền mặt
 </h1>

<form name="frmSearch" id="frmSearch" action="index.php" method="post" target="_blank">
	<input type="hidden" name="module" value="EC_Receipt_Voucher" />
	<input type="hidden" name="action" value="soquytienmat" />
	<input type="hidden" name="print" value="true" />

	<div class="box-section">
		<table cellpadding="2" cellspacing="10" border="0" >
			<tr>
				<td align="left" class="text-label">
					<div class="duration-wrap d-inline-flex gap-2 align-items-center">
						<div class="d-flex gap-2 align-items-center fdate_trigger--wrap">
							<span class="sublabel">Từ ngày: </span>    
							<div class="dateTime d-flex gap-2 position-relative">
							    <input class="date_input box-input" type="text" maxlength="10" size="11" tabindex="103" title="" value="{$POST_FDATE}" id="from_date" name="from_date" autocomplete="off">
							    <button class="icon_dateTime" type="button" id="from_date_trigger" onclick="return false;">
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
											button: "from_date_trigger",
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
							    <input class="date_input box-input" type="text" maxlength="10" size="11" tabindex="103" title="" value="{$POST_TDATE}" id="to_date" name="to_date" autocomplete="off">
							    <button class="icon_dateTime" type="button" id="to_date_trigger" onclick="return false;">
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
											button: "to_date_trigger",
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

						 <div class="d-flex align-items-center gap-2">
							<span class="sublabel">Địa điểm:  </span>    
							<select id="location_id" class="box-select" name="location_id">{$LOCATION_ID}</select>
							<input type="hidden" name="location_name" id="location_name" value="" />
						 </div>

						 <div class="d-flex align-items-center">
							<input class="btn btn-primary" type="submit" name="btnViewDetail" id="btnViewDetail" value="Xem chi tiết" title="Xem chi tiết" onclick="Set_Cookie('showLeftCol','false',30,'/','','')">
						 </div>
					</div>
				</td>
			</tr>
		</table>
	</div>
</form>

{php}
	}
{/php}

{php}
	if(isset($_POST['btnViewDetail'])){
{/php}
{literal}
<style>

	table#table-wrapper tr td{
		font-family: unset ;
	}

	#table-wrapper{
		font-family:"Times New Roman", Times, serif;
		font-size:10pt;
	}
	#table-details{
		border-collapse:collapse;
		line-height:16px;
		font-family:"Times New Roman", Times, serif;
		font-size:10pt;
	}
	#table-details td{
		border:1px solid #000000;
		padding:3px;
	}
	#table-signed{
		line-height:16px;
		font-family:"Times New Roman", Times, serif;
		font-size:10pt;
	}
</style>
{/literal}

	<form action="index.php" method="post">
		<input type="hidden" name="module" value="EC_Receipt_Voucher">
		<input type="hidden" name="action" value="soquytienmat">
		<input type="hidden" name="print" value="true"/>
		<input type="hidden" name="exportexcel"/>
		<input type="hidden" name="from_date" value="{$POST_FROM_DATE}"/>
		<input type="hidden" name="to_date" value="{$POST_TO_DATE}"/>
		<input type="hidden" name="location_id" value="{$LOCATION_ID}"/> 
		<input type="hidden" name="location_name" value="{$LOCATION_NAME}"/> 
		{$VOUCHER_LIST}
	</form>
{php}
	}
{/php}