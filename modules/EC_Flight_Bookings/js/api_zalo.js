const dia_chi_vp_1 = "252/12 Nguyễn Thượng Hiền, Phường Hạnh Thông, Gò Vấp, TP.HCM";

$(document).ready(function () {
    $("#send-zalo").click(function () {
        showDialog("dialog-send-zalo");
        $("#dialog-send-zalo").draggable();

        // Hành trình/hành khách/hành lý/lịch sử ZBS load lazy qua AJAX khi mở dialog
        // (server không tính sẵn nữa - xem getZaloDialogData trong custom/include/utils/booking.php)
        var bookingId = $('input[name="zalo_booking_id"]').val();
        $.ajax({
            url: "index.php?entryPoint=entryPointFlightBookings",
            type: "POST",
            data: { for: "getZaloDialogData", booking_id: bookingId },
            dataType: "json",
            success: function (data) {
                $("input#zalo_journeys").val(btoa(encodeURIComponent(JSON.stringify(data.journeys || []))));
                $('input[name="zalo_passenger"]').val(data.passenger || '');
                $('input[name="zalo_baggage"]').val(data.baggage || '');

                var zbsHistory = data.zbs_history || {};
                $(".zbs-count").each(function () {
                    var type = $(this).data("zbs-type");
                    var count = zbsHistory[type] || 0;
                    $(this).text("(" + count + ")").attr("title", "Đã gửi " + count + " tin");
                });
            }
        });
        return;
    });

    // Get template ZBS
    $('input[type=radio][name=zalo_type]').change(function () {
        $('input#phone_zalo').attr('readonly', false);

        let zaloPhone   = $('input#phone_zalo').val();
        let zaloContact = $('input[name="zalo_contact"]').val();
        let passenger   = $('input[name="zalo_passenger"]').val();
        let baggage     = $('input[name="zalo_baggage"]').val();
        let booking     = $('input[name="zalo_booking_name"]').val();
        let direction   = $('input[name="zalo_flight_type"]').val();
        let totalAmount = $('#total_amount').text().replace(/,/g, '').trim();
        let html = '';

        // Thông tin hành trình
        let journeys = JSON.parse(decodeURIComponent(atob($("input#zalo_journeys").val())));
        let journey_id_dep = '', journey_id_ret = '';
        $.each(journeys, function (key, valueObj) {
            if (valueObj['type'] == 'dep') journey_id_dep = key;
            else if (valueObj['type'] == 'ret') journey_id_ret = key;
        });

        if (this.value == 'journey') {
            let openning_paragraph = `
                <p style="font-weight:400; margin-bottom:5px;">Cảm ơn <input type="text" name="zalo_field_lien_he" id="zalo_field_lien_he" class="zalo_field" value="${zaloContact}" /> đã đặt booking <b>${booking}</b> trên Tìm Chuyến Bay Travelpass.</p>
                <p style="font-weight:400;">Thông tin hành trình bao gồm: </p>
                <input type="hidden" name="zalo_field_booking" id="zalo_field_booking" class="zalo_field" value="${booking}" maxlength="30" />`;
            let concluding_paragraph = `<div class="notify-check">
                <p>Vui lòng kiểm tra thông tin kỹ càng, đảm bảo chính xác trên hệ thống.</p>
                <p class="mt-1">Quý khách nhấn nút quan tâm để cập nhật thông tin hành trình.</p>
            </div>`;

            // Một chiều
            if (direction == '1') {
                let data = journeys[journey_id_dep];

                html = `${openning_paragraph}
                    <input type="hidden" name="zalo_zns_type" id="zalo_zns_type" value="journey-one-way" />
                    <ul class="list-group list-group-flush mt-1">
                        <li class="list-group-item p-1" fieldname="noi_di">
                            <div class="row">
                                <div class="col-3">Nơi đi</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_noi_di" id="zalo_field_noi_di" class="zalo_field" value="${data?.dep_name ?? ''}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1" fieldname="noi_den">
                            <div class="row">
                                <div class="col-3">Nơi đến</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_noi_den" id="zalo_field_noi_den" class="zalo_field" value="${data?.arv_name ?? ''}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1" fieldname="ngay_gio_di">
                            <div class="row">
                                <div class="col-3">Ngày giờ đi</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_ngay_gio_di" id="zalo_field_ngay_gio_di" class="zalo_field" value="${data?.datetime ?? ''}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1" fieldname="hang_hang_khong">
                            <div class="row">
                                <div class="col-3">Hãng hàng không</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_hang_hang_khong" id="zalo_field_hang_hang_khong" class="zalo_field" value="${data?.airline ?? ''}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1" fieldname="ma_chuyen">
                            <div class="row">
                                <div class="col-3">Mã chuyến</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_ma_chuyen" id="zalo_field_ma_chuyen" class="zalo_field" value="${data?.flightno ?? ''}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1" fieldname="hang_ve">
                            <div class="row">
                                <div class="col-3">Hạng vé</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_hang_ve" id="zalo_field_hang_ve" class="zalo_field" value="${data?.class ?? ''}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1" fieldname="hanh_khach">
                            <div class="row">
                                <div class="col-3">Hành khách</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_hanh_khach" id="zalo_field_hanh_khach" class="zalo_field" value="${passenger}" maxlength="100" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1" fieldname="hanh_ly">
                            <div class="row">
                                <div class="col-3">Hành lý</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_hanh_ly" id="zalo_field_hanh_ly" class="zalo_field" value="${baggage}" maxlength="100" />
                                    <p style="font-size:13px; color:grey; font-style:italic">Booker nên kiểm tra lại thông tin hành lý.</p>
                                </div>
                            </div>
                        </li>
                    </ul>
                    ${concluding_paragraph}`;
            }
            // Khứ hồi
            else if (direction == '0') {
                let data_dep = journeys[journey_id_dep];
                let data_ret = journeys[journey_id_ret];

                let chuyen_bay_di = data_dep?.airline ? data_dep['airline'] + ' (' + data_dep['flightno'] + ') - ' + data_dep['class'] : '';
                let chuyen_bay_ve = data_ret?.airline ? data_ret['airline'] + ' (' + data_ret['flightno'] + ') - ' + data_ret['class'] : '';

                html = `${openning_paragraph}
                    <input type="hidden" name="zalo_zns_type" id="zalo_zns_type" value="journey-round-trip" />
                    <ul class="list-group list-group-flush mt-1">
                        <li class="list-group-item p-1">
                            <div class="row">
                                <div class="col-3">Chiều đi</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_chieu_di" id="zalo_field_chieu_di" class="zalo_field" value="${data_dep?.dep_name ?? ''} - ${data_dep?.arv_name ?? ''}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1">
                            <div class="row">
                                <div class="col-3">Ngày giờ đi</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_ngay_gio_di" id="zalo_field_ngay_gio_di" class="zalo_field" value="${data_dep?.datetime ?? ''}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1">
                            <div class="row">
                                <div class="col-3">Chuyến bay đi</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_chuyen_bay_di" id="zalo_field_chuyen_bay_di" class="zalo_field" value="${chuyen_bay_di}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1">
                            <div class="row">
                                <div class="col-3">Chiều về</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_chieu_ve" id="zalo_field_chieu_ve" class="zalo_field" value="${data_ret?.dep_name ?? ''} - ${data_ret?.arv_name ?? ''}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1">
                            <div class="row">
                                <div class="col-3">Ngày giờ về</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_ngay_gio_ve" id="zalo_field_ngay_gio_ve" class="zalo_field" value="${data_ret?.datetime ?? ''}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1">
                            <div class="row">
                                <div class="col-3">Chuyến bay về</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_chuyen_bay_ve" id="zalo_field_chuyen_bay_ve" class="zalo_field" value="${chuyen_bay_ve}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1">
                            <div class="row">
                                <div class="col-3">Hành khách</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_hanh_khach" id="zalo_field_hanh_khach" class="zalo_field" value="${passenger}" maxlength="100" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1">
                            <div class="row">
                                <div class="col-3">Hành lý</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_hanh_ly" id="zalo_field_hanh_ly" class="zalo_field" value="${baggage}" maxlength="100" />
                                    <p style="font-size:13px; color:grey; font-style:italic">Booker nên kiểm tra lại thông tin hành lý.</p>
                                </div>
                            </div>
                        </li>
                    </ul>
                    ${concluding_paragraph}`;
            }
        }
        else if (this.value == 'payment') {
            let openning_paragraph = `
                <p style="font-weight:400;">Tìm Chuyến Bay xin chào, Quý khách <input type="text" name="zalo_field_ten_hk" id="zalo_field_ten_hk" class="zalo_field" value="${zaloContact}" maxlength="30" /> có booking có mã <b>${booking}</b> cần thanh toán trước <input type="text" name="zalo_field_han_giu_cho" id="zalo_field_han_giu_cho" class="zalo_field" placeholder="15:00 20/10/2023" style="width:125px; padding:0 7px;" maxlength="30" />.</p>
                <p style="font-weight:400;">Quý khách có thể chọn những phương thức thanh toán sau.</p>
                <input type="hidden" name="zalo_field_booking" id="zalo_field_booking" class="zalo_field" value="${booking}" />
                <input type="hidden" name="zalo_field_dia_chi_vp_1" id="zalo_field_dia_chi_vp_1" class="zalo_field" value="${dia_chi_vp_1}" />`;

            html = `${openning_paragraph}
                <input type="hidden" name="zalo_zns_type" id="zalo_zns_type" value="${this.value}" />
                <ul class="list-group list-group-flush mt-1">
                    <li class="list-group-item p-1" style="padding: 5px 15px;">1. Thanh toán online trên website chúng tôi. Nhân viên tư vấn sẽ hỗ trợ</li>
                    <li class="list-group-item p-1" style="padding: 5px 15px;">2. Chuyển khoản qua ngân hàng cùng hệ thống. Đây là hình thức tối ưu nhất vì không mất phí thanh toán. Quý khách chuyển vào tài khoản ngân hàng sau:
                        <div style="padding: 4px 15px; font-weight:400">
                            <p>Quý khách ghé văn phòng hoặc giao vé tận nơi (có phí). Địa chỉ: <b>${dia_chi_vp_1}</b></p>
                            <div class="payment_tag">
                                <div class="heading p-2">
                                    <div class="heading_icon">
                                        <svg width="24px" height="24px" viewBox="0 0 24 24" stroke-width="1.5" fill="none" xmlns="http://www.w3.org/2000/svg" color="#096bff">
                                            <path d="M22 9V17C22 18.1046 21.1046 19 20 19H4C2.89543 19 2 18.1046 2 17V7C2 5.89543 2.89543 5 4 5H20C21.1046 5 22 5.89543 22 7V9ZM22 9H6" stroke="#096bff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M16.5 13.3819C16.7654 13.1444 17.1158 13 17.5 13C18.3284 13 19 13.6716 19 14.5C19 15.3284 18.3284 16 17.5 16C17.1158 16 16.7654 15.8556 16.5 15.6181" stroke="#096bff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M16.5 13.3819C16.2346 13.1444 15.8842 13 15.5 13C14.6716 13 14 13.6716 14 14.5C14 15.3284 14.6716 16 15.5 16C15.8842 16 16.2346 15.8556 16.5 15.6181" stroke="#096bff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg> Thanh toán ngay</div>
                                    <span>&gt;</span>
                                </div>
                                <div class="wrap">
                                    <div class="r1">
                                        <span class="col-4">Ngân hàng</span>
                                        <span class="text-important">Ngân hàng TMCP Quân đội (MBBank)</span>
                                    </div>
                                    <div class="r2">
                                        <span class="col-4">Tên tài khoản</span>
                                        <span>CONG TY TNHH MINH HONG VO</span>
                                    </div>
                                    <div class="r3">
                                        <span class="col-4">Số tài khoản</span>
                                        <span class="text-important">0000920990898</span>
                                    </div>
                                    <div class="r4">
                                        <span class="col-4">Số tiền (VND)</span>
                                        <input type="number" name="zalo_field_transfer_amount" id="zalo_field_transfer_amount" class="zalo_field" value="${totalAmount}" maxlength="12" />
                                    </div>
                                    <div class="r5">
                                        <span class="col-4">Nội dung</span>
                                        <input type="text" name="zalo_field_transfer_note" id="zalo_field_transfer_note" class="zalo_field" value="Thanh toan ${zaloPhone}" maxlength=90 />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>`;
        }
        else if (this.value == 'code') {
            if (direction == '1') {
                let data = journeys[journey_id_dep];
                let openning_paragraph = `
                    <p style="font-weight:400;">Cảm ơn <input type="text" name="zalo_field_lien_he" id="zalo_field_lien_he" class="zalo_field" value="${zaloContact}" />, Tìm chuyến bay Travelpass gửi bạn code vé <input type="text" name="zalo_field_code_pnr" id="zalo_field_code_pnr" class="zalo_field" value="" style="width:100px; padding:0 7px;"/>.</p>
                    <p style="font-weight:400;">Thông tin hành trình bao gồm:</p>
                `;

                html = `${openning_paragraph}
                    <input type="hidden" name="zalo_zns_type" id="zalo_zns_type" value="code-one-way" />
                    <ul class="list-group list-group-flush mt-1">
                        <li class="list-group-item p-1">
                            <div class="row">
                                <div class="col-3">Nơi đi</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_noi_di" id="zalo_field_noi_di" class="zalo_field" value="${data?.dep_name ?? ''}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1">
                            <div class="row">
                                <div class="col-3">Nơi đến</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_noi_den" id="zalo_field_noi_den" class="zalo_field" value="${data?.arv_name ?? ''}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1">
                            <div class="row">
                                <div class="col-3">Ngày giờ đi</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_ngay_gio_di" id="zalo_field_ngay_gio_di" class="zalo_field" value="${data?.datetime ?? ''}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1">
                            <div class="row">
                                <div class="col-3">Hãng hàng không</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_hang_hang_khong" id="zalo_field_hang_hang_khong" class="zalo_field" value="${data?.airline ?? ''}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1">
                            <div class="row">
                                <div class="col-3">Mã chuyến</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_ma_chuyen" id="zalo_field_ma_chuyen" class="zalo_field" value="${data?.flightno ?? ''}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1">
                            <div class="row">
                                <div class="col-3">Hạng vé</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_hang_ve" id="zalo_field_hang_ve" class="zalo_field" value="${data?.class ?? ''}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1">
                            <div class="row">
                                <div class="col-3">Hành khách</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_hanh_khach" id="zalo_field_hanh_khach" class="zalo_field" value="${passenger}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1">
                            <div class="row">
                                <div class="col-3">Hành lý</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_hanh_ly" id="zalo_field_hanh_ly" class="zalo_field" value="${baggage}" maxlength="30"/>
                                </div>
                            </div>
                        </li>
                    </ul>`;
            }
            else if (direction == '0') {
                let data_dep = journeys[journey_id_dep];
                let data_ret = journeys[journey_id_ret];
                let openning_paragraph = `
                    <p style="font-weight:400;">Cảm ơn <input type="text" name="zalo_field_lien_he" id="zalo_field_lien_he" class="zalo_field" value="${zaloContact}" />, Tìm chuyến bay Travelpass gửi bạn code vé khứ hồi <input type="text" name="zalo_field_code_pnr" id="zalo_field_code_pnr" class="zalo_field" value="" style="width:100px; padding:0 7px;"/>.</p>
                    <p style="font-weight:400;">Thông tin hành trình bao gồm:</p>
                `;
                let chuyen_bay_di = data_dep?.airline ? data_dep['airline'] + ' (' + data_dep['flightno'] + ') - ' + data_dep['class'] : '';
                let chuyen_bay_ve = data_ret?.airline ? data_ret['airline'] + ' (' + data_ret['flightno'] + ') - ' + data_ret['class'] : '';

                html = `${openning_paragraph}
                    <input type="hidden" name="zalo_zns_type" id="zalo_zns_type" value="code-round-trip" />
                    <ul class="list-group list-group-flush mt-1">
                        <li class="list-group-item p-1">
                            <div class="row">
                                <div class="col-3">Chiều đi</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_chieu_di" id="zalo_field_chieu_di" class="zalo_field" value="${data_dep?.dep_name ?? ''} - ${data_dep?.arv_name ?? ''}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1">
                            <div class="row">
                                <div class="col-3">Ngày giờ đi</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_ngay_gio_di" id="zalo_field_ngay_gio_di" class="zalo_field" value="${data_dep?.datetime ?? ''}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1">
                            <div class="row">
                                <div class="col-3">Chuyến bay đi</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_chuyen_bay_di" id="zalo_field_chuyen_bay_di" class="zalo_field" value="${chuyen_bay_di}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1">
                            <div class="row">
                                <div class="col-3">Chiều về</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_chieu_ve" id="zalo_field_chieu_ve" class="zalo_field" value="${data_ret?.dep_name ?? ''} - ${data_ret?.arv_name ?? ''}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1">
                            <div class="row">
                                <div class="col-3">Ngày giờ về</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_ngay_gio_ve" id="zalo_field_ngay_gio_ve" class="zalo_field" value="${data_ret?.datetime ?? ''}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1">
                            <div class="row">
                                <div class="col-3">Chuyến bay về</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_chuyen_bay_ve" id="zalo_field_chuyen_bay_ve" class="zalo_field" value="${chuyen_bay_ve}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1">
                            <div class="row">
                                <div class="col-3">Hành khách</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_hanh_khach" id="zalo_field_hanh_khach" class="zalo_field" value="${passenger}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item p-1">
                            <div class="row">
                                <div class="col-3">Hành lý</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_hanh_ly" id="zalo_field_hanh_ly" class="zalo_field" value="${baggage}" maxlength="30"/>
                                </div>
                            </div>
                        </li>
                    </ul>`;
            }
        }
        // else if (this.value == 'after-call-sale') {
        //     let data = {};
        //     if(direction == '1') data = journeys[journey_id_dep];
        //     else if(direction == '0') data = journeys[journey_id_ret];

        //     html = `
        //         <input type="hidden" name="zalo_zns_type" id="zalo_zns_type" value="${this.value}" />
        //         <p>Xin chào <input type="text" name="zalo_field_full_name" id="zalo_field_full_name" class="zalo_field" value="${zaloContact}" maxlength="30" style="width:250px" />,</p>
        //         <p style="font-weight:400">Cảm ơn <span id="full_name_copy">${zaloContact}</span> đã sử dụng dịch vụ của Tìm Chuyến Bay.</p>
        //         <p style="font-weight:400">Mã hành trình <input type="text" name="zalo_field_flight_no" id="zalo_field_flight_no" class="zalo_field" value="${data?.flightno ?? ''}" style="width:80px" />, ngày giờ bay <input type="text" name="zalo_field_datetime" id="zalo_field_datetime" class="zalo_field" value="${data?.datetime ?? ''}" style="width:160px" />.</p>
        //         <p style="font-weight:400">Quý khách nhấn nút quan tâm để cấp nhật thông tin đặt vé mới nhất mỗi ngày.</p>`;
        // }
        else if (this.value == 'delay') {
            html = `
                <input type="hidden" name="zalo_zns_type" id="zalo_zns_type" value="${this.value}" />
                <p style="font-weight:400">Xin chào <input type="text" name="zalo_field_full_name" id="zalo_field_full_name" class="zalo_field" value="${zaloContact}" maxlength="30" style="width:250px" />, vì lý do khai thác nên chuyến bay có sự thay đổi:</p>
                <ul class="list-group list-group-flush mt-1">
                    <li class="list-group-item p-1">
                        <div class="row">
                            <div class="col-3">Code vé</div>
                            <div class="col-9">
                                <input type="text" name="zalo_field_pnr" id="zalo_field_pnr" class="zalo_field" value="" maxlength="30" />
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item p-1">
                        <div class="row">
                            <div class="col-3">Hành trình</div>
                            <div class="col-9">
                                <div class="input-group">
                                    <input type="text" name="zalo_field_journey_old" id="zalo_field_journey_old" class="zalo_field form-control" value="" maxlength="100" placeholder="VJ123 HAN đi SGN lúc 15:00 08-07-2024" />
                                    <button type="button" class="btn btn-secondary fw-normal" onclick="get_info_itinerary(0)" style="font">Đi</button>
                                    <button type="button" class="btn btn-secondary fw-normal" onclick="get_info_itinerary(1)" style="font">Về</button>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item p-1">
                        <div class="row">
                            <div class="col-3">Chuyển sang</div>
                            <div class="col-9">
                                <input type="text" name="zalo_field_journey_new" id="zalo_field_journey_new" class="zalo_field" value="" maxlength="100" placeholder="VJ123 HAN-SGN lúc 19:00 08/07/2024" />
                            </div>
                        </div>
                    </li>
                </ul>
                <p style="font-weight:400">Quý khách nhấn nút quan tâm để cấp nhật thông tin hành trình mới nhất.</p>
            `;
        }
        else if (this.value == 'remind-flight') {
            html = `
                <input type="hidden" name="zalo_zns_type" id="zalo_zns_type" value="${this.value}" />
                <p style="font-weight:400">
                    Xin chào <input type="text" name="zalo_field_full_name" id="zalo_field_full_name" class="zalo_field" value="${zaloContact}" maxlength="30" style="width:250px" />,
                    quý khách nên có mặt ở sân bay trước 90 phút.
                </p>
                <ul class="list-group list-group-flush mt-1">
                    <li class="list-group-item p-1">
                        <div class="row">
                            <div class="col-3">Code vé</div>
                            <div class="col-9">
                                <input type="text" name="zalo_field_pnr" id="zalo_field_pnr" class="zalo_field" value="" maxlength="30" />
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item p-1">
                        <div class="row">
                            <div class="col-3">Mã chuyến</div>
                            <div class="col-9">
                                <input type="text" name="zalo_field_flight_no" id="zalo_field_flight_no" class="zalo_field" value="" maxlength="30" placeholder="VJ123" />
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item p-1">
                        <div class="row">
                            <div class="col-3">Hành trình</div>
                            <div class="col-9">
                                <input type="text" name="zalo_field_journey" id="zalo_field_journey" class="zalo_field" value="" maxlength="100" placeholder="Hồ Chí Minh - Hà Nội" />
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item p-1">
                        <div class="row">
                            <div class="col-3">Ngày giờ bay</div>
                            <div class="col-9">
                                <input type="text" name="zalo_field_datetime" id="zalo_field_datetime" class="zalo_field" value="" maxlength="30" placeholder="08/07/2024 lúc 22:00" />
                            </div>
                        </div>
                    </li>
                </ul>
                <p style="font-weight:400">Vui lòng theo dõi bảng điện tử và lưu ý cổng ra máy bay.<br />Quý khách nhấn nút quan tâm để cập nhật thông tin hành trình mới nhất.</p>
            `;
        }

        $('#zalo-message').html(html);
    });

    // Send API
    $('#confirm-send-zalo').click(function (e) {
        let phone = $('input#phone_zalo').val();
        let bookingId = $('input[name="zalo_booking_id"]').val();
        let znsType = $('input[name="zalo_zns_type"]').val();
        let templateData = {};

        // Validate
        let error = false;
        $('.zalo_field').each(function (i, obj) {
            let id = $(this).attr('id');
            let name = id.replaceAll("zalo_field_", "");
            let value = $(this).val();
            if ((value === undefined || value.length == 0) && name != "hanh_ly") {
                $('#' + id).css("border-color", "red");

                if (znsType == "payment") alert('Vui lòng nhập thông tin hạn giữ chỗ');
                else if (znsType == "code-one-way" || znsType == "code-round-trip") alert('Vui lòng nhập thông tin code vé');
                else alert('Vui lòng nhập đầy đủ thông tin');

                error = true;
                return;
            }

            templateData[name] = value;
        });
        if (error) {
            e.preventDefault();
            return;
        }

        $.ajax({
            url: "index.php?entryPoint=entryPointGeneral",
            type: "POST",
            contentType: "application/json",
            dataType: "json",
            data: JSON.stringify({
                class: "entryZaloOAClass",
                method: "sendTemplateMessage",
                params: {
                    phoneNumber: phone,
                    type: znsType,
                    parentId: bookingId,
                    parentType: "EC_Flight_Bookings",
                    templateData: templateData
                }
            }),
            beforeSend: function() {
                closeDialogZaloZBS();
                $('.container-waiting').show();
            },
            success: function (response) {
                $('.container-waiting').hide();
                if ('status' in response && response.status) showModalNotify(1, "Đã gửi");
                else showModalNotify(0, response.message || 'Thao tác không thành công')
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                $('.container-waiting').hide();
                showModalNotify(0, 'ERROR (' + errorThrown + '): Vui lòng liên hệ IT để được hỗ trợ')
                console.error(XMLHttpRequest);
            }
        });
    });

    // Change the fullname
    $('#zalo_field_full_name').on('input', function () {
        let value = $(this).val().trim();
        $('#full_name_copy').html(value);
    });
});

function closeDialogZaloZBS() {
    // Reset
    $('#zalo-message').html('');
    $('input[name="zalo_type"]').prop('checked', false);

    document.getElementById("dialog-send-zalo").close();
}

function get_info_itinerary(dir = 0) {
    const rows = document.querySelectorAll("#itinerary_tbl tbody tr");
    const directionsMap = {};

    rows.forEach(row => {
        const directionCell = row.querySelector('td[data-direction]');
        if (!directionCell) return;

        const direction = parseInt(directionCell.getAttribute("data-direction"));
        if (!directionsMap[direction]) directionsMap[direction] = [];

        const rowData = {};
        row.querySelectorAll("td").forEach(td => {
            const label = td.getAttribute("data-label");
            const value = td.textContent.trim();
            if (label) rowData[label] = value;
        });

        directionsMap[direction].push(rowData);
    });

    const result = [];
    for (const direction in directionsMap) {
        const group = directionsMap[direction];
        const soHieu = (group[0]["Số hiệu"] || "").replace(/\s+/g, '');
        const noiDi = group[0]["Nơi đi"] || "";
        const noiDen = group[group.length - 1]["Nơi đến"] || "";
        const ngayGioDiRaw = group[0]["Ngày giờ đi"] || "";
        
        // Change format ngayGioDi;
        let ngayGioDi = ngayGioDiRaw;
        if (ngayGioDiRaw.includes(" ")) {
            const [date, time] = ngayGioDiRaw.split(" ");
            ngayGioDi = `${time} ${date}`;
        }

        result[direction] = `${soHieu} ${noiDi} đi ${noiDen} lúc ${ngayGioDi}`;
    }

    $('#zalo_field_journey_old').val(result[dir] || "");
    return result[dir] || "";
}