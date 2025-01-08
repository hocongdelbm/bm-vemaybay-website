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
		global $app_list_strings, $current_user, $timedate;
		$date_format = $timedate->get_date_format();

		$contact_button  	= '<a target="_blank" class="btn btn-info btn-view-detail" data-bs-toggle="modal" data-bs-target="#modalHistoryContactBookings" class="contact_name" data-id="'.$this->bean->id.'"><span>Chi tiết</span></a>';
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
		$this->ss->assign('DETAIL_BOOKING', $contact_button.$modal_history_bookings);
	}
}
