// Click to open popup
$(document).on('click', 'input[name="btnSendSMS"]', function () {
    let direction 	 = $(this).attr('direction').trim();
    let flightno  	 = $(this).attr('flightno').trim();
    let journey   	 = $(this).attr('journey').trim();
    var date 		 = $(this).attr('date').trim();
    var time 		 = $(this).attr('time').trim();
    let applied_pass = $(this).attr('applied_pass').trim();

    if(applied_pass.length == 0) {
        alert("Hành trình này không còn khách nào áp dụng!");
        return false;
    }

    let passenger = '';
    let count_passenger = 0;
    let arr_applied_pass_id = applied_pass.split(",");
    $('#tbl_pax tr.psg-line').each(function () {
        if (arr_applied_pass_id.includes($(this).attr('data-id'))) {
            // if(passenger.length == 0) passenger += $(this).find('td:eq(4) .fullname').html().trim().toUpperCase();
            // else passenger += ', ' + $(this).find('td:eq(4) .fullname').html().trim().toUpperCase();
            if(passenger.length == 0) passenger += $(this).find('.passenger_name').text().trim().toUpperCase();
            else passenger += ', ' + $(this).find('.passenger_name').text().trim().toUpperCase();
            count_passenger++;
        }
    });
    if(passenger.length == 0) {
        arr_applied_pass_id.forEach(function(name) {
            if(name.length > 4 && name.length < 50 && name.includes(' ')) passenger += (passenger.length == 0) ? name : ', ' + name;
        });
    }
    if(passenger.length > 62) passenger = `so luong ${count_passenger} hanh khach`;
    
    // // Begin char counter
    // txtarea_obj = $('#sms_content');
    // $('#char-counter').text(txtarea_obj.val().length);
    // txtarea_obj.change(function () {
    //     $('#char-counter').text(txtarea_obj.val().length);
    // }).keyup(function () {
    //     $('#char-counter').text(txtarea_obj.val().length);
    // });
    // // End char counter

    // Reset dialog
    $('#dialog_send_sms .wrap-type input[name="sms_type"]').prop('checked', false);
    $('#dialog_send_sms #sms_content').val();
    $('#dialog_send_sms #sms_content_display').html('<i>Chưa có nội dung</i>');
    $('#dialog_send_sms').attr('direction', direction);
    $('#dialog_send_sms').attr('flightno', flightno);
    $('#dialog_send_sms').attr('journey', journey);
    $('#dialog_send_sms').attr('date', date);
    $('#dialog_send_sms').attr('time', time);
    $('#dialog_send_sms').attr('passenger', passenger);
    $('#dialog_send_sms').dialog({
        width: 680,
        modal: true,
        resizable: false,
        closeOnEscape: false,
    });
});

// const toTitleCase = (phrase) => {
// 	return phrase
// 		.toLowerCase()
// 		.split(' ')
// 		.map(word => word.charAt(0).toUpperCase() + word.slice(1))
// 		.join(' ');
// };

// Choose template SMS
$('input[type=radio][name=sms_type]').change(function () {
    render_template(this.value);
});
// Update template when change phone
$('input#send_sms_to').keyup(function() {
    let phone = $(this).val().trim();
    let type = $('input[name="sms_type"]:checked').val();
    if(phone.length > 9) {
        render_template(type);
    }
});

// Change template
$('#SmsTemplateList').on('change', function() {
    let value_display = $(this).val();
    if(value_display.length == 0) value_display = '<i>Chưa chọn nội dung</i>';
    let phone = $("input#send_sms_to").val().trim();
    let carrier = check_tele_carrier(phone);

    if(carrier == 'vinaphone'){
        $('#sms_content').val('Thanh toan:\\n'+$(this).val());
        $('#sms_content_display').html('Thanh toan:\\n'+ value_display);
    } else{
        $('#sms_content_display').html(value_display);
        $('#sms_content').val($(this).val());
    }
});

// Icon copy
$(document).on('click', '#copy-sms', function () {
    $('#sms_content_display input').each(function(){
        $(this).replaceWith($(this).val().trim());
    });
    let message = $('#sms_content_display').text().trim();

    // Copy the text inside the text field
    navigator.clipboard.writeText(message);

    showToastWarning("Đã sao chép nội dung");
});

// Send SMS
$(document).on('click', '#btn-confirm-send-sms', function () {
    let record 		= $(this).attr('booking_id');
    let direction 	= $('#dialog_send_sms').attr('direction');
    let phone 		= $('#send_sms_to').val().trim();

    $('#sms_content_display input').each(function(){
        $(this).replaceWith($(this).val().trim());
    });
    let message = $('#sms_content_display').text().trim();

    let phone_regex = /[\d]{9,20}/;
    if (phone == '' || !phone_regex.test(phone)) {
        let text_warning = 'Số điện thoại không hợp lệ!';
        showToastWarning(text_warning);
        $('#send_sms_to').focus();
        return false;
    }

    if (message.length == 0) {
        let text_warning = 'Nội dung tin nhắn không được trống!';
        showToastWarning(text_warning);
        $('##sms_content').focus();
        return false;
    }

    $.ajax({
        url: "index.php?entryPoint=entryPointSMS",
        data: {
            type : "send_sms",
            phone : phone,
            message : message,
            direction : direction,
            parent_id : record,
            parent_type : "EC_Flight_Bookings"
        },
        type: "POST",
        cache: false,
        beforeSend: function () {
            $('#btn-confirm-send-sms').attr('disabled', true);
        },
        success: function (json) {
            $('#btn-confirm-send-sms').attr('disabled', false);
            $('#dialog_send_sms').dialog('close');

            let response = JSON.parse(json);
            if(response && response.status == 1) {
                showModalNotify(1, "Đã gửi tin");
                $('.modal-overlay, .btn-modal-close').addClass('reload');
            }
            else showModalNotify(0, response.description);
        }
    });
});



function render_template(type) {
    if (type == 'send_sms_journey') {
        let journey     = $('#dialog_send_sms').attr('journey');
        let date 	    = $('#dialog_send_sms').attr('date');
        let time 	    = $('#dialog_send_sms').attr('time');
        let passenger   = $('#dialog_send_sms').attr('passenger');
        let booking     = $('#btn-confirm-send-sms').attr('booking_name');
        let source      = $('#btn-confirm-send-sms').attr('booking_source');
        let phone       = $('#send_sms_to').val().trim();
        let carrier     = check_tele_carrier(phone);
        let data = {
            source      : source,
            booking     : booking,
            passenger   : passenger,
            journey     : journey,
            date        : date,
            time        : time
        };
        let content = get_template(carrier, type, data);
        
        $('#sms_content_display').html(content);
        $('.tr-select-payment-templates').hide();
    }
    else if(type == 'send_sms_payment') {
        $('#sms_content_display').html('');
        $('#sms_content').val('');
        $("select#SmsTemplateList").val('').change();
        $('.tr-select-payment-templates').show();
    }
    else if(type == 'send_sms_code') {
        let journey 	= $('#dialog_send_sms').attr('journey');
        let date 		= $('#dialog_send_sms').attr('date');
        let time 		= $('#dialog_send_sms').attr('time');
        let flightno 	= $('#dialog_send_sms').attr('flightno');
        let source      = $('#btn-confirm-send-sms').attr('booking_source');
        let phone       = $('#send_sms_to').val().trim();
        let carrier     = check_tele_carrier(phone);
        let data = {
            source : source,
            flightno : flightno,
            journey : journey,
            date : date,
            time : time
        };
        let content = get_template(carrier, type, data);
        
        $('#sms_content_display').html(content);
        $('.tr-select-payment-templates').hide();
    }
    else if(type == 'send_sms_call') {
        let phone   = $('#send_sms_to').val().trim();
        let carrier = check_tele_carrier(phone);
        let content = get_template(carrier, type, '');

        $('#sms_content_display').html(content);
        $('.tr-select-payment-templates').hide();
    } 
}

function get_template(carrier, type, data) {
    // https://docs.google.com/document/d/15n0D1daW5Pnvz0Zz8nb6gA_iYawvVfp4-NB4A1Qa5ro
    if(carrier == 'mobifone') {
        if(type == 'send_sms_journey') {
            // Cam on ban dat ve tren [20 kí tự]. Booking [74 kí tự] ngay [10 kí tự] luc [5 kí tự]. HK [150 kí tự]. Vui long kiem tra ky cang thong tin tren
    
            let html_source    = `<input type="text" name="params_content_sms_source" value="${data.source}" class="box-input text-center" maxlength="20" size="15" />`;
            let html_booking   = `<input type="text" name="params_content_sms_booking" value="${data.booking}" class="box-input text-center" maxlength="14" size="12" />`;
            let html_journey   = `<input type="text" name="params_content_sms_journey" value="${data.journey}" class="box-input text-center" maxlength="60" size="30" />`;
            let html_passenger = `<input type="text" name="params_content_sms_passenger" value="${data.passenger}" class="box-input text-start" maxlength="150" size="60" />`;
            let html_date      = `<input type="text" name="params_content_sms_date" value="${data.date}" class="box-input text-center" maxlength="10" size="8" />`;
            let html_time      = `<input type="text" name="params_content_sms_time" value="${data.time}" class="box-input text-center" maxlength="5" size="4" />`;
    
            return `Cam on ban dat ve tren ${html_source}. Booking ${html_booking} ${html_journey} ngay ${html_date} luc ${html_time}. HK ${html_passenger}. Vui long kiem tra ky cang thong tin tren`;
        }
    
        if(type == 'send_sms_code') {
            // [20 kí tự] gui ban code ve: [8 kí tự]. Chuyen bay [90 kí tự] ngay [10 kí tự] luc [5 kí tự]. Vui long den san bay truoc [3 kí tự] phut
    
            let html_source     = `<input type="text" name="params_content_sms_source" value="${data.source}" class="box-input text-center" maxlength="20" size="15" />`;
            let html_code       = `<input type="text" name="params_content_sms_code" value="" class="box-input text-center" maxlength="8" size="6" />`;
            let html_flightno   = `<input type="text" name="params_content_sms_flightno" value="${data.flightno}" class="box-input text-center" maxlength="7" size="4" />`;
            let html_journey    = `<input type="text" name="params_content_sms_journey" value="${data.journey}" class="box-input text-center" maxlength="82" size="32" />`;
            let html_date       = `<input type="text" name="params_content_sms_date" value="${data.date}" class="box-input text-center" maxlength="10" size="8" />`;
            let html_time       = `<input type="text" name="params_content_sms_time" value="${data.time}" class="box-input text-center" maxlength="5" size="4" />`;
            let html_minute     = `<input type="text" name="params_content_sms_minute" value="90" class="box-input text-center" maxlength="3" size="2" />`;
    
            return `${html_source} gui ban code ve: ${html_code}. Chuyen bay ${html_flightno} ${html_journey} ngay ${html_date} luc ${html_time}. Vui long den san bay truoc ${html_minute} phut`;
        }

        if(type == 'send_sms_call') {
            // Quý khách vừa nhận cuộc gọi từ [30 kí tự] kênh đặt vé máy bay trực tuyến. Liên hệ đặt vé 24/7: [12 kí tự]

            let html_source = `<input type="text" class="box-input text-center" name="params_content_sms_source" value="Timchuyenbay.com" maxlength="30" size="15" />`;
            let html_phone = `<input type="text" class="box-input text-center" name="params_content_sms_phone" value="1900636060" maxlength="12" size="6" />`;

            return `Quý khách vừa nhận cuộc gọi từ ${html_source} kênh đặt vé máy bay trực tuyến. Liên hệ đặt vé 24/7: ${html_phone}`;
        }
    }
    else if(carrier == 'vinaphone') {
        if(type == 'send_sms_journey') {
            // Cam on ban dat ve tren [20 kí tự]. Booking [74 kí tự] ngay [10 kí tự] luc [5 kí tự]. HK [150 kí tự]. Vui long kiem tra ky cang thong tin tren
    
            let html_source    = `<input type="text" name="params_content_sms_source" value="${data.source}" class="box-input text-center" maxlength="20" size="15" />`;
            let html_booking   = `<input type="text" name="params_content_sms_booking" value="${data.booking}" class="box-input text-center" maxlength="14" size="12" />`;
            let html_journey   = `<input type="text" name="params_content_sms_journey" value="${data.journey}" class="box-input text-center" maxlength="60" size="30" />`;
            let html_passenger = `<input type="text" name="params_content_sms_passenger" value="${data.passenger}" class="box-input text-start" maxlength="150" size="60" />`;
            let html_date      = `<input type="text" name="params_content_sms_date" value="${data.date}" class="box-input text-center" maxlength="10" size="8" />`;
            let html_time      = `<input type="text" name="params_content_sms_time" value="${data.time}" class="box-input text-center" maxlength="5" size="4" />`;
    
            return `Cam on ban dat ve tren ${html_source}. Booking ${html_booking} ${html_journey} ngay ${html_date} luc ${html_time}. HK ${html_passenger}. Vui long kiem tra ky cang thong tin tren`;
        }
    
        if(type == 'send_sms_code') {
            // [20 kí tự] gui ban code ve: [8 kí tự]. Chuyen bay [90 kí tự] ngay [10 kí tự] luc [5 kí tự]. Vui long den san bay truoc [3 kí tự] phut
    
            let html_source     = `<input type="text" name="params_content_sms_source" value="${data.source}" class="box-input text-center" maxlength="20" size="15" />`;
            let html_code       = `<input type="text" name="params_content_sms_code" value="" class="box-input text-center" maxlength="8" size="6" />`;
            let html_flightno   = `<input type="text" name="params_content_sms_flightno" value="${data.flightno}" class="box-input text-center" maxlength="7" size="4" />`;
            let html_journey    = `<input type="text" name="params_content_sms_journey" value="${data.journey}" class="box-input text-center" maxlength="82" size="32" />`;
            let html_date       = `<input type="text" name="params_content_sms_date" value="${data.date}" class="box-input text-center" maxlength="10" size="8" />`;
            let html_time       = `<input type="text" name="params_content_sms_time" value="${data.time}" class="box-input text-center" maxlength="5" size="4" />`;
            let html_minute     = `<input type="text" name="params_content_sms_minute" value="90" class="box-input text-center" maxlength="3" size="2" />`;
    
            return `${html_source} gui ban code ve: ${html_code}. Chuyen bay ${html_flightno} ${html_journey} ngay ${html_date} luc ${html_time}. Vui long den san bay truoc ${html_minute} phut`;
        }

        if(type == 'send_sms_call') {
            // Quý khách vừa nhận cuộc gọi từ [30 kí tự] kênh đặt vé máy bay trực tuyến. Liên hệ đặt vé 24/7: [12 kí tự]

            let html_source = `<input type="text" class="box-input text-center" name="params_content_sms_source" value="Timchuyenbay.com" maxlength="30" size="15" />`;
            let html_phone = `<input type="text" class="box-input text-center" name="params_content_sms_phone" value="1900636060" maxlength="12" size="6" />`;

            return `Quý khách vừa nhận cuộc gọi từ ${html_source} kênh đặt vé máy bay trực tuyến. Liên hệ đặt vé 24/7: ${html_phone}`;
        }
        
        // if(type == 'send_sms_journey') {
        //     // Cam on ban dat ve tren{A,35}. Booking {A,65}. Hanh trinh{A,75}. Vui long kiem tra ky cang thong tin tren
    
        //     let html_source         = `<input type="text" class="box-input text-center" name="params_content_sms_source" value="${data.source}" maxlength="35" style="width:132px;" />`;
        //     let html_booking        = `<input type="text" class="box-input text-center" name="params_content_sms_booking" value="${data.booking}" maxlength="12" style="width:120px;" />`;
        //     let html_passenger      = `<input type="text" class="box-input text-start" name="params_content_sms_passenger" value="${data.passenger}" maxlength="48" style="width:514px;" />`;
        //     let html_journey        = `<input type="text" class="box-input text-center" name="params_content_sms_journey" value="${data.journey}" maxlength="48" style="width:210px;" />`;
        //     let html_date           = `<input type="text" class="box-input text-center" name="params_content_sms_date" value="${data.date}" maxlength="10" style="width:90px;" />`;
        //     let html_time           = `<input type="text" class="box-input text-center" name="params_content_sms_time" value="${data.time}" maxlength="5" style="width:56px;" />`;
    
        //     return `Hanh trinh:\\nCam on ban dat ve tren ${html_source}. Booking ${html_booking}, HK ${html_passenger}. Hanh trinh ${html_journey} ngay ${html_date} luc ${html_time}. Vui long kiem tra ky cang thong tin tren`;
        // }
    
        // if(type == 'send_sms_code') {
        //     // .{0,40} gui ban code ve:.{0,30}Chuyen bay .{0,90}Vui long den san bay truoc .{0,10} phut
    
        //     let html_source     = `<input type="text" class="box-input text-center" name="params_content_sms_source" value="“Tim chuyen bay”" readonly style="width:132px;" />`;
        //     let html_code       = `<input type="text" class="box-input text-center" name="params_content_sms_code" value="" maxlength="25" style="width:82px;" />`;
        //     let html_flightno   = `<input type="text" class="box-input text-center" name="params_content_sms_flightno" value="${data.flightno}" maxlength="7" style="width:75px;" />`;
        //     let html_journey    = `<input type="text" class="box-input text-center" name="params_content_sms_journey" value="${data.journey}" maxlength="64" style="width:210px;" />`;
        //     let html_date       = `<input type="text" class="box-input text-center" name="params_content_sms_date" value="${data.date}" maxlength="10" style="width:90px;" />`;
        //     let html_time       = `<input type="text" class="box-input text-center" name="params_content_sms_time" value="${data.time}" maxlength="5" style="width:56px;" />`;
        //     let html_minute     = `<input type="text" class="box-input text-center" name="params_content_sms_minute" value="90" maxlength="8" style="width:42px;" />`;
    
        //     return `Code ve:\\n${html_source} gui ban code ve ${html_code}. Chuyen bay ${html_flightno}, ${html_journey} ngay ${html_date} luc ${html_time}. Vui long den san bay truoc ${html_minute} phut`;
        // }

        // if(type == 'send_sms_call') {
        //     let html_phone = `<input type="text" class="box-input text-center" name="params_content_sms_phone" value="1900636060" maxlength="15" style="width:110px;" />`;

        //     return `Quý khách vừa nhận cuộc gọi từ Timchuyenbay.com kênh đặt vé máy bay trực tuyến.\\nLiên hệ đặt vé 24/7: ${html_phone}`;
        // }
    }
    // Viettel and others carrier
    else {
        if(type == 'send_sms_journey') {
            // Cam on ban dat ve tren [20 kí tự]. Booking [74 kí tự] ngay [10 kí tự] luc [5 kí tự]. HK [150 kí tự]. Vui long kiem tra ky cang thong tin tren
    
            let html_source    = `<input type="text" name="params_content_sms_source" value="${data.source}" class="box-input text-center" maxlength="20" size="15" />`;
            let html_booking   = `<input type="text" name="params_content_sms_booking" value="${data.booking}" class="box-input text-center" maxlength="14" size="12" />`;
            let html_journey   = `<input type="text" name="params_content_sms_journey" value="${data.journey}" class="box-input text-center" maxlength="60" size="30" />`;
            let html_passenger = `<input type="text" name="params_content_sms_passenger" value="${data.passenger}" class="box-input text-start" maxlength="150" size="60" />`;
            let html_date      = `<input type="text" name="params_content_sms_date" value="${data.date}" class="box-input text-center" maxlength="10" size="8" />`;
            let html_time      = `<input type="text" name="params_content_sms_time" value="${data.time}" class="box-input text-center" maxlength="5" size="4" />`;
    
            return `Cam on ban dat ve tren ${html_source}. Booking ${html_booking} ${html_journey} ngay ${html_date} luc ${html_time}. HK ${html_passenger}. Vui long kiem tra ky cang thong tin tren`;
        }
    
        if(type == 'send_sms_code') {
            // [20 kí tự] gui ban code ve: [8 kí tự]. Chuyen bay [90 kí tự] ngay [10 kí tự] luc [5 kí tự]. Vui long den san bay truoc [3 kí tự] phut
    
            let html_source     = `<input type="text" name="params_content_sms_source" value="${data.source}" class="box-input text-center" maxlength="20" size="15" />`;
            let html_code       = `<input type="text" name="params_content_sms_code" value="" class="box-input text-center" maxlength="8" size="6" />`;
            let html_flightno   = `<input type="text" name="params_content_sms_flightno" value="${data.flightno}" class="box-input text-center" maxlength="7" size="4" />`;
            let html_journey    = `<input type="text" name="params_content_sms_journey" value="${data.journey}" class="box-input text-center" maxlength="82" size="32" />`;
            let html_date       = `<input type="text" name="params_content_sms_date" value="${data.date}" class="box-input text-center" maxlength="10" size="8" />`;
            let html_time       = `<input type="text" name="params_content_sms_time" value="${data.time}" class="box-input text-center" maxlength="5" size="4" />`;
            let html_minute     = `<input type="text" name="params_content_sms_minute" value="90" class="box-input text-center" maxlength="3" size="2" />`;
    
            return `${html_source} gui ban code ve: ${html_code}. Chuyen bay ${html_flightno} ${html_journey} ngay ${html_date} luc ${html_time}. Vui long den san bay truoc ${html_minute} phut`;
        }

        if(type == 'send_sms_call') {
            // Quý khách vừa nhận cuộc gọi từ [30 kí tự] kênh đặt vé máy bay trực tuyến. Liên hệ đặt vé 24/7: [12 kí tự]

            let html_source = `<input type="text" class="box-input text-center" name="params_content_sms_source" value="Timchuyenbay.com" maxlength="30" size="15" />`;
            let html_phone = `<input type="text" class="box-input text-center" name="params_content_sms_phone" value="1900636060" maxlength="12" size="6" />`;

            return `Quý khách vừa nhận cuộc gọi từ ${html_source} kênh đặt vé máy bay trực tuyến. Liên hệ đặt vé 24/7: ${html_phone}`;
        }
    }

    return '';
}

function check_tele_carrier(phone) {
    let prefix = phone.replaceAll('+84', '0').slice(0, 3);

    let arr_viettel = ['032', '033', '034', '035', '036', '037', '038', '039', '096', '097', '098', '086'];
    let arr_mobifone = ['070', '079', '077', '076', '078', '090', '093', '089'];
    let arr_vinaphone = ['083', '084', '085', '087', '089', '091', '094', '088'];
    let arr_vietnamobile = ['092', '058', '056'];
    let arr_gmobile = ['099', '059'];

    if(arr_viettel.includes(prefix)) return 'viettel';
    else if(arr_mobifone.includes(prefix)) return 'mobifone';
    else if(arr_vinaphone.includes(prefix)) return 'vinaphone';
    else if(arr_vietnamobile.includes(prefix)) return 'vietnamobile';
    else if(arr_gmobile.includes(prefix)) return 'gmobile';
    return '';
}