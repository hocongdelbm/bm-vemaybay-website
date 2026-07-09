<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('custom/entrypoints/entryAuthClass/entryFareSystemClass.php');

require_once 'modules/EC_Flight_Bookings/custom/viewedit/Assets.php';
require_once 'modules/EC_Flight_Bookings/custom/viewedit/edit_fields.php';
require_once 'modules/EC_Flight_Bookings/custom/viewedit/edit_panels.php';

class EC_Flight_BookingsViewEdit extends ViewEdit
{
	public $bean;
	private $_outbound_airline = '';
	private $_inbound_airline = '';
	private $_outbound_ticket_class = '';
	private $_inbound_ticket_class = '';
	private $_journey = '';

	use EditAssetsTrait;
	use EditFieldsTrait;
	use EditPanelsTrait;

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

		$this->populateCustomFields();
		$this->populateCustomPanels();

		parent::display();
	}

	public function populateCustomFields()
	{
		// Tên booking
		$this->assignNameBookingField();

		// Là đại lý
		$this->assignIsAgentField();

		// Check đã xuất vé
		$this->assignTicketExportedField();

		// Danh xưng liên hệ
		$this->assignContactNameField();

		// Airline outbound - inbound
		$this->assignAirlineField();

		// Ngày xuất vé lượt đi - về
		$this->assignDateTicketIssueField();

		// Giao cho
		$this->assignAssignToUserField();

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
