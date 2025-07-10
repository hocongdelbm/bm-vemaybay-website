const ENDPOINT_AUTO_BOOK = "index.php?entryPoint=entryPointAutoBook";

$(document).ready(function () {
    $(document).on('keypress', function (e) {
        if (e.which == 13) {
            $("#btnSearch").trigger("click");
            e.preventDefault();
        }
    });
    $('#btnSearch').click(function () {
        let airlineCode  = $('select[name="airlineCode"]').val();
        let pnr = $('input[name="pnr"]').val();

        if (!airlineCode || !pnr || pnr.length < 6 || airlineCode.length < 2) return false;

        $.ajax({
            url: ENDPOINT_AUTO_BOOK,
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                action: "get_booking",
                systemCode: airlineCode,
                bookingCode: pnr
            }),
            beforeSend: function () {
                $('.container-waiting').show();
            },
            success: function (response) {
                try {
                    $('input[name="systemCode"]').val(airlineCode);
                    $('input[name="bookingCode"]').val(pnr);

                    $('.container-waiting').hide();
                    const objData = JSON.parse(response);
                    if (objData.status == 1) {
                        renderBooking(objData.data);
                        showBookingContent();
                    }
                    else {
                        showModalNotify("error", objData.message ?? "Lỗi trong quá trình lấy dữ liệu");
                        clearBooking();
                        console.error(objData);
                    }
                }
                catch (e) {
                    showModalNotify("error", "Lỗi trong quá trình lấy dữ liệu", e.message);
                    clearBooking();
                    console.error(e);
                }
            },
            error: function (xhr, status, error) {
                $('.container-waiting').hide();
                let errorMessage = 'Failed to load booking data';
                if (status === 'timeout') errorMessage = 'Request timed out. Please try again.';
                else if (xhr.status === 404) errorMessage = 'Booking not found.';
                else if (xhr.status === 500) errorMessage = 'Server error. Please try again later.';
                showModalNotify("error", errorMessage);
                clearBooking();
            }
        });
    });

    // Add click handlers for status icons (optional functionality)
    $('.status-icon').on('click', function () {
        const tooltip = $(this).attr('data-tooltip');
    });

    $(document).on('click', '.add-baggage', function (e) {
        e.preventDefault();
        const direction = $(this).attr('direction');
        const personOrgId = parseInt($(this).attr('personOrgId') ?? 0);
        const personOrgIdConfirmed = $(this).attr('personOrgIdConfirmed');
        const passengerName = $(this).attr('passengerName');
        const bookingCode = $('input[name="bookingCode"]').val();
        const systemCode = $('input[name="systemCode"]').val();

        if (systemCode.length > 0 && bookingCode.length > 0 && direction.length > 0 && personOrgId > 0) {
            $.ajax({
                url: ENDPOINT_AUTO_BOOK,
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify({
                    'action': 'get_baggage_info',
                    'direction': direction,
                    'systemCode': systemCode,
                    'bookingCode': bookingCode
                }),
                beforeSend: function () {
                    $('.container-waiting').show();
                },
                success: function (response) {
                    try {
                        $('.container-waiting').hide();
                        const objData = JSON.parse(response);
                        if (objData.status == 1) {
                            createBaggageServiceDialog(objData.data, passengerName, personOrgId, personOrgIdConfirmed);
                        }
                        else {
                            showModalNotify("error", objData.message ?? "Lỗi trong quá trình lấy dữ liệu");
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
                    if (status === 'timeout') errorMessage = 'Thời gian phản hồi quá lâu. Vui lòng thử lại.';
                    else if (xhr.status === 500) errorMessage = 'Server error. Please try again later.';
                    showModalNotify("error", errorMessage);
                }
            });
        }
    });

    // Payment button click handler
    $('#payNowButton').on('click', function () {
        const bookingCode = $('input[name="bookingCode"]').val();
        const systemCode = $('input[name="systemCode"]').val();
        const unpaidAmount = $('#paymentUnpaidAmount').text();

        if (!bookingCode || !systemCode || bookingCode.length < 6 || systemCode.length < 2) {
            showModalNotify("warning", "Vui lòng kiểm tra lại PNR và Mã hãng");
            return;
        }

        // Show confirmation dialog
        if (confirm(`Tiến hành thanh toán ${bookingCode} hãng ${systemCode}\nTổng tiền: ${unpaidAmount}`)) {
            // Disable button during processing
            $('#payNowButton').prop('disabled', true).text('Processing...');

            $.ajax({
                url: ENDPOINT_AUTO_BOOK,
                type: "POST",
                contentType: "application/json",
                dataType: 'json',
                data: JSON.stringify({
                    action: "pay_booking",
                    systemCode: systemCode,
                    bookingCode: bookingCode
                }),
                success: function (response) {
                    if (response.status) {
                        $('#btnSearch').trigger('click');
                        showModalNotify("success", "Thanh toán thành công");
                    }
                    else {
                        showModalNotify("error", response.message ?? "Thanh toán không thành công");
                        resetPaymentButton();
                    }
                },
                error: function (xhr, status, error) {
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
    renderFlights(data.Flights);

    // Render fare breakdown
    renderFareBreakdown(data.SumCharge.FareCharges);
}

function renderBookingInfo(data) {
    $('#bookingCode').text(data.BookingCode);
    // Status
    const statusInfo = getBookingStatusInfo(data.BookingStatusId);
    $('#bookingStatus').text(statusInfo.text)
        .removeClass('paid unpaid holding cancelled completed error update-required change-paid change-payment special manual-update trip-cancelled ticket-error unknown')
        .addClass(statusInfo.class)
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
    $('#contactName').text(data.ContactName);
    $('#contactEmail').text(data.ContactEmail);
    $('#contactPhone').text(data.ContactPhone);
    $('#contactAddress').text(data.ContactAddress);

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
    if (data.BookingStatusId !== 100) {
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
    if (data.BookingStatusId === 100 && !isBookingExpired(data.BookingExpired)) {
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
        <span class="btn-text">Pay Now</span>
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
        // Invalid date, treat as unlimited (never expired)
        return false;
    }

    const now = new Date();
    return now > bookingExpired;
}

function getBookingStatusInfo(bookingStatusId) {
    const statusMap = {
        100: {
            text: "Giữ chỗ",
            class: "holding",
            description: "Booking đang được giữ chỗ"
        },
        200: {
            text: "Hủy",
            class: "cancelled",
            description: "Booking đã bị hủy"
        },
        300: {
            text: "Đã xuất vé & Thanh toán",
            class: "completed",
            description: "Đã xuất vé và thanh toán thành công"
        },
        400: {
            text: "Booking Lỗi",
            class: "error",
            description: "Booking gặp lỗi trong quá trình xử lý"
        },
        310: {
            text: "Cập nhật thông tin Booking sau khi xuất vé",
            class: "update-required",
            description: "Cần cập nhật thông tin booking sau khi xuất vé và cần thanh toán"
        },
        320: {
            text: "Thanh toán cho chi phí thay đổi booking",
            class: "change-paid",
            description: "Đã thanh toán đủ cho chi phí thay đổi booking"
        },
        350: {
            text: "Thanh toán cho chi phí thay đổi booking",
            class: "change-payment",
            description: "Cần thanh toán cho chi phí thay đổi booking"
        },
        330: {
            text: "Trạng thái đặc biệt",
            class: "special",
            description: "Trạng thái đặc biệt"
        },
        329: {
            text: "Trạng thái thanh toán được update bằng tay",
            class: "manual-update",
            description: "Trạng thái thanh toán được cập nhật thủ công"
        },
        210: {
            text: "Hủy hành trình",
            class: "trip-cancelled",
            description: "Hành trình đã bị hủy"
        },
        304: {
            text: "Xuất vé lỗi",
            class: "ticket-error",
            description: "Gặp lỗi trong quá trình xuất vé"
        }
    };

    return statusMap[bookingStatusId] || {
        text: `Trạng thái ${bookingStatusId}`,
        class: "unknown",
        description: "Trạng thái không xác định"
    };
}

function renderPassengers(data) {
    const tbody = $('#passengersTable tbody');
    tbody.empty();

    var icon_baggage = `<svg fill="#3d3d3d" height="16px" width="16px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 248.35 248.35" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M186.057,66.136h-15.314V19.839C170.743,8.901,161.844,0,150.904,0H97.448c-10.938,0-19.84,8.901-19.84,19.839v46.296 H62.295c-9.567,0-17.324,7.757-17.324,17.324V214.26c0,9.571,7.759,17.326,17.324,17.326h2.323v12.576 c0,2.315,1.876,4.188,4.186,4.188h19.811c2.315,0,4.188-1.876,4.188-4.188v-12.576h62.741v12.576c0,2.315,1.878,4.188,4.188,4.188 h19.809c2.317,0,4.188-1.876,4.188-4.188v-12.576h2.326c9.567,0,17.324-7.757,17.324-17.326V83.46 C203.381,73.891,195.624,66.136,186.057,66.136z M157.514,66.135H90.832V19.839c0-3.646,2.967-6.613,6.613-6.613h53.456 c3.646,0,6.613,2.967,6.613,6.613V66.135z"></path> </g> </g> </g></svg>`;

    const baggages = data.Baggages;
    const customers = data.Customers;
    customers.forEach(function (customer) {
        const passengerLabel = getPassengerLabel(customer.PassengerTypeId);
        const badgeClass = getPassengerType(customer.PassengerTypeId);

        // Purchased services
        let purchasedServicesHTML = '';
        let purchasedBaggageDep = false
        let purchasedBaggageRet = false;
        if(baggages) {
            baggages.forEach(function (baggage) {
                if(customer.PersonOrgId == baggage.PersonOrgId) {
                    if(baggage.FlightId == 1) purchasedBaggageDep = true;
                    else purchasedBaggageRet = true;
                    purchasedServicesHTML += `<p class="purchased-item">
                        ${icon_baggage} ${baggage.FlightId == 1 ? 'Lượt đi' : 'Lượt về'} ${baggage.ServiceName} <b style="color:blue;">${formatCurrency(baggage.TotalAmount)}</b>
                    </p>`;
                }
            });
        }
 
        // Service actions
        let showAddBag = true;
        let optBaggageServiceHTML = '';
        if (customer.PassengerTypeId != 5 && (data.BookingStatusId != 100 || !isBookingExpired(data.BookingExpired))) {
            for (let i = 0; i < Object.keys(data.Flights).length; i++) {
                if((i == 0 && purchasedBaggageDep) || (i == 1 && purchasedBaggageRet)) continue
                let text = i == 0 ? 'Thêm hành lý đi' : 'Thêm hành lý về';
                let direction = i == 0 ? '0' : '1';
                optBaggageServiceHTML += `<a class="dropdown-item add-baggage"
                    direction="${i}"
                    personOrgId="${customer.PersonOrgId}"
                    personOrgIdConfirmed="${customer.personOrgIdConfirmed ?? ''}"
                    passengerName="${customer.LastName} ${customer.FirstName}"
                >
                    ${text}
                </a>`;
            }
        }
        else showAddBag = false;

        tbody.append(`<tr>
            <td>
                <strong>${customer.PersonOrgId}. ${customer.LastName} ${customer.FirstName}</strong>
                ${purchasedServicesHTML}
            </td>
            <td><span class="badge ${badgeClass}">${passengerLabel}</span></td>
            <td>${customer.Gender === 'M' ? 'Nam' : 'Nữ'}</td>
            <td>${formatDate(customer.BirthDay)} <i class="ms-1">(${customer.Age} tuổi)</i></td>
            <td>
                ${customer.Email ? `<div>${customer.Email}</div>` : ''}
                ${customer.Phone ? `<div>${customer.Phone}</div>` : ''}
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
        const carrierClass = flight.CarrierCode.toLowerCase();
        const flightCard = `
            <div class="flight-card ${isReturn ? 'return' : ''} ${carrierClass}">
                <div class="flight-header">
                    <div class="flight-number ${carrierClass}">${flight.CarrierCode}${flight.FlightNumber}</div>
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
                        <div class="detail-value">${flight.CabinName}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Fare Class</div>
                        <div class="detail-value">${flight.FareClass}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Aircraft</div>
                        <div class="detail-value">${flight.AirCratfType || ''}</div>
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

    fareCharges.forEach(function (fare) {
        const passengerType = getPassengerLabel(fare.PassengerTypeId);
        const row = `
            <tr>
                <td><strong>${passengerType}</strong></td>
                <td>${formatCurrency(fare.FareBaseAmount)}</td>
                <td>${formatCurrency(fare.AirportFeesAmount)}</td>
                <td>${formatCurrency(fare.TaxAmount)}</td>
                <td>${formatCurrency(fare.VATAmount)}</td>
                <td><strong>${formatCurrency(fare.TotalAmount)}</strong></td>
            </tr>
        `;
        tbody.append(row);
    });
}

function clearBooking() {
    hideBookingContent();

    // Clear all form fields
    $('input[name="systemCode"]').val('');
    $('input[name="bookingCode"]').val('');
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
}

// Utility functions
function showBookingContent() {
    $('#bookingContent').removeClass('hidden');
}
function hideBookingContent() {
    $('#bookingContent').addClass('hidden');
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
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

function getPassengerType(typeId) {
    const types = {
        1: 'adult',
        5: 'infant',
        6: 'child'
    };
    return types[typeId] || '';
}

function getPassengerLabel(typeId) {
    const types = {
        1: 'Người lớn',
        5: 'Em bé',
        6: 'Trẻ em'
    };
    return types[typeId] || 'Unknown';
}

// // Alternative AJAX call for testing with mock data
// function loadMockData() {
//     // You can use this function to test with the provided JFON data
//     const mockResponse = {
//         "message": "Success",
//         "data": {
//             // Insert your JSON data here for testing
//         },
//         "status": 1
//     };

//     setTimeout(() => {
//         // hideLoading();
//         renderBooking(mockResponse.data);
//         showBookingContent();
//     }, 1000);
// }

function createBaggageServiceDialog(serviceData, passengerName, personOrgId, personOrgIdConfirmed = '') {
    return new Promise((resolve) => {
        const wrapper = document.createElement('div');
        const firstService = serviceData.ListService[0];
        wrapper.innerHTML = `<div class="baggage-dialog-overlay">
            <div class="baggage-dialog">
                <div class="baggage-header">
                    <h2>${firstService.Origin} ➝ ${firstService.Destination}, ${firstService.CarrierCode}</h2>
                    <p>Ngày bay: <b>${formatDateTime(firstService.DepartureDate)}</b></p>
                    <p>Hành khách: <b>${passengerName}</b></p>
                </div>
                <div id="baggageServiceList" class="service-list"></div>
                <div class="baggage-dialog-actions">
                    <button class="baggage-btn baggage-cancel">Hủy</button>
                    <button class="baggage-btn baggage-confirm">Thêm</button>
                </div>
            </div>
        </div>`;

        document.body.appendChild(wrapper);

        const overlay = wrapper.querySelector('.baggage-dialog-overlay');
        const serviceListEl = wrapper.querySelector('#baggageServiceList');
        const confirmBtn = wrapper.querySelector('.baggage-confirm');
        const cancelBtn = wrapper.querySelector('.baggage-cancel');

        // Render available services
        const list = serviceData.ListService;
        serviceListEl.innerHTML = '';
        list.forEach(service => {
            const div = document.createElement('div');
            div.className = 'baggage-service-item';
            div.innerHTML = `
                <input type="radio" name="baggageOption"
                    id="${service.ServiceKey}"
                    value="${service.ServiceKey}"
                    data-description="${service.ServiceDescription}"
                    data-amount="${service.ServiceTotalAmount}"
                    data-person-org-id="${personOrgId}"
                    data-person-org-id-confirmed="${personOrgIdConfirmed}"
                />
                <label for="${service.ServiceKey}">
                    ${service.ServiceDescription}
                    <br>
                    <strong>${formatCurrency(service.ServiceTotalAmount)}</strong>
                </label
            `;
            serviceListEl.appendChild(div);
        });

        confirmBtn.addEventListener('click', () => {
            const selectedRadio = wrapper.querySelector('input[name="baggageOption"]:checked');
            if (!selectedRadio) return;

            const serviceKey = selectedRadio.value;
            const description = selectedRadio.dataset.description;
            const amount = parseInt(selectedRadio.dataset.amount);
            const personOrgId = selectedRadio.dataset.personOrgId;
            const personOrgIdConfirmed = selectedRadio.dataset.personOrgIdConfirmed || '';

            if(confirm(`Tiến hành thêm ${description}\nHành khách ${passengerName}\nTổng phí: ${formatCurrency(amount)}`)) {
                let systemCode = $('input[name="systemCode"]').val();
                let bookingCode = $('input[name="bookingCode"]').val();

                $.ajax({
                    url: ENDPOINT_AUTO_BOOK,
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({
                        action: "add_baggage",
                        systemCode: systemCode,
                        bookingCode: bookingCode,
                        serviceKey: serviceKey,
                        personOrgId: personOrgId,
                        personOrgIdConfirmed: personOrgIdConfirmed,
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
                                showModalNotify("error", objRes.message ?? "Lỗi thêm hành lý, vui lòng thử lại sau");
                                console.error(objRes);
                            }
                        }
                        catch (e) {
                            showModalNotify("error", "Lỗi thêm hành lý, vui lòng thử lại sau", e.message);
                            console.error(e);
                        }
                    },
                    error: function (xhr, status, error) {
                        $('.container-waiting').hide();
                        let errorMessage = 'Failed to adding baggage';
                        if (status === 'timeout') errorMessage = 'Request timed out. Please try again.';
                        else if (xhr.status === 500) errorMessage = 'Server error. Please try again later.';
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