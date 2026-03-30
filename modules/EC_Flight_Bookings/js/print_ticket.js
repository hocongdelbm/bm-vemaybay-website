$(document).ready(function () {
    var selectedItineraries = {};
    var selectedPassengers = {};

    $(document).on('change', '.check-itinerary', function () {
        var checkbox = $(this);
        var isChecked = checkbox.is(':checked');
        var itineraryId = checkbox.data('id');

        var row = checkbox.closest('tr');

        if (isChecked) {
            var rowData = {
                id: itineraryId,
                stt: row.find('td[data-label="STT"]').text().trim(),
                direction: row.find('td[data-label="Chiều"]').text().trim(),
                airline: row.find('td[data-label="Mã hãng"] img').attr('alt'),
                flightNumber: row.find('td[data-label="Số hiệu"]').text().trim(),
                ticketClass: row.find('td[data-label="Hạng vé"]').text().trim(),
                departure: row.find('td[data-label="Nơi đi"]').text().trim(),
                arrival: row.find('td[data-label="Nơi đến"]').text().trim(),
                departureDate: row.find('td[data-label="Ngày giờ đi"]').text().trim(),
                arrivalDate: row.find('td[data-label="Ngày giờ đến"]').text().trim(),
                bookingId: row.find('input[name="booking_id"]').val(),
                booking: row.find('input[name="booking"]').val(),
                contactEmail: row.find('input[name="contact_email"]').val(),
                contactName: row.find('input[name="contact_name"]').val(),
                directionCode: row.find('input[name="direction"]').val(),
                airlineCode: row.find('input[name="airline_code"]').val(),
                ticketType: row.find('input[name="ticket_type"]').val(),
                addType: row.find('input[name="add_type"]').val() || ''
            };

            selectedItineraries[itineraryId] = rowData;
            row.css('background-color', '#e3f2fd');

        } else {
            delete selectedItineraries[itineraryId];
            row.css('background-color', '');
        }
    });

    $(document).on('change', '.check-passenger', function () {
        var checkbox = $(this);
        var isChecked = checkbox.is(':checked');
        var passengerId = checkbox.data('id');
        var passengerType = checkbox.data('type');

        var row = checkbox.closest('tr.psg-line');

        var luggageRow = row.next('tr.psg-line.luggage');

        if (isChecked) {
            var rowData = {
                id: passengerId,
                type: passengerType,
                stt: row.find('td[data-label="STT"]').text().trim(),
                passengerType: row.find('td[data-label="Loại HK"]').text().trim(),
                salutation: row.find('td[data-label="Danh xưng"]').text().trim(),
                fullname: row.find('.fullname').text().trim(),
                birthdate: row.find('.birthdate').text().trim(),
                cic: row.find('.cic span').text().trim(),
                passport: row.find('.passport span').text().trim(),
                eticketOutbound: row.find('input[name="eticket_outbound[]"]').val(),
                eticketInbound: row.find('input[name="eticket_inbound[]"]').val(),
                pnrOutbound: row.find('input[name="pnr_outbound[]"]').val(),
                pnrInbound: row.find('input[name="pnr_inbound[]"]').val(),
                timesChange: row.data('times-change') || 0
            };

            if (luggageRow.length > 0) {
                var luggageData = {
                    outbound: '',
                    inbound: ''
                };

                // Structure 1: <div class="luggage__outbound"> with <span> label
                var outboundDiv = luggageRow.find('div.luggage__outbound');
                // Structure 2: <p> with <b>Lượt đi:</b> label (old code)
                var outboundLuggage = luggageRow.find('p:contains("Lượt đi:")').text();

                if (outboundDiv.length > 0) {
                    var outboundClone = outboundDiv.clone();
                    outboundClone.find('span').remove();
                    luggageData.outbound = outboundClone.text().replace(/^[\s:]+/, '').trim();
                } else if (outboundLuggage) {
                    luggageData.outbound = outboundLuggage.replace('Lượt đi:', '').trim();
                }

                // Structure 1: <div class="luggage__inbound"> with <span> label
                var inboundDiv = luggageRow.find('div.luggage__inbound');
                // Structure 2: <p> with <b>Lượt về:</b> label (old code)
                var inboundLuggage = luggageRow.find('p:contains("Lượt về:")').text();

                if (inboundDiv.length > 0) {
                    var inboundClone = inboundDiv.clone();
                    inboundClone.find('span').remove();
                    luggageData.inbound = inboundClone.text().replace(/^[\s:]+/, '').trim();
                } else if (inboundLuggage) {
                    luggageData.inbound = inboundLuggage.replace('Lượt về:', '').trim();
                }

                rowData.luggage = luggageData;
            }
            selectedPassengers[passengerId] = rowData;

            row.css('background-color', '#fff3cd');
            if (luggageRow.length > 0) {
                luggageRow.css('background-color', '#fff3cd');
            }

        } else {

            delete selectedPassengers[passengerId];

            row.css('background-color', '');
            if (luggageRow.length > 0) {
                luggageRow.css('background-color', '');
            }
        }
    });

    $('#select-all-passengers').on('change', function () {
        $('.check-passenger').prop('checked', $(this).is(':checked')).trigger('change');
    });

    var pendingPrintData = null;

    function showWarningToast(message, scrollTargetSelector) {
        var $toast = $('.toast-warning');

        $toast.find('.progress-bar').stop(true, true).css('width', '0%');

        $toast.addClass('active');
        $toast.find('#toast-content').text(message);
        $toast.find('.progress-bar').animate({ width: "100%" }, 3000);

        setTimeout(function () {
            $toast.removeClass('active');
        }, 4000);

        $('html, body').animate({
            scrollTop: $(scrollTargetSelector).offset().top
        }, 1000);
    }

    $(document).on('click', '.btnPrintEticket-selection, .btnSendEticket-selection', function () {

        var isPrint = $(this).hasClass('btnPrintEticket-selection');
        var actionType = isPrint ? 'print-selection' : 'send-selection';

        var passengers = Object.values(selectedPassengers);
        var itineraries = Object.values(selectedItineraries);

        if (passengers.length === 0) {
            showWarningToast('Vui lòng chọn hành khách', '#tbl_pax');
            return false;
        }

        if (itineraries.length === 0) {
            showWarningToast('Vui lòng chọn hành trình', '#itinerary_tbl');
            return false;
        }

        var firstItinerary = itineraries[0];

        var directions = itineraries.map(function (iti) { return iti.directionCode; });
        var isRoundTrip = (directions.includes('0') && directions.includes('1')) ? 1 : 0;

        pendingPrintData = {
            passengers: passengers,
            itineraries: itineraries,
            passengerIds: Object.keys(selectedPassengers).join(','),
            itineraryIds: Object.keys(selectedItineraries).join(','),
            // Booking Info
            bookingId: firstItinerary.bookingId,
            booking: firstItinerary.booking,
            contactEmail: firstItinerary.contactEmail,
            contactName: firstItinerary.contactName,
            khuhoi: isRoundTrip
        };

        $('input[name="ngonngu"]').prop('checked', false);
        $('#vn').prop('checked', true);
        $('#what_form').val(actionType);

        $('#dlgSelectLanguage').dialog({
            height: 'auto',
            width: 320,
            modal: true,
            resizable: false
        });
    });

    $(document).on('click', '#btnSelectLanguage', function () {
        var whatForm = $('#what_form').val();

        if (!['print-selection', 'send-selection'].includes(whatForm)) return;
        if (!pendingPrintData) {
            console.error('No pending data!');
            $('#dlgSelectLanguage').dialog('close');
            return;
        }

        var selectedLanguage = $('input[name="ngonngu"]:checked').val();

        submitEticketForm(whatForm, selectedLanguage);

        // Cleanup
        $('#dlgSelectLanguage').dialog('close');
        pendingPrintData = null;
        $('#what_form').val('');
    });

    function submitEticketForm(actionType, language) {
        if (!pendingPrintData) return;

        var isPrint = (actionType === 'print-selection');

        // Dynamic Form Configuration
        var formActionUrl = isPrint ? 'index.php?print=true' : 'index.php';
        var hiddenActionVal = isPrint ? 'printeticket' : 'sendeticket';

        var form = $('<form>', {
            'method': 'POST',
            'action': formActionUrl,
            'target': '_blank'
        });

        // Encode Data
        var passJSON = JSON.stringify(pendingPrintData.passengers);
        var itiJSON = JSON.stringify(pendingPrintData.itineraries);
        var passB64 = btoa(unescape(encodeURIComponent(passJSON)));
        var itiB64 = btoa(unescape(encodeURIComponent(itiJSON)));

        var fields = {
            'module': 'EC_Flight_Bookings',
            'action': hiddenActionVal,
            'lang': language,
            'khuhoi': pendingPrintData.khuhoi,
            'booking_id': pendingPrintData.bookingId,
            'booking': pendingPrintData.booking,
            'contact_email': pendingPrintData.contactEmail,
            'contact_name': pendingPrintData.contactName,
            'passengersData': passB64,
            'itinerariesData': itiB64,
            'listPassengers': pendingPrintData.passengerIds,
            'listItineraries': pendingPrintData.itineraryIds
        };

        $.each(fields, function (name, value) {
            $('<input>').attr({
                type: 'hidden',
                name: name,
                value: value
            }).appendTo(form);
        });

        $('body').append(form);
        form.submit();
        form.remove();
    }
});