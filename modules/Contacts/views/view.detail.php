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
    public function display()
    {
        global $sugar_config;

        // Create and update contact
        createContactsForBooking($this->bean->phone_mobile);

        $this->populateCustomButtons();
		$this->populateLinePoints();

        $aop_portal_enabled = !empty($sugar_config['aop']['enable_portal']) && !empty($sugar_config['aop']['enable_aop']);

        $this->ss->assign("AOP_PORTAL_ENABLED", $aop_portal_enabled);

        require_once('modules/AOS_PDF_Templates/formLetter.php');
        formLetter::DVPopupHtml('Contacts');

        $admin = BeanFactory::newBean('Administration');
        $admin->retrieveSettings();
        if (isset($admin->settings['portal_on']) && $admin->settings['portal_on']) {
            $this->ss->assign("PORTAL_ENABLED", true);
        }

        parent::display();

        // If user shares phone number, update the phone number to contact
        // if(!empty($this->bean->zalo_id) && empty($this->bean->phone_mobile)) {
        //     require_once('modules/EC_SMS_Logs/Zalo.php');
        //     $objZalo = new Zalo();
        //     $json = $objZalo->get_user_info($this->bean->zalo_id);
        //     $arr = json_decode($json, true);

        //     if(isset($arr['error']) && $arr['error'] == 0) {
        //         // Update phone
        //         if(isset($arr['data']['shared_info']) && isset($arr['data']['shared_info']['phone']) && !empty($arr['data']['shared_info']['phone'])) {
        //             $this->bean->phone_mobile = '0' . substr($arr['data']['shared_info']['phone'], 2);
        //             $this->bean->save();
        //             header("Refresh:0");
        //         }
        //     }
        // }
    }

    function populateCustomButtons()
    {
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

    public function populateLinePoints() {
        global $timedate;
		$html = $tbody = '';

        $line_points = BeanFactory::getBean('EC_Contact_Points_Log');
        $points_list = $line_points->get_full_list(
            "date_entered DESC",
            "(
                ec_contact_points_log.contact_id = '". $this->bean->id ."'  
            )",
            false,
            0
        );

        if (count($points_list) > 0) {
            foreach ($points_list as $index => $points_item) {
                // Reference info
                $reference_html = '';
                if(!empty($points_item->parent_id)) {
                    // Booking info
                    if($points_item->parent_type == 'EC_Flight_Bookings') {
                        $booking = BeanFactory::getBean('EC_Flight_Bookings', $points_item->parent_id);
                        $reference_html = '<a class="fw-semibold" target="_blank" href="index.php?module=EC_Flight_Bookings&action=DetailView&record='.$points_item->parent_id.'">'.$booking->name.'</a>';
                    }
                    // Point log info (Refund info)
                    elseif($points_item->parent_type == 'EC_Contact_Points_Log') {
                        $reference_html = '<a class="fw-semibold" target="_blank" href="index.php?module=EC_Contact_Points_Log&action=DetailView&record='.$points_item->parent_id.'">Liên kết hoàn</a>';
                    }
                }

                // Point info
                $point_html = '';
                if($points_item->up > 0) $point_html = '<span style="color:green">+'.$points_item->up.'</span>';
                elseif($points_item->down > 0) $point_html = '<span style="color:red">-'.$points_item->down.'</span>';
                
                // Refund point
                $refund_button = '';
                if($points_item->down > 0 && $booking && in_array($booking->booking_status, ['1', '2', '6']) && $this->isRefund($points_item->id)) {
                    $refund_button = '<button type="button" class="btn btn-secondary btn-refund-point"
                        data-bs-toggle="modal" data-bs-target="#modalConfirmRefund"
                        record="'.$points_item->id.'"
                        refund_point="'.$points_item->down.'"
                        parent_type="'.$points_item->parent_type.'"
                        parent_id="'.$points_item->parent_id.'"
                        parent_name="'.$booking->name.'"
                    >
                        Hoàn điểm
                    </button>';
                }

                $tbody .= '<tr>
                    <td class="text-left"><strong>'.($index + 1).'</strong></td>
                    <td class="text-left">' . date($timedate->get_date_format() . ' H:i:s', strtotime($points_item->date_entered. ' +7 hours')) . '</td>
                    <td class="text-left">'.$points_item->name.'</td>
                    <td class="text-left">'.$reference_html.'</td>
                    <td class="text-center">'.$point_html.'</td>
                    <td class="text-center">'.$points_item->current_point.'</td>
                    <td class="text-right">'.$refund_button.'</td>
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
                <tbody class="border-bottom-none">'.$tbody.'</tbody>
            </table>';
        } else {
            $html = '<div class="text-left fw-semibold">Không có dữ liệu. Liên hệ chưa tích lũy điểm!</div>';
        }

        // Popup confirm refund points
        if(!empty($tbody)) {
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
                            <button type="button" class="btn btn-primary" id="btn-confirm-refund-point" record="" contact_id="'.$this->bean->id.'">Xác nhận</button>
                        </div>
                    </div>
                </div>
            </div>';
        }

		$this->ss->assign('INFO_POINTS', $html);
    }

    public function isRefund($record_id) {
        return $this->bean->db->getOne("SELECT COUNT(pl.id)
            FROM ec_contact_points_log pl
            WHERE pl.parent_id = '$record_id'
                AND pl.parent_type = 'EC_Contact_Points_Log'
                AND pl.up > 0
                AND pl.down = 0
                AND pl.deleted = 0
        ") > 0 ? false : true;
    }
}
