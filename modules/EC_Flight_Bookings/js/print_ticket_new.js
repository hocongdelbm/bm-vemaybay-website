$(document).ready(function () {

    // Click "In vé mới" button → open popup
    $(document).on('click', '.btnPrintEticketNew', function () {
        var $popup = $('#dlgPrintTicketNew');
        var hasPerPaxChanges = $popup.attr('data-has-per-pax-changes') === '1';
        var bookingId = $popup.attr('data-booking-id') || '';
        var booking = $popup.attr('data-booking') || '';
        var ticketType = $popup.attr('data-ticket-type') || '';

        if (hasPerPaxChanges) {
            // ===== PER-PASSENGER LAYOUT =====
            // Hide flat sections, show nested section
            $popup.find('.popup-itinerary-section').hide();
            $popup.find('.popup-passenger-section').hide();
            $popup.find('.popup-perpax-section').show();

            var $perpaxList = $popup.find('.popup-perpax-list');
            $perpaxList.empty();

            // Add "Select All" checkbox at the top
            $perpaxList.append(
                '<div style="margin-bottom:16px; padding:12px; background:#e3f2fd; border:2px solid #2196f3; border-radius:8px;">' +
                    '<label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:600; color:#1976d2;">' +
                        '<input type="checkbox" id="popup-select-all-perpax" style="width:18px;height:18px;cursor:pointer;" />' +
                        '<span>✅ Chọn tất cả hành khách (in tất cả theo nhóm hành trình)</span>' +
                    '</label>' +
                '</div>'
            );

            try {
                var perPaxData = JSON.parse($popup.attr('data-per-pax-itineraries') || '[]');

                for (var p = 0; p < perPaxData.length; p++) {
                    var pax = perPaxData[p];
                    var paxLabel = pax.salutation + ' ' + pax.passengerName;
                    var isChecked = p === 0 ? 'checked' : ''; // Select first passenger by default

                    // Passenger block
                    var $paxBlock = $(
                        '<div class="popup-perpax-block" style="margin-bottom:12px; border:1px solid #e8e8e8; border-radius:8px; background:#fff; overflow:hidden;">' +
                            '<label style="display:flex; align-items:center; gap:8px; padding:10px 12px; cursor:pointer; background:#f5f5f5; border-bottom:1px solid #e8e8e8; font-weight:600;">' +
                                '<input type="radio" name="popup_perpax_radio" class="popup-perpax-radio" value="' + pax.passengerId + '" data-type="' + pax.type + '" ' + isChecked + ' style="width:16px;height:16px;cursor:pointer;" />' +
                                '<span>👤 ' + paxLabel + '</span>' +
                            '</label>' +
                            '<div class="popup-perpax-itineraries" style="padding:6px 12px 8px;"></div>' +
                        '</div>'
                    );

                    // Itineraries for this passenger
                    var $itiContainer = $paxBlock.find('.popup-perpax-itineraries');
                    for (var t = 0; t < pax.itineraries.length; t++) {
                        var iti = pax.itineraries[t];
                        var airlineImg = '';
                        if (iti.airline) {
                            airlineImg = '<img style="width:35px; vertical-align:middle;" src="custom/themes/default/images/airline-icon-100x100/' + iti.airline + '.png" alt="' + iti.airline + '" border="0" /> ';
                        }
                        var itiLabel = '<strong>' + iti.directionLabel + '</strong> &nbsp; ' + airlineImg + '<span style="font-weight:500;">' + iti.flightNo + '</span> &nbsp; ' + iti.departure + ' → ' + iti.arrival + ' &nbsp; ' + iti.depDate;
                        // For the selected passenger check by default, else uncheck
                        var itiChecked = p === 0 ? 'checked' : ''; 

                        $itiContainer.append(
                            '<label style="display:flex; align-items:center; gap:8px; padding:5px 0; cursor:pointer; border-bottom:1px solid #f0f0f0; font-size:13px;">' +
                                '<input type="checkbox" class="popup-perpax-iti-check" value="' + iti.id + '" data-direction="' + iti.direction + '" data-pax-id="' + pax.passengerId + '" ' + itiChecked + ' style="width:14px;height:14px;cursor:pointer;" />' +
                                '<span>' + itiLabel + '</span>' +
                            '</label>'
                        );
                    }

                    $perpaxList.append($paxBlock);
                }
            } catch (e) {
                console.error('Lỗi parse data-per-pax-itineraries:', e);
            }
        } else {
            // ===== FLAT LAYOUT (unchanged) =====
            $popup.find('.popup-itinerary-section').show();
            $popup.find('.popup-passenger-section').show();
            $popup.find('.popup-perpax-section').hide();

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
            try {
                var itinerariesData = $popup.attr('data-itineraries');
                
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
        }

        // Open dialog
        $popup.dialog({
            height: 650,
            width: 760,
            modal: true,
            resizable: false,
            title: 'In vé mới - Chọn thông tin'
        });
    });

    // Select all passengers (flat mode)
    $(document).on('change', '#popup-select-all-psg', function () {
        $('.popup-check-psg').prop('checked', $(this).is(':checked'));
    });

    // Select all itineraries (flat mode)
    $(document).on('change', '#popup-select-all-iti', function () {
        $('.popup-check-iti').prop('checked', $(this).is(':checked'));
    });

    // Per-passenger radio: cascade select itineraries, unselect others
    $(document).on('change', '.popup-perpax-radio', function () {
        var $popup = $('#dlgPrintTicketNew');
        
        // Uncheck all child itineraries
        $popup.find('.popup-perpax-iti-check').prop('checked', false);
        
        // Check itineraries only for the selected passenger
        $(this).closest('.popup-perpax-block').find('.popup-perpax-iti-check').prop('checked', true);
    });

    // Select all per-passenger: disable radios and itinerary checkboxes
    $(document).on('change', '#popup-select-all-perpax', function () {
        var isChecked = $(this).is(':checked');
        
        if (isChecked) {
            // Disable all radios and itinerary checkboxes
            $('.popup-perpax-radio').prop('disabled', true).prop('checked', false);
            $('.popup-perpax-iti-check').prop('disabled', true).prop('checked', false);
            
            // Visual feedback - grey out blocks
            $('.popup-perpax-block').css('opacity', '0.5');
        } else {
            // Re-enable radios and itinerary checkboxes
            $('.popup-perpax-radio').prop('disabled', false);
            $('.popup-perpax-iti-check').prop('disabled', false);
            
            // Restore visual
            $('.popup-perpax-block').css('opacity', '1');
            
            // Auto-select first passenger
            $('.popup-perpax-radio').first().prop('checked', true).trigger('change');
        }
    });

    // Submit → open GET URL in new tab
    $(document).on('click', '#btnSubmitPrintNew', function () {
        var $popup = $('#dlgPrintTicketNew');
        var hasPerPaxChanges = $popup.attr('data-has-per-pax-changes') === '1';
        var passengers = [];
        var itineraries = [];
        var bookingId = $popup.attr('data-booking-id') || '';
        var booking = $popup.attr('data-booking') || '';
        var ticketType = $popup.attr('data-ticket-type') || '';

        if (hasPerPaxChanges) {
            // Check if "Select All" is enabled
            var isSelectAllPerpax = $('#popup-select-all-perpax').is(':checked');
            
            if (isSelectAllPerpax) {
                // Send "All" for both passengers and itineraries
                passengers = ['All'];
                itineraries = ['All'];
            } else {
                // Collect from per-passenger layout (radio)
                var $selectedPax = $('.popup-perpax-radio:checked');
                if ($selectedPax.length > 0) {
                    passengers.push($selectedPax.val());
                    
                    // Only collect itineraries belonging to the selected passenger block
                    $selectedPax.closest('.popup-perpax-block').find('.popup-perpax-iti-check:checked').each(function () {
                        itineraries.push($(this).val());
                    });
                }
            }
        } else {
            // Collect from flat layout
            $('.popup-check-psg:checked').each(function () {
                passengers.push($(this).val());
            });
            $('.popup-check-iti:checked').each(function () {
                itineraries.push($(this).val());
            });
        }

        if (passengers.length === 0) {
            alert('Vui lòng chọn 1 hành khách');
            return;
        }

        if (itineraries.length === 0) {
            alert('Vui lòng chọn ít nhất 1 hành trình');
            return;
        }

        var lang = $('input[name="popup_ngonngu"]:checked').val() || 'vn';

        // Determine round trip from selected itineraries
        var directions = [];
        if (hasPerPaxChanges) {
            if ($('#popup-select-all-perpax').is(':checked')) {
                $('.popup-perpax-iti-check').each(function () {
                    var d = $(this).data('direction');
                    if (d !== undefined && d !== '') directions.push(String(d));
                });
            } else {
                $('.popup-perpax-radio:checked').closest('.popup-perpax-block').find('.popup-perpax-iti-check:checked').each(function () {
                    var d = $(this).data('direction');
                    if (d !== undefined && d !== '') directions.push(String(d));
                });
            }
        } else {
            $('.popup-check-iti:checked').each(function () {
                var d = $(this).data('direction');
                if (d !== undefined && d !== '') directions.push(String(d));
            });
        }
        var khuhoi = (directions.indexOf('0') !== -1 && directions.indexOf('1') !== -1) ? 1 : 0;

        // Deduplicate itineraries (not strictly needed in radio mode since only 1 pax, but safe to keep)
        var uniqueItineraries = [];
        var itiSeen = {};
        for (var j = 0; j < itineraries.length; j++) {
            if (!itiSeen[itineraries[j]]) {
                itiSeen[itineraries[j]] = true;
                uniqueItineraries.push(itineraries[j]);
            }
        }

        // Check if "All" is selected
        var passengersParam = passengers.join(',');
        var itinerariesParam = uniqueItineraries.join(',');

        if (hasPerPaxChanges) {
            // Per-passenger layout: check if "Select All" checkbox is ticked
            if ($('#popup-select-all-perpax').is(':checked')) {
                passengersParam = 'All';
                itinerariesParam = 'All';
            }
        } else {
            // Flat layout: check if all items are selected
            var totalPsg = $('.popup-check-psg').length;
            var totalIti = $('.popup-check-iti').length;
            if (passengers.length === totalPsg) passengersParam = 'All';
            if (uniqueItineraries.length === totalIti) itinerariesParam = 'All';
        }

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
