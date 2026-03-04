<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class ContactsViewDetail extends ViewDetail
{
    /**
     * @see SugarView::display()
     *
     * We are overridding the display method to manipulate the portal information.
     * If portal is not enabled then don't show the portal fields.
     */
    public function display() {
        global $sugar_config;

        // Create and update contact
        createContactsForBooking($this->bean->phone_mobile);

        $this->populateCustomButtons();
        $this->populateLineZalo();
        $this->populateLinePoints();
        $this->populateLineCalls();

        $aop_portal_enabled = !empty($sugar_config['aop']['enable_portal']) && !empty($sugar_config['aop']['enable_aop']);

        $this->ss->assign("AOP_PORTAL_ENABLED", $aop_portal_enabled);

        require_once('modules/AOS_PDF_Templates/formLetter.php');
        formLetter::DVPopupHtml('Contacts');

        $admin = BeanFactory::newBean('Administration');
        $admin->retrieveSettings();
        if (isset($admin->settings['portal_on']) && $admin->settings['portal_on']) {
            $this->ss->assign("PORTAL_ENABLED", true);
        }

        $this->getStyles();
        parent::display();
    }

    private function getStyles() {
        echo "<link rel='stylesheet' href='modules/{$this->bean->module_dir}/css/view.detail.css?v=1.0.0'>";
    }

    private function populateCustomButtons() {
        // global $app_list_strings, $current_user, $timedate;
        // $date_format = $timedate->get_date_format();
        $contact_button      = '<a target="_blank" class="btn btn-info btn-view-detail" data-bs-toggle="modal" data-bs-target="#modalHistoryContactBookings" class="contact_name" data-id="' . $this->bean->id . '"><span>Booking</span></a>';
        $modal_history_bookings = '<div class="modal fade modal-history-bookings" style="--bs-modal-width: 1000px;" id="modalHistoryContactBookings" tabindex="-1" aria-labelledby="modalHistoryContactBookingsLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5 text-white" id="modalHistoryContactBookingsLabel">Lịch sử booking của liên hệ</h1>
                                                <button type="button" class="btn-close me-2" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div id="dialog-history-bookings"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>';
        $this->ss->assign('DETAIL_BOOKING', $contact_button . $modal_history_bookings);
    }

    private function populateLinePoints() {
        global $timedate;
        $html = $tbody = '';

        $line_points = BeanFactory::getBean('EC_Contact_Points_Log');
        $points_list = $line_points->get_full_list(
            "date_entered DESC",
            "(
                ec_contact_points_log.contact_id = '" . $this->bean->id . "'  
            )",
            false,
            0
        );

        if (count($points_list) > 0) {
            foreach ($points_list as $index => $points_item) {
                // Reference info
                $reference_html = '';
                if (!empty($points_item->parent_id)) {
                    // Booking info
                    if ($points_item->parent_type == 'EC_Flight_Bookings') {
                        $booking = BeanFactory::getBean('EC_Flight_Bookings', $points_item->parent_id);
                        $reference_html = '<a class="fw-semibold" target="_blank" href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $points_item->parent_id . '">' . $booking->name . '</a>';
                    }
                    // Point log info (Refund info)
                    elseif ($points_item->parent_type == 'EC_Contact_Points_Log') {
                        $reference_html = '<a class="fw-semibold" target="_blank" href="index.php?module=EC_Contact_Points_Log&action=DetailView&record=' . $points_item->parent_id . '">Liên kết hoàn</a>';
                    }
                }

                // Point info
                $point_html = '';
                if ($points_item->up > 0) $point_html = '<span style="color:green">+' . $points_item->up . '</span>';
                elseif ($points_item->down > 0) $point_html = '<span style="color:red">-' . $points_item->down . '</span>';

                // Refund point
                $refund_button = '';
                if ($points_item->down > 0 && $booking && in_array($booking->booking_status, ['1', '2', '6']) && $this->isRefund($points_item->id)) {
                    $refund_button = '<button type="button" class="btn btn-secondary btn-refund-point"
                        data-bs-toggle="modal" data-bs-target="#modalConfirmRefund"
                        record="' . $points_item->id . '"
                        refund_point="' . $points_item->down . '"
                        parent_type="' . $points_item->parent_type . '"
                        parent_id="' . $points_item->parent_id . '"
                        parent_name="' . $booking->name . '"
                    >
                        Hoàn điểm
                    </button>';
                }

                $tbody .= '<tr>
                    <td class="text-left"><strong>' . ($index + 1) . '</strong></td>
                    <td class="text-left">' . date($timedate->get_date_format() . ' H:i:s', strtotime($points_item->date_entered . ' +7 hours')) . '</td>
                    <td class="text-left">' . $points_item->name . '</td>
                    <td class="text-left">' . $reference_html . '</td>
                    <td class="text-center">' . $point_html . '</td>
                    <td class="text-center">' . $points_item->current_point . '</td>
                    <td class="text-right">' . $refund_button . '</td>
                </tr>';
            }

            $html = '<table class="table table-hover m-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ngày</th>
                        <th>Loại</th>
                        <th>Liên kết</th>
                        <th class="text-center">Điểm thay đổi</th>
                        <th class="text-center">Điểm còn lại</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="border-bottom-none">' . $tbody . '</tbody>
            </table>';
        } else {
            $html = '<div class="text-left fw-semibold">Không có dữ liệu. Liên hệ chưa tích lũy điểm!</div>';
        }

        // Popup confirm refund points
        if (!empty($tbody)) {
            $html .= '<div class="modal fade modal-confirm-refund" id="modalConfirmRefund" tabindex="-1" aria-labelledby="modalConfirmRefundLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5 text-white" id="modalConfirmRefundLabel">Hoàn điểm</h1>
                            <button type="button" class="btn-close me-2" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <h6>Xác nhận hoàn điểm cho lần sử dụng này</h6>
                            <ul class="list-group list-group-flush list-data-refund">
                                <li class="list-group-item pt-0 pb-2">Điểm hoàn lại: <b id="refund_point"></b></li>
                                <li class="list-group-item pt-0 pb-2">
                                    Liên kết: <a id="refund_reference" href="#" target="_blank"></a>
                                </li>
                                <li class="list-group-item pt-0 pb-2">
                                    Lý do hoàn:
                                    <input type="text" name="refund-reason" id="refund_reason" class="form-control" value="Hoàn điểm đã sử dụng" />
                                </li>
                            </ul>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" id="btn-confirm-refund-point" record="" contact_id="' . $this->bean->id . '">Xác nhận</button>
                        </div>
                    </div>
                </div>
            </div>';
        }

        $this->ss->assign('INFO_POINTS', $html);
    }

    private function populateLineCalls() {
        $html = '<table class="table table-hover m-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cuộc gọi</th>
                        <th>Loại</th>
                        <th>Gọi từ</th>
                        <th>Gọi đến</th>
                        <th>Trạng thái</th>
                        <th>Thời gian gọi</th>
                        <th>Hội thoại</th>
                        <th width="30%">Mô tả</th>
                    </tr>
                </thead><tbody>';

        $sql = 'SELECT id, name, status, call_from, call_to, direction, description, date_start, call_talk
            FROM calls
            WHERE (call_from = "' . $this->bean->phone_mobile . '" OR call_to = "' . $this->bean->phone_mobile . '")
            AND deleted = 0
            ORDER BY date_entered DESC';

        $res = $this->bean->db->query($sql);
        $count_calls = $this->bean->db->getRowCount($res);
        if ($count_calls == 0) {
            $html = '<div class="text-left fw-semibold">Không có dữ liệu. Liên hệ chưa có cuộc gọi nào!</div>';
            $this->ss->assign('INFO_CALLS', $html);
            return;
        }

        $i = 1;
        while ($row = $this->bean->db->fetchByAssoc($res)) {

            // STATUS
            $class_status = 'text-dark';
            if ((string)$row['status'] === 'done') {
                $class_status = 'text-primary';
            } elseif ((string)$row['status'] === 'processing') {
                $class_status = 'text-warning';
            } 

            // DIRECTION
            $class_direction = 'text-dark';
            if ((string)$row['direction'] === 'outbound') {
                $class_direction = 'text-primary';
            } elseif ((string)$row['direction'] === 'inbound') {
                $class_direction = 'text-success';
            } elseif ((string)$row['direction'] === 'missed') {
                $class_direction = 'text-danger';
            }

            $html .= '<tr>
                        <td class="td-index text-left"><strong>' . $i . '</strong></td>
                        <td class="td-name text-left"><a class="fw-semibold" href="index.php?module=Calls&action=DetailView&record=' . $row['id'] . '" target="_blank">'.$row['name'].'</td>
                        <td class="td-direction text-left ' . $class_direction . ' fw-semibold">' . $GLOBALS['app_list_strings']['calls_direction_list'][$row['direction']] . '</td>
                        <td class="td-call-from text-left">' . $row['call_from'] . '</td>
                        <td class="td-call-to text-left">' . $row['call_to'] . '</td>
                        <td class="td-status text-left ' . $class_status . ' fw-semibold">' . $GLOBALS['app_list_strings']['call_status_dom'][$row['status']] . '</td>
                        <td class="td-date-start text-left">' . $row['date_start'] . '</td>
                        <td class="td-call-talk text-left">' . global_secondsToTimeFormat($row['call_talk']) . '</td>
                        <td class="td-description text-left">' . $row['description'] . '</td>
                    </tr>';
                    $i++;
        }

        $html .= '</tbody>
            </table>';

        $this->ss->assign('INFO_CALLS', $html);
    }

    private function populateLineZalo() {
        $zaloContact = new EC_Zalo_Contacts();
        $tbody = "";

        $sql = "SELECT id
                ,name
                ,zalo_id
                ,oa_id
                ,alias
                ,avatar
                ,birth_date
                ,last_interaction
                ,is_follower
                ,tags
                ,province_city
                ,ward_commune
                ,address
            FROM ec_zalo_contacts
            WHERE contact_id = '{$this->bean->id}' AND status = '' AND deleted = 0";
        $res = $this->bean->db->query($sql);
        while($row = $this->bean->db->fetchByAssoc($res)) {
            $is_follower_html = $row['is_follower'] ? '<span class="text-primary">Đã quan tâm</span>' : '<span>Chưa quan tâm</span>';
            $last_interaction = !empty($row['last_interaction']) ? date('d-m-Y H:i', strtotime($row['last_interaction'])) : '';

            $is_call = $zaloContact->check_zalo_contact_action_by_data('call', $row['last_interaction'], $row['is_follower']);
            $is_send_consultation = $zaloContact->check_zalo_contact_action_by_data('send_consultation', $row['last_interaction'], $row['is_follower']);

            $action_html = '';
            if($is_call) $action_html .= "<h6><span class='badge rounded-pill bg-primary'>Có thể gọi</span></h6>";
            else $action_html .= "<h6><span class='badge rounded-pill bg-light text-dark fw-normal'><s>Có thể gọi</s></span></h6>";
            if($is_send_consultation) $action_html .= "<h6><span class='badge rounded-pill bg-primary'>Có thể chat</span></h6>";
            else $action_html .= "<h6><span class='badge rounded-pill bg-light text-dark fw-normal'><s>Có thể chat</s></span></h6>";

            $tbody .= "<tr>
                <td>
                    <div class='d-flex align-items-center gap-2'>
                        <img src='{$row['avatar']}' alt='Avatar'
                            style='width:50px; height:50px; border-radius:50%;'    
                        />
                        <a href='#'>{$row['alias']}</a>
                    </div>
                </td>
                <td>{$is_follower_html}</td>
                <td>{$row['tags']}</td>
                <td>{$row['address']} {$row['ward_commune']} {$row['province_city']}</td>
                <td>{$action_html}</td>
                <td>{$last_interaction}</td>
            </tr>";
        }

        if(empty($tbody)) $tbody = "<tr><td colspan='6'><i>Chưa có thông tin</i></td></tr>";
        $this->ss->assign("INFO_ZALO", "<table class='table table-hover m-0'>
            <thead>
                <tr>
                    <th width='30%'></th>
                    <th width='10%'>Trạng thái</th>
                    <th>Thẻ</th>
                    <th width='30%'>Địa chỉ</th>
                    <th>Hành động</th>
                    <th width='12%'>Tương tác cuối</th>
                </tr>
            </thead>
            <tbody class='border-bottom-none'>{$tbody}</tbody>
        </table>");
    }

    public function isRefund($record_id) {
        return $this->bean->db->getOne("SELECT COUNT(pl.id)
            FROM ec_contact_points_log pl
            WHERE pl.parent_id = '{$record_id}'
                AND pl.parent_type = 'EC_Contact_Points_Log'
                AND pl.up > 0
                AND pl.down = 0
                AND pl.deleted = 0
        ") > 0 ? false : true;
    }
}

