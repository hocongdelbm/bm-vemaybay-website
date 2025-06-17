$(document).ready(function () {
	$(`.detail-view-row-item[data-field="BOOKINGS"] .label`).remove();
	$(`.detail-view-row-item[data-field="BOOKINGS"] .detail-view-field`).removeClass("col-8 col-10 col-sm-10");
	$(`.detail-view-row-item[data-field="BOOKINGS"] .detail-view-field`).addClass("col-12 col-sm-12");

	$('#active_pub_voucher').click(function () {
		let record_id = $(this).attr('record_id');

		if (record_id && record_id.length == 36) {
			$.ajax({
				type: "POST",
				url: "index.php?entryPoint=entryPointVoucher",
				data: {
					action: "release_to_website",
					record_id: record_id
				},
				beforeSend: function () {
					$('.container-waiting').show();
				},
				success: function (response) {
					$('.container-waiting').hide();

					if (response.length > 0) {
						let obj = JSON.parse(response);
						if (obj.error == 0) {
							showModalNotify(1, `Đã phát hành và kích hoạt voucher trên website`);
							$('#status').html('<b class="text-warning">Chờ sử dụng</b>');
							return true;
						}
						else {
							let m = obj.message ? obj.message : 'Phát hành thất bại';
							showModalNotify(0, m);
							return;
						}
					}
					else {
						showModalNotify(0, 'Phát hành thất bại, vui lòng thử lại sau');
						return;
					}
				},
				error: function (XMLHttpRequest, textStatus, errorThrown) {
					$('.container-waiting').hide();
					showModalNotify(0, 'Phát hành thất bại, vui lòng thử lại sau');
					console.error(XMLHttpRequest);
					console.error("Status: " + textStatus);
					console.error("Error: " + errorThrown);
				}
			});
		}
	})
});