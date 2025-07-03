$(document).ready(function () {
    // Nút lên lịch gửi tin nhắn hàng loạt theo file danh sách (SMS)
    $(document).on('click', '#btn-confirm-confirm-schedule', function () {
        let record_id   = $('input[name="record"]').val();
        let type        = $('input[name="sms_type"]').val();
        let message     = $('span#content').text();
        let send_time   = $('span#send_time').text();

        if(!message || message.length < 10) {
            showModalNotify(0, "Nội dung tin nhắn không hợp lệ");
            return false;
        }
        if(!send_time || send_time.length == 0) {
            showModalNotify(0, "Thời gian gửi tin không hợp lệ");
            return false;
        }

        if(record_id && record_id.length > 0) {
            closeDialog('dialog-confirm-schedule');
            $('.container-waiting').show();

            $.ajax({
                url: "index.php?entryPoint=entryPointSMS",
                data: {
                    type        : type,
                    record_id   : record_id
                },
                type: "POST",
                cache: false,
                success: function (response) {
                    $('.container-waiting').hide();

                    let data = JSON.parse(response);
                    if(data.Status == 1) {
                        showModalNotify(1, "Đã lên lịch");
                        $('.modal-overlay, .btn-modal-close').addClass('reload');
                    }
                    else if(data.Description) showModalNotify(0, data.Description);
                    else showModalNotify(0, "Lên lịch thất bại");
                }
            });
        }
    });

    // Nút lên lịch gửi tin nhắn quảng cáo theo điều kiện lọc (Zalo)
    $(document).on('click', '#btn-confirm-broadcast', function () {
        let record_id = $('input[name="record"]').val();

        if(record_id && record_id.length > 0) {
            closeDialog('dialog-confirm-schedule');
            $.ajax({
                url: "index.php?entryPoint=entryPointZalo",
                data: {
                    action    : 'send_broadcast',
                    record_id : record_id
                },
                type: "POST",
                cache: false,
                success: function (response) {
                    let data = JSON.parse(response);

                    if(data.error == 0) {
                        showModalNotify(1, 'Gửi thành công');
                        $('.modal-overlay, .btn-modal-close').addClass('reload');
                    }
                    else showModalNotify(0, 'Gửi thất bại');
                }
            });
        }
    });

    // Xem chi tiết promotion
	$("#btn-view-promotion").on("click", function () {
        $("#dialog-view-promotion").dialog({
			title: "Voucher khuyến mãi",
			width: 400,
			modal: true,
			resizable: false,
		});
    })
});