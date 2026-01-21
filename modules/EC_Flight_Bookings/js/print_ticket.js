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

                var outboundLuggage = luggageRow.find('p:contains("Lượt đi:")').text();
                if (outboundLuggage) {
                    luggageData.outbound = outboundLuggage.replace('Lượt đi:', '').trim();
                }

                var inboundLuggage = luggageRow.find('p:contains("Lượt về:")').text();
                if (inboundLuggage) {
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

    var pendingPrintData = null;

    $(document).on('click', '.btnPrintEticket-selection', function () {

        var passengerCount = Object.keys(selectedPassengers).length;
        var itineraryCount = Object.keys(selectedItineraries).length;

        if (passengerCount === 0 || itineraryCount === 0) {
            $('.toast-warning').addClass('active');
            $('.toast-warning #toast-content').text('Vui lòng chọn hành khách và hành trình');
            $('.toast-warning .progress-bar').animate({ width: "100%" }, 3000);
            setTimeout(function () {
                $(".toast-warning").removeClass('active');
            }, 4000);

            $('html, body').animate({
                scrollTop: $("#tbl_pax").offset().top
            }, 1000);
            return false;
        }

        pendingPrintData = {
            passengers: Object.values(selectedPassengers),
            itineraries: Object.values(selectedItineraries),
            passengerIds: Object.keys(selectedPassengers).join(','),
            itineraryIds: Object.keys(selectedItineraries).join(',')
        };


        var firstItinerary = Object.values(selectedItineraries)[0];
        if (firstItinerary) {
            pendingPrintData.bookingId = firstItinerary.bookingId;
            pendingPrintData.booking = firstItinerary.booking;
            pendingPrintData.contactEmail = firstItinerary.contactEmail;
            pendingPrintData.contactName = firstItinerary.contactName;

            var directions = Object.values(selectedItineraries).map(function (iti) {
                return iti.directionCode;
            });
            pendingPrintData.khuhoi = (directions.includes('0') && directions.includes('1')) ? 1 : 0;
        }

        $('input[name="ngonngu"]').prop('checked', false);
        $('#vn').prop('checked', true);

        $('#what_form').val('print-selection');

        $('#dlgSelectLanguage').dialog({
            height: 'auto',
            width: 320,
            modal: true,
            resizable: false
        });
    });

    $(document).on('click', '#btnSelectLanguage', function () {
        var whatForm = $('#what_form').val();

        if (whatForm !== 'print-selection') {
            return;
        }

        if (!pendingPrintData) {
            console.error('No pending print data!');
            $('#dlgSelectLanguage').dialog('close');
            return;
        }

        var selectedLanguage = $('input[name="ngonngu"]:checked').val();

        submitPrintForm(selectedLanguage);

        $('#dlgSelectLanguage').dialog('close');

        pendingPrintData = null;
        $('#what_form').val('');
    });

    function submitPrintForm(language) {
        if (!pendingPrintData) {
            console.error('No pending print data!');
            return;
        }
        var form = $('<form>', {
            'method': 'POST',
            'action': 'index.php?print=true',
            'target': '_blank'
        });

        form.append($('<input>', {
            'type': 'hidden',
            'name': 'module',
            'value': 'EC_Flight_Bookings'
        }));

        form.append($('<input>', {
            'type': 'hidden',
            'name': 'action',
            'value': 'printeticket'
        }));

        form.append($('<input>', {
            'type': 'hidden',
            'name': 'lang',
            'value': language
        }));

        form.append($('<input>', {
            'type': 'hidden',
            'name': 'khuhoi',
            'value': pendingPrintData.khuhoi
        }));

        form.append($('<input>', {
            'type': 'hidden',
            'name': 'booking_id',
            'value': pendingPrintData.bookingId
        }));

        form.append($('<input>', {
            'type': 'hidden',
            'name': 'booking',
            'value': pendingPrintData.booking
        }));

        form.append($('<input>', {
            'type': 'hidden',
            'name': 'contact_email',
            'value': pendingPrintData.contactEmail
        }));

        form.append($('<input>', {
            'type': 'hidden',
            'name': 'contact_name',
            'value': pendingPrintData.contactName
        }));

        var passengersJSON = JSON.stringify(pendingPrintData.passengers);
        var itinerariesJSON = JSON.stringify(pendingPrintData.itineraries);

        var passengersBase64 = btoa(unescape(encodeURIComponent(passengersJSON)));
        var itinerariesBase64 = btoa(unescape(encodeURIComponent(itinerariesJSON)));

        form.append($('<input>', {
            'type': 'hidden',
            'name': 'passengersData',
            'value': passengersBase64
        }));

        form.append($('<input>', {
            'type': 'hidden',
            'name': 'itinerariesData',
            'value': itinerariesBase64
        }));

        form.append($('<input>', {
            'type': 'hidden',
            'name': 'listPassengers',
            'value': pendingPrintData.passengerIds
        }));

        form.append($('<input>', {
            'type': 'hidden',
            'name': 'listItineraries',
            'value': pendingPrintData.itineraryIds
        }));

        $('body').append(form);
        form.submit();
        form.remove();
    }
});