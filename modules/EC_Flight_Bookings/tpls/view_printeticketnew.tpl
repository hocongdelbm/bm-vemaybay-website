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
		font-family: 'Inter',
		Arial,
		sans-serif;
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

	{foreach from=$PASSENGER_GROUPS item=group key=gidx}
		{if $gidx > 0}
			<!-- Page break for multiple passenger groups -->
			<div style="page-break-before: always; margin-top: 20px;"></div>
		{/if}
		<div style="max-width: 900px; margin: 0 auto; border: 1px solid #000;">

			<!-- HEADER -->
			<div style="padding: 5px 15px; display: flex; align-items: center; justify-content: space-between;">
				<div style="display: flex; align-items: center; gap: 12px;">
					<img src="https://bm.vemaybay.website/include/images/mail/logo-tcb-blue.png" alt="logo"
						style="height: 40px;" />
					<div style="line-height: 1.2;">
						<div
							style="font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">
							{if $LANG == 'en'}TIM CHUYEN BAY
							{else}TÌM CHUYẾN BAY
							{/if}</div>
						<div style="font-size: 11px; color: #333; margin-top: 0;">{if $LANG == 'en'}Find flights your
							way{else}Tìm chuyến bay theo cách của bạn
							{/if}</div>
					</div>
				</div>
				<div style="text-align: right; line-height: 1.2;">
					<div style="font-size: 11px; text-transform: uppercase; color: #555; letter-spacing: 1px;">
						{if $LANG == 'en'}Booking{else}Mã đơn hàng{/if}</div>
					<div style="font-size: 14px; font-weight: 800; letter-spacing: 2px;">{$BOOKING_NUMBER}</div>
					<div style="font-size: 11px; color: #555; margin-top: 2px;">Tel: {$COM_TOP_PHONE}</div>
				</div>
			</div>



			<!-- ITINERARIES -->
			{foreach from=$group.itineraries item=iti key=idx}
				<div style=" padding: 0;">

					<!-- Direction label -->
					<div
						style="background: #f0f0f0; padding: 5px 20px; border-bottom: 1px solid #ccc; display: flex; align-items: center; justify-content: space-between;">
						<span
							style="font-weight: 700; font-size: 12px; text-transform: uppercase;">{$iti.direction_label}</span>
						<span style="font-size: 12px; font-weight: 500;">{$iti.airline}</span>
					</div>

					<!-- Flight info -->
					<div style="padding: 10px 20px; display: flex; align-items: center; justify-content: space-between;">
						<!-- Departure -->
						<div style="text-align: center; flex: 2;">
							<div style="font-size: 20px; font-weight: 800; letter-spacing: 2px;">{$iti.dep_code}</div>
							<div style="font-size: 15px; color: #333; font-weight: 500;">{$iti.dep_city}</div>
							<div style="font-size: 12px; color: #666; margin-top: 1px;">{$iti.dep_airport}</div>
							<div style="margin-top: 2px;">
								<div style="font-size: 12px; font-weight: 500; color: #555;">{$iti.dep_time} &nbsp;<span
										style="font-size: 12px; font-weight: 500; color: #555;">{$iti.dep_date}</span></div>
							</div>
						</div>

						<!-- Flight path -->
						<div style="flex: 1; text-align: center; padding: 0 6px;">
							<div style="font-size: 11px; font-weight: 600; color: #555;">
								{$iti.flight_number}
							</div>
							<div
								style="position: relative; height: 20px; display: flex; align-items: center; justify-content: center;">
								<div style="position: absolute; left: 8%; right: 8%; height: 1px; background: #000; top: 50%;">
								</div>
								<div
									style="position: absolute; left: 8%; width: 6px; height: 6px; border-radius: 50%; background: #000; top: 50%; transform: translateY(-50%);">
								</div>
								<div
									style="position: absolute; right: 8%; width: 0; height: 0; border-left: 8px solid #000; border-top: 5px solid transparent; border-bottom: 5px solid transparent; top: 50%; transform: translateY(-50%);">
								</div>
							</div>
						</div>

						<!-- Arrival -->
						<div style="text-align: center; flex: 2;">
							<div style="font-size: 20px; font-weight: 800; letter-spacing: 2px;">{$iti.arr_code}</div>
							<div style="font-size: 15px; color: #333; font-weight: 500;">{$iti.arr_city}</div>
							<div style="font-size: 12px; color: #666; margin-top: 1px;">{$iti.arr_airport}</div>
							<div style="margin-top: 2px;">
								<div style="font-size: 12px; font-weight: 500; color: #555;">{$iti.arr_time} &nbsp;<span
										style="font-size: 12px; font-weight: 500; color: #555;">{$iti.arr_date}</span></div>
							</div>
						</div>
					</div>
				</div>
			{/foreach}

			<!-- PASSENGERS TABLE -->
			{if $group.passengers|@count > 0}
				{* Check if airline is Vietnam Airlines (VN/VNA) or Bamboo Airways (QH/BBA) - only these use e-ticket *}
				{assign var="hasEticket" value=false}
				{foreach from=$group.itineraries item=iti}
					{if $iti.airline_code == 'VN' || $iti.airline_code == 'VNA' || $iti.airline_code == 'QH' || $iti.airline_code == 'BBA'}
						{assign var="hasEticket" value=true}
					{/if}
				{/foreach}

				<div style="border-block: 1px dashed #000;padding: 12px;">

					<table style="width: 100%; border-collapse: collapse; font-size: 12px;">
						<thead>
							<tr>
								<th
									style="text-align: left; padding: 6px 8px; border: 1px solid #000; font-weight: 700; font-size: 12px; text-transform: uppercase; background: #f0f0f0;">
									{if $LANG == 'en'}Passenger{else}Hành khách{/if}</th>
								<th
									style="text-align: center; padding: 6px 8px; border: 1px solid #000; font-weight: 700; font-size: 12px; text-transform: uppercase; background: #f0f0f0;">
									{if $LANG == 'en'}PNR{else}Mã đặt chỗ{/if}</th>
								{if $hasEticket}
									<th
										style="text-align: center; padding: 6px 8px; border: 1px solid #000; font-weight: 700; font-size: 12px; text-transform: uppercase; background: #f0f0f0;">
										{if $LANG == 'en'}Ticket No.{else}Số vé{/if}</th>
								{/if}
								<th
									style="text-align: left; padding: 6px 8px; border: 1px solid #000; font-weight: 700; font-size: 12px; text-transform: uppercase; background: #f0f0f0;">
									{if $LANG == 'en'}Baggage{else}Hành lý ký gửi{/if}</th>
							</tr>
						</thead>
						<tbody>
							{foreach from=$group.passengers item=pax key=pidx}
								<tr>
									<td style="padding: 5px 8px; border: 1px solid #000; font-weight: 500;">
										{$pax.salutation} {$pax.name}
										{if $pax.type|lower != 'người lớn' && $pax.type|lower != 'adult' && $pax.type|lower != 'adt'}
											<span style="font-size: 11px; color: #555;">({$pax.type})</span>
										{/if}
									</td>
									<td style="padding: 5px 8px; text-align: center; border: 1px solid #000;">
										{$pax.pnr}</td>
									{if $hasEticket}
										<td style="padding: 5px 8px; text-align: center; border: 1px solid #000; letter-spacing: 1px;">
											{if $pax.eticket_outbound}
												{$pax.eticket_outbound}
												{if $IS_ROUND_TRIP && $pax.eticket_inbound}
													<br /><span style="font-size: 11px; color: #666;">(Lượt đi)</span>
												{/if}
											{/if}
											{if $IS_ROUND_TRIP && $pax.eticket_inbound}
												{if $pax.eticket_outbound}<br />{/if}
												{$pax.eticket_inbound}
												{if $pax.eticket_outbound}
													<br /><span style="font-size: 11px; color: #666;">(Lượt về)</span>
												{/if}
											{/if}
										</td>
									{/if}
									<td style="padding: 5px 8px; border: 1px solid #000;">
										{if $pax.baggage}{$pax.baggage}{/if}
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
			{/if}

			<!-- NOTES SECTION -->
			<div style="padding: 10px 10px 12px;">
				<div style="padding: 0px 20px;">
					<div style="font-weight: 700; font-size: 13px; margin-bottom: 6px; text-transform: uppercase;">
						{if $LANG == 'en'}Important Notes{else}Lưu ý quan trọng{/if}
					</div>
					<ul
						style="margin: 0; padding-left: 20px; font-size: 12px; line-height: 1.5; color: #000; list-style-type: disc;">
						{if $LANG == 'en'}
							<li style="margin-bottom: 6px;">Please verify all information carefully before heading to the
								airport. Original identification
								documents are required.</li>
							<li style="margin-bottom: 6px;">Please arrive at the airport at least <strong>{$MINUTE_BEFORE}
									minutes</strong> before departure
								(during holidays 150-180 minutes).</li>
							<li style="margin-bottom: 6px;"><strong>Passengers aged 14+ must carry valid ID: National ID, valid
									passport,
									or Level 2 VNeID.</strong></li>
							<li style="margin-bottom: 6px;">Keep your phone on to receive updates from the airline or support
								staff.</li>
							<li style="margin-bottom: 6px;">Promotional tickets are non-refundable and non-changeable.</li>
							<li style="margin-bottom: 6px;"><strong>Round-trip:</strong> If you skip the outbound flight,
								<strong>notify us before the first
									flight date</strong> to use the return.
							</li>
						{else}
							<li style="margin-bottom: 6px;">Quý khách cần kiểm tra thông tin kỹ càng trước khi ra sân bay. Giấy
								tờ tùy thân phải là bản
								chính.</li>
							<li style="margin-bottom: 6px;">Có mặt tại sân bay trước giờ khởi hành <strong>{$MINUTE_BEFORE}
									phút</strong> (Lễ, Tết trước
								150-180 phút).</li>
							<li style="margin-bottom: 6px;"><strong>Hành khách từ 14 tuổi trở lên phải có CCCD, hộ chiếu còn hạn
									hoặc VNeID mức
									độ 2.</strong> Dưới 14 tuổi: giấy khai sinh bản chính.</li>
							<li style="margin-bottom: 6px;">Luôn mở điện thoại để nhận thông tin từ hãng hoặc nhân viên hỗ trợ.
							</li>
							<li style="margin-bottom: 6px;">Vé khuyến mãi không hoàn đổi. Mọi sai sót đều dẫn đến mất vé hoặc
								phí đổi.</li>
							<li style="margin-bottom: 6px;"><strong>Vé khứ hồi:</strong> Không bay chặng đi <strong>phải thông
									báo trước ngày bay đầu
									tiên</strong> để sử dụng chặng về.</li>
						{/if}
					</ul>
				</div>
			</div>

			<!-- FOOTER -->
			<div style="padding: 8px 20px; text-align: center;">
				<div style="font-size: 11px; line-height: 1.5; color: #333;">
					<strong>{$COM_NAME}</strong> &nbsp;|&nbsp; {$COM_ADDRESS}
					<br />
					MST: {$COM_TAXCODE} &nbsp;|&nbsp; Tel: {$COM_PHONE} &nbsp;|&nbsp; Email: {$COM_EMAIL}
				</div>
			</div>

		</div>

	{/foreach}

</body>

</html>