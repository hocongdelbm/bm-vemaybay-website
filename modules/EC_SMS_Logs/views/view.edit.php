<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.edit.php');

class EC_SMS_LogsViewEdit extends ViewEdit {
    function __construct() { parent::__construct(); }

    public function display() {
        // global $db;
        // $record = '5b35dd7a-3afa-f7e1-f48a-67700f803840';
        // $record_name = 'VJ241228C1';
        // $con_id = $con_phone = $con_zalo_id = '';
        // $sql_get_phone_and_zalo = "
        //     SELECT c.id, bk.phone, c.zalo_id
        //     FROM ec_flight_bookings bk
        //         LEFT JOIN contacts c ON c.phone_mobile = bk.phone AND c.deleted = 0
        //     WHERE bk.id = '$record' AND bk.deleted = 0
        // ";
        // $res_get_phone_and_zalo = $db->query($sql_get_phone_and_zalo);
        // while ($row = $db->fetchByAssoc($res_get_phone_and_zalo)) {
        //     $con_id = $row['id'] ?? '';
        //     $con_phone = $row['phone'] ?? '';
        //     $con_zalo_id = $row['zalo_id'] ?? '';
        // }

        // if(!empty($con_phone)) {
        //     // $point = calculatePointsFromBooking($record);
        //     // if($point > 0) {
        //     //     $sql_update_point = "UPDATE contacts SET points = points + $point WHERE id = '$con_id'";
        //     //     $db->query($sql_update_point);
        //     // }
        //     $point = 15;

        //     if(!empty($con_zalo_id)) {
		// 		$Zalo = new Zalo();
        //         // $sql = "SELECT points FROM contacts WHERE id = '$con_id'";
        //         // $total_point = $db->getOne($sql);

        //         $total_point = 100;


        //         if($total_point > 0) {
        //             $header = "CHÚC MỪNG BẠN ĐÃ TÍCH LŨY $point  ĐIỂM!";
        //             $text = "Điểm số càng cao, càng nhiều ưu đãi hấp dẫn. Cảm ơn bạn đã tin tưởng lựa chọn Tìm Chuyến Bay.";
        //             $text2 = "Chúc bạn có một chuyến đi an toàn, vui vẻ & như ý.";
        //             $table = [
        //                 [
        //                     "key" => "Mã booking",
        //                     "value" => "$record_name",
        //                 ],
        //                 [
        //                     "key" => "Số điện thoại",
        //                     "value" => "$con_phone",
        //                 ],
        //                 [
        //                     "key" => "Tổng tích lũy",
        //                     "value" => "$total_point điểm",
        //                 ],
        //             ];
        //             var_dump('here');
        //             $res = $Zalo->send_transaction($con_zalo_id, 'transaction_reward', $header, $text, $table, $text2);

        //             pr($res);
        //         }
        //     }
        // }
        // die();


        $this->css();
		$this->populate_custom_fields();
		parent::display();
        $this->js();
	}

    public function css() {
        $css = '';
        $css .= '<link rel="stylesheet" href="modules/'.$this->bean->module_dir.'/css/edit.css">';
        echo $css;
    }

    public function js() {
        $js = '<script>
            $(document).ready(function () {
                setTimeout(function() { 
                    $("select#type").val("'.$this->bean->type.'").trigger("change");
                }, 500);
            });
        </script>';
        echo $js;
    }

    public function populate_custom_fields() {
        global $app_list_strings;


        // Gửi từ
        $custom_send_from = '<select name="send_from" id="send_from" class="form-select">
                <option value="Travelpass">Travelpass</option>
                <option value="OA Travelpass">OA Travelpass</option>
            </select>
        ';
        $this->ss->assign('CUSTOM_SEND_FROM', $custom_send_from);


        // Loại
        $default_options_type = '
            <option value="send_sms_list_static">'.$app_list_strings['sms_logs_type_list']['send_sms_list_static'].'</option>
            <option value="send_sms_list_dynamic">'.$app_list_strings['sms_logs_type_list']['send_sms_list_dynamic'].'</option>
            <option value="send_zalo_broadcast">'.$app_list_strings['sms_logs_type_list']['send_zalo_broadcast'].'</option>
        ';
        $custom_type = '<select name="type" id="type" class="form-select">
                '. $default_options_type .'
            </select>
        ';
        $this->ss->assign('CUSTOM_TYPE', $custom_type);


        // Loại tin nhắn
        $custom_message_type = '<select name="message_type" id="message_type" class="form-select">
                '.get_select_options_with_id($app_list_strings['sms_logs_type_message_list'], $this->bean->message_type ? $this->bean->message_type : '').'
            </select>
        ';
        $this->ss->assign('CUSTOM_MESSAGE_TYPE', $custom_message_type);


        // Nội dung tin nhắn
        $zalo_template_broadcast_data = [
            'post' => '',
            'gender' => '0',
            'ages' => '',
            'locations' => '',
            'cities' => '',
            'platform' => ''
        ];
        if($this->bean->type == 'send_zalo_broadcast' && !empty($this->bean->data)) $zalo_template_broadcast_data = json_decode(html_entity_decode($this->bean->data), true);

        $arr_value_ages = empty($zalo_template_broadcast_data['ages']) ? array() : explode(',', $zalo_template_broadcast_data['ages']);
        $option_ages = '';
        foreach($app_list_strings['sms_logs_filters']['ages'] as $k => $a) {
            if(in_array($k, $arr_value_ages)) $option_ages .= '<option value="'.$k.'" selected>'.$a.'</option>';
            else $option_ages .= '<option value="'.$k.'">'.$a.'</option>';
        }

        $arr_value_locations = empty($zalo_template_broadcast_data['locations']) ? array() : explode(',', $zalo_template_broadcast_data['locations']);
        $option_locations = '';
        foreach($app_list_strings['sms_logs_filters']['locations'] as $k => $a) {
            if(in_array($k, $arr_value_locations)) $option_locations .= '<option value="'.$k.'" selected>'.$a.'</option>';
            else $option_locations .= '<option value="'.$k.'">'.$a.'</option>';
        }

        $arr_value_cities = empty($zalo_template_broadcast_data['cities']) ? array() : explode(',', $zalo_template_broadcast_data['cities']);
        $option_cities = '';
        foreach($app_list_strings['sms_logs_filters']['cities'] as $k => $a) {
            if(in_array($k, $arr_value_cities)) $option_cities .= '<option value="'.$k.'" selected>'.$a.'</option>';
            else $option_cities .= '<option value="'.$k.'">'.$a.'</option>';
        }

        $arr_value_platform = empty($zalo_template_broadcast_data['platform']) ? array() : explode(',', $zalo_template_broadcast_data['platform']);
        $option_platform = '';
        foreach($app_list_strings['sms_logs_filters']['platform'] as $k => $a) {
            if(in_array($k, $arr_value_platform)) $option_platform .= '<option value="'.$k.'" selected>'.$a.'</option>';
            else $option_platform .= '<option value="'.$k.'">'.$a.'</option>';
        }

        // Tin nhắn broadcast zalo
        $custom_content = '<div class="card card-content-zalo-broadcast" style="display:none">
            <div class="card-body">
                <h6 class="title-template">Mẫu quảng cáo Zalo</h6>
                <ul class="list-group list-group-flush">
                    <li class="row list-group-item">
                        <div class="label">Bài viết <span style="color:red">*</span></div>
                        <div class="field">
                            <select name="field-zalo-broadcast-post">
                                '.get_select_options_with_id($app_list_strings['list_post_zalo_oa'], $zalo_template_broadcast_data['post']).'
                            </select>
                        </div>
                    </li>
                    <li class="row list-group-item">
                        <div class="label">Giới tính</div>
                        <div class="field">
                            <select name="field-zalo-broadcast-gender">
                                '.get_select_options_with_id($app_list_strings['sms_logs_filters']['gender'], $zalo_template_broadcast_data['gender']).'
                            </select>
                        </div>
                    </li>
                    <li class="row list-group-item">
                        <div class="label">Độ tuổi</div>
                        <div class="field">
                            <select name="field-zalo-broadcast-ages[]" class="select" size="4" multiple="multiple">
                                '.$option_ages.'
                            </select>
                        </div>
                    </li>
                    <li class="row list-group-item">
                        <div class="label">Khu vực</div>
                        <div class="field">
                            <select name="field-zalo-broadcast-locations[]" class="select" size="3" multiple="multiple">
                                '.$option_locations.'
                            </select>
                        </div>
                    </li>
                    <li class="row list-group-item">
                        <div class="label">Thành phố</div>
                        <div class="field">
                            <select name="field-zalo-broadcast-cities[]" class="select" size="4" multiple="multiple">
                                '.$option_cities.'
                            </select>
                        </div>
                    </li>
                    <li class="row list-group-item">
                        <div class="label">Nền tảng</div>
                        <div class="field">
                            <select name="field-zalo-broadcast-platform[]" class="select" size="2" multiple="multiple">
                                '.$option_platform.'
                            </select>
                        </div>
                    </li>
                </ul>
            </div>
        </div>';

        // Tin nhắn thường
        $custom_content .= '<textarea id="content" name="content" rows="4" cols="80" maxlength="300">'.$this->bean->content.'</textarea>';
        $this->ss->assign('CUSTOM_CONTENT', $custom_content);
    }
}