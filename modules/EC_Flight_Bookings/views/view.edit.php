<?php
if (!defined('sugarEntry') || !sugarEntry)
	die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.edit.php');
require_once('custom/entrypoints/entryAuthClass/entryFareSystemClass.php');
require_once 'modules/EC_Flight_Bookings/custom/viewedit/Assets.php';
require_once 'modules/EC_Flight_Bookings/custom/viewedit/BasicFields.php';
require_once 'modules/EC_Flight_Bookings/custom/viewedit/Itinerary.php';
require_once 'modules/EC_Flight_Bookings/custom/viewedit/LineDetails.php';
require_once 'modules/EC_Flight_Bookings/custom/viewedit/Passenger.php';

class EC_Flight_BookingsViewEdit extends ViewEdit
{
	/**
	 * @var EC_Flight_Bookings
	 */
	public $bean;
	private $_outbound_airline = '';
	private $_inbound_airline = '';
	private $_outbound_ticket_class = '';
	private $_inbound_ticket_class = '';
	private $_journey = '';
	private $icon_x = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>';

	use ECFlightBookingEditAssetsTrait;
	use ECFlightBookingEditBasicFieldsTrait;
	use ECFlightBookingEditItineraryTrait;
	use ECFlightBookingEditLineDetailsTrait;
	use ECFlightBookingEditPassengerTrait;

	function __construct()
	{
		parent::__construct();
	}

	function display()
	{
		global $current_user;

		$status_arr = ['1', '6', '2', '3']; // allow edit
		$status__com_arr = ['7', '8']; // allow edit admin và QL chỉnh (Admin edit all)

		if (
			(empty($this->bean->id)
				|| in_array($this->bean->booking_status, $status_arr)
				|| (isset($_POST['isDuplicate']) && $_POST['isDuplicate'] == 'true'))
			&& ACLController::checkAccess('EC_Flight_Bookings', 'edit', true)
		) {
			// Load JS/CSS cho màn hình edit booking.
			$this->displayJS();
			$this->displayCSS();

			// Assign các field cơ bản: booking, đại lý, xuất vé, liên hệ, hãng bay, ngày xuất vé, giao cho.
			$this->populateBasicFields();

			// Render bảng chi tiết vé/giá để JS render row và tính tổng.
			$this->populateLineDetails();

			// Render bảng hành trình để JS render row bay, quá cảnh, hạn giữ chỗ.
			$this->populateLineItineraries();

			// Render bảng hành khách theo nghiệp vụ hành lý mới/cũ dựa trên ngày tạo và user tạo booking.
			if ($this->bean->isUseNewBaggage($this->bean->date_entered, $this->bean->created_by)) {
				$this->populateLinePassengers();
			} else {
				$this->populateLinePassengersOld();
			}

			parent::display();
		} else if (in_array($this->bean->booking_status, $status__com_arr) && (isManagerUser($current_user->id))) {
			// Booking đã xuất vé/hoàn tất: quản lý được vào edit với một số panel bị khóa theo JS.
			$this->displayJS();
			$this->displayCSS();

			if (!is_admin($current_user)) {
				// User quản lý không phải admin chỉ được sửa một số vùng, ẩn các detail panel nhạy cảm.
				$this->displayJS_Edit();
			}

			// Assign các field cơ bản: booking, đại lý, xuất vé, liên hệ, hãng bay, ngày xuất vé, giao cho.
			$this->populateBasicFields();

			// Render bảng chi tiết vé/giá để JS render row và tính tổng.
			$this->populateLineDetails();

			// Render bảng hành trình để JS render row bay, quá cảnh, hạn giữ chỗ.
			$this->populateLineItineraries();

			// Render bảng hành khách theo nghiệp vụ hành lý mới/cũ dựa trên ngày tạo và user tạo booking.
			if ($this->bean->isUseNewBaggage($this->bean->date_entered, $this->bean->created_by)) {
				$this->populateLinePassengers();
			} else {
				$this->populateLinePassengersOld();
			}

			parent::display();
		} else {
			header('Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=' . urlencode('Bạn không được quyền chỉnh sửa booking này'));
			exit();
		}
	}

}
