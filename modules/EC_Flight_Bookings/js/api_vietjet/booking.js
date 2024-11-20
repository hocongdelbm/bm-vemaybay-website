const url = "index.php?entryPoint=entryPointAPIVietjet";

$(document).ready(function () {
    $("input#btn_holding_vja").click(function () {
        // Reset
        $("#confirm_reservation_dialog_vja .wrap-journey .journey .journey_info").html("");
        $("#confirm_reservation_dialog_vja .wrap-journey .journey .datetime").html("");
        $("#confirm_reservation_dialog_vja .wrap-journey .journey .flightno").html("");
        $("#confirm_reservation_dialog_vja .wrap-journey .journey .baseprice").html("");
        $("#confirm_reservation_dialog_vja .wrap-journey .journey .ticket_class").html("");
        $("#confirm_reservation_dialog_vja .journey#reservation_journey_dep_vja").css("display", "none");
        $("#confirm_reservation_dialog_vja .journey#reservation_journey_ret_vja").css("display", "none");
        $("#confirm_reservation_dialog_vja .journey#reservation_journey_ret_vja").css({
            "border-top": "1px dashed #000",
            "padding-top": "8px",
            "margin-top": "8px"
        });

        // Thong tin hanh trinh
        let journeys = JSON.parse($("#reservation_form_vja input#journeys_info_vja").val().replace(/'/g, '"'));
        let id_journey_dep = "";
        let id_journey_ret = "";

        let ticket_class_dep = "";
        let ticket_class_ret = "";


        $('#itinerary_tbl input[name=check-journey]:checked').each(function () {
            let journey_id = $(this).attr("journey-id");
            let journey = journeys[journey_id];

            if (journey !== undefined) {
                let dom_journey = $("#reservation_journey_" + journey.type + "_vja");
                dom_journey.find('.journey_info').html(journey.dep_code + " - " + journey.arv_code);
                dom_journey.find('.datetime').html(journey.date + " " + journey.time);
                dom_journey.find('.flightno').html(journey.flightno);
                dom_journey.find('.baseprice').html(journey.price_format + " VND");
                dom_journey.find('.ticket_class').html(journey.ticket_class);

                if (journey.type == 'dep') {
                    id_journey_dep = journey_id;
                    ticket_class_dep = journey.ticket_class;
                }

                if (journey.type == 'ret') {
                    id_journey_ret = journey_id;
                    ticket_class_ret = journey.ticket_class;
                }
            }
        });

        if (id_journey_dep.length > 0) $("#confirm_reservation_dialog_vja .journey#reservation_journey_dep_vja").css("display", "flex");
        if (id_journey_ret.length > 0) $("#confirm_reservation_dialog_vja .journey#reservation_journey_ret_vja").css("display", "flex");
        if (id_journey_dep.length == 0) $("#confirm_reservation_dialog_vja .journey#reservation_journey_ret_vja").css({
            "border": "none",
            "margin-top": 0,
            "padding-top": 0
        });


        // Thong tin hanh khach
        $("#confirm_reservation_dialog_vja .wrap-passenger .passenger").remove(); // Reset
        var array_passenger = [];
        var array_id_pass = [];
        let adult = 0;
        let stt = 1;
        let check_birthdate = true;

        $('#tbl_pax input[name=check-passenger]:checked').each(function () {
            let tr_passenger = $(this).closest('tr');
            let pass_id     = tr_passenger.attr("data-id");
            let type        = tr_passenger.find("td.passenger_type").attr("data");
            let type_format = tr_passenger.find("td.passenger_type").html();
            let gender      = tr_passenger.find("td.passenger_salutation").attr("data");
            let gender_format = gender == 0 ? "Nam" : "Nữ";
            let name        = tr_passenger.find("td.passenger_name .fullname").html();
            let cic         = tr_passenger.find("td.passenger_name .cic").attr('data');
            let passport    = tr_passenger.find("td.passenger_name .passport").attr('data');
            let birthdate   = tr_passenger.find("td.passenger_birthdate .birthdate").html();
            let phone = $("#reservation_form_vja input[name=reservation_phone]").val();
            if (type == 0) adult++;

            if (!birthdate || birthdate.length == 0) {
                check_birthdate = false;
            }

            let p = {
                "type": type,
                "gender": gender,
                "name": name,
                "birthdate": birthdate,
                "phone": phone,
                "cic": cic,
                "passport": passport
            }
            array_id_pass.push(pass_id);
            array_passenger.push(p);

            // Giay to tuy than
            let cic_html = passport_html = '';
            if (type == 0) {
                cic_html = `<p class="cic"><b>CCCD: </b><span>${cic}</span></p>`;
                passport_html = `<p class="passport"><b>Passport: </b><span>${passport}</span></p>`;
            }

            // Lay thong tin vao popup confirm giu cho
            let html_passenger = `<div class="passenger">
                                    <div class="first">
                                        <div class="stt">
                                            <span>${stt}</span>
                                        </div>
                                        <i>
                                            <svg width="86px" height="86px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M10 4H14C17.7712 4 19.6569 4 20.8284 5.17157C22 6.34315 22 8.22876 22 12C22 15.7712 22 17.6569 20.8284 18.8284C19.6569 20 17.7712 20 14 20H10C6.22876 20 4.34315 20 3.17157 18.8284C2 17.6569 2 15.7712 2 12C2 8.22876 2 6.34315 3.17157 5.17157C4.34315 4 6.22876 4 10 4ZM13.25 9C13.25 8.58579 13.5858 8.25 14 8.25H19C19.4142 8.25 19.75 8.58579 19.75 9C19.75 9.41421 19.4142 9.75 19 9.75H14C13.5858 9.75 13.25 9.41421 13.25 9ZM14.25 12C14.25 11.5858 14.5858 11.25 15 11.25H19C19.4142 11.25 19.75 11.5858 19.75 12C19.75 12.4142 19.4142 12.75 19 12.75H15C14.5858 12.75 14.25 12.4142 14.25 12ZM15.25 15C15.25 14.5858 15.5858 14.25 16 14.25H19C19.4142 14.25 19.75 14.5858 19.75 15C19.75 15.4142 19.4142 15.75 19 15.75H16C15.5858 15.75 15.25 15.4142 15.25 15ZM11 9C11 10.1046 10.1046 11 9 11C7.89543 11 7 10.1046 7 9C7 7.89543 7.89543 7 9 7C10.1046 7 11 7.89543 11 9ZM9 17C13 17 13 16.1046 13 15C13 13.8954 11.2091 13 9 13C6.79086 13 5 13.8954 5 15C5 16.1046 5 17 9 17Z" fill="#1C274C"></path> </g></svg>
                                        </i>
                                    </div>
                                    <div class="left">
                                        <p><b>Loại: </b>${type_format}</p>
                                        <p class="fullname"><b>Họ tên: </b><span>${name}</span></p>
                                        ${cic_html}
                                    </div>
                                    <div class="right">
                                        <p><b>Giới tính: </b>${gender_format}</p>
                                        <p><b>Ngày sinh: </b>${birthdate}</p>
                                        ${passport_html}
                                    </div>
                                </div>`;

            $("#confirm_reservation_dialog_vja .wrap-passenger").append(html_passenger);

            stt++;
        });
        $("#confirm_reservation_dialog_vja #confirm_reservation_vja").attr("adult", adult);

        if (array_passenger.length == 0) {
            $('.toast-warning').addClass('active');
            $('.toast-warning #toast-content').text('Vui lòng chọn hành khách');
            $('.toast-warning .progress-bar').animate({ width: "100%" }, 3000);
            setTimeout(function () {
                $(".toast-warning").removeClass('active');
            }, 4000);

            $('html, body').animate({
                scrollTop: $("#tbl_pax").offset().top
            }, 1000);
            return false;
        }

        if (check_birthdate === false) {
            $('.toast-warning').addClass('active');
            $('.toast-warning #toast-content').text('Vui lòng bổ sung ngày sinh cho hành khách');
            $('.toast-warning .progress-bar').animate({ width: "100%" }, 3000);
            setTimeout(function () {
                $(".toast-warning").removeClass('active');
            }, 4000);

            $('html, body').animate({
                scrollTop: $("#tbl_pax").offset().top
            }, 1000);
            return false;
        }

        // Parameters
        const request = {
            "username": $("#reservation_form_vja input[name=reservation_username]").val(),
            "email": $("#reservation_form_vja input[name=reservation_email]").val(),
            "api_key": "1G4$vaEYghZv$I9JIj40U6D$oBqTEVHl6hBhqU$9EAfV6RB+Rq",
            "arr_passengers": JSON.stringify(array_passenger)
        }
        if (id_journey_dep.length > 0) {
            request['dep_code'] = journeys[id_journey_dep].dep_code;
            request['arv_code'] = journeys[id_journey_dep].arv_code;
            request['dep_date'] = journeys[id_journey_dep].date;
            request['deptime_dep'] = journeys[id_journey_dep].time;
            request['price_dep'] = journeys[id_journey_dep].price;
            request['flightno_dep'] = journeys[id_journey_dep].flightno;
            // request['ticket_class'] = journeys[id_journey_dep].ticket_class;
        }
        if (id_journey_ret.length > 0) {
            if (id_journey_dep.length > 0) {
                request['ret_date']         = journeys[id_journey_ret].date;
                request['deptime_ret']      = journeys[id_journey_ret].time;
                request['price_ret']        = journeys[id_journey_ret].price;
                request['flightno_ret']     = journeys[id_journey_ret].flightno;
                // request['ticket_class'] = journeys[id_journey_ret].ticket_class;
            }
            else {
                request['dep_code']     = journeys[id_journey_ret].dep_code;
                request['arv_code']     = journeys[id_journey_ret].arv_code;
                request['dep_date']     = journeys[id_journey_ret].date;
                request['deptime_dep']  = journeys[id_journey_ret].time;
                request['price_dep']    = journeys[id_journey_ret].price;
                request['flightno_dep'] = journeys[id_journey_ret].flightno;
                // request['ticket_class'] = journeys[id_journey_ret].ticket_class;
            }
        }
        $("#confirm_reservation_vja").attr("data", JSON.stringify(request));

        // For save PNR
        $("#confirm_reservation_vja").attr("id_journey_dep", id_journey_dep);
        $("#confirm_reservation_vja").attr("id_journey_ret", id_journey_ret);

        $("#confirm_reservation_vja").attr("ticket_class_dep", ticket_class_dep);
        $("#confirm_reservation_vja").attr("ticket_class_ret", ticket_class_ret);

        $("#confirm_reservation_vja").attr("array_id_pass", JSON.stringify(array_id_pass));
        if (id_journey_dep.length > 0 && id_journey_ret.length > 0) $("#confirm_reservation_vja").attr("direction", 2);
        else if (id_journey_dep.length > 0) $("#confirm_reservation_vja").attr("direction", 0);
        else if (id_journey_ret.length > 0) $("#confirm_reservation_vja").attr("direction", 1);

        // Check emergency
        if (request['dep_date'] === undefined) {
            $('.toast-warning').addClass('active');
            $('.toast-warning #toast-content').text('Vui lòng chọn hành trình!');
            $('.toast-warning .progress-bar').animate({ width: "100%" }, 3000);
            setTimeout(function () {
                $(".toast-warning").removeClass('active');
            }, 4000);

            $('.check-journey').focus();
            return false;
        }
        let year = request['dep_date'].split('-')[0];
        let month = request['dep_date'].split('-')[1];
        let day = request['dep_date'].split('-')[2];
        let hour = request['deptime_dep'].split(':')[0];
        let min = request['deptime_dep'].split(':')[1];
        let flight_time = new Date(year, month - 1, day, hour, min);
        flight_time = flight_time.getTime();
        let current_time = Date.now();
        let time_check = 86400 * 1000; // 24h (millisecond)
        if (flight_time - current_time >= time_check) $("#confirm_reservation_dialog_vja .notes .warning").html("");
        else $("#confirm_reservation_dialog_vja .notes .warning").html("Đây là vé cận, bấm xác nhận sẽ tiến hành xuất vé");

        
        let supplier_id = $('select[name="supplier_booking"]').val();
        $.ajax({
            url: url,
            type: "POST",
            data: {
                action: "get_credit_available",
                supplier_id: supplier_id,
            },
            success: function (response) {
                if (response.length > 0 && response != '[]') {
                    data = JSON.parse(response); // Object
                    $("#confirm_reservation_dialog_vja .notes .credit_available").html("Số dư hiện tại là: " + data.creditAvailable);
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                console.error(XMLHttpRequest);
                console.error("Status: " + textStatus);
                console.error("Error: " + errorThrown);
            }
        });

        showDialog("confirm_reservation_dialog_vja");
        return;
    });

    $("#confirm_reservation_vja").click(function () {
        $('.container-waiting').show();

        let body_request = $(this).attr("data");
        let direction = $(this).attr("direction");
        let adult = $(this).attr("adult");
        let id_journey_dep = $(this).attr("id_journey_dep");
        let id_journey_ret = $(this).attr("id_journey_ret");

        let array_id_pass = $(this).attr("array_id_pass");
        let booking_id = $("#reservation_form_vja input[name=reservation_booking_id]").val();
        let check_passenger = $(".passenger")[0];
        let email = $('input[name="reservation_email"]').val();
        let supplier_id = $('select[name="supplier_booking"]').val();

        if (check_passenger === undefined || check_passenger === false || array_id_pass.length == 0) {
            $('.container-waiting').hide();
            alert('Vui lòng chọn hành khách!')
            return false;
        }
        else if (adult < 1) {
            $('.container-waiting').hide();

            $('.toast-warning').addClass('active');
            $('.toast-warning #toast-content').text('Hành khách phải có người lớn!');
            $('.toast-warning .progress-bar').animate({ width: "100%" }, 3000);
            setTimeout(function () {
                $(".toast-warning").removeClass('active');
            }, 4000);
            return false;
        }
        else if (email.length == 0) {
            $('.container-waiting').hide();
            alert('Vui lòng điền email đặt chỗ');
            return false;
        }
        else if (body_request.length == 0 || booking_id.length == 0) {
            $('.container-waiting').hide();

            $('.toast-warning').addClass('active');
            $('.toast-warning #toast-content').text('Không có dữ liệu để thực hiện thao tác. Vui lòng liên hệ bộ phận IT!');
            $('.toast-warning .progress-bar').animate({ width: "100%" }, 3000);
            setTimeout(function () {
                $(".toast-warning").removeClass('active');
            }, 4000);

            return false;
        }

        if ($("#confirm_reservation_dialog_vja .notes .warning").html().length > 0) {
            let str_credit_available = $("#confirm_reservation_dialog_vja .notes .credit_available").html();
            if (confirm("Thao tác sẽ trừ tiền vào tài khoản đại lý. Bạn có chắc muốn xuất vé?\n" + str_credit_available)) { }
            else {
                $('.container-waiting').hide();
                return false;
            }
        }

        $.ajax({
            url: url,
            type: "POST",
            data: {
                action: "booking",
                body_request: body_request,
                direction: direction,
                id_journey_dep: id_journey_dep,
                id_journey_ret: id_journey_ret,
                array_id_pass: array_id_pass,
                booking_id: booking_id,
                supplier_id: supplier_id
            },
            // contentType: "application/json; charset=utf-8",
            success: function (response) {
                $('.container-waiting').hide();
                data = JSON.parse(response); // Object

                if (data['code'] == 1) {
                    let text_modal_success = data['message'];
                    showModalNotify(1, text_modal_success);
                    $('.modal-overlay, .btn-modal-close').addClass('reload');
                }
                else if (data['code'] == 0 || data['code'] == -1) {
                    if (data['message'] == "ERROR") {
                        let text_modal_error = data['message'] + '\n' + data['description'];
                        showModalNotify(0, text_modal_error);
                    }
                    else {
                        let text_modal_error = data['message'];
                        showModalNotify(0, text_modal_error);
                    }
                    return false;
                }
                else {
                    let text_modal_error = 'ERROR: Vui lòng liên hệ bộ phận IT!';
                    showModalNotify(0, text_modal_error);
                    $('.modal-overlay, .btn-modal-close').addClass('reload');

                    console.log(response);
                    return false;
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                $('.container-waiting').hide();

                let text_modal_error = 'ERROR (' + errorThrown + '): Vui lòng liên hệ bộ phận IT.';
                showModalNotify(0, text_modal_error);

                console.error(XMLHttpRequest);
                console.error("Status: " + textStatus);
                console.error("Error: " + errorThrown);
            }
        });
    });
});

function showDialog(id) {
    // Show dialog
    const dialog = document.getElementById(id);

    // If a browser doesn't support the dialog, then hide the dialog contents by default.
    if (typeof dialog.showModal !== 'function') {
        dialog.hidden = true;
    }

    // "Update details" button opens the <dialog> modally
    if (typeof dialog.showModal === "function") {
        dialog.showModal();
    } else {
        $('.toast-warning').addClass('active');
        $('.toast-warning #toast-content').text('Chức năng không được hỗ trợ trên trình duyệt này!');
        $('.toast-warning .progress-bar').animate({ width: "100%" }, 3000);
        setTimeout(function () {
            $(".toast-warning").removeClass('active');
            location.reload();
        }, 4000);

    }
}

function closeDialog(id) {
    dialog = document.getElementById(id);
    dialog.close();
}