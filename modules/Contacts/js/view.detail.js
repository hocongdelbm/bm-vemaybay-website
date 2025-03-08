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

});
