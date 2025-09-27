const ENTRYPOINT = "index.php?entryPoint=entryPointGeneral";

$(document).ready(function () {
    $(document).on('keypress', function (e) {
        if (e.which == 13) {
            $("#btnSearch").trigger("click");
            e.preventDefault();
        }
    });
    $('#btnSearch').click(function () {
        let pnr = $('input[name="pnr"]').val();

        if (!pnr || pnr.length < 6) return false;
        $.ajax({
            url: ENTRYPOINT,
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                class: "entryAutoBookDatacomClass",
                method: "getBooking",
                params: {
                    pnr: pnr
                }
            }),
            beforeSend: function () {
                $('.container-waiting').show();
            },
            success: function (response) {
                try {
                    const objRes = JSON.parse(response);
                    if (objRes.status == 1) {
                        $('input[name="bookingCode"]').val(objRes.data.BookingCode);
                        $('input[name="bookingId"]').val(objRes.data.BookingId);
                        $('input[name="systemCode"]').val(objRes.data.SystemCode);
                        $('input[name="entryClass"]').val(objRes.data.EntryClass);
                        $('input[name="supplier"]').val(objRes.data.Supplier);
                
                        renderBooking(objRes.data);
                        showBookingContent();
                    }
                    else {
                        showModalNotify("error", objRes.message ?? "Lỗi trong quá trình lấy dữ liệu");
                        clearBooking();
                        console.error(objRes);
                    }

                    $('.container-waiting').hide();
                }
                catch (e) {
                    $('.container-waiting').hide();
                    showModalNotify("error", "Lỗi trong quá trình lấy dữ liệu", e.message);
                    clearBooking();
                    console.error(e);
                }
            },
            error: function (xhr, status, error) {
                $('.container-waiting').hide();
                let errorMessage = 'Không tải được dữ liệu Booking';
                if (status === 'timeout') errorMessage = 'Thời gian phản hồi từ máy chủ quá lâu. Vui lòng thử lại.';
                else if (xhr.status === 404) errorMessage = 'Không tìm thấy Booking';
                else if (xhr.status === 500) errorMessage = 'Lỗi máy chủ. Vui lòng thử lại sau.';
                showModalNotify("error", errorMessage);
                clearBooking();
            }
        });
    });

    // Add click handlers for status icons (optional functionality)
    $('.status-icon').on('click', function () {
        const tooltip = $(this).attr('data-tooltip');
    });

    // Add baggage
    $(document).on('click', '.add-baggage', function (e) {
        e.preventDefault();
        const entryClass    = $('input[name="entryClass"]').val();
        const bookingCode   = $('input[name="bookingCode"]').val();
        const bookingId     = $('input[name="bookingId"]').val();
        const systemCode    = $('input[name="systemCode"]').val();
        const direction     = $(this).attr('direction');
        const passengerData = decodeAutoBook($(this).attr('passengerData') ?? "");

        // const personOrgId           = parseInt($(this).attr('personOrgId') ?? 0);
        // const personOrgIdConfirmed  = $(this).attr('personOrgIdConfirmed') ?? "";
        // const passengerName         = $(this).attr('passengerName') ?? "";

        if (systemCode.length > 0 && bookingCode.length > 0 && direction.length > 0 && personOrgId > 0) {
            $.ajax({
                url: ENTRYPOINT,
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify({
                    class: entryClass,
                    method: "getBaggageInfo",
                    params: {
                        systemCode: systemCode,
                        bookingCode: bookingCode,
                        bookingId: bookingId,
                        direction: direction
                    }
                }),
                beforeSend: function () {
                    $('.container-waiting').show();
                },
                success: function (response) {
                    try {
                        $('.container-waiting').hide();
                        const objData = JSON.parse(response);
                        if (objData.status == 1) {
                            createBaggageServiceDialog(objData.data, passengerData, systemCode, direction);
                        }
                        else {
                            showModalNotify("warning", objData.message ?? "Lỗi trong quá trình lấy dữ liệu");
                            console.error(objData);
                        }
                    }
                    catch (e) {
                        showModalNotify("error", "Lỗi trong quá trình lấy dữ liệu", e.message);
                        console.error(e);
                    }
                },
                error: function (xhr, status, error) {
                    $('.container-waiting').hide();
                    let errorMessage = 'Lấy thông tin hành lý thất bại';
                    if (status === 'timeout') errorMessage = 'Thời gian phản hồi từ máy chủ quá lâu. Vui lòng thử lại.';
                    else if (xhr.status === 500) errorMessage = 'Lỗi máy chủ. Vui lòng thử lại sau.';
                    showModalNotify("error", errorMessage);
                }
            });
        }
    });

    // Payment button click handler
    $('#payNowButton').on('click', function () {
        const bookingCode = $('input[name="bookingCode"]').val();
        const systemCode = $('input[name="systemCode"]').val();
        const entryClass = $('input[name="entryClass"]').val();
        const unpaidAmount = $('#paymentUnpaidAmount').text();

        if (!entryClass || entryClass.length < 1) {
            showModalNotify("warning", "Không tìm thấy nhà cung cấp");
            return;
        }
        if (!bookingCode || !systemCode || bookingCode.length < 6 || systemCode.length < 2) {
            showModalNotify("warning", "Vui lòng kiểm tra lại PNR và Mã hãng");
            return;
        }

        // Show confirmation dialog
        if (confirm(`Tiến hành thanh toán ${bookingCode} hãng ${systemCode}\nTổng tiền: ${unpaidAmount}`)) {
            $('#payNowButton').prop('disabled', true).text('Đang xử lý...'); // Disable button during processing

            $.ajax({
                url: ENTRYPOINT,
                type: "POST",
                contentType: "application/json",
                dataType: 'json',
                data: JSON.stringify({
                    class: "entryAutoBookDatacomClass",
                    method: "payBooking",
                    params: {
                        bookingCode: bookingCode,
                        systemCode: systemCode,
                    }
                }),
                beforeSend: function () {
                    $('.container-waiting').show();
                },
                success: function (response) {
                    if (response.status) {
                        $('#btnSearch').trigger('click');
                        showModalNotify("success", "Thanh toán thành công");
                    }
                    else {
                        $('.container-waiting').hide();
                        showModalNotify("error", response.message ?? "Thanh toán không thành công");
                        resetPaymentButton();
                    }
                },
                error: function (xhr, status, error) {
                    $('.container-waiting').hide();
                    showModalNotify("error", "Xử lý thanh toán không thành công. Vui lòng thử lại");
                    resetPaymentButton();
                }
            });
        }
    });

    // Cancel booking button click handler
    $('#cancelBookingButton').on('click', function () {
        showModalNotify("warning", "Tính năng này sắp có");
        return;
        const bookingCode = $('#bookingCode').text();

        if (confirm(`Are you sure you want to cancel booking ${bookingCode}?\nThis action cannot be undone.`)) {
            $.ajax({
                url: '/api/booking/cancel', // Replace with your cancel API endpoint
                method: 'POST',
                dataType: 'json',
                data: {
                    bookingCode: bookingCode
                },
                success: function (response) {
                    if (response.success) {
                        alert('Booking cancelled successfully.');
                        loadBookingData(); // Reload to update status
                    } else {
                        alert('Failed to cancel booking: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function (xhr, status, error) {
                    alert('Failed to cancel booking. Please try again.');
                }
            });
        }
    });
});

function renderBooking(data) {
    // Render booking information
    renderBookingInfo(data);

    // Render passengers
    renderPassengers(data);

    // Render flights
    renderFlights(data.ListFlight);

    // Render fare breakdown
    renderFareBreakdown(data.ListFare);
}

function renderBookingInfo(data) {
    $('#bookingCode').text(data.BookingCode);
    $('#suppplier').text(data.Supplier);
    // Status
    const statusInfo = getBookingStatusInfo(data.BookingStatus);
    $('#bookingStatus').text(statusInfo.text)
        .removeClass()
        .addClass(`value status ${data.BookingStatus}`)
        .attr('title', statusInfo.description);
    // Total amount
    $('#totalAmount').text(formatCurrency(data.TotalAmount));
    // Paid Amount
    const paidAmountElement = $('#paidAmount');
    paidAmountElement.text(formatCurrency(data.PaidAmount));
    if (data.PaidAmount === 0) {
        paidAmountElement.addClass('zero');
    } else {
        paidAmountElement.removeClass('zero');
    }
    // Unpaid Amount
    const unpaidAmountElement = $('#unpaidAmount');
    unpaidAmountElement.text(formatCurrency(data.UnPaidAmount));
    if (data.UnPaidAmount === 0) {
        unpaidAmountElement.addClass('zero');
    } else {
        unpaidAmountElement.removeClass('zero');
    }
    $('#bookingDate').text(formatDateTime(data.BookingDate));
    // Handle null BookingExpired
    if (!data.BookingExpired || data.BookingExpired === null) {
        $('#bookingExpiry').text('');
    } else {
        $('#bookingExpiry').text(formatDateTime(data.BookingExpired));
    }
    $('#contactName').text(data.Contact.Name);
    $('#contactEmail').text(data.Contact.Email);
    $('#contactPhone').text(data.Contact.Phone);
    $('#contactAddress').text(data.Contact.Address);

    // Update status icons
    updateStatusIcons(data);

    // Update expiry badge
    updateExpiryBadge(data);

    // Update payment section
    updatePaymentSection(data);
}

function updateStatusIcons(data) {
    // Payment Status Badge
    const paidBadge = $('#paidBadge');
    if (data.IsPaid) {
        paidBadge.removeClass('unpaid').addClass('paid');
        paidBadge.text('PAID');
        paidBadge.attr('data-tooltip', 'Tất cả các khoản thanh toán đã được xử lý thành công');
    } else {
        paidBadge.removeClass('paid').addClass('unpaid');
        paidBadge.text('UNPAID');
        paidBadge.attr('data-tooltip', 'Đang chờ xử lý – Chưa thanh toán đầy đủ');
    }

    // Void Status Badge
    const voidBadge = $('#voidBadge');
    if (data.IsVoid) {
        voidBadge.removeClass('void-not-allowed').addClass('void-allowed');
        voidBadge.text('HOÀN');
        voidBadge.attr('data-tooltip', 'Booking có thể hoàn/hủy');
    } else {
        voidBadge.removeClass('void-allowed').addClass('void-not-allowed');
        voidBadge.text('HOÀN');
        voidBadge.attr('data-tooltip', 'Booking không thể hoàn/hủy');
    }

    // Refund Status Badge
    const refundBadge = $('#refundBadge');
    if (data.IsRefund) {
        refundBadge.removeClass('refund-not-allowed').addClass('refund-allowed');
        refundBadge.text('HOÀN TIỀN');
        refundBadge.attr('data-tooltip', 'Booking được hoàn tiền');
    } else {
        refundBadge.removeClass('refund-allowed').addClass('refund-not-allowed');
        refundBadge.text('HOÀN TIỀN');
        refundBadge.attr('data-tooltip', 'Booking không được hoàn tiền');
    }

    // Edit Status Badge
    const editBadge = $('#editBadge');
    if (data.IsEdit) {
        editBadge.removeClass('edit-not-allowed').addClass('edit-allowed');
        editBadge.text('SỬA');
        editBadge.attr('data-tooltip', 'Booking có thể được điều chỉnh hoặc cập nhật');
    } else {
        editBadge.removeClass('edit-allowed').addClass('edit-not-allowed');
        editBadge.text('SỬA');
        editBadge.attr('data-tooltip', 'Booking không thể chỉnh sửa');
    }
}

function updateExpiryBadge(data) {
    const expiryBadge = $('#expiryBadge');

    // Only show badge for BookingStatusId 100 (Giữ chỗ)
    if (data.BookingStatusId == 'holding') {
        expiryBadge.hide();
        return;
    }

    // Check if BookingExpired is null or empty
    if (!data.BookingExpired || data.BookingExpired === null) {
        // No expiry time - unlimited booking, hide badge
        expiryBadge.hide();
        return;
    }

    // Parse booking expiry date
    const bookingExpired = new Date(data.BookingExpired);

    // Check if date is valid
    if (isNaN(bookingExpired.getTime())) {
        // Invalid date, treat as unlimited
        expiryBadge.hide();
        return;
    }

    const now = new Date();
    const timeDiff = bookingExpired - now;
    const minutesLeft = Math.floor(timeDiff / (1000 * 60));

    // Determine badge display
    if (timeDiff <= 0) {
        // Booking has expired
        expiryBadge.removeClass('warning').addClass('expired');
        expiryBadge.text('Đã hết hạn giữ chỗ');
        expiryBadge.show();
    } else if (minutesLeft <= 15) {
        // Booking expires within 15 minutes
        expiryBadge.removeClass('expired').addClass('warning');
        expiryBadge.text('Sắp hết hạn giữ chỗ');
        expiryBadge.show();
    } else {
        // No badge needed
        expiryBadge.hide();
    }
}

function updatePaymentSection(data) {
    const paymentSection = $('#paymentSection');

    // Only show payment section for BookingStatusId 100 and not expired
    if (data.BookingStatus == 'holding' && !isBookingExpired(data.BookingExpired)) {
        // Update payment information
        $('#paymentTotalAmount').text(formatCurrency(data.TotalAmount));
        $('#paymentUnpaidAmount').text(formatCurrency(data.UnPaidAmount));

        // // Handle null BookingExpired with enhanced styling
        // const deadlineElement = $('#paymentDeadline');
        // if (!data.BookingExpired || data.BookingExpired === null) {
        //     deadlineElement.text('')
        //         .removeClass('expiry-time')
        //         .addClass('unlimited-deadline');
        // } else {
        //     deadlineElement.text(formatDateTime(data.BookingExpired))
        //         .removeClass('unlimited-deadline')
        //         .addClass('expiry-time');
        // }

        $('#payButtonAmount').text(formatCurrency(data.UnPaidAmount));

        // Show payment section
        paymentSection.show();
    } else {
        // Hide payment section
        paymentSection.hide();
    }
}

// Reset payment button state
function resetPaymentButton() {
    $('#payNowButton').prop('disabled', false);
    $('#payNowButton').html(`
        <span class="btn-icon">💳</span>
        <span class="btn-text">Thanh toán ngay</span>
        <span class="btn-amount">${$('#paymentUnpaidAmount').text()}</span>
    `);
}

// Helper function to check if booking is expired
function isBookingExpired(bookingExpiredString) {
    // If BookingExpired is null or empty, treat as unlimited (never expired)
    if (!bookingExpiredString || bookingExpiredString === null) {
        return false;
    }

    const bookingExpired = new Date(bookingExpiredString);

    // Check if date is valid
    if (isNaN(bookingExpired.getTime())) {
        console.warn("hehe");
        // Invalid date, treat as unlimited (never expired)
        return false;
    }

    const now = new Date();
    return now > bookingExpired;
}

/**
 * Get booking status info
 * 
 * @param {string} bookingStatus 
 * @returns {object}
 */
function getBookingStatusInfo(bookingStatus) {
    const statusMap = {
        "holding": {
            text: "Giữ chỗ",
            description: "Booking đang được giữ chỗ"
        },
        "cancelled": {
            text: "Hủy",
            description: "Booking đã bị hủy"
        },
        "completed": {
            text: "Đã xuất vé & Thanh toán",
            description: "Đã xuất vé và thanh toán thành công"
        },
        "error": {
            text: "Booking Lỗi",
            description: "Booking gặp lỗi trong quá trình xử lý"
        },
        "update-required": {
            text: "Cập nhật thông tin Booking sau khi xuất vé",
            description: "Cần cập nhật thông tin booking sau khi xuất vé và cần thanh toán"
        },
        "change-paid": {
            text: "Thanh toán cho chi phí thay đổi booking",
            description: "Đã thanh toán đủ cho chi phí thay đổi booking"
        },
        "change-payment": {
            text: "Thanh toán cho chi phí thay đổi booking",
            description: "Cần thanh toán cho chi phí thay đổi booking"
        },
        "special": {
            text: "Trạng thái đặc biệt",
            description: "Trạng thái đặc biệt"
        },
        "manual-update": {
            text: "Trạng thái thanh toán được update bằng tay",
            description: "Trạng thái thanh toán được cập nhật thủ công"
        },
        "trip-cancelled": {
            text: "Hủy hành trình",
            description: "Hành trình đã bị hủy"
        },
        "ticket-error": {
            text: "Xuất vé lỗi",
            description: "Gặp lỗi trong quá trình xuất vé"
        }
    };

    return statusMap[bookingStatus] || {
        text: `Trạng thái ${bookingStatus}`,
        class: "unknown",
        description: "Trạng thái không xác định"
    };
}

function renderPassengers(data) {
    const tbody = $('#passengersTable tbody');
    tbody.empty();

    var icon_baggage = `<svg fill="#3d3d3d" height="16px" width="16px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 248.35 248.35" xml:space="preserve"><title>Hành lý</title><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M186.057,66.136h-15.314V19.839C170.743,8.901,161.844,0,150.904,0H97.448c-10.938,0-19.84,8.901-19.84,19.839v46.296 H62.295c-9.567,0-17.324,7.757-17.324,17.324V214.26c0,9.571,7.759,17.326,17.324,17.326h2.323v12.576 c0,2.315,1.876,4.188,4.186,4.188h19.811c2.315,0,4.188-1.876,4.188-4.188v-12.576h62.741v12.576c0,2.315,1.878,4.188,4.188,4.188 h19.809c2.317,0,4.188-1.876,4.188-4.188v-12.576h2.326c9.567,0,17.324-7.757,17.324-17.326V83.46 C203.381,73.891,195.624,66.136,186.057,66.136z M157.514,66.135H90.832V19.839c0-3.646,2.967-6.613,6.613-6.613h53.456 c3.646,0,6.613,2.967,6.613,6.613V66.135z"></path> </g> </g> </g></svg>`;

    const listPassenger = data.ListPassenger;
    listPassenger.forEach(function (passenger) {
        // Purchased services
        let purchasedServicesHTML = '';
        let purchasedBaggageDep = false
        let purchasedBaggageRet = false;
        let listBaggage = passenger.ListBaggage
        if(listBaggage) {
            listBaggage.forEach(function (baggage) {
                if(baggage.FlightId == 0) purchasedBaggageDep = true;
                else purchasedBaggageRet = true;
                purchasedServicesHTML += `<p class="purchased-item">
                    ${icon_baggage} ${baggage.FlightId == 1 ? 'Lượt đi' : 'Lượt về'} ${baggage.Name ?? baggage.Description} <b style="color:blue;">${formatCurrency(baggage.TotalAmount)}</b>
                </p>`;
            });
        }
 
        // Service actions
        let showAddBag = true;
        let optBaggageServiceHTML = '';
        if (passenger.Type != 'inf' 
            && (
                (data.BookingStatus == 'holding' && !isBookingExpired(data.BookingExpired))
                ||
                (!['error', 'cancelled', 'trip-cancelled', 'ticket-error', 'unknown'].includes(data.BookingStatus))
            )
        ) {
            for (let i = 0; i < Object.keys(data.ListFlight).length; i++) {
                if((i == 0 && purchasedBaggageDep) || (i == 1 && purchasedBaggageRet)) continue;
                let text = i == 0 ? 'Thêm hành lý đi' : 'Thêm hành lý về';
                optBaggageServiceHTML += `<a class="dropdown-item add-baggage"
                    direction="${i}"
                    passengerData="${encodeAutoBook(passenger)}"

                    personOrgId="${passenger.Id}"
                    personOrgIdConfirmed="${passenger.IdConfirmed}"
                    passengerName="${passenger.LastName} ${passenger.FirstName}"
                >
                    ${text}
                </a>`;
            }
        }
        else showAddBag = false;

        const passengerLabelName = getPassengerLabelName(passenger.Type);
        const badgeClass = passenger.Type;
        tbody.append(`<tr>
            <td>
                <strong>${passenger.Id}. ${passenger.LastName} ${passenger.FirstName}</strong>
                ${purchasedServicesHTML}
            </td>
            <td><span class="badge ${badgeClass}">${passengerLabelName}</span></td>
            <td>${passenger.Gender === 'M' ? 'Nam' : 'Nữ'}</td>
            <td>${passenger.DateOfBirth}${(passenger.Age !== undefined && passenger.Age > 0) ? `<i class="ms-1">(${passenger.Age} tuổi)</i>` : ''}</td>
            <td>
                ${passenger.Email ? `<div>${passenger.Email}</div>` : ''}
                ${passenger.Phone ? `<div>${passenger.Phone}</div>` : ''}
            </td>
            <td>
                <div class="btn-group ${!showAddBag ? 'd-none' : ''}">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" style="padding:3px 12px;"></button>
                    <div class="dropdown-menu">
                        ${optBaggageServiceHTML}
                    </div>
                </div>
            </td>
        </tr>`);
    });
}

function renderFlights(flights) {
    const container = $('#flightsList');
    container.empty();

    flights.forEach(function (flight, index) {
        const isReturn = index > 0;
        const carrierClass = flight.AirlineCode.toLowerCase();
        const flightCard = `
            <div class="flight-card ${isReturn ? 'return' : ''} ${carrierClass}">
                <div class="flight-header">
                    <div class="flight-number ${carrierClass}">${flight.AirlineCode}${flight.FlightNumber}</div>
                    <div class="flight-date">${formatDate(flight.DepartureDate)}</div>
                </div>
                
                <div class="flight-route">
                    <div class="airport">
                        <div class="airport-code">${flight.Origin}</div>
                        <div class="airport-name">${flight.OriginCityName}</div>
                        <div class="airport-name">Sân bay ${flight.OriginName}</div>
                        <div class="time">${formatTime(flight.DepartureTime)}</div>
                    </div>
                    
                    <div class="flight-arrow">
                        <i class="arrow">→</i>
                        <div class="duration">${flight.FlightDuration}</div>
                    </div>
                    
                    <div class="airport">
                        <div class="airport-code">${flight.Destination}</div>
                        <div class="airport-name">${flight.DestinationCityName}</div>
                        <div class="airport-name">Sân bay ${flight.DestinationName}</div>
                        <div class="time">${formatTime(flight.Arrivaltime)}</div>
                    </div>
                </div>
                
                <div class="flight-details">
                    <div class="detail-item">
                        <div class="detail-label">Cabin</div>
                        <div class="detail-value">${flight.CabinName ?? ''}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Fare Class</div>
                        <div class="detail-value">${flight.FareClass}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Aircraft</div>
                        <div class="detail-value">${flight.AirCratf || ''}</div>
                    </div>
					<!--
                    <div class="detail-item">
                        <div class="detail-label">Type</div>
                        <div class="detail-value">${isReturn ? 'Lượt về' : 'Lượt đi'}</div>
                    </div>
					-->
                </div>
            </div>
        `;
        container.append(flightCard);
    });
}

function renderFareBreakdown(fareCharges) {
    const tbody = $('#fareTable tbody');
    tbody.empty();

    // fareCharges.forEach(function (fare) {
    Object.entries(fareCharges).forEach(([key, fare]) => {
        const row = `<tr>
            <td>${fare.DirectionText}</td>
            <td>${getPassengerLabelName(fare.Type)}</td>
            <td>${formatCurrency(fare.BaseFare)}</td>
            <td>${formatCurrency(fare.VAT)}</td>
            <td>${formatCurrency(fare.AirportFee)}</td>
            <td>${formatCurrency(fare.OtherFee)}</td>
            <td title="Chưa gồm số lượng"><strong>${formatCurrency(fare.Price)}</strong></td>
        </tr>`;
        tbody.append(row);
    });
}

function clearBooking() {
    hideBookingContent();

    // Clear all form fields
    $('input[name="systemCode"]').val('');
    $('input[name="bookingCode"]').val('');
    $('input[name="bookingId"]').val('');
    $('input[name="supplier"]').val('');

    $('#bookingCode').text('');
    $('#bookingStatus').text('');
    $('#totalAmount').text('');
    $('#paidAmount').text('');
    $('#unpaidAmount').text('');
    $('#bookingDate').text('');
    $('#bookingExpiry').text('');
    $('#contactName').text('');
    $('#contactEmail').text('');
    $('#contactPhone').text('');
    $('#contactAddress').text('');

    // Clear tables
    $('#passengersTable tbody').empty();
    $('#fareTable tbody').empty();
    $('#flightsList').empty();

    // Reset status badges
    $('.status-badge').removeClass().addClass('status-badge').text('').attr('data-tooltip', '');

    // Reset payment
    $('#paymentTotalAmount').text('');
    $('#paymentUnpaidAmount').text('');
    $('#payButtonAmount').text('');
    $('#paymentSection').hide();
}

// Utility functions
function showBookingContent() {
    $('#bookingContent').removeClass('hidden');
}
function hideBookingContent() {
    $('#bookingContent').addClass('hidden');
}

function formatCurrency(amount, showUnit = true) {
    if (showUnit) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    } else {
        return new Intl.NumberFormat('vi-VN', {
            style: 'decimal',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }
}

function formatDate(dateString) {
    if (!dateString) return '';

    const date = new Date(dateString);

    // Check if date is valid
    if (isNaN(date.getTime())) return dateString;

    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();

    return `${day}/${month}/${year}`;
}

function formatDateTime(dateString) {
    if (!dateString) return '';

    const date = new Date(dateString);

    // Check if date is valid
    if (isNaN(date.getTime())) return dateString;

    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${day}/${month}/${year} ${hours}:${minutes}`;
}

function formatTime(dateString) {
    if (!dateString) return '';

    const date = new Date(dateString);

    // Check if date is valid
    if (isNaN(date.getTime())) return dateString;

    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${hours}:${minutes}`;
}

/**
 * Get passenger label name
 * 
 * @param {string} type adt, chd, inf
 * @returns {string}
 */
function getPassengerLabelName(type) {
    const types = {
        "adt" : "Người lớn",
        "chd" : "Trẻ em",
        "inf" : "Em bé"
    };
    return types[type] || "Unknown";
}

function getLinkImageAirline(airlineCode) {
    return img_src = `custom/themes/default/images/airline-icon-120x40/${airlineCode}.gif`;
}

function createBaggageServiceDialog(baggageData, passengerData, systemCode, direction) {
    return new Promise((resolve) => {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = `<div class="baggage-dialog-overlay">
            <div class="baggage-dialog">
                <div class="baggage-header">
                    <h2>
                        <span style="margin-right:12px">${direction == '0' ? 'Lượt đi' : 'Lượt về'}</span>
                        ${baggageData.Origin} ➝ ${baggageData.Destination}
                        <img src="${getLinkImageAirline(systemCode)}" alt="${systemCode}" style="max-width:90px;margin-left:12px" />
                    </h2>
                    <p>Hành khách: <b>${passengerData.LastName} ${passengerData.FirstName}</b></p>
                </div>
                <div id="baggageServiceList" class="service-list"></div>
                <center class="note"><i class="text-danger">Vui lòng kiểm tra kỹ càng thông tin hành trình, hành khách</i></center>
                <div class="baggage-dialog-actions">
                    <button class="baggage-btn baggage-cancel">Hủy</button>
                    <button class="baggage-btn baggage-confirm">Thêm hành lý</button>
                </div>
            </div>
        </div>`;

        document.body.appendChild(wrapper);

        // const overlay = wrapper.querySelector('.baggage-dialog-overlay');
        const baggageListEl = wrapper.querySelector('#baggageServiceList');
        const confirmBtn = wrapper.querySelector('.baggage-confirm');
        const cancelBtn = wrapper.querySelector('.baggage-cancel');

        // Render available services
        if(!('ListBaggage' in baggageData) && !baggageData.ListBaggage || baggageData.ListBaggage.length == 0) {
            baggageListEl.innerHTML = '<center><i>Không có hành lý để thêm</i></center>';
            $('.baggage-dialog center.note').remove();
        }
        else {
            baggageListEl.innerHTML = '';
            baggageData.ListBaggage.forEach((bag, index) => {
                const div = document.createElement('div');
                div.className = 'baggage-service-item';
                div.innerHTML = `
                    <input type="radio" name="baggageOption"
                        id="baggageOption${index}"
                        value="${encodeAutoBook(service.value)}"
                        data-description="${bag.Description || bag.Name}"
                        data-amount="${bag.TotalAmount}"
                        data-vat-amount="${bag.VAT}"
                        data-person-org-id="${passengerData.Id}"
                        data-person-org-id-confirmed="${passengerData.IdConfirmed}"
                    />
                    <label for="baggageOption${index}">
                        ${bag.Description || bag.Name}
                        <br>
                        <span>${formatCurrency(bag.Amount, false)} + ${formatCurrency(bag.VAT, false)} (VAT) = <strong>${formatCurrency(bag.TotalAmount)}</strong></span>
                    </label
                `;
                baggageListEl.appendChild(div);
            });
        }

        confirmBtn.addEventListener('click', () => {
            const selectedRadio = wrapper.querySelector('input[name="baggageOption"]:checked');
            if (!selectedRadio) return;

            const serviceKey = selectedRadio.value;
            const description = selectedRadio.dataset.description;
            const amount = parseInt(selectedRadio.dataset.amount);
            const vatAmount = parseInt(selectedRadio.dataset.vatAmount);
            const personOrgId = selectedRadio.dataset.personOrgId;
            const personOrgIdConfirmed = selectedRadio.dataset.personOrgIdConfirmed || '';

            if(confirm(`Tiến hành thêm ${description}\nHành khách ${passengerName}\nTổng phí: ${formatCurrency(amount)}`)) {
                let systemCode = $('input[name="systemCode"]').val();
                let bookingCode = $('input[name="bookingCode"]').val();

                $.ajax({
                    url: ENTRYPOINT,
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({
                        action: "add_baggage",
                        systemCode: systemCode,
                        bookingCode: bookingCode,
                        serviceKey: serviceKey,
                        personOrgId: personOrgId,
                        personOrgIdConfirmed: personOrgIdConfirmed,
                        // Use for updating data to BM
                        passengerName: passengerName,
                        description: description,
                        amount: amount,
                        vatAmount: vatAmount
                    }),
                    beforeSend: function () {
                        $('.container-waiting').show();
                    },
                    success: function (response) {
                        try {
                            document.body.removeChild(wrapper);
                            resolve(null);

                            const objRes = JSON.parse(response);
                            if (objRes.status == 1) {
                                $('#btnSearch').trigger('click');
                                showModalNotify("success", "Thêm hành lý thành công");
                            }
                            else {
                                showModalNotify("error", objRes.message ?? "Lỗi thêm hành lý, vui lòng thử lại");
                                console.error(objRes);
                            }
                        }
                        catch (e) {
                            showModalNotify("error", "Lỗi thêm hành lý, vui lòng thử lại", e.message);
                            console.error(e);
                        }
                    },
                    error: function (xhr, status, error) {
                        $('.container-waiting').hide();
                        let errorMessage = 'Thêm hành lý không thành công';
                        if (status === 'timeout') errorMessage = 'Thời gian phản hồi từ máy chủ quá lâu. Vui lòng thử lại.';
                        else if (xhr.status === 500) errorMessage = 'Lỗi máy chủ. Vui lòng thử lại sau.';
                        showModalNotify("error", errorMessage);
                    }
                });
            }
        });

        cancelBtn.addEventListener('click', () => {
            document.body.removeChild(wrapper);
            resolve(null);
        });
    });
}

function encodeAutoBook(value) {
    if(!value) return value;
    if(typeof value === "object") return btoa(encodeURIComponent(JSON.stringify(value)));
    if(typeof value === "string") return btoa(encodeURIComponent(value));
}

function decodeAutoBook(value) {
    if(!value || value.length == 0) return value;
    if(typeof value === "string") return JSON.parse(decodeURIComponent(atob(value)));
}