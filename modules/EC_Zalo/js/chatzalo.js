const OA_ID = $(`input[name="oa_id"]`).val();
const OA_NAME = $(`input[name="oa_name"]`).val();
const OA_AVATAR = $(`input[name="oa_avatar"]`).val();
const ADMIN_ID = $(`input[name="admin_id"]`).val();
const ADMIN_NAME = $(`input[name="admin_name"]`).val();
const URL = $(`input[name="entrypoint"]`).val();
const DEFAULT_AVATAR = $(`input[name="default_avatar"]`).val();
const URL_CHAT_WEBSOCKET = $(`input[name="websocket_url"]`).val();
const IMAGE_EXTENSION = $(`input[name="image_extension"]`).val().split(",");
const FILE_EXTENSION = $(`input[name="file_extension"]`).val().split(",");
// const LIMIT_MESSAGE = parseInt($(`input[name="limit_message"]`).val() ?? 0);
var oa_sub_quota = parseInt($(`input[name="oa_sub_quota"]`).val() ?? 0);
var zsocket;
var count_connect_error = 0;

$(document).ready(function () {
    resetGlobalContent();
    getListUser()
    connectWebSocket();

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
            // Reset list chat
            $('#list_mess_main').html('');

            if(value == 'default') getListUser();
            else {
                $.ajax({
                    url: URL,
                    type: "POST",
                    contentType: "application/json",
                    dataType: "json",
                    data: JSON.stringify({
                        class: "entryZaloOAClass",
                        method: "getListUser",
                        params: {
                            oa_id: OA_ID,
                            offset: 0,
                            value: value
                        }
                    }),
                    beforeSend: function() {
                        loadingSkeleton('list_user', 9);
                    },
                    success: function (response) {
                        removeLoadingSkeleton();

                        if(response?.status && response.status == 1) {
                            for (const [key, row_data] of Object.entries(response.data)) {
                                let message_info = row_data.message_info;
                                let user_info = row_data.user_info;

                                let html = create_li_chat(message_info, user_info);
                                $('#list_mess_main').append(html);
                            };
                            $('input[name="offset_list_user"]').val(50);
                        }
                        else {
                            $('#list_mess_main').html('<center><i>Không tìm thấy kết quả</i></center>');
                        }
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        removeLoadingSkeleton();
                        console.error(XMLHttpRequest, `Status: ${textStatus}`, `Error: ${errorThrown}`);
                    }
                });
            }
        }
    });

    // Load more user
    $('#list_mess_main').on('scroll', function() {
        if($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight - 5) {
            if(!$('.loader_list_user').length && $(`input[name="is_loading_list_user"]`).val() == '0') {
                let type_load = $('input[name="user_type_list"]').val();
                let last_timestamp = parseInt($('input[name="last_timestamp"]').val());
                let offset = parseInt($('input[name="offset_list_user"]').val());
                let count_li = $("#list_mess_main").children().length;

                let current_list_user = '';
                $('li.item_mess').each(function() {
                    if($(this).hasClass('item_mess_skeleton')) return;
                    let id = $(this).attr('id').toString().replaceAll("li", "");
                    current_list_user += current_list_user.length == 0 ? id : `,${id}`;
                });

                if(type_load == 'default') getListUser('default', last_timestamp, current_list_user);
                else {
                    if(offset < 0 || (type_load != 'L7D' && count_li < offset)) return false;

                    $.ajax({
                        url: URL,
                        type: "POST",
                        contentType: "application/json",
                        dataType: "json",
                        data: JSON.stringify({
                            class: "entryZaloOAClass",
                            method: "getListUser",
                            params: {
                                oa_id: OA_ID,
                                offset: offset,
                                value: type_load,
                            }
                        }),
                        beforeSend: function() {
                            loadingSkeleton('list_user', 6);
                            $(`input[name="is_loading_list_user"]`).val(1);
                        },
                        success: function (response) {
                            removeLoadingSkeleton();
                            $(`input[name="is_loading_list_user"]`).val(0);
                           
                            if(response?.status && response.status == 1) {
                                for (const [key, row_data] of Object.entries(response.data)) {
                                    let message_info = row_data.message_info;
                                    let user_info = row_data.user_info;
        
                                    let html = create_li_chat(message_info, user_info);
                                    $('#list_mess_main').append(html);
                                };
                                offset += 50;
                                $('input[name="offset_list_user"]').val(offset);
                            }
                            else $('input[name="offset_list_user"]').val(-1);
                        },
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                            removeLoadingSkeleton();
                            $(`input[name="is_loading_list_user"]`).val(0);
                            console.error(XMLHttpRequest, `Status: ${textStatus}`, `Error: ${errorThrown}`);
                        }
                    });   
                }
            }
        }
    });

    // List messages (Open chat)
    $(document).on("click", "li.item_mess", function() {
        let zalo_id = $(this).attr('id').replace("li", "");

        if (zalo_id && zalo_id.length > 10) {
            let user_info_encoded = $(`input#user_data_${zalo_id}`).val();
            let is_get_user_info = user_info_encoded.length > 10 ? 0 : 1;
            user_info = JSON.parse(decodeURIComponent(atob(user_info_encoded)));
            let zalo_phone = (user_info?.shared_info && user_info.shared_info?.phone) ? user_info.shared_info.phone : '';
            const limit_message = parseInt($(`input[name="limit_message"]`).val());

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
            $('#user_tag_display').attr('data', '');
            $('#user_tag_display .title').text('Nhãn');

            $.ajax({
                url: URL,
                type: "POST",
                contentType: "application/json",
                dataType: "json",
                data: JSON.stringify({
                    class: "entryZaloOAClass",
                    method: "getMessages",
                    params: {
                        oa_id: OA_ID,
                        zalo_id: zalo_id,
                        zalo_phone: zalo_phone,
                        is_get_user_info: is_get_user_info,
                        limit_message: limit_message
                    }
                }),
                beforeSend: function() {
                    $('.container-waiting').show();
                },
                success: function (response) {
                    $('.container-waiting').hide();

                    /************  1. User  ************/
                    let data_user = {};
                    if(is_get_user_info === 0) {
                        data_user.data = user_info;
                    }
                    else {
                        data_user = response.user_info;
                        if(!(data_user?.status) || data_user.status == 0) {
                            showModalNotify('warning', 'Người dùng không thể tương tác');
                            return false;
                        }
                    }

                    /**********  2. Messages  **********/
                    let data_message = response.messages_info;

                    // Show
                    $(`#zalochat_main`).show();
                    $(`#zalochat_profile`).show();
                    $(`input[name="offset_load_more_message"]`).val(data_message.offset);

                    create_chat_box(data_user.data, data_message.data);

                    /**********  3. Quota user  **********/
                    const [day, month, year, hour, minute, second] = data_user.data.user_last_interaction_date.match(/\d+/g);
                    const datetemp = new Date(year, month - 1, day, hour, minute, second);
                    handleQuotaUser(data_user.data.quota, datetemp.getTime());
                  
                    // Reset
                    $(`#liuserinfo${zalo_id}`).text('');
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $('.container-waiting').hide();
                    showModalNotify('error', 'Kết nối thất bại, vui lòng thử lại sau');
                    console.error(XMLHttpRequest, `Status: ${textStatus}`, `Error: ${errorThrown}`);
                }
            });
        }
    });

    // Load more messages
    $('.section-post').scroll(function() {
        if($(this).scrollTop() == 0) {
            let zalo_id = $('#content_chat').attr('zalo_id');
            let zalo_phone = $('#profile_mobile').attr('data');
            let offset = parseInt($(`input[name="offset_load_more_message"]`).val());
            const limit_message = parseInt($(`input[name="limit_message"]`).val());

            if(zalo_id && zalo_id.length > 10 && offset > 0 && $(`._section-mt`)[0]) {
                $.ajax({
                    url: URL,
                    type: "POST",
                    contentType: "application/json",
                    dataType: "json",
                    data: JSON.stringify({
                        class: "entryZaloOAClass",
                        method: "getMessages",
                        params: {
                            oa_id: OA_ID,
                            zalo_id: zalo_id,
                            zalo_phone: zalo_phone,
                            offset: offset,
                            is_get_user_info: 0,
                            limit_message: limit_message
                        }
                    }),
                    beforeSend: function() {
                        $('.loader_messages').show();
                    },
                    success: function (response) {
                        let data_message = response.messages_info;
                        if (!(data_message?.status) || data_message.status == 0) {
                            showModalNotify('error', 'Lỗi lấy dữ liệu');
                            return false;
                        }

                        $(`input[name="offset_load_more_message"]`).val(data_message.offset);
                        let last_timestamp = parseInt($(`#section-message__details .section-item`).first().attr('timestamp'));
                        
                        // Display
                        $(`#section-message__details ._section-mt`).remove();
                        let container = $('.section-post')[0];
                        container.scrollTop = container.scrollHeight;
                        let html_old = $(`#section-message__details`).html();
                        let timeline = getTimeline(last_timestamp);
                        let html_new = create_chat_box(null, data_message.data, true) + timeline + html_old;
                        $(`#section-message__details`).html(html_new);

                        // Scroll to message element first
                        container.scrollTop += $(`#_sectionTimestamp${last_timestamp}`).offset().top - 140;
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        $('.loader_messages').hide();
                        console.error(XMLHttpRequest, `Status: ${textStatus}`, `Error: ${errorThrown}`);
                    }
                });
            }
        }
    });

    // Send message
    $('#send_message').click(function () {
        if($(`textarea[name="message_content"]`).val().trim().length == 0 && !isUploading()) return false;
        let formData = generate_form_data();
        resetChatBox();
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
            resetChatBox();
            send_message(formData);
        }
    });

    // Send request user info
    $('#btn_request_user_info').click(function () {
        let zalo_id = $(`#content_chat`).attr(`zalo_id`);
        let formData = new FormData();
        // formData.append('action', 'send_message');
        // formData.append('oa_id', OA_ID);
        // formData.append('zalo_id', zalo_id);
        // formData.append('type', 'request_user_info');
        formData.append('class', 'entryZaloOAClass');
        formData.append('method', 'sendMessage');
        formData.append('params', {
            'oa_id': OA_ID,
            'zalo_id': zalo_id,
            'type': 'request_user_info',
        });
        send_message(formData);
    });

    // Choose image to send
    $('#upload_image').click(function () {
        $('input[name="image_upload"]').trigger("click");
    });
    $('input[name=image_upload]').change(function() {
        resetContentUpload('file');
        let fileInput = $(this)[0];
        if(fileInput.files.length > 0) handleFile(fileInput.files[0]);
    });

    // Choose file to send
    $('#upload_file').click(function () {
        $('input[name="file_upload"]').trigger("click");
    });
    $('input[name=file_upload]').change(function() {
        resetContentUpload('image');
        let fileInput = $(this)[0];
        if(fileInput.files.length > 0) handleFile(fileInput.files[0]);
    });

    // Paste text or image/file
    $(`textarea[name="message_content"]`).bind("paste", function(event){
        event.preventDefault();
                
        const items = event.originalEvent.clipboardData.items;
        let textContent = $(this).val();
        let fileBlob = null;

        for (let i = 0; i < items.length; i++) {
            let item = items[i];

            if (item.kind === 'string' && item.type === 'text/plain') {
                item.getAsString(text => {
                    textContent += text;
                    $(this).val(textContent);

                    if(text && text.length > 0) broadcastAdminAction('', 'typing');
                });
            }
            else if (item.kind === 'file') {
                fileBlob = item.getAsFile();
            }

            if (fileBlob) {
                // Create a new Blob and File object
                const blob = new Blob([fileBlob], { type: fileBlob.type });
                const newFile = new File([blob], fileBlob.name, { type: fileBlob.type });
                handleFile(newFile);
            }
        }
    });

    // Drag image/file over
    const dropArea = document.getElementById('textarea_message_content');
    dropArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropArea.classList.add('dragover');
    });
    dropArea.addEventListener('dragleave', () => {
        dropArea.classList.remove('dragover');
    });
    dropArea.addEventListener('drop', (e) => {
        resetContentUpload('file');
        e.preventDefault();
        dropArea.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (files.length) handleFile(files[0]);
    });

    // Reset display upload content
    $('#reset_upload_content').click(function() { resetContentUpload(); });

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

    // // Save contact
    // $('#btn_save_contact').click(function () {
    //     let zalo_id     = $('#content_chat').attr('zalo_id');
    //     let phone       = $('#profile_mobile').attr('data');
    //     let name        = $('#profile_zalo_name').attr('data');
    //     let alias       = $('#header_name_chat').text();
    //     let city        = $('input[name="profile_address_city"]').val();
    //     let district    = $('input[name="profile_address_district"]').val();
    //     let address     = $('input[name="profile_address_number"]').val();

    //     if(zalo_id && zalo_id.length > 0) {
    //         $.ajax({
    //             url: URL,
    //             type: "POST",
    //             data: {
    //                 action: "save_contact",
    //                 zalo_id : zalo_id,
    //                 phone : phone,
    //                 name : name,
    //                 alias : alias,
    //                 city : city,
    //                 district : district,
    //                 address : address
    //             },
    //             beforeSend: function() {
    //                 $('.container-waiting').show();
    //             },
    //             success: function (response) {
    //                 $('.container-waiting').hide();

    //                 res = JSON.parse(response);
    //                 let m = res['message'] ? res['message'] : 'Thao tác thất bại';
    //                 let d = res['description'] ? res['description'] : '';

    //                 if (res['error'] !== 0) showModalNotify('error', m, d);
    //                 else showModalNotify(1, m);
    //             },
    //             error: function (XMLHttpRequest, textStatus, errorThrown) {
    //                 showModalNotify('error', 'Thao tác thất bại, vui lòng thử lại');
    //                 console.error(XMLHttpRequest);
    //                 console.error("Status: " + textStatus);
    //                 console.error("Error: " + errorThrown);
    //             }
    //         });x
    //     }
    // });

    // Call zalo
    $('#func-call').click(function () {
        let zalo_id = $('#content_chat').attr('zalo_id');
        let name    = $('#profile_zalo_name').text();
        let avatar  = $('#header_avatar_chat').attr('src');
        let phone   = $('#profile_mobile').attr('data');
        if(phone === '') phone = get_phone_by_alias($('#header_name_chat').text());
        
        if(zalo_id && zalo_id > 10) {
            resetPopupVoiceip();

            $('#voiceip-info-zaloid').html(zalo_id);
            $('#voiceip-info-zaloid').attr('href', `https://zalo.me/${zalo_id}`);
            $('#voiceip-info-zaloid').closest('p').find('span').html('Zalo ID: ');
            $('#voiceip-info-phone').html(phone);
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
            // $('#zalochat_main').css('flex', 'unset');
        }
    });

    // Display typing with other users
    $('textarea[name="message_content"]').on('input', function() {
        let zalo_id = $('#content_chat').attr('zalo_id');
        let value = $(this).val();
        if(value && value.length > 0) broadcast_admin_action('typing', zalo_id);
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
                        // User data (base64 string)
                        let user_data = JSON.parse(decodeURIComponent(atob($(`#user_data_${zalo_id}`).val().trim())));
                        user_data.user_alias = alias;
                        $(`#user_data_${zalo_id}`).val(btoa(encodeURIComponent(JSON.stringify(user_data))));
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
        let current_phone       = $('#profile_mobile').attr('data');
        let current_name        = $('input[name="profile_shared_name"]').val();
        let current_address     = $('input[name="profile_address_number"]').val();
        let current_city        = $('input[name="profile_address_city"]').val();

        // Fill info to update modal
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

        if(zalo_id.length * name.length * phone.length * address.length * district_id.length * city_id.length == 0) {
            showModalNotify('warning', 'Vui lòng điền đầy đủ thông tin');
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
                info_user_district: district_id,
                info_user_city: city_id,
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

                    /***** UPDATE INFO *****/
                    // Shared name
                    $('input[name="profile_shared_name"]').val(name);
                    // Phone
                    $('#profile_mobile').text(phone);
                    $('#profile_mobile').attr('data', phone);
                    // Address
                    let city_name = $(`option[value="${city_id}"]`).text();
                    let district_name = $(`option[value="${district_id}"]`).text();
                    $('input[name="profile_address_number"]').val(address);
                    $('input[name="profile_address_district"]').val(district_name);
                    $('input[name="profile_address_city"]').val(city_name);
                    $('#profile_address').text(formatFullAddress(address, district_name, city_name));
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

        if(tag_name == current_tag || checkbox.is(":checked")) remove_tag_user(zalo_id, tag_name);
        else add_tag_user(zalo_id, tag_name);
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

            if(!search_value || search_value.length < 3) return false;

            $.ajax({
                url: URL,
                type: "POST",
                contentType: "application/json",
                dataType: "json",
                data: JSON.stringify({
                    class: "entryZaloOAClass",
                    method: "searchZaloContact",
                    params: {
                        oa_id: OA_ID,
                        search_value: search_value
                    }
                }),
                beforeSend: function() {
                    $('#list_mess_main').hide();
                    $('#list_mess_search').html('<div class="loader_list_user mt-3"></div>').show();
                },
                success: function (response) {
                    $('#loader_list_user').remove();

                    if(response?.status && response.status == 1) {
                        let html = '';
                        $.each(obj['data'], function(index, item) {
                            html += create_li_chat(item.message_info, item.user_info);
                        });
                        if(html.length > 0) $('#list_mess_search').html(html);
                        else $('#list_mess_search').html(`<li class="no-results-found">Không tìm thấy kết quả vui lòng tìm kiếm với từ khóa khác</li>`);
                    }
                    else {
                        $('#list_mess_search').html(`<li class="no-results-found">${obj['message']}</li>`);
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $('#loader_list_user').remove();
                    showModalNotify('error', 'Vui lòng thử lại');
                    console.error(XMLHttpRequest, `Status: ${textStatus}`);
                }
            });
        }
        // Backspace
        else if(e.keyCode === 8 && $(this).val().length == 0) {
            $('#list_mess_main').show();
            $('#list_mess_search').hide();
        }
    });

    // Hide show assign user
    $(document).on('mouseenter', '.avatar-assign', function(e) {
        e.preventDefault();
        let element = $(this).attr('for');
        $(`.assign-fullname#${element}`).stop().fadeIn(300);
        return false;
    });
    $(document).on('mouseleave', '.avatar-assign', function(e) {
        e.preventDefault();
        let element = $(this).attr('for');
        $(`.assign-fullname#${element}`).stop().fadeOut(300);
        return false;
    });

    // Enlarge image
    $(document).on("click", ".message-image", function() {
        const viewer = new Viewer(this, {
            toolbar: true, // Hiển thị thanh công cụ zoom
            movable: true, // Cho phép kéo ảnh
            zoomable: true, // Cho phép zoom
            scalable: true, // Cho phép scale
            // fullscreen: true, // Cho phép fullscreen
            navbar: false, // Ẩn thanh thumbnail
        });
        viewer.show();
    });
});

/**
 * Get list user
 * 
 * @param {string} type
 * @param {number} timestamp
 * @param {string} current_list_user
 */
function getListUser(type = 'default', timestamp = 0, current_list_user = '') {
    if(type == 'default') {
        const limit_chat_box = parseInt($(`input[name="limit_chat_box"]`).val());
        
        $.ajax({
            url: URL,
            type: "POST",
            contentType: "application/json",
            dataType: "json",
            data: JSON.stringify({
                class: "entryZaloOAClass",
                method: "getListRecentMessages",
                params: {
                    oa_id: OA_ID,
                    timestamp: timestamp,
                    current_list_user: current_list_user,
                    limit_record: limit_chat_box
                }
            }),
            beforeSend: function() {
                loadingSkeleton('list_user', 9);
                $(`input[name="is_loading_list_user"]`).val(1);
            },
            success: function (response) { // JSON
                removeLoadingSkeleton();
                $(`input[name="is_loading_list_user"]`).val(0);

                if(response?.data) {
                    response.data.forEach(row_data => {
                        let message_info = row_data.message_info;
                        let user_info = row_data.user_info;

                        let html = create_li_chat(message_info, user_info);
                        $('#list_mess_main').append(html);
                    });
                    $('input[name="last_timestamp"]').val(response.last_timestamp);
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                removeLoadingSkeleton();
                $(`input[name="is_loading_list_user"]`).val(0);
                console.error(XMLHttpRequest, `Status: ${textStatus}`, `Error: ${errorThrown}`);
            }
        });
    }
}

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
        let event           = data['event_name'] ?? '';
        let app_id          = data['app_id'] ?? '';
        let src             = parseInt(data['src']) ?? 0;
        let mid             = data['message_id'] ?? '';
        let sender_id       = data['from_id'] ?? '';
        let recipient_id    = data['to_id'] ?? '';
        let timestamp       = data['timestamp'] ?? 0;
        let type            = data['type'] ?? '';
        // let message         = data['message'] ?? '';
        if(sender_id == '7658987821159451152' || recipient_id == '7658987821159451152') console.log('ZALO_SOCKET: ', e.data); // For test

        /**********  OA EVENT CUSTOM **********/
        if(event == 'oa_typing') {
            let sender_admin_id = data['assigned_user_id'] ?? '';
            let current_user_id_chat = $('#content_chat').attr('zalo_id');

            if(sender_admin_id !== ADMIN_ID && recipient_id === current_user_id_chat) {
                let sender_admin_name = data['assigned_user_name'] ?? '';
                let admin_typing = $('#admin_typing').text();

                if(admin_typing.length > 0) {
                    // Check
                    let check = true;
                    let arr_admin_typing = admin_typing.split(',');
                    arr_admin_typing.forEach(function (item, index) {
                        if(sender_admin_name === item.trim()) check = false;
                    });

                    if(check) {
                        $('#admin_typing').text(`${admin_typing}, ${sender_admin_name}`);
                        $('#admin_action').text('đang soạn tin');
                    }
                }
                else {
                    $('#admin_typing').text(sender_admin_name);
                    if(type == 'sending_image') $('#admin_action').text('đang gửi hình ảnh');
                    else if(type == 'sending_file') $('#admin_action').text('đang gửi tệp');
                    else $('#admin_action').text('đang soạn tin');
                }

                $('.display-admin-typing').show();
                // Hide after 3 seconds
                setTimeout(function() {
                    $('#admin_typing').text('');
                    $('#admin_action').text('');
                    $('.display-admin-typing').hide();
                }, 3000);
            }
        }
        /**********  USER EVENT  **********/
        else if(src == 1) {
            let current_user_id_chat = $('#content_chat').attr('zalo_id');

            // The event belongs to the user who is texting
            if(sender_id === current_user_id_chat) {
                let avatar = $('#header_avatar_chat').attr('src');
                let previous_sender_id = $('#section-message__details .section-item:last').attr('sender_id');
                let previous_timestamp = $('#section-message__details .section-item:last').attr('timestamp');

                data.from_avatar = avatar;  
                data.previous_sender_id = previous_sender_id;
                data.previous_timestamp = previous_timestamp;

                // Get current position first
                let chatBox = document.getElementById('section_chatbox');
                let scrollPosition = chatBox.scrollTop;
                let scrollHeight = chatBox.scrollHeight;
                let clientHeight = chatBox.clientHeight;

                let row = create_chat_row(data, 'new');
                $(`#section-message__details`).append(row);
                $(`#section-message__details .section-item:last .snippet .borHak`).show();

                let message_format = create_li_chat(data, {}, true);
                $(`#li${sender_id} .lastest_message`).html(message_format);
                $(`#li${sender_id} .mess_time`).text(formatTimestampZalo(timestamp, '', 'H:i'));

                // Update quota user
                handleQuotaUser(data['quota_user'] || {}, timestamp);

                // Check if the user has been to the bottom
                if (scrollPosition + clientHeight >= scrollHeight - 200) scroll_messages_bottom();
            }
            // The event belongs to the user currently on the list
            else if($(`#li${sender_id}`).length) {
                let sender = $(`#li${sender_id}`);
                let num = sender.find('.mess_number').text() ? sender.find('.mess_number').text() : 0;
                data.num = parseInt(num) + 1;

                let user_data = {};
                let user_data_raw = $(`input#user_data_${sender_id}`).val();
                if(user_data_raw && user_data_raw.length > 16) {
                    user_data = JSON.parse(decodeURIComponent(atob(user_data_raw)));
                }

                let li = create_li_chat(data, user_data);
                sender.remove();
                $('#list_mess_main').prepend(li);
            }
            // The event belongs to the new user
            else {
                if($('input[name="user_type_list"]').val() !== 'default') return false;
                create_li_chat_new(sender_id, data);
            }
        }
        /**********  OA EVENT  **********/
        else if(src == 0) {
            let current_user_id_chat = $('#content_chat').attr('zalo_id');

            // The event belongs to the user who is texting
            if(recipient_id === current_user_id_chat) {
                let previous_sender_id = $('#section-message__details .section-item:last').attr('sender_id');
                let previous_timestamp = $('#section-message__details .section-item:last').attr('timestamp');
                let previous_assign_user_id = $('#section-message__details .section-item:last').attr('assign_user_id');

                data.previous_sender_id = previous_sender_id;
                data.previous_timestamp = previous_timestamp;
                data.previous_assign_user_id = previous_assign_user_id;

                let row = create_chat_row(data, 'new');
                $(`#section-message__details`).append(row);
                $(`#section-message__details .section-item:last .snippet .borHak`).show();

                let message_format = create_li_chat(data, {}, true);
                $(`#li${recipient_id} .lastest_message`).html(message_format);
                $(`#li${recipient_id} .mess_time`).text(formatTimestampZalo(timestamp, '', 'H:i'));

                scroll_messages_bottom();
            }
            // The event belongs to the user currently on the list
            else if($(`#li${recipient_id}`).length) {
                let recipient = $(`#li${recipient_id}`);

                let user_data = {};
                let user_data_raw = $(`input#user_data_${recipient_id}`).val();
                if(user_data_raw && user_data_raw.length > 16) {
                    user_data = JSON.parse(decodeURIComponent(atob(user_data_raw)));
                }

                let li = create_li_chat(data, user_data);
                recipient.remove();
                $('#list_mess_main').prepend(li);
            }
            // The event belongs to the new user
            else {
                if($('input[name="user_type_list"]').val() !== 'default') return false;
                create_li_chat_new(recipient_id, data);
            }
        }
    };

    zsocket.onerror = function(error) {
        console.error('ZALO_SOCKET: ', error);
    };
    
    zsocket.onclose = function(event) {
        if (event.wasClean) {
            console.log(`ZALO_SOCKET: Connection closed cleanly, code=${event.code} reason=${event.reason}`);
        }
        else {
            console.warn('ZALO_SOCKET: Connection closed unexpectedly', event);
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
 * Broadcast admin action
 * 
 * @param {String} action
 * @param {String} zalo_id (Auto detect from content chat)
 */
function broadcast_admin_action(action = 'typing', zalo_id = '') {
    if(!zalo_id || zalo_id.length == 0) zalo_id = $('#content_chat').attr('zalo_id').trim();
    let event = {
        to_id : zalo_id,
        from_id : OA_ID,
        assigned_user_id: ADMIN_ID,
        assigned_user_name: ADMIN_NAME,
        event_name: "oa_typing",
        type: action,
        timestamp: Date.now()
    }
    if(zalo_id && zalo_id.length > 0 && zsocket) zsocket.send(JSON.stringify(event));
}

/**
 * Create chat box with user
 * (Only call this function after updating offset_load_more_message)
 * 
 * @param {Object} data_user
 * @param {Object} data_message
 * @param {Boolean} is_return
 * @returns {String} HTML
 */
function create_chat_box(data_user, data_message, is_return = false) {
    if(data_user) {
        let chat_link = data_user['chat_link'] ? data_user['chat_link'] : "#";
        let avatar = data_user['avatar'] ? data_user['avatar'] : DEFAULT_AVATAR;
        let name = data_user['display_name'] ? data_user['display_name'] : '';
        let alias = data_user['user_alias'] ? data_user['user_alias'] : name;
        let is_follower = data_user['user_is_follower'] ? parseInt(data_user['user_is_follower']) : 0;
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

        // DOM alias, name, shared name
        $('#profile_zalo_alias').text(alias);
        $('#profile_zalo_name').text(`Tên Zalo: ${name}`);
        $('#profile_zalo_name').attr('data', name);
        $('input[name="profile_shared_name"]').val(shared_info['name'] ? shared_info['name'] : '');

        // DOM zalo id to call
        $('.func-call').attr('zalo-id', data_user['user_id']);

        // DOM chat link
        $('#profile_chat_link').attr('href', chat_link);

        // DOM address
        let address_number = shared_info['address'] ? shared_info['address'] : '';
        let district = shared_info['district'] && shared_info['district'].length > 0 ? shared_info['district'] : '';
        let city = shared_info['city'] && shared_info['city'].length > 0 ? shared_info['city'] : '';
        $('#profile_address').text(formatFullAddress(address_number, district, city));
        $('input[name="profile_address_number"]').val(address_number);
        $('input[name="profile_address_district"]').val(district);
        $('input[name="profile_address_city"]').val(city);

        // DOM mobile
        let mobile = shared_info['phone'] ? shared_info['phone'].toString() : '';
        if(mobile.length == 0) {
            $('#profile_mobile').text('Chưa công khai');
            $('#profile_mobile').attr('data', '');
        }
        else {
            $('#profile_mobile').text(formatPhoneNumberZalo(mobile));
            $('#profile_mobile').attr('data', formatPhoneNumberZalo(mobile));
        }
    }

    let html = '';
    let more_message = '<div id="top_chat" class="_section-mt"><div class="loader_messages" style="display:none"></div></div>';
    let first_timestamp = null;
    if (data_message) {
        let previous_message_id = null;
        let previous_sender_id = null;
        let previous_timestamp = null;
        let previous_assign_user_id = null;
        $.each(data_message.slice().reverse(), function (key, valueObj) {
            if(html.length == 0) {
                html = more_message;
                first_timestamp = valueObj.timestamp ? valueObj.timestamp : (valueObj.time ? valueObj.time : 0);
            }

            let mid = valueObj.message_id ? valueObj.message_id : '';
            let sender_id = valueObj.from_id ? valueObj.from_id : '';
            let timestamp = valueObj.timestamp ? valueObj.timestamp : (valueObj.time ? valueObj.time : 0);
            let assign_user_id = valueObj.assigned_user_id ? valueObj.assigned_user_id : '';
            
            valueObj['from_avatar'] = parseInt(valueObj.src) == 1 ? (data_user && data_user['avatar'] ? data_user['avatar'] : $('#header_avatar_chat').attr('src')) : OA_AVATAR;
            valueObj['previous_sender_id'] = previous_sender_id;
            valueObj['previous_timestamp'] = previous_timestamp;
            valueObj['previous_assign_user_id'] = previous_assign_user_id;
            let row = create_chat_row(valueObj, is_return ? 'load' : 'new');

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
                if(assign_user_id === previous_assign_user_id) {
                    row = row.replace(`{{style-avatar-${mid}}}`, `style="visibility:hidden"`);
                }
            }

            html += row;

            previous_message_id = mid;
            previous_sender_id = sender_id;
            previous_timestamp = timestamp;
            previous_assign_user_id = assign_user_id;
        });
    }

    let offset_load_more_message = $(`input[name="offset_load_more_message"]`).val();
    if(offset_load_more_message < 0 && first_timestamp) {
        let timeline_html = getTimeline(first_timestamp);
        html = timeline_html + html.replace(more_message, "");
    }

    if(is_return) return html;

    $(`#section-message__details`).html(html);
    $(`#section-message__details .section-item:last .snippet .borHak`).show();
    setTimeout(scroll_messages_bottom, 100);
}

/**
 * Create a chat row in chat box
 * 
 * @param {Object} obj
 * @param {String} ctype load, new
 * @returns {String} HTML
 */
function create_chat_row(obj, ctype = 'load') {
    let mid = obj.message_id ? obj.message_id : '';
    if($(`#mess-${mid}`).length > 0) return '';

    let quote_id = obj.quote_id ? obj.quote_id : '';
    let parent_type = obj.message_type ? obj.message_type : 'consultation';
    let type = obj.type ? obj.type : '';
    let message = obj.message ? formatText(obj.message) : '';
    let src = obj.src ? parseInt(obj.src) : 0;
    let timestamp = obj.time ? parseInt(obj.time) : (obj.timestamp ? parseInt(obj.timestamp) : 0);
    let sent_time = formatTimestampZalo(timestamp, 'd/m/Y', 'H:i:s', 'time');
    let sender_id = obj.from_id ? obj.from_id : '';
    let avatar = obj.from_avatar ? obj.from_avatar : (src == 0 ? OA_AVATAR : DEFAULT_AVATAR); // Here
    let previous_sender_id = obj.previous_sender_id ? obj.previous_sender_id : null;
    let previous_timestamp = obj.previous_timestamp ? obj.previous_timestamp : null;
    let timeout = ctype == 'load' ? 60 * 60 * 1000 : 20 * 60 * 1000;
    let html = '';


    // Time line
    let new_timeline = false;
    if (previous_sender_id && sender_id != previous_sender_id) {
        if (previous_timestamp && timestamp - previous_timestamp > timeout) {
            html += getTimeline(timestamp);
            // html += `<div class="_sectionTimestamp"><span>${text_timeline}</span></div>`;
            new_timeline = true;
        }
    }
    else if(previous_sender_id && sender_id == previous_sender_id) {
        if (previous_timestamp && timestamp - previous_timestamp > timeout) {
            html += getTimeline(timestamp);
            // html += `<div class="_sectionTimestamp"><span>${text_timeline}</span></div>`;
            new_timeline = true;
        }
    }
    

    // Content
    let content = '', classnamepicture = '';
    if (parent_type == 'zns') {
        let zns_items = '';
        let template_data = obj.message_data ? obj.message_data : {};

        for (let key in template_data) {
            let label_name = get_label_name_template_zns(key);
            if(!label_name || label_name.length == 0) continue;
            zns_items += `<div class="item">
                <div class="label">${label_name}</div>
                <div class="value">${template_data[key]}</div>
            </div>`;
        }
        
        content = `<div class="card-container content-zns">
            <div class="header-zns">Tin nhắn ZNS</div>
            <div class="body-zns">
                <h6 class="title-zns">${message}</h6>
                <div class="data-zns">${zns_items}</div>
            </div>
        </div>`;
        type = parent_type;
    }
    else if (parent_type == 'call') {
        let template_data = obj.message_data ? obj.message_data : {};
        let call_type = get_style_call_message(type);
        let assign_html = obj.assigned_user_name && obj.assigned_user_name.length > 0 ? `<p class="assign-fullname">Tiếp nhận: ${obj.assigned_user_name}</p>` : ''; 
        let record_html = '';
        if(template_data['record_file'] && template_data['record_file'].length > 0) {
            record_html = `<div class="call-record-file">
                <audio controls=""><source src="${template_data['record_file']}" type="audio/wav"></audio>
            </div>`;
        }

        content = `<div class="card-container content-call">
            <div class="call-title ${call_type.classname}">
                <i>${call_type.icon}</i>
                <span>${type}</span>
            </div>
            ${assign_html}
            ${record_html}
        </div>`;
        type = parent_type;
    }
    else {
        if (type == 'text') {
            content += `<span class="content"><span>${message}</span></span>`;
        }
        else if (type == 'image' || type == 'gif' || type == 'photo') {
            let url = obj.url ? obj.url : '#';
            if(message.length == 0) message = obj.description ? obj.description.trim() : '';
            let text = message.length > 0 ? `<span class="content"><span>${message}</span></span>` : '';
            classnamepicture = text.length > 0 ? 'picture_text' : '';
    
            content += `<div class="content-picture item">
                <img src="${url}" class="message-image">
                ${text}
            </div>`;
        }
        else if (type == 'sticker') {
            let url = obj.url ? obj.url : '#';
            content += `<img src="${url}" class="message-image">`;
        }
        else if (type == 'audio' || type == 'voice') {
            let url = obj.url ? obj.url : '#';
            content += `<div class="sound-container">
                <audio controls=""><source src="${url}" type="audio/wav"></audio>
            </div>`;
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
            let file = obj.message_data ?? null;
            if(file) {
                let file_size = file.size ? formatFileSize(parseInt(file.size)) : '';
                let file_name = file.name ? file.name : '';
                let file_type = file.type ? file.type : '';
                let file_url = file.url ? file.url : '#';
                let file_image = getImageFile(file_type);
    
                content += `<div class="card-container">
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
                </div>`;
            }
        }
        else if (type == 'location') {
            let latitude  = obj.latitude ?? '';
            let longitude = obj.longitude ?? '';
    
            content += `<div class="content">
                <div>
                    <iframe title="map" width="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=${latitude},${longitude}&amp;hl=es;z=14&amp;output=embed"></iframe>
                </div>
            </div>`;
        }
        else if (type == 'business_card') {
            let json        = obj.description ? obj.description : '';
            let thumbnail   = obj.thumbnail ? obj.thumbnail : '';
            let username    = message;
            content += getBusinessCard(json, thumbnail, username);
        }
        else if (type == 'link' || type == 'links') {
            let links = obj.message_data ?? null;
   
            $.each(links , function(index, link) {
                let title = link.title ?? '';
                let url = link.url ?? '';
                let thumb = link.thumbnail ?? (link.thumb ? link.thumb : '');
                let description = link.description ?? '';
    
                // Business card
                if(isJSON(description)) {
                    let username = message;
                    content += getBusinessCard(description, thumb, username);
                }
                else {
                    content += `
                        <div class="product-container">    
                            <div class="main-product">
                                <a class="img" style="background-image: url('${thumb}');" href="${url}" target="_blank"></a>
                                <h6 class="title mt-2 mb-2 p-0">${title}</h6>
                                <p class="desc">${description}</p>
                            </div>
                        </div>
                    `;
                }
            });
        }
        else content = '<i>Tin nhắn chưa hỗ trợ</i>';
    }


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
        let quote_data = obj.quote_data ? obj.quote_data : null;
        quote_html = create_quote_content(quote_id, quote_data);
    }


    // Assigned user
    let avatar_assign = '';
    let assigned_user_id = '';
    if(src == 0) {
        assigned_user_id = obj.assigned_user_id ? obj.assigned_user_id : '';
        let assigned_user_name = obj.assigned_user_name ? obj.assigned_user_name : '';

        if(assigned_user_id.length > 0) {
            if(obj.assigned_user_avatar && obj.assigned_user_avatar.length > 0) {
                avatar_assign = `
                    <img class="avatar-assign avatar-assign-img" src="${obj.assigned_user_avatar}" for="assign${mid}"/>
                    <span id="assign${mid}" class="assign-fullname">${assigned_user_name}</span>
                `;
            }
            else {
                let arrName = assigned_user_name.split(" ");
                let firstName = arrName[arrName.length - 1];
                avatar_assign = `
                    <span class="avatar-assign avatar-assign-name" for="assign${mid}">${firstName.charAt(0)}</span>
                    <span id="assign${mid}" class="assign-fullname">${assigned_user_name}</span>
                `;
            }
        }
    }


    // Action message
    $action_message = `<div class="more ${classname}">
        <div class="inner">
            <div class="btn_quote" data-message-id="${mid}">
                <i class="icon icon_quote_black">
                    ${getIcons('quote', '#808080', '18px', '18px')}
                </i>
            </div>
        </div>   
    </div>`;
    if(parent_type == 'zns' || parent_type == 'call' || parent_type == 'transaction') $action_message = '';


    html += `
        <div id="mess-${mid}" class="section-item ${classname}" sender_id="${sender_id}" timestamp="${timestamp}" assign_user_id="${assigned_user_id}" style="${style_item}">
            <div class="avatar avatar--sm" ${style_avatar}>
                <span class="avatar-img" style="background-image: url('${avatar}');"></span>
                ${avatar_assign}
            </div>
            <div class="item-content">
                <div class="item-headline-container">
                    <div class="snippet">
                        <div class="admin-message-wrapper">
                            <div class="message-container">
                                <div class="item-message ${getClassMessage(type)} ${classnamepicture} ${classquote}">
                                    ${quote_html}
                                    ${content}
                                    ${$action_message}
                                </div>
                            </div>
                        </div>
                        <div class="borHak ${src == 0 ? 'me' : ''}" ${style_borHak}>
                            <span class="">${getSentTimeZalo(sent_time)}</span>
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
 * @param {Object} quote_data
 * @returns {String} HTML
 */
function create_quote_content(quote_id, quote_data) {
    if(!quote_data) return '';

    let src         = quote_data.src ? quote_data.src : '';
    let parent_type = quote_data.parent_type ? quote_data.parent_type : '';
    let type        = quote_data.type ? quote_data.type : '';
    let message     = quote_data.message ? quote_data.message : '';
    let thumbnail   = quote_data.thumbnail ? quote_data.thumbnail : '';  
    let name = src == 1 ? $('#profile_zalo_name').attr('data') : OA_NAME;

    let text = '';
    let img = '';

    if(parent_type == 'call') {
        text = message;
    }
    else if(type == 'text') {
        text = message;
    }
    else if(type == 'image') {
        img = `<img src="${thumbnail}" class="message-image" alt="Image reply">`;
        text = '[Hình ảnh]';
    }
    else if(type == 'sticker') {
        img = `<img src="${thumbnail}" alt="Image reply">`;
        text = '[Sticker]';
    }
    else if(type == 'links') {
        img = `<img src="${thumbnail}" alt="Image reply">`;
        text = '[Tin liên kết]';
    }
    else if(type == 'location-message') {
        text = '[Vị trí]';
    }
    else if(type == 'voice-message') {
        text = '[Tin nhắn thoại]';
    }
    else if(type == 'file') {
        let file_name = quote_data.filename ? quote_data.filename : 'Tệp đính kèm';
        img = `<img src="${thumbnail}" alt="Image reply">`;
        text = `[File] ${file_name}`;
    }
    else if(type == 'video') {
        text = '[Video]';
    }
    else {
        text = '[Tin nhắn chưa hỗ trợ]';
    }

    return `
        <div class="quote-content">
            <div class="line"></div>
            <a class="app__quote-content" href="#mess-${quote_id}">
                ${img}
                <div>
                    <div class="user-name">${name}</div>
                    <div class="user-content">
                        <span>${text}</span>
                    </div>
                </div>
            </a>
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
    let parent_type = message_data.message_type ? message_data.message_type : 'consultation';
    let type    = message_data.type ? message_data.type : '';
    let prefix  = parseInt(message_data.src) === 0 ? '<span style="margin-right:4px">OA:</span>' : '';
    let text    = message_data.message ? message_data.message : '';
    let time    = message_data.timestamp ? formatTimestampListChat(message_data.timestamp) : (message_data.time ? formatTimestampListChat(message_data.time) : '');
    let num     = message_data.num ? message_data.num : '';

    // User data
    let zalo_id = user_data.user_id ? user_data.user_id : '';
    if(zalo_id == '') zalo_id = user_data.id ? user_data.id : '';
    let name = user_data.user_alias ? user_data.user_alias : '';
    if(name == '') name = user_data.display_name ? user_data.display_name : '';
    if(name == '') name = user_data.name ? user_data.name : '';
    let avatar  = user_data.avatar ? user_data.avatar : DEFAULT_AVATAR;
    let tags    = user_data.tags_and_notes_info ? user_data.tags_and_notes_info.tag_names : [];
    let html_tags = tags.length > 0 ? '<div class="tag-content mt-1">' : '<div class="tag-content">';
    $.each(tags , function(index, t) { html_tags += `<div class="tag" data="${t}">${t}</div>`; });
    html_tags += '</div>';

    let content = '';
    if(parent_type == 'zns') {
        content = `<div class="lastest_message">${prefix + text}</div>`;
    }
    else if(parent_type == 'call') {
        let call_type = get_style_call_message(type, '#bdbdbd');

        content = `<div class="lastest_message">
            ${prefix}
            <div class="icon icon_call">${call_type.icon}</div>
            ${type}
        </div>`;
    }
    else if(parent_type == 'custom') {
        content = `<div class="lastest_message"><i>${prefix + text}</i></div>`;
    }
    else {
        if(type == 'text') {
            content = `<div class="lastest_message">${prefix + text}</div>`;
        }
        else if(type == 'photo' || type == 'image') {
            content = `<div class="lastest_message">
                ${prefix}
                <div class="icon icon_${type}">${getIcons('image', '#8D8D8F', 20, 21)}</div>
                Hình ảnh
            </div>`;
        }
        else if(type == 'gif') {
            content = `<div class="lastest_message">
                ${prefix}
                <div class="icon icon_${type}">${getIcons('gif', '#8D8D8F', 20, 17)}</div>
                GIF
            </div>`;
        }
        else if(type == 'sticker') {
            content = `<div class="lastest_message">
                ${prefix}
                <div class="icon icon_${type}">${getIcons('sticker', '#8D8D8F', 20, 21)}</div>
                Sticker
            </div>`;
        }
        else if(type == 'voice' || type == 'audio') {
            content = `<div class="lastest_message">
                ${prefix}
                <div class="icon icon_${type}">${getIcons('voice', '#8D8D8F', 20, 21)}</div>
                Tin nhắn thoại
            </div>`;
        }
        else if(type == 'video') {
            content = `<div class="lastest_message">
                ${prefix}
                <div class="icon icon_${type}">${getIcons('video', '#8D8D8F')}</div>
                Video
            </div>`;
        }
        else if(type == 'file') {
            content = `<div class="lastest_message">
                ${prefix}
                <div class="icon icon_${type}">${getIcons('file', '#8D8D8F', '20px', '18px')}</div>
                Tệp đính kèm
            </div>`;
        }
        else if(type == 'link' || type == 'links') {
            content = `<div class="lastest_message">${prefix}[Tin liên kết]</div>`;
        }
        else if(type == 'location') {
            content = `<div class="lastest_message">
                ${prefix}
                <div class="icon icon_${type}">${getIcons('location', '#8D8D8F', 20, 21)}</div>
                Vị trí
            </div>`;
        }
        else if(type == 'business_card') {
            content = `<div class="lastest_message">
                ${prefix}
                <div class="icon icon_${type}">${getIcons('bcard', '#8D8D8F', 20, 17)}</div>
                Danh thiếp
            </div>`;
        }
        else {
            content = `<div class="lastest_message">${prefix}Bạn có một tin nhắn mới</div>`;
        }
    }

    if(return_only_content) return content;
    return `<li class="item_mess item_mess_new mess_links" id="li${zalo_id}">
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
        <input type="hidden" id="user_data_${zalo_id}" value="${user_data ? btoa(encodeURIComponent(JSON.stringify(user_data))) : ''}">
    </li>`;
}

/**
 * Create HTML <li> for new user chat
 * 
 * @param {String} zalo_id
 * @param {Object} message_data
 * @returns 
 */
function create_li_chat_new(zalo_id, message_data) {
    if(zalo_id && zalo_id.length > 10) {
        $.ajax({
            url: URL,
            type: "POST",
            contentType: "application/json",
            dataType: "json",
            data: JSON.stringify({
                class: "entryZaloOAClass",
                method: "getUserInfo",
                params: {
                    oa_id: OA_ID,
                    zalo_id: zalo_id
                }
            }),
            success: function (response) {
                if (response && response?.status && response.status == 1) {
                    let li = create_li_chat(message_data, response.data);
                    $('#list_mess_main').prepend(li);
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                console.error(XMLHttpRequest, `Status: ${textStatus}`, `Error: ${errorThrown}`);
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

    // let formData = new FormData();
    // formData.append('action', 'send_message');
    // formData.append('oa_id', OA_ID);
    // formData.append('zalo_id', zalo_id);
    // formData.append('text', text);

    let params = {};
    params.oa_id = OA_ID;
    params.zalo_id = zalo_id;
    params.text = text;

    /*****  2. Type message  *****/
    let type = 'text'; // Default
    // Tin nhắn trả lời (Quote)
    if($('input[name="quote_message_id"]').length) {
        // formData.append('quote_message_id', $('input[name="quote_message_id"]').val());
        params.quote_message_id = $('input[name="quote_message_id"]').val();
    }
    else if(input_image[0].files.length > 0) {
        type = 'image';
        let image = input_image[0].files[0];
        let url   = $('#preview_image_upload').attr('src')
        // formData.append('image', image);
        // formData.append('url', url);
        params.image = image;
        params.url = url;
    }
    else if(input_file[0].files.length > 0) {
        type = 'file';
        let file = input_file[0].files[0];
        // formData.append('file', file);
        params.file = file;
    }
    // formData.append('type', type);
    params.type = type;

    let formData = new FormData();
    formData.append('class', 'entryZaloOAClass');
    formData.append('method', 'sendMessage');
    formData.append('params', params);
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
            contentType: "application/json",
            dataType: "json",
            data: JSON.stringify(data),
            beforeSend: function() {
                $('.loader_send_message').remove();
                $('#section-message__details').append('<div class="loader_send_message"></div>');
                scroll_messages_bottom();
            },
            success: function (response) {
                $('.loader_send_message').remove();
    
                if(!('status' in response) || response.status == 0) {
                    let m = response.message || 'Thao tác thất bại';
                    let d = response.description || '';
                    showModalNotify('error', m, d);
                    return false;
                }

                // Update quota user
                if('quota' in response.data && response.data.quota) handleQuotaUser(response.data.quota);
                else handleQuotaUser(null);
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                $('.loader_send_message').remove();
                showModalNotify('error', 'Thao tác thất bại, vui lòng thử lại');
                console.error(XMLHttpRequest, `Status: ${textStatus}`, `Error: ${errorThrown}`);
            }
        });
    }
}

/**
 * Handle HTML quota for user in chat box and related events
 * Làm lại hàm này với 2 action
 * 
 * @param {object} quota
 * @param {number} last_interaction Timestamp milliseconds
 * @returns
 */
function handleQuotaUser(quota, last_interaction = null) {
    let quota_html = '';
    let time_check_day = (last_interaction && last_interaction > 0) ? ~~((Date.now() - parseInt(last_interaction)) / 1000 / 3600 / 24) : 0;

    // Thông tin quota từ user
    if(last_interaction !== null) {
        if(time_check_day > 7) {
            quota_html = `<div class="noti-mess-feedback noti_grey">
                <span>Không thể trò chuyện. Người dùng đã hết tương tác với OA trong vòng 7 ngày gần nhất</span>
            </div>`;
            disable_send_message();
        }
        else {
            console.warn(typeof quota.cs_reply.remain);
            console.warn(oa_sub_quota);
            if(quota?.cs_reply && quota.cs_reply.remain > 0) {
                quota_html = `<div class="noti-mess-feedback noti_green">
                    <span>Còn ${quota.cs_reply.remain}/${quota.cs_reply.total} tin nhắn miễn phí với người dùng trong 48h</span>
                </div>`;
                enable_send_message();
            }
            // Xét quota OA
            else if (oa_sub_quota > 0) {
                oa_sub_quota -= 1;
                quota_html = `<div class="noti-mess-feedback noti_blue">
                    <span>Tin nhắn tiếp theo được miễn phí (Đặc quyền OA Premium)</span>
                </div>`;
                enable_send_message();
            }
            else {
                quota_html = `<div class="noti-mess-feedback noti_yellow">
                    <span>Mỗi tin nhắn tiếp theo sẽ tốn 55đ/tin</span>
                </div>`;
                enable_send_message();
            }
        }
    }
    // Thông tin quota từ action gửi tin
    else {
        if(quota !== null && quota?.quota_type && quota.quota_type == 'reply') {
            quota_html = `<div class="noti-mess-feedback noti_green">
                <span>Còn ${quota.remain}/${quota.total} tin nhắn miễn phí với người dùng trong 48h</span>
            </div>`;
            enable_send_message();
        }
        else if(quota !== null && quota?.quota_type && quota.quota_type == 'sub_quota') {
            quota_html = `<div class="noti-mess-feedback noti_blue">
                <span>Tin nhắn tiếp theo được miễn phí (Đặc quyền OA Premium)</span>
            </div>`;
            enable_send_message();
        }
        else if(quota === null) {
            quota_html = `<div class="noti-mess-feedback noti_yellow">
                <span>Mỗi tin nhắn tiếp theo sẽ tốn 55đ/tin</span>
            </div>`;
            enable_send_message();
        }
    }

    $('#quota_content').html(quota_html);
}

/**
* Handle file 
* 
* @param {File} file
* @return {void}
*/
function handleFile(file) {
    const fileName = file.name;
    const fileSize = file.size;
    const fileExt = fileName.split('.').pop().toLowerCase();
    const listImageExtension = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'heic', 'heif', 'bmp', 'tif', 'tiff', 'ico', 'svg'];
    var isImage = listImageExtension.includes(fileExt) ? true : false;

    // Validate file
    var inputID = '';
    if(IMAGE_EXTENSION.includes(fileExt)) {
        if(fileExt == 'gif' && fileSize > 5*1024*1024) {
            showModalNotify('warning', `Dung lượng GIF tối đa 5MB`);
            return false;
        }
        else if(fileSize > 1024*1024) {
            showModalNotify('warning', `Dung lượng hình ảnh tối đa 1MB`);
            return false;
        }
        inputID = 'input_image_upload';
    }
    else if(FILE_EXTENSION.includes(fileExt)) {
        if(fileSize > 5*1024*1024) {
            showModalNotify('warning', `Dung lượng tệp tối đa 5MB`);
            return false;
        }
        inputID = 'input_file_upload';
    }
    if(!inputID || inputID == '') {
        if(isImage) showModalNotify('error', `Không hỗ trợ định dạng .${fileExt}`, `Các định dạng ảnh hỗ trợ: <b>${IMAGE_EXTENSION.join(', ')}</b>`);
        else showModalNotify('error', `Không hỗ trợ định dạng .${fileExt}`, `Các định dạng tệp hỗ trợ: <b>${FILE_EXTENSION.join(', ')}</b>`);
        return false;
    }

    // Handle display
    const reader = new FileReader();
    reader.onload = (e) => {
        if(isImage) {
            $('#preview_image_upload').attr('src', e.target.result).show();
            broadcast_admin_action('sending_image');
        }
        else {
            $('#preview_file_upload .file-image img').attr('src', getImageFile(fileExt));
            $('#preview_file_upload .file-name').text(fileName);
            $('#preview_file_upload .file-size').text(formatFileSize(fileSize));
            $('#preview_file_upload').css('display', 'flex');
            broadcast_admin_action('sending_file');
        }
    };
    reader.readAsDataURL(file);

    // Put the file data into file input
    const dataTransfer = new DataTransfer();
    const fileInput = document.getElementById(inputID);
    dataTransfer.items.add(file);
    fileInput.files = dataTransfer.files;
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

                res = JSON.parse(response); // Object
                if (res['error'] !== 0) {
                    let m = res['message'] ? res['message'] : 'Thao tác thất bại';
                    let d = res['description'] ? res['description'] : '';
                    showModalNotify('error', m, d);
                    return false;
                }

                // Reset
                $(`input.checkbox_tag[value="${tag_name}"]`).removeAttr('disabled');
                $(`input.checkbox_tag`).prop('checked', false);

                // Recheck
                $(`input.checkbox_tag[value="${tag_name}"]`).prop('checked', true);
                $(`input.checkbox_tag[value="${tag_name}"]`).prop('disabled', 'disabled');

                // Display new tag in header user
                $(`#user_tag_display .title`).text(tag_name);
                $(`#user_tag_display`).attr('data', tag_name);
                
                // Display new tag in list user
                if($(`#li${zalo_id} .tag-content .tag`).length) {
                    $(`#li${zalo_id} .tag-content .tag`).text(tag_name);
                    $(`#li${zalo_id} .tag-content .tag`).attr('data', tag_name);
                }
                else {
                    $(`#li${zalo_id} .tag-content`).html(`<div class="tag" data="${tag_name}">${tag_name}</div>`);
                }
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
                
                res = JSON.parse(response); // Object
                if (res['error'] !== 0) {
                    let m = res['message'] ? res['message'] : 'Thao tác thất bại';
                    let d = res['description'] ? res['description'] : '';
                    showModalNotify('error', m, d);
                    return false;
                }

                $(`input.checkbox_tag[value="${tag_name}"]`).removeAttr('disabled');
                $(`input.checkbox_tag[value="${tag_name}"]`).prop('checked', false);

                // Reset tag in header user
                $(`#user_tag_display .title`).text('Nhãn');
                $(`#user_tag_display`).attr('data', '');
                
                // Remove tag in list user
                $(`#li${zalo_id} .tag-content .tag[data="${tag_name}"]`).remove();
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
}

/**
 * Reset global content
 */
function resetGlobalContent() {
    $('body').addClass('sidebar-icon-only');
    $('#sidebar').removeClass('expanded-sidebar');
    $('#sidebar').addClass('collapsed-sidebar');
    $('#buttontoggle').hide();
}

/**
 * Reset all content in chat box (textarea)
 */
function resetChatBox() {
    // Reset text
    $('textarea[name="message_content"]').val("");
    // Reset quote
    $('.preview_mess').remove();
    resetContentUpload();
}

/**
 * Reset content upload in chat box
 * 
 * @param {String} type image, file,...
 */
function resetContentUpload(type = 'all') {
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
    let chatBox = document.getElementById('section_chatbox');
    chatBox.scrollTop = chatBox.scrollHeight;
    // $('.section-post').scrollTop($('.section-post')[0].scrollHeight);
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
 * Get label name from key in ZNS template
 * 
 * @param {string} key
 * @return {string}
 */
function get_label_name_template_zns(key) {
    switch (key) {
        case 'booking':
            return 'Booking';
        case 'lien_he':
        case 'full_name':
            return 'Liên hệ';
        case 'hang_hang_khong':
            return 'Hãng hàng không';
        case 'ma_chuyen':
        case 'flight_no':
            return 'Mã chuyến';
        case 'hang_ve':
            return 'Hạng vé';
        case 'noi_di':
            return 'Nơi đi';
        case 'noi_di':
            return 'Nơi đến';
        case 'chieu_di':
            return 'Chiều đi';
        case 'chieu_ve':
            return 'Chiều về';
        case 'ngay_gio_di':
            return 'Ngày giờ đi';
        case 'ngay_gio_ve':
            return 'Ngày giờ về';
        case 'chuyen_bay_di':
            return 'Chuyến bay đi';
        case 'chuyen_bay_ve':
            return 'Chuyến bay về';
        case 'datetime':
            return 'Ngày giờ bay';
        case 'hanh_khach':
            return 'Hành khách';
        case 'hanh_ly':
            return 'Hành lý';
        case 'ten_hk':
            return 'Tên hành khách';
        case 'han_giu_cho':
            return 'Hạn giữ chỗ';
        case 'dia_chi_vp_1':
            return 'Địa chỉ VP'
        case 'transfer_amount':
            return 'Số tiền';
        case 'bank_transfer_note':
            return 'Nội dung CK';
        case 'code_pnr':
        case 'pnr':
            return 'PNR';
        case 'journey':
        case 'journey_old':
            return 'Hành trình';
        case 'journey_new':
            return 'Chuyển sang';
        case 'point':
            return 'Điểm tích lũy';
        case 'total_point':
            return 'Tổng điểm';
        default:
            return '';
    }
}

/**
 * Get style call message
 * 
 * @param {string} type
 * @param {string} color
 * @return {object}
 */
function get_style_call_message(type, color = '') {
    switch (type) {
        case 'Cuộc gọi đến':
            color = color.length == 0 ? '#00ac47' : color;
            return {'classname': 'text-success', 'icon': getIcons('inbound_call', color, 15, 15)};
        case 'Cuộc gọi đi':
            color = color.length == 0 ? '#0d6efd' : color;
            return {'classname': 'text-primary', 'icon': getIcons('outbound_call', color, 15, 15)};
        case 'Cuộc gọi nhỡ':
            color = color.length == 0 ? '#dc3545' : color;
            return {'classname': 'text-danger', 'icon': getIcons('missed_call', color, 15, 15)};
        default:
            color = color.length == 0 ? '#8c8c8c' : color;
            return {'classname': '', 'icon': getIcons('call', color, 15, 15)};
    }
}

/**
 * Format call duration
 * 
 * @param {int} seconds
 * @return {string}
 */
function format_call_duration(seconds) {
    const hours = Math.floor(seconds / 3600);
    seconds %= 3600;
    const minutes = Math.floor(seconds / 60);
    seconds %= 60;

    let result = '';
    if (hours > 0) {
        result += `${hours} giờ`;
    }
    if (minutes > 0) {
        result += `${minutes} phút`;
    }
    if (seconds > 0 || result === '') {
        result += `${seconds} giây`;
    }
    return result.trim();
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
        case 'png':
        case 'jpg':
        case 'jpeg':
            return image_file.image;
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
    timestamp           = parseInt(timestamp);
    let sent_time       = formatTimestampZalo(timestamp, 'd/m/Y', 'H:i:s', 'time');
    let datetimeline    = new Date(timestamp);
    let dateofweek      = mapDateOfWeek(datetimeline.getDay());
    let timeline        = getSentTimeZalo(sent_time);
    let text_timeline   = `${timeline} ${dateofweek}, ` + sent_time.split(' ')[1];
    return `<div class="_sectionTimestamp" id="_sectionTimestamp${timestamp}"><span>${text_timeline}</span></div>`;
}

/**
 * Create business card from json data
 * 
 * @param {string} json
 * @param {string} thumbnail
 * @param {string} name
 * @return {string} HTML
 */
function getBusinessCard(json, thumbnail, name = '') {
    if(isJSON(json)) {
        let card = JSON.parse(json);
        if(!card.title || card.title.length == 0) card.title = name;

        return `<div class="card business-card bg-primary text-white">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-8 col-8 left">
                        <img class="avatar" class="message-image" src="${thumbnail}" />
                        <div class="info">
                            <p>${card.title}</p>
                            <span id="card_phone" class="card-phone">${card.phone ?? ''}</span>
                        </div>
                    </div>
                    <div class="col-sm-4 col-4 right">
                        <img class="qrcode" src="${card.qrCodeUrl ?? '#'}" />
                    </div>
                </div>
            </div>
        </div>`;
    }
    return '';
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
    let text_format = text;
    // Format new line
    text_format = text_format.trim().replace(/\r\n/g, "<br />");
    // Format URL
    const regex = /\bhttps?:\/\/\S+/g;
    text_format = text_format.replace(regex, (url) => {
        return `<a href="${url}" target="_blank">${url}</a>`;
    });
    return text_format;
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

		if(date_format.charAt(0) === 'd')
            date = `${day}${sep}${month}${sep}${year}`;
		else if(date_format.charAt(0) === 'Y')
            date = `${year}${sep}${month}${sep}${day}`;
		else
            date = `${month}${sep}${day}${sep}${year}`;
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
 * Format datetime from timestamp
 * 
 * @param {Int} timestamp
 * @param {string} date_format
 * @param {string} time_format 
 * @return {string}
 */
function formatTimestampListChat(timestamp, date_format = 'd/m/Y', time_format = 'H:i') {
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

    const current_date = new Date();
    const current_day = String(current_date.getDate()).padStart(2, '0');
    const current_month = String(current_date.getMonth() + 1).padStart(2, '0');
    const current_year = current_date.getFullYear();

    // Format date 
    let date_result = '';
    let fullcurrentdate = '';
    let sep = '/';
	if(date_format.length > 0) {
		sep = date_format.indexOf('/') !== -1 ? '/' : '-';

		if(date_format.charAt(0) === 'd') {
            date_result = `${day}${sep}${month}${sep}${year}`;
            fullcurrentdate = `${current_day}${sep}${current_month}${sep}${current_year}`;
        }
		else if(date_format.charAt(0) === 'Y') {
            date_result = `${year}${sep}${month}${sep}${day}`;
            fullcurrentdate = `${current_day}${sep}${current_month}${sep}${current_year}`;
        }
		else {
            date_result = `${month}${sep}${day}${sep}${year}`;
            fullcurrentdate = `${current_month}${sep}${current_day}${sep}${current_year}`;
        }
	}   

    // Format time
    let time_result = '';
	if(time_format.length == 5) time_result = `${hours}:${minutes}:${seconds}`;
	else if(time_format.length == 3) time_result = `${hours}:${minutes}`;
	else if(time_format.length == 1) {
		if(time_format === 'H') time_result = hours;
		else if(time_format === 'i') time_result = minutes;
		else if(time_format === 's') time_result = seconds;
	}

    // Format step 2
    if(date_result == fullcurrentdate) date_result = '';
    else if(year == current_year && month == current_month) {
        let diff = parseInt(current_day) - parseInt(day);
        if(diff == 1) date_result = 'Hôm qua';
        else if(diff > 1 && diff < 6) date_result = mapDateOfWeek(Date_.getDay());
        else date_result = `${day}${sep}${month}`;
        time_result = '';
    }
    else if(year == current_year) {
        date_result = `${day}${sep}${month}`;
        time_result = '';
    }
    else time_result = '';

    return `${date_result} ${time_result}`.trim();
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

/**
 * Format full address
 * 
 * @param {string} address Street name
 * @param {string} district District name
 * @param {string} city City ​​name
 * @return {string}
 */
function formatFullAddress(address, district, city) {
    let full_address = '';
    full_address += address;
    full_address += district.length == 0 ? '' : `, ${district}`;
    full_address += city.length == 0 ? '' : `, ${city}`;
    full_address = full_address.length == 0 ? 'Chưa công khai' : full_address;

    if(full_address.charAt(0) === ',') full_address = full_address.substring(1).trim();
    return full_address;
}

function loadingSkeleton(type = 'list_user', qty = 6) {
    if(type == 'list_user') {
        let html = '';
        for(let i = 0; i < qty; i++) {
            html += `<li class="item_mess item_mess_new mess_links item_mess_skeleton">
                <div class="mess_avt">
                    <div class="imgDrop skeleton-item skeleton-imgDrop"></div>
                </div>
                <div class="__content">
                    <div class="info_content">  
                        <div class="mess_content">
                            <div class="mess_name truncate skeleton-item skeleton-mess_name"></div>
                        </div>
                        <div class="mess_more has_btn_more">
                            <div class="mess_time skeleton-item skeleton-mess_time"></div>
                            <div class="mess_number"></div>
                        </div>
                    </div>
                    <div class="box-parent box-lastest_message skeleton-item skeleton-lastest_message"></div>
                </div>
            </li>`
        }
        $('#list_mess_main').append(html);
    }
}
function removeLoadingSkeleton() {
    $('.item_mess_skeleton').remove();
}

function decodeUrl(encodedUrl) {
    // Create a temporary element to decode HTML entities
    const element = document.createElement('div');
    element.innerHTML = encodedUrl;
    
    // Return the decoded URL
    return element.textContent || element.innerText;
}

function mapDateOfWeek(num) {
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
