src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"

// Hàm format giá tiền
function formatPrice(fare) {
    return new Intl.NumberFormat('vi-VN').format(fare);
}

// Hàm format ngày
function formatDate(dateString) {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
}

// Hàm lấy thông tin hãng bay
function getAirlineLogo(airlineCode) {
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
    const airlineInfo = getAirlineLogo(flight.airlineCode);
    
    let html = `
        <div class="flight-item" data-flight="${flight.flightNo}">

            <div class="flight-left">
                <div class="airline-logo">
                    <img src="${airlineInfo.logo}" alt="${airlineInfo.name}">
                </div>
                
                <div class="flight-times">
                    <div class="time-info">
                        <span class="time">${flight.depTime} - ${flight.arvTime}</span>
                    </div>
                    <div class="airline-name">${flight.airline}</div>
                </div>
                
                <div class="flight-route">
                    <div class="route-code">${flight.dep}-${flight.des}</div>
                    <div class="duration">${flight.nDuration}</div>
                </div>

                <div class="flight_code">
                    <div class="flight-number">${flight.flightNo}</div>
                    <a class="flight-details">Details</a>
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
                <a class="dropdown-item d-flex justify-content-between align-items-center" 
                href="#"
                onclick="updateSelectedFare('${flight.flightNo}', '${ticketClass}', ${fare})">
                    <div>
                        <div><strong>${ticketClass}</strong> - <span class="text-muted">${fareBasis}</span></div>
                        <small class="text-success">còn lại ${availableSeats} chỗ</small>
                    </div>
                    <span class="text-danger fw-bold">${formatPrice(fare)} VND</span>
                </a>
            </li>
        `;

    }).join('');
}

function updateSelectedFare(flightNo, ticketClass, fare) {
    const flightItem = document.querySelector(`.flight-item[data-flight="${flightNo}"]`);
    if (!flightItem) return;

    // cập nhật ticketClass
    const ticketClassDiv = flightItem.querySelector(".selected-ticketClass");
    if (ticketClassDiv) ticketClassDiv.textContent = ticketClass;

    // cập nhật giá
    const priceDiv = flightItem.querySelector(".selected-price");
    if (priceDiv) priceDiv.textContent = new Intl.NumberFormat('vi-VN').format(fare);
}
// Hàm load và hiển thị dữ liệu
async function loadFlightData() {
    try {
        const response = await fetch('response.json');
        const data = await response.json();

        if (!data || data.error !== 0) {
            throw new Error('Lỗi khi đọc dữ liệu chuyến bay');
        }

        const depFlights = data.data.dep;
        const retFlights = data.data.ret;

        // Cập nhật thông tin header
        if (depFlights.length > 0) {
            document.getElementById('routeInfo').textContent = 
                `${depFlights[0].depCityName} ✈ ${depFlights[0].desCityName}`;
            document.getElementById('dateInfo').textContent = 
                formatDate(depFlights[0].depDate);
            
            // Cập nhật tiêu đề chuyến đi
            document.getElementById('depTitle').textContent = 
                `Chuyến bay đi - ${formatDate(depFlights[0].depDate)}`;
        }

        if (retFlights.length > 0) {
            // Cập nhật tiêu đề chuyến về
            document.getElementById('retTitle').textContent = 
                `Chuyến bay về - ${formatDate(retFlights[0].depDate)}`;
        }

        // Render danh sách chuyến bay đi
        const depFlightList = document.getElementById('depFlightList');
        depFlightList.innerHTML = depFlights.map((flight, index) => 
            renderFlightItem(flight, index)
        ).join('');

        // Render danh sách chuyến bay về
        const retFlightList = document.getElementById('retFlightList');
        retFlightList.innerHTML = retFlights.map((flight, index) => 
            renderFlightItem(flight, index)
        ).join('');

    } catch (error) {
        console.error('Lỗi:', error);
        document.getElementById('depFlightList').innerHTML = 
            '<div class="loading" style="color: #dc2626;">Không thể tải dữ liệu. Vui lòng thử lại sau.</div>';
        document.getElementById('retFlightList').innerHTML = 
            '<div class="loading" style="color: #dc2626;">Không thể tải dữ liệu. Vui lòng thử lại sau.</div>';
    }
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
    event.target.classList.add('active');
    
    event.preventDefault();
}

// Hàm xử lý chọn chuyến bay
function selectFlight(flightNo) {
    alert('Đã chọn chuyến bay: ' + flightNo);
    // Thêm logic xử lý chọn chuyến bay ở đây
}

// Load dữ liệu khi trang được tải
document.addEventListener('DOMContentLoaded', loadFlightData);