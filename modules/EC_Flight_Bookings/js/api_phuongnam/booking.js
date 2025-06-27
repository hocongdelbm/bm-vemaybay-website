const ENDPOINT_AUTO_BOOK = "index.php?entryPoint=entryPointAutoBook";

$(document).ready(function () {
    let booking_id = $(`#formDetailView input[name="record"]`).val();
    let ticket_type = $(`input[name="ticket_type"]`).val(); // '1':Domestic ; '2':International

    $("#btn-auto-book").click(function () {
        let list_journey_id = [];
        $('#itinerary_tbl input[name=check-journey]:checked').each(function () {
            let journey_id = $(this).attr("journey-id");
            if (journey_id !== undefined && journey_id.length > 30) list_journey_id.push(journey_id);
        });

        let list_passenger_id = [];
        $('#tbl_pax input[name=check-passenger]:checked').each(function () {
            let pass_id = $(this).attr("passenger-id");
            if (pass_id !== undefined && pass_id.length > 30) list_passenger_id.push(pass_id);
        });

        if(booking_id.length * list_journey_id.length * list_passenger_id.length != 0) {
            $.ajax({
                url: ENDPOINT_AUTO_BOOK,
                type: "POST",
                data: {
                    action: "get_info_to_auto_book",
                    booking_id: booking_id,
                    list_journey_id: list_journey_id,
                    list_passenger_id: list_passenger_id
                },
                beforeSend: function () {
                    $('.container-waiting').show();
                },
                success: function (response) {
                    try {
                        $('.container-waiting').hide();
                        const objData = JSON.parse(response);
                        if(objData.status == 1) {
                            showDiaglogAutoBook(objData.data);
                        }
                        else {
                            showToastNotify("error", objData.message ?? "Lỗi trong quá trình xử lý");
                            console.error(objData);
                        }
                    }
                    catch (e) {
                        console.error("JSON parse error:", e);
                        console.error("Response was:", response);
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $('.container-waiting').hide();
                    console.error(XMLHttpRequest);
                    console.error("Status: " + textStatus);
                    console.error("Error: " + errorThrown);
                }
            });
        }

        if(!list_journey_id.length) showToastNotify("warning", "Vui lòng chọn hành trình");
        else if(!list_passenger_id.length) showToastNotify("warning", "Vui lòng chọn hành khách");
    });

    $(document).on("click", "#confirmAutoBook", function() {
        var airlineCodes = $('input[name="airlineCode[]"]').map((i, el) => el.value).get();
        var depCodes = $('input[name="depCode[]"]').map((i, el) => el.value).get();
        var desCodes = $('input[name="desCode[]"]').map((i, el) => el.value).get();
        var flightDate = $('input[name="flightDate[]"]').map((i, el) => el.value).get();
        var ticketClass = $('input[name="ticketClass[]"]').map((i, el) => el.value).get();
        var adt = $('input[name="adt[]"]').map((i, el) => el.value).get();
        var chd = $('input[name="chd[]"]').map((i, el) => el.value).get();
        var inf = $('input[name="inf[]"]').map((i, el) => el.value).get();

        var searchInfo = airlineCodes.map((_, i) => ({
            airlineCode: airlineCodes[i],
            depCode: depCodes[i],
            desCode: desCodes[i],
            flightDate: flightDate[i],
            ticketClass: ticketClass[i],
            adt: adt[i],
            chd: chd[i],
            inf: inf[i]
        }));

        console.log(searchInfo);
    });
});

function showDiaglogAutoBook(bookingData) {
    const existingDialog = document.getElementById('autoBookDialog');
    if (existingDialog) existingDialog.remove(); // Prevent multiple dialogs

    const dialog = document.createElement('div');
    dialog.className = 'auto-book-dialog';
    dialog.id = 'autoBookDialog';

    const autoBookForm = document.createElement('form');
    autoBookForm.id = 'autoBookForm';
    autoBookForm.className = 'dialog-box';

    const closeBtn = document.createElement('button');
    closeBtn.className = 'close-btn';
    closeBtn.textContent = '✕';
    closeBtn.onclick = () => dialog.remove();

    const header = document.createElement('div');
    header.className = 'header';
    header.textContent = 'Auto book';

    const content = document.createElement('div');

    // Render Flight + Fare Section
    const { dep, ret } = bookingData.journeys;
    const depFare = bookingData.fareDetails.dep;
    const retFare = bookingData.fareDetails.ret ?? [];
    const passengerTypes = {'0': 'Người lớn', '1': 'Trẻ em', '2': 'Em bé'};
    const passengerTextTypes = {'0': 'adt', '1': 'chd', '2': 'inf'};
    function renderFlightWithFare(flight, fareArray, dir) {
        var totalAmount = 0;
        var title = dir == 'ret' ? '✈️ Chuyến về' : '✈️ Chuyến đi';
        var label = dir == 'ret' ? 'chuyến về' : 'chuyến đi';
        const fareByType = {};

        // Create fare HTML for each type: Adult, Child, Infant
        const fareColumns = ['0', '1', '2'].map(type => {
            const fare = fareArray[parseInt(type)];
            if (!fare) return '';

            totalAmount += fare.price * fare.qty;

            return `<div class="fare-column">
                <input type="hidden" name="${passengerTextTypes[type]}[]" value="${fare.qty}" readonly />
                <div class="info-row"><b>${passengerTypes[type]}</b></div>
                <div class="info-row">Giá vé: <b>${fare.fareFormat}</b></div>
                <div class="info-row">Thuế (VAT): <b>${fare.taxFormat}</b></div>
                <div class="info-row">Phí: <b>${fare.feeFormat}</b></div>
                <div class="info-row">Tổng mua: <b style="color:red">${fare.priceFormat}</b></div>
            </div>`;
        }).join('');

        // Only display the fare section if there is at least one fare column
        if (!fareColumns) return '';

        function formatNumber(number) {
            sep = num_grp_sep;
            dec = dec_sep;
            const parts = number.toString().split('.');
            const integerPart = parts[0];
            const decimalPart = parts[1] || '';

            const formattedInt = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, sep);
            return decimalPart ? `${formattedInt}${dec}${decimalPart}` : formattedInt;
        }

        return `<div class="flight-info">
            <input type="hidden" name="airlineCode[]" value="${flight.airlineCode}" readonly />
            <input type="hidden" name="depCode[]" value="${flight.depCode}" readonly />
            <input type="hidden" name="desCode[]" value="${flight.desCode}" readonly />
            <input type="hidden" name="flightDate[]" value="${flight.flightDate}" readonly />
            <input type="hidden" name="ticketClass[]" value="${flight.ticketClass}" readonly />

            <div class="section-title">${title}<span class="airline ms-3">(${flight.airlineCode})</span></div>
            <div class="info-row d-flex justify-content-between">
                <div>Hành trình: <b>${flight.depCode} → ${flight.desCode}</b></div>
                <div>Mã chuyến: <b>${flight.flightNo}</b></div>
            </div>
            <div class="info-row d-flex justify-content-between">
                <div>Ngày giờ bay: <b>${flight.flightDate.replace(' ', ' lúc ')}</b></div>
                <div>Hạng vé: <b>${flight.ticketClass}</b></div>
            </div>
            <div class="info-row"><b>💰 Chi tiết giá vé</b></div>
            <div class="fare-row">${fareColumns}</div>
            <div class="d-flex justify-content-end align-items-center mt-2">
                <label style="font-size:15px">Tổng mua ${label}: </label>
                <b style="color:red !important; font-size:15px"><input type="text" value="${formatNumber(totalAmount)} VND" class="npvalue" readonly /></b>
            </div>
        </div>`;
    }
    if (dep) content.innerHTML += renderFlightWithFare(dep, depFare, 'dep');
    if (ret) content.innerHTML += renderFlightWithFare(ret, retFare, 'ret');

    // Passengers
    const passengersHTML = Object.values(bookingData.passengers).map(p => `
        <div class="passenger-info">
            <div class="info-row">Họ tên: <b>${p.salutation}. ${p.name}</b></div>
            <div class="info-row">Loại: <b>${passengerTypes[p.type]}</b></div>
            <div class="info-row">Ngày sinh: <b>${p.birthday}</b></div>
            <div class="info-row">CCCD/Passport: <b>${p.passportNumber || p.cic}</b></div>
        </div>
    `).join('');
    content.innerHTML += `<div class="section">
        <div class="section-title">Thông tin hành khách</div>
        ${passengersHTML}
    </div>`;

    // Contact
    const contactInfo = bookingData.contact;
    const contactHTML = `<div class="contact-info">
        <div class="section-title">Thông tin liên hệ</div>
        <div class="info-row">Tên liên hệ: <b>${contactInfo.name || ''}</b></div>
        <div class="info-row">Số điện thoại: <b>${contactInfo.phone || ''}</b></div>
        <div class="info-row">Email: <b>${contactInfo.email || ''}</b></div>
    </div>`;
    content.innerHTML += contactHTML;

    // Footer
    const footer = document.createElement('div');
    footer.className = 'dialog-footer';
    // Create Confirm button
    const confirmBtn = document.createElement('button');
    confirmBtn.id = 'confirmAutoBook';
    confirmBtn.className = 'dialog-btn confirm-btn';
    confirmBtn.type = 'button';
    confirmBtn.textContent = 'Xác nhận';
    // confirmBtn.addEventListener('click', () => {dialog.remove(); });
    // Create Cancel button
    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'dialog-btn cancel-btn';
    cancelBtn.textContent = 'Huỷ';
    cancelBtn.addEventListener('click', () => {dialog.remove(); });
    // Add both buttons to footer
    footer.appendChild(confirmBtn);
    footer.appendChild(cancelBtn);

    autoBookForm.appendChild(closeBtn);
    autoBookForm.appendChild(header);
    autoBookForm.appendChild(content);
    autoBookForm.appendChild(footer);
    dialog.appendChild(autoBookForm);
    document.body.appendChild(dialog);
}
