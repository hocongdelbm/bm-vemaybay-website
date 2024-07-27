<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once("include/Sugar_Smarty.php");
require_once("modules/EC_Zalo/Zalo.php");

class Viewchatzalo extends SugarView {
    private $Zalo;
    private $entrypoint;
    private $websocket_url;
    public $default_avatar;
    public $default_banner_request_info;
    public $image_file;

    public function __construct() {
        parent::__construct();
        $this->Zalo = new Zalo();
        $this->entrypoint = 'index.php?entryPoint=entrypointZaloOA';
        $this->websocket_url = $_SERVER['SERVER_NAME'] != 'localhost' ? 'wss://'.$_SERVER['SERVER_NAME'].'/chatz/' : 'ws://localhost:8080';
        $this->default_avatar = 'modules/EC_Zalo/images/private/avatar_default.jpg';
        $this->default_banner_request_info = 'modules/EC_Zalo/images/private/request_info_banner.png';
        $this->image_file = [
            'excel' => 'modules/EC_Zalo/images/private/files/file_excel.jpg',
            'word' => 'modules/EC_Zalo/images/private/files/file_word.jpg',
            'powerpoint' => 'modules/EC_Zalo/images/private/files/file_powerpoint.jpg',
            'pdf' => 'modules/EC_Zalo/images/private/files/file_pdf.jpg',
            'txt' => 'modules/EC_Zalo/images/private/files/file_txt.jpg',
            'html' => 'modules/EC_Zalo/images/private/files/file_html.jpg',
            'xml' => 'modules/EC_Zalo/images/private/files/file_xml.jpg',
            'zip' => 'modules/EC_Zalo/images/private/files/file_zip.jpg',
            'rar' => 'modules/EC_Zalo/images/private/files/file_rar.jpg',
            'default' => 'modules/EC_Zalo/images/private/files/file_default.jpg'
        ];
    }
    
    public function display() {
        if(empty($this->Zalo->get_oa_id())) {
            echo "<h3>Không có thông tin Zalo OA</h3>";
            exit();
        }

        $smarty = new Sugar_Smarty();
        $this->populate_content($smarty);
        $smarty->display('modules/'.$this->bean->module_dir.'/tpls/chatzalo.tpl');
    }

    public function populate_content($smarty) {
        global $current_user;
        $fullname = explode(' ', $current_user->name);

        $json_info_oa = $this->Zalo->get_info_oa();
        $arr_info_oa = json_decode($json_info_oa, true);

        if(!isset($arr_info_oa['error']) || $arr_info_oa['error'] == -216) {
            echo $this->populate_content_auth();
            exit();
        }

        $smarty->assign('OA_ID', $this->Zalo->get_oa_id());
        $smarty->assign('OA_AVATAR', isset($arr_info_oa['data']['avatar']) ? $arr_info_oa['data']['avatar'] : '');
        $smarty->assign('OA_NAME', isset($arr_info_oa['data']['name']) ? $arr_info_oa['data']['name'] : '');
        $smarty->assign('DEFAULT_AVATAR', $this->default_avatar);
        $smarty->assign('DEFAULT_BANNER_REQUEST_INFO', $this->default_banner_request_info);
        $smarty->assign('IMAGE_FILE', str_replace('"', "'", json_encode($this->image_file)));
        $smarty->assign('ENTRYPOINT', $this->entrypoint);
        $smarty->assign('WEBSOCKET_URL', $this->websocket_url);
        $smarty->assign('ADMIN_ID', $current_user->id);
        $smarty->assign('ADMIN_NAME', end($fullname));

        $smarty->assign('version', date('Y-m-dH:i:s'));

        // Get icons
        $list_icons = $this->get_icons(0, '{{color}}');
        $icon_template = '';
        foreach($list_icons as $k => $v) {
            $icon_template .= '<div id="template_icon_'.$k.'" style="display:none">'.$v.'</div>';
        }
        $smarty->assign('ICON_TEMPLATE', $icon_template);

        // Get list user
        $html_list_user = $this->get_list_user();
        $offset_list_user = $this->get_number_at_end_string($html_list_user);
        $smarty->assign('LIST_USER', substr($html_list_user, 0, -strlen($offset_list_user)));
        $smarty->assign('OFFSET_LIST_USER', (int)$offset_list_user);

        // Get list tag
        $list_tag = $this->get_list_tag(['html_dropdown', 'html_checkbox']);
        $smarty->assign('LI_TAGS', $list_tag['html_dropdown']);
        $smarty->assign('CHECKBOX_TAGS', $list_tag['html_checkbox']);
    }

    public function populate_content_auth() {
        return '
            <center class="wrap-auth">
                <h6>OA cần xác thực và ủy quyền</h6>
                <a href="'.$this->Zalo->get_link_integrate().'" class="btn btn-primary">Xác thực</a>
            </center>
        ';
    }

    /**
     * Get list user chat
     * 
     * @param int $offset
     * @param array $current_list_user
     * @return string
     */
    public function get_list_user($offset = 0, $current_list_user = []) {
        $html = '';
        $list_zalo_id = ['interaction' => [], 'no_interaction' => []];

        while(count($list_zalo_id['interaction']) < 16) {
            $json = $this->Zalo->get_recent_messages($offset);
            $arr = json_decode($json, true);

            if(isset($arr['error']) && $arr['error'] == 0 && !empty($arr['data'])) {
                foreach($arr['data'] as $row) {
                    $zalo_id = $row['src'] == 1 ? $row['from_id'] : $row['to_id'];

                    if(in_array($zalo_id, $current_list_user)) continue;
                    if(in_array($zalo_id, $list_zalo_id['no_interaction']) || in_array($zalo_id, $list_zalo_id['interaction'])) continue;

                    // Lấy thông tin người dùng
                    $user_info = json_decode($this->Zalo->get_user($zalo_id), true);
                    if(isset($user_info['error']) && $user_info['error'] != 0) {
                        $list_zalo_id['no_interaction'][] = $zalo_id;
                        continue;
                    }
                    else $list_zalo_id['interaction'][] = $zalo_id;

                    $user_info['data']['chat_link'] = $this->Zalo->get_chat_link($zalo_id);
                    $html .= $this->create_li_chat($row, $user_info['data']);
                }
            }
            else break;

            $offset += 10;
        }

        return $html.$offset;
    }

    public function create_li_chat($message_data, $user_data) {
        if(empty($message_data)) return '';

        /**********  1. Handle message  **********/
        $message_src  = $message_data['src'];
        $message_prefix = $message_src === 0 ? '<span style="margin-right:4px">OA:</span>' : '';
        $message_type = $message_data['type'];
        $message_text = isset($message_data['message']) ? $message_data['message'] : '';
        $message_time = $this->format_display_time($message_data['time']);

        // Nội dung tin nhắn hiển thị
        $message_content = '';
        if($message_type == 'text') {
            $message_content = '
                <div class="lastest_message">
                    '.$message_prefix.$message_text.'
                </div>
            ';
        }
        elseif($message_type == 'photo' || $message_type == 'image') {
            $message_content = '
                <div class="lastest_message">
                    '.$message_prefix.'
                    <div class="icon icon_'.$message_type.'">
                        '.$this->get_icons('image', '#8D8D8F', 20, 21).'
                    </div>
                    Hình ảnh
                </div>
            ';
        }
        elseif($message_type == 'gif') {
            $message_content = '
                <div class="lastest_message">
                    '.$message_prefix.'
                    <div class="icon icon_'.$message_type.'">
                        '.$this->get_icons('gif', '#8D8D8F', 20, 17).'
                    </div>
                    GIF
                </div>
            ';
        }
        elseif($message_type == 'sticker') {
            $message_content = '
                <div class="lastest_message">
                    '.$message_prefix.'
                    <div class="icon icon_'.$message_type.'">
                        '.$this->get_icons('sticker', '#8D8D8F', 20, 21).'
                    </div>
                    Sticker
                </div>
            ';
        }
        elseif($message_type == 'voice' || $message_type == 'audio') {
            $message_content = '
                <div class="lastest_message">
                    '.$message_prefix.'
                    <div class="icon icon_'.$message_type.'">
                        '.$this->get_icons('voice', '#8D8D8F', 20, 21).'
                    </div>
                    Tin nhắn thoại
                </div>
            ';
        }
        else if($message_type == 'video') {
            $message_content = '
                <div class="lastest_message">
                    '.$message_prefix.'
                    <div class="icon icon_'.$message_type.'">
                        '.$this->get_icons('video', '#8D8D8F').'
                    </div>
                    Video
                </div>
            ';
        }
        else if($message_type == 'file') {
            $message_content = '
                <div class="lastest_message">
                    '.$message_prefix.'
                    <div class="icon icon_'.$message_type.'">
                        '.$this->get_icons('file', '#8D8D8F', 20, 18).'
                    </div>
                    Tệp đính kèm
                </div>
            ';
        }
        elseif($message_type == 'link' || $message_type == 'links') {
            $message_content = '
                <div class="lastest_message">
                    '.$message_prefix.'
                    [Tin liên kết]
                </div>
            ';
        }
        elseif($message_type == 'location') {
            $message_content = '
                <div class="lastest_message">
                    '.$message_prefix.'
                    <div class="icon icon_'.$message_type.'">
                        '.$this->get_icons('location', '#8D8D8F', 20, 21).'
                    </div>
                    Vị trí
                </div>
            ';
        }
        else {
            $message_content = '<div class="lastest_message">'.$message_prefix.'Bạn có một tin nhắn mới</div>';
        }


        /**********  2. Handle user info  **********/
        // ID
        $zalo_id = isset($user_data['user_id']) ? $user_data['user_id'] : '';
        if(empty($zalo_id)) $zalo_id = $message_src === 1 ? $message_data['from_id'] : $message_data['to_id'];

        // Name
        $name = isset($user_data['user_alias']) ? $user_data['user_alias'] : '';
        if(empty($name)) $name = $user_data['display_name'] ? $user_data['display_name'] : '';
        if(empty($name)) $message_src === 1 ? $message_data['from_display_name'] : $message_data['to_display_name'];
        if(strlen($name) > 24) $name = mb_substr($name, 0, 24) . '...'; // Format name

        // Avatar
        $avatar = isset($user_data['avatar']) ? $user_data['avatar'] : '';
        if(empty($avatar)) $avatar = $message_src === 1 ? $message_data['from_avatar'] : $message_data['to_avatar'];
        
        // Tags
        $tags = isset($user_data['tags_and_notes_info']['tag_names']) ? $user_data['tags_and_notes_info']['tag_names'] : [];
        $html_tags = count($tags) > 0 ? '<div class="tag-content mt-1">' : '<div class="tag-content">';
        foreach($tags as $t) $html_tags .= '<div class="tag" data="'.$t.'">'.$t.'</div>';
        $html_tags .= '</div>';

        return '
            <li class="item_mess mess_links" id="li'.$zalo_id.'">
                <div class="mess_avt">
                    <div class="imgDrop">
                        <img class="avatar-user-list" src="'.$avatar.'" alt="Avatar user" />
                    </div>
                </div>
                <div class="__content">
                    <div class="info_content">
                        <div class="mess_content">
                            <div class="mess_name truncate">'.$name.'</div>
                        </div>
                        <div class="mess_more has_btn_more">
                            <div class="mess_time">'.$message_time.'</div>
                            <div class="mess_number"></div>
                        </div>
                    </div>
                    <div class="box-parent box-lastest_message">
                        '.$message_content.'
                    </div>
                    '.$html_tags.'
                </div>
                <div id="liuserinfo'.$zalo_id.'" style="display:none">'.json_encode($user_data).'</div>
            </li>
        ';
    }

    /**
     * Get list user tag
     * 
     * @param array $return_type [json, array, html_dropdown, html_checkbox]
     * @return array
     */
    public function get_list_tag($return_type = []) {
        $json = $this->Zalo->get_list_tag();
        $arr = json_decode($json, true);

        if($arr['error'] !== 0) return [];

        $result = [];
        if(in_array('json', $return_type)) $result['json'] = json_encode($arr['data']);
        if(in_array('array', $return_type)) $result['array'] = $arr['data'];
        if(in_array('html_dropdown', $return_type)) {
            $tags = $arr['data'];
            array_unshift($tags , 'L7D');
            array_unshift($tags , 'default');

            $html = '';
            foreach($tags as $t) {
                $text = $t;
                if($t == 'default') $text = 'Mặc định';
                elseif($t == 'L7D') $text = 'Người dùng sắp hết tương tác';

                $html .= '<li><a class="dropdown-item dropdown-item-type-list" data="'.$t.'" href="#">'.$text.'</a></li>';
            }
            $result['html_dropdown'] = $html;
        }
        if(in_array('html_checkbox', $return_type)) {
            $tags = $arr['data'];

            $html = '';
            foreach($tags as $t) {
                $html .= '
                    <li>
                        <div class="item_tag">
                            <i class="icon icon_tag">'.$this->get_icons('tag', '#8D8D8F', 18, 18).'</i>
                            <span class="tag-name">'.$t.'</span>
                            <input type="checkbox" name="checkbox_tag" class="checkbox_tag" value="'.$t.'"/>
                        </div>
                    </li>
                ';
            }
            $result['html_checkbox'] = $html;
        }

        return $result;
    }

    public function get_number_at_end_string($str) {
        $matches = [];
        if (!empty($str) && preg_match('#(\d+)$#', $str, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    public function get_icons($key, $color = '#000', $width = 20, $height = 20) {
        $icons = [
            'cancel'    => '<svg width="'.$width.'" height="'.$height.'" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="'.$color.'" stroke-width="1.5"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 1.25C6.06294 1.25 1.25 6.06294 1.25 12C1.25 17.9371 6.06294 22.75 12 22.75C17.9371 22.75 22.75 17.9371 22.75 12C22.75 6.06294 17.9371 1.25 12 1.25ZM9.70164 8.64124C9.40875 8.34835 8.93388 8.34835 8.64098 8.64124C8.34809 8.93414 8.34809 9.40901 8.64098 9.7019L10.9391 12L8.64098 14.2981C8.34809 14.591 8.34809 15.0659 8.64098 15.3588C8.93388 15.6517 9.40875 15.6517 9.70164 15.3588L11.9997 13.0607L14.2978 15.3588C14.5907 15.6517 15.0656 15.6517 15.3585 15.3588C15.6514 15.0659 15.6514 14.591 15.3585 14.2981L13.0604 12L15.3585 9.7019C15.6514 9.40901 15.6514 8.93414 15.3585 8.64124C15.0656 8.34835 14.5907 8.34835 14.2978 8.64124L11.9997 10.9393L9.70164 8.64124Z" fill="'.$color.'"></path></svg>',
            'gif'       => '<svg width="'.$width.'" height="'.$height.'" viewBox="0 0 20 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M15.209 0.166504H4.79232C2.60619 0.166504 0.833984 1.93871 0.833984 4.12484V12.8748C0.833984 15.061 2.60619 16.8332 4.79232 16.8332H15.209C17.3951 16.8332 19.1673 15.061 19.1673 12.8748V4.12484C19.1673 1.93871 17.3951 0.166504 15.209 0.166504ZM7.28995 10.9809C7.69715 10.9809 8.05857 10.8984 8.37423 10.7335C8.68989 10.5686 8.93768 10.3334 9.1176 10.028C9.29753 9.72261 9.38749 9.35684 9.38749 8.93071V8.31991H7.35861V9.09169L8.39831 9.09211L8.39502 9.18165C8.38451 9.29908 8.35823 9.40656 8.31618 9.5041L8.26888 9.59832C8.18129 9.74984 8.05502 9.86702 7.89009 9.94988C7.72516 10.0327 7.52669 10.0742 7.29468 10.0742C7.039 10.0742 6.81725 10.0122 6.62944 9.88833C6.44162 9.76444 6.29642 9.58412 6.19383 9.34737C6.09124 9.11063 6.03995 8.82338 6.03995 8.48563C6.03995 8.14788 6.09203 7.86182 6.1962 7.62744C6.30037 7.39307 6.44596 7.21433 6.63299 7.09122C6.82002 6.96811 7.03743 6.90656 7.28522 6.90656C7.41779 6.90656 7.53892 6.92313 7.64861 6.95628C7.75831 6.98942 7.85616 7.03716 7.94218 7.09951C8.02819 7.16185 8.10119 7.23761 8.16116 7.32678C8.22114 7.41595 8.2677 7.51814 8.30084 7.63336H9.34251C9.30936 7.39346 9.23439 7.17368 9.1176 6.97403C9.00081 6.77438 8.85048 6.60195 8.66661 6.45675C8.48274 6.31155 8.27243 6.1991 8.03569 6.11939C7.79895 6.03969 7.54326 5.99984 7.26864 5.99984C6.94825 5.99984 6.65075 6.05587 6.37612 6.16792C6.1015 6.27998 5.86082 6.44333 5.65406 6.65798C5.4473 6.87263 5.28671 7.13423 5.17229 7.44278C5.05786 7.75134 5.00065 8.10211 5.00065 8.4951C5.00065 9.00647 5.09653 9.44799 5.28829 9.81968C5.48005 10.1914 5.74797 10.4778 6.09203 10.6791C6.4361 10.8803 6.8354 10.9809 7.28995 10.9809ZM11.145 10.9146V6.06612H10.1199V10.9146H11.145ZM12.9782 8.91177V10.9146H11.9531V6.06612H15.1633V6.91129H12.9782V8.0666H14.9503V8.91177H12.9782ZM15.209 1.4165H4.79232C3.29655 1.4165 2.08398 2.62907 2.08398 4.12484V12.8748C2.08398 14.3706 3.29655 15.5832 4.79232 15.5832H15.209C16.7048 15.5832 17.9173 14.3706 17.9173 12.8748V4.12484C17.9173 2.62907 16.7048 1.4165 15.209 1.4165Z" fill="'.$color.'"></path></svg>',
            'image'     => '<svg width="'.$width.'" height="'.$height.'" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12.8 17.8001C13.904 17.8001 14.8 16.9041 14.8 15.8001V14.7121C14.256 14.4801 13.824 13.8801 13.344 13.2081C12.696 12.3201 11.96 11.3121 11.024 11.3121C10.184 11.3121 9.52 12.1121 8.824 12.9601C8.072 13.8801 7.296 14.8241 6.152 14.8241C4.952 14.8241 4.32 14.3281 3.864 13.9681C3.504 13.6801 3.312 13.5361 2.968 13.5361C2.36 13.5361 1.768 13.9921 1.2 14.8801V15.8001C1.2 16.9041 2.096 17.8001 3.2 17.8001H12.8ZM3.2 4.2C2.096 4.2 1.2 5.096 1.2 6.2V13.032C1.744 12.568 2.336 12.336 2.968 12.336C3.73126 12.336 4.18494 12.692 4.58787 13.0082C4.59459 13.0135 4.6013 13.0187 4.608 13.024L4.61571 13.03C5.02045 13.3475 5.37294 13.624 6.152 13.624C6.73513 13.624 7.29434 12.938 7.89333 12.2033L7.896 12.2L7.91061 12.1823C8.715 11.2106 9.62448 10.112 11.024 10.112C12.5675 10.112 13.5353 11.4391 14.3112 12.5029L14.312 12.504C14.44 12.68 14.624 12.928 14.8 13.144V6.2C14.8 5.096 13.904 4.2 12.8 4.2H3.2ZM3.2 3H12.8C14.568 3 16 4.432 16 6.2V15.8C16 17.568 14.568 19 12.8 19H3.2C1.432 19 0 17.568 0 15.8V6.2C0 4.432 1.432 3 3.2 3ZM4.96356 7.47055C4.96356 6.82095 4.43636 6.29375 3.78676 6.29375C3.13716 6.29375 2.60996 6.82095 2.60996 7.47055C2.60996 8.12015 3.13716 8.64655 3.78676 8.64655C4.43636 8.64655 4.96356 8.12015 4.96356 7.47055Z" fill="'.$color.'"></path></svg>',
            'location'  => '<svg width="'.$width.'" height="'.$height.'" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M10.0522 2.01044C6.71544 2.01044 4.0105 4.82769 4.0105 8.30295C4.0105 8.75719 4.0575 9.22228 4.14868 9.69712C4.49488 11.5 5.45554 13.3856 6.83755 15.283C7.44308 16.1143 8.09054 16.8929 8.73821 17.5981L9.06619 17.9487L9.53906 18.4291L9.61281 18.5002C9.86193 18.7361 10.2424 18.7361 10.4915 18.5002L10.6724 18.3232L11.0381 17.9487L11.3661 17.5981C12.0138 16.8929 12.6613 16.1143 13.2668 15.283C14.6488 13.3856 15.6095 11.5 15.9556 9.69712C16.0468 9.22228 16.0938 8.75719 16.0938 8.30295C16.0938 4.82769 13.3889 2.01044 10.0522 2.01044ZM10.0522 3.37098C12.6674 3.37098 14.7875 5.5791 14.7875 8.30295C14.7875 8.66462 14.7495 9.04073 14.6747 9.43029C14.3742 10.9954 13.4993 12.7126 12.2261 14.4607C11.6529 15.2476 11.0374 15.9877 10.4221 16.6577L10.0522 17.0513L9.99227 16.9891C9.89329 16.8853 9.78971 16.7747 9.68221 16.6577C9.06695 15.9877 8.45147 15.2476 7.87827 14.4607C6.60499 12.7126 5.73017 10.9954 5.42962 9.43029C5.35482 9.04073 5.3168 8.66462 5.3168 8.30295C5.3168 5.5791 7.4369 3.37098 10.0522 3.37098ZM10.0522 5.07166C8.33871 5.07166 6.94969 6.51836 6.94969 8.30295C6.94969 10.0875 8.33871 11.5342 10.0522 11.5342C11.7656 11.5342 13.1546 10.0875 13.1546 8.30295C13.1546 6.51836 11.7656 5.07166 10.0522 5.07166ZM10.0522 6.43221C11.0442 6.43221 11.8483 7.26977 11.8483 8.30295C11.8483 9.33614 11.0442 10.1737 10.0522 10.1737C9.06017 10.1737 8.25599 9.33614 8.25599 8.30295C8.25599 7.26977 9.06017 6.43221 10.0522 6.43221Z" fill="'.$color.'"></path></svg>',
            'file'      => '<svg width="'.$width.'" height="'.$height.'" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="'.$color.'" stroke-width="1.5"><path d="M21.4383 11.6622L12.2483 20.8522C11.1225 21.9781 9.59552 22.6106 8.00334 22.6106C6.41115 22.6106 4.88418 21.9781 3.75834 20.8522C2.63249 19.7264 2 18.1994 2 16.6072C2 15.015 2.63249 13.4881 3.75834 12.3622L12.9483 3.17222C13.6989 2.42166 14.7169 2 15.7783 2C16.8398 2 17.8578 2.42166 18.6083 3.17222C19.3589 3.92279 19.7806 4.94077 19.7806 6.00222C19.7806 7.06368 19.3589 8.08166 18.6083 8.83222L9.40834 18.0222C9.03306 18.3975 8.52406 18.6083 7.99334 18.6083C7.46261 18.6083 6.95362 18.3975 6.57834 18.0222C6.20306 17.6469 5.99222 17.138 5.99222 16.6072C5.99222 16.0765 6.20306 15.5675 6.57834 15.1922L15.0683 6.71222" stroke="'.$color.'" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>',
            'quote'     => '<svg width="'.$width.'" height="'.$height.'" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="'.$color.'" stroke-width="1.5"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.21255 12.75C9.12943 13.5242 8.9054 14.1421 8.5147 14.6891C7.99181 15.4211 7.11571 16.1036 5.66459 16.8292C5.29411 17.0144 5.14394 17.4649 5.32918 17.8354C5.51442 18.2059 5.96493 18.3561 6.33541 18.1708C7.88429 17.3964 9.00819 16.5789 9.7353 15.5609C10.4761 14.5238 10.75 13.3571 10.75 12V7.5C10.75 6.53351 9.96649 5.75 9 5.75H5C4.03351 5.75 3.25 6.53351 3.25 7.5V11C3.25 11.9665 4.03352 12.75 5 12.75H9.21255Z" fill="'.$color.'"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M19.2125 12.75C19.1294 13.5242 18.9054 14.1421 18.5147 14.6891C17.9918 15.4211 17.1157 16.1036 15.6646 16.8292C15.2941 17.0144 15.1439 17.4649 15.3292 17.8354C15.5144 18.2059 15.9649 18.3561 16.3354 18.1708C17.8843 17.3964 19.0082 16.5789 19.7353 15.5609C20.4761 14.5238 20.75 13.3571 20.75 12V7.5C20.75 6.53352 19.9665 5.75 19 5.75H15C14.0335 5.75 13.25 6.53352 13.25 7.5V11C13.25 11.9665 14.0335 12.75 15 12.75H19.2125Z" fill="'.$color.'"></path></svg>',
            'video'     => '<svg width="'.$width.'" height="'.$height.'" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="'.$color.'" stroke-width="1.5"><path d="M14 12L10.5 14V10L14 12Z" fill="'.$color.'" stroke="'.$color.'" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M2 12.7075V11.2924C2 8.39705 2 6.94939 2.90549 6.01792C3.81099 5.08645 5.23656 5.04613 8.08769 4.96549C9.43873 4.92728 10.8188 4.8999 12 4.8999C13.1812 4.8999 14.5613 4.92728 15.9123 4.96549C18.7634 5.04613 20.189 5.08645 21.0945 6.01792C22 6.94939 22 8.39705 22 11.2924V12.7075C22 15.6028 22 17.0505 21.0945 17.9819C20.189 18.9134 18.7635 18.9537 15.9124 19.0344C14.5613 19.0726 13.1812 19.1 12 19.1C10.8188 19.1 9.43867 19.0726 8.0876 19.0344C5.23651 18.9537 3.81097 18.9134 2.90548 17.9819C2 17.0505 2 15.6028 2 12.7075Z" stroke="'.$color.'" stroke-width="1.5"></path></svg>',
            'voice'     => '<svg width="'.$width.'" height="'.$height.'" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M7.44462 5.68223C7.44462 4.25001 8.60524 3.09645 10.0304 3.09645C11.4626 3.09645 12.6162 4.25706 12.6162 5.68223V11.2832L12.6162 11.2861C12.6237 12.7166 11.4642 13.8782 10.0304 13.8782C8.59818 13.8782 7.44462 12.7176 7.44462 11.2924V5.68223ZM10.0304 2C8.00177 2 6.34817 3.64238 6.34817 5.68223V11.2924C6.34817 13.321 7.99055 14.9746 10.0304 14.9746C12.0682 14.9746 13.7226 13.3228 13.7126 11.2818V5.68223C13.7126 3.6536 12.0703 2 10.0304 2ZM5.09645 11.2284C5.09645 10.9257 4.851 10.6802 4.54822 10.6802C4.24545 10.6802 4 10.9257 4 11.2284C4 11.8775 4.10289 12.5029 4.29324 13.0891C4.29632 13.1393 4.30642 13.19 4.32416 13.2398C5.1008 15.42 7.09026 17.0273 9.48223 17.2359V18.9036H8.23048C7.92771 18.9036 7.68226 19.149 7.68226 19.4518C7.68226 19.7546 7.92771 20 8.23048 20H11.8396C12.1424 20 12.3878 19.7546 12.3878 19.4518C12.3878 19.149 12.1424 18.9036 11.8396 18.9036H10.5787V17.2342C13.6495 16.9567 16.0609 14.3708 16.0609 11.2284C16.0609 10.9257 15.8155 10.6802 15.5127 10.6802C15.2099 10.6802 14.9645 10.9257 14.9645 11.2284C14.9645 11.7636 14.8789 12.2792 14.7207 12.7621C14.699 12.7964 14.6807 12.8338 14.6666 12.8738C13.993 14.7879 12.1606 16.1606 10.0165 16.1624C7.30132 16.1549 5.09645 13.9454 5.09645 11.2284Z" fill="'.$color.'"></path></svg>',
            'sticker'   => '<svg width="'.$width.'" height="'.$height.'" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M13.6 17.8C14.264 17.8 14.8 17.264 14.8 16.6V7.44H12.856C12.1448 7.44 11.56 6.864 11.56 6.144V4.2H2.4C1.7448 4.2 1.2 4.736 1.2 5.4V16.6C1.2 17.264 1.7448 17.8 2.4 17.8H13.6ZM12.856 6.23997H14.432L12.76 4.56797V6.14397C12.76 6.19997 12.8088 6.23997 12.856 6.23997ZM14.4088 4.52L14.488 4.6L15.4142 5.52621C15.7893 5.90129 16 6.40999 16 6.94043V16.6C16 17.928 14.928 19 13.6 19H2.4C1.0808 19 0 17.928 0 16.6V5.4C0 4.072 1.0808 3 2.4 3H12.0599C12.5901 3 13.0987 3.21057 13.4737 3.58542L14.4088 4.52ZM10.1869 9.19749C10.1869 9.70069 10.5133 10.1079 10.9149 10.1079C11.3173 10.1079 11.6429 9.70069 11.6429 9.19749C11.6429 8.69509 11.3173 8.28789 10.9149 8.28789C10.5133 8.28789 10.1869 8.69509 10.1869 9.19749ZM5.09148 10.1079C4.68908 10.1079 4.36348 9.70069 4.36348 9.19749C4.36348 8.69509 4.68908 8.28789 5.09148 8.28789C5.49308 8.28789 5.81948 8.69509 5.81948 9.19749C5.81948 9.70069 5.49308 10.1079 5.09148 10.1079ZM5.09592 12.6719C5.09592 13.1679 6.85592 13.5359 7.99992 13.5359C9.14472 13.5359 10.9199 13.1679 10.9199 12.6719C10.9199 12.3426 10.1373 12.4294 9.27623 12.525C8.84046 12.5733 8.3846 12.6239 7.99992 12.6239C7.6149 12.6239 7.16009 12.5732 6.7261 12.5247C5.87059 12.4293 5.09592 12.3428 5.09592 12.6719ZM6.47562 11.7756C6.94567 11.8365 7.46639 11.904 7.99992 11.904C8.53576 11.904 9.0611 11.8347 9.53558 11.7722C10.7683 11.6098 11.6577 11.4927 11.4959 12.752C11.2807 14.504 9.92792 15.504 7.99992 15.504C6.07272 15.504 4.71272 14.48 4.50392 12.752C4.36565 11.5023 5.24778 11.6166 6.47562 11.7756Z" fill="'.$color.'"></path></svg>',
            'tag'       => '<svg width="'.$width.'" height="'.$height.'" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="'.$color.'" stroke-width="1.5"><path d="M3 17.4V6.6C3 6.26863 3.26863 6 3.6 6H16.6789C16.8795 6 17.0668 6.10026 17.1781 6.26718L20.7781 11.6672C20.9125 11.8687 20.9125 12.1313 20.7781 12.3328L17.1781 17.7328C17.0668 17.8997 16.8795 18 16.6789 18H3.6C3.26863 18 3 17.7314 3 17.4Z" fill="'.$color.'" stroke="'.$color.'" stroke-width="1.5"></path></svg>',
        ];

        if($key === 0) return $icons;
        return isset($icons[$key]) ? $icons[$key] : '';
    }

    protected function format_display_time($timestamp) {
        if(empty($timestamp)) return '';

        $week = [
            1 => "Thứ hai",
            2 => "Thứ ba",
            3 => "Thứ tư",
            4 => "Thứ năm",
            5 => "Thứ sáu",
            6 => "Thứ bảy",
            7 => "Chủ nhật"
        ];

        $timestamp = strlen($timestamp) > 11 ? (int)($timestamp / 1000) : $timestamp;
        $date = date('Y-m-d', $timestamp);
        $year = date('Y', $timestamp);

        if($date == date('Y-m-d')) return date('H:i', $timestamp);
        elseif($date == date('Y-m-d', strtotime("-1 days"))) return "Hôm qua";
        elseif($date == date('Y-m-d', strtotime("-2 days"))) return $week[date('N', $timestamp)];
        elseif($year != date('Y')) return date('d/m/Y', $timestamp);
        else return date('d/m', $timestamp);
    }
}