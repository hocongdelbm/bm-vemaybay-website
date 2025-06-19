$(document).ready(function () {
    getTraffic();
    getLogs();
    handleAccordion();
    $(".search_button.btn.btn-primary").on("click", function(){
        $(".online_data.row").empty();
        getTraffic();
        getLogs();
    });
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
                    let groupHtml = `<div class="online_data_content mb-4 col-lg-4">`;
                    let formatted_key = key.replace("top_", "");
                    formatted_key = formatted_key.charAt(0).toUpperCase() + formatted_key.slice(1);;
                    $translate_key = {
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
                                <div class="d-flex">
                                    <div class="mx-1">${item.value}</div>
                                    <div class="progress px-0" style="width: 150px">
                                        <div class="progress-bar bg-info" role="progressbar" style="width:${percent}%;">
                                        </div>
                                    </div>
                                </div>
                                <label class="" for="progress">${item.label}</label>
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
                 $("#visitor_logs").append(`
                    <div class="online_data_content_loading d-flex flex-column justify-content-center align-items-center">
			            <div class="spinner online_data_waiting"></div>
			            <span class="online_data_waiting_text">Đang tải dữ liệu...</span>
		            </div>
                `);
            },
            success: function(response) {
                $("#visitor_logs").empty();
                $(".online_data_content_loading").hide();
                const data = response.data;
                renderVisitorTableRows(data, 1, 10);
                renderPagination(data, 10);
            },
            error: function(err) {
                console.error("Lỗi khi lấy dữ liệu:", err);
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
            let extend       = $(".btn.btn-primary.extend_btn");
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
        const host = new URL(entry.url).host;
        const path = new URL(entry.url).pathname;

        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${entry.created_at}</td>
            <td>${entry.client_ip}</td>
            <td>${host}</td>
            <td>${path}</td>
        `;
        tbody.appendChild(tr);
    });
    }

    function renderPagination(data, perPage) {
        const pageCount = Math.ceil(data.length / perPage);
        const pagination = document.getElementById("pagination");
        pagination.innerHTML = '';

        for (let i = 1; i <= pageCount; i++) {
            const btn = document.createElement("button");
            btn.innerText = i;
            btn.addEventListener("click", () => {
                document.querySelectorAll("#pagination button").forEach(b => b.classList.remove("active"));
                btn.classList.add("active");
                renderVisitorTableRows(data, i, perPage);
            });
            if (i === 1) btn.classList.add("active");
            pagination.appendChild(btn);
        }
    }
});