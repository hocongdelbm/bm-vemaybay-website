/*========== GOM PNR ==========*/
$(document).ready(function() {
    const url = "index.php?entryPoint=entryPointAPIVietjet";
    const id_modal_confirm_payment_pnr = '#modal-confirm-payment-pnr';
    const id_modal_payment_success = '#modal-payment-pnr-success';

    // Payment with PNR
    $('#btn-payment-pnr').click(function() {
        let pnr = $('#pnr').html();
        $(id_modal_confirm_payment_pnr + ' #pnr-payment-pnr').html(pnr);
        showModal(id_modal_confirm_payment_pnr);
    });
    $('#confirm-payment-pnr').click(function() {
        let supplier_id             = $('#supplier_id').val();
        let reservation_key         = $('#reservation_key').html();
        let total_amount            = parseInt($('#charges').attr("data"));
        let total_amount_processing = parseInt($('#payments').attr("data"));
        let pnr                     = $('#pnr').html();

        if (reservation_key.length == 0) {
            showModalNotify('error', 'Không thể thực hiện thao tác');
            return;
        }
        else if(total_amount <= total_amount_processing) {
            showModalNotify('error', 'Booking đã hoàn tất thanh toán');
            return;
        }

        $.ajax({
            type: 'POST',
            url: url,
            data: {
                action: 'pay',
                supplier_id : supplier_id,
                reservation_key : reservation_key,
                total_amount : total_amount,
                total_amount_processing : total_amount_processing,
                pnr : pnr,
            },
            beforeSend: function () {
                $('.container-waiting').show();
            },
            success: function(res) {
                data = JSON.parse(res);
                $('.container-waiting').hide();

                if (data['error'] === true) {
                    showModalNotify('error', data['message'], data.response);
                    return;
                }

                $('#pnrtt').html($('#pnr-payment-pnr').html());
                $('#khoatt').html(data['data']['key']);
                $('#bienlaitt').html(data['data']['receiptNumber']);
                $('#ngaytt').html(data['data']['paymentTime']);
                $('#phuongthuctt').html(data['data']['paymentMethod']);
                $('#motatt').html(data['data']['payerDescription']);
                $('#ghichutt').html(data['data']['notes']);

                // Update info
                let icon = '<svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#28a745" stroke-width="1.5"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 1.25C6.06294 1.25 1.25 6.06294 1.25 12C1.25 17.9371 6.06294 22.75 12 22.75C17.9371 22.75 22.75 17.9371 22.75 12C22.75 6.06294 17.9371 1.25 12 1.25ZM7.53044 11.9697C7.23755 11.6768 6.76268 11.6768 6.46978 11.9697C6.17689 12.2626 6.17689 12.7374 6.46978 13.0303L9.46978 16.0303C9.76268 16.3232 10.2376 16.3232 10.5304 16.0303L17.5304 9.03033C17.8233 8.73744 17.8233 8.26256 17.5304 7.96967C17.2375 7.67678 16.7627 7.67678 16.4698 7.96967L10.0001 14.4393L7.53044 11.9697Z" fill="#28a745"></path></svg>';
                $("#status_pnr").html(`<span style="margin-right:3px; color:#28a745">Đã thanh toán</span>${icon}`);
                $("#btn-payment-pnr").hide();
                $("#payments").html($("#charges").html());
                $("#payments").attr("data", total_amount);
                $("#balance_agency").html(data['data']['creditAvailable']); // Update số dư

                showModal(id_modal_payment_success);
                return;
            }
        });
    });

    $("#export-info-payment").click(function() {
        let pnrtt        = $("#pnrtt").html();
        let khoatt       = $("#khoatt").html();
        let bienlaitt    = $("#bienlaitt").html();
        let ngaytt       = $("#ngaytt").html();
        let phuongthuctt = $("#phuongthuctt").html();
        let motatt       = $("#motatt").html();
        let ghichutt     = $("#ghichutt").html();

        var data = 'PNR\t:\t' + pnrtt + '\n';
        data += 'Khoa TT\t:\t' + khoatt + '\n';
        data += 'Bien lai TT\t:\t' + bienlaitt + '\n';
        data += 'Ngay TT\t:\t' + ngaytt + '\n';
        data += 'Phuong thuc TT\t:\t' + phuongthuctt + '\n';
        data += 'Mo ta TT\t:\t' + motatt + '\n';
        data += 'Ghi chu TT\t:\t' + ghichutt;

        var filename = "payment_" + pnrtt + ".txt";

        download(data, filename);
    });
});