// Hàm format giá tiền
function formatPrice(fare) {
    return new Intl.NumberFormat('vi-VN').format(fare);
}
// fomat giá tiền modal
const fareInput = document.getElementById('modal_fare');

fareInput.addEventListener('input', function (e) {
    // Lấy giá trị nhập vào, loại bỏ mọi ký tự không phải số
    let value = e.target.value.replace(/\D/g, '');

    // Nếu rỗng thì thôi
    if (!value) {
        e.target.value = '';
        return;
    }

    // Format số có dấu chấm mỗi 3 chữ số
    value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    // Gán lại vào input
    e.target.value = value;
});
// let fareValue = fareInput.value.replace(/\./g, ''); // loại bỏ dấu chấm

// Hàm format ngày
function formatDate(dateString) {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
}
function formatDateToYMD(dateStr) {
    if (!dateStr) return "";
    const [day, month, year] = dateStr.split("-");
    return `${year}-${month}-${day}`;
}



// Hàm lấy thông tin hãng bay
function getAirlineInfo(airlineCode) {
    const logos = {
        'VN': {
            color: '#1e40af',
            name: 'Vietnam Airlines',
            logo: 'https://gmi.vietjet.net/images/img/Images-brand/VN.png'
        },
        'VJ': {
            color: '#dc2626',
            name: 'VietJet Air',
            logo: 'https://gmi.vietjet.net/images/img/Images-brand/VJ.png'
        },
        'BL': {
            color: '#059669',
            name: 'Pacific Airlines',
            logo: 'https://gmi.vietjet.net/images/img/Images-brand/BL.png'
        },
        'QH': {
            color: '#7c3aed',
            name: 'Bamboo Airways',
            logo: 'https://gmi.vietjet.net/images/img/Images-brand/QH.png'
        },
        'VU': {
            color: '#f59e0b',
            name: 'Vietravel Airlines',
            logo: 'https://gmi.vietjet.net/images/img/Images-brand/VU.png'
        }
    };

    return logos[airlineCode] || {
        color: '#6b7280',
        name: 'Unknown',
        logo: 'https://via.placeholder.com/80x40?text=NA'
    };
}

// Hàm render một chuyến bay
function renderFlightItem(flight, index) {
    // const airlineInfo = getAirlineInfo(flight.airlineCode);
    const airlineInfo = getAirlineInfo(flight.details[0].carrierCode);


    let html = `
        <div class="flight-item" data-flight="${flight.flightNo}"
                                data-airline="${flight.airlineCode}"
                                data-carrier="${flight.details[0].carrierCode}"
                                data-date="${flight.depDate}"
                                data-depname="${flight.depName}"
                                data-desname="${flight.desName}"
                                data-deptime="${flight.depTime}"
                                data-arvtime="${flight.arvTime}"
                                data-depdate="${flight.depDate}"
                                data-arvdate="${flight.arvDate}">
            <div class="flight-left">
                <div class="airline-logo">
                    <img src="${airlineInfo.logo}" alt="${airlineInfo.name}">
                </div>
                
                <div class="flight-times">
                    <div class="time-info">
                        <span class="time">${flight.depTime} - ${flight.arvTime}</span>
                    </div>
                    <div class="airline-name">${flight.details[0].carrier}</div>
                </div>
                
                <div class="flight-route">
                    <div class="route-code">${flight.dep}-${flight.des}</div>
                    <div class="duration">${flight.nDuration}</div>
                </div>

                <div class="flight_code">
                    <div class="flight-number">${flight.flightNo}</div>
                    <a class="flight-details">Chi tiết chuyến bay</a>
                </div>
            </div>

            <div style="display: flex; align-items: center;">
                <div class="flight-price">
                    <div class="selected-ticketClass">${flight.ticketClass}</div>
                    <div class="price-dropdown d-flex align-items-center">
                        <div class="selected-price fw-bold text-danger me-1">${formatPrice(flight.fare)}</div>
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle small-arrow" data-bs-toggle="dropdown" aria-expanded="false">▼</a>
                            <ul class="dropdown-menu dropdown-scroll">
                                ${renderFareOptions(flight)}
                            </ul>
                        </div>
                    </div>
                </div>
                <button class="select-btn" onclick="selectFlight('${flight.flightNo}')">Sửa</button>
            </div>
        </div>
    `;

    return html;
}

// Hàm render các tùy chọn giá
function renderFareOptions(flight) {
    if (!flight.fareOptions || flight.fareOptions.length === 0) {
        return '';
    }

    return flight.fareOptions.map(option => {
        const ticketClass = option.ticketClass || '';
        const fareBasis = option.fareBasis || '';
        const fare = option.fare || 0;
        const availableSeats = flight.availableSeats;

        return `
            <li>
                <div class="dropdown-item d-flex justify-content-between align-items-center">
                    <div>
                        <div><strong>${ticketClass}</strong> - <span class="text-muted">${fareBasis}</span></div>
                        <small class="text-success">còn lại ${availableSeats} chỗ</small>
                    </div>
                    <span class="text-danger fw-bold">${formatPrice(fare)} VND</span>
                </div>
            </li>
        `;
    }).join('');
}
//  href="#" onclick="updateSelectedFare('${flight.flightNo}', '${ticketClass}', ${fare})"

// Lưu tất cả timer đang chạy
const countdownTimers = {};

function startCountdown(expireTime, elementId) {
    const countdownEl = document.getElementById(elementId);
    if (!countdownEl) return;

    // Nếu đã có timer cũ → xóa trước
    if (countdownTimers[elementId]) {
        clearInterval(countdownTimers[elementId]);
    }

    function updateCountdown() {
        const now = new Date().getTime();
        const endTime = new Date(expireTime).getTime();
        let diff = Math.floor((endTime - now) / 1000); // giây còn lại

        if (diff <= 0) {
            countdownEl.textContent = "Hết hạn";
            clearInterval(countdownTimers[elementId]);
            delete countdownTimers[elementId]; // xóa khỏi danh sách
            return;
        }

        const minutes = Math.floor(diff / 60);
        const seconds = diff % 60;
        countdownEl.textContent = `${minutes} phút : ${seconds < 10 ? '0' + seconds : seconds} giây`;
    }

    updateCountdown(); // chạy ngay lần đầu
    countdownTimers[elementId] = setInterval(updateCountdown, 1000); // lưu lại timer
}

// Hàm hiển thị dữ liệu chuyến bay
function displayFlightData(data) {
    if (!data || data.error !== 0) {
        throw new Error('Lỗi khi đọc dữ liệu chuyến bay');
    }

    // const depFlights = data.data.dep || [];
    // const retFlights = data.data.ret || [];
    const depData = data.data.dep;
    const retData = data.data.ret;
    // Nếu API trả về 1 object đơn lẻ
    const depFlights = Array.isArray(depData) ? depData : (depData ? [depData] : []);
    const retFlights = Array.isArray(retData) ? retData : (retData ? [retData] : []);

    // Cập nhật thông tin header
    if (depFlights.length > 0) {
        document.getElementById('depTitle').textContent =
            `Chuyến bay đi - ${formatDate(depFlights[0].depDate)}`;

        // Nếu có thời gian cache hết hạn
        if (data.dep_cache_expires_at) {
            startCountdown(data.dep_cache_expires_at, 'depCountdown');
        }

        // Render danh sách chuyến bay đi
        const depFlightList = document.getElementById('depFlightList');
        depFlightList.innerHTML = depFlights.map((flight, index) =>
            renderFlightItem(flight, index)
        ).join('');
    } else {
        document.getElementById('depFlightList').innerHTML =
            '<div class="loading" style="color: #dc2626;">Không tìm thấy chuyến bay đi nào.</div>';
    }

    if (retFlights.length > 0) {
        document.getElementById('retTitle').textContent =
            `Chuyến bay về - ${formatDate(retFlights[0].depDate)}`;

        if ((data.ret_cache_expires_at)) {
            startCountdown(data.ret_cache_expires_at, 'retCountdown');
        } else {
            const el = document.getElementById('retCountdown');
            if (el) el.textContent = '';

            // 👉 Dừng đồng hồ nếu có timer cũ
            if (countdownTimers['retCountdown']) {
                clearInterval(countdownTimers['retCountdown']);
                delete countdownTimers['retCountdown'];
            }
        }

        // Render danh sách chuyến bay về
        const retFlightList = document.getElementById('retFlightList');
        retFlightList.innerHTML = retFlights.map((flight, index) =>
            renderFlightItem(flight, index)
        ).join('');
    } else {
        document.getElementById('retTitle').textContent =
            `Chuyến bay về `;
        if ((data.ret_cache_expires_at)) {
            startCountdown(data.ret_cache_expires_at, 'retCountdown');
        } else {
            const el = document.getElementById('retCountdown');
            if (el) el.textContent = '';

            // Dừng đồng hồ nếu có timer cũ
            if (countdownTimers['retCountdown']) {
                clearInterval(countdownTimers['retCountdown']);
                delete countdownTimers['retCountdown'];
            }
        }
        document.getElementById('retFlightList').innerHTML =
            '<div class="loading" style="color: #dc2626;">Không tìm thấy chuyến bay về nào.</div>';
    }
}

// Hàm tìm kiếm chuyến bay
function searchFlight(event, isLive) {
    event.preventDefault();

    const airlineCode = document.getElementById('airlineCode').value;
    const depCode = document.getElementById('depCode').value.trim().toUpperCase();
    const desCode = document.getElementById('desCode').value.trim().toUpperCase();
    const departDateDisplay = document.getElementById('departDate').value; // "08-10-2025"
    const departDate = formatDateToYMD(departDateDisplay); // "2025-10-08"
    const returnDateDisplay = document.getElementById('returnDate').value;
    const returnDate = formatDateToYMD(returnDateDisplay);
    const is = isLive;

    if (!airlineCode || !depCode || !desCode || !departDate) {
        showModalNotify('warning',
            '<strong>Vui lòng điền đầy đủ thông tin bắt buộc!</strong>'
        );
        return;
    }

    document.getElementById('depFlightList').innerHTML = '<div class="loading">Đang tìm kiếm chuyến bay...</div>';
    document.getElementById('retFlightList').innerHTML = '<div class="loading">Đang tìm kiếm chuyến bay...</div>';

    $.ajax({
        url: "index.php?entryPoint=entryPointGeneral",
        method: "POST",
        contentType: "application/json",
        data: JSON.stringify({
            class: "entryFareSystemClass",
            method: "searchFlightBM",
            params: {
                airlineCode: airlineCode,
                depCode: depCode,
                desCode: desCode,
                departDate: departDate,
                returnDate: returnDate,
                isLive: is
            }
        }),
        beforeSend: function () {
            $(".container-waiting").show();
        },
        success: function (response) {
            $('.container-waiting').hide();
            try {
                const data = typeof response === 'string' ? JSON.parse(response) : response;
                // 🔍 Nếu API trả về lỗi
                if (data.error && data.error === 1) {
                    let message = data.message;

                    // ✅ Kiểm tra nội dung lỗi cụ thể
                    if (message === "Invalid return date") {
                        showModalNotify('warning',
                            ('Ngày đi và ngày về không hợp lệ') +
                            '<br><strong>Vui lòng xem lại!</strong>'
                        );

                        document.getElementById('depTitle').textContent =
                            `Chuyến bay đi `;

                        const el_dep = document.getElementById('depCountdown');
                        if (el_dep) el_dep.textContent = '';

                        // Dừng đồng hồ nếu có timer cũ
                        if (countdownTimers['depCountdown']) {
                            clearInterval(countdownTimers['depCountdown']);
                            delete countdownTimers['depCountdown'];
                        }

                        document.getElementById('retTitle').textContent =
                            `Chuyến bay về `;

                        const el_ret = document.getElementById('retCountdown');
                        if (el_ret) el_ret.textContent = '';

                        // Dừng đồng hồ nếu có timer cũ
                        if (countdownTimers['retCountdown']) {
                            clearInterval(countdownTimers['retCountdown']);
                            delete countdownTimers['retCountdown'];
                        }
                    } else {
                        showModalNotify('warning',
                            '<strong>' + message + '</strong>'
                        );
                    }

                    // Hiển thị lỗi ra màn hình nếu cần
                    document.getElementById('depFlightList').innerHTML =
                        `<div class="loading" style="color: #dc2626;">Lỗi trong quá trình xử lý dữ liệu.</div>`;
                    document.getElementById('retFlightList').innerHTML =
                        `<div class="loading" style="color: #dc2626;">Lỗi trong quá trình xử lý dữ liệu.</div>`;
                    return;
                }

                displayFlightData(data);
            } catch (e) {
                console.error('Lỗi xử lý dữ liệu:', e);
                document.getElementById('depFlightList').innerHTML =
                    '<div class="loading" style="color: #dc2626;">Lỗi trong quá trình xử lý dữ liệu.</div>';
                document.getElementById('retFlightList').innerHTML =
                    '<div class="loading" style="color: #dc2626;">Lỗi trong quá trình xử lý dữ liệu.</div>';
            }
        },
        error: function (xhr, textStatus, errorThrown) {
            $(".container-waiting").hide();

            console.error("AJAX Error:", textStatus, errorThrown);
            console.error("Response Text:", xhr.responseText);

            let message = "Không thể kết nối đến máy chủ. Vui lòng thử lại sau.";
            if (xhr.status === 0) {
                message = "Mất kết nối mạng hoặc server không phản hồi.";
            } else if (xhr.status === 404) {
                message = "API không tồn tại (404 Not Found).";
            } else if (xhr.status === 500) {
                message = "Lỗi máy chủ nội bộ (500 Internal Server Error).";
            } else if (xhr.responseText) {
                try {
                    const err = JSON.parse(xhr.responseText);
                    message = err.message || message;
                } catch (_) { }
            }

            document.getElementById('depFlightList').innerHTML =
                `<div class="loading" style="color: #dc2626;">❌ ${message}</div>`;
            document.getElementById('retFlightList').innerHTML =
                `<div class="loading" style="color: #dc2626;">❌ ${message}</div>`;
        }
    });
}

// Hàm chuyển tab
function showTab(event, tabName) {
    // Ẩn tất cả tab content
    const tabContents = document.getElementsByClassName('tab-content');
    for (let i = 0; i < tabContents.length; i++) {
        tabContents[i].classList.remove('active');
    }

    // Xóa class active khỏi tất cả tabs
    const tabs = document.getElementsByClassName('tab');
    for (let i = 0; i < tabs.length; i++) {
        tabs[i].classList.remove('active');
    }

    // Hiển thị tab được chọn
    document.getElementById(tabName).classList.add('active');
    event.currentTarget.classList.add('active');

    event.preventDefault();
}

// Biến lưu thông tin chuyến bay hiện tại
let currentFlightData = {};

// Hàm mở modal
function openEditModal(flightNo, airlineCode, carrierCode, depCode, desCode, fare, depName, desName, depTime, arvTime, depDate, arvDate) {
    // --- Reset lỗi trước khi hiển thị modal ---
    const fareInput = document.getElementById('modal_fare');
    const fareError = document.getElementById('fareError');
    if (fareInput) fareInput.classList.remove('is-invalid');
    if (fareError) fareError.style.display = 'none';
    // Giả sử bạn có thêm dữ liệu chi tiết (depName, desName, depTime, arvTime, arvDate)
    const flightData = {
        depName: depName,
        desName: desName,
        depTime: depTime,
        arvTime: arvTime,
        depDate: depDate,
        arvDate: arvDate
    };

    currentFlightData = { ...flightData, flightNo, airlineCode, carrierCode, depCode, desCode, depDate, originalFare: fare };

    const airlineInfo = getAirlineInfo(carrierCode);

    // Hãng
    const airlineEl = document.getElementById('modal_airlineCode_display');
    airlineEl.textContent = airlineInfo.name;
    airlineEl.style.backgroundColor = airlineInfo.color;
    airlineEl.style.color = 'white';

    // Tuyến
    document.getElementById('modal_depName').textContent = flightData.depName;
    document.getElementById('modal_desName').textContent = flightData.desName;
    document.getElementById('modal_depCode').textContent = `(${depCode})`;
    document.getElementById('modal_desCode').textContent = `(${desCode})`;
    document.getElementById('modal_depTime').textContent = `${flightData.depTime} - ${formatDate(depDate)}`;
    document.getElementById('modal_arvTime').textContent = `${flightData.arvTime} - ${formatDate(flightData.arvDate)}`;

    // Số hiệu + giá vé
    document.getElementById('modal_flightNo').textContent = flightNo;
    document.getElementById('modal_fare').value = formatPrice(fare);
    document.getElementById('modal_originalFare').textContent = formatPrice(fare);

    new bootstrap.Modal(document.getElementById('editFlightModal')).show();
}


// Hàm cập nhật giá vé với xử lý dữ liệu đầy đủ
function updateFlightFare() {
    const formatted = document.getElementById('modal_fare').value; // ví dụ: "1.234.000"
    const newFare = formatted.replace(/\./g, ''); // loại bỏ dấu chấm -> "1234000"
    // const newFare = document.getElementById('modal_fare').value;

    // // Validate giá vé
    // if (!newFare || newFare <= 0) {
    //     // showModalNotify('warning',
    //     //     '<strong>Vui lòng nhập giá vé hợp lệ!</strong>'
    //     // );
    //     alert('Vui lòng nhập giá vé hợp lệ!');
    //     return;
    // }
    const fareInput = document.getElementById('modal_fare');
    const fareError = document.getElementById('fareError');

    // Lấy giá trị nhập vào
    const fareValue = fareInput.value.trim();

    // Kiểm tra hợp lệ: không rỗng, là số, lớn hơn 0
    if (fareValue === "" || isNaN(fareValue) || parseFloat(fareValue) <= 0) {
        fareError.style.display = 'block';        // Hiện thông báo lỗi
        fareInput.classList.add('is-invalid');    // Viền đỏ Bootstrap
        return; // Dừng xử lý
    }

    // Nếu hợp lệ -> ẩn thông báo lỗi
    fareError.style.display = 'none';
    fareInput.classList.remove('is-invalid');

    // Validate dữ liệu chuyến bay
    if (!currentFlightData || !currentFlightData.flightNo) {
        showModalNotify('waring',
            '<strong>Không tìm thấy thông tin chuyến bay!</strong>'
        );
        return;
    }

    const btnUpdate = document.querySelector('.btn-update');
    const originalText = btnUpdate.innerHTML;
    btnUpdate.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang cập nhật...';
    btnUpdate.disabled = true;

    // Gọi AJAX đến entryPointGeneral
    $.ajax({
        url: 'index.php?entryPoint=entryPointGeneral',
        type: 'POST',
        contentType: "application/json",
        data: JSON.stringify({
            class: "entryFareSystemClass",
            method: "updateTicket",
            params: {
                airlineCode: currentFlightData.airlineCode,
                depCode: currentFlightData.depCode,
                desCode: currentFlightData.desCode,
                depDate: currentFlightData.depDate,
                flightNo: currentFlightData.flightNo,
                fare: parseInt(newFare)
            }
        }),
        success: function (response) {
            try {
                // Parse response nếu là string
                const data = typeof response === 'string' ? JSON.parse(response) : response;

                // Log để debug
                console.log('Update response:', data);

                // Kiểm tra kết quả
                if (data.status === 1) {
                    // Cập nhật giá trên giao diện
                    updateSelectedFare(currentFlightData.flightNo, '', parseInt(newFare));

                    // Đóng modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editFlightModal'));
                    if (modal) {
                        modal.hide();
                    }

                    // Thông báo thành công
                    showModalNotify('success',
                        ('Cập nhật giá vé thành công!') +
                        '<br><br><strong>Chuyến bay:</strong> ' + currentFlightData.flightNo +
                        '<br><strong>Giá mới:</strong> ' + formatPrice(newFare) + ' VND'
                    );

                    // alert('✅ ' + (data.message || 'Cập nhật giá vé thành công!') + 
                    //       '\n\nChuyến bay: ' + currentFlightData.flightNo + 
                    //       '\nGiá mới: ' + formatPrice(newFare) + ' VND');

                    // Cập nhật lại originalFare trong currentFlightData
                    currentFlightData.originalFare = parseInt(newFare);

                } else {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editFlightModal'));
                    if (modal) {
                        modal.hide();
                    }
                    // Hiển thị lỗi từ API
                    showModalNotify('error',
                        ('Cập nhật thất bại') +
                        '<br><br><strong>Có lỗi xảy ra</strong>'
                    );
                    // alert('❌ Cập nhật thất bại!\n' + (data.message || 'Có lỗi xảy ra'));
                }

            } catch (e) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('editFlightModal'));
                if (modal) {
                    modal.hide();
                }
                console.error('Error parsing response:', e);
                showModalNotify('error',
                    ('Cập nhật thất bại') +
                    '<br><br><strong>Lỗi xử lý dữ liệu phản hồi!</strong>'
                );
                // alert('❌ Lỗi xử lý dữ liệu phản hồi!\n' + e.message);
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', { xhr, status, error });

            let errorMessage = '⚠️ Lỗi kết nối API!\n\n';

            if (xhr.status === 0) {
                errorMessage += 'Không thể kết nối đến server';
            } else if (xhr.status === 404) {
                errorMessage += 'Không tìm thấy endpoint (404)';
            } else if (xhr.status === 500) {
                errorMessage += 'Lỗi server (500)';
            } else {
                errorMessage += 'Mã lỗi: ' + xhr.status + '\n' + error;
            }

            // Thử parse error response
            try {
                const errorData = JSON.parse(xhr.responseText);
                if (errorData.message) {
                    errorMessage += '\n\nChi tiết: ' + errorData.message;
                }
            } catch (e) {
                // Không parse được thì bỏ qua
            }
            showModalNotify('error',
                (errorMessage)
            );
        },
        complete: function () {
            // Reset trạng thái button
            btnUpdate.innerHTML = originalText;
            btnUpdate.disabled = false;
        }
    });
}
function displaySingleFlightUpdate(response) {
    if (!response || response.status !== 1) {
        throw new Error('Lỗi khi cập nhật chuyến bay');
    }

    const flightData = response.data;

    // Tìm và cập nhật chuyến bay trong danh sách hiện tại
    const flightCard = document.querySelector(`[data-flight-no="${flightData.flightNo}"]`);

    if (flightCard) {
        // Cập nhật giá hiển thị
        const priceElement = flightCard.querySelector('.price');
        if (priceElement) {
            priceElement.textContent = formatPrice(flightData.fare) + ' VND';
        }

        // Highlight chuyến bay vừa cập nhật
        flightCard.style.border = '2px solid #10b981';
        flightCard.style.backgroundColor = '#f0fdf4';

        setTimeout(() => {
            flightCard.style.border = '';
            flightCard.style.backgroundColor = '';
        }, 3000);
    }

    // Thông báo thành công
    alert('✅ ' + (response.message || 'Cập nhật giá vé thành công!') +
        '\n\nChuyến bay: ' + flightData.flightNo +
        '\nGiá mới: ' + formatPrice(flightData.fare) + ' VND' +
        (response.description?.fare ? '\n' + response.description.fare : ''));
}


// Hàm cập nhật giá đã chọn (từ code gốc của bạn)
function updateSelectedFare(flightNo, ticketClass, fare) {
    const flightItem = document.querySelector(`.flight-item[data-flight="${flightNo}"]`);
    if (!flightItem) return;

    if (ticketClass) {
        const ticketClassDiv = flightItem.querySelector(".selected-ticketClass");
        if (ticketClassDiv) ticketClassDiv.textContent = ticketClass;
    }

    const priceDiv = flightItem.querySelector(".selected-price");
    if (priceDiv) priceDiv.textContent = formatPrice(fare);
}

// Hàm được gọi từ nút "Sửa" trong danh sách chuyến bay
function selectFlight(flightNo) {
    // Tìm thông tin chuyến bay từ DOM hoặc từ data đã lưu
    const flightItem = document.querySelector(`.flight-item[data-flight="${flightNo}"]`);
    if (!flightItem) {
        showModalNotify('warning',
            '<strong>Không tìm thấy thông tin chuyến bay!</strong>'
        );
        return;
    }

    // Lấy thông tin từ DOM (bạn cần điều chỉnh để lấy đúng data)
    const routeCode = flightItem.querySelector('.route-code').textContent;
    const [depCode, desCode] = routeCode.split('-');
    const priceText = flightItem.querySelector('.selected-price').textContent.replace(/\D/g, '');
    const fare = parseInt(priceText);
    const airlineCode = flightItem.getAttribute('data-airline');
    const carrierCode = flightItem.getAttribute('data-carrier');

    // Lấy dữ liệu chi tiết
    const flightData = {
        depName: flightItem.dataset.depname,
        desName: flightItem.dataset.desname,
        depTime: flightItem.dataset.deptime,
        arvTime: flightItem.dataset.arvtime,
        depDate: flightItem.dataset.depdate,
        arvDate: flightItem.dataset.arvdate
    };

    // Sau đó gọi openEditModal
    openEditModal(
        flightNo,
        airlineCode,
        carrierCode,
        depCode,
        desCode,
        fare,
        flightData.depName,
        flightData.desName,
        flightData.depTime,
        flightData.arvTime,
        flightData.depDate,
        flightData.arvDate
    );
}
// Khởi tạo khi trang được tải
document.addEventListener('DOMContentLoaded', function () {
    // Gắn sự kiện submit cho form
    const searchForm = document.getElementById('searchForm');
    const fareButton = document.getElementById('btnFare');
    fareButton.addEventListener('click', function (event) {
        event.preventDefault();
        searchFlight(event, 0);
    });
    searchForm.addEventListener('submit', function (event) {
        event.preventDefault();
        searchFlight(event, 1);
    });
    // if (searchForm) {
    //     searchForm.addEventListener('submit', searchFlight);
    // }

    // Set ngày mặc định cho date inputs
    // const today = new Date().toISOString().split('T')[0];
    // document.getElementById('departDate').value = today;
});

