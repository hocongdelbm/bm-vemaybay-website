$(document).ready(function () {

    // Click "In vé mới" button → open popup
    $(document).on('click', '.btnPrintEticketNew', function () {
        var $popup = $('#dlgPrintTicketNew');
        var $passengerList = $popup.find('.popup-passenger-list');
        var $itineraryList = $popup.find('.popup-itinerary-list');

        // Clear previous
        $passengerList.empty();
        $itineraryList.empty();

        // Populate passengers from page
        // Deduplicate by fullname — last row wins (latest change version)
        var psgByName = {};
        var psgOrder = [];
        $('#tbl_pax tr.psg-line:not(.luggage)').each(function () {
            var $row = $(this);
            var passId = $row.find('.check-passenger').data('id');
            var passType = $row.find('.check-passenger').data('type');
            var fullname = $row.find('.fullname').text().trim();
            var salutation = $row.find('td[data-label="Danh xưng"]').text().trim();

            if (!passId || !fullname) return;

            // Luggage info
            var $luggageRow = $row.next('tr.psg-line.luggage');
            var luggageText = '';
            if ($luggageRow.length > 0) {
                luggageText = $luggageRow.find('td').text().trim().substring(0, 50);
                if ($luggageRow.find('td').text().trim().length > 50) luggageText += '...';
            }

            var label = salutation + ' ' + fullname;
            if (luggageText) label += ' <small style="color:#888;">(' + luggageText + ')</small>';

            // Overwrite per name — last one wins (= latest version)
            if (!psgByName[fullname]) psgOrder.push(fullname);
            psgByName[fullname] = { passId: passId, passType: passType, label: label };
        });

        // Render deduplicated passengers (in original order)
        for (var k = 0; k < psgOrder.length; k++) {
            var psg = psgByName[psgOrder[k]];
            $passengerList.append(
                '<label style="display:flex; align-items:center; gap:8px; padding:6px 0; cursor:pointer; border-bottom:1px solid #f0f0f0;">' +
                '<input type="checkbox" class="popup-check-psg" value="' + psg.passId + '" data-type="' + psg.passType + '" checked style="width:16px;height:16px;cursor:pointer;" />' +
                '<span>' + psg.label + '</span>' +
                '</label>'
            );
        }

        // Populate itineraries from data embedded by PHP
        // This ensures rescheduled versions (add_type = 3) are preferred over originals
        try {
            var itinerariesData = $popup.attr('data-itineraries');
            var bookingId = $popup.attr('data-booking-id') || '';
            var booking = $popup.attr('data-booking') || '';
            var ticketType = $popup.attr('data-ticket-type') || '';
            
            if (itinerariesData) {
                var itineraries = JSON.parse(itinerariesData);
                for (var i = 0; i < itineraries.length; i++) {
                    var iti = itineraries[i];
                    
                    var airlineImg = '';
                    if (iti.airline) {
                        airlineImg = '<img style="width:45px" src="custom/themes/default/images/airline-icon-100x100/' + iti.airline + '.png" alt="' + iti.airline + '" border="0" />';
                    }
                    
                    var label = '<strong>' + iti.directionLabel + '</strong> &nbsp; ' + airlineImg + ' <span style="font-weight: 500;">' + iti.flightNo + '</span> &nbsp; ' + iti.departure + ' → ' + iti.arrival + ' &nbsp; ' + iti.depDate;

                    $itineraryList.append(
                        '<label style="display:flex; align-items:center; gap:8px; padding:6px 0; cursor:pointer; border-bottom:1px solid #f0f0f0;">' +
                        '<input type="checkbox" class="popup-check-iti" value="' + iti.id + '" data-direction="' + iti.direction + '" data-ticket-type="' + ticketType + '" data-booking-id="' + bookingId + '" data-booking="' + booking + '" checked style="width:16px;height:16px;cursor:pointer;" />' +
                        '<span>' + label + '</span>' +
                        '</label>'
                    );
                }
            }
        } catch (e) {
            console.error('Lỗi parse data-itineraries:', e);
        }

        // Open dialog
        $popup.dialog({
            height: 'auto',
            width: 560,
            maxHeight: $(window).height() * 0.85,
            modal: true,
            resizable: false,
            title: 'In vé mới - Chọn thông tin'
        });
    });

    // Select all passengers
    $(document).on('change', '#popup-select-all-psg', function () {
        $('.popup-check-psg').prop('checked', $(this).is(':checked'));
    });

    // Select all itineraries
    $(document).on('change', '#popup-select-all-iti', function () {
        $('.popup-check-iti').prop('checked', $(this).is(':checked'));
    });

    // Submit → open GET URL in new tab
    $(document).on('click', '#btnSubmitPrintNew', function () {
        var passengers = [];
        var itineraries = [];

        $('.popup-check-psg:checked').each(function () {
            passengers.push($(this).val());
        });

        $('.popup-check-iti:checked').each(function () {
            itineraries.push($(this).val());
        });

        if (passengers.length === 0) {
            alert('Vui lòng chọn ít nhất 1 hành khách');
            return;
        }

        if (itineraries.length === 0) {
            alert('Vui lòng chọn ít nhất 1 hành trình');
            return;
        }

        var lang = $('input[name="popup_ngonngu"]:checked').val() || 'vn';

        // Determine round trip
        var directions = [];
        $('.popup-check-iti:checked').each(function () {
            var d = $(this).data('direction');
            if (d !== undefined && d !== '') directions.push(String(d));
        });
        var khuhoi = (directions.indexOf('0') !== -1 && directions.indexOf('1') !== -1) ? 1 : 0;

        // Get booking info from first itinerary
        var $firstIti = $('.popup-check-iti:checked').first();
        var bookingId = $firstIti.data('booking-id') || '';
        var booking = $firstIti.data('booking') || '';
        var ticketType = $firstIti.data('ticket-type') || '';

        // If all are selected, use "All" to keep URL short
        var totalPsg = $('.popup-check-psg').length;
        var totalIti = $('.popup-check-iti').length;
        var passengersParam = (passengers.length === totalPsg) ? 'All' : passengers.join(',');
        var itinerariesParam = (itineraries.length === totalIti) ? 'All' : itineraries.join(',');

        // Build GET URL
        var url = 'index.php?print=true'
            + '&module=EC_Flight_Bookings'
            + '&action=printeticketnew'
            + '&record=' + bookingId
            + '&booking_id=' + bookingId
            + '&booking=' + encodeURIComponent(booking)
            + '&lang=' + lang
            + '&khuhoi=' + khuhoi
            + '&ticket_type=' + ticketType
            + '&passengers=' + passengersParam
            + '&itineraries=' + itinerariesParam;

        window.open(url, '_blank');

        // Close dialog
        $('#dlgPrintTicketNew').dialog('close');
    });
});
