<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('modules/EC_Messages/SMS.php');
require_once 'custom/include/helpers/api/Onepay.php';

require_once 'modules/EC_Flight_Bookings/custom/viewdetail/Assets.php';

require_once 'modules/EC_Flight_Bookings/custom/viewdetail/detail_buttons.php';
require_once 'modules/EC_Flight_Bookings/custom/viewdetail/detail_fields.php';

require_once 'modules/EC_Flight_Bookings/custom/viewdetail/Itinerary.php';
require_once 'modules/EC_Flight_Bookings/custom/viewdetail/Passenger.php';
require_once 'modules/EC_Flight_Bookings/custom/viewdetail/detail_panels.php';

require_once 'modules/EC_Flight_Bookings/custom/viewdetail/Notes.php';
require_once 'modules/EC_Flight_Bookings/custom/viewdetail/Payment.php';
require_once 'modules/EC_Flight_Bookings/custom/viewdetail/ZaloSms.php';
require_once 'modules/EC_Flight_Bookings/custom/viewdetail/Templates.php';

class EC_Flight_BookingsViewDetail extends ViewDetail
{
	use AssetsTrait;

	use DetailButtonsTrait;
	use DetailFieldsTrait;

	use ItineraryTrait;
	use PassengerTrait;
	use DetailPanelsTrait;

	use NotesTrait;
	use PaymentTrait;
	use ZaloSmsTrait;
	use TemplatesTrait;

	private $_outbound_airline = '';
	private $_inbound_airline = '';
	private $_outbound_ticket_class = '';
	private $_inbound_ticket_class = '';
	private $_is_had_rv = 0;
	private $editing_rights = false;

	public function display()
	{
		global $current_user;
		$deparment_info = myGetDepartmentInfo($current_user->department_id);
		$this->editing_rights = ACLController::checkAccess($this->bean->object_name, 'edit', true);

		$this->displayCSSTrait();

		$this->populateCustomButtons($deparment_info);
		$this->populateCustomFields();
		$this->populateCustomPanels($deparment_info);

		$this->populateLineNotesMessage();
		$this->populateSMSTemplate();

		parent::display();

		$this->displayJSTrait();
	}

	/**
	 * Detail action buttons.
	 */
	public function populateCustomButtons($deparment_info)
	{
		global $current_user;
		$use_mail_confirm = is_admin($current_user) ? 1 : $deparment_info['use_mail_confirm'];

		// Cancel button
		$this->assignCancelledBookingButton();

		// Status and call buttons
		$this->assignStatusAndCallsButtons();

		// Mail confirm button
		$this->assignSendMailConfirmButton($use_mail_confirm);

		// Status dropdown
		$this->assignChangeBookingStatusButton();

		// Viewed users button
		$this->assignViewedBookingButton();

		// Document buttons
		$this->assignDocumentButtons();

		// Receipt voucher button
		$this->assignCreateReceiptVoucherButton();

		// Ticket return button
		$this->assignTicketReturnButton();

		// Post-ticket edit buttons
		$this->assignPostTicketEditButtons();

		// Auto book button
		$this->assignAutoBookButton();

		// Print and send button
		$this->assignPrintAndSendTicketButton();
	}

	/**
	 * Booking custom fields.
	 */
	public function populateCustomFields()
	{
		// Invoice info
		$this->assignInvoiceInfoField();

		// Booking name
		$this->assignBookingNameField();

		// Ticket type
		$this->assignTicketTypeField();

		// Customer source
		$this->assignCustomerSourceField();

		// Booking bookmarks
		$this->assignBookingBookmarkField();

		// System bookmarks
		$this->assignSystemBookmarkField();

		// Ticket exported flags
		$this->assignTicketExportedField();

		// Ticket issue dates
		$this->assignTicketIssueDateField();

		// Airlines
		$this->assignAirlineField();

		// Contact name
		$this->assignContactNameField();

		// Contact phone
		$this->assignContactPhoneField();

		// Zalo info
		$this->assignZaloInfoField();

		// Paid flag
		$this->assignPaidFlagField();

		// Working process actions
		$this->assignWorkingProcessActionFields();

		// Invoice export flags
		$this->assignInvoiceExportFields();

		// Online payment
		$this->assignOnlinePaymentFields();

		// Transaction history
		$this->assignTransactionHistoryField();

		// Discount amount
		$this->assignDiscountAmountField();

		// Profit info
		$this->assignProfitField();
	}

	/**
	 * Booking custom panels.
	 */
	public function populateCustomPanels($deparment_info)
	{
		// Itineraries
		$this->ss->assign('LINE_ITINERARIES', $this->populateLineItineraries($deparment_info));

		// Ticket detail
		$this->ss->assign('LINE_DETAILS', $this->populateLineDetails());

		// Passengers
		$this->ss->assign('LINE_PASSENGERS', $this->populateLinePassengers());

		// Related vouchers
		$this->ss->assign('LINE_RELATE_VOUCHER', $this->populateLineRelateVoucher());
	}

	/**
	 * Notes panel.
	 */
	public function populateLineNotesMessage()
	{
		// KPI actions
		$actions_kpi = $this->getWorkingProcessNoteActions();

		// Note rows
		list($row_content, $note_username) = $this->renderNoteMessageRows($actions_kpi);

		// Notes panel
		$html = $this->renderLineNotesPanel($row_content, $note_username);

		// Delete note dialog
		$html .= $this->renderDeleteMessageDialog();

		// Smarty assignment
		$this->ss->assign('BUTTON_LINE_NOTES', $html);
	}
}
