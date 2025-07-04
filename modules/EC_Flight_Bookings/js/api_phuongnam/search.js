const ENDPOINT_AUTO_BOOK = "index.php?entryPoint=entryPointAutoBook";

$(document).ready(function () {
    $(document).on('keypress', function (e) {
        if (e.which == 13) {
            $("#btnSearch").trigger("click");
            e.preventDefault();
        }
    });
    $('#btnSearch').click(function () {
        let bookingCode = $('input[name="bookingCode"]').val();
        let systemCode = $('select[name="systemCode"]').val();

		if(!systemCode || !bookingCode || bookingCode.length < 6 || systemCode.length < 2) return false;

        $.ajax({
            url: ENDPOINT_AUTO_BOOK,
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                action: "get_booking",
                systemCode: systemCode,
                bookingCode: bookingCode
            }),
            beforeSend: function () {
                $('.container-waiting').show();
            },
            success: function (response) {
                try {
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
                    console.error("Response was:", response);
                }
            },
			error: function(xhr, status, error) {
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
    $('.status-icon').on('click', function() {
        const tooltip = $(this).attr('data-tooltip');
        console.log('Status clicked:', tooltip);
        // You can add more functionality here if needed
    });

	// Payment button click handler
    $('#payNowButton').on('click', function() {
        const bookingCode = $('input[name="bookingCode"]').val();
        const systemCode = $('select[name="systemCode"]').val();
		const unpaidAmount = $('#paymentUnpaidAmount').text();

        if(!bookingCode || !systemCode || bookingCode.length < 6 || systemCode.length < 2) {
            showModalNotify("warning", "Vui lòng kiểm tra lại PNR và Mã hãng");
            return;
        }
        
		// Show confirmation dialog
		if (confirm(`Tiến hành thanh toán cho ${bookingCode} hãng ${systemCode}\nAmount: ${unpaidAmount}`)) {
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
				success: function(response) {
					if (response.status) {
                        showModalNotify("success", "Thanh toán thành công");
                        $('#btnSearch').trigger('click');
					}
                    else {
						showModalNotify("error", response.message ?? "Thanh toán không thành công");
						resetPaymentButton();
					}
				},
				error: function(xhr, status, error) {
                    showModalNotify("error", "Xử lý thanh toán không thành công. Vui lòng thử lại");
					resetPaymentButton();
				}
			});
		}
	});
    
    // Cancel booking button click handler
    $('#cancelBookingButton').on('click', function() {
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
				success: function(response) {
					if (response.success) {
						alert('Booking cancelled successfully.');
						loadBookingData(); // Reload to update status
					} else {
						alert('Failed to cancel booking: ' + (response.message || 'Unknown error'));
					}
				},
				error: function(xhr, status, error) {
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
    renderPassengers(data.Customers);
    
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
        expiryBadge.text('Holding expired');
        expiryBadge.show();
    } else if (minutesLeft <= 15) {
        // Booking expires within 15 minutes
        expiryBadge.removeClass('expired').addClass('warning');
        expiryBadge.text('Expired soon');
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
        
        // Handle null BookingExpired with enhanced styling
        const deadlineElement = $('#paymentDeadline');
        if (!data.BookingExpired || data.BookingExpired === null) {
            deadlineElement.text('')
                          .removeClass('expiry-time')
                          .addClass('unlimited-deadline');
        } else {
            deadlineElement.text(formatDateTime(data.BookingExpired))
                          .removeClass('unlimited-deadline')
                          .addClass('expiry-time');
        }
        
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

function renderPassengers(customers) {
    const tbody = $('#passengersTable tbody');
    tbody.empty();
    
    customers.forEach(function(customer) {
        const passengerLabel = getPassengerLabel(customer.PassengerTypeId);
        const badgeClass = getPassengerType(customer.PassengerTypeId);
        const row = `
            <tr>
                <td><strong>${customer.LastName} ${customer.FirstName}</strong></td>
                <td><span class="badge ${badgeClass}">${passengerLabel}</span></td>
                <td>${customer.Gender === 'M' ? 'Nam' : 'Nữ'}</td>
                <td>${customer.Age}</td>
                <td>${formatDate(customer.BirthDay)}</td>
                <td>
                    ${customer.Email ? `<div>${customer.Email}</div>` : ''}
                    ${customer.Phone ? `<div>${customer.Phone}</div>` : ''}
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

function renderFlights(flights) {
    const container = $('#flightsList');
    container.empty();
    
    flights.forEach(function(flight, index) {
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
                        <div class="airport-name">${flight.OriginName}</div>
                        <div class="time">${formatTime(flight.DepartureTime)}</div>
                    </div>
                    
                    <div class="flight-arrow">
                        <i class="arrow">→</i>
                        <div class="duration">${flight.FlightDuration}</div>
                    </div>
                    
                    <div class="airport">
                        <div class="airport-code">${flight.Destination}</div>
                        <div class="airport-name">${flight.DestinationCityName}</div>
                        <div class="airport-name">${flight.DestinationName}</div>
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
    
    fareCharges.forEach(function(fare) {
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
//     // You can use this function to test with the provided JSON data
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