$(document).ready(function () {
    $('.btn-view-detail').click(function () {
        let contact_id = $(this).attr('data-id');

        if (contact_id.length > 0) {
            $.ajax({
                cache: false,
                type: 'post',
                data: {
                    contact_id: contact_id,
                    for: "showHistoryBookingContact"
                },
                async: false,
                url: 'index.php?entryPoint=entryPointFlightBookings',
                success: function (output) {
                    $('#dialog-history-bookings').html(output);
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    console.error(XMLHttpRequest);
                    console.error("Status: " + textStatus);
                    console.error("Error: " + errorThrown);
                }
            });
        }
    });

    // Open popup refund confirm
    $('.btn-refund-point').click(function () {
        let record = $(this).attr('record');
        let refund_point = parseInt($(this).attr('refund_point'));
        let parent_type = $(this).attr('parent_type');
        let parent_id   = $(this).attr('parent_id');
        let parent_name = $(this).attr('parent_name');

        if (record && record.length == 36 && refund_point > 0) {
            $('#modalConfirmRefund #refund_point').text(refund_point);
            $('#modalConfirmRefund #refund_reference').text(parent_name);
            $('#modalConfirmRefund #refund_reference').attr('href', `index.php?module=${parent_type}&action=DetailView&record=${parent_id}`);
            $('#modalConfirmRefund #btn-confirm-refund-point').attr('record', record);
            $('#modalConfirmRefund #refund_reason').val('Hoàn điểm đã sử dụng');
        }
        else {
            $('#modalConfirmRefund #refund_point').text('');
            $('#modalConfirmRefund #refund_reference').text('');
            $('#modalConfirmRefund #refund_reference').attr('href', '#');
            $('#modalConfirmRefund #btn-confirm-refund-point').attr('record', '');
            $('#modalConfirmRefund #refund_reason').val('');
        }
    });
    $('#btn-confirm-refund-point').click(function () {
        let record = $(this).attr('record');
        let contact_id = $(this).attr('contact_id');
        let reason = $('#refund_reason').val();

        if(record && contact_id && record.length == 36 && contact_id.length == 36) {
            $('#modalConfirmRefund button.btn-close').trigger('click');
            $.ajax({
                url: 'index.php?entryPoint=entryPointFlightBookings',
                type: 'POST',
                cache: false,
                data: {
                    for: "refund_points",
                    contact_id: contact_id,
                    parent_id: record,
                    reason: reason
                },
                beforeSend: function() {
                    $('.container-waiting').show();
                },
                success: function (response) {
                    $('.container-waiting').hide();

                    let res = JSON.parse(response);
                    if (res['error'] ==- 0) showModalNotify(1, 'Hoàn điểm thành công');
                    else showModalNotify(0, 'Hoàn điểm thất bại', res['message']);
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $('.container-waiting').hide();
                    console.error(XMLHttpRequest);
                    console.error("Status: " + textStatus);
                    console.error("Error: " + errorThrown);
                }
            });
        }
    });
});
