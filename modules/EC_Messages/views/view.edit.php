<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.edit.php');

class EC_MessagesViewEdit extends ViewEdit {
    function __construct() { parent::__construct(); }

    public function display() {
        $this->css();
		$this->populate_custom_fields();
		parent::display();
        // $this->js();
	}

    public function css() {
        $css = '';
        $css .= '<link rel="stylesheet" href="modules/'.$this->bean->module_dir.'/css/edit.css">';
        echo $css;
    }

    public function js() {
        $js = '';
        $js .= '<script src="modules/'.$this->bean->module_dir.'/js/edit.js"></script>';
        echo $js;
    }

    public function populate_custom_fields() {
        global $app_list_strings;

        // Gửi từ
        $custom_send_from = '<select name="send_from" id="send_from" class="form-select">
            <option value="Travelpass">Travelpass</option>
        </select>';
        $this->ss->assign('CUSTOM_SEND_FROM', $custom_send_from);


        // Loại
        $custom_type = '<select name="type" id="type" class="form-select">
            <option value="sms_campaign_static">'.$app_list_strings['message_type_list']['sms_campaign_static'].'</option>
        </select>';
        $this->ss->assign('CUSTOM_TYPE', $custom_type);


        // Loại tin nhắn
        $custom_category = '<select name="category" id="category" class="form-select">
            '.get_select_options_with_id($app_list_strings['message_category_list'], $this->bean->category ? $this->bean->category : '').'
        </select>';
        $this->ss->assign('CUSTOM_CATEGORY', $custom_category);

        // Tin nhắn thường
        $custom_content = '<textarea id="content" name="content" rows="4" cols="80" maxlength="300">'.$this->bean->content.'</textarea>';
        $this->ss->assign('CUSTOM_CONTENT', $custom_content);

        // Mô tả
        $custom_description = '<textarea id="description" name="description" rows="4" cols="80" maxlength="300">'.$this->bean->description.'</textarea>';
        $this->ss->assign('CUSTOM_DESCRIPTION', $custom_description);


        // // Nội dung tin nhắn
        // $zalo_template_broadcast_data = [
        //     'post' => '',
        //     'gender' => '0',
        //     'ages' => '',
        //     'locations' => '',
        //     'cities' => '',
        //     'platform' => ''
        // ];
        // if($this->bean->type == 'send_zalo_broadcast' && !empty($this->bean->data)) $zalo_template_broadcast_data = json_decode(html_entity_decode($this->bean->data), true);

        // $arr_value_ages = empty($zalo_template_broadcast_data['ages']) ? array() : explode(',', $zalo_template_broadcast_data['ages']);
        // $option_ages = '';
        // foreach($app_list_strings['sms_logs_filters']['ages'] as $k => $a) {
        //     if(in_array($k, $arr_value_ages)) $option_ages .= '<option value="'.$k.'" selected>'.$a.'</option>';
        //     else $option_ages .= '<option value="'.$k.'">'.$a.'</option>';
        // }

        // $arr_value_locations = empty($zalo_template_broadcast_data['locations']) ? array() : explode(',', $zalo_template_broadcast_data['locations']);
        // $option_locations = '';
        // foreach($app_list_strings['sms_logs_filters']['locations'] as $k => $a) {
        //     if(in_array($k, $arr_value_locations)) $option_locations .= '<option value="'.$k.'" selected>'.$a.'</option>';
        //     else $option_locations .= '<option value="'.$k.'">'.$a.'</option>';
        // }

        // $arr_value_cities = empty($zalo_template_broadcast_data['cities']) ? array() : explode(',', $zalo_template_broadcast_data['cities']);
        // $option_cities = '';
        // foreach($app_list_strings['sms_logs_filters']['cities'] as $k => $a) {
        //     if(in_array($k, $arr_value_cities)) $option_cities .= '<option value="'.$k.'" selected>'.$a.'</option>';
        //     else $option_cities .= '<option value="'.$k.'">'.$a.'</option>';
        // }

        // $arr_value_platform = empty($zalo_template_broadcast_data['platform']) ? array() : explode(',', $zalo_template_broadcast_data['platform']);
        // $option_platform = '';
        // foreach($app_list_strings['sms_logs_filters']['platform'] as $k => $a) {
        //     if(in_array($k, $arr_value_platform)) $option_platform .= '<option value="'.$k.'" selected>'.$a.'</option>';
        //     else $option_platform .= '<option value="'.$k.'">'.$a.'</option>';
        // }

        // // Tin nhắn broadcast zalo
        // $custom_content = '<div class="card card-content-zalo-broadcast" style="display:none">
        //     <div class="card-body">
        //         <h6 class="title-template">Mẫu quảng cáo Zalo</h6>
        //         <ul class="list-group list-group-flush">
        //             <li class="row list-group-item">
        //                 <div class="label">Bài viết <span style="color:red">*</span></div>
        //                 <div class="field">
        //                     <select name="field-zalo-broadcast-post">
        //                         '.get_select_options_with_id($app_list_strings['list_post_zalo_oa'], $zalo_template_broadcast_data['post']).'
        //                     </select>
        //                 </div>
        //             </li>
        //             <li class="row list-group-item">
        //                 <div class="label">Giới tính</div>
        //                 <div class="field">
        //                     <select name="field-zalo-broadcast-gender">
        //                         '.get_select_options_with_id($app_list_strings['sms_logs_filters']['gender'], $zalo_template_broadcast_data['gender']).'
        //                     </select>
        //                 </div>
        //             </li>
        //             <li class="row list-group-item">
        //                 <div class="label">Độ tuổi</div>
        //                 <div class="field">
        //                     <select name="field-zalo-broadcast-ages[]" class="select" size="4" multiple="multiple">
        //                         '.$option_ages.'
        //                     </select>
        //                 </div>
        //             </li>
        //             <li class="row list-group-item">
        //                 <div class="label">Khu vực</div>
        //                 <div class="field">
        //                     <select name="field-zalo-broadcast-locations[]" class="select" size="3" multiple="multiple">
        //                         '.$option_locations.'
        //                     </select>
        //                 </div>
        //             </li>
        //             <li class="row list-group-item">
        //                 <div class="label">Thành phố</div>
        //                 <div class="field">
        //                     <select name="field-zalo-broadcast-cities[]" class="select" size="4" multiple="multiple">
        //                         '.$option_cities.'
        //                     </select>
        //                 </div>
        //             </li>
        //             <li class="row list-group-item">
        //                 <div class="label">Nền tảng</div>
        //                 <div class="field">
        //                     <select name="field-zalo-broadcast-platform[]" class="select" size="2" multiple="multiple">
        //                         '.$option_platform.'
        //                     </select>
        //                 </div>
        //             </li>
        //         </ul>
        //     </div>
        // </div>';

        // // Tin nhắn thường
        // $custom_content .= '<textarea id="content" name="content" rows="4" cols="80" maxlength="300">'.$this->bean->content.'</textarea>';
        // $this->ss->assign('CUSTOM_CONTENT', $custom_content);
    }
}