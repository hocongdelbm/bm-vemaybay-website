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

// const fareInputAll = document.getElementById('modal_fare_all');
// fareInputAll.addEventListener('input', function (e) {
//     // Cho phép dấu '-' ở đầu, sau đó là các chữ số
//     let value = e.target.value.replace(/(?!^-)[^\d]/g, '');

//     // Nếu chỉ nhập '-' thì cho phép
//     if (value === '-') {
//         e.target.value = '-';
//         return;
//     }

//     // Nếu rỗng thì thôi
//     if (!value) {
//         e.target.value = '';
//         return;
//     }

//     // Format dấu chấm phân cách nếu có số
//     let isNegative = value.startsWith('-');
//     let numeric = value.replace('-', '');
//     numeric = numeric.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
//     e.target.value = isNegative ? '-' + numeric : numeric;
// });

// 
// Thêm vào phần xử lý input modal_fare_all
const fareInputAll = document.getElementById('modal_fare_all');
const fareChangeMessage = document.getElementById('fareChangeMessage');
const fareChangeText = document.getElementById('fareChangeText');

fareInputAll.addEventListener('input', function (e) {
    // Cho phép dấu '-' ở đầu, sau đó là các chữ số
    let value = e.target.value.replace(/(?!^-)[^\d]/g, '');

    // Nếu chỉ nhập '-' thì cho phép
    if (value === '-') {
        e.target.value = '-';
        fareChangeMessage.style.display = 'none';
        return;
    }

    // Nếu rỗng thì thôi
    if (!value) {
        e.target.value = '';
        fareChangeMessage.style.display = 'none';
        return;
    }

    // Format dấu chấm phân cách nếu có số
    let isNegative = value.startsWith('-');
    let numeric = value.replace('-', '');
    numeric = numeric.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    e.target.value = isNegative ? '-' + numeric : numeric;

    // Hiển thị thông báo
    const numericValue = parseInt(value.replace('-', ''));
    
    if (!isNaN(numericValue) && numericValue > 0) {
        fareChangeMessage.style.display = 'block';
        
        if (isNegative) {
            // Giảm giá
            fareChangeText.innerHTML = `
                <i class="bi bi-arrow-down-circle text-increase"></i> 
                Giảm giá <span class="text-increase">${formatPrice(numericValue)} VND</span> cho toàn bộ chuyến bay
            `;
            fareChangeText.className = 'fw-bold ';
        } else {
            // Tăng giá
            fareChangeText.innerHTML = `
                <i class="bi bi-arrow-up-circle text-increase"></i> 
                Tăng giá thêm <span class="text-increase">${formatPrice(numericValue)} VND</span> cho toàn bộ chuyến bay
            `;
            fareChangeText.className = 'fw-bold ';
        }
    } else {
        fareChangeMessage.style.display = 'none';
    }
});

// Reset thông báo khi đóng modal
document.getElementById('editFlightModal_all').addEventListener('hidden.bs.modal', function () {
    fareChangeMessage.style.display = 'none';
    fareInputAll.value = '';
});
// 


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

function getApiField(data, fieldName) {
    if (!data || typeof data !== 'object') return '';
    if (data[fieldName]) return data[fieldName];
    if (data.data && typeof data.data === 'object' && data.data[fieldName]) return data.data[fieldName];
    return '';
}

function formatCacheDateTime(dateTimeString) {
    if (!dateTimeString) return '';
    const normalized = String(dateTimeString).trim().replace('T', ' ');
    const match = normalized.match(/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})/);
    if (!match) return '';

    const year = match[1];
    const month = match[2];
    const day = match[3];
    const hour = match[4];
    const minute = match[5];

    return `${hour}:${minute} ${day}/${month}/${year}`;
}

function setCacheTimeChip(chipId, valueId, dateTimeString) {
    const chipEl = document.getElementById(chipId);
    const valueEl = document.getElementById(valueId);
    if (!chipEl || !valueEl) return;

    const formatted = formatCacheDateTime(dateTimeString);
    if (!formatted) {
        chipEl.classList.add('d-none');
        valueEl.textContent = '';
        return;
    }

    valueEl.textContent = formatted;
    chipEl.classList.remove('d-none');
}

function clearCacheTimeChips() {
    setCacheTimeChip('depCacheTime', 'depCacheTimeValue', '');
    setCacheTimeChip('retCacheTime', 'retCacheTimeValue', '');
}

function escapeHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function normalizeFareSystemName(rawText) {
    const text = String(rawText || '').trim();
    if (!text) return '';

    const statusMatch = text.match(/Controller\(\s*(-?\d+)\s*\)/);
    const isErrorStatus = statusMatch ? Number(statusMatch[1]) === 0 : false;

    let baseName = '';
    const controllerMatch = text.match(/([A-Za-z0-9_]+)Controller\b/);
    if (controllerMatch && controllerMatch[1]) {
        baseName = controllerMatch[1];
    } else {
        baseName = text
            .replace(/\(-?\d+\)/g, '')
            .replace(/[\[\]]/g, '')
            .replace(/^Fare system\s*/i, '')
            .replace(/Controller\b/g, '')
            .trim();
    }

    if (!baseName) return '';
    return isErrorStatus ? `${baseName} (Lỗi)` : baseName;
}

function parseFareSystemByLeg(source) {
    const chunks = [];

    function collect(rawValue) {
        const text = String(rawValue || '').trim();
        if (!text) return;

        const bracketMatches = [...text.matchAll(/\[([^\]]+)\]/g)];
        if (bracketMatches.length > 0) {
            bracketMatches.forEach(match => {
                chunks.push(match[1]);
            });
            return;
        }

        chunks.push(text);
    }

    if (Array.isArray(source)) {
        source.forEach(item => collect(item));
    } else {
        collect(source);
    }

    const names = chunks.map(normalizeFareSystemName).filter(Boolean);
    const depName = names[0] || '';
    const retName = names[1] || depName;

    return {
        dep: depName,
        ret: retName
    };
}

function buildNoFlightHtml(message, fareSystemName) {
    const sourceHtml = fareSystemName
        ? `<div class="no-flight-source">${escapeHtml(fareSystemName)}</div>`
        : '';

    return `<div class="no-flight-wrap"><div class="loading" style="color: #dc2626;">${message}</div>${sourceHtml}</div>`;
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
        },
        '9G': {
            color: '#000000',
            name: '9G',
            logo: 'https://gmi.vietjet.net/images/img/Images-brand/9G.png'
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
                                data-dep="${flight.dep}"
                                data-des="${flight.des}"
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
                    <a class="flight-details d-none">Chi tiết chuyến bay</a>
                </div>
                <div class="source">
                    <div style="font-size:.85rem;font-weight:500;">Nguồn</div>
                    <div style="font-size:.85rem;">${flight.source || ''}</div>
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

function clearCountdown(elementId) {
    const el = document.getElementById(elementId);
    if (el) el.textContent = '';

    if (countdownTimers[elementId]) {
        clearInterval(countdownTimers[elementId]);
        delete countdownTimers[elementId];
    }
}

function resetFlightMetaDisplay() {
    clearCacheTimeChips();
    clearCountdown('depCountdown');
    clearCountdown('retCountdown');
}

// Hàm hiển thị dữ liệu chuyến bay
function displayFlightData(data) {
    if (!data || data.error !== 0) {
        throw new Error('Lỗi khi đọc dữ liệu chuyến bay');
    }

    const payload = data.data || {};
    const depData = payload.dep;
    const retData = payload.ret;
    const depFlights = Array.isArray(depData) ? depData : (depData ? [depData] : []);
    const retFlights = Array.isArray(retData) ? retData : (retData ? [retData] : []);
    const depCacheCreatedAt = getApiField(data, 'dep_cache_created_at');
    const depCacheExpiresAt = getApiField(data, 'dep_cache_expires_at');
    const retCacheExpiresAt = getApiField(data, 'ret_cache_expires_at');
    const retCacheCreatedAt = getApiField(data, 'ret_cache_created_at');
    const fareSystemByLeg = parseFareSystemByLeg(data.source);

    // Cập nhật thông tin header
    if (depFlights.length > 0) {
        document.getElementById('depTitle').textContent =
            `Chuyến bay đi - ${formatDate(depFlights[0].depDate)}`;

        const btnFareDepAll = document.getElementById('btnFare_dep_all');
        btnFareDepAll.style.display = 'block';
        btnFareDepAll.onclick = () => openEditModalAll(
            depFlights[0].airlineCode,
            depFlights[0].details[0].carrierCode,
            depFlights[0].dep,
            depFlights[0].des,
            depFlights[0].depName,
            depFlights[0].desName,
            depFlights[0].depDate
        );


        setCacheTimeChip('depCacheTime', 'depCacheTimeValue', depCacheCreatedAt);

        if (depCacheExpiresAt) {
            startCountdown(depCacheExpiresAt, 'depCountdown');
        } else {
            clearCountdown('depCountdown');
        }

        // Render danh sách chuyến bay đi
        const depFlightList = document.getElementById('depFlightList');
        depFlightList.innerHTML = depFlights.map((flight, index) =>
            renderFlightItem(flight, index)
        ).join('');
    } else {
        document.getElementById('depTitle').textContent = 'Chuyến bay đi';
        document.getElementById('btnFare_dep_all').style.display = 'none';
        setCacheTimeChip('depCacheTime', 'depCacheTimeValue', '');
        clearCountdown('depCountdown');
        document.getElementById('depFlightList').innerHTML =
            buildNoFlightHtml('Không tìm thấy chuyến bay đi nào.', fareSystemByLeg.dep);
    }

    if (retFlights.length > 0) {
        document.getElementById('retTitle').textContent =
            `Chuyến bay về - ${formatDate(retFlights[0].depDate)}`;

        const btnFareRetAll = document.getElementById('btnFare_ret_all');
        btnFareRetAll.style.display = 'block';
        btnFareRetAll.onclick = () => openEditModalAll(
            retFlights[0].airlineCode,
            retFlights[0].details[0].carrierCode,
            retFlights[0].dep,
            retFlights[0].des,
            retFlights[0].depName,
            retFlights[0].desName,
            retFlights[0].depDate
        );


        setCacheTimeChip('retCacheTime', 'retCacheTimeValue', retCacheCreatedAt);

        if (retCacheExpiresAt) {
            startCountdown(retCacheExpiresAt, 'retCountdown');
        } else {
            clearCountdown('retCountdown');
        }

        // Render danh sách chuyến bay về
        const retFlightList = document.getElementById('retFlightList');
        retFlightList.innerHTML = retFlights.map((flight, index) =>
            renderFlightItem(flight, index)
        ).join('');
    } else {
        document.getElementById('retTitle').textContent =
            `Chuyến bay về `;

        const btnFareRetAll = document.getElementById('btnFare_ret_all');
        btnFareRetAll.style.display = 'none';

        setCacheTimeChip('retCacheTime', 'retCacheTimeValue', '');
        clearCountdown('retCountdown');
        document.getElementById('retFlightList').innerHTML =
            buildNoFlightHtml('Không tìm thấy chuyến bay về nào.', fareSystemByLeg.ret);
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

    resetFlightMetaDisplay();
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
                        setCacheTimeChip('depCacheTime', 'depCacheTimeValue', '');
                        clearCountdown('depCountdown');

                        document.getElementById('retTitle').textContent =
                            `Chuyến bay về `;
                        setCacheTimeChip('retCacheTime', 'retCacheTimeValue', '');
                        clearCountdown('retCountdown');
                    } else if (message === "Invalid departure or destination") {
                        showModalNotify('warning',
                            '<strong>Nơi đi hoặc nơi đến không hợp lệ!</strong>'
                        );
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
                resetFlightMetaDisplay();
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

            resetFlightMetaDisplay();
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

// Hàm mở modal cập nhật dồng loạt
function openEditModalAll(airlineCode, carrierCode, depCode, desCode, depName, desName, depDate) {

    currentFlightData = { airlineCode, carrierCode, depCode, desCode, depName, desName, depDate };
    const airlineInfo = getAirlineInfo(carrierCode);

    // Hãng
    const airlineEl = document.getElementById('modal_airlineCode_display_all');
    airlineEl.textContent = airlineInfo.name;
    airlineEl.style.backgroundColor = airlineInfo.color;
    airlineEl.style.color = 'white';

    // Tuyến
    document.getElementById('modal_depName_all').textContent = `${depName}`;
    document.getElementById('modal_desName_all').textContent = `${desName}`;
    document.getElementById('modal_depCode_all').textContent = `(${depCode})`;
    document.getElementById('modal_desCode_all').textContent = `(${desCode})`;
    document.getElementById('modal_depTime_all').textContent = `${formatDate(depDate)}`;

    new bootstrap.Modal(document.getElementById('editFlightModal_all')).show();
}

// Hàm cập nhật giá vé với xử lý dữ liệu đầy đủ
function updateFlightFareAll() {
    const formatted = document.getElementById('modal_fare_all').value; // ví dụ: "1.234.000"
    const newFare = formatted.replace(/\./g, ''); // loại bỏ dấu chấm -> "1234000"

    const fareInputAll = document.getElementById('modal_fare_all');
    const fareError = document.getElementById('fareError');
    

    //  Validate với newFare đã clean
    if (newFare === "" || isNaN(newFare)) {
        fareError.style.display = 'block';
        fareInputAll.classList.add('is-invalid');
        return;
    }

    // Nếu hợp lệ -> ẩn thông báo lỗi
    fareError.style.display = 'none';
    fareInput.classList.remove('is-invalid');


    const btnUpdate = document.querySelector('.btn-update-all');
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
            method: "updateFlightAll",
            params: {
                airlineCode: currentFlightData.airlineCode,
                depCode: currentFlightData.depCode,
                desCode: currentFlightData.desCode,
                depDate: currentFlightData.depDate,
                fareChange: parseInt(newFare)
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
                    updateSelectedFareAll();

                    // Đóng modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editFlightModal_all'));
                    if (modal) {
                        modal.hide();
                    }

                    let message;
                    if (newFare < 0) {
                        message = `Giảm giá đồng loạt <strong>${formatPrice(Math.abs(newFare))} VND</strong> cho mỗi chuyến bay.`;
                    } else {
                        message = `Tăng giá đồng loạt thêm <strong>${formatPrice(newFare)} VND</strong> cho mỗi chuyến bay.`;
                    }

                    showModalNotify('success', message);

                    currentFlightData.originalFare = parseInt(newFare);

                } else {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editFlightModal_all'));
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

    // Validate với newFare (đã loại bỏ dấu chấm)
    if (newFare === "" || isNaN(newFare) || parseInt(newFare) <= 0) {
        fareError.style.display = 'block';
        fareInput.classList.add('is-invalid');
        return;
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
            method: "updateFlight",
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

function updateSelectedFareAll_v1(flightNo, seatClass, newFare) {
    // Tìm tất cả các phần tử flight-item có cùng tuyến bay (dep-des)
    const items = document.querySelectorAll('.flight-item');
    if (!items.length) return;

    items.forEach(item => {
        const dep = item.getAttribute('data-dep');
        const des = item.getAttribute('data-des');
        const airline = item.getAttribute('data-airline');

        // Nếu khớp với chuyến bay hiện tại
        if (
            dep === currentFlightData.depCode &&
            des === currentFlightData.desCode &&
            airline === currentFlightData.airlineCode
        ) {
            // Cập nhật giá hiển thị
            const fareEl = item.querySelector('.fare-price');
            if (fareEl) {
                // Nếu newFare âm => giảm giá
                const current = parseInt(fareEl.textContent.replace(/\D/g, '')) || 0;
                const updated = current + newFare;
                fareEl.textContent = formatPrice(updated > 0 ? updated : 0) + ' VND';
            }
        }
    });
}

function updateSelectedFareAll() {
    // Lấy giá trị fareChange từ input (đã được format)
    const formatted = document.getElementById('modal_fare_all').value;
    const fareChange = parseInt(formatted.replace(/\./g, '').replace(/-/g, '')); // Loại bỏ dấu chấm và dấu âm để lấy số thuần
    const isNegative = formatted.trim().startsWith('-'); // Kiểm tra có phải số âm không
    const finalFareChange = isNegative ? -fareChange : fareChange;

    // Tìm tất cả các flight-item có cùng tuyến bay
    const items = document.querySelectorAll('.flight-item');
    
    if (!items.length) {
        console.warn('Không tìm thấy chuyến bay nào để cập nhật');
        return;
    }

    let updatedCount = 0;

    items.forEach(item => {
        // Lấy thông tin từ data attributes
        const itemDepCode = item.getAttribute('data-dep') || item.querySelector('.route-code')?.textContent.split('-')[0];
        const itemDesCode = item.getAttribute('data-des') || item.querySelector('.route-code')?.textContent.split('-')[1];
        const itemAirline = item.getAttribute('data-airline');
        const itemDepDate = item.getAttribute('data-depdate');

        // Kiểm tra xem có khớp với chuyến bay đang cập nhật không
        const isMatch = 
            itemDepCode === currentFlightData.depCode &&
            itemDesCode === currentFlightData.desCode &&
            itemAirline === currentFlightData.airlineCode &&
            itemDepDate === currentFlightData.depDate;

        if (isMatch) {
            // Tìm element hiển thị giá
            const priceElement = item.querySelector('.selected-price');
            
            if (priceElement) {
                // Lấy giá hiện tại (loại bỏ ký tự không phải số)
                const currentPriceText = priceElement.textContent.replace(/\D/g, '');
                const currentPrice = parseInt(currentPriceText) || 0;
                
                // Tính giá mới = giá cũ + fareChange
                const newPrice = currentPrice + finalFareChange;
                
                // Đảm bảo giá không âm
                const finalPrice = Math.max(0, newPrice);
                
                // Cập nhật hiển thị với format
                priceElement.textContent = formatPrice(finalPrice);
                
                // Thêm hiệu ứng highlight (tùy chọn)
                item.style.transition = 'background-color 0.3s ease';
                item.style.backgroundColor = '#d1fae5';
                
                setTimeout(() => {
                    item.style.backgroundColor = '';
                }, 1500);
                
                updatedCount++;
            }
        }
    });

    console.log(`Đã cập nhật ${updatedCount} chuyến bay`);
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
    // Khởi tạo Select2 ở đây
    $('.airport-select').select2({
        placeholder: "Chọn sân bay ",
        allowClear: true,
        width: '100%'
    });
});
