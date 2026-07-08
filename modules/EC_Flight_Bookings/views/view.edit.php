<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('custom/entrypoints/entryAuthClass/entryFareSystemClass.php');

require_once 'modules/EC_Flight_Bookings/custom/viewedit/Assets.php';
require_once 'modules/EC_Flight_Bookings/custom/viewedit/BasicFields.php';

require_once 'modules/EC_Flight_Bookings/custom/viewedit/edit_fields.php';

require_once 'modules/EC_Flight_Bookings/custom/viewedit/Itinerary.php';
require_once 'modules/EC_Flight_Bookings/custom/viewedit/LineDetails.php';
require_once 'modules/EC_Flight_Bookings/custom/viewedit/Passenger.php';

class EC_Flight_BookingsViewEdit extends ViewEdit
{
	public $bean;
	private $_outbound_airline = '';
	private $_inbound_airline = '';
	private $_outbound_ticket_class = '';
	private $_inbound_ticket_class = '';
	private $_journey = '';
	private $icon_x = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>';

	use ECFlightBookingEditAssetsTrait;
	use EditFieldsTrait;
	use ECFlightBookingEditBasicFieldsTrait;
	use ECFlightBookingEditItineraryTrait;
	use ECFlightBookingEditLineDetailsTrait;
	use ECFlightBookingEditPassengerTrait;

	public function __construct()
	{
		parent::__construct();
	}

	public function display()
	{
		global $current_user;

		$status_arr = ['1', '6', '2', '3']; // allow edit normal user
		$status_com_arr = ['7', '8']; // allow edit Admin (Role QuanLy)

		$canEditNormal = (empty($this->bean->id) || in_array($this->bean->booking_status, $status_arr) || (isset($_POST['isDuplicate']) && (string)$_POST['isDuplicate'] === 'true')) && ACLController::checkAccess($this->bean->object_name, 'edit', true);

		$isCompletedManagerEdit = !$canEditNormal && in_array($this->bean->booking_status, $status_com_arr) && isManagerUser($current_user->id);

		if (!$canEditNormal && !$isCompletedManagerEdit) {
			header('Location: index.php?module=' . $this->bean->object_name . '&action=Error&error_string=' . urlencode('Bạn không được quyền chỉnh sửa booking này'));
			exit();
		}

		$this->displayJS();
		$this->displayCSS();

		if ($isCompletedManagerEdit && !is_admin($current_user)) {
			$this->displayJS_Edit();
		}

		$this->populateCustomFields();

		$this->populateCustomPanels();

		parent::display();
	}

	public function populateCustomFields()
	{
		$this->populateBasicFields();

		// Thông tin hóa đơn
		$this->assignInvoiceField();

		// Nơi đặt BK
		$this->assignCityField();
	}

	public function populateCustomPanels()
	{
		// Chi tiết vé
		$this->populateLineDetails();

		// Thông tin hành trình
		$this->populateLineItineraries();

		// Thông tin hành khách
		if ($this->bean->isUseNewBaggage($this->bean->date_entered, $this->bean->created_by)) {
			$this->populateLinePassengers();
		} else {
			$this->populateLinePassengersOld();
		}
	}
}
