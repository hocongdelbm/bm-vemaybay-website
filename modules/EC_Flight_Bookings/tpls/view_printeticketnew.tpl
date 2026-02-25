<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>{if $LANG == 'en'}E-ticket - {$BOOKING_NUMBER}{else}Vé điện tử - {$BOOKING_NUMBER}{/if}</title>
	<style>
		@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
		@page {ldelim}
			size: A4;
			margin: 10mm 12mm;
		{rdelim}
		* {ldelim} margin: 0; padding: 0; box-sizing: border-box; {rdelim}
		body {ldelim}
			font-family: 'Inter', Arial, sans-serif;
			background: #fff;
			color: #000;
			padding: 0;
			font-size: 12px;
			-webkit-print-color-adjust: exact;
			print-color-adjust: exact;
		{rdelim}
		@media print {ldelim}
			body {ldelim} padding: 0; {rdelim}
			.no-print {ldelim} display: none !important; {rdelim}
		{rdelim}
	</style>
</head>
<body>

<div style="max-width: 680px; margin: 0 auto; border: 2px solid #000;">

	<!-- HEADER -->
	<div style="border-bottom: 2px solid #000; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between;">
		<div style="display: flex; align-items: center; gap: 12px;">
			<img src="https://bm.vemaybay.website/include/images/mail/logo-tcb-blue.png" alt="logo" style="height: 40px;" />
			<div>
				<div style="font-size: 16px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">{$COM_NAME}</div>
				<div style="font-size: 10px; color: #333; margin-top: 1px;">{if $LANG == 'en'}Find flights your way{else}Tìm chuyến bay theo cách của bạn{/if}</div>
			</div>
		</div>
		<div style="text-align: right;">
			<div style="font-size: 9px; text-transform: uppercase; color: #555; letter-spacing: 1px;">{if $LANG == 'en'}Booking{else}Mã đặt chỗ{/if}</div>
			<div style="font-size: 20px; font-weight: 800; letter-spacing: 2px;">{$BOOKING_NUMBER}</div>
			<div style="font-size: 9px; color: #555; margin-top: 2px;">Tel: {$COM_TOP_PHONE}</div>
		</div>
	</div>

	<!-- ITINERARIES -->
	{foreach from=$ITINERARIES item=iti key=idx}
	<div style="border-bottom: 1px dashed #000; padding: 0;">

		<!-- Direction label -->
		<div style="background: #f0f0f0; padding: 5px 20px; border-bottom: 1px solid #ccc; display: flex; align-items: center; justify-content: space-between;">
			<span style="font-weight: 700; font-size: 11px; text-transform: uppercase;">{$iti.direction_label}</span>
			<span style="font-size: 11px; font-weight: 500;">{$iti.airline} &nbsp;&#8226;&nbsp; {$iti.flight_number}</span>
		</div>

		<!-- Flight info -->
		<div style="padding: 14px 20px; display: flex; align-items: center; justify-content: space-between;">
			<!-- Departure -->
			<div style="text-align: center; flex: 1;">
				<div style="font-size: 28px; font-weight: 800; letter-spacing: 2px;">{$iti.dep_code}</div>
				<div style="font-size: 11px; color: #333; margin-top: 2px; font-weight: 500;">{$iti.dep_city}</div>
				<div style="font-size: 9px; color: #666; margin-top: 1px;">{$iti.dep_airport}</div>
				<div style="margin-top: 6px;">
					<div style="font-size: 16px; font-weight: 700;">{$iti.dep_time}</div>
					<div style="font-size: 10px; color: #555;">{$iti.dep_date}</div>
				</div>
			</div>

			<!-- Flight path -->
			<div style="flex: 0.8; text-align: center; padding: 0 6px;">
				<div style="position: relative; height: 20px; display: flex; align-items: center; justify-content: center;">
					<div style="position: absolute; left: 8%; right: 8%; height: 1px; background: #000; top: 50%;"></div>
					<div style="position: absolute; left: 8%; width: 6px; height: 6px; border-radius: 50%; background: #000; top: 50%; transform: translateY(-50%);"></div>
					<div style="position: absolute; right: 8%; width: 0; height: 0; border-left: 8px solid #000; border-top: 5px solid transparent; border-bottom: 5px solid transparent; top: 50%; transform: translateY(-50%);"></div>
				</div>
				<div style="font-size: 9px; color: #666; margin-top: 2px;">{$iti.airline_code} {$iti.flight_number}</div>
			</div>

			<!-- Arrival -->
			<div style="text-align: center; flex: 1;">
				<div style="font-size: 28px; font-weight: 800; letter-spacing: 2px;">{$iti.arr_code}</div>
				<div style="font-size: 11px; color: #333; margin-top: 2px; font-weight: 500;">{$iti.arr_city}</div>
				<div style="font-size: 9px; color: #666; margin-top: 1px;">{$iti.arr_airport}</div>
				<div style="margin-top: 6px;">
					<div style="font-size: 16px; font-weight: 700;">{$iti.arr_time}</div>
					<div style="font-size: 10px; color: #555;">{$iti.arr_date}</div>
				</div>
			</div>
		</div>
	</div>
	{/foreach}

	<!-- PASSENGERS TABLE -->
	{if $PASSENGERS|@count > 0}
	<div style="padding: 12px 20px 6px;">
		<div style="font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; border-bottom: 1px solid #000; padding-bottom: 4px;">
			{if $LANG == 'en'}Passenger Information{else}Thông tin hành khách{/if}
		</div>

		<table style="width: 100%; border-collapse: collapse; font-size: 11px;">
			<thead>
				<tr>
					<th style="text-align: left; padding: 6px 8px; border: 1px solid #000; font-weight: 700; font-size: 10px; text-transform: uppercase; background: #f0f0f0;">{if $LANG == 'en'}Passenger{else}Hành khách{/if}</th>
					<th style="text-align: center; padding: 6px 8px; border: 1px solid #000; font-weight: 700; font-size: 10px; text-transform: uppercase; background: #f0f0f0;">{if $LANG == 'en'}PNR{else}Mã đặt chỗ{/if}</th>
					<th style="text-align: center; padding: 6px 8px; border: 1px solid #000; font-weight: 700; font-size: 10px; text-transform: uppercase; background: #f0f0f0;">{if $LANG == 'en'}Ticket No.{else}Số vé{/if}</th>
					<th style="text-align: left; padding: 6px 8px; border: 1px solid #000; font-weight: 700; font-size: 10px; text-transform: uppercase; background: #f0f0f0;">{if $LANG == 'en'}Baggage{else}Hành lý{/if}</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$PASSENGERS item=pax key=pidx}
				<tr>
					<td style="padding: 5px 8px; border: 1px solid #000; font-weight: 500;">
						{$pax.salutation} {$pax.name}
						<span style="font-size: 9px; color: #555;">({$pax.type})</span>
					</td>
					<td style="padding: 5px 8px; text-align: center; border: 1px solid #000; font-weight: 600; font-family: 'Courier New', monospace; letter-spacing: 1px;">{$pax.pnr}</td>
					<td style="padding: 5px 8px; text-align: center; border: 1px solid #000; font-family: 'Courier New', monospace; font-size: 10px;">
						{if $pax.eticket_outbound}{$pax.eticket_outbound}{/if}
						{if $IS_ROUND_TRIP && $pax.eticket_inbound}
							<br/>{$pax.eticket_inbound}
						{/if}
					</td>
					<td style="padding: 5px 8px; border: 1px solid #000; font-size: 10px;">
						{if $pax.baggage_outbound}
							{if $IS_ROUND_TRIP}<span style="font-weight:600; font-size:9px;">{if $LANG == 'en'}OUT:{else}Đi:{/if}</span> {/if}{$pax.baggage_outbound}
						{/if}
						{if $IS_ROUND_TRIP && $pax.baggage_inbound}
							<br/><span style="font-weight:600; font-size:9px;">{if $LANG == 'en'}IN:{else}Về:{/if}</span> {$pax.baggage_inbound}
						{/if}
					</td>
				</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	{/if}

	<!-- NOTES SECTION -->
	<div style="padding: 10px 20px 12px;">
		<div style="border: 1px solid #000; padding: 10px 14px;">
			<div style="font-weight: 700; font-size: 11px; margin-bottom: 6px; text-transform: uppercase;">
				{if $LANG == 'en'}Important Notes{else}Lưu ý quan trọng{/if}
			</div>
			<ul style="margin: 0; padding-left: 16px; font-size: 10px; line-height: 1.6; color: #000;">
				{if $LANG == 'en'}
				<li>Please verify all information carefully before heading to the airport. Original identification documents are required.</li>
				<li>Please arrive at the airport at least <strong>{$MINUTE_BEFORE} minutes</strong> before departure (during holidays 150-180 minutes).</li>
				<li><strong>Passengers aged 14+ must carry valid ID: National ID, driver's license, valid passport, or Level 2 VNeID.</strong></li>
				<li>Keep your phone on to receive updates from the airline or support staff.</li>
				<li>Promotional tickets are non-refundable and non-changeable.</li>
				<li><strong>Round-trip:</strong> If you skip the outbound flight, <strong>notify us before the first flight date</strong> to use the return.</li>
				{else}
				<li>Quý khách cần kiểm tra thông tin kỹ càng trước khi ra sân bay. Giấy tờ tùy thân phải là bản chính.</li>
				<li>Có mặt tại sân bay trước giờ khởi hành <strong>{$MINUTE_BEFORE} phút</strong> (Lễ, Tết trước 150-180 phút).</li>
				<li><strong>Hành khách từ 14 tuổi trở lên phải có CCCD, bằng lái xe, hộ chiếu còn hạn hoặc VNeID mức độ 2.</strong> Dưới 14 tuổi: giấy khai sinh bản chính.</li>
				<li>Luôn mở điện thoại để nhận thông tin từ hãng hoặc nhân viên hỗ trợ.</li>
				<li>Vé khuyến mãi không hoàn đổi. Mọi sai sót đều dẫn đến mất vé hoặc phí đổi.</li>
				<li><strong>Vé khứ hồi:</strong> Không bay chặng đi <strong>phải thông báo trước ngày bay đầu tiên</strong> để sử dụng chặng về.</li>
				{/if}
			</ul>
		</div>
	</div>

	<!-- FOOTER -->
	<div style="border-top: 2px solid #000; padding: 8px 20px; text-align: center;">
		<div style="font-size: 10px; line-height: 1.5; color: #333;">
			<strong>{$COM_NAME}</strong> &nbsp;|&nbsp; {$COM_ADDRESS}
			<br/>
			MST: {$COM_TAXCODE} &nbsp;|&nbsp; Tel: {$COM_PHONE} &nbsp;|&nbsp; Email: {$COM_EMAIL}
		</div>
	</div>

</div>

</body>
</html>