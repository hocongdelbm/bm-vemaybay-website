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
		table tr td, table tr th {ldelim}
		font-family: 'Inter',
		Arial,
		sans-serif !important;
		color: #000 !important;
		{rdelim}
	</style>
</head>

<body style="margin:0; padding:0; font-size:12px; color:#000; background:#fff;">

	{foreach from=$PASSENGER_GROUPS item=group key=gidx}
		{if $gidx > 0}
			<div style="page-break-before: always; margin-top: 20px;"></div>
		{/if}

		<!-- OUTER WRAPPER -->
		<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#fff;">
			<tr>
				<td align="center">

					<!-- MAIN CONTAINER -->
					<table width="900" cellpadding="0" cellspacing="0" border="0"
						style="max-width:900px; background:#fff; border:1px solid #000; font-family:'Inter',Arial,sans-serif; color:#000;">

						<!-- ===== HEADER ===== -->
						<tr>
							<td style="padding:4px 8px;">
								<table width="100%" cellpadding="0" cellspacing="0" border="0">
									<tr>
										<!-- Logo + Brand -->
										<td align="left" valign="middle">
											<table cellpadding="0" cellspacing="0" border="0">
												<tr>
													<td valign="middle" width="35" style="padding:0;">
														<img src="https://bm.vemaybay.website/include/images/mail/logo-tcb-blue.png"
															alt="logo" height="40" width="40"
															style="display:block; width:40px; height:40px; max-width:40px; margin-left:-3px;" />
													</td>
													<td valign="middle" style="line-height:1.2; padding:0 0 0 2px;">
														<div
															style="font-size:14px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:2px;">
															{if $LANG == 'en'}TIM CHUYEN BAY{else}TÌM CHUYẾN BAY{/if}
														</div>
														<div style="font-size:10px; color:#333;">
															{if $LANG == 'en'}Find flights your way
															{else}Tìm chuyến bay theo
															cách của bạn{/if}
														</div>
													</td>
												</tr>
											</table>
										</td>
										<!-- Order Info -->
										<td align="right" valign="middle" style="line-height:1.2;">
											<div
												style="font-size:11px; text-transform:uppercase; color:#555; letter-spacing:1px;">
												{if $LANG == 'en'}Booking{else}Mã đơn hàng{/if}
											</div>
											<div style="font-size:11px; font-weight:800; letter-spacing:2px;">
												{$BOOKING_NUMBER}</div>
											<div style="font-size:11px; color:#555; margin-top:2px;">Tel: {$COM_TOP_PHONE}
											</div>
										</td>
									</tr>
								</table>
							</td>
						</tr>

						<!-- ===== ITINERARIES ===== -->
						{foreach from=$group.itineraries item=iti key=idx}

							{if $idx > 0}
								<!-- Dashed separator between itineraries -->
								<tr>
									<td style="padding:0 12px;">
										<div style="border-top:1px dashed #999;"></div>
									</td>
								</tr>
							{/if}

							<!-- Direction label -->
							<tr>
								<td
									style="background:#f0f0f0; padding:4px 12px; border-top:1px solid #ccc; border-bottom:1px solid #ccc;">
									<table width="100%" cellpadding="0" cellspacing="0" border="0">
										<tr>
											<td style="font-weight:700; font-size:12px; text-transform:uppercase;">
												{$iti.direction_label}</td>
										</tr>
									</table>
								</td>
							</tr>

							<!-- Flight info -->
							<tr>
								<td style="padding:8px 12px;">
									<table width="100%" cellpadding="0" cellspacing="0" border="0">
										<tr>
											<!-- Departure -->
											<td width="40%" align="center" valign="top">
												<div style="font-size:20px; font-weight:800; letter-spacing:2px;">
													{$iti.dep_code}</div>
												<div style="font-size:15px; color:#333; font-weight:500;">{$iti.dep_city}</div>
												<div style="font-size:12px; color:#666; margin-top:1px;">{$iti.dep_airport}
												</div>
												<div style="margin-top:2px; font-size:12px; font-weight:500; color:#555;">
													{$iti.dep_time} &nbsp;{$iti.dep_date}
												</div>
											</td>
											<!-- Arrow -->
											<td width="20%" align="center" valign="middle">
												<div style="font-size:10px; font-weight:600; color:#555;">{$iti.flight_number}
												</div>
												<div style="font-size:20px; line-height:1;">&#10132;</div>
												<div style="font-size:11px; font-weight:600; color:#555;">
													{$iti.airline}
												</div>
											</td>
											<!-- Arrival -->
											<td width="40%" align="center" valign="top">
												<div style="font-size:20px; font-weight:800; letter-spacing:2px;">
													{$iti.arr_code}</div>
												<div style="font-size:15px; color:#333; font-weight:500;">{$iti.arr_city}</div>
												<div style="font-size:12px; color:#666; margin-top:1px;">{$iti.arr_airport}
												</div>
												<div style="margin-top:2px; font-size:12px; font-weight:500; color:#555;">
													{$iti.arr_time} &nbsp;{$iti.arr_date}
												</div>
											</td>
										</tr>
									</table>
								</td>
							</tr>

						{/foreach}

						<!-- ===== PASSENGERS ===== -->
						{if $group.passengers|@count > 0}
							{assign var="hasEticket" value=false}
							{foreach from=$group.itineraries item=iti}
								{if $iti.airline_code == 'VN' || $iti.airline_code == 'VNA' || $iti.airline_code == 'QH' || $iti.airline_code == 'BBA'}
									{assign var="hasEticket" value=true}
								{/if}
							{/foreach}

							<!-- Passengers header -->
							<tr>
								<td
									style="background:#f0f0f0; padding:5px 12px; font-weight:700; font-size:12px; text-transform:uppercase; border-top:1px solid #000;">
									{if $LANG == 'en'}Passengers{else}Hành khách{/if}
								</td>
							</tr>

							{foreach from=$group.passengers item=pax key=pidx}
								<!-- Passenger {$pidx+1} -->
								<tr>
									<td style="padding:6px 12px;{if $pidx > 0} border-top:1px dashed #ccc;{/if}">
										<!-- Passenger name -->
										<div style="font-size:16px; font-weight:700; margin-bottom:4px;">
											{$pax.salutation} {$pax.name}
											{if $pax.type|lower != 'người lớn' && $pax.type|lower != 'adult' && $pax.type|lower != 'adt'}
												<span style="font-size:11px; color:#555; font-weight:400;">({$pax.type})</span>
											{/if}
										</div>
										<!-- Key-value rows -->
										<table width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size:12px;">
											{assign var="outboundBaggage" value=""}
											{if $pax.hand_baggage_outbound}
												{assign var="outboundBaggage" value="Xách tay: "|cat:$pax.hand_baggage_outbound}
											{/if}
											{if $pax.baggage_outbound}
												{if $outboundBaggage != ""}{assign var="outboundBaggage" value=$outboundBaggage|cat:", "}{/if}
												{assign var="outboundBaggage" value=$outboundBaggage|cat:"Ký gửi: "|cat:$pax.baggage_outbound}
											{elseif $pax.baggage && !$IS_ROUND_TRIP}
												{if $outboundBaggage != ""}{assign var="outboundBaggage" value=$outboundBaggage|cat:", "}{/if}
												{assign var="outboundBaggage" value=$outboundBaggage|cat:"Ký gửi: "|cat:$pax.baggage}
											{/if}

											{assign var="inboundBaggage" value=""}
											{if $pax.hand_baggage_inbound}
												{assign var="inboundBaggage" value="Xách tay: "|cat:$pax.hand_baggage_inbound}
											{/if}
											{if $pax.baggage_inbound}
												{if $inboundBaggage != ""}{assign var="inboundBaggage" value=$inboundBaggage|cat:", "}{/if}
												{assign var="inboundBaggage" value=$inboundBaggage|cat:"Ký gửi: "|cat:$pax.baggage_inbound}
											{elseif $pax.baggage && $IS_ROUND_TRIP}
												{if $inboundBaggage != ""}{assign var="inboundBaggage" value=$inboundBaggage|cat:", "}{/if}
												{assign var="inboundBaggage" value=$inboundBaggage|cat:"Ký gửi: "|cat:$pax.baggage}
											{/if}

											{assign var="isCombined" value=false}
											{if $IS_ROUND_TRIP}
												{assign var="actualPnrInbound" value=$pax.pnr}
												{if $pax.pnr_inbound}
													{assign var="actualPnrInbound" value=$pax.pnr_inbound}
												{/if}

												{assign var="actualEticketInbound" value=$pax.eticket_outbound}
												{if $pax.eticket_inbound}
													{assign var="actualEticketInbound" value=$pax.eticket_inbound}
												{/if}

												{assign var="baggageMatches" value=false}
												{if $outboundBaggage == $inboundBaggage || (!$outboundBaggage && $inboundBaggage) || ($outboundBaggage && !$inboundBaggage)}
													{assign var="baggageMatches" value=true}
												{/if}

												{if $pax.pnr == $actualPnrInbound && (!$hasEticket || $pax.eticket_outbound == $actualEticketInbound) && $baggageMatches}
													{assign var="isCombined" value=true}
												{/if}
											{else}
												{assign var="isCombined" value=true}
											{/if}

											<!-- Extract separated PNRs if they came in combined " / " -->
											{assign var="displayPnrOut" value=$pax.pnr}
											{assign var="displayPnrIn" value=$actualPnrInbound}

											{if $pax.pnr|strpos:"/" !== false}
												{assign var="pnrParts" value="/"|explode:$pax.pnr}
												{if $pnrParts|@count >= 2}
													{assign var="displayPnrOut" value=$pnrParts[0]|regex_replace:"/\(Lượt đi\) /":""|trim}
													{assign var="displayPnrIn" value=$pnrParts[1]|regex_replace:"/\(Lượt về\)/":""|trim}
												{/if}
											{/if}
											{if $actualPnrInbound|strpos:"/" !== false}
												{assign var="pnrPartsIn" value="/"|explode:$actualPnrInbound}
												{if $pnrPartsIn|@count >= 2}
													{assign var="displayPnrIn" value=$pnrPartsIn[1]|regex_replace:"/\(Lượt về\)/":""|trim}
												{/if}
											{/if}

											{if $isCombined}
												<!-- ROUND_TRIP COMBINED OR ONE-WAY -->
												{if $IS_ROUND_TRIP}
													<tr>
														<td width="35%" style="color:#555; padding:3px 0; font-weight:600;">
															{if $LANG == 'en'}Round-trip{else}Khứ hồi{/if}
														</td>
														<td style="padding:3px 0;"></td>
													</tr>
												{/if}
												<tr>
													<td
														style="color:#555; font-size:14px; padding:3px 0; width: 35%; {if $IS_ROUND_TRIP} padding-left:10px;{/if}">
														{if $LANG == 'en'}PNR{else}Mã đặt chỗ{/if}
													</td>
													<td style="font-weight:700; font-size:14px; padding:3px 0; width: 35%;">{$pax.pnr}
													</td>
												</tr>
												{if $outboundBaggage || $inboundBaggage}
													<tr>
														<td style="color:#555; padding:3px 0;{if $IS_ROUND_TRIP} padding-left:10px;{/if}">
															{if $LANG == 'en'}Baggage{else}Hành lý{/if}
														</td>
														<td style="padding:3px 0;">
															{if $outboundBaggage == $inboundBaggage}
																{$outboundBaggage}
															{elseif $outboundBaggage && !$inboundBaggage}
																{$outboundBaggage}
															{elseif !$outboundBaggage && $inboundBaggage}
																{$inboundBaggage}
															{else}
																<div style="margin-bottom:2px;">{if $LANG == 'en'}Outbound{else}Lượt đi{/if}:
																	{$outboundBaggage}</div>
																<div>{if $LANG == 'en'}Inbound{else}Lượt về{/if}: {$inboundBaggage}</div>
															{/if}
														</td>
													</tr>
												{/if}
											{else}
												<!-- DIFFERENT PNR/TICKET/BAGGAGE SEPARATED -->

												<!-- Compute baggage values BEFORE using them -->
												{assign var="finalOutboundBaggage" value=""}
												{if $outboundBaggage}
													{assign var="finalOutboundBaggage" value=$outboundBaggage}
													{if $outboundBaggage|strpos:"Lượt về:" !== false}
														{assign var="bgParts" value="Lượt về:"|explode:$outboundBaggage}
														{assign var="firstPart" value=$bgParts|@current}
														{assign var="finalOutboundBaggage" value=$firstPart|replace:"Lượt đi:":""|trim:"- "}
													{elseif $outboundBaggage|strpos:"(Lượt về)" !== false}
														{assign var="bgParts" value="(Lượt về)"|explode:$outboundBaggage}
														{assign var="firstPart" value=$bgParts|@current}
														{if $firstPart|strpos:"-" !== false}
															{assign var="bgParts2" value="-"|explode:$firstPart}
															{assign var="firstSubPart" value=$bgParts2|@current}
															{assign var="finalOutboundBaggage" value=$firstSubPart|replace:"(Lượt đi)":""|trim:"- "}
														{else}
															{assign var="finalOutboundBaggage" value=$firstPart|replace:"(Lượt đi)":""|trim:"- "}
														{/if}
													{else}
														{assign var="finalOutboundBaggage" value=$outboundBaggage|replace:"(Lượt đi)":""|replace:"Lượt đi:":""|trim:"- "}
													{/if}
												{/if}

												{assign var="finalInboundBaggage" value=""}
												{if $inboundBaggage}
													{assign var="finalInboundBaggage" value=$inboundBaggage}
													{if $inboundBaggage|strpos:"Lượt về:" !== false}
														{assign var="bgParts" value="Lượt về:"|explode:$inboundBaggage}
														{if $bgParts|@count >= 2}
															{assign var="secondPart" value=$bgParts[1]}
															{assign var="finalInboundBaggage" value=$secondPart|trim:"- "}
														{/if}
													{elseif $inboundBaggage|strpos:"(Lượt về)" !== false}
														{assign var="bgParts" value="-"|explode:$inboundBaggage}
														{if $bgParts|@count >= 2}
															{assign var="secondPart" value=$bgParts[1]}
															{assign var="finalInboundBaggage" value=$secondPart|replace:"(Lượt về)":""|trim:"- "}
														{else}
															{assign var="finalInboundBaggage" value=$inboundBaggage|replace:"(Lượt về)":""|trim:"- "}
														{/if}
													{else}
														{assign var="finalInboundBaggage" value=$inboundBaggage|replace:"Lượt về:":""|replace:"(Lượt về)":""|trim:"- "}
													{/if}
												{/if}

												<!-- Outbound -->
												<tr>
													<td width="35%" style="color:#555; padding:3px 0; font-weight:600;">
														{if $LANG == 'en'}Outbound{else}Lượt đi{/if}
													</td>
													<td style="padding:3px 0;"></td>
												</tr>
												<tr>
													<td
														style="color:#555; font-size:14px; padding:3px 0; padding-left:10px; width: 35%;">
														{if $LANG == 'en'}PNR{else}Mã đặt chỗ{/if}
													</td>
													<td style="font-weight:700; font-size:14px; padding:3px 0; width: 35%;">
														{$displayPnrOut}</td>
												</tr>
												{if $pax.hand_baggage_outbound}
													<tr>
														<td style="color:#555; padding:3px 0; padding-left:10px;">
															{if $LANG == 'en'}Carry-on{else}Xách tay{/if}
														</td>
														<td style="padding:3px 0;">{$pax.hand_baggage_outbound}</td>
													</tr>
												{/if}
												{if $pax.baggage_outbound}
													<tr>
														<td style="color:#555; padding:3px 0; padding-left:10px;">
															{if $LANG == 'en'}Checked baggage{else}Ký gửi{/if}
														</td>
														<td style="padding:3px 0;">{$pax.baggage_outbound}</td>
													</tr>
												{/if}

												<!-- Inbound -->
												{if $IS_ROUND_TRIP}
													<tr>
														<td style="color:#555; padding:5px 0 3px 0; font-weight:600;">
															{if $LANG == 'en'}Inbound{else}Lượt về{/if}
														</td>
														<td style="padding:5px 0 3px 0;"></td>
													</tr>
													<tr>
														<td style="color:#555; font-size:14px; padding:3px 0; padding-left:10px;">
															{if $LANG == 'en'}PNR{else}Mã đặt chỗ{/if}
														</td>
														<td style="font-weight:700; font-size:14px; padding:3px 0;">{$displayPnrIn}</td>
													</tr>
													{if $pax.hand_baggage_inbound}
														<tr>
															<td style="color:#555; padding:3px 0; padding-left:10px;">
																{if $LANG == 'en'}Carry-on{else}Xách tay{/if}
															</td>
															<td style="padding:3px 0;">{$pax.hand_baggage_inbound}</td>
														</tr>
													{/if}
													{if $pax.baggage_inbound}
														<tr>
															<td style="color:#555; padding:3px 0; padding-left:10px;">
																{if $LANG == 'en'}Checked baggage{else}Ký gửi{/if}
															</td>
															<td style="padding:3px 0;">{$pax.baggage_inbound}</td>
														</tr>
													{/if}
												{/if}
											{/if}
										</table>
									</td>
								</tr>
							{/foreach}
						{/if}

						<!-- ===== NOTES ===== -->
						<tr>
							<td style="padding:8px 12px; border-top:1px dashed #000;">
								<div style="font-weight:700; font-size:13px; margin-bottom:6px; text-transform:uppercase;">
									{if $LANG == 'en'}Important Notes{else}Lưu ý quan trọng{/if}
								</div>
								<ul
									style="margin:0; padding-left:18px; font-size:12px; line-height:1.6; color:#000; list-style-type:disc;">
									{if $LANG == 'en'}
										<li style="margin-bottom:4px;">Please verify all information carefully before heading to
											the airport. Original identification documents are required.</li>
										<li style="margin-bottom:4px;">Please arrive at the airport at least
											<strong>{$MINUTE_BEFORE} minutes</strong> before departure (during holidays 150-180
											minutes).
										</li>
										<li style="margin-bottom:4px;"><strong>Passengers aged 14+ must carry valid ID: National
												ID, valid passport, or Level 2 VNeID.</strong> Under 14: original birth
											certificate.</li>
										<li style="margin-bottom:4px;">Keep your phone on to receive updates from the airline or
											support staff.</li>
										<li style="margin-bottom:4px;">Promotional tickets are non-refundable and
											non-changeable. Any errors may result in ticket loss or change fees.</li>
										<li style="margin-bottom:4px;"><strong>Round-trip:</strong> If you skip the outbound
											flight, <strong>notify us before the first flight date</strong> to use the return.
										</li>
									{else}
										<li style="margin-bottom:4px;">Quý khách cần kiểm tra thông tin kỹ càng trước khi ra sân
											bay. Giấy tờ tùy thân phải là bản chính.</li>
										<li style="margin-bottom:4px;">Có mặt tại sân bay trước giờ khởi hành
											<strong>{$MINUTE_BEFORE} phút</strong> (Lễ, Tết trước 150-180 phút).
										</li>
										<li style="margin-bottom:4px;"><strong>Hành khách từ 14 tuổi trở lên phải có CCCD, hộ
												chiếu còn hạn hoặc VNeID mức độ 2.</strong> Dưới 14 tuổi: giấy khai sinh bản
											chính.</li>
										<li style="margin-bottom:4px;">Luôn mở điện thoại để nhận thông tin từ hãng hoặc nhân
											viên hỗ trợ.</li>
										<li style="margin-bottom:4px;">Vé khuyến mãi không hoàn đổi. Mọi sai sót đều dẫn đến mất
											vé hoặc phí đổi.</li>
										<li style="margin-bottom:4px;"><strong>Vé khứ hồi:</strong> Không bay chặng đi
											<strong>phải thông báo trước ngày bay đầu tiên</strong> để sử dụng chặng về.
										</li>
									{/if}
								</ul>
							</td>
						</tr>

						<!-- ===== FOOTER ===== -->
						<tr>
							<td style="padding:6px 12px; text-align:center; border-top:1px solid #ddd;">
								<div style="font-size:10px; line-height:1.6; color:#333;">
									<strong>{$COM_NAME}</strong>
									<br />
									{$COM_ADDRESS}
									<br />
									MST: {$COM_TAXCODE}
									<br />
									Tel: {$COM_PHONE}
									<br />
									Email: {$COM_EMAIL}
								</div>
							</td>
						</tr>

					</table>
					<!-- END MAIN CONTAINER -->

				</td>
			</tr>
		</table>
		<!-- END OUTER WRAPPER -->

	{/foreach}

</body>

</html>