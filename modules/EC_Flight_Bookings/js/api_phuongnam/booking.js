const ENDPOINT_AUTO_BOOK = "index.php?entryPoint=entryPointAutoBook";

$(document).ready(function () {
    let booking_id = $(`#formDetailView input[name="record"]`).val();
    let ticket_type = $(`input[name="ticket_type"]`).val(); // '1':Domestic ; '2':International

    $("#btn-auto-book").click(function () {
        let list_journey_id = [];
        $('#itinerary_tbl input[name=check-journey]:checked').each(function () {
            let journey_id = $(this).attr("journey-id");
            if (journey_id !== undefined && journey_id.length > 30) list_journey_id.push(journey_id);
        });

        let list_passenger_id = [];
        $('#tbl_pax input[name=check-passenger]:checked').each(function () {
            let pass_id = $(this).attr("passenger-id");
            if (pass_id !== undefined && pass_id.length > 30) list_passenger_id.push(pass_id);
        });

        if(booking_id.length * list_journey_id.length * list_passenger_id.length != 0) {
            $.ajax({
                url: ENDPOINT_AUTO_BOOK,
                type: "POST",
                data: {
                    action: "get_info_to_auto_book",
                    booking_id: booking_id,
                    list_journey_id: list_journey_id,
                    list_passenger_id: list_passenger_id
                },
                beforeSend: function () {
                    $('.container-waiting').show();
                },
                success: function (response) {
                    try {
                        $('.container-waiting').hide();
                        const objData = JSON.parse(response);
                        console.log(objData);
                    }
                    catch (e) {
                        console.error("JSON parse error:", e);
                        console.error("Response was:", response);
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $('.container-waiting').hide();
                    console.error(XMLHttpRequest);
                    console.error("Status: " + textStatus);
                    console.error("Error: " + errorThrown);
                }
            });
        }

        if(!list_journey_id.length) showToastNotify("warning", "Vui lòng chọn hành trình");
        else if(!list_passenger_id.length) showToastNotify("warning", "Vui lòng chọn hành khách");
    });
});