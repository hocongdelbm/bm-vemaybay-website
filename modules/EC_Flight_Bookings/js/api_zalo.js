const url_zalo_zns = "index.php?entryPoint=entryPointZalo";
const dia_chi_vp_1 = "65/28 Giải Phóng, P.4, Tân Bình, TP.HCM";

$(document).ready(function () {
    $("#send-zalo").click(function () {
        showDialog("dialog-send-zalo");
        return;
    });

    // Get template
    $('input[type=radio][name=zalo_type]').change(function () {
        $('#phone_zalo').attr('readonly', false);

        let direction = $('input[name=flight_type_zalo]').val();
        let passenger = $('input[name=passenger_zalo]').val();
        let luggage = $('input[name=luggage_zalo]').val();
        let booking = $('#name b').html();
        let contact_name = $('#contact_name .contact_name').attr('data');
        let phone = $('#phone_zalo').val();
        let total_amount = $('#total_amount').text().replace(/,/g, '').trim();
        let html = '';

        // Thông tin hành trình
        let journeys = JSON.parse($("#journeys_zalo").val().replace(/'/g, '"'));
        let journey_id_dep = '', journey_id_ret = '';
        $.each(journeys, function (key, valueObj) {
            if (valueObj['type'] == 'dep') journey_id_dep = key;
            else if (valueObj['type'] == 'ret') journey_id_ret = key;
        });

        if (this.value == 'journey') {
            let openning_paragraph = `
                <p style="font-weight:400;">Cảm ơn <input type="text" name="zalo_field_lien_he" id="zalo_field_lien_he" class="zalo_field" value="${contact_name}" /> đã đặt booking <b>${booking}</b> trên Tìm Chuyến Bay Travelpass.</p>
                <p style="font-weight:400;">Thông tin hành trình bao gồm: </p>
                <input type="hidden" name="zalo_field_booking" id="zalo_field_booking" class="zalo_field" value="${booking}" maxlength="30" />`;
            let concluding_paragraph = `
                <div class="notify-check">
                    <p>Vui lòng kiểm tra thông tin kỹ càng, đảm bảo chính xác trên hệ thống.</p>
                    <p>Quý khách nhấn nút quan tâm để cập nhật thông tin hành trình.</p>
                </div>`;

            // Một chiều
            if (direction == '1') {
                let data = journeys[journey_id_dep];

                html = `${openning_paragraph}
                    <input type="hidden" name="zalo_type_zns" id="zalo_type_zns" value="journey-one-way" />
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item" fieldname="noi_di">
                            <div class="row">
                                <div class="col-3">Nơi đi</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_noi_di" id="zalo_field_noi_di" class="zalo_field" value="${data['dep_name']}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item" fieldname="noi_den">
                            <div class="row">
                                <div class="col-3">Nơi đến</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_noi_den" id="zalo_field_noi_den" class="zalo_field" value="${data['arv_name']}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item" fieldname="ngay_gio_di">
                            <div class="row">
                                <div class="col-3">Ngày giờ đi</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_ngay_gio_di" id="zalo_field_ngay_gio_di" class="zalo_field" value="${data['datetime']}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item" fieldname="hang_hang_khong">
                            <div class="row">
                                <div class="col-3">Hãng hàng không</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_hang_hang_khong" id="zalo_field_hang_hang_khong" class="zalo_field" value="${data['airline']}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item" fieldname="ma_chuyen">
                            <div class="row">
                                <div class="col-3">Mã chuyến</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_ma_chuyen" id="zalo_field_ma_chuyen" class="zalo_field" value="${data['flightno']}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item" fieldname="hang_ve">
                            <div class="row">
                                <div class="col-3">Hạng vé</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_hang_ve" id="zalo_field_hang_ve" class="zalo_field" value="${data['class']}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item" fieldname="hanh_khach">
                            <div class="row">
                                <div class="col-3">Hành khách</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_hanh_khach" id="zalo_field_hanh_khach" class="zalo_field" value="${passenger}" maxlength="100" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item" fieldname="hanh_ly">
                            <div class="row">
                                <div class="col-3">Hành lý</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_hanh_ly" id="zalo_field_hanh_ly" class="zalo_field" value="${luggage}" maxlength="100" />
                                    <p style="font-size:13px; color:grey; font-style:italic">Booker nên bổ sung thêm tổng số kg để khách yên tâm. <br />VD: 2 kiện (tổng 30kg)</p>
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

                html = `${openning_paragraph}
                    <input type="hidden" name="zalo_type_zns" id="zalo_type_zns" value="journey-round-trip" />
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-3">Chiều đi</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_chieu_di" id="zalo_field_chieu_di" class="zalo_field" value="${data_dep['dep_name'] + ' - ' + data_dep['arv_name']}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-3">Ngày giờ đi</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_ngay_gio_di" id="zalo_field_ngay_gio_di" class="zalo_field" value="${data_dep['datetime']}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-3">Chuyến bay đi</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_chuyen_bay_di" id="zalo_field_chuyen_bay_di" class="zalo_field" value="${data_dep['airline'] + ' (' + data_dep['flightno'] + ') - ' + data_dep['class']}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-3">Chiều về</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_chieu_ve" id="zalo_field_chieu_ve" class="zalo_field" value="${data_ret['dep_name'] + ' - ' + data_ret['arv_name']}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-3">Ngày giờ về</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_ngay_gio_ve" id="zalo_field_ngay_gio_ve" class="zalo_field" value="${data_ret['datetime']}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-3">Chuyến bay về</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_chuyen_bay_ve" id="zalo_field_chuyen_bay_ve" class="zalo_field" value="${data_ret['airline'] + ' (' + data_ret['flightno'] + ') - ' + data_ret['class']}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-3">Hành khách</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_hanh_khach" id="zalo_field_hanh_khach" class="zalo_field" value="${passenger}" maxlength="100" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-3">Hành lý</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_hanh_ly" id="zalo_field_hanh_ly" class="zalo_field" value="${luggage}" maxlength="100" />
                                    <p style="font-size:13px; color:grey; font-style:italic">Booker nên bổ sung thêm tổng số kg để khách yên tâm. <br />VD: 2 kiện đi (tổng 30kg), 3 kiện về (tổng 40kg)</p>
                                </div>
                            </div>
                        </li>
                    </ul>
                    ${concluding_paragraph}`;
            }
        }
        else if (this.value == 'payment') {
            let openning_paragraph = `
                <p style="font-weight:400;">Tìm Chuyến Bay xin chào, Quý khách <input type="text" name="zalo_field_ten_hk" id="zalo_field_ten_hk" class="zalo_field" value="${contact_name}" maxlength="30" /> có booking <b>${booking}</b> cần thanh toán trước <input type="text" name="zalo_field_han_giu_cho" id="zalo_field_han_giu_cho" class="zalo_field" placeholder="15:00 20/10/2023" style="width:125px; padding:0 7px;" maxlength="30" />.</p>
                <p style="font-weight:400;">Quý khách có thể chọn những phương thức thanh toán sau:</p>
                <input type="hidden" name="zalo_field_booking" id="zalo_field_booking" class="zalo_field" value="${booking}" />
                <input type="hidden" name="zalo_field_dia_chi_vp_1" id="zalo_field_dia_chi_vp_1" class="zalo_field" value="${dia_chi_vp_1}" />`;

            html = `${openning_paragraph}
                <input type="hidden" name="zalo_type_zns" id="zalo_type_zns" value="${this.value}" />
                <ul class="list-group list-group-flush">
                    <li class="list-group-item" style="padding: 5px 15px;">1. Thanh toán online trên website chúng tôi. Nhân viên tư vấn sẽ hỗ trợ</li>
                    <li class="list-group-item" style="padding: 5px 15px;">2. Chuyển khoản qua ngân hàng cùng hệ thống.
                        <div style="padding: 4px 15px; font-weight:400">
                            <p>Đây là hình thức tối ưu nhất vì không mất phí thanh toán. Quý khách chuyển vào tài khoản ngân hàng sau:</p>
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
                                        <input type="number" name="zalo_field_transfer_amount" id="zalo_field_transfer_amount" class="zalo_field" value="${total_amount}" maxlength="12" />
                                    </div>
                                    <div class="r5">
                                        <span class="col-4">Nội dung</span>
                                        <input type="text" name="zalo_field_bank_transfer_note" id="zalo_field_bank_transfer_note" class="zalo_field" value="Thanh toan ${phone}" maxlength=90 />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item" style="padding: 5px 15px;">
                        Quý khách ghé văn phòng hoặc giao vé tận nơi (có phí).
                        <p>Địa chỉ: ${dia_chi_vp_1}</p>
                    </li>
                </ul>`;
        }
        else if (this.value == 'code') {
            if (direction == '1') {
                let data = journeys[journey_id_dep];
                let openning_paragraph = `
                    <p style="font-weight:400;">Cảm ơn <input type="text" name="zalo_field_lien_he" id="zalo_field_lien_he" class="zalo_field" value="${contact_name}" />, Tìm chuyến bay Travelpass gửi bạn code vé <input type="text" name="zalo_field_code_pnr" id="zalo_field_code_pnr" class="zalo_field" value="" style="width:100px; padding:0 7px;"/>.</p>
                    <p style="font-weight:400;">Thông tin hành trình bao gồm:</p>
                `;

                html = `${openning_paragraph}
                    <input type="hidden" name="zalo_type_zns" id="zalo_type_zns" value="code-one-way" />
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-3">Nơi đi</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_noi_di" id="zalo_field_noi_di" class="zalo_field" value="${data['dep_name']}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-3">Nơi đến</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_noi_den" id="zalo_field_noi_den" class="zalo_field" value="${data['arv_name']}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-3">Ngày giờ đi</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_ngay_gio_di" id="zalo_field_ngay_gio_di" class="zalo_field" value="${data['datetime']}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-3">Hãng hàng không</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_hang_hang_khong" id="zalo_field_hang_hang_khong" class="zalo_field" value="${data['airline']}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-3">Mã chuyến</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_ma_chuyen" id="zalo_field_ma_chuyen" class="zalo_field" value="${data['flightno']}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-3">Hạng vé</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_hang_ve" id="zalo_field_hang_ve" class="zalo_field" value="${data['class']}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-3">Hành khách</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_hanh_khach" id="zalo_field_hanh_khach" class="zalo_field" value="${passenger}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-3">Hành lý</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_hanh_ly" id="zalo_field_hanh_ly" class="zalo_field" value="${luggage}" maxlength="30"/>
                                </div>
                            </div>
                        </li>
                    </ul>`;
            }
            else if (direction == '0') {
                let data_dep = journeys[journey_id_dep];
                let data_ret = journeys[journey_id_ret];
                let openning_paragraph = `
                    <p style="font-weight:400;">Cảm ơn <input type="text" name="zalo_field_lien_he" id="zalo_field_lien_he" class="zalo_field" value="${contact_name}" />, Tìm chuyến bay Travelpass gửi bạn code vé khứ hồi <input type="text" name="zalo_field_code_pnr" id="zalo_field_code_pnr" class="zalo_field" value="" style="width:100px; padding:0 7px;"/>.</p>
                    <p style="font-weight:400;">Thông tin hành trình bao gồm:</p>
                `;

                html = `${openning_paragraph}
                    <input type="hidden" name="zalo_type_zns" id="zalo_type_zns" value="code-round-trip" />
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-3">Chiều đi</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_chieu_di" id="zalo_field_chieu_di" class="zalo_field" value="${data_dep['dep_name'] + ' - ' + data_dep['arv_name']}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-3">Ngày giờ đi</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_ngay_gio_di" id="zalo_field_ngay_gio_di" class="zalo_field" value="${data_dep['datetime']}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-3">Chuyến bay đi</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_chuyen_bay_di" id="zalo_field_chuyen_bay_di" class="zalo_field" value="${data_dep['airline'] + ' (' + data_dep['flightno'] + ') - ' + data_dep['class']}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-3">Chiều về</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_chieu_ve" id="zalo_field_chieu_ve" class="zalo_field" value="${data_ret['dep_name'] + ' - ' + data_ret['arv_name']}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-3">Ngày giờ về</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_ngay_gio_ve" id="zalo_field_ngay_gio_ve" class="zalo_field" value="${data_ret['datetime']}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-3">Chuyến bay về</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_chuyen_bay_ve" id="zalo_field_chuyen_bay_ve" class="zalo_field" value="${data_ret['airline'] + ' (' + data_ret['flightno'] + ') - ' + data_ret['class']}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-3">Hành khách</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_hanh_khach" id="zalo_field_hanh_khach" class="zalo_field" value="${passenger}" />
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-3">Hành lý</div>
                                <div class="col-9">
                                    <input type="text" name="zalo_field_hanh_ly" id="zalo_field_hanh_ly" class="zalo_field" value="${luggage}" maxlength="30"/>
                                </div>
                            </div>
                        </li>
                    </ul>`;
            }
        }
        else if (this.value == 'after-call-sale') {
            let data = {};
            if(direction == '1') data = journeys[journey_id_dep];
            else if(direction == '0') data = journeys[journey_id_ret];

            html = `
                <input type="hidden" name="zalo_type_zns" id="zalo_type_zns" value="${this.value}" />
                <p>Xin chào <input type="text" name="zalo_field_full_name" id="zalo_field_full_name" class="zalo_field" value="${contact_name}" maxlength="30" style="width:250px" />,</p>
                <p style="font-weight:400">Cảm ơn <span id="full_name_copy">${contact_name}</span> đã sử dụng dịch vụ của Tìm Chuyến Bay.</p>
                <p style="font-weight:400">Mã hành trình <input type="text" name="zalo_field_flight_no" id="zalo_field_flight_no" class="zalo_field" value="${data['flightno']}" style="width:80px" />, ngày giờ bay <input type="text" name="zalo_field_datetime" id="zalo_field_datetime" class="zalo_field" value="${data['datetime']}" style="width:160px" />.</p>
                <p style="font-weight:400">Quý khách nhấn nút quan tâm để cấp nhật thông tin đặt vé mới nhất mỗi ngày.</p>`;
        }
        else if (this.value == 'delay') {
            html = `
                <input type="hidden" name="zalo_type_zns" id="zalo_type_zns" value="${this.value}" />
                <p style="font-weight:400">Xin chào <input type="text" name="zalo_field_full_name" id="zalo_field_full_name" class="zalo_field" value="${contact_name}" maxlength="30" style="width:250px" />, vì lý do khai thác nên chuyến bay có sự thay đổi:</p>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-3">Code vé</div>
                            <div class="col-9">
                                <input type="text" name="zalo_field_pnr" id="zalo_field_pnr" class="zalo_field" value="" maxlength="30" />
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-3">Hành trình</div>
                            <div class="col-9">
                                <input type="text" name="zalo_field_journey_old" id="zalo_field_journey_old" class="zalo_field" value="" maxlength="100" placeholder="VJ123 HAN-SGN lúc 15:00 08/07/2024" />
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
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
                <input type="hidden" name="zalo_type_zns" id="zalo_type_zns" value="${this.value}" />
                <p style="font-weight:400">
                    Xin chào <input type="text" name="zalo_field_full_name" id="zalo_field_full_name" class="zalo_field" value="${contact_name}" maxlength="30" style="width:250px" />,
                    quý khách nên có mặt ở sân bay trước 90 phút.
                </p>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-3">Code vé</div>
                            <div class="col-9">
                                <input type="text" name="zalo_field_pnr" id="zalo_field_pnr" class="zalo_field" value="" maxlength="30" />
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-3">Mã chuyến</div>
                            <div class="col-9">
                                <input type="text" name="zalo_field_flight_no" id="zalo_field_flight_no" class="zalo_field" value="" maxlength="30" placeholder="VJ123" />
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-3">Hành trình</div>
                            <div class="col-9">
                                <input type="text" name="zalo_field_journey" id="zalo_field_journey" class="zalo_field" value="" maxlength="100" placeholder="Hồ Chí Minh - Hà Nội" />
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
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
        else if (this.value == 'promotion') {
            $('#phone_zalo').attr('readonly', true);

            html = `
                <input type="hidden" name="zalo_type_zns" id="zalo_type_zns" value="${this.value}" />
                <div class="d-flex align-items-center gap-2">
                    <select name="template_choose" id="template_choose">
                        <option value="">Chọn mẫu</option>
                        <option value="voucher_83">Voucher khuyến mãi 8/3</option>
                        <option value="voucher_30">Voucher bạn mới</option>
                        <option value="voucher_50">Voucher thành viên</option>
                        <option value="voucher_100">Voucher năm mới</option>
                        <option value="voucher_300">Voucher tháng 3</option>
                    </select>
                    <!-- <p id="template_desc"></p> -->
                </div>
                <div id="template_promotion"></div>
            `;
        }

        $('#zalo-message').html(html);
    });

    // Get template PROMOTION
    $(document).on('change', '#template_choose', function() {
        let value_template = $(this).val();
        $('.template_promotion').hide();
        $('#template_promotion').html(templatePromotion(value_template));
    });

    // Send API
    $('#confirm-send-zalo').click(function (e) {
        let type_zns        = $('input[name=zalo_type_zns]').val();
        let phone           = $('input[name=phone_zalo]').val();
        let parent_id       = $('input#parent_id_zalo').val();
        let template_data   = {};

        if(type_zns == 'promotion'){
            let zalo_id         = $('input[name=zalo_id]').val();
            let banner_pro      = 'https://bm.vemaybay.website/' + $('.template_promotion--banner img').attr('src');
            let header_pro      = $('.template_promotion--header h3').text();
            let text_pro        = $('.template_promotion--text').html();
            let template_type   = $('#template_choose').val();
                
            let table_pro = {};
            $("input:hidden[name='sms_deleted[]'][value='0']").each(function (i, obj) {
                let stt_sms     = $(this).attr('id').substring(11);
                var this_key    = $("input#key_sms"+stt_sms).val();
                let this_value  = $("input#value_sms"+stt_sms).val();

                if(this_key && this_value){
                    table_pro[this_key] = this_value;
                }
            })

            let button_pro = [];
            $("input:hidden[name='sms_button_deleted[]'][value='0']").each(function (i, obj) {
                let stt_button      = $(this).attr('id').substring(18);

                let type_button     = $("input#sms_button_" + stt_button).val();
                let title_button    = $("input#sms_button_" + stt_button).attr('title');
                let payload_button  = $("input#sms_button_" + stt_button).attr('data-payload');
                // let image_icon_button  = $("#image_icon_" + stt_button).find('img').attr('src');
                let image_icon_button  = '';

                if(title_button && type_button && payload_button){
                    button_pro.push({
                        'title': title_button,
                        'image_icon': image_icon_button,
                        'type': type_button,
                        'payload': payload_button
                    });
                }
            })

            closeDialogZaloZNS();
            $('.container-waiting').show();
            $.ajax({
                url: url_zalo_zns,
                type: "POST",
                data: {
                    action : "send_promotion",
                    zalo_id : zalo_id,
                    banner : banner_pro,
                    header : header_pro,
                    text : text_pro,
                    table : table_pro,
                    buttons : button_pro,
                    phone : phone,
                    parent_id : parent_id,
                    type_zns : type_zns,
                    template_type : template_type,
                },
                success: function (response) {
                    $('.container-waiting').hide();
    
                    let res_data = JSON.parse(response);
                    console.log(res_data);

                    if (res_data['code'] == 1) showModalNotify(1, res_data['message']);
                    else {
                        showModalNotify(0, res_data['message']);
                        console.log(response);
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $('.container-waiting').hide();
    
                    let text_modal_error = 'ERROR (' + errorThrown + '): Vui lòng liên hệ bộ phận IT.';
                    showModalNotify(0, text_modal_error)

                    console.error(XMLHttpRequest);
                    console.error("Status: " + textStatus);
                    console.error("Error: " + errorThrown);
                }
            });
        }
        else {
            // Validate
            let error = false;
            $('.zalo_field').each(function (i, obj) {
                let id = $(this).attr('id');
                let name = id.replaceAll("zalo_field_", "");
                let value = $(this).val();
                if ((value === undefined || value.length == 0) && name != "hanh_ly") {
                    $('#' + id).css("border-color", "red");
    
                    if (type_zns == "payment") alert('Vui lòng nhập thông tin hạn giữ chỗ');
                    else if (type_zns == "code-one-way" || type_zns == "code-round-trip") alert('Vui lòng nhập thông tin code vé');
                    else alert('Vui lòng nhập đầy đủ thông tin');
    
                    error = true;
                    return;
                }
    
                template_data[name] = value;
            });
            if (error) {
                e.preventDefault();
                return;
            }
    
            closeDialogZaloZNS();
            $('.container-waiting').show();
            $.ajax({
                url: url_zalo_zns,
                type: "POST",
                data: {
                    action : "send_zns",
                    phone : phone,
                    type_zns : type_zns,
                    parent_id : parent_id,
                    template_data : JSON.stringify(template_data)
                },
                success: function (response) {
                    $('.container-waiting').hide();
    
                    let res_data = JSON.parse(response);
                    if (res_data['code'] == 1) showModalNotify(1, res_data['message']);
                    else {
                        showModalNotify(0, res_data['message']);
                        console.log(response);
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $('.container-waiting').hide();
    
                    let text_modal_error = 'ERROR (' + errorThrown + '): Vui lòng liên hệ bộ phận IT.';
                    showModalNotify(0, text_modal_error)
    
                    console.error(XMLHttpRequest);
                    console.error("Status: " + textStatus);
                    console.error("Error: " + errorThrown);
                }
            });
        }
    });

    // ADD ROW SMS
    $(document).on("click","#btnAddRow_sms",function() {
		let ln = parseInt($('#sms_row_count').val());
		let ln_current = parseInt($('#sms_row_current').val());

        if(ln_current < 5){
            $('#last-row').before(insertRowSMS(ln));

            ln++;
            ln_current++;
            $('#sms_row_count').val(ln);
            $('#sms_row_current').val(ln_current);
        } else {
            alert('Tối đa 5 dòng');
        }
    })

    // ADD ROW button
    $(document).on("change","#type_button",function() {
        let ln              = parseInt($('#button__count').val());
		let ln_current      = parseInt($('#button__current').val());
		let value_button    = $(this).val();
		let type_button     = $('option:selected', this).attr('type-button');

        if(value_button.length == 0) return false;

        if(ln_current < 4){
            $("#list__button").append(insertButton(ln, value_button, type_button));

            ln++;
            ln_current++;
            $('#button__count').val(ln);
            $('#button__current').val(ln_current);
        } else {
            alert('Tối đa 4 nút');
        }
    })

    // Change the fullname
    $('#zalo_field_full_name').on('input', function () {
        let value = $(this).val().trim();
        $('#full_name_copy').html(value);
    });
});

function insertRowSMS(ln){
	let html = '';

    html = `<tr id="sms_line_${ln}">
                <td class="text-start"><input autocomplete="off" type="text" name="key_sms[]" id="key_sms${ln}" class="key_input box-input" title="key" maxlength="25" /></td>
                <td class="text-start"><input autocomplete="off" type="text" name="value_sms[]" id="value_sms${ln}" class="value_input box-input" title="value" maxlength="100" /></td>
                <td class="text-center">
                    <button class="button-search-in-edit remove_sms" title="Xóa sms" type="button" onclick="markSMSDeleted(${ln})">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#ec2029" class="bi bi-dash-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"></path><path d="M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8z"></path></svg>
                    </button>
                    <input type="hidden" value="0" name="sms_deleted[]" id="sms_deleted${ln}" />
                </td>
            </tr>`;

	return html;
}

function insertButton(ln, value_button, type_button){
	let html = '';
	let title_button = '';
	let payload_button = '';
	let image_icon_button = '';

    if(value_button == 'url_tcb'){
        title_button = 'Đặt vé ngay';
        payload_button = 'https://timchuyenbay.com';
    }
    else if(value_button == 'phone_callnow'){
        title_button = 'Hotline';
        payload_button = '1900636060';
    }
    else if(value_button == 'show_consultant'){
        title_button = 'Tư vấn';
        payload_button = 'Tôi cần hỗ trợ. Có ai có thể chat ngay bây giờ không?';
    } 
    // } else if(value_button == 'oa.query.hide'){
    //     title_button = 'Ẩn';
    //     payload_button = 'Cần hỗ trợ hide';

    html = `<div id="button_wrap_${ln}" class="mt-2 position-relative">
                <div class="sms_button--wrap">
                    <div class="sms_button--header">
                        <div class="image_icon" id="image_icon_${ln}">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 100 100" fill="#0068ff" width="22" height="22"><path d="M42.6494141,19.424408V13.105072c0-0.8286133,0.6713867-1.5,1.5-1.5s1.5,0.6713867,1.5,1.5v6.3193359  c0,0.8286133-0.6713867,1.5-1.5,1.5S42.6494141,20.2530212,42.6494141,19.424408z M52.2167969,23.7779236  c0.2050781,0.0957031,0.4208984,0.1411133,0.6328125,0.1411133c0.5644531,0,1.1054688-0.3208008,1.3603516-0.8666992  l2.6708984-5.7270508c0.3505859-0.7504883,0.0253906-1.6430664-0.7255859-1.9931641  c-0.7529297-0.3515625-1.6435547-0.0253906-1.9931641,0.7255859l-2.6708984,5.7270508  C51.140625,22.5352478,51.4658203,23.4278259,52.2167969,23.7779236z M34.0893555,23.0523376  c0.2543945,0.5458984,0.7954102,0.8666992,1.3603516,0.8666992c0.2124023,0,0.4277344-0.0454102,0.6328125-0.1411133  c0.7509766-0.3500977,1.0756836-1.2426758,0.7255859-1.9931641l-2.6708984-5.7270508  c-0.3500977-0.7509766-1.2416992-1.0742188-1.9931641-0.7255859c-0.7509766,0.3500977-1.0756836,1.2426758-0.7255859,1.9931641  L34.0893555,23.0523376z M70.9586792,49.7137756c-2.6220703-0.25-4.828125,1.8046875-4.828125,4.3754883v-1.8432617  c0-2.2963867-1.6787109-4.3466797-3.9645996-4.5649414c-2.6220703-0.25-4.8276367,1.8051758-4.8276367,4.3754883v-1.8432617  c0-2.2963867-1.6789551-4.3466797-3.9648438-4.5644531c-2.6218262-0.2495117-4.8271484,1.8051758-4.8276367,4.375v-18.043457  c0-2.2963867-1.6787109-4.3466797-3.9643555-4.5649414c-2.6220703-0.25-4.828125,1.8051758-4.828125,4.3754883v31.7626953  l-7.0732422-5.8632813c-1.7912598-1.4848633-4.4665527-1.5473633-6.1601563,0.0478516  c-1.9377441,1.8251953-1.8996582,4.8393555-0.0297852,6.6181641L40.437439,79.3075256  c0.4179688,0.4482422,0.7272949,0.9863281,0.9042969,1.5727539l1.409668,4.6704102  c0.5097656,1.6884766,2.0654297,2.8442383,3.8293457,2.8442383h20.8613281c1.9987793,0,3.6906738-1.4750977,3.9626465-3.4550781  l3.4995117-25.4487305c0.012207-0.090332,0.0185547-0.1816406,0.0185547-0.2724609v-4.940918  C74.9227905,51.981842,73.2440796,49.9320374,70.9586792,49.7137756z"></path></svg>
                        </div>
                        <span>${title_button}</span>
                    </div>
                    <div class="sms_button--footer">
                        <div class="chevron_icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/>
                            </svg>
                        </div>
                    </div>
                    <input type="hidden" value="${type_button}" title="${title_button}" data-payload="${payload_button}" class="btn btn-secondary w-auto" name="button_sms[]" id="sms_button_${ln}" />
                </div>
                <div class="remove_button">
                    <button class="button-search-in-edit w-10" title="Xóa button" type="button" onclick="markButtonDeleted(${ln})">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#ec2029" class="bi bi-dash-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"></path><path d="M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8z"></path></svg>
                    </button>
                    <input type="hidden" value="0" name="sms_button_deleted[]" id="sms_button_deleted${ln}" />
                </div>
            </div>
            `;

	return html;
}

function markSMSDeleted(ln){
	$('#sms_line_' + ln).hide();
	$('#sms_deleted' + ln).val(1);

	let arr = document.getElementsByName('sms_deleted[]');
	let tongsd = 0;
    for(let i = 0; i < arr.length; i++){
		if(arr[i].value == '0'){
			tongsd++;
		}
	}
	$('#sms_row_current').val(tongsd);
}

function markButtonDeleted(ln){
	$('#button_wrap_' + ln).hide();
	$('#sms_button_deleted' + ln).val(1);

    let arr = document.getElementsByName('sms_button_deleted[]');
	let tongsd = 0;
    for(let i = 0; i < arr.length; i++){
		if(arr[i].value == '0'){
			tongsd++;
		}
	}
	$('#button__current').val(tongsd);
}

function closeDialogZaloZNS() {
    // Reset
    $('#zalo-message').html('');
    $('input[name="zalo_type"]').prop('checked', false);

    dialog = document.getElementById("dialog-send-zalo");
    dialog.close();
}

function templatePromotion(index_template){
    let html = '', image = '', title = '', content = '';

    if(index_template == 'voucher_83') {
        image = 'include/images/templates/vouchers-8-3.jpg';
        title = '💝 ƯU ĐÃI KHỦNG 8/3 - NHẬN NGAY VOUCHER 83K 💝';
        content = 'Mừng ngày Quốc tế Phụ nữ, đặt vé máy bay nhận ngay VOUCHER 83K tại Vietjet (.net). Ngày 8/3 là dịp lý tưởng để bạn bày tỏ và thể hiện tình cảm dành cho người phụ nữ bên cạnh mình. Một chuyến du lịch xa thực sự sẽ là món quà vô cùng ý nghĩa trong dịp này.'
    }
    else if(index_template == 'voucher_30') {
        image = 'include/images/templates/vouchers-new.jpg';
        title = '⚡️CHÀO MỪNG KHÁCH HÀNG MỚI';
        content = 'Chỉ cần bấm quan tâm OA, nhận ngay voucher giảm giá 30K trực tiếp trên đơn. Chỉ thêm 2 lần đặt vé thành công nữa bạn sẽ trở thành thành viên của TCB với nhiều ưu đãi hấp dẫn.'
    }
    else if(index_template == 'voucher_50') {
        image = 'include/images/templates/vouchers-50.jpg';
        title = '⚡️ƯU ĐÃI HẤP DẪN DÀNH CHO THÀNH VIÊN CỦA TÌM CHUYẾN BAY';
        content = 'Bạn đã mua vé nhưng chưa trở thành thành viên của TCB? Nhấn quan tâm OA ngay để nhận voucher 50K cho lần đặt booking tiếp theo. Bạn sẽ nhận được quà tặng hấp dẫn khi đặt 5 booking trong tháng.'
    }
    else if(index_template == 'voucher_100') {
        image = 'include/images/templates/vouchers-2024.jpg';
        title = '⚡️ƯU ĐÃI CHÀO MỪNG NĂM MỚI 2024';
        content = 'Giảm giá dành cho khách hàng cũ đặt lại đơn đầu tiên trong năm 2024. Giảm trực tiếp 100K trên đơn cho hành trình bay khứ hồi. Đặc biệt, quan tâm OA để trở thành thành viên của TCB và nhận ưu đãi giảm giá 30K cho những lần đặt tiếp theo.'
    }
    else if(index_template == 'voucher_300') {
        image = 'include/images/templates/vouchers-3.jpg';
        title = '⚡️CHÀO THÁNG 3 - BAY THẢ GA KHÔNG LO VỀ GIÁ';
        content = 'Giảm giá trực tiếp trên đơn khi đặt nhóm từ 4 hành khách trở lên, thực hiện hành trình bay khứ hồi hoặc 8 khách cho hành trình bay 1 chiều. Đặc biệt khi đặt nhóm từ 10 khách sẽ được tặng 1 gói ký gửi 20KG miễn phí – Không quy đổi sang hình thức khác. Freeship vé khu vực nội thành HCM.';
    }
    else return '';

    html = `
            <div id="template_promotion_${index_template}" class="template_promotion template_promotion_${index_template} table-view table-promotion__sms mt-3" >
                <div class="template_promotion--banner">
                    <img src="${image}" alt="voucher" />
                </div>
                <div class="template_promotion--header px-2">
                    <h3>${title}</h3>
                </div>
                <div class="template_promotion--text my-2 px-2">${content}</div>  
                <div class="template_promotion--tablecontent">
                    <table class="tbl_zalosms table-details__booking">
                        <thead>
                            <tr>
                                <th>Tên</th>
                                <th>Giá trị</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr id="last-row" class="footer-tr">
                                <td colspan="4" class="text-start">
                                    <input type="button" class="btn btn-primary" id="btnAddRow_sms" value="Thêm dòng" title="Thêm dòng" />
                                    <input type="hidden" id="sms_row_count" value="0" />
                                    <input type="hidden" id="sms_row_current" value="0" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="template_promotion--button">
                    <div id="tbl_button_option_${index_template}" class="tbl_button_option table-details__booking">
                        <select name="type_button[]" id="type_button" class="m-2">
                            <option value="">---Thêm nút---</option>
                            <option value="url_tcb" type-button="oa.open.url">Đặt vé ngay (timchuyenbay.com)</option>
                            <option value="show_consultant" type-button="oa.query.show">Cần tư vấn</option>
                            <option value="phone_callnow" type-button="oa.open.phone">Hotline</option>
                            <!-- <option value="oa.query.hide">Ẩn</option> -->
                        </select>
                        <input type="hidden" id="button__count" value="0" />
                        <input type="hidden" id="button__current" value="0" />
                        <div id="list__button"></div>
                    </div>
                </div>
            </div>
        `;

    return html;
}
