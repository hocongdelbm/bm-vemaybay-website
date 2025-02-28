<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.detail.php');

class EC_MessagesViewDetail extends ViewDetail {
    function display() {
        $this->css();
        $this->populate_custom_buttons();
        $this->populate_custom_fields();
        $this->js();
        parent::display();
    }

    public function css() {
        $css = '';
        $css .= '<link rel="stylesheet" href="modules/'.$this->bean->module_dir.'/css/detail.css">';
        echo $css;
    }

    public function js() {
        $js = '<script src="modules/'.$this->bean->module_dir.'/js/detail.js"></script>';
        if($this->bean->status == 'done' || $this->bean->status == 'scheduled' || $this->bean->type == 'send') {
            $js .= '<script>
                $(document).ready(function () {
                    $("#edit_button").hide();
                });
            </script>';
        }
        echo $js;
    }

    public function populate_custom_fields() {
        // Status
        if($this->bean->status == 'scheduled') $custom_status = '<b class="text-warning">Đã lên lịch</b>';
        else if($this->bean->status == 'done') $custom_status = '<b class="text-success">Đã gửi</b>';
        else if($this->bean->status == 'fail') $custom_status = '<b class="text-danger">Thất bại</b>';
        else $custom_status = 'Mới tạo';
        $this->ss->assign('CUSTOM_STATUS', $custom_status);

        // Parent
        if(!empty($this->bean->parent_id) && !empty($this->bean->parent_type)) {
            $obj_name = $this->bean->parent_type;
            $obj = new $obj_name();
            $obj->retrieve($this->bean->parent_id);

            $custom_parent = '<a href="/index.php?module='.$this->bean->parent_type.'&action=DetailView&record='.$this->bean->parent_id.'" target="_blank">'.$obj->name.'</a>';
            $this->ss->assign('CUSTOM_PARENT', $custom_parent);
        }

        // Send from
        if($this->bean->send_from === '2941581384627345950') $this->ss->assign('CUSTOM_SEND_FROM', '<b style="color:#0091ff">OA Tìm chuyến bay</b>');
        elseif($this->bean->send_from === 'Travelpass') $this->ss->assign('CUSTOM_SEND_FROM', '<b>Travelpass</b> <i>(Brandname)</i>');
        else $this->ss->assign('CUSTOM_SEND_FROM', $this->bean->send_from);

        // Send to
        if(!empty($this->bean->send_to)) $this->ss->assign('CUSTOM_SEND_TO', $this->bean->send_to);
        else if($this->bean->type == 'sms_campaign_static' || $this->bean->type == 'sms_campaign_dynamic') $this->ss->assign('CUSTOM_SEND_TO', 'Hàng loạt');
        else if($this->bean->type == 'send_zalo_broadcast') $this->ss->assign('CUSTOM_SEND_TO', $this->generate_filter_zalo_broadcast());

        // Content
        $custom_content = '';
        if($this->bean->type == 'send_zalo_broadcast') $custom_content = '<a href="'.$this->bean->content.'" target="_blank" style="text-decoration:underline">Bài viết OA liên kết</a>';
        else $custom_content = $this->bean->content;
        $this->ss->assign('CUSTOM_CONTENT', $custom_content);
    }

    public function populate_custom_buttons() {
        // Nút lên lịch SMS
        if(($this->bean->type == 'sms_campaign_static' || $this->bean->type == 'sms_campaign_dynamic') && $this->bean->status == 'new' && strtotime($this->bean->send_time) > strtotime(date('d-m-Y H:i:00',  strtotime('+7 hours')))) {
            $count = $this->bean->data ? count(json_decode(html_entity_decode($this->bean->data), true)) : 0;

            $button_schedule = '<button type="button" class="btn btn-warning" onclick="showDialog(\'dialog-confirm-schedule\')">Lên lịch</button>
                <dialog id="dialog-confirm-schedule" class="dialog-confirm-schedule">
                    <h5 class="title">Xác nhận lên lịch</h5>
                    <form method="dialog" name="form-confirm-schedule">
                        <p class="text-confirm">Lên lịch gửi tin nhắn cho <b>'.$count.'</b> số điện thoại vào lúc <i>'.date("H:i d/m/Y", strtotime($this->bean->send_time)).'</i></p>
                        <div class="d-flex gap-2 justify-content-end mt-3">
                            <input type="button" class="btn btn-primary" name="btn-confirm-confirm-schedule" id="btn-confirm-confirm-schedule" value="Xác nhận" title="Xác nhận" />
                            <input type="button" class="btn btn-secondary" name="btn-cancel-confirm-schedule" value="Hủy" title="Hủy" onclick="closeDialog(\'dialog-confirm-schedule\')" />
                            <input type="hidden" name="sms_type" value="'.$this->bean->type.'" />
                        </div>
                    </form>
                </dialog>
            ';
            $this->ss->assign('CUSTOM_BUTTON_SCHEDULE', $button_schedule);
        }
        // Nút lên lịch Zalo
        else if($this->bean->type == 'zalo_broadcast' && $this->bean->status == 'new') {
            $button_broadcast = '<button type="button" class="btn btn-warning" onclick="showDialog(\'dialog-confirm-schedule\')">Lên lịch</button>
                <dialog id="dialog-confirm-schedule" class="dialog-confirm-schedule">
                    <h5 class="title">Xác nhận gửi tin quảng cáo</h5>
                    <form method="dialog" name="form-confirm-broadcast">
                        <p class="text-confirm">Thao tác sẽ tiến hành gửi tin broadcast đến 500 người dùng Zalo theo điều kiện lọc đã chọn</p>
                        <div class="d-flex gap-2 justify-content-end mt-2">
                            <input type="button" class="btn btn-primary" name="btn-confirm-broadcast" id="btn-confirm-broadcast" value="Xác nhận" title="Xác nhận" />
                            <input type="button" class="btn btn-secondary" name="btn-cancel-broadcast" value="Hủy" title="Hủy" onclick="closeDialog(\'dialog-confirm-schedule\')" />
                        </div>
                    </form>
                </dialog>
            ';
            $this->ss->assign('CUSTOM_BUTTON_SCHEDULE', $button_broadcast);
        }

        // CUSTOM_VIEW_PROMOTION
        $button_voucher = '';
        if($this->bean->type == 'zalo_promotion'){
            $data_promotion = json_decode(html_entity_decode($this->bean->data), true);
            if(isset($data_promotion) && !empty($data_promotion)){
                $button_voucher = '<button type="button" id="btn-view-promotion" class="btn btn-primary">Xem khuyến mãi</button>
                    <dialog id="dialog-view-promotion" class="dialog-view-promotion">
                        <div id="template_promotion_view" class="template_promotion table-view table-promotion__zalo" >
                            <div class="template_promotion--banner">
                                <img src="'.$data_promotion['banner'].'" alt="voucher" />
                            </div>
                            <div class="template_promotion--header px-2">
                                <h3>'.$data_promotion['header'].'</h3>
                            </div>
                            <div class="template_promotion--text my-2 px-2">
                                '.$data_promotion['text'].'
                            </div>
                            <div class="template_promotion--tablecontent px-2">';
                                foreach($data_promotion['table'] as $key => $value){
                                    $button_voucher .= '
                                        <div class="tablecontent-row d-flex align-items-center justify-content-between mt-1">
                                            <div class="tablecontent-key w-33">'.$key.'</div>
                                            <div class="tablecontent-value flex-fill text-start fw-semibold">'.$value.'</div>
                                        </div>
                                    ';
                                }
                            $button_voucher .= '</div>
                            <div class="template_promotion--button">
                                <div id="tbl_button_option" class="tbl_button_option table-details__booking">
                                    <div id="list__button">
                                        <div id="button_wrap" class="mt-2 position-relative">';
                                            foreach($data_promotion['buttons'] as $button){
                                                $button_voucher .= '
                                                    <div class="sms_button--wrap">
                                                        <div class="sms_button--header">
                                                            <div class="image_icon" id="image_icon">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-dot" viewBox="0 0 16 16">
                                                                    <path d="M8 9.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
                                                                </svg>
                                                            </div>
                                                            <span>'.$button['title'].'</span>
                                                        </div>
                                                        <div class="sms_button--footer">
                                                            <div class="chevron_icon">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16">
                                                                    <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/>
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    </div>';
                                            }
                    $button_voucher .= '</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </dialog>
                ';
            } else {
                $button_voucher = '
                    <dialog id="dialog-view-promotion" class="dialog-view-promotion">
                        <div class="d-flex flex-column gap-2 align-items-center">
                            <svg fill="#b3b3b3" width="70" height="70" viewBox="0 0 846.66 846.66" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd" version="1.1" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" stroke="#b3b3b3"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <defs> <style type="text/css">  .fil0 {fill:black;fill-rule:nonzero}  </style> </defs> <g id="Layer_x0020_1"> <path class="fil0" d="M93.28 100.89l69.12 0 0 -71.08c0,-11.44 9.28,-20.71 20.72,-20.71l414.03 0c97.36,0 176.94,79.58 176.94,176.94l0 539.02c0,11.44 -9.27,20.71 -20.71,20.71l-69.12 0 0 71.08c0,11.44 -9.28,20.71 -20.72,20.71l-570.26 0c-11.44,0 -20.71,-9.27 -20.71,-20.71l0 -695.25c0,-11.44 9.27,-20.71 20.71,-20.71zm148.42 178.12c-27.24,0 -27.24,-41.42 0,-41.42l273.42 0c27.24,0 27.24,41.42 0,41.42l-273.42 0zm0 216.78c-27.24,0 -27.24,-41.42 0,-41.42l273.42 0c27.24,0 27.24,41.42 0,41.42l-273.42 0zm0 -108.39c-27.24,0 -27.24,-41.42 0,-41.42l273.42 0c27.24,0 27.24,41.42 0,41.42l-273.42 0zm-37.87 -286.51l303.48 0c97.36,0 176.95,79.58 176.95,176.94l0 426.52 48.41 0 0 -518.31c0,-74.49 -61.03,-135.52 -135.52,-135.52l-393.32 0 0 50.37zm11.51 478.47l326.15 0c11.43,0 20.71,9.28 20.71,20.71l0 105.46c0,11.44 -9.28,20.72 -20.71,20.72l-326.15 0c-11.44,0 -20.71,-9.28 -20.71,-20.72l0 -105.46c0,-11.43 9.27,-20.71 20.71,-20.71zm305.43 41.42l-284.72 0 0 64.04 284.72 0 0 -64.04zm-13.46 -478.47l-393.32 0 0 653.83 528.84 0 0 -518.31c0,-74.49 -61.02,-135.52 -135.52,-135.52z"></path> </g> </g></svg>
                            <p>Không có dữ liệu về tin nhắn khuyến mãi!</p>
                            </div>
                    </dialog>
                ';
            }


            $this->ss->assign('CUSTOM_VIEW_PROMOTION', $button_voucher);
        }
    }

    public function generate_filter_zalo_broadcast() {
        $filters = json_decode(html_entity_decode($this->bean->data), true);
        $html = '<center>DS người dùng Zalo</center>
            <div class="wrap-value">';

        $html .= '<p><b>Giới tính:</b> '.$GLOBALS['app_list_strings']['sms_logs_filters']['gender'][$filters['gender']].'</p>';

        if(!empty($filters['ages'])) {
            $html .= '<p><b>Độ tuổi:</b> ';
            foreach(explode(',', $filters['ages']) as $k => $a) {
                if($k == 0) $html .= $GLOBALS['app_list_strings']['sms_logs_filters']['ages'][$a];
                else $html .= ', ' . $GLOBALS['app_list_strings']['sms_logs_filters']['ages'][$a];
            }
            $html .= '</p>';
        }
        if(!empty($filters['locations'])) {
            $html .= '<p><b>Khu vực:</b> ';
            foreach(explode(',', $filters['locations']) as $k => $l) {
                if($k == 0) $html .= $GLOBALS['app_list_strings']['sms_logs_filters']['locations'][$l];
                else $html .= ', ' . $GLOBALS['app_list_strings']['sms_logs_filters']['locations'][$l];
            }
            $html .= '</p>';
        }
        if(!empty($filters['cities'])) {
            $html .= '<p><b>Thành phố:</b> ';
            foreach(explode(',', $filters['cities']) as $k => $c) {
                if($k == 0) $html .= $GLOBALS['app_list_strings']['sms_logs_filters']['cities'][$c];
                else $html .= ', ' . $GLOBALS['app_list_strings']['sms_logs_filters']['cities'][$c];
            }
            $html .= '</p>';
        }
        if(!empty($filters['platform'])) {
            $html .= '<p><b>Nền tảng:</b> ';
            foreach(explode(',', $filters['platform']) as $k => $p) {
                if($k == 0) $html .= $GLOBALS['app_list_strings']['sms_logs_filters']['platform'][$p];
                else $html .= ', ' . $GLOBALS['app_list_strings']['sms_logs_filters']['platform'][$p];
            }
            $html .= '</p>';
        }

        $html .= '</div>';

        return $html;
    }
}