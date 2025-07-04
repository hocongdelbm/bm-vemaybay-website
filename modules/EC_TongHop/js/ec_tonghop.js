$(document).ready(function () {
    let currentFilters = {};
    const keyMap = {
            "ips"     : "client_ip",
            "urls"    : "url",
            "channels": "referer",
            "bots"    : "bot_name",
            "devices" : "device",
            "os"      : "os",
            "countries": "country",
            "browsers": "browser"
    };

    init();
    function init() {
        const data = getSearchData();
        bindEvents();
        handleQuickRange();
        fetchTraffic(data.domain, data.option);
        fetchLogs(data.domain, data.option);
        fetchTrafficInsights(data.domain, data.option);
        handleAccordion();
    }

    function getSearchData(){
        let domain = $("select[name='url_selected'] option:selected").text() || "timchuyenbay.vn";
        let fromDate = $("#from_date").val();
        let toDate = $("#to_date").val();
        let quickRange = $("select[name='time_selected']").val();
        let option = {"from_date": fromDate, "to_date": toDate, "flag": quickRange};
        return {domain, option};
    }

    function bindEvents() {
        $(document).on("click", ".filter-remove", function(){
            const reverseKeyMap = Object.fromEntries(Object.entries(keyMap).map(([k, v]) => [v, k]));
            const originalKey = $(this).data("key");
            const filterValue = $(this).data("filter");
            const convertedKey = keyMap[originalKey];
            const data = getSearchData();
            const domain = data.domain;

            let fullValue = filterValue;
            if (convertedKey === "url" || convertedKey === "referer") {
                fullValue = `https://${domain}${filterValue}`;
            }

            if (currentFilters[convertedKey] === fullValue) {
                delete currentFilters[convertedKey];
            }

            const option = data.option;
            option.filter = currentFilters;

            handleQuickRange();
            fetchTraffic(domain, option);
            fetchLogs(domain, option);
            fetchTrafficInsights(domain, option);
            renderFilterTag(currentFilters);
            updateFilterBTN(currentFilters, domain);
        });
        $(document).on("click", ".close_tag", function(){
            const key = $(this).data("key");
            delete currentFilters[key];
            const data = getSearchData();
            let domain = data.domain;
            let option = data.option;
            if(Object.entries(currentFilters).length !==0){
                option.filter = currentFilters;
            }
            handleQuickRange();
            fetchTraffic(domain, option);
            fetchLogs(domain, option);
            fetchTrafficInsights(domain, option);
            renderFilterTag(currentFilters);
        });
        $(document).on("click", ".filter-btn", function(){
            const filter_key  = $(this).data("key");
            let filter_data = $(this).data("filter");
            const data = getSearchData();
            let domain = data.domain;
            let option = data.option;
            if(filter_key == "urls" || filter_key == "channels"){
                filter_data = `https://${domain}${filter_data}`;   
            }
            const filter_array = { [filter_key] : filter_data};
            option["filter"] = filter_array;
            option.filter = convertFilterKey(option.filter);
            for (const key in option.filter) {
                currentFilters[key] = option.filter[key];
            }
            option.filter = currentFilters;
            handleQuickRange();
            fetchTraffic(domain, option);
            fetchLogs(domain, option);
            fetchTrafficInsights(domain, option);
            renderFilterTag(currentFilters);
            const $container = $(this).closest(".filter-btns");
            $container.find(".filter-btn").addClass("inactive");
            $container.find(".filter-remove").removeClass("inactive");
        });
        $(".search_button.btn.btn-primary").on("click", () => {
            $(".online_data.row").empty();
            handleQuickRange();
            const data = getSearchData();
            data.option.filter = currentFilters;
            fetchTraffic(data.domain, data.option);
            fetchLogs(data.domain, data.option);
            renderFilterTag(currentFilters);
            fetchTrafficInsights(data.domain, data.option);
        });

        $(".reset_button.btn.btn-warning").on("click", () => {
            $("select[name='url_selected'], select[name='time_selected']").val("");
        });

        $(document).on("click", ".extend_log_detail", function () {
            $(this).toggleClass("rotated");
            fetchLogDetails($(this).attr("log-id"), this);
        });

        $(document).on('click', '.copy-ip-btn', function () {
            const ip = $(this).data('ip');
            navigator.clipboard.writeText(ip).then(() => {
                $(this).text('✔');
                setTimeout(() => $(this).html(getCopyIcon()), 1500);
            }).catch(() => alert("Không thể sao chép IP."));
        });
    }

     function updateFilterBTN(filters, domain) {
        const reverseKeyMap = Object.fromEntries(Object.entries(keyMap).map(([k, v]) => [v, k]));
        $(".filter-btn").removeClass("inactive").show();
        $(".filter-remove").addClass("inactive").hide();

        for (const [convertedKey, value] of Object.entries(filters)) {
            const originalKey = reverseKeyMap[convertedKey];
            let selectorValue = value;

            if (convertedKey === "url" || convertedKey === "referer") {
                const domainPrefix = `https://${domain}`;
                if (selectorValue.startsWith(domainPrefix)) {
                    selectorValue = selectorValue.replace(domainPrefix, "");
                }
            }

            $(`[data-key="${originalKey}"][data-filter="${selectorValue}"]`).each(function () {
                const $btn = $(this);
                if ($btn.hasClass("filter-remove")) {
                    $btn.removeClass("inactive").show();
                } else if ($btn.hasClass("filter-btn")) {
                    $btn.addClass("inactive").hide();
                }
            });
        }
    }

    function handleQuickRange() {
        const quickRange = $("select[name='time_selected']").val();
        if (quickRange) {
            const range = getQuickTimeRange(quickRange);
            $("#from_date").val(range.from);
            $("#to_date").val(range.to);
        }
    }

    function convertFilterKey(filter){
        const converted = {};
        for (const key in filter) {
            const converted_key = keyMap[key] || key;
            converted[converted_key] = filter[key];
        }
        return converted;
    }

    function renderFilterTag(filter){
        const filter_zone = $(".traffic.filter_tag");
        filter_zone.empty().append(`<h5>Filter tags:</h5><div class="filter_tag_wrapper d-flex"></div>`);
        const wrapper = filter_zone.find(".filter_tag_wrapper");
        if(Object.entries(filter).length === 0){
            filter_zone.empty();
            return;
        }
        Object.entries(filter).forEach(([key, value]) => {
             const tagHTML = `
            <div class="filter_tag_content d-flex">
                <div class="tag_wrapper d-flex">
                    <span class="tag_content" data-tag="${key}">${key}: ${value}</span>
                    <div class="close_tag d-flex" data-key="${key}">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16 16L12 12M12 12L8 8M12 12L16 8M12 12L8 16" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </div>
                </div>
            </div>`;
            wrapper.append(tagHTML);
        });
    }

    function getQuickTimeRange(value) {
        const now = new Date();
        const pad = num => String(num).padStart(2, "0");
        const format = (date, endOfDay = false) =>
            `${pad(date.getDate())}-${pad(date.getMonth() + 1)}-${date.getFullYear()} ${endOfDay ? "23:59:59" : "00:00:00"}`;
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
            case "this_week":
                const cw = new Date(now);
                const day = cw.getDay() || 7;
                cw.setDate(cw.getDate() - day + 1);
                const cwEnd = new Date(cw);
                cwEnd.setDate(cw.getDate() + 6);
                result.from = format(cw);
                result.to = format(cwEnd, true);
                break;
            case "last_week":
                const pw = new Date(now);
                const currentDay = pw.getDay() || 7;
                pw.setDate(pw.getDate() - currentDay - 6);
                const pwEnd = new Date(pw);
                pwEnd.setDate(pw.getDate() + 6);
                result.from = format(pw);
                result.to = format(pwEnd, true);
                break;
            case "this_month":
                const cmStart = new Date(now.getFullYear(), now.getMonth(), 1);
                const cmEnd = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                result.from = format(cmStart);
                result.to = format(cmEnd, true);
                break;
            case "last_month":
                const pmStart = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                const pmEnd = new Date(now.getFullYear(), now.getMonth(), 0);
                result.from = format(pmStart);
                result.to = format(pmEnd, true);
                break;
        }
        return result;
    }

    function fetchTraffic(domain, option) {
        const endPoint = "index.php?entryPoint=entryPointSummarySite";

        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        $(".online_data").html(renderLoading());
        $(".btn.btn-primary.extend_btn").addClass("hide");

        $.ajax({
            type: "POST",
            url: endPoint,
            contentType: 'application/json',
            data: JSON.stringify({domain: domain, options: option, action: "get_traffic"}),
            success: function (response) {
                const data = Object.entries(response.data);
                if (isAllGroupsEmpty(data)) {
                    renderEmptyData(".online_data", "Không có dữ liệu, vui lòng chọn ngày hoặc tên miền khác", domain);
                    return;
                }
                renderTrafficGroups(".online_data", domain, data);
                handleAccordion();
                updateFilterBTN(currentFilters, domain);
            },
            error: function (xhr) {
                let message = "Không truy xuất được dữ liệu, vui lòng thử lại sau";
                if (xhr.status === 401) message = "Bạn không có quyền truy cập dữ liệu này";
                else if (xhr.status === 400) message = "Thời gian hoặc tên miền không hợp lệ, vui lòng kiểm tra lại";
                else if (xhr.status === 500) message = "Lỗi máy chủ, vui lòng thử lại sau";
                renderEmptyData(".online_data", message, domain);
            }
        });
    }
    function fetchTrafficInsights(domain, option){
        const endPoint = "index.php?entryPoint=entryPointSummarySite";
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        $(".total_traffic_value").html(renderLoading());
        $(".unique_ip_value").html(renderLoading());
        $(".row.chart-row").html(renderLoading());
        $(".btn.btn-primary.extend_btn").addClass("hide");
        $.ajax({
            type: "POST",
            url: endPoint,
            contentType: 'application/json',
            data: JSON.stringify({domain: domain, options: option, action: "get_traffic_insights"}),
            success: function(response){
                console.log(response);
                const selector = $(".box-section.insight_traffic");
                renderTrafficInsights(selector, domain, response.data);
            },
            error: function(xhr){
                console.log(xhr);
            }
        });
    }
    function renderTrafficInsights(selector, domainName, data) {
        const container = $(selector);
        container.empty();

        container.append(` 
                    <div class="online_data_title col-lg-12 d-flex flex-column align-items-center pb-3">
                        <h3 style="margin-bottom:unset">Chỉ số traffic của ${domainName}</h3>
                    </div>
                `);
        container.append(`
            <div class="insight-numbers mb-4 row">
                <div class="col-lg-4 mb-4 text-center">
                    <div class="total_traffic d-flex justify-content-center">
                        <h6 class="total_traffic_title">Tổng traffic:</h6>
                        <span class="total_traffic_value">${data.total_traffic}</span>
                    </div>
                </div>
                <div class="col-lg-4 mb-4 text-center">
                    <div class="total_traffic d-flex justify-content-center">
                        <h6 class="total_traffic_title">Tổng bot:</h6>
                        <span class="total_traffic_value">${data.total_bot}</span>
                    </div>
                </div>
                <div class="col-lg-4 mb-4 text-center">
                    <div class="unique_ip d-flex justify-content-center">
                        <h6 class="unique_ip_title">IP duy nhất:</h6>
                        <span class="unique_ip_value">${data.unique_ips}</span>
                    </div>
                </div>
            </div>
            <hr style="border: none; border-top: 2px dashed #ccc; margin: 20px 0;">
        `);

        container.append(`
            <div class="row chart-row mb-4">
                <div class="col-lg-5 mb-4">
                    <h6 class="text-center mb-3" title="Lượng truy cập người dùng thực(không bot)">Tổng truy cập người dùng</h6>
                    <canvas id="totalChart"></canvas>
                </div>
                <div class="col-lg-5 mb-4">
                    <h6 class="text-center mb-3">Truy cập của các trang chính</h6>
                    <canvas id="totalChartDemo"></canvas>
                </div>
            </div>
            <div class="row chart-row mb-4">
                <div class="col-lg-12 mb-4">
                    <h6 class="text-center mb-3">Phân loại truy cập</h6>
                    <canvas id="barChart"></canvas>
                </div>
            </div>
        `);

        const colorSet = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#00C49F', '#FF6666'];

        function renderPie(canvasId, items) {
            const total = items.reduce((sum, i) => sum + i.value, 0);
            const canvas = document.getElementById(canvasId);
            const ctx = canvas.getContext('2d');
            const labelMap = {
                '/chon-hanh-trinh': 'Hành trình',
                '/tim-chuyen-bay': 'Hành trình',
                '/thong-tin-hanh-khach': 'Hành khách',
                '/thong-tin-thanh-toan': 'Thanh toán',
                '/hoan-tat-don-hang': 'Hoàn tất'
            }
            items.forEach(item => {
                if(labelMap[item.label]){
                    item.label = labelMap[item.label];
                }
            });
            if (total === 0) {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.font = '16px Arial';
                ctx.fillStyle = '#666';
                ctx.textAlign = 'center';
                ctx.fillText('Không có dữ liệu', canvas.width / 2, canvas.height / 2);
                return;
            }

            const labels = items.map(i => i.label);
            const values = items.map(i => i.value);
            const backgroundColor = labels.map((_, idx) => colorSet[idx % colorSet.length]);

            new Chart(canvas, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: backgroundColor,
                        pieThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: ctx => `${ctx.label}: ${ctx.parsed}`
                            }
                        }
                    }
                }
            });
        }

        renderPie('totalChart', data.source || []);
        renderPie('totalChartDemo', data.main_page || []);
        const search_data = getSearchData();
        console.log(search_data.option)
        const labels = (data.user_type || []).map(item => item.label);
        const byUserData = (data.user_type || []).map(item => item.by_user);
        const byBotData = (data.user_type || []).map(item => item.by_bot);

        new Chart(document.getElementById('barChart'), {
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Người dùng',
                        data: byUserData,
                        backgroundColor: '#36A2EB',
                        borderColor: '#007BFF',
                        type: 'bar',
                        order: 1,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Người dùng',
                        data: byUserData,
                        type: 'line',
                        borderColor: '#007BFF',
                        backgroundColor: 'transparent',
                        fill: true,
                        pointRadius: 3,
                        borderWidth: 2,
                        tension: 0.3,
                        order: 0,
                        yAxisID: 'y',
                        datalabels: { display: true },
                        hoverRadius: 4,
                        segment: { borderDash: [5, 5] },
                        hidden: false
                    },
                    {
                        label: 'Bot',
                        data: byBotData,
                        backgroundColor: '#FF6384',
                        borderColor: '#FF6384',
                        type: 'bar',
                        order: 1,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Bot',
                        data: byBotData,
                        type: 'line',
                        borderColor: '#FF6384',
                        backgroundColor: 'transparent',
                        fill: true,
                        pointRadius: 3,
                        borderWidth: 2,
                        tension: 0.3,
                        order: 0,
                        yAxisID: 'y',
                        datalabels: { display: true },
                        hoverRadius: 4,
                        segment: { borderDash: [5, 5] },
                        hidden: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Thời gian'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Lượt truy cập'
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            filter: function(item, chart) {
                                return item.datasetIndex === 0 || item.datasetIndex === 2;
                            }
                        },
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y}`
                        }
                    }
                }
            }
        });
    }



    function renderTrafficGroups(selector, domainName, data) {
        const $container = $(selector);
        $container.empty().append(`
            <div class="online_data_title col-lg-12 d-flex flex-column align-items-center pb-3">
                <h3 style="margin-bottom:unset">Traffic của ${domainName}</h3>
            </div>
        `);

        const desiredOrder = ['IPs', 'Urls', 'Referers', 'Bots'];
        const translateMap = {
            "Channels": "Referers",
            "Devices": "Thiết bị",
            "Browsers": "Trình duyệt",
            "Countries": "Quốc gia",
            "Ips": "IPs"
        };

        const normalizeKey = (key) => {
            let formatted = key.replace("top_", "");
            formatted = formatted.charAt(0).toUpperCase() + formatted.slice(1);
            return translateMap[formatted] || formatted;
        };

        data.sort((a, b) => {
            const indexA = desiredOrder.indexOf(normalizeKey(a[0]));
            const indexB = desiredOrder.indexOf(normalizeKey(b[0]));
            return (indexA === -1 ? 999 : indexA) - (indexB === -1 ? 999 : indexB);
        });

        const fragment = document.createDocumentFragment();
        data.forEach(([key, values]) => {
            if (!Array.isArray(values) || values.length === 0) return null;
            fragment.appendChild(renderTrafficGroup(key, values, normalizeKey));
        });

        $container.append(fragment);
    }

    function renderTrafficGroup(key, values, normalizeKey) {
        const formattedKey = normalizeKey(key);
        const groupDiv = document.createElement("div");
        groupDiv.className = "online_data_content mb-4 col-lg-3";
        groupDiv.innerHTML = `<h6 class="attribute_title">${formattedKey}</h6>`;
        const data_key = key.replace("top_","").toLowerCase();
        const total = values.reduce((sum, item) => sum + item.value, 0);
        values.forEach(item => {
            const percent = total ? ((item.value / total) * 100).toFixed(1) : 0;

            const progressHTML = `
                <div class="progress_wrapper d-flex align-items-center">
                    <div class="mx-1">${item.value}</div>
                    <div class="progress px-0" style="width: 150px">
                        <div class="progress-bar" role="progressbar" style="width:${percent}%;"></div>
                    </div>
                </div>`;

            const labelHTML = (formattedKey === "IPs")
                ? `<label title="${item.label}" for="progress">
                        <div class="visitor_cell col-lg-3 ip_address d-flex">
                            <a href="https://ipinfo.io/${item.label}" target="_blank">
                                <span title="${item.label}">${item.label}</span>
                            </a>
                            <button data-ip="${item.label}" class="copy-ip-btn">${getCopyIcon()}</button>
                        </div>
                </label>`
                : `<label title="${item.label}" for="progress">${item.label}</label>`;

            const filterBtns = `
                <div class="filter-btns">
                    <button class="btn btn-sm btn-primary filter-btn" data-filter="${item.label}" data-key="${data_key}">Filter</button>
                    <button class="btn btn-sm btn-primary filter-remove inactive" data-filter="${item.label}" data-key="${data_key}">Remove</button>
                </div>`;
                // use later
                // <button class="btn btn-sm btn-outline-secondary exclude-btn" data-exclude="${item.label}" data-key="${formattedKey}">Exclude</button>
            groupDiv.innerHTML += `
                <div class="traffic_value ${formattedKey} d-flex flex-row-reverse mb-2 traffic-item" style="justify-content:space-between; position:relative;">
                    ${progressHTML}
                    ${labelHTML}
                    ${filterBtns}
                </div>`;
        });

        return groupDiv;
    }

    function handleAccordion() {
        const accordion = $(".online_data.row");
        const extendBtn = $(".extend_btn");
        // const contentHeight = $(".online_data_content").height() || 365;

        if ($(".traffic_value").length === 0) return;

        extendBtn.removeClass("hide");
        accordion.css({
            "transition": "max-height 0.5s cubic-bezier(0.4,0,0.2,1)",
            // "max-height": (contentHeight + 100) + "px",
            "overflow-y": "hidden"
        });

        extendBtn.off("click").on("click", function () {
            if (!accordion.hasClass('expanded')) {
                accordion.addClass('expanded').css({
                    "max-height": "2000px",
                    "overflow-y": "auto"
                });
                $(this).html('&#x25B2;');
            } else {
                accordion.removeClass('expanded').css({
                    "max-height": "350px",
                    "overflow-y": "hidden"
                });
                $(this).html('&#x25BC;');
            }
        });
    }

    function fetchLogs(domain, option) {
        const endPoint = "index.php?entryPoint=entryPointSummarySite";
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        $("#visitor_tbody").html(renderLoading());

        $.ajax({
            type: "POST",
            url: endPoint,
            contentType: 'application/json',
            data: JSON.stringify({domain: domain, options: option, action: "get_logs"}),
            success: function (response) {
                const data = response.data;
                $("#visitor_tbody").empty();
                $(".access_log.title").text(`Truy cập gần nhất của ${domain}`);
                if(Array.isArray(data)&&data.length === 0){
                    $("#visitor_tbody").html(`<td class="text-center"><span class="my-5">Không có dữ liệu, vui lòng chọn ngày hoặc tên miền khác</span></td>`);
                    setupPagination(data, 1, 0);
                    return;
                }else{
                    renderVisitorTableRows(data, 1, 25);
                    setupPagination(data, 1, 25);
                    return
                }
            },
            error: function (err) {
                // $("#visitor_tbody").append(`<div><h5>Lỗi khi tải dữ liệu, vui lòng thử lại sau!</h5></div>`);
            }
        });
    }

    function fetchLogDetails(id, btn) {
        const currentRow = $(btn).closest("tr");
        const detailRow = currentRow.next(".detail-row");
        if (detailRow.length > 0) {
            detailRow.toggle();
            return;
        }
        const endPoint = "index.php?entryPoint=entryPointSummarySite";
        let domainName = $("select[name='url_selected'] option:selected").text() || "timchuyenbay.com";
        currentRow.after(`
            <tr class="detail-row">
                <td colspan="4">${renderLoading()}</td>
            </tr>
        `);
        $.ajax({
            type: "POST",
            url: endPoint,
            contentType: 'application/json',
            data: JSON.stringify({ domain: domainName, id: id, action: "get_log_detail"}),
            success: function (response) {
                const data = response.data[0];
                currentRow.next(".detail-row").find("td").html(renderLogDetail(data));
            },
            error: function (xhr) {
                let message = "Không truy xuất được chi tiết log.";
                if (xhr.status === 401) message = "Bạn không có quyền truy cập dữ liệu này!";
                else if (xhr.status === 400) message = "Không tìm thấy tên miền hoặc ID log, vui lòng kiểm tra lại!";
                else if (xhr.status === 500) message = "Lỗi máy chủ, vui lòng thử lại sau!";
                currentRow.next(".detail-row").find("td").html(`<span class="text-danger">${message}</span>`);
            }
        });
    }

    function renderLogDetail(data) {
        const query = data.query || "...";
        const referer = data.referer || "...";
        return `
            <div class="detail_logs_wrapper">
                <div class="detail_logs_content row">
                    <div class="col-lg-6 detail_logs_data"><strong>URL:</strong> <span>${data.url}</span></div>
                    <div class="col-lg-6 detail_logs_data"><strong>Query:</strong> <span>${query}</span></div>
                    <div class="col-lg-6 detail_logs_data"><strong>Referer:</strong> <span>${referer}</span></div>
                    <div class="col-lg-6 detail_logs_data"><strong>User-Agent:</strong> <span>${data.user_agent}</span></div>
                    <div class="col-lg-3 detail_logs_data"><strong>Content-Type:</strong> <span>${data.content_type}</span></div>
                    <div class="col-lg-3 detail_logs_data"><strong>Device:</strong> <span title="device-os-cpu">${data.device}, ${data.os}, ${data.cpu_cores}</span></div>
                    <div class="col-lg-3 detail_logs_data"><strong>Interface:</strong> <span title="browser-resolution-pixel-ratio">${data.browser}, ${data.resolution}, ${data.pixel_ratio}</span></div>
                    <div class="col-lg-3 detail_logs_data"><strong>Location:</strong> <span title="country-city-region-long-lat">${data.country}, ${data.city}, ${data.region}, ${data.longtitude}, ${data.latitude}</span></div>
                </div>
            </div>
        `;
    }

    function renderLoading() {
        return `
            <div class="online_data_content_loading d-flex flex-column justify-content-center align-items-center">
                <div class="spinner online_data_waiting"></div>
                <span class="online_data_waiting_text">Đang tải dữ liệu...</span>
            </div>
        `;
    }

    function renderEmptyData(selector, message, domainName) {
        $(selector).empty().append(`
            <div class="online_data_content d-flex flex-column justify-content-center align-items-center">
                <h3 style="margin-bottom:unset">Traffic của ${domainName}</h3>
                <span class="online_data_failed_text my-5">${message}</span>
            </div>
        `);
    }

    function isAllGroupsEmpty(data) {
        return data.every(([key, value]) => Array.isArray(value) && value.length === 0);
    }

    function renderVisitorTableRows(data, page, perPage) {
        const start = (page - 1) * perPage;
        const end = start + perPage;
        const rows = data.slice(start, end);
        const tbody = document.getElementById("visitor_tbody");
        tbody.innerHTML = '';

        rows.forEach(entry => {
            const path = new URL(entry.url).pathname;
            const date = formatDateTime(entry.created_at);
            const tr = document.createElement("tr");
            let bot_name = entry.bot_name;
            let display_name = bot_name;
            if(bot_name == "Unknown"){
                display_name = "";
            }
            tr.innerHTML = `
                <td class="visitor_cell col-lg-3">
                    <button class="extend_log_detail" log-id="${entry.id}">&#9654;</button>
                    <strong>${date}</strong>
                </td>
                <td class="visitor_cell col-lg-3 ip_address">
                    <a href="https://ipinfo.io/${entry.client_ip}" target="_blank">
                        <span title="${entry.client_ip}">${entry.client_ip}</span>
                    </a>
                    <button data-ip="${entry.client_ip}" class="copy-ip-btn">${getCopyIcon()}</button>
                </td>
                <td class="visitor_cell col-lg-3">${display_name}</td>
                <td class="visitor_cell col-lg-3" title="${path}"><span class="visitor_path">${path}<span></td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById("itemRange").innerText = `${start + 1} - ${Math.min(end, data.length)} of ${data.length} items`;
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
        $('#prevPage').off().on('click', () => { if (currentPage > 1) renderPage(currentPage - 1); });
        $('#nextPage').off().on('click', () => { if (currentPage < totalPages) renderPage(currentPage + 1); });
        $('#lastPage').off().on('click', () => renderPage(totalPages));
        $('#itemsPerPage').off().on('change', function () {
            itemsPerPage = this.value === "max" ? data.length : parseInt(this.value);
            currentPage = 1;
            setupPagination(data, currentPage, itemsPerPage);
            toggleTableScroll(itemsPerPage);
        });

        renderPage(currentPage);
    }

    function updatePaginationButtons(currentPage, totalPages) {
        $('#firstPage, #prevPage').prop('disabled', currentPage <= 1);
        $('#nextPage, #lastPage').prop('disabled', currentPage >= totalPages);
    }

    function toggleTableScroll(perPage) {
        const wrapper = document.querySelector('.total_entrance_detail');
        if (perPage > 25) {
            wrapper.classList.add('scrolling');
            wrapper.style.maxHeight = "950px";
            wrapper.style.overflowY = "scroll";
            wrapper.style.overflowX = "hidden";
        } else {
            wrapper.classList.remove('scrolling');
            wrapper.style.maxHeight = "unset";
            wrapper.style.overflowY = "unset";
            wrapper.style.overflowX = "unset";
        }
    }

    function formatDateTime(dateString) {
        const date = new Date(dateString.replace(" ", "T"));
        const options = {
            weekday: 'long',
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
            timeZone: 'Asia/Ho_Chi_Minh'
        };
        return new Intl.DateTimeFormat('vi-VN', options).format(date);
    }

    function capitalizeFirstLetter(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    function getCopyIcon() {
        return `<svg width="12px" height="12px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M6 11C6 8.17157 6 6.75736 6.87868 5.87868C7.75736 5 9.17157 5 12 5H15C17.8284 5 19.2426 5 20.1213 5.87868C21 6.75736 21 8.17157 21 11V16C21 18.8284 21 20.2426 20.1213 21.1213C19.2426 22 17.8284 22 15 22H12C9.17157 22 7.75736 22 6.87868 21.1213C6 20.2426 6 18.8284 6 16V11Z" stroke="#7d7d7d" stroke-width="1.5"></path> <path d="M6 19C4.34315 19 3 17.6569 3 16V10C3 6.22876 3 4.34315 4.17157 3.17157C5.34315 2 7.22876 2 11 2H15C16.6569 2 18 3.34315 18 5" stroke="#7d7d7d" stroke-width="1.5"></path> </g></svg>`;
    }
});
