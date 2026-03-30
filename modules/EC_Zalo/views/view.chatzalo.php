<?php
require_once("include/Sugar_Smarty.php");
require_once("custom/include/helpers/api/APIZaloOA.php");

class Viewchatzalo extends SugarView {
    /**
     * @var EC_Zalo
     */
    public $bean;
    private $zaloOA;
    private $entrypoint;
    private $websocket_url;
    public $default_avatar;
    public $limit_chat_box;
    public $limit_message;

    public function __construct() {
        parent::__construct();
        $this->zaloOA = new APIZaloOA();
        $this->entrypoint = 'index.php?entryPoint=entryPointGeneral';
        $this->websocket_url = $_SERVER['SERVER_NAME'] != 'localhost' ? 'wss://'.$_SERVER['SERVER_NAME'].'/chatz/' : 'ws://localhost:8080';
        $this->default_avatar = EC_Zalo_Helper::IMAGE_PATH . '/avatar-default.jpg';
        $this->limit_chat_box = 15;
        $this->limit_message = 10;
    }
    
    public function display() {
        if(empty($this->zaloOA->get_oa_id())) {
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

        $info_oa = EC_Zalo_Helper::get_info_oa($this->zaloOA->get_oa_id());
        if(isset($info_oa['error']) && ($info_oa['error'] == -216 || $info_oa['error'] == -14014)) {
            echo $this->populate_content_auth();
            exit();
        }

        $sub_quota = 0;
        if(!empty($info_oa)) {
            foreach($info_oa['quota'] as $q) {
                if($q['quota_type'] == 'sub_quota') $sub_quota = (int)$q['remain'];
            }
        }
        else {
            echo "<h3>Không có thông tin Zalo OA</h3>";
            exit();
        }

        $smarty->assign('OA_ID', $this->zaloOA->get_oa_id());
        $smarty->assign('OA_AVATAR', $info_oa['avatar'] ?? '');
        $smarty->assign('OA_NAME', $info_oa['name'] ?? '');
        $smarty->assign('OA_SUB_QUOTA', $sub_quota);
        $smarty->assign('DEFAULT_AVATAR', $this->default_avatar);
        $smarty->assign('IMAGE_FILE', str_replace('"', "'", json_encode(EC_Zalo_Messages_Helper::IMAGE_FILE)));
        $smarty->assign('ENTRYPOINT', $this->entrypoint);
        $smarty->assign('WEBSOCKET_URL', $this->websocket_url);
        $smarty->assign('ADMIN_ID', $current_user->id);
        $smarty->assign('ADMIN_NAME', end($fullname));
        $smarty->assign('LIMIT_MESSAGE', $this->limit_message);
        $smarty->assign('LIMIT_CHAT_BOX', $this->limit_chat_box);

        // Get icons
        $list_icons = $this->get_icons(0, '{{color}}');
        $icon_template = '';
        foreach($list_icons as $k => $v) {
            $icon_template .= '<div id="template_icon_'.$k.'" style="display:none">'.$v.'</div>';
        }
        $smarty->assign('ICON_TEMPLATE', $icon_template);

        // // Get list user
        // if($current_user->id != '1') {
        //     $html_list_user = $this->get_list_user();
        //     $offset_list_user = $this->get_number_at_end_string($html_list_user);
        //     $smarty->assign('LIST_USER', substr($html_list_user, 0, -strlen($offset_list_user)));
        //     $smarty->assign('OFFSET_LIST_USER', (int)$offset_list_user);
        // }

        // Get list tag
        $list_tag = $this->get_list_tag(['html_dropdown', 'html_checkbox']);
        $smarty->assign('LI_TAGS', $list_tag['html_dropdown']);
        $smarty->assign('CHECKBOX_TAGS', $list_tag['html_checkbox']);

        // Get list city
        $smarty->assign('OPTION_CITIES', $this->get_list_cities());

        // Get list file extension
        $smarty->assign('IMAGE_EXTENSION', implode(',', $this->zaloOA->get_file_extension('image')));
        $smarty->assign('FILE_EXTENSION', implode(',', $this->zaloOA->get_file_extension('file')));
    }

    public function populate_content_auth() {
        return '<center class="wrap-auth">
            <h6>OA cần xác thực và ủy quyền</h6>
            <a href="'.$this->zaloOA->get_link_integrate().'" class="btn btn-primary">Xác thực</a>
        </center>';
    }

    /**
     * Get list user tag
     * 
     * @param array $return_type [json, array, html_dropdown, html_checkbox]
     * @return array
     */
    public function get_list_tag($return_type = []) {
        $json = $this->zaloOA->get_list_tag();
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
            'bcard'     => '<svg width="'.$width.'" height="'.$height.'" ersion="1.1" id="Layer_1" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 54 64" enable-background="new 0 0 54 64" xml:space="preserve" fill="'.$color.'"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <title>Contact-book-2</title> <desc>Created with Sketch.</desc> <g id="Page-1" sketch:type="MSPage"> <g id="Contact-book-2" transform="translate(1.000000, 1.000000)" sketch:type="MSLayerGroup"> <path id="Shape_1_" sketch:type="MSShapeGroup" fill="none" stroke="#6B6C6E" stroke-width="2" d="M47,7h3c1.1,0,2,0.9,2,2v8 c0,1.1-0.9,2-2,2h-3"></path> <path id="Shape_2_" sketch:type="MSShapeGroup" fill="none" stroke="#6B6C6E" stroke-width="2" d="M47,24h3c1.1,0,2,0.9,2,2v8 c0,1.1-0.9,2-2,2h-3"></path> <path id="Shape_3_" sketch:type="MSShapeGroup" fill="none" stroke="#6B6C6E" stroke-width="2" d="M47,41h3c1.1,0,2,0.9,2,2v8 c0,1.1-0.9,2-2,2h-3"></path> <path id="Shape" sketch:type="MSShapeGroup" fill="none" stroke="#6B6C6E" stroke-width="2" d="M0,2c0-1.1,0.9-2,2-2h44 c1.1,0,2,0.9,2,2v58c0,1.1-0.9,2-2,2H2c-1.1,0-2-0.9-2-2V2L0,2z"></path> <path id="Shape_4_" sketch:type="MSShapeGroup" fill="none" stroke="#6B6C6E" stroke-width="2" d="M6,3v56"></path> <path id="Shape_5_" sketch:type="MSShapeGroup" fill="none" stroke="#6B6C6E" stroke-width="2" d="M20.8,38 c1.3-0.6,3.1-1.7,3.1-2.7c0-0.5-0.2-0.9-0.4-1c-2.5-1.4-2.9-5.8-3.1-5.8c-0.8,0-1.4-2.1-1.4-3.4c0-1.1,0.3-1.1,0.9-1.4v-0.2 c0-3.6,2.3-6.5,6-6.5s6.3,3,6.3,6.6v0.2c0.6,0.3,0.8,0.5,0.8,1.6c0,1.4-0.5,3.3-1.3,3.3c-0.2,0-0.8,4.3-3.2,5.7 c-0.2,0.1-0.4,0.3-0.4,0.8c0,1.2,1.9,2.2,3.2,2.8c1.6,0.8,8.7,1.5,8.7,9H11.9C11.9,39.5,18.8,39,20.8,38L20.8,38z"></path> </g> </g> </g></svg>',
            'tag'       => '<svg width="'.$width.'" height="'.$height.'" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="'.$color.'" stroke-width="1.5"><path d="M3 17.4V6.6C3 6.26863 3.26863 6 3.6 6H16.6789C16.8795 6 17.0668 6.10026 17.1781 6.26718L20.7781 11.6672C20.9125 11.8687 20.9125 12.1313 20.7781 12.3328L17.1781 17.7328C17.0668 17.8997 16.8795 18 16.6789 18H3.6C3.26863 18 3 17.7314 3 17.4Z" fill="'.$color.'" stroke="'.$color.'" stroke-width="1.5"></path></svg>',
            'call'          =>  '<svg width="'.$width.'" height="'.$height.'" fill="'.$color.'" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 348.077 348.077" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <g> <path d="M340.273,275.083l-53.755-53.761c-10.707-10.664-28.438-10.34-39.518,0.744l-27.082,27.076 c-1.711-0.943-3.482-1.928-5.344-2.973c-17.102-9.476-40.509-22.464-65.14-47.113c-24.704-24.701-37.704-48.144-47.209-65.257 c-1.003-1.813-1.964-3.561-2.913-5.221l18.176-18.149l8.936-8.947c11.097-11.1,11.403-28.826,0.721-39.521L73.39,8.194 C62.708-2.486,44.969-2.162,33.872,8.938l-15.15,15.237l0.414,0.411c-5.08,6.482-9.325,13.958-12.484,22.02 C3.74,54.28,1.927,61.603,1.098,68.941C-6,127.785,20.89,181.564,93.866,254.541c100.875,100.868,182.167,93.248,185.674,92.876 c7.638-0.913,14.958-2.738,22.397-5.627c7.992-3.122,15.463-7.361,21.941-12.43l0.331,0.294l15.348-15.029 C350.631,303.527,350.95,285.795,340.273,275.083z"></path> </g> </g> </g> </g></svg>',
            'inbound_call'  => '<svg width="'.$width.'" height="'.$height.'" fill="'.$color.'" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 351.912 351.912" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <g> <path d="M342.237,284.604l-54.638-54.668c-4.257-4.245-10.189-6.581-16.693-6.581c-6.845,0-13.462,2.672-18.134,7.338 l-29.525,29.537l-7.98-4.425c-17.564-9.733-41.599-23.083-66.999-48.507c-25.493-25.473-38.848-49.576-48.621-67.194 l-4.366-7.758l29.589-29.576c9.791-9.821,10.121-25.439,0.738-34.84L70.961,13.29c-4.254-4.243-10.175-6.591-16.675-6.591 c-6.854,0-13.459,2.682-18.131,7.359L22.737,27.563l-1.261,2.06C16.489,36.03,12.4,43.229,9.332,51.075 C6.497,58.553,4.72,65.66,3.915,72.788c-7.046,58.556,19.96,112.317,93.212,185.563c86.835,86.812,159.386,93.561,179.556,93.561 c3.452,0,5.536-0.186,6.131-0.246c7.451-0.906,14.586-2.69,21.779-5.488c7.77-3.026,14.945-7.086,21.329-12.088l3.057-2.402 l12.556-12.321C351.31,309.596,351.623,293.996,342.237,284.604z"></path> </g> <g> <path d="M198.194,157.593c9.68-0.015,86.907-10.854,89.802-11.286c0.919,0,5.675-0.108,8.095-2.543 c1.52-1.519,3.272-4.875-2.071-10.22l-19.155-19.149l68.466-68.449c1.67-1.672,3.646-5.996-0.805-10.433L310.406,3.396 c-2.228-2.225-4.455-3.369-6.653-3.396c-1.675-0.018-3.278,0.64-4.521,1.877c-0.294,0.315-0.522,0.591-0.685,0.805 l-69.151,69.145l-20.891-20.882c-4.546-4.542-7.548-2.534-8.617-1.444c-2.372,2.366-2.42,7.293-2.372,8.403l-8.448,89.505 c-0.036,0.456-0.294,4.563,2.426,7.491C192.61,156.128,194.687,157.593,198.194,157.593z"></path> </g> </g> </g> </g></svg>',
            'outbound_call' => '<svg width="'.$width.'" height="'.$height.'" fill="'.$color.'" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 355.694 355.694" xml:space="preserve" stroke="#8c8c8c"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <g> <path d="M345.923,287.653l-55.22-55.25c-4.3-4.293-10.299-6.652-16.874-6.652c-6.924,0-13.606,2.695-18.326,7.416l-29.844,29.855 l-8.077-4.479c-17.75-9.849-42.045-23.329-67.716-49.035c-25.772-25.737-39.268-50.099-49.137-67.912l-4.414-7.839l29.901-29.897 c9.893-9.917,10.232-25.707,0.75-35.212L71.732,13.421c-4.305-4.29-10.289-6.659-16.855-6.659 c-6.929,0-13.607,2.708-18.324,7.443L22.982,27.848l-1.279,2.083c-5.041,6.476-9.175,13.757-12.274,21.683 c-2.87,7.563-4.66,14.745-5.476,21.945c-7.116,59.195,20.173,113.527,94.218,187.559c87.77,87.748,161.109,94.576,181.495,94.576 c3.482,0,5.603-0.187,6.197-0.252c7.529-0.907,14.735-2.721,22.014-5.543c7.848-3.062,15.102-7.17,21.557-12.214l3.087-2.426 l12.688-12.454C355.086,312.916,355.41,297.142,345.923,287.653z"></path> </g> <g> <path d="M226.283,155.849c2.246,2.252,4.51,3.408,6.726,3.432c1.711,0.018,3.326-0.646,4.569-1.895 c0.312-0.318,0.528-0.594,0.696-0.816l69.896-69.89l21.119,21.109c4.594,4.594,7.619,2.564,8.707,1.462 c2.396-2.39,2.449-7.371,2.396-8.497l8.545-90.469c0.023-0.462,0.282-4.611-2.456-7.569C345.347,1.469,343.257,0,339.696,0 c-9.771,0.006-87.832,10.968-90.757,11.406c-0.931,0-5.74,0.111-8.196,2.57c-1.531,1.537-3.309,4.93,2.102,10.331l19.348,19.354 l-69.188,69.175c-1.682,1.693-3.675,6.059,0.811,10.553L226.283,155.849z"></path> </g> </g> </g> </g></svg>',
            'missed_call'   => '<svg height="'.$width.'" width="'.$height.'" fill="'.$color.'" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" enable-background="new 0 0 512 512" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M328,374.8c-8.2-16.9-18.8-29.2-37.1-21.7l-36.1,13.4c-28.9,13.4-43.3,0-57.8-20.2l-65-147.8c-8.1-16.9-3.9-32.8,14.4-40.3 l50.5-20.2c18.3-7.6,15.4-23.4,7.2-40.3L161,17.1c-8.2-16.9-25-21-43.3-13.5C81,18.7,50.7,42.4,31.1,77.5 c-24,42.9-12,102.6-7.2,127.7c4.8,25.1,21.6,69.1,43.3,114.2c21.7,45.2,40.8,80.7,57.8,100.8c17,20.1,57.8,75.1,108.3,87.4 c41.4,10,86.1,1.6,122.7-13.5c18.3-7.5,18.4-23.4,10.2-40.4L328,374.8z M489.4,137.7L450,98.3l-59.1,59.1l-59.1-59.1l-39.4,39.4 l59.1,59.1L292.4,256l39.4,39.4l59.1-59.1l59.1,59.1l39.4-39.4l-59.1-59.1L489.4,137.7z"></path> </g></svg>',
        ];

        if($key === 0) return $icons;
        return isset($icons[$key]) ? $icons[$key] : '';
    }

    /**
     * Get list cities option
     * 
     * @return string HTML
     */
    public function get_list_cities() {
        $options = '<option>Chọn Tỉnh/Thành phố</option>';
        $sql = "SELECT id, fullname FROM cities";
        $res = $this->bean->db->query($sql);
        while($row = $this->bean->db->fetchByAssoc($res)) {
            $options .= '<option value="'.$row['id'].'">'.$row['fullname'].'</>';
        }
        return $options;
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