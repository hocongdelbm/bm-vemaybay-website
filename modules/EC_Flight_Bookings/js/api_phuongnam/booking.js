const ENDPOINT_AUTO_BOOK = "index.php?entryPoint=entryPointAutoBook";
var bookingId = '';
var ticketType = ''; // '1':Domestic ; '2':International
var statusAutoBook = 1;

$(document).ready(function () {
    bookingId = $(`#formDetailView input[name="record"]`).val();
    ticketType = $(`input[name="ticket_type"]`).val();

    // Open auto book dialog
    $("#btnAutoBook").click(function () {
        let listItineraryId = getSelectedItinerariesData();
        let listPassengers = getSelectedPassengersData(); // Object
        let listPassengerId = Object.keys(listPassengers);

        if(bookingId.length * listItineraryId.length * listPassengerId.length != 0) {
            let adtCount = 0;
            let chdCount = 0;
            let infCount = 0;
            Object.entries(listPassengers).forEach(([key, value]) => {
                if(value === '0' || value === 0) adtCount++;
                else if(value === '1' || value === 1) chdCount++;
                else if(value === '2' || value === 2) infCount++;
            });
            if(adtCount < 1) {
                showToastNotify("warning", "Booking phải có người lớn");
                return;
            }
            if(adtCount < infCount) {
                showToastNotify("warning", "Số lượng em bé nhiều hơn người lớn");
                return;
            }

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
                            showModalNotify("error", objData.message ?? "Lỗi trong quá trình xử lý", objData.description ?? "");
                            console.error(objData);
                        }
                    }
                    catch (e) {
                        showModalNotify("error", "Lỗi trong quá trình xử lý", e.message);
                        console.error(e);
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

    // Show steps in auto book dialog
    $(document).on("click", "#confirmAutoBook", async function() {
        try {
            statusAutoBook = 1;
            
            /******  STEP 1: RESEARCHING FLIGHTS INFO  ******/
            var step = 1;
            var airlineCodes = $('input[name="airlineCode[]"]').map((i, el) => el.value).get();
            var depCodes = $('input[name="depCode[]"]').map((i, el) => el.value).get();
            var desCodes = $('input[name="desCode[]"]').map((i, el) => el.value).get();
            var flightDate = $('input[name="flightDate[]"]').map((i, el) => el.value).get();
            var ticketClass = $('input[name="ticketClass[]"]').map((i, el) => el.value).get();
            var flightNo = $('input[name="flightNo[]"]').map((i, el) => el.value).get();
            var within24h = $('input[name="within24h[]"]').map((i, el) => el.value).get();
            var adt = 0;
            var chd = 0;
            var inf = 0;
            var listPassengers = getSelectedPassengersData();
            var listPassengerId = Object.keys(listPassengers);
            if(listPassengers) {
                Object.entries(listPassengers).forEach(([key, value]) => {
                    if(value == '0') adt++;
                    else if(value == '1') chd++;
                    else if(value == '2') inf++;
                });
            }
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
                adt: adt,
                chd: chd,
                inf: inf,
                adtPrice: adtPrice[i],
                chdPrice: chdPrice[i],
                infPrice: infPrice[i]
            }));

            var response1 = {};
            var textItiSuccess = '<b style="color:#4285f4; margin-left:8px">OK</b>';
            if(statusAutoBook == 1) {
                let iti0 = `Hành trình ${searchInfo[0]['depCode']} đi ${searchInfo[0]['desCode']}`;
                showStepsInDialogAutoBook(step, iti0);
                response1 = await $.ajax({
                    url: ENDPOINT_AUTO_BOOK,
                    method: 'POST',
                    contentType: "application/json",
                    dataType: "json",
                    data: JSON.stringify(searchInfo[0])
                });
                if(!response1 || !response1.status || response1.status == 0) {
                    if(response1.message == "Unmatched information") {
                        showStepsInDialogAutoBook(step, iti0, 'Thông tin chưa khớp, vui lòng kiểm tra lại');
                        showUpdateFlightData(searchInfo[0]['depCode'], searchInfo[0]['desCode'], searchInfo[0]['adt'], searchInfo[0]['chd'], searchInfo[0]['inf'], response1.data.updateData ?? {});
                        if($('#flight-info-dep').length) {
                            $('#autoBookForm').animate({
                                scrollTop: $('#flight-info-dep').position().top
                            }, 500);
                        }
                    }
                    else showStepsInDialogAutoBook(step, iti0, response1.message ?? 'Lỗi, vui lòng thử lại sau');
                    return;
                }
            }
            else if(statusAutoBook == 0) {
                showStepsInDialogAutoBook(step, '', 'Đã hủy quá trình đặt chỗ');
                return;
            }

            // Research inbound if available
            var response2 = {};
            if(statusAutoBook == 1 && searchInfo[1] !== undefined) {
                let iti0 = `Hành trình ${searchInfo[0]['depCode']} đi ${searchInfo[0]['desCode']}${textItiSuccess}</br>`;
                let iti1 = `${iti0}Hành trình ${searchInfo[1]['depCode']} đi ${searchInfo[1]['desCode']}`;
                showStepsInDialogAutoBook(step, iti1);
                response2 = await $.ajax({
                    url: ENDPOINT_AUTO_BOOK,
                    method: 'POST',
                    contentType: "application/json",
                    dataType: "json",  
                    data: JSON.stringify(searchInfo[1])
                });
                if(!response2 || !response2.status || response2.status == 0) {
                    if(response2.message == "Unmatched information") {
                        showStepsInDialogAutoBook(step, iti1, 'Thông tin chưa khớp, vui lòng kiểm tra lại');
                        showUpdateFlightData(searchInfo[1]['depCode'], searchInfo[1]['desCode'], searchInfo[1]['adt'], searchInfo[1]['chd'], searchInfo[1]['inf'], response2.data.updateData ?? {});
                        if($('#flight-info-ret').length) {
                            $('#autoBookForm').animate({
                                scrollTop: $('#flight-info-ret').position().top
                            }, 500);
                        }
                    }
                    else showStepsInDialogAutoBook(step, iti1, response2.message ?? 'Lỗi, vui lòng thử lại sau');
                    return;
                }

                showStepsInDialogAutoBook(step, iti1 + textItiSuccess);
            }
            else if(statusAutoBook == 0) {
                showStepsInDialogAutoBook(step, '', 'Đã hủy quá trình đặt chỗ');
                return;
            }

            /******  STEP 2: VERIFY  ******/
            step = 2;
            var verifyResponse = {};
            var isWithin24h = within24h.includes('1') ? 1 : 0;
            if(statusAutoBook == 1) {
                setTimeout(() => {showStepsInDialogAutoBook(step);}, 300);

                var flights = {};
                if(response1.data && response1.data.standardData && Object.keys(response1.data.standardData).length > 0) {
                    flights[0] = response1.data.standardData;
                }
                if(response2.data && response2.data.standardData && Object.keys(response2.data.standardData).length > 0) {
                    flights[1] = response2.data.standardData;
                }

                if (Object.keys(flights).length > 0) {
                    verifyResponse = await $.ajax({
                        url: ENDPOINT_AUTO_BOOK,
                        method: 'POST',
                        contentType: "application/json",
                        dataType: "json",  
                        data: JSON.stringify({
                            'action': 'verify',
                            'bookingId': bookingId,
                            'flights': flights,
                            'listPassengerId': listPassengerId,
                            'isWithin24h': isWithin24h
                        })
                    });

                    if(!verifyResponse || !verifyResponse.status || verifyResponse.status == 0 || !verifyResponse.requestBody) {
                        showStepsInDialogAutoBook(step, '', verifyResponse.message ?? 'Lỗi, vui lòng thử lại sau');
                        return;
                    }
                }
                else {
                    showStepsInDialogAutoBook(step, '', 'Thiếu thông tin xác thực');
                    console.error(flights);
                }
            }
            else if(statusAutoBook == 0) {
                showStepsInDialogAutoBook(step, '', 'Đã hủy quá trình đặt chỗ');
                return;
            }

            /******  STEP 3: BOOKING  ******/
            step = 3;
            var bookingResponse = {};
            if(statusAutoBook == 1) {
                let caption = isWithin24h ? 'Xuất vé cận' : 'Đặt chỗ';
                showStepsInDialogAutoBook(step, caption + "...");
                $('#btnAutoBookAction').prop('disabled', true); // Disable button action
                
                bookingResponse = await $.ajax({
                    url: ENDPOINT_AUTO_BOOK,
                    method: 'POST',
                    contentType: "application/json",
                    dataType: "json",  
                    data: JSON.stringify({
                        'action': 'booking',
                        'requestBody': verifyResponse.requestBody,
                        'bookingId': bookingId,
                        'listPassengerId': listPassengerId,
                    })
                });

                // Enable button action
                $('#btnAutoBookAction').html('Đóng');
                $('#btnAutoBookAction').attr("action", "close");
                $('#btnAutoBookAction').prop('disabled', false);

                if(!bookingResponse || !bookingResponse.status || bookingResponse.status == 0) {
                    showStepsInDialogAutoBook(step, caption, bookingResponse.message ?? 'Lỗi, vui lòng thử lại sau');
                    return;
                }

                // Display BookingCodes (PNR) to client
                caption = isWithin24h ? 'Xuất vé thành công' : 'Đặt chỗ thành công';
                const bookingCodes = bookingResponse.data.map(item => item.BookingCode);
                bookingCodes.forEach(code => {
                    caption += caption.length == 0 ? `<b>${code}</b>` : `<br/><b>${code}</b>`;
                });
                showStepsInDialogAutoBook(step, caption, '', 1);
            }
            else if(statusAutoBook == 0) {
                showStepsInDialogAutoBook(step, '', 'Đã hủy quá trình đặt chỗ');
                return;
            }
        }
        catch (e) {
            console.error(e);
            hideDialogAutoBook();
            showModalNotify(0, 'Lỗi trong quá trình giữ chỗ, vui lòng thử lại sau', e.message);
        }
    });

    $(document).on("click", "#btnAutoBookAction", function() {
        let action = $(this).attr('action');

        if(action == 'cancel') {
            if(statusAutoBook == 1) {
                statusAutoBook = 0;
                $(this).attr("action", "close");
                $(this).html("Đóng");
                $(this).prop('disabled', true);
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
        else if(action == 'complete') {
            hideDialogAutoBook();
            $('.container-waiting').show();
            setTimeout(function () {location.reload();}, 500);
        }
    });

    // Update data (flight datetime, fares) to BM
    $(document).on("click", ".btn-update-auto-book", function() {
        let data = $(this).attr('data');

        if(data && data.length > 0) {
            $.ajax({
                url: ENDPOINT_AUTO_BOOK,
                type: "POST",
                contentType: "application/json", 
                data: atob(data),
                beforeSend: function () {
                    hideDialogAutoBook();
                    $('.container-waiting').show();
                },
                success: function (response) {
                    try {
                        const objData = JSON.parse(response);
                        if(objData.status == 1) {
                            $("#btnAutoBook").trigger("click");
                            return;
                        }
                        else {
                            $('.container-waiting').hide();
                            hideDialogAutoBook();
                            showModalNotify("error", objData.message ?? "Cập nhật không thành công, vui lòng F5 và thử lại");
                            console.error(objData);
                        }
                    }
                    catch (e) {
                        $('.container-waiting').hide();
                        hideDialogAutoBook();
                        showModalNotify("error", "Cập nhật không thành công, vui lòng F5 và thử lại", e.message);
                        console.error(e);
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

    // Header
    const header = document.createElement('div');
    header.className = 'header';
    header.innerHTML = `<h4 class="title">Auto book</h4>`;
    const closeBtn = document.createElement('button');
    closeBtn.className = 'close-btn';
    closeBtn.textContent = '✕';
    closeBtn.onclick = () => dialog.remove();
    header.appendChild(closeBtn);

    const content = document.createElement('div');

    // Render Flight + Fare Section
    var BookingWithin24h = false;
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
        if(!fareColumns) return '';

        if(!BookingWithin24h) BookingWithin24h = flight.within24h;
        return `<div id="flight-info-${dir}" class="flight-info">
            <input type="hidden" name="airlineCode[]" value="${flight.airlineCode}" readonly />
            <input type="hidden" name="depCode[]" value="${flight.depCode}" readonly />
            <input type="hidden" name="desCode[]" value="${flight.desCode}" readonly />
            <input type="hidden" name="flightDate[]" value="${flight.flightDate}" readonly />
            <input type="hidden" name="ticketClass[]" value="${flight.ticketClass}" readonly />
            <input type="hidden" name="flightNo[]" value="${flight.flightNo}" readonly />
            <input type="hidden" name="within24h[]" value="${flight.within24h}" readonly />

            <div class="section-title">
                ${title}
                <img class="ms-3" src="${getLinkImageAirline(flight.airlineCode)}" alt="${flight.airlineCode}" style="max-width:90px" />
                ${flight.within24h ? '<span class="within24h">Vé cận</span>' : ''}
            </div>
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
                <div><b><span style="font-weight:700;color:${p.salutation == 'Ms' ? '#f7689e' : '#2d87d5'}">${p.salutation}.</span> ${p.name}</b></div>
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
        <div class="info-row">Tên liên hệ: <b>${contactInfo.title || ''} ${contactInfo.name || ''}</b></div>
        <div class="info-row">Số điện thoại: <b>${contactInfo.phone || ''}</b></div>
        <div class="info-row">Email: <b>${contactInfo.email || ''}</b></div>
        <div class="info-row">Địa chỉ: <b>${contactInfo.address || ''}</b></div>
    </div>`;
    content.innerHTML += contactHTML;


    // Note
    let noteHTML = `<div class="note p-2 mt-3" style="background:#e0ecfc">
        ${BookingWithin24h ? '<p style="color:red">- Đây là <b>vé cận</b>, sẽ tiến hành thanh toán ngay.</p>' : '<p>- <b>Vé cận</b> sẽ tiến hành thanh toán ngay.</p>'}
        <p>- Kiểm tra kỹ càng thông tin trước khi xác nhận.</p>
    </div>`;
    content.innerHTML += noteHTML;


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
    dialog.showModal();
    $('#autoBookDialog').draggable(); // Using only by Jquery
}

function hideDialogAutoBook() {
    let dialog = document.getElementById('autoBookDialog');
    if (dialog) dialog.remove();
}

function showStepsInDialogAutoBook(current_step = 1, current_caption = '', current_error = '', is_finished = 0) {
    // Show overlay
    const dialog = $('#autoBookDialog');
    const dialogOverlay = dialog.find('.loading-overlay');
    // dialogOverlay.css('height', dialog[0].scrollHeight + 'px').addClass('active');

    let icon_finished = `<svg width="22px" height="22px" viewBox="0 0 16 16" stroke="#fff" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><polyline points="2.75 8.75,6.25 12.25,13.25 4.75"></polyline></g></svg>`;
    let icon_error = `<svg width="18px" height="18px" class="me-1" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:sub;"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zm-1.5-5.009c0-.867.659-1.491 1.491-1.491.85 0 1.509.624 1.509 1.491 0 .867-.659 1.509-1.509 1.509-.832 0-1.491-.642-1.491-1.509zM11.172 6a.5.5 0 0 0-.499.522l.306 7a.5.5 0 0 0 .5.478h1.043a.5.5 0 0 0 .5-.478l.305-7a.5.5 0 0 0-.5-.522h-1.655z" fill="#ff0000"></path></g></svg>`;
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
            caption: "",
            error: ""
        },
    };

    let buttonAction = 'cancel';
    let stepsHTML = '';
    $.each(steps, function(stepNum, stepData) {
        let stepClass = '';
        if(stepNum == current_step) stepClass = 'step-active';
        else if(stepNum < current_step) stepClass = 'step-finished';
        if(is_finished == 1) stepClass = 'step-finished';
        stepClass += (stepNum == Object.keys(steps).length) ? ' last-step' : '';

        let caption = '', error = '', loaderHTML = '';
        if(stepNum == current_step) {
            caption = current_caption && current_caption.length > 0 ? current_caption : stepData.caption;
            error = current_error && current_error.length > 0 ? current_error : stepData.error;
            if(error.length == 0 && is_finished == 0 && statusAutoBook == 1) loaderHTML = '<div><div class="loader-step"></div></div>';
            else if(error.length > 0) {
                error = icon_error + error;
                buttonAction = 'close';
            }
        }
        else {
            caption = stepData.caption;
        }

        stepsHTML += `<div class="step ${stepClass}">
            <div>
                <div class="circle">${(stepNum < current_step || is_finished == 1) ? icon_finished : stepNum}</div>
            </div>
            <div>
                <div class="title">${stepData.title}</div>
                <div class="caption">${caption}</div>
                <div class="error">${error}</div>
            </div>
            ${loaderHTML}
        </div>`;
    });

    let buttonClass = 'btn-secondary';
    let buttonText = buttonAction == 'cancel' ? 'Hủy' : 'Đóng';
    if(is_finished == 1) {
        buttonAction = 'complete';
        buttonClass = 'btn-primary';
        buttonText = 'Hoàn tất';
    }
    let content = `<div class="loading-content">
        ${stepsHTML}
        <div class="buttons">
            <button type="button" id="btnAutoBookAction" class="btn ${buttonClass}" action="${buttonAction}">${buttonText}</button>
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
    let list_passenger = {};
    $('#tbl_pax input[name=check-passenger]:checked').each(function () {
        let pass_id = $(this).attr("passenger-id");
        let pass_type = $(this).attr("passenger-type");
        if (pass_id !== undefined && pass_id.length > 30) {
            list_passenger[pass_id] = pass_type;
        }
    });
    return list_passenger;
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

function getLinkImageAirline(airlineCode) {
    return img_src = `custom/themes/default/images/airline-icon-120x40/${airlineCode}.gif`;
    // let domesticAirline = ["VJ", "VN", "BL", "QH", "VU"];
    // if(domesticAirline.includes(airlineCode)) {
    //     return img_src = `custom/themes/default/images/airline-icon-120x40/${airlineCode}.gif`;
    // }
}