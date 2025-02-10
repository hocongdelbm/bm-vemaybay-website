/********************   DECLARE   ********************/
const SIP_USER = document.getElementById('sip_user').value;
const SIP_PASSWORD = document.getElementById('sip_password').value;
const AGENT_STATUS = document.getElementById('agent_status').value || 'Available';
const CURRENT_USER = document.getElementById('sip_instance_id').value;

// const SIP_INSTANCE   = 'uuid:' + document.getElementById('sip_instance_id').value;
const SIP_DOMAIN = 'td.timchuyenbay.net';
const SIP_URI = `sip:${SIP_USER}@${SIP_DOMAIN}`;
const SIP_CONTACT = `sip:${SIP_USER}@${SIP_DOMAIN};transport=ws`;
const WS_SERVERS = `wss://${SIP_DOMAIN}:7444`;
const RINGTONE_FILE = 'ringtone.mp3';
const TITLE_PAGE = document.getElementsByTagName("title")[0].innerHTML;

/********************   CONFIG   ********************/
var ua_status = '';
var callLog = [];
var ua;
var session;
var configuration = {
    'uri': SIP_URI,
    'password': SIP_PASSWORD,
    'ws_servers': WS_SERVERS,
    'register': true,
    'session_timers': false,
    'register_expires': 120,
    'no_answer_timeout': 90,
    'display_name': 'Tim chuyen bay',
    'contact_uri': SIP_CONTACT,
    'log': true,
    // 'instance_id' : SIP_INSTANCE,
    // 'stun_servers': [{ urls: 'stun.cloudflare.com:3478' }],
};

// Register callbacks to desired call events (For debug)
let call_flow = '';
var eventHandlers = {
    'progress': function (e) {
        // console.warn('call is in progress');
        call_flow += 'Call is in progress. ';
    },
    'failed': function (e) {
        const errorCause = e.message?.data || e.cause;
        call_flow += `Call failed with cause: ${errorCause} `;

        if (errorCause && errorCause.includes('486 Busy Here')) {
            showModalNotify('warning', 'Số máy quý khách vừa gọi hiện đang bận và không thể nhận cuộc gọi. Vui lòng liên hệ lại sau!');
        } else if(errorCause && errorCause.includes('408 Request Timeout')){
            showModalNotify('warning', 'Lỗi kết nối mạng hoặc người nhận không phản hồi trong thời gian cho phép. Vui lòng liên hệ lại sau!');
        }
    },
    'ended': function (e) {
        // console.warn('call ended with cause:  ' + e.cause + ' ');
        call_flow += 'Call ended with cause: ' + e.cause + ' ';
    },
    'confirmed': function (e) {
        // console.warn('call confirmed');
        call_flow += 'Call confirmed. ';
    }
};

var callOptions = {
    'mediaConstraints': { 'audio': true, 'video': false },
    'sessionTimersExpires': 180, // Don't set a value lower than 90
    'eventHandlers': eventHandlers, // For debug
};

/***********   Setup audio and ringtone   *************/
var audio_jssip = document.getElementById("audio_jssip");
var incomingCallAudio = new window.Audio(RINGTONE_FILE);
incomingCallAudio.loop = true;

/********************   INIT   ********************/
JsSIP.debug.enable('JsSIP:*'); // More detailed debug output
// JsSIP.debug.disable('JsSIP:*');
// JsSIP.debug.enable('JsSIP:Transport JsSIP:RTCSession*');

socket = new JsSIP.WebSocketInterface(WS_SERVERS);
configuration.sockets = [socket];

if (ua) { ua.stop(); ua.unregister({ all: true }); ua = null; }
ua = new JsSIP.UA(configuration);
ua.start();

ua.on('registrationFailed', function (ev) {
    console.error('Lỗi đăng ký máy chủ SIP: ' + ev.cause);
    configuration.uri = null;
    configuration.password = null;
    showConnect(false);
    ua_status = 'registrationFailed';
});
ua.on('connecting', function (ev) {
    // console.warn('Connecting');
    ua_status = 'Connecting';
});
ua.on('connected', function (ev) {
    // console.warn('Connected');
    showConnect(true);
    ua_status = 'Connected';
});
ua.on('disconnected', function (ev) {
    // console.warn('Disconnected');
    showConnect(false);
    ua_status = 'disconnected';
});

/*************  CHECK ONLINE FOR CALL  *************/
setInterval(check_online_for_call, 90000);
function check_online_for_call() {
    // On calling not check online
    if (session) return;

    if (configuration.uri && configuration.password) {
        if (AGENT_STATUS == 'Available') {
            if ($('input#busy_stt').prop('checked') == true) {
                return;
            }

            if (!ua.isConnected()) ua.start();
            showConnect(true);
        } else {
            ua.stop();
            showConnect(false);
        }
    }
    else showConnect(false);
}

ua.on('newRTCSession', function (ev) {
    // When the previous call is existing
    if (session) {
        if (ev.session.direction === "incoming") {
            ev.session.terminate(); // End the call to forward
            ev.session = null;
        }
        incomingCallAudio.autoplay = false;
        incomingCallAudio.pause();
        return;
    }
    session = ev.session;

    /************  HANDLE OUTBOUND CALL  ************/
    session.on("confirmed", function () {
        $(document).prop('title', 'Đang gọi...');

        // Close notification
        closeNotification();

        // Change interface
        let name = $('#voiceip-info-name').html();
        let phone = $('#voiceip-info-phone').html();
        let zaloid = $('#voiceip-info-zaloid').html();
        if (name.length > 0) {
            $('#voiceip-name').val(name);
            // $('#voiceip-name').prop('readonly', true);
        } else $('#voiceip-name').val('');

        if (phone.length > 0) {
            $('#voiceip-phone').val(phone);
            $('#voiceip-phone').prop('readonly', true);
        } else $('#voiceip-phone').val('');

        if (zaloid.length > 0) {
            $('#voiceip-zalo-id').val(zaloid);
            $('#voiceip-zalo-id').prop('readonly', true);
        } else $('#voiceip-zalo-id').val('');

        handleButtons('processing');
        startTimer();

        // session.sendDTMF(4);
        $(document).on('click', '.calc-number', function () {
            let dtml_value = $("#display_dtmf").html().trim();
            let value_dtmf = $(this).attr('dtmf');

            dtml_value += value_dtmf;
            $("#display_dtmf").html(dtml_value);

            if (value_dtmf.length > 0) {
                if (session) {
                    let options = {
                        'duration': 160,
                        'interToneGap': 1200,
                    };
                    session.sendDTMF(value_dtmf, options);
                }
            }
        });

        $('#clear_dtmf').on('click', function () {
            let cur_dtml_value = $("#display_dtmf").html().trim();
            if (cur_dtml_value.length > 0) {
                cur_dtml_value = cur_dtml_value.slice(0, -1); // Xóa ký tự cuối cùng
                $('#display_dtmf').html(cur_dtml_value);
            }
        });

        // UDPATE TƯƠNG TÁC KHI CUỘC GỌI ĐANG DIỄN RA
        setInterval(function () {
            const currentTime = new Date(new Date().toString().split('GMT')[0] + ' UTC').toISOString().split('.')[0].replace('T', ' ');
            $.ajax({
                url: "index.php?entryPoint=entryPointUpdateTimeUserClick",
                type: "POST",
                cache: false,
                data: {
                    time: currentTime,
                    for: "saveLastClickUser",
                },
                success: function (response) { }
            });
        }, 90000);
    });

    /************  HANDLE INBOUND CALL  ************/
    if (session.direction === "incoming") {
        incomingCallAudio.pause();
        incomingCallAudio.currentTime = 0;
        incomingCallAudio.muted = false;
        incomingCallAudio.autoplay = true;

        // Get data
        let INVITE = session._request.data;
        let hotline = extract_hotline(INVITE);
        let call_id = extract_call_id(INVITE);
        let phone = session._request.from._uri._user.length < 12 ? session._request.from._uri._user : '';
        let zalo_id = session._request.from._uri._user.length > 18 ? session._request.from._uri._user : '';

        // Push nofitication 
        sendNotification(phone);

        // SPAM
        if (isSpamPhoneNumber(phone)) {
            session.terminate();
            return;
        }

        setTimeout(function () {
            incomingCallAudio.play();
        }, 100);
        $(document).prop('title', 'Có cuộc gọi đến...');
        showToastCall('incoming__call', call_id, zalo_id, phone, hotline)

        // ADD template-notes CHO cuộc gọi đến
        $('#template-notes').html(`
            <option value="in_journey">Khách hỏi hành trình</option>
            <option value="in_ticket_hunt">Nhu cầu săn vé máy bay</option>
            <option value="in_group_booking">Đặt vé đoàn nhiều người</option>
            <option value="in_complaint_delay">Phàn nàn sự cố delay</option>
            <option value="in_invoice_contact">Liên hệ kế toán hóa đơn</option>
            <option value="in_mistake">Nhầm lẫn, Lý Thông linh tinh</option>
            <option value="in_other">Khác, chưa định nghĩa</option>
        `)

        // Nghe máy
        session.on("accepted", function () {
            incomingCallAudio.autoplay = false;
            incomingCallAudio.pause();
            $(document).prop('title', 'Đang gọi...');
        });
    }

    /************  HANDLE ENDED  ************/
    // Kết thúc khi ĐÃ kết nối
    session.on("ended", function () {
        incomingCallAudio.autoplay = false;
        incomingCallAudio.pause();
        $(document).prop('title', TITLE_PAGE);

        // Close notification
        closeNotification();

        stopTimer();
        handleButtons('completed');

        $('.voiceip-content__client').slideDown();
        $('.calc-dtmf__wrap').slideUp();
        $(".voiceip-modal-transfer").hide();

        // SAVE LOG
        logCallEvent(session, SIP_USER, ua_status, call_flow);
        saveCallLog();
        call_flow = '';
        session = null;
    });

    /************  HANDLE FAILED  ************/
    // Kết thúc khi CHƯA kết nối
    session.on("failed", function (e) {
        incomingCallAudio.autoplay = false;
        incomingCallAudio.pause();

        if (session.direction === "incoming") {
            let INVITE = session._request.data;
            let call_id = extract_call_id(INVITE);
            let hotline = extract_hotline(INVITE);
            let phone = session._request.from._uri._user.length < 12 ? session._request.from._uri._user : '';
            let zalo_id = session._request.from._uri._user.length > 18 ? session._request.from._uri._user : '';

            setTimeout(function () {
                $.ajax({
                    url: "index.php?entryPoint=entryPointCallContact",
                    data: {
                        type: "check_missed_call",
                        call_id: call_id
                    },
                    type: "POST",
                    cache: false,
                    success: function (response) {
                        $('#popup-voiceip').removeClass('show');
                        $('#popup__voiceip--wrap').removeClass('show');

                        $('#call-overlay').removeClass('opened');
                        $(`.toast__main[type="incoming__call"][call_id="${call_id}"]`).parent().remove();

                        if (response == 1) showToastCall('missed__call', call_id, zalo_id, phone, hotline);
                    }
                });
            }, 1000);
        }
        else if (session.direction === "outgoing") {
            let call_id = session._request.call_id;
            let phone = session._request.to._uri._user.length < 12 ? session._request.to._uri._user : '';
            let zalo_id = session._request.to._uri._user.length > 18 ? session._request.to._uri._user : '';

            $('#popup-voiceip').removeClass('show');
            $('#popup__voiceip--wrap').removeClass('show');
            $('#call-overlay').removeClass('opened');
            showToastCall('decline__call', call_id, zalo_id, phone);
        }

        $(document).prop('title', TITLE_PAGE);

        // SAVE LOG
        logCallEvent(session, SIP_USER, ua_status, call_flow);
        saveCallLog();
        call_flow = '';
        session = null;
    });

    /************ OUTGOING CALL SOUND ************/
    if (session._connection && session.direction === "outgoing") {
        // ADD template-notes CHO cuộc gọi ĐI
        $('#template-notes').html(`
            <option value="out_no_need">Khách chưa có nhu cầu</option>
            <option value="out_interest">Đang quan tâm sơ bộ</option>
            <option value="out_no_response">Không nghe máy, bực mình</option>
        `)

        if (session._connection.addEventListener) {
            session._connection.addEventListener('track', (e) => {
                if (e.streams && e.streams[0]) {
                    audio_jssip.srcObject = e.streams[0];
                }
                else {
                    let stream = new MediaStream(e.track);
                    audio_jssip.srcObject = stream;
                    stream.addTrack(e.track);
                    alert("Lỗi âm thanh cuộc gọi");
                }
                audio_jssip.play();
            });
        }
        else {
            session._connection.ontrack = (e) => {
                if (e.streams && e.streams[0]) {
                    audio_jssip.srcObject = e.streams[0];
                }
                else {
                    let stream = new MediaStream(e.track);
                    audio_jssip.srcObject = stream;
                    stream.addTrack(e.track);
                    alert("Lỗi âm thanh cuộc gọi");
                }
                audio_jssip.play();
            };
        }
    } else if (session.direction === "outgoing") {
        alert("Lỗi kết nối");
    }

});

$(document).ready(function () {
    // Microphone permission 
    $(document).on('click', '#call-phone__circle', function () {
        if (!ua || !ua.isConnected() || !ua.isRegistered()) {
            showModalNotify('error', 'Không có kết nối. Vui lòng nhấn Online hoặc refresh trang và thử lại!');
            return false;
        } else {
            if (navigator.mediaDevices) {
                navigator.mediaDevices.getUserMedia({ audio: true, video: false })
                    .then(stream => {
                        $(".call-phone__numpad").toggle(200);
                    })
                    .catch(function (error) {
                        showModalNotify('error', 'Không có microphone hoặc quyền bị từ chối');
                        $('.call-phone__numpad').hide();
                    });
            } else {
                showModalNotify('warning', 'Trình duyệt không hỗ trợ navigator.mediaDevices');
                $('.call-phone__numpad').hide();
            }
        }
    });

    // Checked trạng thái bận của user
    if (AGENT_STATUS == 'Available') {
        showConnect(true);
    } else {
        showConnect(false);
        ua.stop();
    }

    // Checkbox busy
    $('input#busy_stt').change(function () {
        let status = 'Available';

        if ($(this).prop('checked') == true) {
            // status = 'Logged Out';
            status = 'On Break';
            showConnect(false);
            if (ua) ua.stop();
        } else {
            check_online_for_call();
            showConnect(true);
        }

        $.ajax({
            url: "index.php?entryPoint=entryPointUpdateTimeUserClick",
            data: {
                agent: SIP_USER,
                status: status,
                for: "changeStatusAgent"
            },
            type: "POST",
            cache: false,
            success: function (response) {
                console.log(response);
            }
        });
    });

    // Nút gọi đi - Phone
    $(document).on('click', '.btn-voiceip-calling', function () {
        let id = $(this).attr('id');
        let number = '', call_id = '';
        let booking_id = booking_name = type_call_booking = journey_id = '';
        let outbound_phone = $('#select-phone-outbound').val();

        // Gọi bằng numpad
        if (id == 'btn-voiceip-main-calling') {
            number = $('#call_voiceip_main_number').val().trim();
            $('#call_voiceip_main_number').val('');
        }
        else if (id == 'btnCalled' || id == 'btnRecall' || 'btnRemind') {
            number = $(this).attr('phone');
            booking_id = $(this).attr('booking_id');
            booking_name = $(this).attr('booking_name');
            type_call_booking = (id == 'btnCalled') ? 'called' : (id == 'btnRemind') ? 'remind' : 'recall';
            journey_id = $(this).attr('iti_id');
        } else if (id == 'listview-call_from' || id == 'listview-call_to') {
            number = $(this).attr('phone');
            type_call_booking = 'recall';
        }
        else if (id === undefined || id.length == '') {
            number = $(this).attr('call_to');
            call_id = $(this).attr('call_id');
        }

        if (number.length > 0 && number != SIP_USER) {
            callOptions.extraHeaders = ['X-Caller: ' + outbound_phone]

            resetPopupVoiceip();
            $('.call-phone__numpad').hide();

            // Map call to phone or zalo_id
            let phone = '', zalo_id = '';
            if (number.length < 15) {
                phone = number;
                $('#voiceip-info-phone').html(formatPhoneNumber(phone));
            }
            else {
                zalo_id = number;
                $('#voiceip-info-zaloid').attr('href', `https://zalo.me/${zalo_id}`);
                $('#voiceip-info-zaloid').closest('p').find('span').html('Zalo ID: ');
                $('#voiceip-info-zaloid').html(zalo_id);
            }

            $.ajax({
                url: "index.php?entryPoint=entryPointCallContact",
                data: {
                    type: "get_contact",
                    phone: phone,
                    zalo_id: zalo_id
                },
                type: "POST",
                cache: false,
                success: function (response) {
                    if (response.length > 0 && response != '[]') {
                        data = JSON.parse(response);

                        let contact_id = data.id;
                        let name = data.name;
                        let email = data.email;
                        let zaloid = (data.zalo_id && data.zalo_id.length > 0) ? data.zalo_id : zalo_id;
                        phone = (data.phone && data.phone.length > 0) ? data.phone : phone;
                        let avatar = data.avatar ? data.avatar.replace(/\\/g, "") : "";

                        let info_booking = data.info_booking;
                        let info_refund_ticket = data.info_refund_ticket;
                        let info_call = data.info_call;
                        let activity_contact = info_booking + info_refund_ticket + info_call;

                        $('#popup-inforbooking').html(activity_contact);
                        $('input[name="voiceip-contact-id"]').val(contact_id);
                        $('#voiceip-info-name').html(name);
                        if (email && email.length > 0) {
                            $('#voiceip-email').val(email);
                        }
                        $('#voiceip-info-phone').html(formatPhoneNumber(phone));
                        if (zaloid && zaloid.length > 0) {
                            $('#voiceip-info-zaloid').attr('href', `https://zalo.me/${zaloid}`);
                            $('#voiceip-info-zaloid').closest('p').find('span').html('Zalo ID: ');
                            $('#voiceip-info-zaloid').html(zaloid);
                            display_avatar_zalo(avatar);
                        }
                    }

                    // Make a call
                    if (!ua || !ua.isConnected() || !ua.isRegistered()) {
                        showModalNotify('warning', 'Không có kết nối');
                        return false;
                    }
                    ua.call(number, callOptions);

                    if (id == 'btnCalled' || id == 'btnRecall' || 'btnRemind') {
                        $('.voiceip-update').attr('booking_id', booking_id);
                        $('.voiceip-update').attr('booking_name', booking_name);
                        $('.voiceip-update').attr('type_call_booking', type_call_booking);
                        $('.voiceip-update').attr('journey_id', journey_id);
                    }

                    handleButtons('outgoing');
                    if (id === undefined || id.length == '') $(`.toast__main[type="missed__call"][call_id="${call_id}"]`).parent().remove();
                    $('.voiceip-header__title').html('Đang gọi...');
                    $('.voiceip-timer').hide();
                    $('#popup-voiceip').attr('call_id', session._request.call_id); // New call id
                    $('#popup-voiceip').addClass('show');
                    $('#popup__voiceip--wrap').addClass('show');
                    $('#call-overlay').addClass('opened');
                    return true;
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    console.error(XMLHttpRequest);
                    console.error("Status: " + textStatus);
                    console.error("Error: " + errorThrown);
                }
            });
        }
    });

    $(document).keyup(function (e) {
        if (e.keyCode === 13) {
            $('#btn-voiceip-main-calling').click();
        }
    });

    // Nút gọi đi - Zalo
    $(document).on('click', '.btn-voiceip-calling-zalo', function () {
        let id = $(this).attr('id');
        let number = '', call_id = '';
        let booking_id = booking_name = type_call_booking = '';
        let outbound_phone = $('#select-phone-outbound').val();

        // Gọi bằng numpad zalo
        if (id == 'btn-voiceip-main-zalo') {
            number = $('#call_voiceip_main_number').val().trim();
            $('#call_voiceip_main_number').val('');
        } else if (id == 'listview-call_from' || id == 'listview-call_to') {
            number = $(this).attr('phone');
        }
        else if (id == 'btnCalledZalo' || id == 'btnRecallZalo') {
            number = $(this).attr('phone');
            booking_id = $(this).attr('booking_id');
            booking_name = $(this).attr('booking_name');
            type_call_booking = (id == 'btnCalledZalo') ? 'called' : 'recall';
        }

        if (number.length < 15) {
            $('#voiceip-info-phone').html(formatPhoneNumber(number));
        }
        else {
            $('#voiceip-info-zaloid').attr('href', `https://zalo.me/${number}`);
            $('#voiceip-info-zaloid').closest('p').find('span').html('Zalo ID: ');
            $('#voiceip-info-zaloid').html(number);
        }

        if (number.length > 0 && number != SIP_USER) {
            callOptions.extraHeaders = ['X-Caller: ' + outbound_phone]

            resetPopupVoiceip();
            $('.call-phone__numpad').hide();

            $.ajax({
                url: "index.php?entryPoint=entryPointCallContact",
                data: {
                    type: "get_contact_zalo",
                    number: number // Phone or Zalo id
                },
                type: "POST",
                cache: false,
                success: function (response) {
                    let obj = JSON.parse(response);

                    if (obj.error == 0) {
                        let contact_id = obj.data.contact_id;
                        let name = obj.data.name;
                        let zaloid = obj.data.zalo_id;
                        let phone = obj.data.phone;
                        let email = obj.data.email;
                        let avatar = obj.data.avatar ? obj.data.avatar.replace(/\\/g, "") : "";

                        let info_booking = data.info_booking;
                        let info_refund_ticket = data.info_refund_ticket;
                        let info_call = data.info_call;
                        let activity_contact = info_booking + info_refund_ticket + info_call;

                        // Make a call
                        if (!ua || !ua.isConnected() || !ua.isRegistered()) {
                            showModalNotify('warning', 'Không có kết nối');
                            return false;
                        }
                        ua.call(zaloid, callOptions);

                        if (id == 'btnCalledZalo' || id == 'btnRecallZalo') {
                            $('.voiceip-update').attr('booking_id', booking_id);
                            $('.voiceip-update').attr('booking_name', booking_name);
                            $('.voiceip-update').attr('type_call_booking', type_call_booking);
                        }

                        $('#popup-inforbooking').html(activity_contact);
                        $('input[name="voiceip-contact-id"]').val(contact_id);
                        $('#voiceip-info-name').html(name);
                        $('#voiceip-info-phone').html(formatPhoneNumber(phone));
                        if (zaloid && zaloid.length > 0) {
                            $('#voiceip-info-zaloid').attr('href', `https://zalo.me/${zaloid}`);
                            $('#voiceip-info-zaloid').closest('p').find('span').html('Zalo ID: ');
                            $('#voiceip-info-zaloid').html(zaloid);
                            display_avatar_zalo(avatar);
                        }

                        if (email && email.length > 0) {
                            $('#voiceip-email').val(email);
                        }

                        handleButtons('outgoing');
                        if (id === undefined || id.length == '') $(`.toast__main[type="missed__call"][call_id="${call_id}"]`).parent().remove();
                        $('.voiceip-header__title').html('Đang gọi...');
                        $('.voiceip-timer').hide();
                        $('#popup-voiceip').attr('call_id', session._request.call_id); // New call id
                        $('#popup-voiceip').addClass('show');
                        $('#popup__voiceip--wrap').addClass('show');
                        $('#call-overlay').addClass('opened');
                        return true;
                    }
                    else {
                        showModalNotify('warning', obj.message);
                        $('.btn-modal-close').addClass('reload');
                        return false;
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    console.error(XMLHttpRequest);
                    console.error("Status Zalo: " + textStatus);
                    console.error("Error Zalo: " + errorThrown);
                }
            });
        }
    });

    // Nút nghe máy
    $(document).on('click', '.voiceip-accept', function () {
        session.answer(callOptions);
        if (session._connection.addEventListener) {
            session._connection.addEventListener('track', (e) => {
                audio_jssip.srcObject = e.streams[0];
                audio_jssip.play();
            });
        }
        else {
            session._connection.ontrack = (e) => {
                audio_jssip.srcObject = e.streams[0];
                audio_jssip.play();
            };
        }
    });

    // Nút từ chối
    $(document).on('click', '.voiceip-decline', function () {
        if (session) {
            session.terminate();
            session = null;
        }
        handleButtons('completed');
    });

    // Nút gác máy
    $(document).on('click', '.voiceip-end', function () {
        if (session) {
            session.terminate();
            session = null;
        }
        handleButtons('completed');
    });

    // Nút cập nhật thông tin sau khi gọi
    $(document).on('click', '.voiceip-update', function () {
        $(this).css("pointer-events", "none");
        let contact_id = $('input[name="voiceip-contact-id"]').val();
        let name = $('input[name="voiceip-name"]').val();
        let phone = $('input[name="voiceip-phone"]').val();
        let zalo_id = $('input[name="voiceip-zalo-id"]').val();
        let email = $('input[name="voiceip-email"]').val();
        let note = $('textarea[name="voiceip-notes"]').val();
        let call_id = $('#popup-voiceip').attr('call_id');
        let call_reason = $('#template-notes').val();

        let booking_id = $(this).attr('booking_id');
        let booking_name = $(this).attr('booking_name');
        let type_call_booking = $(this).attr('type_call_booking');
        let journey_id = $(this).attr('journey_id');

        let regEmailNew = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        // Checkbox
        let is_success = $('input#is_success').prop('checked');

        if (phone.length == 0 && zalo_id.length == 0) {
            showToastWarning('Vui lòng bổ sung SĐT');
            $(this).css("pointer-events", "");
            return false;
        }
        else if (name.length == 0) {
            showToastWarning('Vui lòng bổ sung tên liên hệ');
            $(this).css("pointer-events", "");
            return false;
        }
        else if (note.length < 15) {
            showToastWarning('Vui lòng thêm ghi chú rõ ràng cho cuộc gọi');
            $(this).css("pointer-events", "");
            return false;
        }
        else if (call_id.length == 0) {
            showToastWarning('Thiếu dữ liệu call_id, liên hệ IT');
            $(this).css("pointer-events", "");
            return false;
        }

        // Validate email
        if (email.length > 0 && !regEmailNew.test(email)) {
            alert('Email không hợp lệ');
            return false;
        }

        $.ajax({
            url: "index.php?entryPoint=entryPointCallContact",
            data: {
                type: "update_call",
                call_id: call_id,
                contact_id: contact_id,
                phone: phone,
                zalo_id: zalo_id,
                name: name,
                email: email,
                note: note,
                call_reason: call_reason,
                is_success: is_success,

                booking_id: booking_id,
                booking_name: booking_name,
                type_call_booking: type_call_booking,
                journey_id: journey_id
            },
            type: "POST",
            cache: false,
            beforeSend: function () {
                $('.container-waiting').show();
            },
            success: function (response) {
                $('.container-waiting').hide();

                if (parseInt(response) == 200) {
                    $(".voiceip-update").css("pointer-events", "");
                    $('#popup-voiceip').removeClass('show');
                    $('#popup__voiceip--wrap').removeClass('show');
                    $('#call-overlay').removeClass('opened');
                    showModalNotify('success', 'Cập nhật thông tin thành công.');
                    if (booking_id.length > 0) $('.btn-modal-close').addClass('reload');
                }
                else {
                    showModalNotify('error', 'Lỗi cập nhật. Vui lòng đợi 1 lát rồi thử lại!');
                    $(".voiceip-update").css("pointer-events", "");
                    return false;
                }
            }
        });
    });

    // Nút tắt âm
    $(document).on('click', '.voiceip-mute', function () {
        if (!session.isMuted().audio) {
            session.mute({ audio: true });
            $(this).addClass('voiceip-unmute');
            $(this).removeClass('voiceip-mute');
            $(this).find('.i-mute').hide();
            $(this).find('.i-unmute').show();
            $(this).find('.voiceip-button__desc').text("Mở âm");
        }
    });

    // Nút mở âm
    $(document).on('click', '.voiceip-unmute', function () {
        if (session.isMuted().audio) {
            session.unmute({ audio: true });
            $(this).addClass('voiceip-mute');
            $(this).removeClass('voiceip-unmute');
            $(this).find('.i-mute').show();
            $(this).find('.i-unmute').hide();
            $(this).find('.voiceip-button__desc').text("Tắt âm");
        }
    });

    $(document).on('click', '.load-data__transfer', function (event) {
        // LOAD LIST USER TRANSFER
        let data_user_transfer = [
            // KeToan
            ['0', { name: 'Đỗ Thị Kim Ngân', sip: '120' }],
            ['1', { name: 'Nguyễn Trang Đài', sip: '121' }],
            ['2', { name: 'Chung Thanh Nhân', sip: '122' }],
            ['3', { name: 'Bùi Thị Quỳnh Trang', sip: '123' }],
            ['4', { name: 'Thái Thị Yến Oanh', sip: '124' }],
            ['5', { name: 'Nguyễn Ngọc Thu', sip: '125' }],

            // Booker
            ['6', { name: 'Nguyễn Ngọc Lan Phương', sip: '101' }],
            ['7', { name: 'Nguyễn Thị Đông', sip: '102' }],
            ['8', { name: 'Đoàn Thị Kim Ly', sip: '103' }],
            ['9', { name: 'Trần Minh Tuấn', sip: '104' }],
            ['10', { name: 'Trần Như Điền', sip: '105' }],
            ['11', { name: 'Trương Mỹ Nhân', sip: '106' }],
            ['12', { name: 'Nguyễn Duy Đăng', sip: '108' }],
            ['13', { name: 'Nguyễn Lộc Danh', sip: '109' }],

            // Other
            ['14', { name: 'Phạm Chiến Thắng', sip: '203' }],
        ];

        let tranfer_html = '';
        data_user_transfer.forEach(([key, value]) => {
            let full_name = (value.name !== '') ? `${value.name}` : '';
            let number_sip = (value.sip !== '') ? `(${value.sip})` : '';

            tranfer_html += `<li class="transfer-item" data-sip="${value.sip}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a5 5 0 1 0 5 5 5 5 0 0 0-5-5zm0 8a3 3 0 1 1 3-3 3 3 0 0 1-3 3zm9 11v-1a7 7 0 0 0-7-7h-4a7 7 0 0 0-7 7v1h2v-1a5 5 0 0 1 5-5h4a5 5 0 0 1 5 5v1z"></path></svg>
                                <span class="value-name">${full_name} ${number_sip}</span>
                            </li>`;
        });

        $("#offer-transfer-list").addClass('active').html(tranfer_html);
    });

    // SELECT SIP
    $(document).on('click', '.transfer-item', function (event) {
        let value_sip = ($(this).attr('data-sip') != '') ? $(this).attr('data-sip') : '';
        if (value_sip == "") {
            showToastWarning('Số chuyển tiếp không xác định!');
            return false;
        }

        $('#transfer-phone').val(value_sip);
        $('#offer-transfer-list').removeClass('active');
    });

    // Nút transfer cuộc gọi
    $(document).on('click', '#transfer-submit', function (event) {
        let phone_transfer = $('#transfer-phone').val();
        if (phone_transfer == '') {
            showToastWarning('Số chuyển tiếp không xác định!');
            return false;
        }

        session.refer(phone_transfer)
    });

    // Nút Bàn phím dtmf
    $(document).on('click', '.voiceip-dtmf', function (event) {
        if ($('.voiceip-content__client').is(':visible')) {
            $('.voiceip-content__client').slideUp();
            $('.calc-dtmf__wrap').slideDown();
        } else {
            $('.voiceip-content__client').slideDown();
            $('.calc-dtmf__wrap').slideUp();
        }
    });

    // Stop and unregister ua when reload
    $(window).on('beforeunload', function () {
        if (ua) { ua.stop(); ua.unregister({ all: true }); ua = null; }
    });

    // Chỉ cho phép nhập số
    $('#call_voiceip_main_number').on('input', function () {
        var inputValue = $(this).val();
        var numericValue = inputValue.replace(/[^0-9S]/g, '');
        $(this).val(numericValue);
    });

    // Mở popup cuộc gọi
    $(document).on('click', '.toast__main', function () {
        resetPopupVoiceip();
        let type = $(this).attr('type');
        let call_id = $(this).attr('call_id');

        if (type == 'incoming__call') {
            let arg_phone = $(this).attr('phone');
            let arg_zaloid = $(this).attr('zalo_id');
            let switchboard = $(this).find('.switchboard').attr('data');

            $.ajax({
                url: "index.php?entryPoint=entryPointCallContact",
                data: {
                    type: "get_contact",
                    phone: arg_phone,
                    zalo_id: arg_zaloid
                },
                type: "POST",
                cache: false,
                success: function (response) {
                    if (response.length > 0 && response != '[]') {
                        data = JSON.parse(response);
                        let contact_id = data.contact_id;
                        let name = data.name;
                        let phone = arg_phone.length > 0 ? arg_phone : data.phone;
                        let zaloid = arg_zaloid.length > 0 ? arg_zaloid : data.zalo_id;
                        let email = data.email;
                        let avatar = data.avatar ? data.avatar.replace(/\\/g, "") : "";

                        let info_booking = data.info_booking;
                        let info_refund_ticket = data.info_refund_ticket;
                        let info_call = data.info_call;
                        let activity_contact = info_booking + info_refund_ticket + info_call;

                        $('#popup-inforbooking').html(activity_contact);
                        $('input[name="voiceip-contact-id"]').val(contact_id);
                        $('#voiceip-info-name').html(name);
                        $('#voiceip-info-phone').html(formatPhoneNumber(phone));

                        if (zaloid.length > 0) {
                            $('#voiceip-info-zaloid').attr('href', `https://zalo.me/${zaloid}`);
                            $('#voiceip-info-zaloid').closest('p').find('span').html('Zalo ID: ');
                            $('#voiceip-info-zaloid').html(zaloid);
                            display_avatar_zalo(avatar);
                        }

                        if (email && email.length > 0) {
                            $('#voiceip-email').val(email);
                        }
                    }
                    else {
                        $('#voiceip-info-phone').html(formatPhoneNumber(arg_phone));
                        if (arg_zaloid.length > 0) {
                            $('#voiceip-info-zaloid').attr('href', `https://zalo.me/${arg_zaloid}`);
                            $('#voiceip-info-zaloid').closest('p').find('span').html('Zalo ID: ');
                            $('#voiceip-info-zaloid').html(arg_zaloid);
                        }
                    }

                    handleButtons('incomming');
                    $('#popup-voiceip .voiceip-header__title').html(switchboard);
                    $('#popup-voiceip').attr('call_id', call_id);
                    $('#popup__voiceip--wrap').addClass('show');
                    $('#popup-voiceip').addClass('show');
                    $('#call-overlay').addClass('opened');
                }
            });

            $(this).parent().remove();
        }
    });

    // Xem chi tiết booking của sdt đó
    $(document).on('click', '.voiceip-viewbooking', function () {
        $("#popup-inforbooking").toggle("slide");
    });


});

// CALL LOG ===============================
// -------------------------------------------------
// -------------------------------------------------
function saveCallLog() {
    var fullLog = callLog.join('\n');

    $.ajax({
        url: "index.php?entryPoint=entryPointCallContact",
        data: {
            type: "save_log_call",
            log: fullLog,
        },
        type: "POST",
        cache: false,
        success: function (response) {

        }
    });
    // console.warn(fullLog);
    callLog = [];
}

function logCallEvent(session, ua, status, event = '') {
    // INFOR CALL
    // console.warn(session);
    let call_id = '';
    let direction = session.direction || '';
    if (direction === "incoming") {
        let INVITE = session._request.data;
        call_id = extract_call_id(INVITE);
    }
    else if (direction === "outgoing") {
        call_id = session._request.call_id;
    }
    let call_from = session._request.from._uri._user || '';
    let call_to = session._request.to._uri._user || '';

    var timestamp = getCurrentTimestamp();
    var logEntry = `[${timestamp}][${ua}][${status}]:[${direction}][${call_from}][${call_to}][${call_id}] ${event}`;
    callLog.push(logEntry);
}

function getCurrentTimestamp() {
    var currentDate = new Date();
    currentDate.setHours(currentDate.getHours() + 7);
    return currentDate.toISOString().replace('T', ' ').split('.')[0];
}

// PUSH NOTIFICATION ===============================
// -------------------------------------------------
// -------------------------------------------------

// Register a service worker
const check_support = () => {
    if (!('serviceWorker' in navigator)) {
        // throw new Error('No Service Worker support!');
        console.warn('No Service Worker support!')
    }
    if (!('PushManager' in window)) {
        console.warn('No Push API Support!')
        // throw new Error('No Push API Support!')
    }
}

const registerServiceWorker = async () => {
    const existingRegistration = await navigator.serviceWorker.getRegistration();
    if (existingRegistration) {
        await existingRegistration.unregister();
    }

    const swRegistration = await navigator.serviceWorker.register('service-worker.js?v=' + Date.now() + '');
    return swRegistration;
}

// Permission Micro
if (navigator.mediaDevices) {
    navigator.mediaDevices.getUserMedia({ audio: true, video: false });
}

// Permission Notify
const requestNotificationPermission = async () => {
    const permission = await window.Notification.requestPermission()
    if (permission !== 'granted') {
        // throw new Error('Permission not granted for Notification')
        console.warn('Permission not granted for Notification');
    }
}
requestNotificationPermission()

const sendNotification = (phone) => {
    if (navigator.serviceWorker.controller) {
        navigator.serviceWorker.controller.postMessage({ type: 'show_notification', phone });
    } else {
        console.warn('No active Service Worker controller found.');
    }
};

const closeNotification = () => {
    if (navigator.serviceWorker.controller) {
        navigator.serviceWorker.controller.postMessage({ type: 'close_notification' });
    } else {
        console.warn('No active Service Worker controller found.');
    }
};


// Khởi tạo service worker và xin quyền thông báo
const init = async () => {
    try {
        check_support();
        swRegistration = await registerServiceWorker();
        await requestNotificationPermission();

    } catch (error) {
        console.error('Error in initialization:', error);
    }
};

// Khởi tạo service worker khi trang được tải
window.addEventListener('load', () => {
    init();
});

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.addEventListener('message', function (event) {
        if (event.data.type === 'ACCEPT_CALL') {
            let INVITE = session._request.data;
            let call_id = extract_call_id(INVITE);
            let call_from = session._request.from._uri._user.length < 12 ? session._request.from._uri._user : '';
            let zalo_id = session._request.from._uri._user.length > 18 ? session._request.from._uri._user : '';

            resetPopupVoiceip();
            $.ajax({
                url: "index.php?entryPoint=entryPointCallContact",
                data: {
                    type: "get_contact",
                    phone: call_from,
                    zalo_id: zalo_id
                },
                type: "POST",
                cache: false,
                success: function (response) {
                    if (response.length > 0 && response != '[]') {
                        data = JSON.parse(response);
                        let contact_id = data.contact_id;
                        let name = data.name;
                        let phone = call_from.length > 0 ? call_from : data.phone;
                        let zaloid = zalo_id.length > 0 ? zalo_id : data.zalo_id;
                        let email = data.email;
                        let avatar = data.avatar ? data.avatar.replace(/\\/g, "") : "";

                        let info_booking = data.info_booking;
                        let info_refund_ticket = data.info_refund_ticket;
                        let info_call = data.info_call;
                        let activity_contact = info_booking + info_refund_ticket + info_call;

                        $('#popup-inforbooking').html(activity_contact);
                        $('input[name="voiceip-contact-id"]').val(contact_id);
                        $('#voiceip-info-name').html(name);
                        $('#voiceip-info-phone').html(formatPhoneNumber(phone));

                        $('#voiceip-name').val(name);
                        $('#voiceip-phone').val(phone);

                        if (zaloid.length > 0) {
                            $('#voiceip-info-zaloid').attr('href', `https://zalo.me/${zaloid}`);
                            $('#voiceip-info-zaloid').closest('p').find('span').html('Zalo ID: ');
                            $('#voiceip-info-zaloid').html(zaloid);
                            display_avatar_zalo(avatar);
                        }

                        if (email && email.length > 0) {
                            $('#voiceip-email').val(email);
                        }
                    }
                    else {
                        $('#voiceip-info-phone').html(formatPhoneNumber(call_from));
                        if (zalo_id.length > 0) {
                            $('#voiceip-info-zaloid').attr('href', `https://zalo.me/${zalo_id}`);
                            $('#voiceip-info-zaloid').closest('p').find('span').html('Zalo ID: ');
                            $('#voiceip-info-zaloid').html(zalo_id);
                        }
                    }

                    handleButtons('processing');
                    $('#popup-voiceip').attr('call_id', call_id); // New call id
                    $('#popup__voiceip--wrap').addClass('show');
                    $('#popup-voiceip').addClass('show');
                    $('#call-overlay').addClass('opened');
                    $("#" + event.data.id).remove();
                }
            });

            session.answer(callOptions);
            if (session._connection.addEventListener) {
                session._connection.addEventListener('track', (e) => {
                    audio_jssip.srcObject = e.streams[0];
                    audio_jssip.play();
                });
            }
            else {
                session._connection.ontrack = (e) => {
                    audio_jssip.srcObject = e.streams[0];
                    audio_jssip.play();
                };
            }
        } else if (event.data.type === 'REJECT_CALL') {
            if (session) {
                session.terminate();
                session = null;

                incomingCallAudio.autoplay = false;
                incomingCallAudio.pause();
                return;
            } else {
                console.warn('session not exist')
            }
        }
    });
}
// -- END =========================
// --------------------------------

/********************   FUNCTIONS HANDLE   ********************/
function toast({ call_id = "", zalo_id = "", phone = "", type = "", hotline = "" }) {
    const main = document.getElementById("toast-incoming");

    let title = '', time = '', icon = '', action = ''
    let call_from = zalo_id.length > 0 ? zalo_id : phone;
    let call_from_display = '<span>' + call_from + '</span>';

    if (zalo_id.length > 0) {
        if (type == "incoming__call")
            call_from_display = `<span style="position:absolute; left:21px; top:60%; transform:translateY(-60%); z-index:10">
                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="23" height="23" viewBox="0 0 48 48"> <path fill="#2962ff" d="M15,36V6.827l-1.211-0.811C8.64,8.083,5,13.112,5,19v10c0,7.732,6.268,14,14,14h10	c4.722,0,8.883-2.348,11.417-5.931V36H15z"></path><path fill="#eee" d="M29,5H19c-1.845,0-3.601,0.366-5.214,1.014C10.453,9.25,8,14.528,8,19	c0,6.771,0.936,10.735,3.712,14.607c0.216,0.301,0.357,0.653,0.376,1.022c0.043,0.835-0.129,2.365-1.634,3.742	c-0.162,0.148-0.059,0.419,0.16,0.428c0.942,0.041,2.843-0.014,4.797-0.877c0.557-0.246,1.191-0.203,1.729,0.083	C20.453,39.764,24.333,40,28,40c4.676,0,9.339-1.04,12.417-2.916C42.038,34.799,43,32.014,43,29V19C43,11.268,36.732,5,29,5z"></path><path fill="#2962ff" d="M36.75,27C34.683,27,33,25.317,33,23.25s1.683-3.75,3.75-3.75s3.75,1.683,3.75,3.75	S38.817,27,36.75,27z M36.75,21c-1.24,0-2.25,1.01-2.25,2.25s1.01,2.25,2.25,2.25S39,24.49,39,23.25S37.99,21,36.75,21z"></path><path fill="#2962ff" d="M31.5,27h-1c-0.276,0-0.5-0.224-0.5-0.5V18h1.5V27z"></path><path fill="#2962ff" d="M27,19.75v0.519c-0.629-0.476-1.403-0.769-2.25-0.769c-2.067,0-3.75,1.683-3.75,3.75	S22.683,27,24.75,27c0.847,0,1.621-0.293,2.25-0.769V26.5c0,0.276,0.224,0.5,0.5,0.5h1v-7.25H27z M24.75,25.5	c-1.24,0-2.25-1.01-2.25-2.25S23.51,21,24.75,21S27,22.01,27,23.25S25.99,25.5,24.75,25.5z"></path><path fill="#2962ff" d="M21.25,18h-8v1.5h5.321L13,26h0.026c-0.163,0.211-0.276,0.463-0.276,0.75V27h7.5	c0.276,0,0.5-0.224,0.5-0.5v-1h-5.321L21,19h-0.026c0.163-0.211,0.276-0.463,0.276-0.75V18z"></path> </svg>
            </span>`;
        else {
            call_from_display = `<a href="https://zalo.me/${call_from}" target="_blank" style="color:#008fe5; text-decoration:underline;">${call_from}</a>`;
        }
    }

    if (type == "incoming__call") {
        title = "Cuộc gọi đến";
        let icon_color = '#14a866';
        icon = `<svg fill="${icon_color}" width="32px" height="32px" viewBox="0 0 56 56" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M 32.5233 25.3867 L 46.1405 25.3867 C 47.1014 25.3867 47.8514 24.6133 47.8046 23.6524 C 47.7577 22.7383 47.0546 22.0586 46.1405 22.0586 L 42.5780 22.0586 L 35.6874 22.4336 L 39.6249 18.8477 L 49.7266 8.7227 C 50.1015 8.3477 50.2892 7.9258 50.2892 7.4336 C 50.2892 6.5195 49.5154 5.7930 48.5309 5.7930 C 48.0624 5.7930 47.6639 5.9570 47.3124 6.3086 L 37.2343 16.4336 L 33.6249 20.3242 L 34.0233 13.4570 L 34.0233 9.9648 C 34.0233 9.0508 33.3202 8.3477 32.4061 8.3008 C 31.4686 8.2539 30.6718 9.0039 30.6718 9.9648 L 30.6718 23.5352 C 30.6718 24.7774 31.2812 25.3867 32.5233 25.3867 Z M 17.5936 38.2070 C 24.3671 44.9805 32.6171 50.2070 39.3436 50.2070 C 42.3671 50.2070 45.0155 49.1524 47.1483 46.8086 C 48.3903 45.4258 49.1640 43.8086 49.1640 42.2149 C 49.1640 41.0430 48.7186 39.9180 47.5936 39.1211 L 40.4218 34.0117 C 39.3202 33.2617 38.4061 32.8867 37.5624 32.8867 C 36.4843 32.8867 35.5468 33.4961 34.4686 34.5508 L 32.8046 36.1914 C 32.5468 36.4492 32.2186 36.5664 31.9139 36.5664 C 31.5390 36.5664 31.2108 36.4258 30.9530 36.3086 C 29.5233 35.5352 27.0390 33.4024 24.7186 31.1055 C 22.4218 28.8086 20.2890 26.3242 19.5390 24.8711 C 19.3983 24.6133 19.2812 24.2852 19.2812 23.9336 C 19.2812 23.6289 19.3749 23.3242 19.6327 23.0664 L 21.2733 21.3555 C 22.3280 20.2774 22.9374 19.3399 22.9374 18.2617 C 22.9374 17.4180 22.5624 16.5039 21.7890 15.4024 L 16.7499 8.3008 C 15.9296 7.1758 14.7812 6.6836 13.5155 6.6836 C 11.9686 6.6836 10.3514 7.3867 8.9921 8.7227 C 6.7186 10.9024 5.7108 13.5977 5.7108 16.5742 C 5.7108 23.3008 10.8436 31.4570 17.5936 38.2070 Z"></path></g></svg>`;
    }
    else if (type == "missed__call") {
        title = "Cuộc gọi nhỡ";
        let icon_color = '#ff623d';
        icon = `<svg fill="${icon_color}" width="32px" height="32px" viewBox="0 0 24.00 24.00" xmlns="http://www.w3.org/2000/svg" stroke="${icon_color}" stroke-width="0.00024000000000000003"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" d="M14.4267305,17.3239887 C13.8091494,17.1184418 12.9237465,17.0002 11.9967657,17 C11.0703845,16.9998001 10.1850309,17.1179592 9.56735459,17.323626 C9.29781857,17.4133731 9.10254274,17.5124903 8.99818889,17.5994146 L8.99818886,18.4997844 C8.99830883,19.0560874 8.98526108,19.3378275 8.91482312,19.6766528 C8.76529679,20.3959143 8.36503921,20.9530303 7.5979407,20.9971778 C5.57992549,21.3324217 4.23196922,21.5 3.49954722,21.5 C2.04222339,21.5 1,20.1968274 1,19 L1,17.5 C1,13.7761071 6.02664974,10.9987117 11.9971973,11 C17.9690798,11.0012886 22.993963,13.7768824 22.9935942,17.4728433 C22.9981103,17.6390833 23.0000363,17.8114009 22.9999995,18.0054528 C22.9999727,18.1468201 22.9992073,18.2587316 22.9969405,18.5090552 C22.9947039,18.7560368 22.993963,18.8651358 22.993963,19 C22.993963,20.1895648 21.9503425,21.5 20.4944157,21.5 C19.7626874,21.5 18.4165903,21.332739 16.4017544,20.9981299 C15.3495506,20.9554142 15.0603932,20.1844357 15.0052983,19.044091 C14.9974219,18.8810653 14.9958289,18.7545264 14.9957743,18.5011312 C14.9956956,17.9832104 14.9956891,17.9405386 14.9957547,17.5995238 C14.8913892,17.5126847 14.6961745,17.4136666 14.4267305,17.3239887 Z M6.99818889,18.5 L6.99818889,17.5 C6.99818889,15.7340787 9.20464625,14.9993975 11.9971973,15 C14.7913808,15.0006029 16.9957741,15.7342819 16.9957741,17.5 C16.9956885,17.9366661 16.9956885,17.9366661 16.995774,18.4997844 C16.995822,18.7225055 16.9971357,18.8268559 17.0029681,18.9475751 C17.0051195,18.992103 17.0078746,19.0335402 17.0110607,19.0715206 C18.7614943,19.3571487 19.9381265,19.5 20.4944157,19.5 C20.7329265,19.5 20.993963,19.1722263 20.993963,19 C20.993963,18.8570865 20.9947313,18.7439632 20.9970225,18.4909448 C20.9992358,18.2465315 20.9999742,18.1385601 20.9999995,18.0050735 C21.0000331,17.8280282 20.998305,17.6734088 20.993963,17.5 C20.993963,15.2010869 17.0111151,13.001082 11.9967657,13 C6.98400975,12.9989183 3,15.2002196 3,17.5 L3,19 C3,19.1781726 3.2573842,19.5 3.49954722,19.5 C4.05591217,19.5 5.23278898,19.3571098 6.98361703,19.071404 C6.99451507,18.9374564 6.99824508,18.76066 6.99818889,18.5 Z M7,5.41421356 L7,8 L5,8 L5,2 L11,2 L11,4 L8.41421356,4 L12,7.58578644 L17.2928932,2.29289322 L18.7071068,3.70710678 L12,10.4142136 L7,5.41421356 Z"></path> </g></svg>`;
        time = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="16" fill="currentColor" class="bi bi-dash-lg" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8Z"/>
                </svg>
                <i>Lúc ${getCurrentTime()}</i>`;
        action = `<button class="btn-voiceip-calling mt-1" call_id="${call_id}" call_to="${call_from}"> 
            <svg width="25px" height="25px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#14a866"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M16.5562 12.9062L16.1007 13.359C16.1007 13.359 15.0181 14.4355 12.0631 11.4972C9.10812 8.55901 10.1907 7.48257 10.1907 7.48257L10.4775 7.19738C11.1841 6.49484 11.2507 5.36691 10.6342 4.54348L9.37326 2.85908C8.61028 1.83992 7.13596 1.70529 6.26145 2.57483L4.69185 4.13552C4.25823 4.56668 3.96765 5.12559 4.00289 5.74561C4.09304 7.33182 4.81071 10.7447 8.81536 14.7266C13.0621 18.9492 17.0468 19.117 18.6763 18.9651C19.1917 18.9171 19.6399 18.6546 20.0011 18.2954L21.4217 16.883C22.3806 15.9295 22.1102 14.2949 20.8833 13.628L18.9728 12.5894C18.1672 12.1515 17.1858 12.2801 16.5562 12.9062Z" fill="#14a866"></path> </g></svg>
        </button>`;
    }
    else if (type == "decline__call") {
        title = "Cuộc gọi đã kết thúc";
        let icon_color = '#ec2029';
        icon = `<svg width="30px" height="30px" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="${icon_color}" stroke="${icon_color}"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round" stroke="#CCCCCC" stroke-width="0.43200000000000005"></g><g id="SVGRepo_iconCarrier"> <title>phone_block_fill</title> <g id="页面-1" stroke-width="0.00024000000000000003" fill="none" fill-rule="evenodd"> <g id="Contact" transform="translate(-528.000000, -48.000000)"> <g id="phone_block_fill" transform="translate(528.000000, 48.000000)"> <path d="M24,0 L24,24 L0,24 L0,0 L24,0 Z M12.5934901,23.257841 L12.5819402,23.2595131 L12.5108777,23.2950439 L12.4918791,23.2987469 L12.4918791,23.2987469 L12.4767152,23.2950439 L12.4056548,23.2595131 C12.3958229,23.2563662 12.3870493,23.2590235 12.3821421,23.2649074 L12.3780323,23.275831 L12.360941,23.7031097 L12.3658947,23.7234994 L12.3769048,23.7357139 L12.4804777,23.8096931 L12.4953491,23.8136134 L12.4953491,23.8136134 L12.5071152,23.8096931 L12.6106902,23.7357139 L12.6232938,23.7196733 L12.6232938,23.7196733 L12.6266527,23.7031097 L12.609561,23.275831 C12.6075724,23.2657013 12.6010112,23.2592993 12.5934901,23.257841 L12.5934901,23.257841 Z M12.8583906,23.1452862 L12.8445485,23.1473072 L12.6598443,23.2396597 L12.6498822,23.2499052 L12.6498822,23.2499052 L12.6471943,23.2611114 L12.6650943,23.6906389 L12.6699349,23.7034178 L12.6699349,23.7034178 L12.678386,23.7104931 L12.8793402,23.8032389 C12.8914285,23.8068999 12.9022333,23.8029875 12.9078286,23.7952264 L12.9118235,23.7811639 L12.8776777,23.1665331 C12.8752882,23.1545897 12.8674102,23.1470016 12.8583906,23.1452862 L12.8583906,23.1452862 Z M12.1430473,23.1473072 C12.1332178,23.1423925 12.1221763,23.1452606 12.1156365,23.1525954 L12.1099173,23.1665331 L12.0757714,23.7811639 C12.0751323,23.7926639 12.0828099,23.8018602 12.0926481,23.8045676 L12.108256,23.8032389 L12.3092106,23.7104931 L12.3186497,23.7024347 L12.3186497,23.7024347 L12.3225043,23.6906389 L12.340401,23.2611114 L12.337245,23.2485176 L12.337245,23.2485176 L12.3277531,23.2396597 L12.1430473,23.1473072 Z" id="MingCute" fill-rule="nonzero"> </path> <path d="M6.85728,2.44489 C7.99928,3.27790429 8.88915755,4.41552061 9.65001761,5.50316536 L10.0920364,6.14691904 L10.0920364,6.14691904 L10.509,6.76166 L10.509,6.76166 C10.9374,7.38835 10.8351,8.244 10.2531,8.74772 L8.30198,10.1967 C8.10859,10.3404 8.04429,10.6014 8.16028,10.8125 C8.60173,11.6161 9.38819,12.8119 10.2882,13.7119 C11.1891,14.6128 12.4414,15.45 13.3002,15.9412 C13.5229,16.0685 13.803,15.9948 13.9438,15.7803 L15.2131,13.8468 C15.6999,13.1991 16.6088,13.0576 17.2695,13.5149 L17.9332982,13.9735916 C19.1717645,14.8335207 20.5037538,15.8105615 21.521,17.1133 C21.8626,17.5507 21.9133,18.1227 21.7096,18.5981 C20.8728,20.5507 18.7552,22.2136 16.5524,22.1325 L16.2518759,22.1158001 L16.2518759,22.1158001 L16.0189256,22.0957065 L16.0189256,22.0957065 L15.7611336,22.0668244 L15.7611336,22.0668244 L15.4795621,22.0277651 L15.4795621,22.0277651 L15.1752731,21.97714 C15.1227241,21.9676615 15.0692729,21.9576432 15.0149414,21.9470562 L14.6785676,21.8764784 C14.6208039,21.8635009 14.5622043,21.8498968 14.5027909,21.8356372 L14.136722,21.7419821 L14.136722,21.7419821 L13.7521839,21.6312063 L13.7521839,21.6312063 L13.3502388,21.501921 C11.5039131,20.8764078 9.16110938,19.6464875 6.75735,17.2427 C4.35356813,14.8389125 3.12365344,12.4961028 2.49813876,10.6497861 L2.36885301,10.2478433 L2.36885301,10.2478433 L2.25807648,9.86330795 L2.25807648,9.86330795 L2.16442042,9.4972422 L2.16442042,9.4972422 L2.08649611,9.15070812 C2.06298899,9.03857004 2.04187237,8.92986425 2.02291481,8.82476776 L1.97228778,8.52048321 L1.97228778,8.52048321 L1.93322631,8.23891652 L1.93322631,8.23891652 L1.90434165,7.98112978 L1.90434165,7.98112978 L1.88424507,7.74818505 L1.88424507,7.74818505 L1.86754,7.44767 L1.86754,7.44767 C1.78675,5.25221 3.46855,3.11902 5.41215,2.28605 C5.86822,2.09059 6.4206,2.12636 6.85728,2.44489 Z M13.818,3.81801 C14.1785538,3.45753 14.7457349,3.42980077 15.1379989,3.73482231 L15.2322,3.81801 L17,5.58578 L18.7678,3.81801 C19.1583,3.42749 19.7915,3.42749 20.182,3.81801 C20.5424615,4.17849 20.5701893,4.74572503 20.2651834,5.1380135 L20.182,5.23222 L18.4142,6.99999 L20.182,8.76776 C20.5725,9.15828 20.5725,9.79145 20.182,10.182 C19.8215385,10.5424615 19.2542793,10.5701893 18.8620027,10.2651834 L18.7678,10.182 L17,8.4142 L15.2322,10.182 C14.8417,10.5725 14.2086,10.5725 13.818,10.182 C13.4575385,9.82149231 13.4298107,9.25425515 13.7348166,8.86196652 L13.818,8.76776 L15.5858,6.99999 L13.818,5.23222 C13.4275,4.8417 13.4275,4.20853 13.818,3.81801 Z" id="形状" fill="${icon_color}"> </path> </g> </g> </g> </g></svg>`;
    }

    if (main) {
        const toast = document.createElement("div");
        // Remove toast when clicked
        toast.onclick = function (e) {
            if (e.target.closest(".toast__close")) {
                main.removeChild(toast);

                // Tắt âm thanh
                incomingCallAudio.pause();
                incomingCallAudio.currentTime = 0;
                incomingCallAudio.muted = false;
                incomingCallAudio.autoplay = true;

                // Ignore call
                if (session) {
                    session.terminate();
                    session = null;
                }
            }
        };
        toast.classList.add("toast-call", `toast--${type}`);
        toast.style.animation = `slideInLeft ease .3s`;

        toast.innerHTML = `
            <div class="toast__icon toast__main" type="${type}" call_id="${call_id}" zalo_id="${zalo_id}" phone="${phone}">${icon}</div>
            <div class="toast__body toast__main" type="${type}" call_id="${call_id}" zalo_id="${zalo_id}" phone="${phone}">
                <h3 class="toast__title">
                    ${title}...
                </h3>
                ${hotline != '' ? `<span class="switchboard" data="${hotline}">${hotline}</span>` : ''}
                <p class="toast__msg">
                    ${call_from_display}
                    ${time}
                </p>
            </div>
            ${action != '' ? `<div class="toast__action">${action}</div>` : ''}
            <div class="toast__close">
                <svg xmlns="http://www.w3.org/2000/svg" version="1.0" width="22" height="22" viewBox="0 0 834.000000 834.000000" preserveAspectRatio="xMidYMid meet">
                    <g transform="translate(0.000000,834.000000) scale(0.100000,-0.100000)" fill="#ff0000" stroke="none">
                        <path d="M7030 6906 c-228 -95 -1055 -557 -1825 -1019 -287 -172 -844 -513 -1119 -686 -72 -44 -132 -81 -136 -81 -6 0 -132 102 -400 324 -354 294 -671 571 -1039 910 -171 157 -204 183 -280 220 -83 39 -90 41 -181 40 -85 0 -103 -3 -162 -31 -76 -36 -117 -83 -144 -166 -29 -89 -80 -128 -170 -129 -43 -1 -61 5 -88 26 -19 14 -78 46 -131 71 l-97 45 -81 -47 c-166 -95 -391 -256 -404 -290 -10 -26 97 -233 201 -388 337 -502 756 -1010 1233 -1495 l123 -126 -92 -65 c-51 -37 -219 -150 -373 -251 -798 -527 -1154 -766 -1290 -867 -82 -61 -221 -163 -308 -227 -86 -63 -157 -118 -157 -121 0 -16 60 -106 122 -184 37 -48 68 -97 68 -109 0 -34 -17 -60 -87 -131 -51 -52 -64 -71 -59 -87 8 -23 183 -181 294 -264 41 -31 103 -71 136 -88 l61 -31 115 31 c136 35 224 46 263 31 33 -13 35 -36 6 -91 -12 -22 -19 -46 -16 -53 2 -6 56 -45 119 -84 l115 -73 93 0 c86 0 96 2 129 27 20 15 155 122 301 238 503 400 593 469 1400 1072 74 55 173 130 219 167 46 36 89 66 94 66 14 0 242 -181 467 -371 364 -308 539 -444 797 -615 220 -148 279 -178 342 -177 85 1 269 81 432 187 54 36 109 68 122 71 14 4 70 -14 157 -51 224 -93 208 -90 291 -64 91 27 174 67 263 125 39 24 74 46 79 48 5 1 0 22 -12 44 l-21 42 28 18 c25 18 194 99 362 176 97 44 187 112 240 183 47 62 90 156 74 161 -52 17 -387 237 -699 458 -428 304 -1275 936 -1269 947 4 6 214 155 469 333 518 362 1676 1087 2332 1460 90 52 121 74 117 85 -13 32 -7 45 33 72 57 39 133 114 133 132 0 11 -160 155 -254 227 -19 15 -29 16 -118 2 l-97 -15 -68 27 c-89 35 -173 83 -173 99 0 7 14 25 30 41 17 16 30 32 30 36 0 33 -425 262 -479 259 -3 -1 -31 -11 -61 -24z"/>
                    </g>
                </svg>
            </div>
            `;
        // <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M20.7457 3.32851C20.3552 2.93798 19.722 2.93798 19.3315 3.32851L12.0371 10.6229L4.74275 3.32851C4.35223 2.93798 3.71906 2.93798 3.32854 3.32851C2.93801 3.71903 2.93801 4.3522 3.32854 4.74272L10.6229 12.0371L3.32856 19.3314C2.93803 19.722 2.93803 20.3551 3.32856 20.7457C3.71908 21.1362 4.35225 21.1362 4.74277 20.7457L12.0371 13.4513L19.3315 20.7457C19.722 21.1362 20.3552 21.1362 20.7457 20.7457C21.1362 20.3551 21.1362 19.722 20.7457 19.3315L13.4513 12.0371L20.7457 4.74272C21.1362 4.3522 21.1362 3.71903 20.7457 3.32851Z" fill="#0F0F0F"></path> </g></svg>
        main.appendChild(toast);
    }
}

function showToastCall(type = '', call_id = '', zalo_id = '', phone = '', hotline = '') {
    toast({
        call_id: call_id,
        zalo_id: zalo_id,
        phone: phone,
        hotline: hotline,
        type: type,
    });
}

function showConnect(check = true) {
    if (check) {
        let color = '#1bcfb4';
        let icon = `<svg width="28px" height="28px" viewBox="0 0 24 24" fill="none" class="icon icon-phone icon-online" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round" stroke="#CCCCCC" stroke-width="0.096"></g><g id="SVGRepo_iconCarrier"> <path d="M17.62 10.7516C17.19 10.7516 16.85 10.4016 16.85 9.98156C16.85 9.61156 16.48 8.84156 15.86 8.17156C15.25 7.52156 14.58 7.14156 14.02 7.14156C13.59 7.14156 13.25 6.79156 13.25 6.37156C13.25 5.95156 13.6 5.60156 14.02 5.60156C15.02 5.60156 16.07 6.14156 16.99 7.11156C17.85 8.02156 18.4 9.15156 18.4 9.97156C18.4 10.4016 18.05 10.7516 17.62 10.7516Z" fill="${color}"></path> <path d="M21.2298 10.75C20.7998 10.75 20.4598 10.4 20.4598 9.98C20.4598 6.43 17.5698 3.55 14.0298 3.55C13.5998 3.55 13.2598 3.2 13.2598 2.78C13.2598 2.36 13.5998 2 14.0198 2C18.4198 2 21.9998 5.58 21.9998 9.98C21.9998 10.4 21.6498 10.75 21.2298 10.75Z" fill="${color}"></path> <path d="M11.05 14.95L9.2 16.8C8.81 17.19 8.19 17.19 7.79 16.81C7.68 16.7 7.57 16.6 7.46 16.49C6.43 15.45 5.5 14.36 4.67 13.22C3.85 12.08 3.19 10.94 2.71 9.81C2.24 8.67 2 7.58 2 6.54C2 5.86 2.12 5.21 2.36 4.61C2.6 4 2.98 3.44 3.51 2.94C4.15 2.31 4.85 2 5.59 2C5.87 2 6.15 2.06 6.4 2.18C6.66 2.3 6.89 2.48 7.07 2.74L9.39 6.01C9.57 6.26 9.7 6.49 9.79 6.71C9.88 6.92 9.93 7.13 9.93 7.32C9.93 7.56 9.86 7.8 9.72 8.03C9.59 8.26 9.4 8.5 9.16 8.74L8.4 9.53C8.29 9.64 8.24 9.77 8.24 9.93C8.24 10.01 8.25 10.08 8.27 10.16C8.3 10.24 8.33 10.3 8.35 10.36C8.53 10.69 8.84 11.12 9.28 11.64C9.73 12.16 10.21 12.69 10.73 13.22C10.83 13.32 10.94 13.42 11.04 13.52C11.44 13.91 11.45 14.55 11.05 14.95Z" fill="${color}"></path> <path d="M21.9696 18.3291C21.9696 18.6091 21.9196 18.8991 21.8196 19.1791C21.7896 19.2591 21.7596 19.3391 21.7196 19.4191C21.5496 19.7791 21.3296 20.1191 21.0396 20.4391C20.5496 20.9791 20.0096 21.3691 19.3996 21.6191C19.3896 21.6191 19.3796 21.6291 19.3696 21.6291C18.7796 21.8691 18.1396 21.9991 17.4496 21.9991C16.4296 21.9991 15.3396 21.7591 14.1896 21.2691C13.0396 20.7791 11.8896 20.1191 10.7496 19.2891C10.3596 18.9991 9.96961 18.7091 9.59961 18.3991L12.8696 15.1291C13.1496 15.3391 13.3996 15.4991 13.6096 15.6091C13.6596 15.6291 13.7196 15.6591 13.7896 15.6891C13.8696 15.7191 13.9496 15.7291 14.0396 15.7291C14.2096 15.7291 14.3396 15.6691 14.4496 15.5591L15.2096 14.8091C15.4596 14.5591 15.6996 14.3691 15.9296 14.2491C16.1596 14.1091 16.3896 14.0391 16.6396 14.0391C16.8296 14.0391 17.0296 14.0791 17.2496 14.1691C17.4696 14.2591 17.6996 14.3891 17.9496 14.5591L21.2596 16.9091C21.5196 17.0891 21.6996 17.2991 21.8096 17.5491C21.9096 17.7991 21.9696 18.0491 21.9696 18.3291Z" fill="${color}"></path></g></svg>`;
        $('#call-phone__circle').html(icon);
        $('#agent-number').html(SIP_USER);
        $("#availability-status").removeClass("busy").addClass("online");
        $('input#busy_stt').prop("checked", false);
    }
    else {
        let color = '#ff5f4d';
        let icon = `<svg width="28px" height="28px" viewBox="0 0 24 24" fill="none" class="icon icon-phone icon-busy" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M22.0005 18.3291C22.0005 18.6891 21.9205 19.0591 21.7505 19.4191C21.5805 19.7791 21.3605 20.1191 21.0705 20.4391C20.5805 20.9791 20.0405 21.3691 19.4305 21.6191C18.8305 21.8691 18.1705 21.9991 17.4705 21.9991C16.4505 21.9991 15.3605 21.7591 14.2105 21.2691C13.0605 20.7791 11.9005 20.1191 10.7605 19.2891C10.1805 18.8591 9.61055 18.4191 9.06055 17.9391L12.3205 14.6791C12.3305 14.6791 12.3305 14.6791 12.3405 14.6891C12.8605 15.1291 13.2905 15.4291 13.6305 15.6091C13.6805 15.6291 13.7405 15.6591 13.8105 15.6891C13.8905 15.7191 13.9705 15.7291 14.0605 15.7291C14.2305 15.7291 14.3605 15.6691 14.4705 15.5591L15.2305 14.8091C15.4805 14.5591 15.7205 14.3691 15.9505 14.2491C16.1805 14.1091 16.4105 14.0391 16.6605 14.0391C16.8505 14.0391 17.0505 14.0791 17.2705 14.1691C17.4905 14.2591 17.7205 14.3891 17.9705 14.5591L21.2905 16.9091C21.5505 17.0891 21.7305 17.2991 21.8405 17.5491C21.9405 17.7991 22.0005 18.0491 22.0005 18.3291Z" fill="${color}"></path> <path d="M10.76 13.24L7.5 16.5C7.49 16.5 7.49 16.5 7.48 16.49C6.45 15.45 5.52 14.36 4.68 13.22C3.87 12.1 3.22 10.97 2.74 9.86C2.73 9.84 2.73 9.83 2.72 9.81C2.24 8.67 2 7.58 2 6.54C2 5.86 2.12 5.21 2.36 4.61C2.56 4.1 2.86 3.62 3.27 3.19C3.34 3.11 3.42 3.02 3.51 2.94C3.67 2.78 3.83 2.64 4 2.53C4.01 2.53 4.01 2.53 4.01 2.53C4.51 2.17 5.04 2 5.6 2C5.88 2 6.16 2.06 6.41 2.18C6.65 2.29 6.86 2.45 7.03 2.68C7.05 2.7 7.06 2.72 7.08 2.74L9.4 6.01C9.58 6.26 9.71 6.49 9.8 6.71C9.89 6.92 9.94 7.13 9.94 7.32C9.94 7.56 9.87 7.8 9.73 8.03C9.6 8.26 9.41 8.5 9.17 8.74L8.41 9.53C8.3 9.64 8.25 9.77 8.25 9.93C8.25 10.01 8.26 10.08 8.28 10.16C8.31 10.24 8.34 10.3 8.36 10.36C8.54 10.69 8.85 11.12 9.29 11.64C9.74 12.16 10.22 12.69 10.74 13.22C10.75 13.23 10.75 13.23 10.76 13.24Z" fill="${color}"></path> <path d="M21.7709 2.22891C21.4709 1.92891 20.9809 1.92891 20.6809 2.22891L2.23086 20.6889C1.93086 20.9889 1.93086 21.4789 2.23086 21.7789C2.38086 21.9189 2.57086 21.9989 2.77086 21.9989C2.97086 21.9989 3.16086 21.9189 3.31086 21.7689L21.7709 3.30891C22.0809 3.00891 22.0809 2.52891 21.7709 2.22891Z" fill="${color}"></path></g></svg>`;
        $('#call-phone__circle').html(icon);
        $("#availability-status").removeClass("online").addClass("busy");
        $('input#busy_stt').prop("checked", true);
    }
}

function string_between_strings(startStr, endStr, str) {
    pos = str.indexOf(startStr) + startStr.length;
    return str.substring(pos, str.indexOf(endStr, pos));
}

function extract_call_id(str) {
    return string_between_strings("X-cid: ", "\n", str).trim();
}

function extract_hotline(str) {
    const pattern = /X-Hotline:(?<regex_x_cid>\s*[\w\.-]+).*/;
    const matches = str.match(pattern);
    if (matches && matches.groups && matches.groups.regex_x_cid) {
        let hotline = matches.groups.regex_x_cid.trim();

        switch (hotline) {
            case '0911236600':
            case '01388506538':
                return 'Laptop Dell';
            case '02839977788':
            case '02866509900':
                return 'timchuyenbay (.com)';
            case '02839977799':
                return 'Sanvemaybay (.com.vn)';
            case '1900636063':
                return 'Vemaybay5s (.com)';
            case '02873001886':
                return 'Sữa tươi Úc';

            /**********  ZALO  **********/
            case '2941581384627345950101':
                return 'Zalo nội địa';
            case '2941581384627345950102':
                return 'Zalo quốc tế';
            case '2941581384627345950103':
                return 'Khiếu nại';

            default:
                return 'Vietjet.net';
        }
    }
    return '';
}

function handleButtons(type) {
    if (type == 'incomming') {
        $('.voiceip-calling .skype').css('animation', 'play 1.5s ease infinite');
        $('.voiceip-button').hide();
        $('.voiceip-accept').show();
        $('.voiceip-decline').show();

        $('#voiceip-timer').hide();
    }
    else if (type == 'outgoing') {
        $('.voiceip-calling .skype').css('animation', 'play 1.5s ease infinite');
        $('.voiceip-button').hide();
        $('.voiceip-end').show();
    }
    else if (type == 'processing') {
        $('.voiceip-button').hide();
        $('.voiceip-end').show();
        $('.voiceip-mute').show();
        $('.voiceip-unmute').show();
        $('.voiceip-dtmf').show();

        let t = $('.voiceip-header__title').html();
        if (t.indexOf("...") !== -1) $('.voiceip-header__title').html('Cuộc gọi');

        $('#voiceip-timer').show();
        $('.voiceip-calling .skype').css('animation', 'play 1.5s ease infinite');

        $('.wrap-info-voiceip').hide();
        $('.wrap-form-voiceip').show();
    }
    else if (type == 'completed') {
        $('.voiceip-calling .skype').css('animation', 'none');
        $('.voiceip-button').hide();
        $('.voiceip-update').show();
        $('.voiceip-dtmf').show();
    }
}

function display_avatar_zalo(avatar) {
    if (avatar.length == 0) return;

    $('#popup-voiceip .skype').addClass('skype-avatar');
    $('#popup-voiceip .skype-avatar').css({
        'background-image': 'url(' + avatar + ')',
        'background-size': 'contain',
        'border': 'solid 3px #fff',
    });
}

function hide_avatar_zalo() {
    $('#popup-voiceip .skype').removeClass('skype-avatar');
    $('#popup-voiceip .skype').css({
        'background-image': 'unset',
        'background-size': 'unset',
        'border': 'solid 15px #fff',
    });
}

// Reset popup call
function resetPopupVoiceip() {
    // Info
    $('#voiceip-info-name').html('');
    $('#voiceip-info-phone').html('');
    $('#voiceip-info-zaloid').closest('p').find('span').html('');
    $('#voiceip-info-zaloid').html('');
    $('#voiceip-info-zaloid').attr('href', '#');

    // Value
    $('#voiceip-zalo-id').val('');
    $('#voiceip-phone').val('');
    $('#voiceip-name').val('');
    $('#voiceip-email').val('');
    $('#voiceip-notes').val('');
    $('input[name="voiceip-contact-id"]').val('');

    $('.voiceip-update').attr('booking_id', '');
    $('.voiceip-update').attr('booking_name', '');
    $('.voiceip-update').attr('type_call_booking', '');

    $('.wrap-form-voiceip').hide();
    $('.wrap-info-voiceip').show();

    $('#popup-voiceip').attr('call_id', '');
    $('.voiceip-header__title').html('Cuộc gọi');
    hide_avatar_zalo();
    resetTimer();
}

function formatPhoneNumber(phoneNumber) {
    const cleaned = ('' + phoneNumber).replace(/\D/g, '');
    const match = cleaned.match(/^(\d{3})(\d{4})(\d{3})$/);

    if (match) {
        return match[1] + ' ' + match[2] + ' ' + match[3];
    }

    return phoneNumber;
}

/********************   COUNT CALL TIME   ********************/
// Declare variable
var call_timer;
var seconds = 0;
var minutes = 0;
var hours = 0;

function startTimer() {
    $('#voiceip-timer').show();
    call_timer = setInterval(function () {
        seconds++;
        if (seconds == 60) {
            seconds = 0;
            minutes++;
            if (minutes == 60) {
                minutes = 0;
                hours++;
            }
        }

        // Format time HH:mm:ss
        $('#voiceip-timer').text(
            (hours < 10 ? '0' : '') + hours + ':' +
            (minutes < 10 ? '0' : '') + minutes + ':' +
            (seconds < 10 ? '0' : '') + seconds
        );
    }, 1000);
}

function stopTimer() {
    clearInterval(call_timer);
}

function resetTimer() {
    stopTimer();
    seconds = 0;
    minutes = 0;
    hours = 0;
    // Format time HH:mm:ss
    $('#voiceip-timer').text(
        (hours < 10 ? '0' : '') + hours + ':' +
        (minutes < 10 ? '0' : '') + minutes + ':' +
        (seconds < 10 ? '0' : '') + seconds
    );
}

function getCurrentTime() {
    let date = new Date();
    let hour = date.getHours() < 10 ? ('0' + date.getHours().toString()) : date.getHours();
    let min = date.getMinutes() < 10 ? ('0' + date.getMinutes().toString()) : date.getMinutes();
    let second = date.getSeconds() < 10 ? ('0' + date.getSeconds().toString()) : date.getSeconds();
    let d = date.getDate();
    let m = date.getMonth() + 1;
    let y = date.getFullYear();
    return hour + ':' + min + ':' + second + ' ' + d + '/' + m + '/' + y;  // HH:mm:ss dd/mm/yyyy
}

/********************  HEADER CALL PHONE  ********************/
var addNumber = function (field) {
    let call_number_val = $("#call_voiceip_main_number").val();
    $("#call_voiceip_main_number").val(call_number_val + field.name);
};

var addNumber_oninput = function (field) {
    $("#call_voiceip_main_number").val(field.value);
};

var deleteAll = function () {
    $("#call_voiceip_main_number").val('');
};

var removeNumber = function () {
    let call_number_val = $("#call_voiceip_main_number").val();

    if (call_number_val.length > 0) {
        var newValue = call_number_val.slice(0, -1);
        $('#call_voiceip_main_number').val(newValue);
    }
};

$(document).keyup(function (e) {
    if (e.keyCode == 8) {
        removeNumber();
    }
});

// When click outside to close modal 
$(document).mouseup(function (e) { // event nhả chuột
    // modal count_passenger
    let numpad = $(".call-phone__numpad");
    if (!numpad.is(e.target) && numpad.has(e.target).length === 0) {
        numpad.hide();
    }

    let transfer_list = $("#offer-transfer-list");
    if (!transfer_list.is(e.target) && transfer_list.has(e.target).length === 0) {
        transfer_list.removeClass('active');
    }
});

// Check is SPAM Call
function isSpamPhoneNumber(phoneNumber) {
    let phoneString = phoneNumber.toString();
    let topPhone = ['028', '024', '021', '022', '029', '195', '252', '247', '231', '371', '232', '224', '027'];
    let prefix = phoneString.substring(0, 3);

    if (topPhone.includes(prefix)) {
        for (let i = 3; i <= phoneString.length - 3; i++) {
            if (phoneString[i] === phoneString[i + 1] && phoneString[i] === phoneString[i + 2]) {
                return true;
            }
        }
    }

    return false;
}

// SEARCH - TRANSFER
function removeAccents(str) {
    return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd').replace(/Đ/g, 'D');
}

function onSearch() {
    const input_transfer = document.querySelector("#transfer-phone");
    const filter_transfer = removeAccents(input_transfer.value.toLowerCase());
    const list_transfer = document.querySelectorAll("ul#offer-transfer-list li");

    list_transfer.forEach((el) => {
        const text_transfer = removeAccents(el.textContent.toLowerCase());
        el.style.display = text_transfer.includes(filter_transfer) ? "" : "none";
    });
}