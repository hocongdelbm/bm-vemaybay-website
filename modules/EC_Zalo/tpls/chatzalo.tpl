<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.css">
<link type="text/css" rel="stylesheet" href="modules/EC_Zalo/css/chatzalo.css?v=3.6">

<div class="wrap-content wrap-content-chat">
    <div class="content-page zalochat-page">
        <div id="content_main" class="content_main">
            <div class="zalochat_component">
                <div id="zalochat_sidebar" class="zalochat_sidebar">
                    <div class="chat-sidebar__top">
                        <div class="type">
                            <div class="dropdown dropdown-type-list">
                                <button type="button" class="btn btn-dropdown" data-bs-toggle="dropdown">
                                    <span id="text_display_type_list">Mặc định</span>
                                    <i class="icon icon_dropdown">
                                        <svg width="20px" height="20px" stroke-width="1.5" viewBox="0 0 24 24"
                                            fill="none" xmlns="http://www.w3.org/2000/svg" color="#000000">
                                            <path d="M6 9L12 15L18 9" stroke="#000000" stroke-width="1.5"
                                                stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </i>
                                </button>
                                <ul class="dropdown-menu">
                                    {$LI_TAGS}
                                </ul>
                                <input type="hidden" name="user_type_list" value="default" />
                            </div>
                        </div>
                        
                        <div class="search-icon">
                            <i class="icon icon_search" id="search_user">
                                <svg width="20px" height="20px" viewBox="0 0 24 24" stroke-width="1.5" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" color="#000000">
                                    <path d="M17 17L21 21" stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                    <path
                                        d="M3 11C3 15.4183 6.58172 19 11 19C13.213 19 15.2161 18.1015 16.6644 16.6493C18.1077 15.2022 19 13.2053 19 11C19 6.58172 15.4183 3 11 3C6.58172 3 3 6.58172 3 11Z"
                                        stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                </svg>
                            </i>
                        </div>

                        <div class="search-processing">
                            <div class="wrap">
                                <i class="icon icon_search">
                                    <svg width="20px" height="20px" viewBox="0 0 24 24" stroke-width="1.5" fill="none" xmlns="http://www.w3.org/2000/svg" color="#939393">
                                        <path d="M17 17L21 21" stroke="#939393" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                        <path d="M3 11C3 15.4183 6.58172 19 11 19C13.213 19 15.2161 18.1015 16.6644 16.6493C18.1077 15.2022 19 13.2053 19 11C19 6.58172 15.4183 3 11 3C6.58172 3 3 6.58172 3 11Z" stroke="#939393" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </i>
                                <input type="text" name="search_user" class="input-search-user" placeholder="Nhập tên, số điện thoại" />
                                <button type="button" class="btn btn-secondary close-search-processing">Đóng</button>
                            </div>
                        </div>
                    </div>
                    <div class="chat-sidebar__bottom">
                        <div class="noti_statistics"></div>
                        <ul id="list_mess_main" class="list_mess"></ul>
                        <ul id="list_mess_search" class="list_mess"></ul>
                    </div>
                </div>

                <div id="zalochat_main" class="zalochat_main">
                    <div id="chat_welcome" class="content chat_welcome"></div>
                    <div class="zalochat_content" id="content_chat">
                        <div class="group-feed">
                            <section class="user-current">
                                <div class="info_avt">
                                    <div class="imgDrop">
                                        <img id="header_avatar_chat" src="{$DEFAULT_AVATAR}" alt="convers_avt">
                                    </div>
                                </div>
                                <div class="snippet">
                                    <div class="form-edit-name">
                                        <div class="input">
                                            <input type="text" name="alias_edit" value="" maxlength="250">
                                            <div class="input_sub"></div>
                                        </div>
                                        <button id="cancel_alias_edit"
                                            class="btn btn_line btn-blue func-close">Hủy</button>
                                        <button id="save_alias_edit" class="btn btn_bg btn-blue func-close">Lưu</button>
                                    </div>
                                    <div class="item-title nickname">
                                        <div class="u__name">
                                            <span id="header_name_chat" class="name_truncate">Người dùng</span>
                                            <i id="edit_alias" class="icon icon_edit">
                                                <svg width="16" height="16" viewBox="0 0 24 24" color="#000000" stroke-width="1.5" fill="none" xmlns="http://www.w3.org/2000/svg" >
                                                    <path stroke="#000000" stroke-width="1.5" d="M14.3632 5.65156L15.8431 4.17157C16.6242 3.39052 17.8905 3.39052 18.6716 4.17157L20.0858 5.58579C20.8668 6.36683 20.8668 7.63316 20.0858 8.41421L18.6058 9.8942M14.3632 5.65156L4.74749 15.2672C4.41542 15.5993 4.21079 16.0376 4.16947 16.5054L3.92738 19.2459C3.87261 19.8659 4.39148 20.3848 5.0115 20.33L7.75191 20.0879C8.21972 20.0466 8.65806 19.8419 8.99013 19.5099L18.6058 9.8942M14.3632 5.65156L18.6058 9.8942" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </svg>
                                            </i>
                                        </div>
                                    </div>
                                    <div class="item-message flexBox">
                                        <section class="choose_label">
                                            <ul class="list_tag flexBox">
                                                <span id="header_follow_chat" class="user-unfollowed">Chưa quan tâm</span>
                                            </ul>
                                        </section>

                                        <section class="dropdown dropdown-tags choose_label">
                                            <div class="dropdown_btn" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                                <div id="user_tag_display" class="input_choose tag">
                                                    <div class="title">Nhãn</div>
                                                    <svg height="20" width="20" color="#69686D" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg" style="color: rgb(105, 104, 109);">
                                                        <polyline points="6 9 12 15 18 9"></polyline>
                                                    </svg>
                                                </div>
                                            </div>
                                            <ul id="header_tags_chat" class="dropdown-menu list-tags">
                                                {$CHECKBOX_TAGS}
                                            </ul>
                                        </section>
                                    </div>
                                </div>
                                <a id="func-call" class="func-call">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor"
                                        class="bi bi-telephone" viewBox="0 0 16 16">
                                        <path
                                            d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z" />
                                    </svg>
                                </a>
                                <a id="func-slide" class="func-slide slide">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor"
                                        class="bi bi-layout-sidebar-inset-reverse" viewBox="0 0 16 16">
                                        <path
                                            d="M2 2a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1zm12-1a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2z" />
                                        <path
                                            d="M13 4a1 1 0 0 0-1-1h-2a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1z" />
                                    </svg>
                                </a>
                            </section>
                            <section class="section-message">
                                <div class="message__list">
                                    <div class="section-post" id="section_chatbox">
                                        <input type="hidden" name="offset_load_more_message" value="0" readonly>
                                        <div id="section-message__details" class="section-message__details"></div>
                                    </div>
                                </div>
                                <div class="display-admin-typing">
                                    <div>
                                        <b id="admin_typing"></b> <span id="admin_action"></span>
                                        <div class="loader_typing"></div>
                                    </div>
                                </div>
                            </section>
                            <section class="section-compose">
                                <div class="add-msg-box">
                                    <div class="section-compose-inline">
                                        <div class="notion_bottom">
                                            <div class="noti-typing-wrap"></div>
                                            <div id="quota_content" class="reply"></div>
                                        </div>
                                    </div>
                                    <div class="add-msg-box-func">
                                        <div class="chat_item choose-image">
                                            <input type="file" name="image_upload" id="input_image_upload" class="hidden"
                                                accept="image/png, image/jpg, image/gif" />
                                            <button id="upload_image" class="btn-action btn-upload-image">
                                                <svg width="20" height="21" viewBox="0 0 20 21" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                        d="M12.8 17.8001C13.904 17.8001 14.8 16.9041 14.8 15.8001V14.7121C14.256 14.4801 13.824 13.8801 13.344 13.2081C12.696 12.3201 11.96 11.3121 11.024 11.3121C10.184 11.3121 9.52 12.1121 8.824 12.9601C8.072 13.8801 7.296 14.8241 6.152 14.8241C4.952 14.8241 4.32 14.3281 3.864 13.9681C3.504 13.6801 3.312 13.5361 2.968 13.5361C2.36 13.5361 1.768 13.9921 1.2 14.8801V15.8001C1.2 16.9041 2.096 17.8001 3.2 17.8001H12.8ZM3.2 4.2C2.096 4.2 1.2 5.096 1.2 6.2V13.032C1.744 12.568 2.336 12.336 2.968 12.336C3.73126 12.336 4.18494 12.692 4.58787 13.0082C4.59459 13.0135 4.6013 13.0187 4.608 13.024L4.61571 13.03C5.02045 13.3475 5.37294 13.624 6.152 13.624C6.73513 13.624 7.29434 12.938 7.89333 12.2033L7.896 12.2L7.91061 12.1823C8.715 11.2106 9.62448 10.112 11.024 10.112C12.5675 10.112 13.5353 11.4391 14.3112 12.5029L14.312 12.504C14.44 12.68 14.624 12.928 14.8 13.144V6.2C14.8 5.096 13.904 4.2 12.8 4.2H3.2ZM3.2 3H12.8C14.568 3 16 4.432 16 6.2V15.8C16 17.568 14.568 19 12.8 19H3.2C1.432 19 0 17.568 0 15.8V6.2C0 4.432 1.432 3 3.2 3ZM4.96356 7.47055C4.96356 6.82095 4.43636 6.29375 3.78676 6.29375C3.13716 6.29375 2.60996 6.82095 2.60996 7.47055C2.60996 8.12015 3.13716 8.64655 3.78676 8.64655C4.43636 8.64655 4.96356 8.12015 4.96356 7.47055Z"
                                                        fill="#8D8D8F"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="chat_item choose-file">
                                            <input type="file" name="file_upload" id="input_file_upload" class="hidden"
                                                accept="application/pdf, application/msword, application/vnd.ms-excel, application/vnd.ms-powerpoint, text/plain" />
                                            <button id="upload_file" class="btn-action btn-upload-file">
                                                <svg width="20px" height="18px" stroke-width="1.5" viewBox="0 0 24 24"
                                                    fill="none" xmlns="http://www.w3.org/2000/svg" color="#8D8D8F">
                                                    <path
                                                        d="M21.4383 11.6622L12.2483 20.8522C11.1225 21.9781 9.59552 22.6106 8.00334 22.6106C6.41115 22.6106 4.88418 21.9781 3.75834 20.8522C2.63249 19.7264 2 18.1994 2 16.6072C2 15.015 2.63249 13.4881 3.75834 12.3622L12.9483 3.17222C13.6989 2.42166 14.7169 2 15.7783 2C16.8398 2 17.8578 2.42166 18.6083 3.17222C19.3589 3.92279 19.7806 4.94077 19.7806 6.00222C19.7806 7.06368 19.3589 8.08166 18.6083 8.83222L9.40834 18.0222C9.03306 18.3975 8.52406 18.6083 7.99334 18.6083C7.46261 18.6083 6.95362 18.3975 6.57834 18.0222C6.20306 17.6469 5.99222 17.138 5.99222 16.6072C5.99222 16.0765 6.20306 15.5675 6.57834 15.1922L15.0683 6.71222"
                                                        stroke="#8D8D8F" stroke-width="1.5" stroke-linecap="round"
                                                        stroke-linejoin="round"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="add-msg-box-txtArea">
                                        <div class="preview_upload">
                                            <img id="preview_image_upload" class="preview-image-upload" src="#"
                                                alt="Upload image" />
                                            <div id="preview_file_upload" class="preview-file-upload">
                                                <div class="file-image">
                                                    <img src="" alt="Images of file" />
                                                </div>
                                                <div class="file-info">
                                                    <p class="file-name"></p>
                                                    <p class="file-size"></p>
                                                </div>
                                            </div>
                                            <i class="icon icon_close" id="reset_upload_content">
                                                <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg" color="#000000"
                                                    stroke-width="1.5">
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                        d="M12 1.25C6.06294 1.25 1.25 6.06294 1.25 12C1.25 17.9371 6.06294 22.75 12 22.75C17.9371 22.75 22.75 17.9371 22.75 12C22.75 6.06294 17.9371 1.25 12 1.25ZM9.70164 8.64124C9.40875 8.34835 8.93388 8.34835 8.64098 8.64124C8.34809 8.93414 8.34809 9.40901 8.64098 9.7019L10.9391 12L8.64098 14.2981C8.34809 14.591 8.34809 15.0659 8.64098 15.3588C8.93388 15.6517 9.40875 15.6517 9.70164 15.3588L11.9997 13.0607L14.2978 15.3588C14.5907 15.6517 15.0656 15.6517 15.3585 15.3588C15.6514 15.0659 15.6514 14.591 15.3585 14.2981L13.0604 12L15.3585 9.7019C15.6514 9.40901 15.6514 8.93414 15.3585 8.64124C15.0656 8.34835 14.5907 8.34835 14.2978 8.64124L11.9997 10.9393L9.70164 8.64124Z"
                                                        fill="#000000"></path>
                                                </svg>
                                            </i>
                                        </div>
                                        <div class="content_mess_input">
                                            <textarea name="message_content" id="textarea_message_content" class="textarea" maxlength="2000"
                                                placeholder="Nhập nội dung tin nhắn..."></textarea>
                                            <button id="send_message" class="content_send btn btn-primary"
                                                action="text">Gửi</button>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

                <div id="zalochat_profile" class="zalochat_profile">
                    <div style="min-width: 100%; display: table;">
                        <div class="zalochat_profile--wrap">
                            <div class="head py-12">
                                <div class="profile_name midle">
                                    <div class="info_avt">
                                        <div class="imgDrop">
                                            <img id="profile_avatar" src="{$DEFAULT_AVATAR}" alt="Profile avatar">
                                        </div>
                                    </div>
                                    <div class="username_content">
                                        <div id="profile_zalo_alias" class="user_alias">Người dùng</div>
                                        <div id="profile_zalo_name" class="user_name" data="">Người dùng</div>
                                        <input type="hidden" name="profile_shared_name" value="">
                                    </div>
                                </div>
                                <div class="profile_link mt-12" style="justify-content: space-between;">
                                    <div class="link_ttl">Link hội thoại:</div>
                                    <div class="flexBox">
                                        <a id="profile_chat_link" href="" class="link_content limit_text"
                                            target="_blank">https://oa.zalo....share</a>
                                        <div class="active" style="display: block;">
                                            <div class="btn_coppy"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="info_more mt-12">
                                    <dl>
                                        <dt>Địa chỉ:</dt>
                                        <dd id="profile_address">Chưa công khai</dd>
                                        <input type="hidden" name="profile_address_city" value="">
                                        <input type="hidden" name="profile_address_district" value="">
                                        <input type="hidden" name="profile_address_number" value="">
                                    </dl>
                                    <dl>
                                        <dt>Số điện thoại:</dt>
                                        <dd id="profile_mobile">Chưa công khai</dd>
                                    </dl>
                                </div>
                                <button id="btn_request_user_info" class="btn_info btn btn_bg btn-blue2 mt-2">Gửi yêu cầu chia sẻ thông tin</button>
                                <button id="btn_update_user_info" class="btn_info btn btn_bg btn-blue2 mt-2"
                                    data-bs-toggle="modal" data-bs-target="#modal_update_info_user">Cập nhật thông tin</button>
                            </div>
                            <div class="line mt-20"></div>
                            <div class="fun_item func-filter mt-20">
                                <div class="flexBox midle space">
                                    <div class="ttl_sub" style="font-weight: bold;">Quản lý nhãn</div>
                                    <a href="#" class="btn_manager_tag">Quản lý</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="modal_update_info_user">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Thông tin người dùng</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="info_user_id" value="">
                <div class="row pt-3">
                    <div class="col-3 label">Họ tên</div>
                    <div class="col-9 input">
                        <input type="text" name="info_user_name" class="form-control" value=""
                            placeholder="Nhập họ tên">
                    </div>
                </div>
                <div class="row pt-3">
                    <div class="col-3 label">Số điện thoại</div>
                    <div class="col-9 input">
                        <input type="text" name="info_user_phone" class="form-control" value=""
                            placeholder="Nhập số điện thoại">
                    </div>
                </div>
                <div class="row pt-3">
                    <div class="col-3 label">Tỉnh/Thành</div>
                    <div class="col-9 input">
                        <select name="info_user_city" class="form-select">
                            {$OPTION_CITIES}
                        </select>
                    </div>
                </div>
                <div class="row pt-3">
                    <div class="col-3 label">Quận/Huyện</div>
                    <div class="col-9 input">
                        <select name="info_user_district" class="form-select"></select>
                    </div>
                </div>
                <div class="row pt-3">
                    <div class="col-3 label">Địa chỉ</div>
                    <div class="col-9 input">
                        <textarea name="info_user_address" class="form-control" rows="3" placeholder="Nhập địa chỉ"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="close_save_user_info" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" id="save_user_info" class="btn btn-primary">Lưu</button>
            </div>
        </div>
    </div>
</div>

<div class="image-dialog" id="imageDialog">
    <span class="close" id="closeDialog">&times;</span>
    <img class="dialog-content" id="dialogImage" />
</div>

{$ICON_TEMPLATE}
<input type="hidden" name="oa_id" value="{$OA_ID}" readonly />
<input type="hidden" name="oa_name" value="{$OA_NAME}" readonly />
<input type="hidden" name="oa_avatar" value="{$OA_AVATAR}" readonly />
<input type="hidden" name="admin_id" value="{$ADMIN_ID}" readonly />
<input type="hidden" name="admin_name" value="{$ADMIN_NAME}" readonly />
<input type="hidden" name="default_avatar" value="{$DEFAULT_AVATAR}" readonly />
<input type="hidden" name="image_file" value="{$IMAGE_FILE}" readonly />
<input type="hidden" name="entrypoint" value="{$ENTRYPOINT}" readonly />
<input type="hidden" name="websocket_url" value="{$WEBSOCKET_URL}" readonly />
<input type="hidden" name="offset_list_user" value="0" readonly />
<input type="hidden" name="is_loading_list_user" value="0" readonly />
<input type="hidden" name="last_timestamp" value="0" readonly />
<input type="hidden" name="limit_message" value="{$LIMIT_MESSAGE}" readonly />
<input type="hidden" name="image_extension" value="{$IMAGE_EXTENSION}" readonly />
<input type="hidden" name="file_extension" value="{$FILE_EXTENSION}" readonly />

<script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.js"></script>
<script src="modules/EC_Zalo/js/chatzalo.js?v=3.6"></script>