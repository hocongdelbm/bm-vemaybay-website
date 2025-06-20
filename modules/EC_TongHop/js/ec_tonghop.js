$(document).ready(function () {
    init();
    function init(){
        getTraffic();
        getLogs();
        handleAccordion();
        $(".search_button.btn.btn-primary").on("click", function(){
            $(".online_data.row").empty();
            handleQuickRange();
            getTraffic();
            getLogs();
        });
        $(".reset_button.btn.btn-warning").on("click", function(){
            $("select[name='url_selected']").val("");
            $("select[name='time_selected']").val("");
        })
        $(document).on("click", ".extend_log_detail", function () {
            $(this).toggleClass("rotated");
            const logId = $(this).attr("log-id");
            getDetails(logId, this);
        });
    }

    function handleQuickRange(){
        const quickRange = $("select[name='time_selected']").val(); 
        if (quickRange) {
            const range = getQuickTimeRange(quickRange);
            $("#from_date").val(range.from);
            $("#to_date").val(range.to);
        }
    }

    function getQuickTimeRange(value) {
        const now = new Date();
        const pad = num => String(num).padStart(2, "0");

        function format(date, endOfDay = false) {
            return `${pad(date.getDate())}-${pad(date.getMonth() + 1)}-${date.getFullYear()} ${endOfDay ? "23:59:59" : "00:00:00"}`;
        }

        const result = { from: "", to: "" };

        switch (value) {
            case "yesterday":
                const y = new Date(now);
                y.setDate(y.getDate() - 1);
                result.from = format(y);
                result.to = format(y, true);
                break;
            case "daybefore":
                const db = new Date(now);
                db.setDate(db.getDate() - 2);
                result.from = format(db);
                result.to = format(db, true);
                break;
            case "current_week":
                const cw = new Date(now);
                const day = cw.getDay() || 7;
                cw.setDate(cw.getDate() - day + 1); // start of week
                const cwEnd = new Date(cw);
                cwEnd.setDate(cw.getDate() + 6);
                result.from = format(cw);
                result.to = format(cwEnd, true);
                break;
            case "previous_week":
                const pw = new Date(now);
                const currentDay = pw.getDay() || 7;
                pw.setDate(pw.getDate() - currentDay - 6);
                const pwEnd = new Date(pw);
                pwEnd.setDate(pw.getDate() + 6);
                result.from = format(pw);
                result.to = format(pwEnd, true);
                break;
            case "current_month":
                const cmStart = new Date(now.getFullYear(), now.getMonth(), 1);
                const cmEnd = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                result.from = format(cmStart);
                result.to = format(cmEnd, true);
                break;
            case "previous_month":
                const pmStart = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                const pmEnd = new Date(now.getFullYear(), now.getMonth(), 0);
                result.from = format(pmStart);
                result.to = format(pmEnd, true);
                break;
        }

        return result;
    }

    function getTraffic() {
        let endPoint = "index.php?entryPoint=entryPointGetTraffic";
        let domain_name  = $("select[name='url_selected'] option:selected").text();
        if(domain_name == ""){
            domain_name = "timchuyenbay.com";
        }
        let from_date    = $("#from_date").val();
        let to_date      = $("#to_date").val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });
        $.ajax({
            type: "POST",
            url: endPoint,
            contentType: 'application/json',
            data: JSON.stringify({
                "domain"    : domain_name,
                "from_date" : from_date,
                "to_date"   : to_date
            }),
            beforeSend: function () {
                $(".online_data").append(`
                    <div class="online_data_content_loading d-flex flex-column justify-content-center align-items-center">
			            <div class="spinner online_data_waiting"></div>
			            <span class="online_data_waiting_text">Đang tải dữ liệu...</span>
		            </div>
                `);
                $(".btn.btn-primary.extend_btn").addClass("hide");
            },
            success: function (response) {
                let data = Object.keys(response.data).map((key) => [key, response.data[key]]);
                let check = checkEmptyData(data);
                if(check == true){
                    $(".chart_content").empty();
                    $(".online_data").empty();
                    $(".online_data_content").empty();
                    $(".online_data_content_loading").hide();
                    $(".online_data").append(`
                    <div class="online_data_content d-flex flex-column justify-content-center align-items-center">
                         <span class="online_data_failed_text">Không có dữ liệu, vui lòng chọn ngày hoặc tên miền khác</span>
                    </div>
                    `);
                    return;
                }
                $(".chart_content").empty();
                $(".online_data").empty();
                $(".online_data_content").empty();
                $(".online_data_content_loading").hide();
                $(".online_data").append(`<div class="online_data_title col-lg-12 justify-content-center d-flex flex-column align-items-center">
                        <h3>Traffic của ${domain_name}</h3>
                    </div>
                `);
                data.forEach(group => {
                    const [key, values] = group;
                    let groupHtml = `<div class="online_data_content mb-4 col-lg-3">`;
                    let formatted_key = key.replace("top_", "");
                    formatted_key = formatted_key.charAt(0).toUpperCase() + formatted_key.slice(1);;
                    $translate_key = {
                        "Channels"  : "Referers",
                        "Devices"   : "Thiết bị",
                        "Browsers"  : "Trình duyệt",
                        "Countries" : "Quốc gia",
                        "Ips"       : "IPs",
                    };
                    if($translate_key[formatted_key]){
                        formatted_key = $translate_key[formatted_key];
                    }
                    groupHtml += `<h6 class="attribute_title">${formatted_key}</h6>`;

                    const total = values.reduce((sum, item) => sum + item.value, 0);
                    values.forEach(item => {
                        const percent = ((item.value / total) * 100).toFixed(1);
                        groupHtml += `
                            <div class="traffic_value d-flex flex-row-reverse mb-2" style="justify-content:space-between">
                                <div class="d-flex align-items-center">
                                    <div class="mx-1">${item.value}</div>
                                    <div class="progress px-0" style="width: 150px">
                                        <div class="progress-bar" role="progressbar" style="width:${percent}%;">
                                        </div>
                                    </div>
                                </div>
                                <label class="" title="${item.label}" for="progress">${item.label}</label>
                            </div>
                        `;
                    });

                    groupHtml += `</div>`;
                    $(".online_data").append(groupHtml);
                });
                handleAccordion();
            },
            error: function (xhr) {
                $(".online_data").empty();
                $(".online_data_content_loading").hide();
                if(xhr.status === 401){
                    $message = "Bạn không có quyền truy cập dữ liệu này";
                }else if(xhr.status === 400){
                    $message = "Thời gian hoặc tên miền không hợp lệ, vui lòng kiểm tra lại";
                }else if(xhr.status === 500){
                    $message = "Lỗi máy chủ, vui lòng thử lại sau";
                }else{
                    $message = "Không truy xuất được dữ liệu, vui lòng thử lại sau";
                }
                $(".online_data").append(`
                    <div class="online_data_content d-flex flex-column justify-content-center align-items-center">
                         <span class="online_data_failed_text">${message}</span>
                    </div>
                    `);
            }
        });
    }

    function getLogs(){
        let endPoint = "index.php?entryPoint=entryPointAccessLogs";
        let domain_name = $("select[name='url_selected'] option:selected").text();
        if(domain_name == ""){
            domain_name = "timchuyenbay.com";
        }
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });
        $.ajax({
            type: "POST",
            url: endPoint,
            contentType: 'application/json',
            data: JSON.stringify({
                "domain"    : domain_name,
            }),
            beforeSend: function () {
                 $("#visitor_tbody").empty();
                 $("#visitor_tbody").append(`
                    <div class="online_data_content_loading d-flex flex-column justify-content-center align-items-center">
			            <div class="spinner online_data_waiting"></div>
			            <span class="online_data_waiting_text">Đang tải dữ liệu...</span>
		            </div>
                `);
            },
            success: function(response) {
                $("#visitor_tbody").empty();
                $(".online_data_content_loading").hide();
                const data = response.data;
                renderVisitorTableRows(data, 1, 10);
                // renderPagination(data, 10);
                setupPagination(data, 1, 10);
            },
            error: function(err) {
                console.error("Lỗi khi lấy dữ liệu:", err);
            }
        });
    }

    function getDetails(id, btn) {
        const currentRow = $(btn).closest("tr");
        const detailRow = currentRow.next(".detail-row");

        if (detailRow.length > 0) {
            detailRow.toggle();
            return;
        }

        let endPoint = "index.php?entryPoint=entryPointDetailLogs";
        let domain_name = $("select[name='url_selected'] option:selected").text() || "timchuyenbay.com";

        currentRow.after(`
            <tr class="detail-row">
                <td colspan="4">
                    <div class="online_data_content_loading d-flex justify-content-center align-items-center">
                        <div class="spinner online_data_waiting"></div>
                        <span class="online_data_waiting_text">Đang tải dữ liệu...</span>
                    </div>
                </td>
            </tr>
        `);

        $.ajax({
            type: "POST",
            url: endPoint,
            contentType: 'application/json',
            data: JSON.stringify({
                domain: domain_name,
                id: id
            }),
            success: function (response) {
                const data = response.data[0];
                const query = data.query ? data.query : "...";
                const referer = data.referer ? data.referer : "...";
                const html = `
                    <div class="detail_logs_wrapper">
                        <div class="detail_logs_content row">
                            <div class="col-lg-6 detail_logs_data">
                                <strong>URL:</strong>
                                <span>${data.url}</span>
                            </div>
                            <div class="col-lg-6 detail_logs_data">
                                <strong>Query:</strong> 
                                <span>${query}</span>
                            </div>
                            <div class="col-lg-6 detail_logs_data">
                                <strong>Referer:</strong>
                                <span>${referer}</span>
                            </div>
                            <div class="col-lg-6 detail_logs_data">
                                <strong>User-Agent:</strong>
                                <span>${data.user_agent}</span>
                            </div>
                            <div class="col-lg-3 detail_logs_data">
                                <strong>Content-Type:</strong>
                                <span>${data.content_type}</span>
                            </div>
                            <div class="col-lg-3 detail_logs_data">
                                <strong>Device:</strong>
                                <span title="device-os-cpu">${data.device}, ${data.os}, ${data.cpu_cores}</span>
                            </div>
                            <div class="col-lg-3 detail_logs_data">
                                <strong>Interface:</strong>
                                <span title="browser-resolution-pixel-ratio">${data.browser}, ${data.resolution}, ${data.pixel_ratio}</span>
                            </div>
                            <div class="col-lg-3 detail_logs_data">
                                <strong>Location:</strong>
                                <span title="country-city-region-long-lat">${data.country}, ${data.city}, ${data.region}, ${data.longtitude}, ${data.latitude}</span>
                            </div>
                        </div>
                    </div>
                `;

                currentRow.next(".detail-row").find("td").html(html);
            },
            error: function (xhr) {
                status_code = xhr.code;
                if(xhr.status === 401){
                    $message = "Bạn không có quyền truy cập dữ liệu này!";
                }else if(xhr.status === 400){
                    $message = "Không tìm thấy tên miền hoặc ID log, vui lòng kiểm tra lại!";
                }
                else if(xhr.status === 500){
                    $message = "Lỗi máy chủ, vui lòng thử lại sau!";
                }
                currentRow.next(".detail-row").find("td").html(`<span class="text-danger">${message}</span>`);
            }
        });
    }

    function handleAccordion() {
    const maxHeight = $(".online_data_content").height();
    let traffic_values = $(".traffic_value");
        if (traffic_values.length === 0) {
            return;
        } else {
            let accordion    = $(".online_data.row");
            let extend       = $(".extend_btn");
            extend.removeClass("hide");
                extend.off("click").on("click", function () {
                    if (!accordion.hasClass('expanded')) {
                        accordion.addClass('expanded');
                        $(this).html('&#x25B2;');
                    } else {
                        accordion.removeClass('expanded');
                        $(this).html('&#x25BC;');
                    }
                });
        }
    }

    function checkEmptyData(data){
        const allEmpty = data.every(([key, value]) => Array.isArray(value) && value.length === 0);
        return allEmpty;
    }

    function renderVisitorTableRows(data, page, perPage) {
    const start = (page - 1) * perPage;
    const end = start + perPage;
    const rows = data.slice(start, end);

    const tbody = document.getElementById("visitor_tbody");
    tbody.innerHTML = '';

    rows.forEach(entry => {
        const path = new URL(entry.url).pathname;
        const date = entry.created_at;
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td class="visitor_cell col-lg-3">
                <button class="extend_log_detail" log-id="${entry.id}">&#9654;</button>
                <strong>${date}</strong>
            </td>
            <td class="visitor_cell col-lg-3 ip_address">
                <a href="https://ipinfo.io/${entry.client_ip}" target="_blank">
                    <span title="${entry.client_ip}">${entry.client_ip}</span>
                </a>
                <button data-ip="${entry.client_ip}" class="copy-ip-btn">
                    <svg width="12px" height="12px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M6 11C6 8.17157 6 6.75736 6.87868 5.87868C7.75736 5 9.17157 5 12 5H15C17.8284 5 19.2426 5 20.1213 5.87868C21 6.75736 21 8.17157 21 11V16C21 18.8284 21 20.2426 20.1213 21.1213C19.2426 22 17.8284 22 15 22H12C9.17157 22 7.75736 22 6.87868 21.1213C6 20.2426 6 18.8284 6 16V11Z" stroke="#7d7d7d" stroke-width="1.5"></path> <path d="M6 19C4.34315 19 3 17.6569 3 16V10C3 6.22876 3 4.34315 4.17157 3.17157C5.34315 2 7.22876 2 11 2H15C16.6569 2 18 3.34315 18 5" stroke="#7d7d7d" stroke-width="1.5"></path> </g></svg>
                </button
            </td>
            <td class="visitor_cell col-lg-3">${entry.bot_name}</td>
            <td class="visitor_cell col-lg-3" title="${path}">${path}</td>
        `;
        tbody.appendChild(tr);
    });
     document.getElementById("itemRange").innerText =
        `${start + 1} - ${Math.min(end, data.length)} of ${data.length} items`;

    document.getElementById("currentPage").innerText = page;
    document.getElementById("totalPages").innerText = Math.ceil(data.length / perPage);
    }

    function setupPagination(data, currentPage, itemsPerPage) {
        const totalPages = Math.ceil(data.length / itemsPerPage);

        function renderPage(page) {
            currentPage = page;
            renderVisitorTableRows(data, currentPage, itemsPerPage);
            updatePaginationButtons(currentPage, totalPages);
        }

        $('#firstPage').off().on('click', () => renderPage(1));
        $('#prevPage').off().on('click', () => {
            if (currentPage > 1) renderPage(currentPage - 1);
        });
        $('#nextPage').off().on('click', () => {
            if (currentPage < totalPages) renderPage(currentPage + 1);
        });
        $('#lastPage').off().on('click', () => renderPage(totalPages));

        $('#itemsPerPage').off().on('change', function () {
            if(this.value == "max"){
                itemsPerPage = parseInt(data.length);
            }else{
                itemsPerPage = parseInt(this.value);
            }
            currentPage = 1;
            setupPagination(data, currentPage, itemsPerPage); // re-init pagination
            toggleTableScroll(itemsPerPage);
        });

        // Lần đầu khởi tạo
        renderPage(currentPage);
    }

    function updatePaginationButtons(currentPage, totalPages) {
        $('#firstPage').prop('disabled', currentPage <= 1);
        $('#prevPage').prop('disabled', currentPage <= 1);
        $('#nextPage').prop('disabled', currentPage >= totalPages);
        $('#lastPage').prop('disabled', currentPage >= totalPages);
    }

    function toggleTableScroll(perPage) {
    const wrapper = document.querySelector('.total_entrance_detail');
    if (perPage > 10) {
        wrapper.classList.add('scrolling');
        wrapper.style.maxHeight = "400px";
        wrapper.style.overflowY = "scroll";
        wrapper.style.overflowX = "hidden";
    } else {
        wrapper.classList.remove('scrolling');
        wrapper.style.maxHeight = "unset";
        wrapper.style.overflowY = "unset";
        wrapper.style.overflowX = "unset";
    }
    }
});
$(document).on('click', '.copy-ip-btn', function () {
    const ip = $(this).data('ip');
    navigator.clipboard.writeText(ip).then(() => {
        $(this).text('✔');
        setTimeout(() => $(this).html(`
                <svg width="12px" height="12px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M6 11C6 8.17157 6 6.75736 6.87868 5.87868C7.75736 5 9.17157 5 12 5H15C17.8284 5 19.2426 5 20.1213 5.87868C21 6.75736 21 8.17157 21 11V16C21 18.8284 21 20.2426 20.1213 21.1213C19.2426 22 17.8284 22 15 22H12C9.17157 22 7.75736 22 6.87868 21.1213C6 20.2426 6 18.8284 6 16V11Z" stroke="#7d7d7d" stroke-width="1.5"></path> <path d="M6 19C4.34315 19 3 17.6569 3 16V10C3 6.22876 3 4.34315 4.17157 3.17157C5.34315 2 7.22876 2 11 2H15C16.6569 2 18 3.34315 18 5" stroke="#7d7d7d" stroke-width="1.5"></path> </g></svg>
            `), 1500);
    }).catch(() => {
        alert("Không thể sao chép IP.");
    });
});