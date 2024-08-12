const OA_ID = $(`input[name="oa_id"]`).val();
const OA_NAME = $(`input[name="oa_name"]`).val();
const OA_AVATAR = $(`input[name="oa_avatar"]`).val();
const ADMIN_ID = $(`input[name="admin_id"]`).val();
const ADMIN_NAME = $(`input[name="admin_name"]`).val();
const URL = $(`input[name="entrypoint"]`).val();
const DEFAULT_AVATAR = $(`input[name="default_avatar"]`).val();
const URL_CHAT_WEBSOCKET = $(`input[name="websocket_url"]`).val();
const USER_EVENT_LIST = ['user_send_text', 'user_send_image', 'user_send_gif', 'user_send_link', 'user_send_sticker', 'user_send_location', 'user_send_file', 'user_send_audio', 'user_send_video'];
const OA_EVENT_LIST = ['oa_send_text', 'oa_send_image', 'oa_send_gif', 'oa_send_sticker', 'oa_send_file', 'oa_send_list'];
var zsocket;
var count_connect_error = 0;

$(document).ready(function () {
    // connectWebSocket();

    // Click
    $('.choose_label').click(function () {
        $(this).toggleClass('is-open');
        $(".choose_label .dropdown_content_label ").toggleClass('opened');
    });

    // Choose user type list
    $('.dropdown-item-type-list').click(function () {
        let text = $(this).text();
        let value = $(this).attr('data');

        if(value && value.length > 0 && value != $('input[name="user_type_list"]').val()) {
            // Update value
            $('#text_display_type_list').text(text);
            $('input[name="user_type_list"]').val(value);

            $.ajax({
                url: URL,
                type: "POST",
                data: {
                    action: "get_list_user",
                    offset: 0,
                    value: value,
                    format_list_chat: 1
                },
                contentType: "application/x-www-form-urlencoded; charset=utf-8",
                beforeSend: function() {
                    $('#list_mess_main').html('<div class="loader_list_user mt-3"></div>');
                },
                success: function (response) { // html
                    $('.loader_list_user').remove();

                    if(response.length > 0) {
                        $('#list_mess_main').html(response);
                        $('input[name="offset_list_user"]').val(50);
                    }
                    else {
                        $('#list_mess_main').html('<center><i>Không tìm thấy kết quả</i></center>');
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $('.loader_list_user').remove();
                    console.error(XMLHttpRequest);
                    console.error("Status: " + textStatus);
                    console.error("Error: " + errorThrown);
                }
            });
        }
    });

    // List messages
    $(document).on("click", "li.item_mess", function() {
        let zalo_id = $(this).attr('id').replace("li", "");

        if (zalo_id) {
            let user_info = $(`#liuserinfo${zalo_id}`).text();
            let is_get_user_info = user_info.length > 10 ? 0 : 1;

            // Hide
            $('#chat_welcome').hide();
            $(`#zalochat_main`).hide();
            $(`#zalochat_profile`).hide();

            // Reset
            $(`#section-message__details`).html('');
            $(this).find('.mess_number').html('');
            $(`#content_chat`).attr('zalo_id', zalo_id);
            $('li.item_mess').removeClass('active');
            $(`#li${zalo_id}`).addClass('active');
            
            $.ajax({
                url: URL,
                type: "POST",
                data: {
                    action: "get_messages",
                    oa_id: OA_ID,
                    zalo_id: zalo_id,
                    is_get_user_info: is_get_user_info
                },
                beforeSend: function() {
                    $('.container-waiting').show();
                },
                success: function (response) {
                    $('.container-waiting').hide();
                    data = JSON.parse(response); // Object

                    /************  1. User  ************/
                    let data_user = {};
                    if(is_get_user_info === 0) {
                        data_user['data'] = JSON.parse(user_info);
                    }
                    else {
                        data_user = data['user_info'];
                        if(data_user['error'] !== 0) {
                            showModalNotify('warning', 'Người dùng không thể tương tác');
                            return false;
                        }
                    }

                    /**********  2. Messages  **********/
                    let data_message = data['messages_info'];
                    if (data_message['error'] !== 0) {
                        showModalNotify('warning', 'Người dùng không thể tương tác');
                        return false;
                    }

                    // Show
                    $(`#zalochat_main`).show();
                    $(`#zalochat_profile`).show();
                    $(`input[name="offset_load_more_message"]`).val(data_message['offset']);

                    create_chat_box(data_user['data'], data_message['data']);

                    /**********  3. Quota  **********/
                    if(data['quota_info']) {
                        let data_quota = data['quota_info'];
                        let cs = data['quota_info']['cs_reply']['remain'];
                        let oa_cs = data['quota_info']['oa_cs'] ? data['quota_info']['oa_cs'] : 0;
                        handle_quota_user(cs, data_quota['last_interaction'], oa_cs);
                    }
                  
                    // Reset
                    $(`#liuserinfo${zalo_id}`).text('');
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $('.container-waiting').hide();
                    showModalNotify('error', 'Kết nối thất bại, vui lòng thử lại sau');
                    console.error(XMLHttpRequest);
                    console.error("Status: " + textStatus);
                    console.error("Error: " + errorThrown);
                }
            });
        }
    });

    // Load more messages
    $('.section-post').scroll(function() {
        if($(this).scrollTop() == 0) {
            let zalo_id = $('#content_chat').attr('zalo_id');
            let offset = parseInt($(`input[name="offset_load_more_message"]`).val());

            if(zalo_id && offset > 0 && $(`._section-mt`)[0]) {
                $.ajax({
                    url: URL,
                    type: "POST",
                    data: {
                        action: "get_messages",
                        oa_id: OA_ID,
                        zalo_id: zalo_id,
                        offset: offset,
                        is_get_user_info: 0
                    },
                    beforeSend: function() {
                        $('.loader_messages').show();
                    },
                    success: function (response) {
                        data = JSON.parse(response); // Object
    
                        let data_message = data['messages_info'];
                        if (data_message['error'] !== 0) {
                            showModalNotify('error', 'Lỗi lấy dữ liệu');
                            return false;
                        }

                        $(`input[name="offset_load_more_message"]`).val(data_message['offset']);
                        let last_timestamp = parseInt($(`#section-message__details .section-item`).first().attr('timestamp'));
                        
                        // Display
                        $(`#section-message__details ._section-mt`).remove();
                        let container = $('.section-post')[0];
                        container.scrollTop = container.scrollHeight;
                        let html_old = $(`#section-message__details`).html();
                        let timeline = getTimeline(last_timestamp);
                        let html_new = create_chat_box(null, data_message['data'], true) + timeline + html_old;
                        $(`#section-message__details`).html(html_new);

                        // Scroll to message element first
                        container.scrollTop += $(`#_sectionTimestamp${last_timestamp}`).offset().top - 140;
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        $('.loader_messages').hide();
                        console.error(XMLHttpRequest);
                        console.error("Status: " + textStatus);
                        console.error("Error: " + errorThrown);
                    }
                });
            }
        }
    });

    // Send message
    $('#send_message').click(function () {
        if($(`textarea[name="message_content"]`).val().trim().length == 0 && !isUploading()) return false;
        let formData = generate_form_data();
        reset_chat_box();
        send_message(formData);
    });
    $(`textarea[name="message_content"]`).on('keydown', function (e) {
        if (e.shiftKey && (e.key === 'Enter' || e.keyCode === 13)) {
            e.preventDefault();

            // Insert a newline character at the current cursor position
            let cursorPosition = $(this).prop('selectionStart');
            let textBeforeCursor = $(this).val().substring(0, cursorPosition);
            let textAfterCursor = $(this).val().substring(cursorPosition);

            $(this).val(textBeforeCursor + '\n' + textAfterCursor);

            // Move the cursor to the correct position
            $(this).prop('selectionStart', cursorPosition + 1);
            $(this).prop('selectionEnd', cursorPosition + 1);
        }
        else if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            if($(this).val().trim().length == 0 && !isUploading()) return false;
            let formData = generate_form_data();
            reset_chat_box();
            send_message(formData);
        }
    });

    // Send request user info
    $('#btn_request_user_info').click(function () {
        let zalo_id = $(`#content_chat`).attr(`zalo_id`);
        let formData = new FormData();
        formData.append('action', 'send_message');
        formData.append('oa_id', OA_ID);
        formData.append('zalo_id', zalo_id);
        formData.append('type', 'request_user_info');

        send_message(formData);
    });

    // Choose image to send
    $('#upload_image').click(function () {
        $('input[name="image_upload"]').trigger("click");
    });
    $('input[name=image_upload]').change(function() {
        reset_upload_content('file');

        let fileInput = $(this)[0];
        let reader = new FileReader();
        reader.onload = function (e) {
            $('#preview_image_upload').attr('src', e.target.result).show();
        }
        reader.readAsDataURL(fileInput.files[0]);
    });
    $(`textarea[name="message_content"]`).bind("paste", function(event){
        event.preventDefault();
                
        const items = event.originalEvent.clipboardData.items;
        let textContent = $(this).val();
        let imageBlob = null;

        for (let i = 0; i < items.length; i++) {
            let item = items[i];

            if (item.kind === 'string' && item.type === 'text/plain') {
                item.getAsString(text => {
                    textContent += text;
                    $(this).val(textContent);
                });
            }
            else if (item.kind === 'file' && item.type.startsWith('image/')) {
                imageBlob = item.getAsFile();
            }

            if (imageBlob) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#preview_image_upload').attr('src', e.target.result).show();

                    // Create a new Blob and File object
                    const blob = new Blob([imageBlob], { type: imageBlob.type });
                    const newFile = new File([blob], imageBlob.name, { type: imageBlob.type });

                    // Create a DataTransfer object to simulate file input
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(newFile);

                    // Set the files to the file input
                    $('input[name=image_upload]')[0].files = dataTransfer.files;
                };
                reader.readAsDataURL(imageBlob);
            }
        }
    });

    // Choose file to send
    $('#upload_file').click(function () {
        $('input[name="file_upload"]').trigger("click");
    });
    $('input[name=file_upload]').change(function() {
        reset_upload_content('image');

        let fileInput = $(this)[0];
        let filename  = $(this).val().split('\\').pop();
        let ext  = filename.replace(/^.*\./, '');
        let size = this.files[0].size

        $('#preview_file_upload .file-image img').attr('src', getImageFile(ext));
        $('#preview_file_upload .file-name').text(filename);
        $('#preview_file_upload .file-size').text(formatFileSize(size));
        $('#preview_file_upload').css('display', 'flex');
    });

    // Reset display upload content
    $('#reset_upload_content').click(function() { reset_upload_content(); });

    // Load more user
    $('#list_mess_main').on('scroll', function() {
        if($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight - 10) {
            if(!$('.loader_list_user').length) {
                let type_load = $('input[name="user_type_list"]').val();
                let offset = parseInt($('input[name="offset_list_user"]').val());
                let count_li = $("#list_mess_main").children().length;

                let current_list_user = '';
                $('li.item_mess').each(function() {
                    let id = $(this).attr('id').toString().replaceAll("li", "");
                    current_list_user += current_list_user.length == 0 ? id : `,${id}`;
                });

                if(type_load == 'default') {
                    $.ajax({
                        url: URL,
                        type: "POST",
                        data: {
                            action: "get_recent_messages", 
                            offset: offset,
                            current_list_user: current_list_user
                        },
                        beforeSend: function() {
                            $('#list_mess_main').append('<div class="loader_list_user"></div>');
                        },
                        success: function (response) { // html
                            $('.loader_list_user').remove();

                            offset = getNumberAtEndString(response);
                            html = response.slice(0, -offset.length);
    
                            $('input[name="offset_list_user"]').val(offset);
                            $('#list_mess_main').append(html);
                        },
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                            $('.loader_list_user').remove();
                            console.error(XMLHttpRequest);
                            console.error("Status: " + textStatus);
                            console.error("Error: " + errorThrown);
                        }
                    });
                }
                else {
                    if(offset == -1 || (type_load != 'L7D' && count_li < offset)) return false;

                    $.ajax({
                        url: URL,
                        type: "POST",
                        data: {
                            action: "get_list_user",
                            offset: offset,
                            value: type_load,
                            format_list_chat: 1
                        },
                        beforeSend: function() {
                            $('#list_mess_main').append('<div class="loader_list_user"></div>');
                        },
                        success: function (response) { // html
                            $('.loader_list_user').remove();

                            if(response == 'No data') offset = -1;
                            else {
                                offset += 50;
                                $('#list_mess_main').append(response);
                            }
    
                            $('input[name="offset_list_user"]').val(offset);
                        },
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                            $('.loader_list_user').remove();
                            console.error(XMLHttpRequest);
                            console.error("Status: " + textStatus);
                            console.error("Error: " + errorThrown);
                        }
                    });   
                }
            }
        }
    })

    // Quote
    $(document).on("click", ".more .btn_quote", function() {
        $('.preview_mess').remove();

        let quote_message_id = $(this).attr('data-message-id');
        let name = $('#profile_zalo_name').attr('data');
        if($(this).closest('.more').hasClass("me")) {
            name = OA_NAME;
        }

        let message = '', content = '', classname = '';
        if($(this).closest('.item-message').hasClass("picture")) {
            message = '[Hình ảnh]';
            content = $(this).closest('.item-message').find('.content-picture > img')[0].outerHTML;
            classname = 'has_images';
        }
        else if($(this).closest('.item-message').hasClass("sticker")) {
            message = '[Sticker]';
            content = $(this).closest('.item-message').find('img')[0].outerHTML;
            classname = 'has_sticker';
        }
        else if($(this).closest('.item-message').hasClass("card--file")) {
            message = '[Tệp đính kèm] ';
            message += $(this).closest('.item-message').find('.title > span').text();
            content = $(this).closest('.item-message').find('img')[0].outerHTML;
            classname = 'has_file';
        }
        else if($(this).closest('.item-message').hasClass("location-message")) message = '[Vị trí]';
        else if($(this).closest('.item-message').hasClass("product")) message = '[Tin liên kết]';
        else if($(this).closest('.item-message').hasClass("voice-message")) message = '[Tin nhắn thoại]';
        else message = $(this).closest('.item-message').find('.content > span').text();

        let quote = `
            <div class="preview_mess">
                <i class="icon icon_close" onclick="return this.parentNode.remove();">
                    <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#000000" stroke-width="1.5"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 1.25C6.06294 1.25 1.25 6.06294 1.25 12C1.25 17.9371 6.06294 22.75 12 22.75C17.9371 22.75 22.75 17.9371 22.75 12C22.75 6.06294 17.9371 1.25 12 1.25ZM9.70164 8.64124C9.40875 8.34835 8.93388 8.34835 8.64098 8.64124C8.34809 8.93414 8.34809 9.40901 8.64098 9.7019L10.9391 12L8.64098 14.2981C8.34809 14.591 8.34809 15.0659 8.64098 15.3588C8.93388 15.6517 9.40875 15.6517 9.70164 15.3588L11.9997 13.0607L14.2978 15.3588C14.5907 15.6517 15.0656 15.6517 15.3585 15.3588C15.6514 15.0659 15.6514 14.591 15.3585 14.2981L13.0604 12L15.3585 9.7019C15.6514 9.40901 15.6514 8.93414 15.3585 8.64124C15.0656 8.34835 14.5907 8.34835 14.2978 8.64124L11.9997 10.9393L9.70164 8.64124Z" fill="#000000"></path></svg>
                </i>
                <div class="wrap">
                    <div class="content_get ${classname}">${content}</div>
                    <div class="content_mess">
                        <div class="reply_name">
                            <i class="icon_quote_black">
                                <svg width="15px" height="15px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#808080" stroke-width="1.5">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M9.21255 12.75C9.12943 13.5242 8.9054 14.1421 8.5147 14.6891C7.99181 15.4211 7.11571 16.1036 5.66459 16.8292C5.29411 17.0144 5.14394 17.4649 5.32918 17.8354C5.51442 18.2059 5.96493 18.3561 6.33541 18.1708C7.88429 17.3964 9.00819 16.5789 9.7353 15.5609C10.4761 14.5238 10.75 13.3571 10.75 12V7.5C10.75 6.53351 9.96649 5.75 9 5.75H5C4.03351 5.75 3.25 6.53351 3.25 7.5V11C3.25 11.9665 4.03352 12.75 5 12.75H9.21255Z" fill="#808080"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M19.2125 12.75C19.1294 13.5242 18.9054 14.1421 18.5147 14.6891C17.9918 15.4211 17.1157 16.1036 15.6646 16.8292C15.2941 17.0144 15.1439 17.4649 15.3292 17.8354C15.5144 18.2059 15.9649 18.3561 16.3354 18.1708C17.8843 17.3964 19.0082 16.5789 19.7353 15.5609C20.4761 14.5238 20.75 13.3571 20.75 12V7.5C20.75 6.53352 19.9665 5.75 19 5.75H15C14.0335 5.75 13.25 6.53352 13.25 7.5V11C13.25 11.9665 14.0335 12.75 15 12.75H19.2125Z" fill="#808080"></path>
                                </svg>
                            </i>
                            <span class="me-1">Trả lời <strong>${name}</strong></span>
                        </div>
                        <div class="reply_text">${message}</div>
                    </div>
                </div>
                <input type="hidden" name="quote_message_id" value="${quote_message_id}" />
            </div>
        `;

        $('.content_mess_input').before(quote);
    });

    // Save contact
    $('#btn_save_contact').click(function () {
        let zalo_id     = $('#content_chat').attr('zalo_id');
        let phone       = $('#profile_mobile').text() != 'Chưa công khai' ? $('#profile_mobile').text() : '';
        let name        = $('#profile_zalo_name').attr('data');
        let alias       = $('#header_name_chat').text();
        let city        = $('input[name="profile_address_city"]').val();
        let district    = $('input[name="profile_address_district"]').val();
        let address     = $('input[name="profile_address_number"]').val();

        if(zalo_id && zalo_id.length > 0) {
            $.ajax({
                url: URL,
                type: "POST",
                data: {
                    action: "save_contact",
                    zalo_id : zalo_id,
                    phone : phone,
                    name : name,
                    alias : alias,
                    city : city,
                    district : district,
                    address : address
                },
                beforeSend: function() {
                    $('.container-waiting').show();
                },
                success: function (response) {
                    $('.container-waiting').hide();

                    res = JSON.parse(response);
                    let m = res['message'] ? res['message'] : 'Thao tác thất bại';
                    let d = res['description'] ? res['description'] : '';

                    if (res['error'] !== 0) showModalNotify('error', m, d);
                    else showModalNotify(1, m);
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    showModalNotify('error', 'Thao tác thất bại, vui lòng thử lại');
                    console.error(XMLHttpRequest);
                    console.error("Status: " + textStatus);
                    console.error("Error: " + errorThrown);
                }
            });x
        }
    });

    // Call zalo
    $('#func-call').click(function () {
        let zalo_id = $('#content_chat').attr('zalo_id');
        let name    = $('#profile_zalo_name').text();
        let avatar  = $('#header_avatar_chat').attr('src');
        let phone   = $('#profile_mobile').text() != 'Chưa công khai' ? $('#profile_mobile').text() : '';
        if(phone === '') phone = get_phone_by_alias($('#header_name_chat').text());
        
        if(zalo_id && zalo_id > 10) {
            resetPopupVoiceip();

            $('#voiceip-info-zaloid').html(zalo_id);
            $('#voiceip-info-zaloid').attr('href', `https://zalo.me/${zalo_id}`);
            $('#voiceip-info-zaloid').closest('p').find('span').html('Zalo ID: ');
            $('#voiceip-info-phone').html(formatPhoneNumber(phone));
            $('#voiceip-info-name').html(name);
            display_avatar_zalo(avatar);
            
            // Make a call
            if (!ua || !ua.isConnected() || !ua.isRegistered()) {
                showModalNotify('warning', 'Không có kết nối');
                return false;
            }
            ua.call(zalo_id, callOptions);

            $(this).css('pointer-events', 'none');
            setTimeout(function() {
                $('#func-call').css('pointer-events', 'unset');
            }, 15000);

            handleButtons('outgoing');
            $('.voiceip-header__title').html('Đang gọi...');
            $('.voiceip-timer').hide();
            $('#popup-voiceip').attr('call_id', session._request.call_id); // New call id
            $('#popup-voiceip').addClass('show');
            $('#popup__voiceip--wrap').addClass('show');
            $('#call-overlay').addClass('opened');
            return true;
        }
    });

    // Slide tab profile
    $('#func-slide').click(function () {
        if($('#zalochat_profile').is(":visible")) {
            $('#zalochat_profile').hide();
            $('#zalochat_main').css('flex', '1');
        }
        else {
            $('#zalochat_profile').show();
            $('#zalochat_main').css('flex', 'unset');
        }
    });

    // Display typing with other users
    $('textarea[name="message_content"], input[name="image_upload"], input[name="file_upload"]').on('input', function() {
        let zalo_id = $('#content_chat').attr('zalo_id');
        let value = $(this).val();

        if(zalo_id && zalo_id.length > 0 && value && value.length > 0) {
            let event = {
                sender : {
                    id: OA_ID,
                    admin_id: ADMIN_ID,
                    admin_name: ADMIN_NAME
                },
                recipient : {
                    id: zalo_id
                },
                message : {
                    msg_id: '',
                    text: 'đang soạn tin'
                },
                event_name: "oa_typing",
                timestamp: Date.now()
            }

            if(zsocket) zsocket.send(JSON.stringify(event));
        }
    });

    // Edit alias
    $('#edit_alias').click(function () {
        $('.item-title.nickname').hide();
        $('.form-edit-name').css('display', 'flex');
        let alias = $('#header_name_chat').text();
        $('input[name="alias_edit"]').val(alias);
        $('.input_sub').text(`${alias.length}/250`);
    });
    $('input[name="alias_edit"]').on('input', function() {
        if($(this).val().length == 0) $('#save_alias_edit').prop('disabled', true);
        else $('#save_alias_edit').prop('disabled', false);
        $('.input_sub').text(`${$(this).val().length}/250`);
    });
    $('#cancel_alias_edit').click(function () {
        $('.form-edit-name').hide();
        $('.item-title.nickname').css('display', 'flex');
    });
    $('#save_alias_edit').click(function () {
        let zalo_id = $('#content_chat').attr('zalo_id');
        let alias = $('input[name="alias_edit"]').val().trim();

        if(zalo_id && alias && zalo_id.length > 0 && alias.length > 0) {
            $.ajax({
                url: URL,
                type: "POST",
                data: {
                    action : "update_user_alias",
                    zalo_id : zalo_id,
                    alias : alias
                },
                beforeSend: function() {
                    $('.container-waiting').show();
                },
                success: function (response) {
                    $('.container-waiting').hide();

                    res = JSON.parse(response); // Object
        
                    if(res['error'] === 0) {
                        $('#header_name_chat').text(alias); // Name chat
                        $('#profile_zalo_alias').text(alias); // In profile
                        $(`#li${zalo_id} .info_content .mess_name`).text(alias); // List user
                    }
                    else {
                        let m = res['message'] ? res['message'] : 'Thao tác thất bại';
                        let d = res['description'] ? res['description'] : '';
                        showModalNotify('error', m, d);
                        return false;
                    }

                    $('.form-edit-name').hide();
                    $('.item-title.nickname').css('display', 'flex');
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $('.container-waiting').hide();
                    showModalNotify('error', 'Thao tác thất bại, vui lòng thử lại');
                    console.error(XMLHttpRequest);
                    console.error("Status: " + textStatus);
                    console.error("Error: " + errorThrown);
                }
            });
        }
    });

    // Edit user info
    $('#btn_update_user_info').click(function () {
        let current_name        = $('#profile_zalo_name').attr('data');
        let current_phone       = $('#profile_mobile').text() != 'Chưa công khai' ? $('#profile_mobile').text() : '';
        let current_address     = $('input[name="profile_address_number"]').val();
        let current_city        = $('input[name="profile_address_city"]').val();

        $('input[name="info_user_id"]').val($('#content_chat').attr('zalo_id'));
        $('input[name="info_user_name"]').val(current_name);
        $('input[name="info_user_phone"]').val(current_phone);
        $('textarea[name="info_user_address"]').val(current_address);
        $('select[name="info_user_city"] > option').each(function() {
            if(this.value == current_city || this.text.indexOf(current_city) !== -1) {
                $('select[name="info_user_city"]').val(this.value);
                $('select[name="info_user_city"]').change();
                return false;
            }
        });
    });
    $('select[name="info_user_city"]').change(function() {
        let city_id = $(this).val();
        let current_district = $('input[name="profile_address_district"]').val();

        if(city_id && city_id.length > 0) {
            $.ajax({
                url: 'index.php?entryPoint=entryPointAddressHandling',
                type: "POST",
                data: {
                    action: "get_districts",
                    parent_value: city_id
                },
                contentType: "application/x-www-form-urlencoded; charset=utf-8",
                beforeSend: function () {
                    $(`select[name="info_user_district"]`).prop("disabled", true);
                },
                success: function (response) { // JSON
                    let obj = JSON.parse(response);
                    if (obj.error === 0) {
                        let options = '';
                        $.each(obj.data, function (key, name) {
                            if (current_district && (key == current_district || name.indexOf(current_district) !== -1)) options += `<option value="${key}" selected>${name}</option>`;
                            else options += `<option value="${key}">${name}</option>`
                        });
                        $(`select[name="info_user_district"]`).html(options);
                        $(`select[name="info_user_district"]`).prop("disabled", false);
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    console.error(XMLHttpRequest);
                }
            });
        }
    });
    $('#save_user_info').click(function () {
        let zalo_id     = $('input[name="info_user_id"]').val();
        let name        = $('input[name="info_user_name"]').val();
        let phone       = $('input[name="info_user_phone"]').val();
        let address     = $('textarea[name="info_user_address"]').val();
        let city_id     = $('select[name="info_user_city"]').val();
        let district_id = $('select[name="info_user_district"]').val();

        if(name.length*phone.length*address.length*district_id.length*city_id.length == 0) {
            showModalNotify('warning', 'Vui lòng điền đầy đủ các trường');
            return;
        }

        $.ajax({
            url: URL,
            type: "POST",
            data: {
                action: "update_user_info",
                zalo_id: zalo_id,
                info_user_phone: phone,
                info_user_name: name,
                info_user_address: address,
                info_user_city: city_id,
                info_user_district: district_id,
            },
            contentType: "application/x-www-form-urlencoded; charset=utf-8",
            beforeSend: function () {
                $('.container-waiting').show();
            },
            success: function (response) { // JSON
                $('.container-waiting').hide();
                let obj = JSON.parse(response);

                if(obj['error'] === 0) {
                    $('#close_save_user_info').click();
                    showModalNotify('success', 'Cập nhật thành công');

                    $('#profile_zalo_name').attr('data', name);
                    $('#profile_zalo_name').text(`Tên Zalo: ${name}`);
                    $('#profile_mobile').text(phone);
                    $('input[name="profile_address_city"]').val(city_id);
                    $('input[name="profile_address_district"]').val(district_id);
                    $('input[name="profile_address_number"]').val(address);
                    let address_full = address + ', ' + $(`option[value="${district_id}"]`).text() + ', ' + $(`option[value="${city_id}"]`).text();
                    $('#profile_address').text(address_full);
                }
                else showModalNotify('error', 'Vui lòng thử lại');
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                $('.container-waiting').hide();
                showModalNotify('error', 'Vui lòng thử lại');
                console.error(XMLHttpRequest);
            }
        });
    });

    // Choose tag user 
    $(document).on("click", "#header_tags_chat .item_tag", function() {
        let zalo_id = $('#content_chat').attr('zalo_id');
        let checkbox = $(this).find('input[name="checkbox_tag"]');
        let tag_name = checkbox.val();
        let current_tag = $(`#user_tag_display .title`).text();

        if(tag_name === current_tag) return;
        if(checkbox.is(":checked")) remove_tag_user(zalo_id, tag_name);
        else add_tag_user(zalo_id, tag_name);

        // Display new tag in header user
        $(`#user_tag_display .title`).text(tag_name);
        $(`#user_tag_display`).attr('data', tag_name);
        // Display new tag in list user
        $(`#li${zalo_id} .tag-content .tag`).text(tag_name);
        $(`#li${zalo_id} .tag-content .tag`).attr('data', tag_name);
    });

    // Search user
    $('#search_user').click(function () {
        $('.chat-sidebar__top > .type').hide();
        $('.chat-sidebar__top > .search-icon').hide();
        $('.chat-sidebar__top > .search-processing').show();
    });
    $('.close-search-processing').click(function () {
        $('.chat-sidebar__top > .type').show();
        $('.chat-sidebar__top > .search-icon').show();
        $('.chat-sidebar__top > .search-processing').hide();

        $('#list_mess_main').show();
        $('#list_mess_search').hide();
    });
    $(`input[name="search_user"]`).on('keydown', function (e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            let search_value = $(this).val().trim();

            if(!search_value || search_value.length == 0) return false;

            $.ajax({
                url: URL,
                type: "POST",
                data: {
                    action: "search_contact",
                    search_value: search_value
                },
                beforeSend: function() {
                    $('#list_mess_main').hide();
                    $('#list_mess_search').html('<div class="loader_list_user mt-3"></div>').show();
                },
                success: function (response) { // HTML
                    $('#loader_list_user').remove();

                    if (response && response.length > 0) {
                        $('#list_mess_search').html(response);
                    }
                    else {
                        $('#list_mess_search').html(`<li class="no-results-found">Không tìm thấy kết quả vui lòng tìm kiếm với từ khóa khác</li>`);
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $('#loader_list_user').remove();
                    showModalNotify('error', 'Vui lòng thử lại');
                    console.error(XMLHttpRequest);
                    console.error("Status: " + textStatus);
                }
            });
        }
        // Backspace
        else if(e.keyCode === 8 && $(this).val().length == 0) {
            $('#list_mess_main').show();
            $('#list_mess_search').hide();
        }
    });
});

function connectWebSocket() {
    if(!OA_ID || OA_ID.length === 0) return false;

    zsocket = new WebSocket(URL_CHAT_WEBSOCKET);

    zsocket.onopen = function(e) {
        console.log("ZALO_SOCKET: Connection established!");
        count_connect_error = 0;
    };
    
    zsocket.onmessage = function(e) {
        if(!e.data) return false;

        data = JSON.parse(e.data);
        let event = data['event_name'] ? data['event_name'] : '';
        let mid = data['message']['msg_id'] ? data['message']['msg_id'] : '';
        let app_id = data['app_id'] ? data['app_id'] : '';
        let sender_id = data['sender']['id'] ? data['sender']['id'] : '';
        let recipient_id = data['recipient']['id'] ? data['recipient']['id'] : '';
        let timestamp = data['timestamp'] ? data['timestamp'] : 0;

        /**********  USER EVENT  **********/
        if(USER_EVENT_LIST.includes(event)) {
            let src = 1;
            let type = event.replace("user_send_", "");
            let message = data['message']['text'] ? data['message']['text'] : '';
            let quote_id = data['message']['quote_msg_id'] ? data['message']['quote_msg_id'] : '';
            let current_user_id_chat = $('#content_chat').attr('zalo_id');

            // The event belongs to the user who is texting
            if(sender_id === current_user_id_chat) {
                let avatar = $('#header_avatar_chat').attr('src');
                let previous_sender_id = $('#section-message__details .section-item:last').attr('sender_id');
                let previous_timestamp = $('#section-message__details .section-item:last').attr('timestamp');
                
                // Basic field
                let obj = {
                    'src' : src,
                    'time' : timestamp,
                    'type' : type,
                    'message' : message,
                    'message_id' : mid,
                    'quote_id' : quote_id,
                    'from_id' : sender_id,
                    'from_avatar' : avatar,
                    'previous_sender_id' : previous_sender_id,
                    'previous_timestamp' : previous_timestamp
                };

                // Add additional fields 
                let attachments = data['message']['attachments'] ? data['message']['attachments'] : null;
                if(attachments) {
                    if(type == 'image' || type == 'photo' || type == 'gif' || type == 'sticker') {
                        obj['url'] = attachments[0]['payload']['url'];
                    }
                    else if(type == 'audio' || type == 'voice') {
                        obj['url'] = attachments[0]['payload']['url'];
                    }
                    else if(type == 'video') {
                        obj['url'] = attachments[0]['payload']['url'];
                        obj['thumb'] = attachments[0]['payload']['thumbnail'];
                        obj['description'] = attachments[0]['payload']['description'];
                    }
                    else if(type == 'file') {
                        obj['file'] = attachments[0]['payload'];
                    }
                    else if(type == 'link' || type == 'links') {
                        obj['links'] = [];
                        $.each(attachments , function(index, att) {
                            let url = att.payload.url;
                            let title = att.payload.title;
                            let thumb = att.payload.thumbnail;
                            let description = att.payload.description;
                            obj['links'].push({'url':url, 'title':title, 'thumb':thumb, 'description':description});
                        });
                    }
                    else if(type == 'location') {
                        obj['location'] = attachments[0]['payload']['coordinates'];
                    }
                }

                let row = create_chat_row(obj, 'new');
                $(`#section-message__details`).append(row);
                $(`#section-message__details .section-item:last .snippet .borHak`).show();

                let message_format = create_li_chat(obj, {}, true);
                $(`#li${sender_id} .lastest_message`).html(message_format);
                $(`#li${sender_id} .mess_time`).text(formatTimestampZalo(timestamp, '', 'H:i'));

                // Update quota user
                handle_quota_user(8, timestamp);

                scroll_messages_bottom();
            }
            // The event belongs to the user currently on the list
            else if($(`#li${sender_id}`).length) {
                let sender = $(`#li${sender_id}`);
                let num = sender.find('.mess_number').text() ? sender.find('.mess_number').text() : 0;

                let messageObj = {
                    src : src,
                    type : type,
                    time : timestamp,
                    message : message,
                    num : parseInt(num) + 1
                }

                let userObj = {
                    id : sender_id,
                    name : sender.find('.mess_name').text(),
                    avatar : sender.find('.avatar-user-list').attr('src')
                }

                let li = create_li_chat(messageObj, userObj);
                sender.remove();
                $('#list_mess_main').prepend(li);
            }
            // The event belongs to the new user
            else {
                if($('input[name="user_type_list"]').val() !== 'default') return false;
                create_li_chat_new(src, type, message, timestamp, sender_id);
            }
        }
        /**********  OA EVENT  **********/
        else if(OA_EVENT_LIST.includes(event)) {
            let src = 0;
            let type = event.replace("oa_send_", "");
            let message = data['message']['text'] ? data['message']['text'] : '';
            let quote_id = data['message']['quote_msg_id'] ? data['message']['quote_msg_id'] : '';
            let current_user_id_chat = $('#content_chat').attr('zalo_id');

            // The event belongs to the user who is texting
            if(recipient_id === current_user_id_chat) {
                let previous_sender_id = $('#section-message__details .section-item:last').attr('sender_id');
                let previous_timestamp = $('#section-message__details .section-item:last').attr('timestamp');

                // Basic field
                let obj = {
                    'src' : src,
                    'time' : timestamp,
                    'type' : type,
                    'message' : message,
                    'message_id' : mid,
                    'quote_id' : quote_id,
                    'from_id' : OA_ID,
                    'from_avatar' : OA_AVATAR,
                    'previous_sender_id' : previous_sender_id,
                    'previous_timestamp' : previous_timestamp
                };

                // Add additional fields 
                let attachments = data['message']['attachments'] ? data['message']['attachments'] : null;
                if(attachments) {
                    if(type == 'image' || type == 'photo' || type == 'gif' || type == 'sticker') {
                        obj['url'] = attachments[0]['payload']['url'];
                    }
                    else if(type == 'file') {
                        obj['file'] = attachments[0]['payload'];
                    }
                    else if(type == 'list') {
                        obj['links'] = [];
                        $.each(attachments , function(index, att) {
                            let url = att.payload.url;
                            let title = att.payload.title;
                            let thumb = att.payload.thumbnail;
                            let description = att.payload.description;
                            obj['links'].push({'url':url, 'title':title, 'thumb':thumb, 'description':description});
                        });
                        obj.type = 'links';
                    }
                }

                let row = create_chat_row(obj, 'new');
                $(`#section-message__details`).append(row);
                $(`#section-message__details .section-item:last .snippet .borHak`).show();

                let message_format = create_li_chat(obj, {}, true);
                $(`#li${recipient_id} .lastest_message`).html(message_format);
                $(`#li${recipient_id} .mess_time`).text(formatTimestampZalo(timestamp, '', 'H:i'));

                scroll_messages_bottom();
            }
            // The event belongs to the user currently on the list
            else if($(`#li${recipient_id}`).length) {
                let recipient = $(`#li${recipient_id}`);

                let messageObj = {
                    src : src,
                    type : type,
                    time : timestamp,
                    message : message,
                }

                let userObj = {
                    id : recipient_id,
                    name : recipient.find('.mess_name').text(),
                    avatar : recipient.find('.avatar-user-list').attr('src')
                }

                let li = create_li_chat(messageObj, userObj);
                recipient.remove();
                $('#list_mess_main').prepend(li);
            }
            // The event belongs to the new user
            else {
                if($('input[name="user_type_list"]').val() !== 'default') return false;
                create_li_chat_new(src, type, message, timestamp, recipient_id);
            }
        }
        /**********  OA EVENT CUSTOM **********/
        else if(event == 'oa_typing') {
            let sender_admin_id = data['sender']['admin_id'] ? data['sender']['admin_id'] : '';
            let current_user_id_chat = $('#content_chat').attr('zalo_id');

            if(sender_admin_id !== ADMIN_ID && recipient_id === current_user_id_chat) {
                let sender_admin_name = data['sender']['admin_name'] ? data['sender']['admin_name'] : '';
                let admin_typing = $('#admin_typing').text();

                if(admin_typing.length > 0) {
                    // Check
                    let check = true;
                    let arr_admin_typing = admin_typing.split(',');
                    arr_admin_typing.forEach(function (item, index) {
                        if(sender_admin_name === item.trim()) check = false;
                    });

                    if(check) $('#admin_typing').text(`${admin_typing}, ${sender_admin_name}`);
                }
                else $('#admin_typing').text(sender_admin_name);
                $('.display-admin-typing').show();

                // Hide after 2 seconds
                setTimeout(function() {
                    $('#admin_typing').text('');
                    $('.display-admin-typing').hide();
                }, 2000);
            }
        }
    };

    zsocket.onerror = function(error) {
        console.error('ZALO_SOCKET: ', error);
    };
    
    zsocket.onclose = function(event) {
        if (event.wasClean) {
            console.log(`ZALO_SOCKET: WebSocket connection closed cleanly, code=${event.code} reason=${event.reason}`);
        }
        else {
            console.error('ZALO_SOCKET: WebSocket connection closed unexpectedly', event);

            if(count_connect_error < 10) {
                count_connect_error++;
                setTimeout(connectWebSocket, 3000); // Retry after 3 seconds
            }

            if(count_connect_error == 5) {
                showModalNotify('error', 'Kết nối thất bại, vui lòng thử lại');
            }
        }
    };
}

/**
 * Create chat box with user
 * 
 * @param {Object} data_user
 * @param {Object} data_message
 * @returns 
 */
function create_chat_box(data_user, data_message, is_return = false) {
    if(data_user) {
        let chat_link = data_user['chat_link'] ? data_user['chat_link'] : "#";
        let avatar = data_user['avatar'] ? data_user['avatar'] : DEFAULT_AVATAR;
        let name = data_user['display_name'] ? data_user['display_name'] : '';
        let alias = data_user['user_alias'] ? data_user['user_alias'] : name;
        let is_follower = data_user['user_is_follower'] ? data_user['user_is_follower'] : false;
        let shared_info = data_user['shared_info'] ? data_user['shared_info'] : [];
        let tags = (data_user['tags_and_notes_info'] && data_user['tags_and_notes_info']['tag_names']) ? data_user['tags_and_notes_info']['tag_names'] : [];


        /******  1.1 Header chat  ******/
        // DOM avatar
        $('#header_avatar_chat').attr('src', avatar);

        // DOM name
        $('#header_name_chat').text(alias);

        // DOM follow
        let is_follower_text = is_follower ? 'Đã quan tâm' : 'Chưa quan tâm';
        let is_follower_class = is_follower ? 'user-followed' : 'user-unfollowed';
        $('#header_follow_chat').text(is_follower_text);
        $('#header_follow_chat').removeClass();
        $('#header_follow_chat').addClass(is_follower_class);

        // DOM tags
        $(`input.checkbox_tag`).prop('checked', false);
        if(tags.length > 0) {
            for (i = 0; i < tags.length; ++i) {
                $(`input.checkbox_tag[value="${tags[i]}"]`).prop('checked', true);
                $(`input.checkbox_tag[value="${tags[i]}"]`).prop('disabled', 'disabled');
                $(`#user_tag_display .title`).text(tags[i]);
                $(`#user_tag_display`).attr('data', tags[i]);
            }
        }


        /******  1.2 Profile  ******/
        // DOM avatar
        $('#profile_avatar').attr('src', avatar);

        // DOM alias, name
        $('#profile_zalo_alias').text(alias);
        $('#profile_zalo_name').text(`Tên Zalo: ${name}`);
        $('#profile_zalo_name').attr('data', name);

        // DOM zalo id to call
        $('.func-call').attr('zalo-id', data_user['user_id']);

        // DOM chat link
        $('#profile_chat_link').attr('href', chat_link);

        // DOM address
        let city = shared_info['city'] && shared_info['city'].length > 0 ? shared_info['city'] : '';
        let district = shared_info['district'] && shared_info['district'].length > 0 ? shared_info['district'] : '';
        let address_number = shared_info['address'] ? shared_info['address'] : '';
        let address = '';
        address += address_number;
        address += district.length == 0 ? '' : `, ${district}`;
        address += city.length == 0 ? '' : `, ${city}`;
        address = address.length == 0 ? 'Chưa công khai' : address;
        $('#profile_address').text(address);
        $('input[name="profile_address_city"]').val(city);
        $('input[name="profile_address_district"]').val(district);
        $('input[name="profile_address_number"]').val(address_number);

        // DOM mobile
        let mobile = shared_info['phone'] ? shared_info['phone'].toString() : '';
        if(mobile.length == 0) $('#profile_mobile').text('Chưa công khai');
        else $('#profile_mobile').text(formatPhoneNumberZalo(mobile));
    }

    let html = '';
    if (data_message) {
        let previous_message_id = null;
        let previous_sender_id = null;
        let previous_timestamp = null;
        $.each(data_message.slice().reverse(), function (key, valueObj) {
            if(html.length == 0) html = `<div id="top_chat" class="_section-mt"><div class="loader_messages" style="display:none"></div></div>`;

            let mid = valueObj.message_id ? valueObj.message_id : '';
            let sender_id = valueObj.from_id ? valueObj.from_id : '';
            let timestamp = valueObj.time ? valueObj.time : 0;

            valueObj['previous_sender_id'] = previous_sender_id;
            valueObj['previous_timestamp'] = previous_timestamp;
            let row = create_chat_row(valueObj);

            // Handling time message 
            if (previous_sender_id && (sender_id !== previous_sender_id || row.includes("_sectionTimestamp"))) {
                html = html.replace(`{{style-borHak-${previous_message_id}}}`, `style="display:block"`);
            }

            // Handling padding
            if (previous_sender_id && sender_id === previous_sender_id) {
                html = html.replace(`{{style-item-bottom-${previous_message_id}}}`, 'padding-bottom:0;');
                row  = row.replace(`{{style-item-top-${mid}}}`, 'padding-top:0;');
            }

            // Handling avatar
            if (previous_sender_id && sender_id === previous_sender_id) {
                row = row.replace(`{{style-avatar-${mid}}}`, `style="visibility:hidden"`);
            }

            html += row;

            previous_message_id = mid;
            previous_sender_id = sender_id;
            previous_timestamp = timestamp;
        });
    }

    if(is_return) return html;

    $(`#section-message__details`).html(html);
    $(`#section-message__details .section-item:last .snippet .borHak`).show();
    setTimeout(scroll_messages_bottom(), 500);
}

/**
 * Create HTML chat row in chat box
 * 
 * @param {Object} obj
 * @param {String} ctype load, new
 * @returns {String} HTML
 */
function create_chat_row(obj, ctype = 'load') {
    let mid = obj.message_id ? obj.message_id : '';
    let quote_id = obj.quote_id ? obj.quote_id : '';
    let type = obj.type ? obj.type : '';
    let message = obj.message ? formatText(obj.message) : '';
    let src = obj.src ? obj.src : 0;
    let timestamp = obj.time ? parseInt(obj.time) : 0;
    let sent_time = formatTimestampZalo(timestamp, 'd/m/Y', 'H:i:s', 'time');
    let sender_id = obj.from_id ? obj.from_id : '';
    let avatar = obj.from_avatar ? obj.from_avatar : DEFAULT_AVATAR;
    let previous_sender_id = obj.previous_sender_id ? obj.previous_sender_id : null;
    let previous_timestamp = obj.previous_timestamp ? obj.previous_timestamp : null;
    let timeout = ctype == 'load' ? 60 * 60 * 1000 : 20 * 60 * 1000;
    let html = '';

    // Time line
    let datetimeline    = new Date(timestamp);
    let dateofweek      = map_date_of_week_zalo(datetimeline.getDay());
    let timeline        = getSentTimeZalo(sent_time);
    let text_timeline   = `${timeline} ${dateofweek}, ` + sent_time.split(' ')[1];
    let new_timeline    = false;
    if (previous_sender_id && sender_id != previous_sender_id) {
        if (previous_timestamp && timestamp - previous_timestamp > timeout) {
            html += `<div class="_sectionTimestamp"><span>${text_timeline}</span></div>`;
            new_timeline = true;
        }
    }
    else if(previous_sender_id && sender_id == previous_sender_id) {
        if (previous_timestamp && timestamp - previous_timestamp > timeout) {
            html += `<div class="_sectionTimestamp"><span>${text_timeline}</span></div>`;
            new_timeline = true;
        }
    }
    

    // Content
    let content = '', classnamepicture = '';
    if (type == 'text') {
        content += `<span class="content"><span>${message}</span></span>`;
    }
    else if (type == 'photo' || type == 'image' || type == 'gif') {
        let url = obj.url ? obj.url : '#';
        if(message.length == 0) message = obj.description ? obj.description.trim() : '';
        let text = message.length > 0 ? `<span class="content"><span>${message}</span></span>` : '';
        classnamepicture = text.length > 0 ? 'picture_text' : '';

        content += `
            <div class="content-picture item">
                <img src="${url}">
                ${text}
            </div>
        `;
    }
    else if (type == 'sticker') {
        let url = obj.url ? obj.url : '#';
        content += `<img src="${url}">`;
    }
    else if (type == 'voice' || type == 'audio') {
        let url = obj.url ? obj.url : '#';
        content += `
            <div class="sound-container">
                <a class="func-play control" href="${url}" target="_blank" title="Play">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-play-circle-fill" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M6.79 5.093A.5.5 0 0 0 6 5.5v5a.5.5 0 0 0 .79.407l3.5-2.5a.5.5 0 0 0 0-.814z"/>
                    </svg>
                </a>
                <div class="ic_load">
                    <div class="line"></div>
                    <div class="line"></div>
                    <div class="line"></div>
                </div>
                <span class="timer">00:00</span>
            </div>
        `;
    }
    else if (type == 'video') {
        let url = obj.url ? obj.url : '#';
        content += `
            <video id="video" width="320" controls="">
                <source src="${url}" type="video/mp4">
                <source src="${url}" type="video/ogg">
            </video>
        `;
    }
    else if (type == 'file') {
        let file = obj.file ? obj.file : null;
        if(file) {
            let file_size = file.size ? formatFileSize(parseInt(file.size)) : '';
            let file_name = file.name ? file.name : '';
            let file_type = file.type ? file.type : '';
            let file_url = file.url ? file.url : '#';
            let file_image = getImageFile(file_type);

            content += `
                <div class="card-container">
                    <div class="avatar avatar--m avatar-square">
                        <div class="avatar-img">
                            <img src="${file_image}">
                        </div>
                    </div>
                    <div class="desc-content">
                        <div class="title"><span>${file_name}</span></div>
                        <div>${file_size}</div>
                        <a title="Mở tập tin" href="${file_url}" target="_blank" download="${file_url}">Xem tệp tin</a>
                    </div>
                </div>
            `;
        }
    }
    else if (type == 'location') {
        let location = obj.location ? obj.location : '';
        if(typeof location !== 'object') location = JSON.parse(location);
        let latitude  = location.latitude ? location.latitude : '';
        let longitude = location.longitude ? location.longitude : '';

        content += `
            <div class="content">
                <div>
                    <iframe title="map" width="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=${latitude},${longitude}&amp;hl=es;z=14&amp;output=embed"></iframe>
                </div>
            </div>
        `;
    }
    else if (type == 'link' || type == 'links') {
        let links = obj.links ? obj.links : null;
        $.each(links , function(index, link) { 
            let title = link.title ? link.title : '';
            let url = link.url ? link.url : '';
            let thumb = link.thumb ? link.thumb : '';
            let description = link.description ? link.description : '';

            // Business card
            if(isJSON(description)) {
                let card = JSON.parse(description);

                content += `
                    <div class="card business-card bg-primary text-white">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-8 col-8 left">
                                    <img class="avatar" src="${thumb}" />
                                    <div class="info">
                                        <p>${title}</p>
                                        <span id="card_phone" class="card-phone">${card.phone}</span>
                                    </div>
                                </div>
                                <div class="col-sm-4 col-4 right">
                                    <img class="qrcode" src="${card.qrCodeUrl}" />
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
            else {
                content += `
                    <div class="product-container">    
                        <div class="main-product">
                            <a class="img" style="background-image: url('${thumb}');" href="${url}" target="_blank"></a>
                            <h6 class="title mt-2 mb-2 p-0" style="font-size:0.8rem">${title}</h6>
                            <p class="desc">${description}</p>
                        </div>
                    </div>
                `;
            }
        });
    }
    else content = '<i>Tin nhắn chưa hỗ trợ</i>';

    // Display
    let classname = src == 1 ? 'mess_filter ': 'me';
    let style_item = `{{style-item-top-${mid}}}; {{style-item-bottom-${mid}}}`;
    let style_avatar = !new_timeline ? `{{style-avatar-${mid}}}` : '';
    let style_borHak = `{{style-borHak-${mid}}}`;
    if(ctype == 'new' && previous_sender_id && previous_sender_id == sender_id) {
        let previous_message_id = $(`#section-message__details .section-item:last`).attr('id');

        // Avatar
        if(!new_timeline) style_avatar = 'style="visibility:hidden"';

        // Time
        $(`#${previous_message_id} .snippet .borHak`).hide();

        // Padding
        style_item = 'padding-top:0';
        $(`#${previous_message_id}`).css('padding-bottom', '0');
    }

    // Quote
    let quote_html = '', classquote = '';
    if(quote_id.length > 0) {
        classquote = 'quote';
        quote_html = create_quote_content(quote_id);
    }

    html += `
        <div id="mess-${mid}" class="section-item ${classname}" sender_id="${sender_id}" timestamp="${timestamp}" style="${style_item}">
            <div class="avatar avatar--sm" ${style_avatar}>
                <span class="avatar-img" style="background-image: url('${avatar}');"></span>
            </div>
            <div class="item-content">
                <div class="item-headline-container">
                    <div class="snippet">
                        <div class="admin-message-wrapper">
                            <div class="message-container">
                                <div class="item-message ${getClassMessage(type)} ${classnamepicture} ${classquote}">
                                    ${quote_html}
                                    ${content}
                                    <div class="more ${classname}">
                                        <div class="inner">
                                            <div class="btn_quote" data-message-id="${mid}">
                                                <i class="icon icon_quote_black">
                                                    ${getIcons('quote', '#808080', '18px', '18px')}
                                                </i>
                                            </div>
                                        </div>   
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="borHak ${src == 0 ? 'me' : ''}" ${style_borHak}>
                            <span class="">${timeline}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    return html;
}

/**
 * Create HTML quote in chat box
 * 
 * @param {String} quote_id
 * @returns {String} HTML
 */
function create_quote_content(quote_id) {
    let check = false;
    let name = '', img = '', text = ''; 

    if($(`#mess-${quote_id}`).length) {
        let src = $(`#mess-${quote_id}`).hasClass('me') ? 0 : 1;
        name = src == 1 ? $('#profile_zalo_name').attr('data') : OA_NAME;
        let element = $(`#mess-${quote_id} .item-message`);

        if(element.hasClass('message_notfound')) {
            text = element.find('.content span').text();
            check = true;
        }
        else if(element.hasClass('product')) {
            let src = element.find('.img').css("background-image");
            src = src.substring(str.indexOf("'") + 1, str.lastIndexOf("'"));
            img = `<img src="${src}" alt="Image reply">`;
            text = '[Tin liên kết]';
            check = true;
        }
        else if(element.hasClass('picture') || element.hasClass('sticker')) {
            let src = element.find('img').attr('src');
            img  = `<img src="${src}" alt="Image reply">`;
            text = '[Hình ảnh]';
            check = true;
        }
        else if(element.hasClass('sticker')) {
            let src = element.find('img').attr('src');
            img  = `<img src="${src}" alt="Image reply">`;
            text = '[Sticker]';
            check = true;
        }
        else if(element.hasClass('location-message')) {
            text = '[Vị trí]';
            check = true;
        }
        else if(element.hasClass('voice-message')) {
            text = '[Tin nhắn thoại]';
            check = true;
        }
    }

    if(!check) return '';
    return `
        <div class="quote-content">
            <div class="line"></div>
            <div class="app__quote-content">
                ${img}
                <div>
                    <div class="user-name">${name}</div>
                    <div class="user-content">
                        <span>${text}</span>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Create HTML <li> for list user chat
 * 
 * @param {Object} message_data
 * @param {Object} user_data
 * @param {Boolean} return_only_content 
 * @returns {String} HTML
 */
function create_li_chat(message_data, user_data, return_only_content = false) {
    // Message data
    let type    = message_data.type ? message_data.type : '';
    let prefix  = message_data.src === 0 ? '<span style="margin-right:4px">OA:</span>' : '';
    let text    = message_data.message ? message_data.message : '';
    let time    = message_data.time ? formatTimestampZalo(message_data.time, '', 'H:i') : '';
    let num     = message_data.num ? message_data.num : '';
    // User data
    let zalo_id = user_data.id ? user_data.id : '';
    let name    = user_data.name ? user_data.name : '';
    let avatar  = user_data.avatar ? user_data.avatar : DEFAULT_AVATAR;
    let tags    = user_data.tags_and_notes_info ? user_data.tags_and_notes_info.tag_names : [];
    let html_tags = tags.length > 0 ? '<div class="tag-content mt-1">' : '<div class="tag-content">';
    $.each(tags , function(index, t) { html_tags += `<div class="tag" data="${t}">${t}</div>`; });
    html_tags += '</div>';

    let content = '';
    if(type == 'text') {
        content = `<div class="lastest_message">${prefix + text}</div>`;
    }
    else if(type == 'photo' || type == 'image') {
        content = `
            <div class="lastest_message">
                ${prefix}
                <div class="icon icon_${type}">${getIcons('image', '#8D8D8F', 20, 21)}</div>
                Hình ảnh
            </div>
        `;
    }
    else if(type == 'gif') {
        content = `
            <div class="lastest_message">
                ${prefix}
                <div class="icon icon_${type}">${getIcons('gif', '#8D8D8F', 20, 17)}</div>
                GIF
            </div>
        `;
    }
    else if(type == 'sticker') {
        content = `
            <div class="lastest_message">
                ${prefix}
                <div class="icon icon_${type}">${getIcons('sticker', '#8D8D8F', 20, 21)}</div>
                Sticker
            </div>
        `;
    }
    else if(type == 'voice' || type == 'audio') {
        content = `
            <div class="lastest_message">
                ${prefix}
                <div class="icon icon_${type}">${getIcons('voice', '#8D8D8F', 20, 21)}</div>
                Tin nhắn thoại
            </div>
        `;
    }
    else if(type == 'video') {
        content = `
            <div class="lastest_message">
                ${prefix}
                <div class="icon icon_${type}">${getIcons('video', '#8D8D8F')}</div>
                Video
            </div>
        `;
    }
    else if(type == 'file') {
        content = `
            <div class="lastest_message">
                ${prefix}
                <div class="icon icon_${type}">${getIcons('file', '#8D8D8F', '20px', '18px')}</div>
                Tệp đính kèm
            </div>
        `;
    }
    else if(type == 'link' || type == 'links') {
        content = `<div class="lastest_message">${prefix}[Tin liên kết]</div>`;
    }
    else if(type == 'location') {
        content = `
            <div class="lastest_message">
                ${prefix}
                <div class="icon icon_${type}">${getIcons('location', '#8D8D8F', 20, 21)}</div>
                Vị trí
            </div>
        `;
    }
    else {
        content = `<div class="lastest_message">${prefix}Bạn có một tin nhắn mới</div>`;
    }

    if(return_only_content) return content;
    return `
        <li class="item_mess item_mess_new mess_links" id="li${zalo_id}">
            <div class="mess_avt">
                <div class="imgDrop">
                    <img class="avatar-user-list" src="${avatar}" alt="Avatar user" />
                </div>
            </div>
            <div class="__content">
                <div class="info_content">  
                    <div class="mess_content">
                        <div class="mess_name truncate">${name}</div>
                    </div>
                    <div class="mess_more has_btn_more">
                        <div class="mess_time">${time}</div>
                        <div class="mess_number">${num}</div>
                    </div>
                </div>
                <div class="box-parent box-lastest_message">
                    ${content}
                </div>
                ${html_tags}
            </div>
        </li>
    `;
}

/**
 * Create HTML <li> for new user chat
 * 
 * @param {Int} src
 * @param {String} type
 * @param {String} message
 * @param {String} timestamp
 * @param {String} zalo_id
 * @returns 
 */
function create_li_chat_new(src, type, message, timestamp, zalo_id) {
    if(zalo_id) {
        $.ajax({
            url: URL,
            type: "POST",
            data: {
                action  : "get_user_info",
                oa_id   : OA_ID,
                zalo_id : zalo_id
            },
            success: function (response) {
                obj = JSON.parse(response); // Object
    
                if (obj['error'] == 0) {
                    let value = obj['data'];

                    let user_data = {
                        id : zalo_id,
                        name : value['user_alias'] ? value['user_alias'] : value['display_name'],
                        avatar : value['avatar']
                    };

                    let message_data = {
                        src  : src,
                        type : type,
                        time : timestamp,
                        message : message,
                        num : 1
                    };

                    let li = create_li_chat(message_data, user_data);
                    $('#list_mess_main').prepend(li);
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                console.error(XMLHttpRequest);
                console.error("Status: " + textStatus);
                console.error("Error: " + errorThrown);
            }
        });
    }
}

/**
 * Generate form data to send message
 * 
 * @return {object}
 */
function generate_form_data() {
    let zalo_id = $(`#content_chat`).attr(`zalo_id`);
    let text = $(`textarea[name="message_content"]`).val().trim();
    let input_image = $('input[name=image_upload]');
    let input_file = $('input[name=file_upload]');

    let formData = new FormData();
    formData.append('action', 'send_message');
    formData.append('oa_id', OA_ID);
    formData.append('zalo_id', zalo_id);
    formData.append('text', text);

    /*****  2. Type message  *****/
    let type = 'text'; // Default
    // Tin nhắn trả lời (Quote)
    if($('input[name="quote_message_id"]').length) {
        formData.append('quote_message_id', $('input[name="quote_message_id"]').val());
    }
    else if(input_image[0].files.length > 0) {
        type = 'image';
        let image = input_image[0].files[0];
        let url   = $('#preview_image_upload').attr('src')
        formData.append('image', image);
        formData.append('url', url);
    }
    else if(input_file[0].files.length > 0) {
        type = 'file';
        let file = input_file[0].files[0];
        formData.append('file', file);
    }
    formData.append('type', type);

    return formData;
}

/**
 * Send message (consultation) to zalo id
 * 
 * @param {object} data Form data
 */
function send_message(data) {
    if(data) {
        $.ajax({
            url: URL,
            type: "POST",
            data: data,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $('.loader_send_message').remove();
                $('#section-message__details').append('<div class="loader_send_message"></div>');
                scroll_messages_bottom();
            },
            success: function (response) {
                $('.loader_send_message').remove();
                res = JSON.parse(response); // Object
    
                if (res['error'] !== 0) {
                    let m = res['message'] ? res['message'] : 'Thao tác thất bại';
                    let d = res['description'] ? res['description'] : '';
                    showModalNotify('error', m, d);
                    return false;
                }

                // Update quota user
                if(res['data']['quota']) {
                    let quota = res['data']['quota'];
                    if(quota['quota_type'] == 'reply') {
                        handle_quota_user(quota['remain'], '', 0);
                    }
                    else if(quota['quota_type'] == 'sub_quota') {
                        handle_quota_user(0, '', quota['remain']);
                    }
                }
                else handle_quota_user(0, '', 0);
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                $('.loader_send_message').remove();
                showModalNotify('error', 'Thao tác thất bại, vui lòng thử lại');
                console.error(XMLHttpRequest);
                console.error("Status: " + textStatus);
                console.error("Error: " + errorThrown);
            }
        });
    }
}

/**
 * Handle HTML quota for user in chat box and related events
 * 
 * @param {int} cs
 * @param {string} last_interaction
 * @param {int} oa_cs
 * @returns
 */
function handle_quota_user(cs, last_interaction, oa_cs = 0) {
    let quota_html = '';
    let time_check_day = (last_interaction && last_interaction.length > 0) ? ~~((Date.now() - parseInt(last_interaction)) / 1000 / 3600 / 24) : 0;

    if(time_check_day > 6) {
        quota_html = `<div class="noti-mess-feedback noti_grey">
            <span>Không thể gửi tin. Người dùng đã hết tương tác với OA trong vòng 7 ngày gần nhất</span>
        </div>`;
        disable_send_message();
    }
    else if(cs > 0) {
        enable_send_message();
        quota_html = `<div class="noti-mess-feedback noti_green">
            <span>Tin nhắn tiếp theo được miễn phí</span>
        </div>`;
    }
    else if(oa_cs > 0) {
        enable_send_message();
        quota_html = `<div class="noti-mess-feedback noti_blue">
            <span>Tin nhắn tiếp theo được miễn phí (Đặc quyền của OA Premium)</span>
        </div>`;
    }
    else {
        enable_send_message();
        quota_html = `<div class="noti-mess-feedback noti_yellow">
            <span>Mỗi tin nhắn tiếp theo sẽ tốn 55đ/tin</span>
        </div>`;
    }

    $('#quota_content').html(quota_html);
}

/**
 * Add tag name to user
 * 
 * @param {string} zalo_id
 * @param {string} tag_name
 */
function add_tag_user(zalo_id, tag_name) {
    if(zalo_id && tag_name && zalo_id.length > 0 && tag_name.length > 0) {
        $.ajax({
            url: URL,
            type: "POST",
            data: {
                action : 'add_tag_user',
                zalo_id : zalo_id,
                tag_name : tag_name
            },
            beforeSend: function() {
                $('.container-waiting').show();
            },
            success: function (response) {
                $('.container-waiting').hide();

                // res = JSON.parse(response); // Object
    
                // if (res['error'] !== 0) {
                //     let m = res['message'] ? res['message'] : 'Thao tác thất bại';
                //     let d = res['description'] ? res['description'] : '';
                //     showModalNotify('error', m, d);
                //     return false;
                // }

                // Reset
                $(`input.checkbox_tag[value="${tag_name}"]`).removeAttr('disabled');
                $(`input.checkbox_tag`).prop('checked', false);

                // Recheck
                $(`input.checkbox_tag[value="${tag_name}"]`).prop('checked', true);
                $(`input.checkbox_tag[value="${tag_name}"]`).prop('disabled', 'disabled');
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                $('.container-waiting').hide();
                showModalNotify('error', 'Thao tác thất bại, vui lòng thử lại');
                console.error(XMLHttpRequest);
                console.error("Status: " + textStatus);
                console.error("Error: " + errorThrown);
            }
        });
    }

    console.warn('ADD');
}

/**
 * Remove tag name from user
 * 
 * @param {string} zalo_id
 * @param {string} tag_name
 */
function remove_tag_user(zalo_id, tag_name) {
    if(zalo_id && tag_name && zalo_id.length > 0 && tag_name.length > 0) {
        $.ajax({
            url: URL,
            type: "POST",
            data: {
                action : 'remove_tag_user',
                zalo_id : zalo_id,
                tag_name : tag_name
            },
            beforeSend: function() {
                $('.container-waiting').show();
            },
            success: function (response) {
                $('.container-waiting').hide();
                
                // res = JSON.parse(response); // Object
    
                // if (res['error'] !== 0) {
                //     let m = res['message'] ? res['message'] : 'Thao tác thất bại';
                //     let d = res['description'] ? res['description'] : '';
                //     showModalNotify('error', m, d);
                //     return false;
                // }

                $(`input.checkbox_tag[value="${tag_name}"]`).removeAttr('disabled');
                $(`input.checkbox_tag[value="${tag_name}"]`).prop('checked', false);
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                $('.container-waiting').hide();
                showModalNotify('error', 'Thao tác thất bại, vui lòng thử lại');
                console.error(XMLHttpRequest);
                console.error("Status: " + textStatus);
                console.error("Error: " + errorThrown);
            }
        });
    }

    console.warn('REMOVE');
}

/**
 * Reset all content in chat box (textarea)
 */
function reset_chat_box() {
    // Reset text
    $('textarea[name="message_content"]').val("");

    // Reset quote
    $('.preview_mess').remove();

    reset_upload_content();
}

/**
 * Reset upload content in chat box
 * @param {String} type image, file,...
 */
function reset_upload_content(type = 'all') {
    // Reset image upload
    if(type == 'all' || type == 'image') {
        $('#preview_image_upload').hide();
        $('#preview_image_upload').attr('src', '#');
        $('input[name="image_upload"]').val("");
    }

    // Reset file upload
    if(type == 'all' || type == 'file') {
        $('#preview_file_upload').hide();
        $('#preview_file_upload .file-image img').attr('src', '#');
        $('#preview_file_upload .file-name').text('');
        $('#preview_file_upload .file-size').text('');
        $('input[name="file_upload"]').val('');
    }
}

function disable_send_message() {
    $('textarea[name="message_content"]').attr('readonly', '');
    $('#send_message').prop('disabled', true);
    $('#upload_image').prop('disabled', true);
    $('#upload_file').prop('disabled', true);
}

function enable_send_message() {
    $('textarea[name="message_content"]').removeAttr('readonly');
    $('#send_message').prop('disabled', false);
    $('#upload_image').prop('disabled', false);
    $('#upload_file').prop('disabled', false);
}

function scroll_messages_bottom() {
    $('.section-post').scrollTop($('.section-post')[0].scrollHeight);
}

/**
 * Get class name of the message type
 * 
 * @param {String} type
 * @return {string}
 */
function getClassMessage(type) {
    switch (type) {
        case 'text':
            return 'message_notfound';
        case 'photo':
        case 'image':
        case 'gif':
            return 'picture';
        case 'voice':
        case 'audio':
            return 'sound voice-message';
        case 'sticker':
            return 'sticker';
        case 'location':
            return 'location-message';
        case 'link':
        case 'links':
            return 'product';
        case 'file':
            return 'card card--file';
        case 'video':
            return 'video-message';
        default:
            return type;
    }
}

/**
 * Get hour and min in datetime
 * 
 * @param {string} sent_time hh:mm:ss dd/mm/yyyy
 * @return {string}
 */
function getSentTimeZalo(sent_time) {
    let timePart = sent_time.split(' ')[0];
    let hourAndMinute = timePart.substring(0, 5);
    return hourAndMinute;
}

/**
 * Get number at the end string
 * 
 * @param {string} str
 * @return {string}
 */
function getNumberAtEndString(str) {
    let matches = str.match(/\d+$/);
    if (matches) {
        return matches[0].trim();
    }
    return null;
}

/**
 * Get image file to display
 * 
 * @param {String} type
 * @return {string}
 */
function getImageFile(type) {
    let image_file = JSON.parse($(`input[name="image_file"]`).val().replace(/'/g, '"'));

    switch (type) {
        case 'xls':
        case 'xlsx':
        case 'csv':
            return image_file.excel;
        case 'doc':
        case 'docx':
            return image_file.word;
        case 'ppt':
        case 'pptx':
        case 'pptm':
            return image_file.powerpoint;
        case 'pdf':
        case 'txt':
        case 'html':
        case 'xml':
        case 'zip':
        case 'rar':
            return image_file[type];
        default:
            return image_file.default;
    }
}

/**
 * Get icon to display
 * 
 * @param {String} key
 * @param {String} color #000000
 * @param {String} width px
 * @param {String} height px
 * @return {string} HTML
 */
function getIcons(key, color = '#000', width = '20px', height = '20px') {
    let svg = $(`#template_icon_${key} svg`);
    svg.attr({width: width, height: height});
    return svg[0].outerHTML.replace(/{{color}}/g, color);
}

/**
 * Create timeline from timestamp (Use for load more messages)
 * 
 * @param {int} timestamp
 * @return {string} HTML
 */
function getTimeline(timestamp) {
    let sent_time       = formatTimestampZalo(timestamp, 'd/m/Y', 'H:i:s', 'time');
    let datetimeline    = new Date(timestamp);
    let dateofweek      = map_date_of_week_zalo(datetimeline.getDay());
    let timeline        = getSentTimeZalo(sent_time);
    let text_timeline   = `${timeline} ${dateofweek}, ` + sent_time.split(' ')[1];
    return `<div class="_sectionTimestamp" id="_sectionTimestamp${timestamp}"><span>${text_timeline}</span></div>`;
}

/**
 * Get phone in alias string
 * 
 * @param {int} alias
 * @return {string}
 */
function get_phone_by_alias(alias) {
    if(!alias || alias === '') return '';

    // Regular expression to match sequences of digits
    let matches = alias.match(/\d+/g);

    if (matches !== null && matches.length > 0) {
        for (let i = 0; i < matches.length; i++) {
            let number = matches[i];
            if (number.length === 10) {
                return number;
            }
        }
    }

    return '';
}

function formatText(text) {
    return text.trim().replace(/\r\n/g, "<br />");
}

/**
 * Format phone number to display
 * 
 * @param {String} phone
 * @param {String} type
 * @return {string}
 */
function formatPhoneNumberZalo(phone, type = '') {
    let phoneFormat = phone.trim();
    
    if (type === 'zalo') {
        phoneFormat = phoneFormat.replace(/\s/g, '');
        phoneFormat = phoneFormat.replace(/^\+84/, '0');
        phoneFormat = phoneFormat.replace(/^84/, '0');
        phoneFormat = phoneFormat.replace(/^00/, '0');
        phoneFormat = phoneFormat.replace(/^0/, '84');
    } else {
        phoneFormat = phoneFormat.replace(/\s/g, '');
        phoneFormat = phoneFormat.replace('+', '');
        phoneFormat = phoneFormat.replace('84', '0');
    }
    
    return phoneFormat;
}

/**
 * Format datetime from timestamp
 * 
 * @param {Int} timestamp
 * @param {string} date_format
 * @param {string} time_format 
 * @param {string} first
 * @return {string}
 */
function formatTimestampZalo(timestamp, date_format = 'd/m/Y', time_format = 'H:i:s', first = 'date') {
	// Create a new Date object using the timestamp
	if(typeof timestamp === 'string') timestamp = parseInt(timestamp);
	const Date_ = new Date(timestamp);

	// Extract the components
	const day = String(Date_.getDate()).padStart(2, '0');
	const month = String(Date_.getMonth() + 1).padStart(2, '0'); // Months are zero-based
	const year = Date_.getFullYear();
	const hours = String(Date_.getHours()).padStart(2, '0');
	const minutes = String(Date_.getMinutes()).padStart(2, '0');
	const seconds = String(Date_.getSeconds()).padStart(2, '0');

	let date = '';
	if(date_format.length > 0) {
		let sep = date_format.indexOf('/') !== -1 ? '/' : '-';
		if(date_format.charAt(0) === 'd') date = `${day}${sep}${month}${sep}${year}`;
		else if(date_format.charAt(0) === 'Y') date = `${year}${sep}${month}${sep}${day}`;
		else date = `${month}${sep}${day}${sep}${year}`;
	}

	let time = '';
	if(time_format.length == 5) time = `${hours}:${minutes}:${seconds}`;
	else if(time_format.length == 3) time = `${hours}:${minutes}`;
	else if(time_format.length == 1) {
		if(time_format === 'H') time = hours;
		else if(time_format === 'i') time = minutes;
		else if(time_format === 's') time = seconds;
	}

	let datetime = first === 'date' ? `${date} ${time}` : `${time} ${date}`;
	return datetime.trim();
}

/**
 * Format file size beautiful
 * 
 * @param {Int} bytes
 * @return {string}
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function map_date_of_week_zalo(num) {
    let name = ['Chủ nhật','Thứ hai','Thứ ba','Thứ tư','Thứ năm','Thứ sáu','Thứ bảy'];
    return name[num];
}

function isUploading() {
    if($('input[name="image_upload"]').val().length == 0 && $('input[name="file_upload"]').val().length == 0) return false;
    return true;
}

function isJSON(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}