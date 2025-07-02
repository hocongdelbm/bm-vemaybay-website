const ENDPOINT_AUTO_BOOK = "index.php?entryPoint=entryPointAutoBook";
var bookingId = '';
var ticketType = ''; // '1':Domestic ; '2':International
var statusAutoBook = 1;

$(document).ready(function () {
    bookingId = $(`#formDetailView input[name="record"]`).val();
    ticketType = $(`input[name="ticket_type"]`).val();

    $("#btnAutoBook").click(function () {
        let listItineraryId = getSelectedItinerariesData();
        let listPassengerId = getSelectedPassengersData();

        if(bookingId.length * listItineraryId.length * listPassengerId.length != 0) {
            $.ajax({
                url: ENDPOINT_AUTO_BOOK,
                type: "POST",
                contentType: "application/json", 
                data: JSON.stringify({
                    action: "get_info_to_auto_book",
                    bookingId: bookingId,
                    listItineraryId: listItineraryId,
                    listPassengerId: listPassengerId
                }),
                beforeSend: function () {
                    $('.container-waiting').show();
                },
                success: function (response) {
                    try {
                        $('.container-waiting').hide();
                        const objData = JSON.parse(response);
                        if(objData.status == 1) {
                            showDialogAutoBook(objData.data);
                        }
                        else {
                            showModalNotify("error", objData.message ?? "Lỗi trong quá trình xử lý");
                            console.error(objData);
                        }
                    }
                    catch (e) {
                        showModalNotify("error", "Lỗi trong quá trình xử lý");
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

        if(!listItineraryId.length) showToastNotify("warning", "Vui lòng chọn hành trình");
        else if(!listPassengerId.length) showToastNotify("warning", "Vui lòng chọn hành khách");
    });

    $(document).on("click", "#cancelAutoBook", function() {
        let action = $(this).attr('action');

        if(action == 'cancel') {
            if(statusAutoBook == 1) {
                statusAutoBook = 0;
                $(this).attr("action", "close");
                $(this).html("Đóng");
            }
            else if(statusAutoBook == 0) {
                statusAutoBook = 1;
                $('#autoBookDialog .loading-overlay').html('');
                $('#autoBookDialog .loading-overlay').removeClass('active');
            }
        }
        else if(action == 'close') {
            $('#autoBookDialog .loading-overlay').html('');
            $('#autoBookDialog .loading-overlay').removeClass('active');
        }
    });

    $(document).on("click", ".btn-update-auto-book", function() {
        let data = $(this).attr('data');

        if(data && data.length > 0) {
            $.ajax({
                url: ENDPOINT_AUTO_BOOK,
                type: "POST",
                contentType: "application/json", 
                data: atob(data),
                beforeSend: function () {
                    $('.container-waiting').show();
                },
                success: function (response) {
                    try {
                        // $('.container-waiting').hide();
                        const objData = JSON.parse(response);
                        if(objData.status == 1) {
                            // showModalNotify("success", "Cập nhật thành công");
                            $("#btnAutoBook").trigger("click");
                        }
                        else {
                            hideDialogAutoBook();
                            showModalNotify("error", objData.message ?? "Lỗi trong quá trình xử lý");
                            console.error(objData);
                        }
                    }
                    catch (e) {
                        hideDialogAutoBook();
                        showModalNotify("error", "Lỗi trong quá trình xử lý");
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
    });

    // STEPS
    $(document).on("click", "#confirmAutoBook", async function() {
        try {
            // STEP 1: RESEARCHING FLIGHTS INFO
            var step = 1;
            var airlineCodes = $('input[name="airlineCode[]"]').map((i, el) => el.value).get();
            var depCodes = $('input[name="depCode[]"]').map((i, el) => el.value).get();
            var desCodes = $('input[name="desCode[]"]').map((i, el) => el.value).get();
            var flightDate = $('input[name="flightDate[]"]').map((i, el) => el.value).get();
            var ticketClass = $('input[name="ticketClass[]"]').map((i, el) => el.value).get();
            var flightNo = $('input[name="flightNo[]"]').map((i, el) => el.value).get();
            var adt = $('input[name="adt[]"]').map((i, el) => el.value).get();
            var chd = $('input[name="chd[]"]').map((i, el) => el.value).get();
            var inf = $('input[name="inf[]"]').map((i, el) => el.value).get();
            var adtPrice = $('input[name="adtPrice[]"]').map((i, el) => el.value).get();
            var chdPrice = $('input[name="chdPrice[]"]').map((i, el) => el.value).get();
            var infPrice = $('input[name="infPrice[]"]').map((i, el) => el.value).get();

            var searchInfo = airlineCodes.map((_, i) => ({
                action: 'research', // Add action to call entrypoint
                airlineCode: airlineCodes[i],
                depCode: depCodes[i],
                desCode: desCodes[i],
                flightDate: flightDate[i],
                ticketClass: ticketClass[i],
                flightNo: flightNo[i],
                adt: adt[i],
                chd: chd[i],
                inf: inf[i],
                adtPrice: adtPrice[i],
                chdPrice: chdPrice[i],
                infPrice: infPrice[i]
            }));

            var response1 = {}, response2 = {};
            if(statusAutoBook == 1) {
                let iti0 = `Hành trình ${searchInfo[0]['depCode']} đi ${searchInfo[0]['desCode']}`;
                showStepsInDialogAutoBook(step, iti0);
                const response1 = await $.ajax({
                    url: ENDPOINT_AUTO_BOOK,
                    method: 'POST',
                    contentType: "application/json",
                    dataType: "json",
                    data: JSON.stringify(searchInfo[0])
                });
                if(response1 && response1.status && response1.status == 0) {
                    if(response1.message == "Unmatched information") {
                        showStepsInDialogAutoBook(step, iti0, 'Thông tin chưa khớp, vui lòng kiểm tra lại');
                        showUpdateFlightData(searchInfo[0]['depCode'], searchInfo[0]['desCode'], searchInfo[0]['adt'], searchInfo[0]['chd'], searchInfo[0]['inf'], response1.data.updateData ?? {});
                    }
                    else showStepsInDialogAutoBook(step, iti0, response1.message);
                    return;
                }
                else {
                    showStepsInDialogAutoBook(step, iti0, response1.message ?? 'Lỗi, vui lòng thử lại sau');
                    return;
                }
            }

            // Research inbound if available
            if(statusAutoBook == 1 && searchInfo[1] !== undefined) {
                let iti0 = `Hành trình ${searchInfo[0]['depCode']} đi ${searchInfo[0]['desCode']} <b style="color:#4285f4">OK</b></br>`;
                let iti1 = `${iti0}Hành trình ${searchInfo[1]['depCode']} đi ${searchInfo[1]['desCode']}`;
                showStepsInDialogAutoBook(step, iti1);
                const response2 = await $.ajax({
                    url: ENDPOINT_AUTO_BOOK,
                    method: 'POST',
                    contentType: "application/json",
                    dataType: "json",  
                    data: JSON.stringify(searchInfo[1])
                });
                if(response2 && response2.status && response2.status == 0) {
                    if(response2.message == "Unmatched information") {
                        showStepsInDialogAutoBook(step, iti1, 'Thông tin chưa khớp, vui lòng kiểm tra lại');
                        showUpdateFlightData(searchInfo[1]['depCode'], searchInfo[1]['desCode'], searchInfo[1]['adt'], searchInfo[1]['chd'], searchInfo[1]['inf'], response2.data.updateData ?? {});
                    }
                    else showStepsInDialogAutoBook(step, iti1, response2.message);
                    return;
                }
                else {
                    showStepsInDialogAutoBook(step, iti1, response2.message ?? 'Lỗi, vui lòng thử lại sau');
                    return;
                }
            }

            // STEP 2: VERIFY
            if(statusAutoBook == 1) {
                let flights = {};
                if(response1.data && response1.data.standartData && Object.keys(response1.data.standartData).length > 0) {
                    flights[0] = response1.data.standartData;
                }
                if(response2.data && response2.data.standartData && Object.keys(response2.data.standartData).length > 0) {
                    flights[1] = response2.data.standartData;
                }

                if (Object.keys(flights).length > 0) {
                    const verifyResponse = await $.ajax({
                        url: ENDPOINT_AUTO_BOOK,
                        method: 'POST',
                        contentType: "application/json",
                        dataType: "json",  
                        data: JSON.stringify({
                            'action': 'verify',
                            'bookingId': bookingId,
                            'flights': flights,
                            'listPassengerId': getSelectedPassengersData()
                        })
                    });

                    console.log(verifyResponse);
                }
            }
        }
        catch (error) {
            console.error('Error during AJAX calls:', error);
        }
    });
});

function showDialogAutoBook(bookingData) {
    const existingDialog = document.getElementById('autoBookDialog');
    if (existingDialog) existingDialog.remove(); // Prevent multiple dialogs

    const dialog = document.createElement('dialog');
    dialog.className = 'auto-book-dialog';
    dialog.id = 'autoBookDialog';

    const autoBookForm = document.createElement('form');
    autoBookForm.id = 'autoBookForm';
    autoBookForm.className = 'dialog-form';

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
        var direction = dir == 'ret' ? 1 : 0;
        const fareByType = {};

        // Create fare HTML for each type: Adult, Child, Infant
        const fareColumns = ['0', '1', '2'].map(type => {
            const fare = fareArray[parseInt(type)];
            if (!fare) return '';

            totalAmount += fare.price * fare.qty;

            return `<div class="fare-column">
                <input type="hidden" name="${passengerTextTypes[type]}[]" value="${fare.qty}" readonly />
                <input type="hidden" name="${passengerTextTypes[type]}Price[]" value="${fare.price}" readonly />
                <input type="hidden" id="${passengerTextTypes[type]}Fare${flight.depCode}${flight.desCode}" value="${fare.fare}" readonly />
                <input type="hidden" id="${passengerTextTypes[type]}Tax${flight.depCode}${flight.desCode}" value="${fare.tax}" readonly />
                <input type="hidden" id="${passengerTextTypes[type]}Fee${flight.depCode}${flight.desCode}" value="${fare.fee}" readonly />
                <input type="hidden" id="${passengerTextTypes[type]}Price${flight.depCode}${flight.desCode}" value="${fare.price}" readonly />

                <div class="info-row"><b>${passengerTypes[type]} x1</b></div>
                <div class="info-row">
                    <div class="lbl">Giá vé:</div>
                    <div class="value" id="${passengerTextTypes[type]}Fare${flight.depCode}${flight.desCode}Display">
                        <span class="old-value"></span>
                        <span class="cur-value">${fare.fareFormat}</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="lbl">Thuế (VAT):</div>
                    <div class="value" id="${passengerTextTypes[type]}Tax${flight.depCode}${flight.desCode}Display">
                        <span class="old-value"></span>
                        <span class="cur-value">${fare.taxFormat}</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="lbl">Phí:</div>
                    <div class="value" id="${passengerTextTypes[type]}Fee${flight.depCode}${flight.desCode}Display">
                        <span class="old-value"></span>
                        <span class="cur-value">${fare.feeFormat}</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="lbl">Tổng mua:</div>
                    <div class="value" id="${passengerTextTypes[type]}Price${flight.depCode}${flight.desCode}Display">
                        <b class="old-value"></b>
                        <b class="cur-value">${fare.priceFormat}</b>
                    </div>
                </div>
            </div>`;
        }).join('');

        // Only display the fare section if there is at least one fare column
        if (!fareColumns) return '';

        return `<div class="flight-info">
            <input type="hidden" name="airlineCode[]" value="${flight.airlineCode}" readonly />
            <input type="hidden" name="depCode[]" value="${flight.depCode}" readonly />
            <input type="hidden" name="desCode[]" value="${flight.desCode}" readonly />
            <input type="hidden" name="flightDate[]" value="${flight.flightDate}" readonly />
            <input type="hidden" name="ticketClass[]" value="${flight.ticketClass}" readonly />
            <input type="hidden" name="flightNo[]" value="${flight.flightNo}" readonly />

            <div class="section-title">${title}<span class="airline ms-3">(${flight.airlineCode})</span></div>
            <div class="info-row d-flex justify-content-between">
                <div>Hành trình: <b>${flight.depCode} → ${flight.desCode}</b></div>
                <div>Mã chuyến: <b>${flight.flightNo}</b></div>
            </div>
            <div class="info-row d-flex justify-content-between">
                <div class="d-flex gap-1">
                    <div>Ngày giờ bay:</div>
                    <div id="flightDate${flight.depCode}${flight.desCode}Display">
                        <span class="old-value"></span>
                        <b class="cur-value">${flight.flightDate.replace(' ', ' lúc ')}</b>
                    </div>
                </div>
                <div>Hạng vé: <b>${flight.ticketClass}</b></div>
            </div>
            <div class="info-row mt-1"><b>💰 Chi tiết giá vé</b></div>
            <div class="fare-row">${fareColumns}</div>
            <div class="d-flex justify-content-end align-items-center mt-2">
                <label style="font-size:15px">Tổng mua ${label}: </label>
                <b style="color:red !important; font-size:15px">
                    <input type="text" value="${formatNumber(totalAmount)} VND" id="totalAmount${flight.depCode}${flight.desCode}" class="npvalue" readonly />
                </b>
            </div>
            <center>
                <button type="button" id="btnUpdate${flight.depCode}${flight.desCode}" class="btn-update-auto-book btn btn-warning mt-2" direction="${direction}" style="display:none">Cập nhật</button>
            </center>
        </div>`;
    }
    if (dep) content.innerHTML += renderFlightWithFare(dep, depFare, 'dep');
    if (ret) content.innerHTML += renderFlightWithFare(ret, retFare, 'ret');


    // Passengers
    const passengersHTML = Object.values(bookingData.passengers).map(p => `
        <div class="passenger-info">
            <div class="info-row d-flex justify-content-between">
                <div><b>${p.salutation}. ${p.name}</b></div>
                <div><b>${passengerTypes[p.type]}</b></div>
            </div>
            <div class="info-row d-flex justify-content-between">
                <div>CCCD/Passport: <b>${p.passportNumber || p.cic}</b></div>
                <div>Ngày sinh: <b>${p.birthday}</b></div>
            </div>
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
    // Create Cancel button
    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'dialog-btn cancel-btn';
    cancelBtn.textContent = 'Huỷ';
    cancelBtn.addEventListener('click', () => {dialog.remove(); });
    // Add both buttons to footer
    footer.appendChild(confirmBtn);
    footer.appendChild(cancelBtn);
    

    // Loading
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'loading-overlay';


    autoBookForm.appendChild(closeBtn);
    autoBookForm.appendChild(header);
    autoBookForm.appendChild(content);
    autoBookForm.appendChild(footer);
    // autoBookForm.appendChild(loadingOverlay);
    
    // Create the wrapper div
    const formWrapper = document.createElement('div');
    formWrapper.classList.add('form-wrapper');
    formWrapper.appendChild(autoBookForm);
    formWrapper.appendChild(loadingOverlay);

    dialog.appendChild(formWrapper);
    document.body.appendChild(dialog);
}

function hideDialogAutoBook() {
    let dialog = document.getElementById('autoBookDialog');
    if (dialog) dialog.removeChild(dialog);
}

function showStepsInDialogAutoBook(current_step = 1, current_caption = '', current_error = '') {
    // Show overlay
    const dialog = $('#autoBookDialog');
    const dialogOverlay = dialog.find('.loading-overlay');
    // dialogOverlay.css('height', dialog[0].scrollHeight + 'px').addClass('active');

    let icon_finished = `<svg width="22px" height="22px" viewBox="0 0 16 16" stroke="#fff" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><polyline points="2.75 8.75,6.25 12.25,13.25 4.75"></polyline></g></svg>`;
    let steps = {
        1: {
            title: "Đối sánh thông tin chuyến bay",
            caption: "",
            error: ""
        },
        2: {
            title: "Xác thực thông tin",
            caption: "Quá trình xác thực với NCC",
            error: ""
        },
        3: {
            title: "Tiến hành đặt chỗ trên hãng",
            caption: "Sẽ xuất vé nếu là vé cận",
            error: ""
        },
    };

    let actionButton = 'cancel';
    let stepsHTML = '';
    $.each(steps, function(stepNum, stepData) {
        let stepClass = '';
        if(stepNum == current_step) stepClass = 'step-active';
        else if(stepNum < current_step) stepClass = 'step-finished';
        stepClass += (stepNum == Object.keys(steps).length) ? ' last-step' : '';

        let caption = '', error = '', loaderHTML = '';
        if(stepNum == current_step) {
            caption = current_caption && current_caption.length > 0 ? current_caption : stepData.caption;
            error = current_error && current_error.length > 0 ? current_error : stepData.error;
            if(error.length == 0) loaderHTML = '<div><div class="loader-step"></div></div>';
            else {
                error = `&#128712; ` + error;
                actionButton = 'close';
            }
        }
        else {
            caption = stepData.caption;
        }

        stepsHTML += `<div class="step ${stepClass}">
            <div>
                <div class="circle">${stepNum < current_step ? icon_finished : stepNum}</div>
            </div>
            <div>
                <div class="title">${stepData.title}</div>
                <div class="caption">${caption}</div>
                <div class="error">${error}</div>
            </div>
            ${loaderHTML}
        </div>`;
    });

    let content = `<div class="loading-content">
        ${stepsHTML}
        <div class="buttons">
            <button type="button" id="cancelAutoBook" class="btn btn-secondary" action="${actionButton}">${actionButton == 'cancel' ? 'Hủy' : 'Đóng'}</button>
        </div>
    </div>`;
    dialogOverlay.html(content);
    dialogOverlay.addClass('active');
}

function showUpdateFlightData(depCode, desCode, adtCount, chdCount, infCount, updateData) {
    // Update flightDate
    if('flightDate' in updateData) {
        let oldFlightDate = $(`#flightDate${depCode}${desCode}Display .cur-value`).text();
        let newFlightDate = updateData.flightDate.replace(' ', ' lúc ');
        $(`#flightDate${depCode}${desCode}Display .cur-value`).text(newFlightDate);
        $(`#flightDate${depCode}${desCode}Display .old-value`).text(oldFlightDate);
    }

    // Update fares
    let updateTotalAmount = 0;
    const passengerTypes = [
        { type: 'adt', count: adtCount },
        { type: 'chd', count: chdCount },
        { type: 'inf', count: infCount }
    ];
    passengerTypes.forEach(({ type, count }) => {
        const fareKey = `${type}Fare`;
        const fareData = updateData[fareKey];

        if (fareData) {
            for (let key in fareData) {
                const capKey = key.charAt(0).toUpperCase() + key.slice(1);
                const newValue = fareData[key];
                const inputId = `input#${type}${capKey}${depCode}${desCode}`;
                const displayPrefix = `#${type}${capKey}${depCode}${desCode}Display`;
                const oldValue = $(inputId).val();

                if (newValue != oldValue) {
                    $(`${displayPrefix} .cur-value`).text(formatNumber(newValue));
                    $(`${displayPrefix} .old-value`).text(formatNumber(oldValue));
                }

                if (key === 'price') {
                    updateTotalAmount += newValue * count;
                }
            }
        }
    });
    $(`input#totalAmount${depCode}${desCode}`).val(formatNumber(updateTotalAmount) + ' VND');

    // Button update
    let direction = $(`#btnUpdate${depCode}${desCode}`).attr('direction'); 
    updateData.action = 'update_data';
    updateData.bookingId = bookingId;
    updateData.direction = parseInt(direction);
    $(`#btnUpdate${depCode}${desCode}`).attr('data', btoa(JSON.stringify(updateData)));
    $(`#btnUpdate${depCode}${desCode}`).show();
}

function getSelectedPassengersData() {
    let list_passenger_id = [];
    $('#tbl_pax input[name=check-passenger]:checked').each(function () {
        let pass_id = $(this).attr("passenger-id");
        if (pass_id !== undefined && pass_id.length > 30) list_passenger_id.push(pass_id);
    });
    return list_passenger_id;
}

function getSelectedItinerariesData() {
    let list_itinerary_id = [];
    $('#itinerary_tbl input[name=check-journey]:checked').each(function () {
        let itinerary_id = $(this).attr("itinerary-id");
        if (itinerary_id !== undefined && itinerary_id.length > 30) list_itinerary_id.push(itinerary_id);
    });
    return list_itinerary_id;
}

function formatNumber(number) {
    sep = num_grp_sep;
    dec = dec_sep;
    const parts = number.toString().split('.');
    const integerPart = parts[0];
    const decimalPart = parts[1] || '';

    const formattedInt = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, sep);
    return decimalPart ? `${formattedInt}${dec}${decimalPart}` : formattedInt;
}

function unformatNumber(formattedStr) {
    const sep = num_grp_sep;
    const dec = dec_sep;

    // Escape group separator if needed (e.g., dot)
    const escapedSep = sep.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const escapedDec = dec.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

    // Remove group separator, replace decimal with dot
    const cleaned = formattedStr
        .replace(new RegExp(escapedSep, 'g'), '')
        .replace(new RegExp(escapedDec), '.');

    return parseFloat(cleaned);
}