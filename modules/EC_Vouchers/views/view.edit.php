<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.edit.php');

class EC_VouchersViewEdit extends ViewEdit {
    function __construct() {
        parent::__construct();
    }

    function display() {
        $this->populateCustomFields();
        $this->displayJS();
        parent::display();
    }

    function populateCustomFields() {
        // Mã voucher
        if (empty($this->bean->name)) {
            do {
                $this->bean->name = strtoupper(substr(sha1(mt_rand()), 17, 6));
                $exists = $this->checkVoucherExists($this->bean->name);
            } while ($exists);
        }

        // Thời hạn voucher
        $duration = '
            <div class="from-to-date--wrap d-inline-flex gap-2 align-items-center">
                <div class="dateTime d-flex gap-2 position-relative">
                    <input type="text" id="validate_from_date" name="validate_from_date" value="'.(!empty($_POST['validate_from_date']) ? $_POST['validate_from_date'] : $this->bean->validate_from_date).'" class="date_input box-input">
                    <button class="icon_dateTime" type="button" id="validate_from_date_trigger" onclick="return false;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
                            <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
                        </svg>
                    </button>
                </div>
                <svg width="40" height="20" fill="none">
                    <g clip-path="url(#icon_arrow_flight_long_svg__clip0)" stroke="#718096" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M33.5 8.5L36 11M4 11h32"></path>
                    </g>
                    <defs>
                        <clipPath id="icon_arrow_flight_long_svg__clip0">
                            <path fill="#fff" d="M0 0h40v20H0z"></path>
                        </clipPath>
                    </defs>
                </svg>
                <div class="dateTime d-flex gap-2 position-relative">
                    <input type="text" id="validate_to_date" name="validate_to_date" value="'.(!empty($_POST['validate_to_date']) ? $_POST['validate_to_date'] : $this->bean->validate_to_date).'" class="date_input box-input">
                    <button class="icon_dateTime" type="button" id="validate_to_date_trigger" onclick="return false;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
                            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
                            <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        ';

        $duration .= '<script>
                        $(document).ready(function() {
                            Calendar.setup({
                                inputField : "validate_from_date",
                                daFormat : "%d-%m-%Y",
                                button : "validate_from_date_trigger",
                                singleClick : true,
                                dateStr : "",
                                step : 1,
                                weekNumbers : false
                            });
                            Calendar.setup({
                                inputField : "validate_to_date",
                                daFormat : "%d-%m-%Y",
                                button : "validate_to_date_trigger",
                                singleClick : true,
                                dateStr : "",
                                step : 1,
                                weekNumbers : false
                            });
                        });
                    </script>';
        $this->ss->assign('CUS_DURATION', $duration);
    }

    function checkVoucherExists($voucher) {
        $sql = 'SELECT COUNT(*) WHERE name = "' . $voucher . '"';
        return $this->bean->db->query($sql);
    }

    function displayJS() {
        echo '<script>
            $(document).ready(function() {
                Calendar.setup ({
                    inputField : "from_date",
                    daFormat : "%d-%m-%Y %H:%M",
                    button : "from_date_trigger",
                    singleClick : true,
                    dateStr : "' . date('d-m-Y') . '",
                    step : 1,
                    weekNumbers:false
                });
                Calendar.setup ({
                    inputField : "to_date",
                    daFormat : "%d-%m-%Y %H:%M",
                    button : "to_date_trigger",
                    singleClick : true,
                    dateStr : "' . date('d-m-Y') . '",
                    step : 1,
                    weekNumbers:false
                });
                $("#reduce_amount").addClass("allow-number-only");
                $(".allow-number-only").number(true, 0, dec_sep, num_grp_sep);
            })
        </script>';
    }
}

// tạo voucher khuyến mãi
        // $html = '<h1 style="text-align: center; font-size: 13pt;">TẠO DS VOUCHER KHUYẾN MÃI</h1>';
        // $html .= '<br><div style="text-align: center;"><b>SL voucher phát hành:</b> <input type="text" name="voucher_qty" size="5">&nbsp;&nbsp;&nbsp;<b>Thời hạn:</b> <input type="text" name="from_date" size="10"> - <input type="text" name="to_date" size="10"></div>';
        // $html .= '<br><div style="text-align: center;"><b>Mô tả:</b> <textarea type="text" name="description" rows="4" cols="66" style="vertical-align: top;"></textarea></div>';
        // $html .= '<br><div style="text-align: center;"><input type="button" id="create_voucher" value="Tạo Voucher" style="font-weight: bold;"></div>';
        // $this->ss->assign('CREATE_VOUCHER', $html);

        // array (
        //         0 => array(
        //             'name' => 'create_voucher',
        //             'label' => 'LBL_CREATE_VOUCHER',
        //             'customCode' => '{$CREATE_VOUCHER}',
        //         ),
        //     ),
