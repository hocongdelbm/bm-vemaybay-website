/*---------- SEARCH ----------*/
$(document).ready(function () {
    $(document).on('keypress', function (e) {
        if (e.which == 13) {
            $("#btn-search-pnr").trigger("click");
            e.preventDefault();
        }
    });

    $('#btn-search-pnr').click(function () {
        let pnr = $('input[name="pnr"]').val();
        let flag = $(this).attr('flag');
        let message = $(this).attr('message');

        $(this).removeAttr('message');

        if (flag === undefined || flag != 'refresh') {
            resetHTML();
            $('.wrap-info').hide();
            $('.container-waiting').show();
        }
        else {
            resetHTML(flag);
            $(this).removeAttr('flag');
        }

        $.ajax({
            type: "POST",
            url: "index.php?entryPoint=entryPointAPIVietjet",
            data: {
                action: "search",
                pnr: pnr
            },
            beforeSend: function () {
                $('.container-waiting').show();
            },
            success: function (res) {
                $('.container-waiting').hide();

                if (pnr.length != 6) {
                    showModalNotify('error', 'Mã PNR không đúng');
                    return false;
                }
                if (message !== undefined && message.length > 0) {
                    showModalNotify('success', message);
                }

                data = JSON.parse(res);
                if (data['error'] === true) {
                    showModalNotify('error', data['message']);
                    return false;
                }

                /************  SUCCESS  ************/
                resetInfoPNR();

                /*****  Thông tin chung  *****/
                var booking_info = data.booking_info;
                $("#pnr").html(booking_info.pnr);
                $("#reservation_key").html(booking_info.reservation_key);
                $("#reservation_key").attr("re_key", booking_info.reservation_key);

                $("#email_booking").html(booking_info.email);
                $("#number").html(booking_info.number);
                $("#charges").html(booking_info.charges_format);
                $("#payments").html(booking_info.payments_format);
                $("#refunds").html(booking_info.refunds_format);
                $("#charges").attr("data", booking_info.charges);
                $("#payments").attr("data", booking_info.payments);
                $("#refunds").attr("data", booking_info.refunds);
                
                $("#balance_agency").html(data.supplier.creditAvailable); // Thông tin agency
                $("#status_pnr").attr('value', booking_info.status_code);
                $("#supplier_id").val(data.supplier.id);
                $("#supplier_name").html(data.supplier.name);

                if (booking_info.status_code == 1 || booking_info.status == "Booked")
                    $("#status_pnr").html('<i style="color:#0d6efd">Đã giữ chỗ đến ' + booking_info.hold_time + '</i>');
                else if (booking_info.status_code == 2 || booking_info.status == "There was payment transaction") {
                    let icon = '<svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#28a745" stroke-width="1.5"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 1.25C6.06294 1.25 1.25 6.06294 1.25 12C1.25 17.9371 6.06294 22.75 12 22.75C17.9371 22.75 22.75 17.9371 22.75 12C22.75 6.06294 17.9371 1.25 12 1.25ZM7.53044 11.9697C7.23755 11.6768 6.76268 11.6768 6.46978 11.9697C6.17689 12.2626 6.17689 12.7374 6.46978 13.0303L9.46978 16.0303C9.76268 16.3232 10.2376 16.3232 10.5304 16.0303L17.5304 9.03033C17.8233 8.73744 17.8233 8.26256 17.5304 7.96967C17.2375 7.67678 16.7627 7.67678 16.4698 7.96967L10.0001 14.4393L7.53044 11.9697Z" fill="#28a745"></path></svg>';
                    $("#status_pnr").html(`<span style="margin-right:3px; color:#28a745">Đã thanh toán</span>${icon}`);
                }
                else if (booking_info.status_code == 0 || booking_info.status == "Canceled")
                    $("#status_pnr").html('<span style="color:red">Đã hủy</span>');
                else
                    $("#status_pnr").html(booking_info.status);


                /*****  Thông tin hành trình  *****/
                var journeys = data.journeys;
                let journeys_html = "";
                $.each(journeys, function (direction, journey) {
                    if (direction == 0) {
                        journeys_html += '<h4 class="subtitle my-3">Lượt đi <b style="margin-left:15px; font-style:italic; font-size:20px; color:red;">' + data.base_price.dep + '</b></h4>';
                        $.each(journey.segments, function (key, segment) {
                            journeys_html += html_segment(direction, segment, journey.key);
                        });
                    }
                    else if (direction == 1) {
                        journeys_html += '<h4 class="subtitle my-3">Lượt về <b style="margin-left:15px; font-style:italic; font-size:20px; color:red;">' + data.base_price.ret + '</b></h4>';
                        $.each(journey.segments, function (key, segment) {
                            journeys_html += html_segment(direction, segment, journey.key);
                        });
                    }
                });

                // Modal change journey
                // const currentDATE = new Date().toLocaleDateString('en-GB').split('/').reverse().join('-');
                // journeys_html += `<div class="modal fade" id="modal-change-journey" tabindex="-1" aria-labelledby="modal-change-journeyLabel" aria-hidden="true">
                //                     <div class="modal-dialog modal-dialog-centered">
                //                         <div class="modal-content">
                //                         <div class="modal-header">
                //                             <h1 class="modal-title fs-5" id="modal-change-journeyLabel">Tim chuyến bay</h1>
                //                             <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                //                                 <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                //                                     <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                //                                 </svg>
                //                             </button>
                //                         </div>
                //                         <div class="modal-body" id="modal-change__journey">
                //                             <form action="index.php" method="POST">
                //                                 <div class="frm-change__journey">
                //                                     <div class="row py-1 row-depart">
                //                                         <div class="col-4">
                //                                             <label for="change__journey--depart">Điểm khởi hành</label>
                //                                         </div>
                //                                         <div class="col-8">
                //                                             <input type="text" id="change__journey--depart" value="" class="box-input" placeholder="SGN" oninput="this.value = this.value.toUpperCase();" required/>
                //                                         </div>
                //                                     </div>
                //                                     <div class="row py-1 row-arrival">
                //                                         <div class="col-4">
                //                                             <label for="change__journey--arrival">Điểm đến</label>
                //                                         </div>
                //                                         <div class="col-8">
                //                                             <input type="text" id="change__journey--arrival" value="" class="box-input" placeholder="HAN" oninput="this.value = this.value.toUpperCase();" required/>
                //                                         </div>
                //                                     </div>
                //                                     <div class="row py-1 row-datedepart">
                //                                         <div class="col-4">
                //                                             <label for="change__journey--datedepart">Ngày đi</label>
                //                                         </div>
                //                                         <div class="col-8">
                //                                             <div class="dateTime d-flex gap-2 position-relative">
                //                                                 <input class="date_input box-input" type="text" maxlength="10" size="11" tabindex="103" title="" value="${currentDATE}" id="change__journey--datedepart" name="change__journey--datedepart" autocomplete="off">
                //                                                 <button class="icon_dateTime" type="button" id="from_date_trigger" onclick="return false;">
                //                                                 <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                //                                                     <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
                //                                                     <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
                //                                                     </svg>
                //                                                 </button>
                //                                                 <script type="text/javascript">
                //                                                         Calendar.setup ({
                //                                                             inputField : "change__journey--datedepart",
                //                                                             daFormat : "%d-%m-%Y",
                //                                                             button : "from_date_trigger",
                //                                                             singleClick : true,
                //                                                             dateStr : "",
                //                                                             step : 1
                //                                                         });
                //                                                 </script>
                //                                             </div>
                //                                         </div>
                //                                     </div>
                //                                 </div>
                //                             </form>
                //                             <div id="flightlist"></div>
                //                         </div>
                //                         <div class="modal-footer">
                //                             <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                //                             <button type="submit" class="btn btn-primary" name="search-journey__change" id="search-journey__change" direction="" journey_key="">Tìm chuyến bay</button>
                //                         </div>
                //                         </div>
                //                     </div>
                //                 </div>`;
                $("#journeys").html(journeys_html);


                /*****  Thông tin hành khách  *****/
                var passengers = data.passengers;
                let passengers_html = "";
                $.each(passengers, function (key, pass) {
                    passengers_html += `<tr key="${key}">
                                <td class="td_type">${pass.type}</td>
                                <td>${pass.gender}</td>
                                <td class="td_name">${pass.name}</td>
                                <td>${pass.birthdate}</td>
                                <td>${pass.email}</td>
                                <td>${pass.mobile}</td>
                                <td>${pass.button_add_luggage}</td>
                            </tr>`;
                });
                $("#tbody-passengers").html(passengers_html);


                /*****  Thông tin hành lý  *****/
                if (data.options !== undefined) {
                    let options = data.options;
                    let options_html = "";
                    $.each(options, function (key, option) {
                        let option_direction = "";
                        if (option.direction == "Lượt đi") option_direction = '<b style="color:#0d6efd">Lượt đi</b>';
                        else if (option.direction == "Lượt về") option_direction = '<b style="color:#dc3545">Lượt về</b>';

                        options_html += `<tr>
                                <td>${option_direction}</td>
                                <td>${option.passenger_type}</td>
                                <td>${option.passenger_gender}</td>
                                <td>${option.passenger_name}</td>
                                <td>${option.detail}</td>
                                <td>${new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(option.total)}</td>
                            </tr>`;
                    });
                    $("#tbody-options").html(options_html);
                }


                /*****  Thông tin thuế phí  *****/
                if (data.charges !== undefined) {
                    let charges_dep = data.charges.dep;
                    let charges_dep_html = "";
                    $.each(charges_dep, function (key_pass, objValue) {
                        let total_amount = 0;
                        let p = passengers[key_pass];
                        let p_info = p['name'] + " (" + p['type'] + ", " + p['gender'] + ") ";

                        charges_dep_html += `<h5 class="mt-2 text-primary">${p_info}</h5>
                                            <table class="table table-hover table-bordered mb-5"><thead>
                                                <tr>
                                                    <th>Loại phí</th>
                                                    <th>Mô tả</th>
                                                    <th>Tổng cộng (VAT)</th>
                                                </tr>
                                            </thead><tbody>`;

                        $.each(objValue, function (key, charge) {
                            total_amount += charge.total_amount;
                            charges_dep_html += `<tr>
                                    <td>${charge.code} - ${charge.code_description}</td>
                                    <td>${charge.description}</td>
                                    <td>${new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(charge.total_amount)}</td>
                                </tr>`;
                        });
                        charges_dep_html += '<tr>\
                                    <td colspan="2"><b style="font-size:16px">TỔNG CỘNG</b></td>\
                                    <td><b style="font-size:16px">'+ new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(total_amount) + '</b></td>\
                                </tr></tbody></table>';
                    });
                    $("#collapse-charges-dep .card-body").html(charges_dep_html);
                    $("#charges-journey-dep").html(journeys[0].itinerary);
                }

                if (data.charges !== undefined && data.charges.ret !== undefined) {
                    let charges_ret = data.charges.ret;
                    let charges_ret_html = "";
                    $.each(charges_ret, function (key_pass, objValue) {
                        let total_amount = 0;
                        let p = passengers[key_pass];
                        let p_info = p['name'] + " (" + p['type'] + ", " + p['gender'] + ") ";

                        charges_ret_html += `<h5 class="mt-2 text-primary">${p_info}</h5>
                                <table class="table table-hover mb-5 table-bordered"><thead>
                                    <tr>
                                        <th>Loại phí</th>
                                        <th>Mô tả</th>
                                        <th>Tổng cộng (VAT)</th>
                                    </tr>
                                </thead><tbody>`;

                        $.each(objValue, function (key, charge) {
                            total_amount += charge.total_amount;
                            charges_ret_html += `<tr>
                                    <td>${charge.code} - ${charge.code_description}</td>
                                    <td>${charge.description}</td>
                                    <td>${new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(charge.total_amount)}</td>
                                </tr>`;
                        });

                        charges_ret_html += '<tr>\
                                    <td colspan="2"><b style="font-size:18px">TỔNG CỘNG</b></td>\
                                    <td><b style="font-size:18px">'+ new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(total_amount) + '</b></td>\
                                </tr></tbody></table>';
                    });
                    $("#collapse-charges-ret .card-body").html(charges_ret_html);
                    $("#charges-journey-ret").html(journeys[1].itinerary);
                    $("#card-charges-ret").show();
                }


                /*****  Nút thanh toán  *****/
                if (booking_info.status_code == 2 || booking_info.status == "There was payment transaction") $("#btn-payment-pnr").hide();
                else $("#btn-payment-pnr").show();

                $(".wrap-info-pnr").show();
                return true;
            }
        });
    });
});

function html_segment(direction, obj, journey_key) {
    direction = direction == 0 ? "dep" : "ret";
    let label_dir = direction == 'dep' ? "lượt đi" : "lượt về";

    let html = `<div class="journey journey-${direction}" id="journey-${direction}" journey_key="${journey_key}">
                    <div class="row">
                        <div class="col-md-5 border-right">
                             <div class="row py-1">
                                <div class="col-3 label">Nơi đi</div>
                                <div class="col-1">:</div>
                                <div class="col-8">${obj.dep_name} (${obj.dep_code})</div>
                            </div>
                            <div class="row py-1">
                                <div class="col-3 label">Thời gian đi</div>
                                <div class="col-1">:</div>
                                <div class="col-8">${obj.dep_time}</div>
                            </div>
                             <div class="row py-1">
                                <div class="col-3 label">Mã chuyến bay</div>
                                <div class="col-1">:</div>
                                <div class="col-8">${obj.flight_no}</div>
                            </div>
                        </div>
                        <div class="col-md-5 border-right">
                             <div class="row py-1">
                                <div class="col-3 label">Nơi đến</div>
                                <div class="col-1">:</div>
                                <div class="col-8">${obj.arv_name} (${obj.arv_code})</div>
                            </div>
                            <div class="row py-1">
                                <div class="col-3 label">Thời gian đến</div>
                                <div class="col-1">:</div>
                                <div class="col-8">${obj.arv_time}</div>
                            </div>
                             <div class="row py-1">
                                <div class="col-3 label">Số hiệu</div>
                                <div class="col-1">:</div>
                                <div class="col-8">${obj.aircraft}</div>
                            </div>
                        </div>
                        <div class="col-md-2 flex-center d-none">
                            <button class="btn btn-info btn-update_journey btn-update-journey__${direction}" id="btn-update_journey" data-bs-toggle="modal" data-bs-target="#modal-change-journey">
                                Thay đổi hành trình ${label_dir}
                            </button>
                        </div>
                    </div>
                </div>`;

    return html;
}