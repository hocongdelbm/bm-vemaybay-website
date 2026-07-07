<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('modules/EC_Messages/SMS.php');
require_once 'custom/include/helpers/api/Onepay.php';

require_once 'modules/EC_Flight_Bookings/custom/viewdetail/Assets.php';
require_once 'modules/EC_Flight_Bookings/custom/viewdetail/Buttons.php';
require_once 'modules/EC_Flight_Bookings/custom/viewdetail/BookingFields.php';
require_once 'modules/EC_Flight_Bookings/custom/viewdetail/Itinerary.php';
require_once 'modules/EC_Flight_Bookings/custom/viewdetail/Passenger.php';
require_once 'modules/EC_Flight_Bookings/custom/viewdetail/Voucher.php';
require_once 'modules/EC_Flight_Bookings/custom/viewdetail/Notes.php';
require_once 'modules/EC_Flight_Bookings/custom/viewdetail/Payment.php';
require_once 'modules/EC_Flight_Bookings/custom/viewdetail/ZaloSms.php';
require_once 'modules/EC_Flight_Bookings/custom/viewdetail/Templates.php';
require_once 'modules/EC_Flight_Bookings/custom/viewdetail/Permissions.php';

class EC_Flight_BookingsViewDetail extends ViewDetail
{
	use AssetsTrait;
	use ButtonsTrait;
	use BookingFieldsTrait;
	use ItineraryTrait;
	use PassengerTrait;
	use VoucherTrait;
	use NotesTrait;
	use PaymentTrait;
	use ZaloSmsTrait;
	use TemplatesTrait;
	use PermissionsTrait;

	public $bean;
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
		$this->populateLineNotesMessage();
		$this->populateLineDetails();
		$this->populateSMSTemplate();

		// Itineraries
		$html = $this->populateLineItineraries($deparment_info);
		$this->ss->assign('LINE_ITINERARIES', $html);

		// Passengers
		$this->ss->assign('LINE_PASSENGERS', $this->populateLinePassengers());

		// Related vouchers
		$this->ss->assign('LINE_RELATE_VOUCHER', $this->populateLineRelateVoucher());

		$this->createModal();

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
		$this->renderCancelledBookingButton();

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

		// Revenue update button
		$this->assignUpdateRevenueButton();
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

	/**
	 * Ticket detail table.
	 */
	public function populateLineDetails()
	{
		$html = $this->renderLineDetailsTableHeader();
		$i = 0;
		$totals = $this->newLineDetailsTotals();

		$res = $this->queryLineDetailRows();
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$html .= $this->renderLineDetailRow($row, $i);
			$this->addLineDetailTotals($totals, $row);
			$i++;
		}

		$html .= $this->renderLineDetailsFooter($totals);
		$html .= '</table>';
		$html .= $this->renderSupplierOptionHiddenInput();
		$this->ss->assign('LINE_DETAILS', $html);
	}

	/**
	 * Itinerary table.
	 */
	public function populateLineItineraries($deparment_info)
	{
		global $app_list_strings, $timedate, $current_user;

		// Date format
		$date_format = $timedate->get_date_format();

		// Airport list
		$airport_list = $app_list_strings['domestic_airport_list'] + $app_list_strings['africa_airport_list'] + $app_list_strings['americas_airport_list'] + $app_list_strings['australia_airport_list'] + $app_list_strings['europe_airport_list'] + $app_list_strings['northeast_asia_airport_list'] + $app_list_strings['southeast_asia_airport_list'];

		// E-ticket mail permission
		$use_mail_eticket = is_admin($current_user) ? 1 : $deparment_info['use_mail_eticket'];

		// Itinerary rows
		$itineraryRows = $this->getItineraryRowsForDetail();

		// Itinerary header
		$html = $this->renderLineItineraryTableHeader();

		// Applied passengers
		list($departure_applied_pass, $arrival_applied_pass) = $this->getAppliedPassengerItinerariesByDirection();

		$html .= $this->renderOriginalItineraryRows($itineraryRows['original'], $date_format, $airport_list, $use_mail_eticket, $departure_applied_pass, $arrival_applied_pass);

		// Itinerary templates
		return $this->appendLineItineraryTemplates($html, $itineraryRows['edited']);
	}

	/**
	 * Passenger table.
	 */
	public function populateLinePassengers()
	{
		global $app_list_strings, $timedate;

		// Date format
		$date_format = $timedate->get_date_format();

		// Passenger rows
		$passengerRows = $this->getPassengerRowsForDetail();

		// Passenger header
		$html = $this->renderPassengerTableHeader();

		// Original passengers
		$html .= $this->renderOriginalPassengerRows($passengerRows['original'], $date_format, $app_list_strings);

		// Edited passengers
		$html .= $this->renderEditedPassengerRows($passengerRows['edited']);
		$html .= '</tbody></table>';

		return $html;
	}

	/**
	 * Related voucher table.
	 */
	public function populateLineRelateVoucher()
	{
		global $db;

		// Voucher header
		$html = $this->renderRelatedVoucherTableHeader();

		// Voucher rows
		$res = $this->queryRelatedVoucherRows($db);
		$count = $db->countRows($res);

		if ($count > 0) {
			$i = 1;
			while ($row = $db->fetchByAssoc($res)) {
				// Voucher row
				$html .= $this->renderRelatedVoucherRow($row, $i);
				$i++;
			}
		} else {
			// Empty voucher row
			$html .= $this->renderEmptyRelatedVoucherRow();
		}

		$html .= '</table>';

		return $html;
	}
}
