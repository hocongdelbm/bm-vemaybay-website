<?php

use PhpOffice\PhpSpreadsheet\Shared\OLE\PPS;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Get new report terms
 * @param string $selectedValue
 * @return string
 */
function myGetNewReportTerms($selectedValue = '', $returnType = 'html')
{
    $currMonth      = date('n');
    $prevMonth      = $currMonth - 1;
    $currQuarter    = ceil(date('n', time()) / 3);
    $prevQuarter    = $currQuarter - 1;
    $currYear       = date('Y');
    $prevYear       = $currYear - 1;

    if ($selectedValue == '') {
        $selectedValue = 'q' . $currQuarter;
    }

    // Calculate months
    $months = array();
    for ($i = 1; $i <= 12; $i++) {
        $lastDayOfMonth = cal_days_in_month(CAL_GREGORIAN, $i, date('Y'));
        $month = str_pad($i, 2, '0', STR_PAD_LEFT);
        $months['m' . $i] = array(
            'name' => 'Tháng ' . $i,
            'from_date' => '01-' . $month . '-' . $currYear,
            'to_date' => $lastDayOfMonth . '-' . $month . '-' . $currYear,
            'term' => 'm' . $i,
            'year' => $currYear
        );
    }

    if ($prevMonth <= 0) {
        $months['prev_month'] = array(
            'name' => 'Tháng trước',
            'from_date' => '01-12-' . $prevYear,
            'to_date' => '31-12-' . $prevYear,
            'term' => 'm12',
            'year' => $prevYear
        );
    } else {
        $lastDayOfPrevMonth = cal_days_in_month(CAL_GREGORIAN, $prevMonth, $currYear);
        $months['prev_month'] = array(
            'name' => 'Tháng trước',
            'from_date' => '01-' . str_pad($prevMonth, 2, '0', STR_PAD_LEFT) . '-' . $currYear,
            'to_date' => str_pad($lastDayOfPrevMonth, 2, '0', STR_PAD_LEFT) . '-' . str_pad($prevMonth, 2, '0', STR_PAD_LEFT) . '-' . $currYear,
            'term' => 'm' . $prevMonth,
            'year' => $currYear
        );
    }

    $months['this_month'] = array(
        'name' => 'Tháng này',
        'from_date' => $months['m' . $currMonth]['from_date'],
        'to_date' => $months['m' . $currMonth]['to_date'],
        'term' => 'm' . $currMonth,
        'year' => $currYear
    );

    //========== Calculate quarters
    $quarters = array();
    $quarters['q1'] = array('name' => 'Quý 1', 'from_date' => '01-01-' . $currYear, 'to_date' => '31-03-' . $currYear, 'term' => 'q1', 'year' => $currYear);
    $quarters['q2'] = array('name' => 'Quý 2', 'from_date' => '01-04-' . $currYear, 'to_date' => '30-06-' . $currYear, 'term' => 'q2', 'year' => $currYear);
    $quarters['q3'] = array('name' => 'Quý 3', 'from_date' => '01-07-' . $currYear, 'to_date' => '30-09-' . $currYear, 'term' => 'q3', 'year' => $currYear);
    $quarters['q4'] = array('name' => 'Quý 4', 'from_date' => '01-10-' . $currYear, 'to_date' => '31-12-' . $currYear, 'term' => 'q4', 'year' => $currYear);

    if ($prevQuarter <= 0) {
        $quarters['prev_quarter'] = array(
            'name' => 'Quý trước',
            'from_date' => '01-10-' . $prevYear,
            'to_date' => '31-12-' . $prevYear,
            'term' => 'q4',
            'year' => $prevYear
        );
    } else {
        $quarters['prev_quarter'] = array(
            'name' => 'Quý trước',
            'from_date' => $quarters['q' . $prevQuarter]['from_date'],
            'to_date' => $quarters['q' . $prevQuarter]['to_date'],
            'term' => 'q' . $prevQuarter,
            'year' => $currYear
        );
    }

    $quarters['this_quarter'] = array(
        'name' => 'Quý này',
        'from_date' => $quarters['q' . $currQuarter]['from_date'],
        'to_date' => $quarters['q' . $currQuarter]['to_date'],
        'term' => 'q' . $currQuarter,
        'year' => $currYear
    );

    //========== Calculate year
    $years = array();
    $years['prev_year'] = array(
        'name' => 'Năm trước',
        'from_date' => '01-01-' . $prevYear,
        'to_date' => '31-12-' . $prevYear,
        'term' => 'year',
        'year' => $prevYear
    );

    $years['this_year'] = array(
        'name' => 'Năm nay',
        'from_date' => '01-01-' . $currYear,
        'to_date' => '31-12-' . $currYear,
        'term' => 'year',
        'year' => $currYear
    );

    $options = array_merge($months, $quarters, $years);
    $html = '';
    foreach ($options as $optKey => $optVal) {
        $html .= '<option ' . ($optKey == $selectedValue ? 'selected="selected"' : '') . ' value="' . $optKey . '" data-term="' . $optVal['term'] . '" data-year="' . $optVal['year'] . '" data-fromdate="' . $optVal['from_date'] . '" data-todate="' . $optVal['to_date'] . '">' . $optVal['name'] . '</option>';
    }

    if ($returnType == 'array') {
        return $options;
    } else {
        return $html;
    }
}

/**
 * Lấy tỉ giá của ngoại tê theo giao dịch mua, bán
 * @param string $CurrencyCode mã loại tiền tệ VND, USD...
 * @param string $type loại giao dịch mua bán
 * @return numeric $arr[$i][$type] tỉ giá trả về
 */
function myGetCurrencyExrate($CurrencyCode, $type = 'Sell')
{
    $xml = simplexml_load_file("http://www.vietcombank.com.vn/ExchangeRates/ExrateXML.aspx");
    $obj = $xml->children();
    $arr = $obj->Exrate;
    for ($i = 0; $i < count($arr); $i++) {
        if ($arr[$i]['CurrencyCode'] == $CurrencyCode) {
            return $arr[$i][$type];
        }
    }
}

/**
 * Lấy dữ liệu và trả về HTML <option></option>
 * @param string $module tên phân hệ
 * @param string $val giá trị để thiết lập selected
 * @param string $val_name tên trường cần thiết lập trong value
 * @param string $where điều kiện truy vấn bổ sung
 * @return string $html
 */
function myGetSelectOptionsWithDb($module, $val, $val_name = 'id', $where = '')
{
    global $db;
    $sql = "SELECT " . $val_name . "," . ($module == 'Users' ? "CONCAT(IFNULL(last_name, ''),' ',IFNULL(first_name, '')) AS name" : "name") . "
				FROM " . strtolower($module) . " 
				WHERE deleted = 0 " . $where;
    $res = $db->query($sql);
    $html = '';
    while ($row = $db->fetchByAssoc($res)) {
        if ($row[$val_name] == $val || (is_array($val) && in_array($row[$val_name], $val))) $selected = 'selected="selected"';
        else $selected = '';
        $html .= '<option ' . $selected . ' value="' . $row[$val_name] . '">' . $row['name'] . '</option>';
    }
    return $html;
}

/**
 * Subject: Lấy danh sách option và chia làm 2 cột
 * đây là phiên bản cải tiến của hàm myGetSelectOptionsWithDb()
 * By: LuongQC
 * @module: string
 * @code: string
 * @val: string
 * @val_name: string
 * @where: string
 * return: string
 **/
function myGetSelectOptionsWithDbExt($module, $code, $val, $val_name = 'id', $where = '')
{
    global $db;
    $field = '';
    if (isset($code) && !empty($code)) {
        $field .= ',' . $code;
    }
    $sql = "SELECT id,name" . $field . " FROM " . strtolower($module) . " WHERE deleted=0 " . $where . " ORDER BY " . $code;
    $res = $db->query($sql);
    $html = '';
    while ($row = $db->fetchByAssoc($res)) {
        if ($row[$val_name] == $val || (is_array($val) && in_array($row[$val_name], $val))) $selected = 'selected="selected"';
        else $selected = '';
        $code2 = str_replace("@", "&nbsp;", str_pad($row[$code], 18, "@"));
        $html .= '<option name="' . $row['name'] . '" code="' . $row[$code] . '" ' . $selected . ' value="' . $row['id'] . '" >' . $code2 . '  ' . $row['name'] . '</option>';
    }
    return $html;
}

/**
 * Lấy danh sách tài khoản ngân hàng
 * @param string $val giá trị mặc định
 * @param string $where truy vấn mở rộng
 * @param string $type loại trả về HTML/ARRAY
 * @return string/array
 */
function myGetBankAccountList($val, $where = '', $type = 'HTML')
{
    global $db;
    $html = '';
    $arr = array();
    $sql = "
        SELECT ba.id,
            ba.name AS tentaikhoan,
            ba.account_number AS sotaikhoan,
            b.name AS nganhang,
            b.short_name AS tenviettat,
            ba.account_holder AS chutaikhoan
        FROM ec_bank_account ba
            LEFT JOIN ec_banks b ON ba.bank_id = b.id AND b.deleted = 0 
        WHERE ba.deleted = 0 AND ba.unfollow = 0 ";

    if (isset($where) && !empty($where)) {
        $sql .= $where;
    }
    $sql .= " ORDER BY CONVERT(tentaikhoan USING UTF8) COLLATE utf8_unicode_ci ";

    $res = $db->query($sql);
    while ($row = $db->fetchByAssoc($res)) {
        if ($row['id'] == $val || (is_array($val) && !empty($val) && in_array($row['id'], $val))) {
            $selected = 'selected="selected"';
            $is_selected = true;
        } else {
            $selected = '';
            $is_selected = false;
        }

        $sotk = str_replace("@", "&nbsp;", str_pad($row['sotaikhoan'], 20, "@"));
        $html .= '<option ' . $selected . ' value="' . $row['id'] . '" sotk="' . $row['sotaikhoan'] . '"';
        $html .= ' name="' . $row['tentaikhoan'] . '" chutk="' . $row['chutaikhoan'] . '" >' . $sotk . '  ' . $row['tentaikhoan'] . '</option>';
        $arr[] = array(
            'id' => $row['id'],
            'tentaikhoan'   => $row['tentaikhoan'],
            'sotaikhoan'    => $row['sotaikhoan'],
            'nganhang'      => $row['nganhang'],
            'tenviettat'    => $row['tenviettat'],
            'chutaikhoan'   => $row['chutaikhoan'],
            'selected'      => $is_selected
        );
    }

    if ($type == 'HTML')
        return $html;
    if ($type == 'ARRAY')
        return $arr;
}

/**
 * Lấy danh sách các tháng
 * @param string $val giá trị để thiết lập selected
 * @return string
 */
function myGetMonthList($val = '')
{
    $html = '';
    for ($i = 1; $i <= 12; $i++) {
        $selected = !empty($val) && $i == $val ? 'selected="selected"' : '';
        $html .= '<option ' . $selected . ' value="' . $i . '">' . $i . '</option>';
    }
    return $html;
}

/**
 * Lấy danh sách các năm
 * @param numeric $current_year năm hiện tại
 * @param numeric $number_year khoảng cách giữa các năm
 * @param string $val dùng để thiết lập selected
 * @return string
 */
function myGetYearList($current_year, $number_year = 3, $val = '')
{
    $html = '';
    for ($i = ($current_year - $number_year); $i <= ($current_year + $number_year); $i++) {
        $selected = !empty($val) && $i == $val ? 'selected="selected"' : '';
        $html .= '<option ' . $selected . ' value="' . $i . '">' . $i . '</option>';
    }
    return $html;
}

function myGetHourList($hour = '')
{
    $html = '';
    for ($i = 0; $i <= 23; $i++) {
        $hour_txt = str_pad($i, 2, '0', STR_PAD_LEFT);
        $selected = (!empty($hour) && $hour_txt == $hour) ? 'selected="selected"' : '';
        $html .= '<option ' . $selected . ' value="' . $hour_txt . '">' . $hour_txt . '</option>';
    }
    return $html;
}

function myGetMinuteList($minute = '')
{
    $html = '';
    for ($i = 0; $i <= 59; $i++) {
        $minute_txt = str_pad($i, 2, '0', STR_PAD_LEFT);
        $selected = (!empty($minute) && $minute_txt == $minute) ? 'selected="selected"' : '';
        $html .= '<option ' . $selected . ' value="' . $minute_txt . '">' . $minute_txt . '</option>';
    }
    return $html;
}

/**
 * Kiểm tra 1 trường có tồn tại
 * 
 * @param string $module tên phân hệ
 * @param string $field tên trường cần kiểm tra
 * @param string $field_value giá trị của trường cần kiểm tra
 * @param string $id dòng dữ liệu muốn kiểm tra
 * @return bool
 */
function myCheckValueExist($module, $fields = array(), $field_value = array(), $id)
{
    global $db;
    $rowcount = 0;
    $field_con = '';
    if (count($fields) > 0) {
        $i = 0;
        foreach ($fields as $field) {
            if (!isset($field_value[$i]) || is_null($field_value[$i]) || empty($field_value[$i])) continue;
            $field_con .= " AND " . $field . " = '" . $field_value[$i] . "'";
            $i++;
        }
    }

    $sql = "SELECT COUNT(id) FROM ". strtolower($module) ." WHERE id <> '$id' AND deleted = 0 " . $field_con;
    $rowcount = $db->getOne($sql);

    if ($rowcount > 0) return true;
    return false;
}

/**
 * Kiểm tra 2 trường cùng 1 lúc có tồn tại
 * @param string $module tên phân hệ
 * @param string $field trường cần kiểm tra
 * @param string $field_value giá trị của trường cần kiểm tra
 * @param string $field2 trường thứ 2 cần kiểm tra
 * @param string $field_value giá trị của trường thứ 2 cần kiểm tra
 * @param string $id dòng dữ liệu muốn kiểm tra
 * @return bool
 */
function myCheck2ValueExist($module, $field, $field_value, $field2, $field_value2, $id)
{
    global $db;
    $rowcount = 0;
    $sql = "SELECT COUNT(id) FROM " . strtolower($module) . "
				WHERE id <> '" . $id . "' 
				AND " . $field . " = '" . $field_value . "' 
				AND " . $field2 . " = '" . $field_value2 . "' 
				AND deleted = 0 ";
    $rowcount = $db->getOne($sql);
    if ($rowcount > 0)
        return true;
    return false;
}

/**
 * Đếm số dòng dữ liệu tả về
 * @param string $module tên phân hệ
 * @param string $where điều kiện truy vấn bổ sung
 * @return numeric
 */
function myGetRecordCount($module, $where = "")
{
    global $db;
    $total = 0;
    $sql = "SELECT COUNT(id) FROM " . strtolower($module) . " WHERE deleted=0 ";
    if (isset($where) && !empty($where))
        $sql .= $where;
    $total += $db->getOne($sql);
    return $total;
}

// Tự động phát sinh tên chứng từ
function myAutoGenerateName($module, $where = '', $num = 7)
{
    global $db;
    $total = 0;
    $sql = "SELECT COUNT(id) FROM " . strtolower($module);

    if (!empty($where)) {
        $sql .= " WHERE ";
        $sql .= $where;
    }

    $total += $db->getOne($sql);
    return str_pad(($total + 1), $num, '0', STR_PAD_LEFT);
}

// My utf8 convert
function myRemoveUnicodeChars($str)
{
    if (!$str) return false;
    $utf8 = [
        'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
        'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
        'D' => 'Đ',
        'd' => 'đ',
        'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
        'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
        'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
        'i' => 'í|ì|ỉ|ĩ|ị',
        'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
        'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
        'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
        'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
        'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        'y' => 'ý|ỳ|ỷ|ỹ|ỵ'
    ];
    foreach ($utf8 as $ascii => $uni) $str = preg_replace("/($uni)/i", $ascii, $str);
    return $str;
}

// Check string is unicode
function global_is_unicode($string)
{
    return preg_match('/[^\x20-\x7e]/', $string);
}

// Get the string between string
function myGetStringBetween($string, $start, $end)
{
    $string = " " . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return "";
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

// Lấy số tồn đầu kỳ
function getTheBeginningOfPeriod($sotk, $thang, $nam)
{
    global $db;
    $sodauky = 0;
    $thangtruoc = $thang == 1 ? 12 : str_pad($thang - 1, 2, '0', STR_PAD_LEFT);
    if ($thang == 1 && $nam == date('Y'))
        $namtruoc = $nam - 1;
    else if ($nam != date('Y'))
        $namtruoc = $nam;
    else
        $namtruoc = '';
    $sql = "SELECT (duno_" . $thangtruoc . "-duco_" . $thangtruoc . ")
				FROM ec_chitiettaikhoan" . $namtruoc . "
				WHERE deleted=0 AND sotaikhoan='" . $sotk . "' ";
    $sodauky += $db->getOne($sql);
    return $sodauky;
}

// Get flight class
function myGetFlightClass($price, $dep, $arv, $dep_date, $SI_Name)
{
    if (!$price || !$dep || !$arv || !$dep_date || !$SI_Name) {
        return 'disconnect';
    }

    $HangVe = '';
    ini_set("soap.wsdl_cache_enabled", false);
    // connect webservice
    try {
        $client = new SoapClient("http://210.86.239.110/sabrebypass.asmx?wsdl", array('exceptions' => true));
    } catch (SoapFault $e) {
        return 'disconnect';
    }
    $str = '';
    // add params to webservice funtion
    $params = array(
        'str_by_pass' => strtoupper('PRICESFQ' . $dep . $arv . date('dM', strtotime($dep_date))),
        'SI_Name' => $SI_Name
    );
    $result = $client->Sabre_By_Pass2($params);
    $html = trim($result->Sabre_By_Pass2Result);
    // save and read xml
    $dom = new DOMDocument();
    @$dom->loadXML($html);
    $str = $dom->saveXML();
    $xml = simplexml_load_string($str);
    $price = unformat_number($price);
    foreach ($xml->Object as $xmlObj) {
        $DonGia = unformat_number($xmlObj->DonGia);
        if ($DonGia == $price) {
            $HangVe = trim($xmlObj->HangVe);
            return $HangVe;
        }
    }
    return $HangVe;
}

// Get number seat of flight class
function myGetNumberSeatFlight($dep, $arv, $dep_date, $arv_date, $fl_number, $fl_class, $SI_Name)
{
    if (!$dep || !$arv || !$dep_date || !$arv_date || !$fl_number || !$fl_class || !$SI_Name) {
        return 'disconnect';
    }

    ini_set("soap.wsdl_cache_enabled", false);
    try {
        $client = new SoapClient("http://210.86.239.110/sabrebypass.asmx?wsdl", array('exceptions' => true));
    } catch (SoapFault $e) {
        return 'disconnect';
    }

    $sabre_str = strtoupper('FLIGHT1' . date('dM', strtotime($dep_date)) . trim($dep) . trim($arv));
    $params = array(
        'str_by_pass' => $sabre_str,
        'SI_Name' => $SI_Name
    );
    $result = $client->Sabre_By_Pass2($params);
    $html = trim($result->Sabre_By_Pass2Result);
    $first_occur = strpos($html, '@');
    $str_to_clean = trim(substr($html, 0, $first_occur));
    $clean_str = trim(str_replace($str_to_clean, '', $html));

    $explode_arr = explode('@', $clean_str);
    $flight_number = strtoupper(strlen(trim($fl_number)) > 6 ? str_replace(array('*', ' '), '', trim($fl_number)) : trim($fl_number)); // số hiệu chuyến bay
    $route_time = strtoupper(trim($dep) . trim($arv) . ' ' . date('Hi', strtotime($dep_date)) . ' ' . date('Hi', strtotime($arv_date))) . ' '; // vd:DADSGN 1030 1130 (lưu ý có khoảng trắng phía cuối của chuỗi)
    $seat_class = strtoupper(trim($fl_class)); // hạng ghế
    $number_seat = 0;

    foreach ($explode_arr as $flight) {
        if (trim($flight) != '' && strpos(trim($flight), $flight_number) !== false) {
            $flight = str_replace($flight_number, '', trim($flight));
            $flight = str_replace($route_time, '', trim($flight));
            $flight_class = explode(' ', trim($flight));
            foreach ($flight_class as $flight_seat) {
                if (trim($flight_seat) != '' && strpos(trim($flight_seat), $seat_class) !== false) {
                    $number_seat = (int)substr(trim($flight_seat), 1, strlen($flight_seat));
                    return $number_seat;
                }
            }
        }
    }
    return $number_seat;
}

// Get code book assign
function myGetCodeBookAssign($aircode, $code_book_str, $selected_val = '')
{
    global $db, $current_user, $app_list_strings;

    $sql = "SELECT code_book, code_password
				FROM ec_codebookassign 
				WHERE deleted=0
				AND aircode='" . $aircode . "'
				AND assigned_user_id='" . $current_user->id . "'
				ORDER BY date_entered ";

    $res = $db->query($sql);
    $html = '';

    // if ($db->getRowCount($res) > 0) {
    if ($db->countRows($res) > 0) {
        while ($row = $db->fetchByAssoc($res)) {
            $selected = $row['code_book'] == $selected_val ? 'selected="selected"' : '';
            $html .= '<option ' . $selected . ' pwd="' . $row['code_password'] . '" value="' . $row['code_book'] . '">' . $row['code_book'] . '</option>';
        }
    } else {
        $code_books = myGetStringBetween($code_book_str, '[' . $aircode . ']', '[/' . $aircode . ']');
        $code_book_arr = explode(';', $code_books);
        foreach ($code_book_arr as $row) {
            $code_book = explode('|', $row);
            $html .= '<option pwd="' . $code_book[1] . '" value="' . $code_book[0] . '">' . $code_book[0] . '</option>';
        }
    }

    return $html;
}

// Get supplier remaining credit
function myGetSupplierRemainingCredit($airline, $agent_id, $agent_pwd, $format = 'json')
{
    $api_key = 'N830B51ZEA3Gzc6343R9T6Wn24C8iiBU51t2ppeJ';
    $url = 'http://s1.vietnamairlines.bid/index.php/apiv1/api/get_remaining_credit/format/' . $format;

    $postdata = array(
        'airline' => $airline,
        'agent_id' => $agent_id,
        'agent_pwd' => $agent_pwd,
    );

    $curl_handle = curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, $url);
    curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array('X-API-KEY: ' . $api_key));
    curl_setopt($curl_handle, CURLOPT_POST, true);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_handle, CURLOPT_POSTFIELDS, http_build_query($postdata));
    $data = curl_exec($curl_handle);
    curl_close($curl_handle);
    $result = json_decode($data, true);
    return $result;
}

/**
 * Recheck booking's flight
 * @param string $aircode
 * @param string $pnr
 * @param string $fullName
 * @param string $flightNo
 * @param int $timeout time in seconds
 * @param int $useProxy 0=Disabled | 1=Enabled
 * @param int $email
 * @return mixed
 */
function myRecheckFlight($aircode, $pnr, $fullName, $flightNo, $timeout = 30, $useProxy = 1, $email = '')
{
    $api_key = 'N830B51ZEA3Gzc6343R9T6Wn24C8iiBU51t2ppeJ';
    $url = 'http://s1.vietnamairlines.bid/index.php/apiv1/api/recheck_flight';
    $url .= '/use_proxy/' . $useProxy;
    $url .= '/aircode/' . $aircode;
    $url .= '/flight_no/' . $flightNo;
    $url .= '/pnr/' . $pnr;
    $url .= '/full_name/' . $fullName;
    if (!empty($email)) {
        $url .= '/email/' . $email;
    }

    $curl_handle = curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, $url);
    curl_setopt($curl_handle, CURLOPT_ENCODING, 'gzip');
    curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array('X-API-KEY: ' . $api_key));
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($curl_handle, CURLOPT_TIMEOUT, $timeout);
    $data = curl_exec($curl_handle);
    curl_close($curl_handle);
    $result = json_decode($data, true);

    return $result;
}

// Get airline info
function myGetAirlineInfo($airline_code, $search_by = 'FULL', $case_sensitive = 1, $format = 'array')
{
    $api_key = 'N830B51ZEA3Gzc6343R9T6Wn24C8iiBU51t2ppeJ';
    $url = 'http://api.vemaybaynamphuong.com/index.php/apiv1/api/airline_search/format/json/term/' . $airline_code . '/case_sensitive/' . $case_sensitive . '/search_by/' . $search_by;

    $curl_handle = curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, $url);
    curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array('X-API-KEY: ' . $api_key));
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, 0);
    $data = curl_exec($curl_handle);
    curl_close($curl_handle);
    $result = '';
    if ($format == 'array') $result = json_decode($data, true);
    else if ($format == 'json') $result = $data;
    return $result;
}

function myGetAirlineInfo2($airline_code, $search_by, $case_sensitive = 1, $format = 'array') {
    $search_by_allow = array('CODE', 'NAME', 'FULL');
    $search_by = $search_by && in_array($search_by, $search_by_allow) ? $search_by : 'FULL';
    $case_sensitive = $case_sensitive ? $case_sensitive : 0; // default is case insensitive
    $q = $airline_code;
    $q = myRemoveUnicodeChars($q);
    if (!$case_sensitive) {
        $q = strtolower($q);
    }
    if (!$q || strlen($q) < 2) {
        return array();
    }

    $xmlstring = file_get_contents('custom/airlines.xml');
    $xml = simplexml_load_string($xmlstring);
    $json = json_encode($xml);
    $json_arr = json_decode($json, true);

    $data = array();
    foreach ($json_arr['RECORD'] as $json_val) {

        $code_str = $json_val['code'];
        if (!$case_sensitive) {
            $code_str = strtolower($code_str);
        }

        $name_str = $json_val['name'];
        if (!$case_sensitive) {
            $name_str = strtolower($name_str);
        }

        if (strpos($code_str, $q) !== false && $search_by == 'CODE') {
            $full_str = $json_val['name'] . ', ' . $json_val['country'] . ' (' . $json_val['code'] . ')';
            array_push($data, array(
                'label' => $full_str,
                'value' => $full_str,
                'code' => $json_val['code'],
                'country' => $json_val['country'],
                'name' => $json_val['name']
            ));
        } else if (strpos($name_str, $q) !== false && $search_by == 'NAME') {
            $full_str = $json_val['name'] . ', ' . $json_val['country'] . ' (' . $json_val['code'] . ')';
            array_push($data, array(
                'label' => $full_str,
                'value' => $full_str,
                'code' => $json_val['code'],
                'country' => $json_val['country'],
                'name' => $json_val['name']
            ));
        } else if ((strpos($code_str, $q) !== false || strpos($name_str, $q) !== false) && $search_by == 'FULL') {
            $full_str = $json_val['name'] . ', ' . $json_val['country'] . ' (' . $json_val['code'] . ')';
            array_push($data, array(
                'label' => $full_str,
                'value' => $full_str,
                'code' => $json_val['code'],
                'country' => $json_val['country'],
                'name' => $json_val['name']
            ));
        }
        if (count($data) > 30) {
            break;
        }
    }

    if ($format == 'array') $result = array('data' => $data);
    else if ($format == 'json') $result = json_encode($data);
    return $result;
}

function myGetAirportInfo2($airport_code, $case_sensitive = 1, $format = 'array')
{
    $case_sensitive = $case_sensitive ? $case_sensitive : 0; // default is case insensitive
    $q = $airport_code;
    $q = myRemoveUnicodeChars($q);
    if (!$case_sensitive) {
        $q = strtolower($q);
    }
    if (!$q || strlen($q) < 3) {
        return array();
    }

    $json = file_get_contents('custom/airports.json');
    $json_arr = json_decode($json, true);

    $data = array();
    foreach ($json_arr as $json_val) {

        $str = $json_val['value'];
        if (!$case_sensitive) {
            $str = strtolower($str);
        }
        if (strpos($str, $q) !== false) {
            array_push($data, array(
                'label' => $json_val['value'],
                'value' => $json_val['value'],
                'code' => substr($json_val['value'], (strpos($json_val['value'], '(') + 1), 3),
                'country' => substr($json_val['value'], (strpos($json_val['value'], ',') + 2), 2),
                'name' => substr($json_val['value'], 0, strpos($json_val['value'], ','))
            ));
        }
        if (count($data) > 30) {
            break;
        }
    }
    if ($data) {
        return array('data' => $data);
    }
}


// Get duration info
function myGetDurationInfo($time_departure, $time_arrival)
{
    $duration   = strtotime($time_arrival) - strtotime($time_departure);

    if ($duration > 0) {
        $hours      = floor($duration / 3600);
        $minutes    = floor(($duration % 3600) / 60);

        if ($hours > 0 && $minutes == 0) {
            $formatted_duration = $hours . 'h';
        } elseif ($hours == 0 && $minutes > 0) {
            $formatted_duration = $minutes . 'm';
        } else {
            $formatted_duration = $hours . 'h ' . $minutes . 'm';
        }
    }

    return $formatted_duration;
}

/**
 * Returns user/system preference for number grouping separator character(default ",") and the decimal separator
 *(default ".").  Special case: when num_grp_sep is ".", it will return NULL as the num_grp_sep.
 * @return array Two element array, first item is num_grp_sep, 2nd item is dec_sep
 */
function my_get_number_separators($reset_sep = false)
{
    global $current_user, $sugar_config;

    static $dec_sep = null;
    static $num_grp_sep = null;

    // This is typically only used during unit-tests
    // TODO: refactor this. unit tests should not have static dependencies
    if ($reset_sep) {
        $dec_sep = $num_grp_sep = null;
    }

    if ($dec_sep == null) {
        $dec_sep = $sugar_config['default_decimal_seperator'];
        if (!empty($current_user->id)) {
            $user_dec_sep = $current_user->getPreference('dec_sep');
            $dec_sep = (empty($user_dec_sep) ? $sugar_config['default_decimal_seperator'] : $user_dec_sep);
        }
    }

    if ($num_grp_sep == null) {
        $num_grp_sep = $sugar_config['default_number_grouping_seperator'];
        if (!empty($current_user->id)) {
            $user_num_grp_sep = $current_user->getPreference('num_grp_sep');
            $num_grp_sep = (empty($user_num_grp_sep)
                ? $sugar_config['default_number_grouping_seperator'] : $user_num_grp_sep);
        }
    }

    return array($num_grp_sep, $dec_sep);
}


// Get department info
function myGetDepartmentInfo($department_id)
{
    global $db;
    $info = [];
    if ($department_id) {
        $sql = "SELECT sg.id,
                    sg.name,
                    sg.noninheritable,
                    sg.use_auto_book,
                    sg.use_mail_confirm,
                    sg.use_mail_eticket,
                    sg.com_name,
                    sg.com_taxcode,
                    sg.com_address,
                    sg.com_address2,
                    sg.com_address3,
                    sg.com_address4,
                    sg.com_website,
                    sg.com_website2,
                    sg.com_website3,
                    sg.com_email,
                    sg.com_email2,
                    sg.com_email3,
                    sg.com_phone,
                    sg.com_phone2,
                    sg.com_phone3,
                    sg.com_hotline1,
                    sg.com_hotline2,
                    sg.com_hotline3,
                    sg.notify_fromname,
                    sg.notify_fromaddress,
                    sg.mail_smtpserver,
                    sg.mail_smtpport,
                    sg.mail_smtpssl,
                    sg.company_logo,
                    sg.mail_smtpuser,
                    sg.mail_smtppass,
                    sg.promo_link,
                    sg.color,
                    sg.delivery_fee,
                    sg.payment_guide_link,
                    sg.location,
                    sg.local_email,
                    sg.code_book,
                    sg.com_email_bcc
                FROM securitygroups sg
                WHERE sg.id = '$department_id' AND sg.deleted = 0";
        $res = $db->query($sql);
        $info = $db->fetchByAssoc($res);
    }
    return $info;
}

// Get working process count
function myGetWorkingProcessCount($parent_type, $parent_id, $field = '')
{
    global $db;
    $recheck_count = 0;
    $sql = "SELECT SUM(IFNULL($field, 0)) 
            FROM ec_working_process 
            WHERE parent_id = '$parent_id' AND parent_type = '$parent_type' AND deleted = 0";
    if ($field != '') {
        $sql .= " AND $field IS NOT NULL ";
    }
    $recheck_count += $db->getOne($sql);
    return $recheck_count;
}

// Hàm kiểm tra sự tồn tại của WorkingProcess
function isWorkingProcessExisting($parent_type, $parent_id, $field = '')
{
    // Ngoại trừ xuất hđ đầu ra
    global $db;

    $sql    = "SELECT COUNT(*) as count FROM ec_working_process WHERE parent_id = '$parent_id' AND parent_type = '$parent_type' AND $field = 1 AND deleted = 0";
    $result = $db->query($sql);
    $row    = $db->fetchByAssoc($result);
    return $row['count'] > 0;
}

// Remove working process exist
function myRemoveWorkingProcess($parent_type, $parent_id, $field = '')
{
    global $db;
    $sql = "UPDATE ec_working_process
			SET deleted = 1
			WHERE parent_id = '" . $parent_id . "'
				AND parent_type = '" . $parent_type . "'
				AND deleted = 0";
    if ($field != '') {
        $sql .= " AND " . $field . " IS NOT NULL ";
    }
    $db->query($sql);
}

// Create working process
function myCreateWorkingProcess($parent_type, $parent_id, $parent_name, $description, $assigned_user_id, $field)
{
    global $sugar_config, $current_user;
    if (!empty($field)) {
        $work = new EC_Working_Process();
        $work->id = '';
        $work->name = $parent_name;
        $work->parent_type = $parent_type;
        $work->parent_id = $parent_id;
        $work->description = trim($description);
        $work->assigned_user_id = $assigned_user_id;
        $work->$field = 1;
        $work->save();
        
        if(empty($work->id)) {
            // // SEND TELE WARNING SAVE KPI FAILED
            // $messages = "- Domain: <b>" . $sugar_config['host_name'] . "</b>\n" .
            // "- User: <b>" . $current_user->user_name . "</b>\n" .
            // "<pre>[WARNING]: myCreateWorkingProcess FAILED ".$description.".</pre>";
            // $content = html_entity_decode($messages, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            // sendTelegramWarningSystem(
            //     json_encode(array(
            //         'text' => $content,
            //         'parse_mode' => 'HTML',
            //         'reply_markup' => array(
            //             'inline_keyboard' => array(
            //                 array(
            //                     array(
            //                         'text' => 'Redirect url',
            //                         'url' => 'https://' . $sugar_config['host_name'] . '/index.php?module='.$parent_name.'&action=DetailView&record=' . $parent_id,
            //                     ),
            //                 ),
            //             ),
            //         ),
            //     ), JSON_UNESCAPED_UNICODE),
            // );

            $link = Mattermost::markdownLink("https://" . $sugar_config['host_name'] . "/index.php?module=$parent_name&action=DetailView&record=$parent_id", "Redirect url");
            $message = Mattermost::$line_separation;
            $message .= Mattermost::markdownHeading("[WARNING]: Function myCreateWorkingProcess() failed");
            $message .= "\n- Domain: **" . $sugar_config['host_name'] . "**";
            $message .= "\n- User: **$current_user->user_name**";
            $message .= "\n- Description: **$description**";
            $message .= "\n$link";
            Mattermost::sendMessage($sugar_config['mattermost']['channel_id_logs'] ?? '', $message);
        }
    }
}

// Get total record of module by day
function myGetTotalRecordByDay($module, $str = '0', $len = 4)
{
    global $db;
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $total = 0;
    $date_entered = date('Y-m-d H:i:s', strtotime(date('Y-m-d 16:59:59')) - 86400); // giờ sugarcrm lệch 7h so với giờ server
    $sql = "SELECT COUNT(id) + 1 FROM " . strtolower($module) . " WHERE date_entered > '" . $date_entered . "' ";
    $total += $db->getOne($sql);
    return date('ymd') . str_pad($total, $len, $str, STR_PAD_LEFT);
}

// Get location list by string
function myGetLocationListByString($str, $select_val = '')
{
    $html = '<option value=""></option>';
    if (!empty($str)) {
        // 1 location
        if (strpos($str, ',') === false) {
            $item = explode('|', $str);
            $selected = ($select_val == trim($item[0])) ? 'selected="selected"' : '';
            $html .= '<option ' . $selected . ' value="' . trim($item[0]) . '">' . trim($item[1]) . '</option>';
        } else {
            $items = explode(',', $str);
            foreach ($items as $item) {
                $itemval = explode('|', $item);
                $selected = ($select_val == trim($itemval[0])) ? 'selected="selected"' : '';
                $html .= '<option ' . $selected . ' value="' . trim($itemval[0]) . '">' . trim($itemval[1]) . '</option>';
            }
        }
    }
    return $html;
}

// Get location list by deparment ID
function myGetLocationListByDepID($department_id, $select_val = '')
{
    global $db, $current_user;
    $html = '<option value=""></option>';
    $sql = "SELECT id, name
            FROM ec_location
            WHERE deleted = 0
            AND is_display = 0 ";

    // Accountant request show all location
    //    if(!is_admin($current_user)) {
    //        if ($department_id == '48840c01-3a4f-c430-f703-56f32c7cd8a4') // Travelpass
    //            $sql .= " AND company_id IN ('8df43570-09de-d2b3-b2fd-506eca7522f7', '" . $department_id . "') ";
    //        else
    //            $sql .= " AND company_id = '" . $department_id . "' ";
    //    }

    $sql .= "ORDER BY date_entered ";
    $res = $db->query($sql);
    while ($row = $db->fetchByAssoc($res)) {
        $selected = ($row['id'] == $select_val) ? 'selected="selected"' : '';
        $html .= '<option ' . $selected . ' value="' . $row['id'] . '">' . $row['name'] . '</option>';
    }

    return $html;
}

function myGetAllDepByCurrentUser()
{
    global $current_user;
    $dep_arr = is_admin($current_user) ? SecurityGroup::getAllSecurityGroups() : SecurityGroup::getUserSecurityGroups($current_user->id);
    $dep_arr = array_values($dep_arr);
    return $dep_arr;
}

function myMakeHtmlOption($rows, $select_val = '')
{
    $html = '';
    foreach ($rows as $row) {
        $selected = ($row['id'] == $select_val) ? 'selected="selected"' : '';
        $html .= '<option ' . $selected . ' value="' . $row['id'] . '">' . $row['name'] . '</option>';
    }
    return $html;
}

function myIsBookingPaid($booking_id)
{
    global $db;
    if (isset($booking_id) && !empty($booking_id)) {
        $sql = "SELECT b.is_paid
					FROM ec_flight_bookings b
					WHERE b.deleted = 0 AND b.id = '" . $booking_id . "' ";
        $is_paid = $db->getOne($sql);
        if ($is_paid) {
            return true;
        }
    }

    return false;
}

/**
 * Gateway send USSD command
 * @param $arr
 * ex: array(
 * array(
 * "port"=>0,
 * "command"=>"*101#"
 * ),
 * array(
 * "port"=>3,
 * "command"=>"*101#"
 * )
 * )
 * @param int $times_request delay time to check port
 * @param int $total_port
 * @return array
 */
function myGatewaySendUSSD($arr, $times_request = 10, $total_port = 8)
{
    global $app_list_strings;
    $arr_api_clear = array();
    $arr_api_cmd = array();
    for ($i = 0; $i < $total_port; $i++) {
        $arr_api_clear["Index" . $i] = "on";
        if (isset($arr[$i]["port"])) {

            $arr_api_cmd['Index' . $arr[$i]["port"]] = 'on';
            $arr_api_cmd['MsgInfo' . $arr[$i]["port"]] = $arr[$i]["command"];
            $arr_api_cmd['USSDInfo' . $arr[$i]["port"]] = '';
        }
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $app_list_strings['system_config_list']['sms_gateway_url'] . "/goform/WIAUSSDClearReply");
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.95 Safari/537.36');
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, $app_list_strings['system_config_list']['sms_gateway_usr'] . ':' . $app_list_strings['system_config_list']['sms_gateway_pwd']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arr_api_clear));
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_setopt($ch, CURLOPT_URL, $app_list_strings['system_config_list']['sms_gateway_url'] . "/goform/WIAUSSDStopAutoRefresh");
    curl_exec($ch);
    curl_setopt($ch, CURLOPT_URL, $app_list_strings['system_config_list']['sms_gateway_url'] . "/goform/WIAUSSDSend");
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arr_api_cmd));
    curl_exec($ch);

    $match = array();
    $results = array();
    $message = array();
    $check = false;
    $count_error = 0;
    while (!$check) {
        $count_error += 1;
        curl_setopt($ch, CURLOPT_URL, $app_list_strings['system_config_list']['sms_gateway_url'] . "/MsgUSSD.htm");
        $res = curl_exec($ch);
        preg_match_all("'case(.*?)break'si", $res, $match);
        $check = true;
        for ($i = 0; $i < $total_port; $i++) {
            if (isset($arr[$i]["port"])) {
                if (preg_match("/Waiting/", $match[1][$arr[$i]["port"]], $t) || preg_match("/End/", $match[1][$arr[$i]["port"]], $t) || preg_match("/registered/", $match[1][$arr[$i]["port"]], $t)) {
                    sleep(1);
                    $check = false;
                    break;
                }
            }
        }
        if ($count_error >= $times_request) {
            curl_close($ch);
            $results[] = array(
                "status" => 401,
                "message" => "Bad request, please request again after 15 seconds"
            );
            return $results;
            break;
        }
    }
    curl_close($ch);

    for ($i = 0; $i < $total_port; $i++) {
        if (isset($arr[$i]["port"])) {
            preg_match_all("'\"(.*?)\"'siU", $match[1][$arr[$i]["port"]], $s);
            if (preg_match("/not/", $s[1][0], $t)) {
                $message[] = array(
                    "sim_error" => 1,
                    "port" => $arr[$i]["port"],
                    "content" => $s[1][0]
                );
            } else {
                $message[] = array(
                    "sim_error" => 0,
                    "port" => $arr[$i]["port"],
                    "content" => $s[1][0]
                );
            }
        }
    }

    $results["status"] = 200;
    $results["message"] = $message;
    return $results;
}

/**
 * Get age of birthday with current time
 * @param $dob yyyy-mm-dd
 * @param $current_time yyyy-mm-dd
 * @return false|int|string
 */
function myGetAge($dob, $current_time)
{
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $dob = strtotime($dob);
    $current_time = strtotime(!empty($current_time) ? $current_time : date('Y-m-d'));

    $age_years = date('Y', $current_time) - date('Y', $dob);
    $age_months = date('m', $current_time) - date('m', $dob);
    $age_days = date('d', $current_time) - date('d', $dob);

    if ($age_days < 0) {
        $days_in_month = date('t', $current_time);
        $age_months--;
        $age_days = $days_in_month + $age_days;
    }

    if ($age_months < 0) {
        $age_years--;
        $age_months = 12 + $age_months;
    }

    return array(
        'years' => $age_years,
        'months' => $age_months,
        'days' => $age_days
    );
}

/**
 * Send SMS
 * @param $sms_port
 * @param $send_to
 * @param $message
 * @return bool
 */
function mySendSMS($sms_port, $send_to, $message, $sms_encode = 0)
{
    global $app_list_strings;
    $sms_gateway_url = $app_list_strings['system_config_list']['sms_gateway_url'];
    $username = $app_list_strings['system_config_list']['sms_gateway_usr'];
    $password = $app_list_strings['system_config_list']['sms_gateway_pwd'];
    $refer = $sms_gateway_url . '/enWIASendMsg.htm';
    $url = $sms_gateway_url . '/goform/WIAMsgSend';
    $post_data = array(
        'CurrentPort' => $sms_port,
        'Encoding' => $sms_encode, // 0=GSM | 1=UCS2
        'Addressee' => $send_to,
        'MsgInfo' => $message,
        'ok' => 'Send'
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, $refer);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $sms_result = curl_exec($ch);
    curl_close($ch);

    return false;
}

/**
 * Remove none-word character
 * @param $str
 * @return null|string|string[]
 */
function myRemoveNoneWordChar($str)
{
    return preg_replace('/\W/', '', $str);
}

/**
 * Remove none-digit character
 * @param $str
 * @return null|string|string[]
 */
function myRemoveNoneDigitChar($str)
{
    return preg_replace('/\D/', '', $str);
}


/**
 * Get days between 2 specific date but not over a month
 * @param $from_date
 * @param $to_date
 * @return int
 */
function myGetDayBetween($from_date, $to_date)
{
    $fdate = new DateTime(date('d-m-Y', strtotime($from_date)));
    $tdate = new DateTime(date('d-m-Y', strtotime($to_date)));
    $result = $tdate->diff($fdate);
    return $result->d;
}

/**
 * Get hours between 2 specific hour but not over a day
 * @param $from_date
 * @param $to_date
 * @return int
 */
function myGetHourBetween($from_date, $to_date)
{
    $fdate = new DateTime(date('d-m-Y H:i:s', strtotime($from_date)));
    $tdate = new DateTime(date('d-m-Y H:i:s', strtotime($to_date)));
    $result = $tdate->diff($fdate);
    return $result->h;
}


// Lấy tất cả các ngày chủ nhật trong tháng và các ngày lễ
// chỉ tính tới ngày hiện tại
function getExcludeDays($month_year)
{
    $month = date('m', strtotime('01-' . $month_year));

    // các ngày lễ
    $holidays = array(
        // '01' => array(1),
        'default' => array(),
    );

    // tính các ngày chủ nhật
    $fdate = '01-' . $month_year;
    if ($month_year == date('m-Y')) {
        $tdate = date('d');
    } else {
        $tdate = date('t', strtotime('01-' . $month_year));
    }

    $first_sunday = 7 - date('N', strtotime($fdate)) + 1;

    for ($i = $first_sunday; $i <= $tdate; $i += 7) {
        $sundays[] = $i;
    }

    $exclude_days = array_unique(array_merge($sundays, (array_key_exists($month, $holidays) ? $holidays[$month] : $holidays['default'])), SORT_REGULAR);


    return $exclude_days;
}

/**
 * Calculate number of days between dates
 * @param string from_date
 * @param string to_date
 * @return int total_day
 */
function myCalculateDayBetweenDates($from_date, $to_date)
{
    $start = strtotime($from_date);
    $end = strtotime($to_date);
    return ceil(abs($end - $start) / 86400);
}

/**
 * creating between two date
 * @param string since
 * @param string until
 * @param string step
 * @param string date format
 * @return array
 * @author Ali OYGUR <alioygur@gmail.com>
 */
function myGetDateRange($first, $last, $step = '+1 day', $format = 'd-m-Y')
{
    $dates = array();
    $current = strtotime($first);
    $last = strtotime($last);

    while ($current <= $last) {

        $dates[] = date($format, $current);
        $current = strtotime($step, $current);
    }

    return $dates;
}

/*
 * Tính số dư công nợ phải trả
 * < 30tr thì báo lên group kế toán
 * chatid: -1311652274
*/
function calculateSupplierBalance($supplier_id)
{
    global $db;
    $sql = "
        SELECT 
            SUM(IFNULL(tmp.debt_amount, 0)) - SUM(IFNULL(tmp.pay_amount, 0)) AS total_debt
		FROM (
            -- START TERM
            SELECT SUM(IFNULL(p.dunodau, 0) - IFNULL(p.ducodau, 0)) AS debt_amount
                 , 0 AS pay_amount
            FROM ec_chitiettaikhoan" . date('Y') . " p
            WHERE p.deleted = 0
            AND p.parent_type = 'Accounts'
            AND p.sotaikhoan IN ('144', '331')
            AND p.parent_id IS NOT NULL
            AND p.parent_id = '" . $supplier_id . "'

            -- BOOKING DETAILS
            UNION
            SELECT SUM(IFNULL(d.total_bought_price, 0)) AS debt_amount
                 , 0 AS pay_amount
            FROM ec_booking_details d
            LEFT JOIN ec_flight_bookings p ON d.booking_id = p.id AND p.deleted = 0
            WHERE d.deleted = 0
            AND p.booking_status IN ('7', '8')
            AND p.is_ticket_exported = 1
            AND p.date_ticket_issue >= '" . date('Y-01-01') . "'
            AND p.date_ticket_issue <= '" . date('Y-m-d') . "'
            AND d.total_bought_price > 0
            AND d.supplier_id IS NOT NULL
            AND d.supplier_id = '" . $supplier_id . "'

            -- BOOKING PAXS OUTBOUND
            UNION
            SELECT SUM(IFNULL(d.luggage_purchase, 0)) AS debt_amount
                 , 0 AS pay_amount
            FROM ec_booking_passengers d
            LEFT JOIN ec_flight_bookings p ON d.booking_id = p.id AND p.deleted = 0
            WHERE d.deleted = 0
            AND p.booking_status IN ('7', '8')
            AND p.is_ticket_exported = 1
            AND p.date_ticket_issue >= '" . date('Y-01-01') . "'
            AND p.date_ticket_issue <= '" . date('Y-m-d') . "'
            AND d.luggage_price > 0
            AND d.luggage_purchase > 0
            AND d.supplier_id IS NOT NULL
            AND d.add_type IS NULL
            AND d.supplier_id = '" . $supplier_id . "'

            -- BOOKING PAXS INBOUND
            UNION
            SELECT SUM(IFNULL(d.luggage_purchase_inbound, 0)) AS debt_amount
                 , 0 AS pay_amount
            FROM ec_booking_passengers d
            LEFT JOIN ec_flight_bookings p ON d.booking_id = p.id AND p.deleted = 0
            WHERE d.deleted = 0
            AND p.booking_status IN ('7', '8')
            AND p.is_ticket_exported = 1
            AND p.date_ticket_issue >= '" . date('Y-01-01') . "'
            AND p.date_ticket_issue <= '" . date('Y-m-d') . "'
            AND d.luggage_price_inbound > 0
            AND d.luggage_purchase_inbound > 0
            AND d.supplier_inbound_id IS NOT NULL
            AND d.add_type IS NULL
            AND d.supplier_inbound_id = '" . $supplier_id . "'

            -- RECEIPT
            UNION
            SELECT SUM(IFNULL(p.amount, 0)) AS debt_amount
                 , 0 AS pay_amount
            FROM ec_receipt_voucher p
            WHERE p.deleted = 0
            AND p.loai_thu = '9'
            AND p.account_id_c IS NOT NULL
            AND p.ngaychungtu >= '" . date('Y-01-01') . "'
            AND p.ngaychungtu <= '" . date('Y-m-d') . "'
            AND p.account_id_c = '" . $supplier_id . "'

            -- SUPPLIER 1
            UNION
            SELECT SUM(IFNULL(p.bought_amount, 0)) AS debt_amount
                 , 0 AS pay_amount
            FROM ec_receipt_voucher p
            WHERE p.deleted = 0
            AND p.loai_thu IN ('4', '5')
            AND p.supplier_id IS NOT NULL
            AND p.bought_amount IS NOT NULL
            AND p.ngaychungtu >= '" . date('Y-01-01') . "'
            AND p.ngaychungtu <= '" . date('Y-m-d') . "'
            AND p.supplier_id = '" . $supplier_id . "'

            -- SUPPLIER 2
            UNION
            SELECT SUM(IFNULL(p.bought_amount2, 0)) AS debt_amount
                 , 0 AS pay_amount
            FROM ec_receipt_voucher p
            WHERE p.deleted = 0
            AND p.loai_thu IN ('4', '5')
            AND p.supplier2_id IS NOT NULL
            AND p.bought_amount2 IS NOT NULL
            AND p.ngaychungtu >= '" . date('Y-01-01') . "'
            AND p.ngaychungtu <= '" . date('Y-m-d') . "'
            AND p.supplier2_id = '" . $supplier_id . "'

            -- SUPPLIER 3
            UNION
            SELECT SUM(IFNULL(p.bought_amount3, 0)) AS debt_amount
                 , 0 AS pay_amount
            FROM ec_receipt_voucher p
            WHERE p.deleted = 0
            AND p.loai_thu IN ('4', '5')
            AND p.supplier3_id IS NOT NULL
            AND p.bought_amount3 IS NOT NULL
            AND p.ngaychungtu >= '" . date('Y-01-01') . "'
            AND p.ngaychungtu <= '" . date('Y-m-d') . "'
            AND p.supplier3_id = '" . $supplier_id . "'

            -- TICKET REFUND
            UNION
            SELECT -SUM(IFNULL(c.sotienhang, 0)) AS debt_amount
                 , 0 AS pay_amount
            FROM ec_chitiethoanve c
            LEFT JOIN ec_hoanve p ON c.hoanve_id = p.id AND p.deleted = 0
            WHERE c.deleted = 0
            AND c.dahoan = 1
            AND p.tinhtrang = '1'
            AND p.ngayhachtoan >= '" . date('Y-01-01') . "'
            AND p.ngayhachtoan <= '" . date('Y-m-d') . "'
            AND c.sotienhang > 0
            AND c.nhacc_id IS NOT NULL
            AND c.nhacc_id = '" . $supplier_id . "'

            -- PAYMENT VOUCHER
            UNION
            SELECT 0 AS debt_amount
                 , SUM(IFNULL(p.amount, 0)) AS pay_amount
            FROM ec_payment_voucher p
            WHERE p.deleted = 0
            AND p.pv_status = '3'
            AND p.ngaychungtu >= '" . date('Y-01-01') . "'
            AND p.ngaychungtu <= '" . date('Y-m-d') . "'
            AND p.supplier_id IS NOT NULL
            AND p.supplier_id = '" . $supplier_id . "'
		) AS tmp";
    return $db->getOne($sql);
}

function getEmailFromUser($user_id)
{
    global $db;
    $sql = "SELECT CONCAT(u.last_name,' ',u.first_name) as fullname, email_address, primary_address, reply_to_address, if(primary_address = 1, u.password_email,'') as primary_password 
            FROM email_addr_bean_rel b 
            INNER JOIN email_addresses e ON e.id = b.email_address_id AND e.deleted = 0 
            INNER JOIN users u ON u.id = b.bean_id AND u.deleted = 0
            WHERE b.deleted = 0 AND b.bean_module = 'Users' AND u.id = '" . $user_id . "' AND primary_address = 1";

    $res = $db->query($sql);
    $email = array();
    while ($row = $db->fetchByAssoc($res)) {
        if (!strpos($row["email_address"], '@timchuyenbay.net')) {
            $email["primary_fullname"] = $row["fullname"];
            $email["primary_email"]    = $row["email_address"];
            $email["primary_pwd"]      = $row["primary_password"];
        }
        if (strpos($row["email_address"], '@timchuyenbay.net')) {
            $email["second_fullname"]  = $row["fullname"];
            $email["second_email"]     = $row["email_address"];
        }
    }
    return $email;
}

function mySendMail($user_id, $to_email, $to_name, $subject, $body) {
    try {
        $send_ok = true;

        // SUGAR SENDMAIL
        require_once('include/SugarPHPMailer.php');
        $mail = new SugarPHPMailer();
        $department_info = myGetDepartmentInfo("48840c01-3a4f-c430-f703-56f32c7cd8a4"); // Security travelpass 

        if (isset($department_info['mail_smtpserver']) && isset($department_info['mail_smtpport'])) {
            // get email from user
            $user_email = getEmailFromUser($user_id);
            $from_mail = !empty($user_email['second_email']) ? $user_email['second_email'] : $user_email['primary_email'];

            // Load from department settings
            $mail->Host         = $department_info['mail_smtpserver'];
            $mail->Port         = $department_info['mail_smtpport'];
            $mail->SMTPAuth     = TRUE;
            $mail->SMTPSecure   = $department_info['mail_smtpssl'] == 1 ? 'ssl' : 'tls';
            $mail->SMTPKeepAlive = false;
            // $mail->SMTPDebug     = 4;
            $mail->Mailer       = "smtp";
            $mail->Timeout      = 300;
            $mail->Username     = $user_email['primary_email'];
            $mail->Password     = $user_email['primary_pwd'];
            $mail->ContentType  = "text/html";
            $mail->From         = $from_mail;
            $mail->FromName     = ucwords(myRemoveUnicodeChars($user_email['primary_fullname']));
            $mail->Subject      = $subject;
            $mail->Body         = from_html(wordwrap('&lt;html&gt;&lt;body&gt;' . $body . '&lt;/body&gt;&lt;/html&gt;', 996));
            $mail->AddAddress($to_email, $to_name);

            // Add Bcc for current user 
            $mail->AddReplyTo($from_mail);
            $mail->AddReplyTo("info@timchuyenbay.com");
            $mail->AddBCC("info@timchuyenbay.com");

            // Add Bcc, ReplyTo for user admin
            // if(isset($department_info['com_email_bcc']) && trim($department_info['com_email_bcc']) != ''){
            //     $bcc_arr = explode(';', $department_info['com_email_bcc']);
            //     foreach($bcc_arr as $bcc_add){ 
            //         $mail->AddReplyTo($bcc_add); 
            //         $mail->AddBCC($bcc_add);
            //     }
            // }

        } else {
            // Load system settings
            require_once('modules/Administration/Administration.php');
            $admin = new Administration();
            $admin->retrieveSettings();
            if ($admin->settings['mail_sendtype'] == "SMTP") {
                $mail->Host = $admin->settings['mail_smtpserver'];
                $mail->Port = $admin->settings['mail_smtpport'];
                if ($admin->settings['mail_smtpauth_req']) {
                    $mail->SMTPAuth = TRUE;
                    $mail->SMTPSecure = $admin->settings['mail_smtpssl'] == 1 ? 'ssl' : 'tls';
                    $mail->Username = $admin->settings['mail_smtpuser'];
                    $mail->Password = $admin->settings['mail_smtppass'];
                }
                $mail->Mailer   = "smtp";
                $mail->SMTPKeepAlive = false;
            } else {
                $mail->Mailer = 'sendmail';
            }

            $mail->From     = $admin->settings['notify_fromaddress'];
            $mail->FromName = $admin->settings['notify_fromname'];

            $mail->ContentType = "text/html";
            $mail->Subject = $subject;
            $mail->Body = from_html(wordwrap('&lt;html&gt;&lt;body&gt;' . $body . '&lt;/body&gt;&lt;/html&gt;', 996));
            $mail->AddAddress($to_email, $to_name);
        }

        if (!$mail->send()) {
            $send_ok = false;
            $GLOBALS['log']->fatal(json_encode([
                "Mailer error" => $mail->ErrorInfo,
                "Mailer full SMTP log" => $mail->fullSmtpLog,
                "Mailer Host" => $mail->Host,
                "Mailer Port" => $mail->Port,
                "Mailer Username" => $mail->Username,
                "Mailer Password" => $mail->Password,
            ]));
        }

        return $send_ok;
    }
    catch (RuntimeException $e) {
        $GLOBALS['log']->fatal("Runtime Exception: {$e->getMessage()} when calling mySendMail() in custom_utils.php");
        return false;
    }
    catch (Exception $e) {
        $GLOBALS['log']->fatal("Exception: {$e->getMessage()} when calling mySendMail() in custom_utils.php");
        return false;
    }
    catch (Throwable $th) {
        $GLOBALS['log']->fatal("Throwable: {$th->getMessage()} when calling mySendMail() in custom_utils.php");
        return false;
    }
}

// Lấy thông tin hành lý
function generateLuggage($booking_date, $airline, $ticket_class, $pass_type, $luggage_index = NULL, $auto_gen_select = 0, $luggage_price = 0, $is_new_edited = 0)
{
    global $app_list_strings, $current_user;
    $luggage_arr = '';

    // Nếu không có thông tin đặt vé thì lấy ngày hiện tại
    if (empty($booking_date)) {
        $booking_date = date('d-m-Y');
    }

    $arr_replace = [
        'VNA' => 'vietnamair',
        'VNP' => 'pacificair',
        'VJA' => 'vietjet',
        'BBA' => 'bambooair',
        'VTA' => 'vietravel',

        'VU' => 'vietravel',
        'QH' => 'bambooair',
        'VN' => 'vietnamair',
        'VJ' => 'vietjet',
        'BL' => 'pacificair',
        'JQ' => 'jetstar',
        '3K' => 'jetstar',
    ];

    // Phân loại theo hãng bay
    if ($airline == 'VNA' || $airline == 'VN') {
        // array các hạng vé A / P / G
        $arrayClassEconomy = ['Economy (EP)-A', 'Economy (EP)-P', 'Economy (EP)-G', 'A', 'P', 'G'];
        // array các hạng vé Business
        $arrayClassBusiness = ['Business (BF)-C', 'Business (BF)-J', 'Business (BC)-D', 'C', 'J', 'D'];

        // Trẻ sơ sinh
        if ($pass_type == 2) {
            $pass_ticket_class = '_infant';
            $luggage_arr = $app_list_strings[$arr_replace[$airline] . $pass_ticket_class . '_luggage_price_list'];
            // Hạng vé A / P / G của trẻ sơ sinh không có hành lý xách tay
            if (stripos($ticket_class, "Eco Super Lite") !== false || in_array($ticket_class, $arrayClassEconomy)) {
                unset($luggage_arr[0]);
            }
        }
        // Hạng vé Business
        elseif (in_array($ticket_class, $arrayClassBusiness)) {
            $pass_ticket_class = '_business';
            $luggage_arr = $app_list_strings[$arr_replace[$airline] . $pass_ticket_class . '_luggage_price_list'];
        }
        // Hạng vé A / P / G thì hành lý như bên dưới
        elseif (stripos($ticket_class, "Eco Super Lite") !== false || in_array($ticket_class, $arrayClassEconomy)) {
            $luggage_arr = [
                '1' => 'Không có hành lý ký gửi ',
                '0' => '1 kiện 23kg (0 VND/Khách)',
                '2' => '1 kiện 32kg (0 VND/khách)',
                '350000'    => '1 kiện 23kg (350.000 VND/Khách)',
                '700000'    => '2 kiện 23kg (700.000 VND/ Khách)',
                '1050000'   => '3 kiện 23kg (1.050.000 VND/ Khách)',
                '1400000'   => '4 kiện 23kg (1.400.000 VND/ Khách)',
                '1750000'   => '5 kiện 23kg (1.750.000 VND/ Khách)',
                '190000'    => '1 kiện 10kg (190.000 VND/ Khách)',
                '220000'    => '1 kiện 10kg (220.000 VND/ Khách)',
                '440000'    => '2 kiện 10kg (440.000 VND/ Khách)',
                '660000'    => '3 kiện 10kg (660.000 VND/ Khách)'
            ];
        }
        // Từ ngày 14-12-2022, đổi giá hành lý mới cho 1 / 2 / 3 kiện 10kg
        else if (strtotime($booking_date) >= strtotime('2022-12-14 00:00:00')) {
            $luggage_arr = $app_list_strings[$arr_replace[$airline] . '_luggage_price_list2'];
        }
    }
    else if ($airline == 'VJA' || $airline == 'VJ') {
        // Sau ngày 21-11-2022 đổi sang hành lý mới
        if (strtotime($booking_date) >= strtotime('2022-11-21 00:00:00')) {
            if ($is_new_edited || !is_null($luggage_index)) {
                $luggage_arr = $app_list_strings[$arr_replace[$airline] . '_luggage_price_list2'];
                $luggage_price = $luggage_index;
            } else {
                $luggage_arr = $app_list_strings[$arr_replace[$airline] . '_luggage_price_list2'];
            }
        } else {
            // Dùng đổi thông tin để thêm hành lý hoặc trong màn hình edit của booking cũ
            if ($is_new_edited || !is_null($luggage_index)) {
                $luggage_arr = $app_list_strings['new_' . $arr_replace[$airline] . '_luggage_price_list'];
                $luggage_price = $luggage_index;
            }
        }
    }
    else if ($airline == 'BBA' || $airline == 'QH') {
        if ($pass_type == '2') {
            $pass_ticket_class = '_infant';
            $luggage_arr = $app_list_strings[$arr_replace[$airline] . $pass_ticket_class . '_luggage_price_list'];
        } else if (!empty($ticket_class)) {
            $pass_ticket_class = '_' . strtolower(str_replace(' ', '', $ticket_class));
            $luggage_list = $app_list_strings[$arr_replace[$airline] . $pass_ticket_class . '_luggage_price_list'];
            if (empty($luggage_list)) $luggage_list = array();
            $luggage_arr = $luggage_list + $app_list_strings['bambooair_advanced_luggage_price_list'];
        }
    }
    else if ($airline == 'VNP' || $airline == 'BL') {
        if ($pass_type == '2') {
            $pass_ticket_class = '_infant';
            $luggage_arr = $app_list_strings[$arr_replace[$airline] . $pass_ticket_class . '_luggage_price_list'];
        }
    }
    else if ($airline == 'VTA' || $airline == 'VU') {
        // Từ ngày 06-01-2023 thì lấy thông tin hành lý mới lần 2
        if (strtotime($booking_date) >= strtotime('2023-01-06')) {
            $luggage_arr = $app_list_strings['new_' . $arr_replace[$airline] . '_luggage_price_list2'];
        }
        // Booking đặt từ ngày 11-08-2022 thì lấy thông tin hành lý mới
        else if (strtotime($booking_date) >= strtotime('2022-08-11')) {
            $luggage_arr = $app_list_strings['new_' . $arr_replace[$airline] . '_luggage_price_list1'];
        }
    }
    else {
        // INTER
        $luggage_arr = $app_list_strings['inter_luggage_price_list'];
    }

    // Nếu vẫn chưa có thông tin hành lý thì lấy mặc định
    if (empty($luggage_arr)) {
        $luggage_arr = $app_list_strings[$arr_replace[$airline] . '_luggage_price_list'];
    }

    // Tự tạo ra các option hành lý
    if ($auto_gen_select) {
        $luggage_arr = get_select_options_with_id($luggage_arr, (int)$luggage_price);
    }

    return $luggage_arr;
}

// Hàm trả về các index nếu hành lý có giá giống nhau
function populateLuggageIndex($airline, $booking_date, $luggage_idx = 0, $auto_gen_select = 0)
{
    global $app_list_strings;
    $arr_replace = array(
        'VNA' => 'vietnamair',
        'JET' => 'jetstar',
        'VJA' => 'vietjet',
        'BBA' => 'bambooair',
        'VJ' => 'vietjet',
        'BL' => 'jetstar',
        'JQ' => 'jetstar',
        '3K' => 'jetstar',
        'VNP' => 'pacificair',
        'VTA' => 'vietravelair'
    );
    $luggage_idx_arr = array();
    if ($airline == 'VJA' || $airline == 'VJ') {
        // sau ngày 21-11-2022 đổi sang hành lý mới
        if (strtotime($booking_date) >= strtotime('2022-11-21 00:00:00')) {
            $luggage_idx_arr = $app_list_strings[$arr_replace[$airline] . '_index_price_list2'];
        } else {
            $luggage_idx_arr = $app_list_strings[$arr_replace[$airline] . '_index_price_list'];
        }
    }

    if ($auto_gen_select) {
        $luggage_idx_arr = get_select_options_with_id($luggage_idx_arr, $luggage_idx);
    }

    return $luggage_idx_arr;
}

// Admin hệ thống và quản lý
function isAllowedUser()
{
    global $current_user, $db;

    if (is_admin($current_user)) {
        return true;
    }

    // $sql = '
    //     SELECT IF(COUNT(id) > 0, 1, 0)
    //     FROM acl_roles_users
    //     WHERE deleted = 0 
    //     AND user_id = "' . $current_user->id . '" 
    //     AND role_id = "222d9e8c-a54c-d7b8-8f75-567e493d6ea3"
    // ';

    $sql = '
        SELECT IF(COUNT(id) > 0, 1, 0)
        FROM acl_roles_users
        WHERE user_id = "' . $current_user->id . '" 
            AND role_id = "' . $GLOBALS['app_list_strings']['roles_users']['QUANLY'] . '"
            AND deleted = 0
    ';

    $is_exist = $db->getOne($sql);
    if ($is_exist) {
        return true;
    }

    return false;
}

// Check quyền cho admin hệ thống, QL và kế toán
function isManagerUser($user_id)
{
    global $db, $current_user;

    if (is_admin($current_user)) {
        return 1;
    }

    // $sql = 'SELECT COUNT(id) 
    //         FROM acl_roles_users 
    //         WHERE deleted = 0 
    //         AND role_id IN (
    //             "222d9e8c-a54c-d7b8-8f75-567e493d6ea3",
    //             "c4ae12df-787f-5a30-5612-509b1346b649"
    //         )
    //         AND user_id = "' . $user_id . '"';

    $sql = 'SELECT COUNT(id) 
            FROM acl_roles_users 
            WHERE user_id = "' . $user_id . '"
                AND role_id IN (
                    "' . $GLOBALS['app_list_strings']['roles_users']['QUANLY'] . '",
                    "' . $GLOBALS['app_list_strings']['roles_users']['KETOAN'] . '"
                )
                AND deleted = 0';
    $is_manager = $db->getOne($sql);

    if ($is_manager) return 1;
    return 0;
}

// Là nhân viên có role Telesale
function isTelesaleUser($user_id)
{
    global $db;

    $sql = 'SELECT COUNT(id) FROM acl_roles_users WHERE user_id = "' . $user_id . '" AND role_id = "34beb2a2-5ee7-f001-2496-68ca264d1d3f" AND deleted = 0';
    $is_telesale = $db->getOne($sql);
    return ($is_telesale) ? 1 : 0;
}


// Bỏ các khoảng trắng
function replaceAllSpacesToSingleSpace($string)
{
    return trim(preg_replace('!\s+!', ' ', $string));
}

// Get info user
function myGetUser($uid = '')
{
    global $db, $sugar_config;

    $info   = array();
    $sql    = "SELECT u.id, CONCAT(IFNULL(u.last_name,'') ,' ', IFNULL(u.first_name,'')) as full_name, 
    u.user_name, department_id, sg.name AS dep_name, email_address, phone_work, phone_mobile, address_street, employee_type 
    FROM users u 
    LEFT JOIN securitygroups sg ON sg.id= u.department_id
    LEFT JOIN email_addr_bean_rel b ON u.id = b.bean_id AND b.deleted = 0 AND b.primary_address = 1
    LEFT JOIN email_addresses e ON e.id = b.email_address_id AND e.deleted = 0 
    WHERE u.deleted = 0 AND status ='Active' AND  employee_status = 'Active' AND domain IS NULL AND u.title <> 'Bot'";

    if (!empty($uid))
        $sql .= " AND u.id = '" . $uid . "'";

    $res = $db->query($sql, true, 'ERROR: Cannot get value');
    while ($row = $db->fetchByAssoc($res)) {
        array_push($info, array(
            "id" => $row['id'],
            "user_name" => $row['user_name'],
            "full_name" => $row['full_name'],
            "department_id" => $row['department_id'],
            "dep_name" => $row['dep_name'],
            "email" => $row['email_address'],
            "phone_work"  => $row['phone_work'],
            "phone_mobile"  => $row['phone_mobile'],
            "address_street"  => $row['address_street'],
            "employee_type" => $row['employee_type']
        ));
    }
    return $info;
}

function isBot($id)
{
    $u = new User();
    $u->retrieve($id);

    if (strtoupper($u->title) == 'BOT') return true;
    return false;
}

// CẬP NHẬT STATUS_BOOKING TRONG EC_CUSTOMER KHI STATUS_BOOKING ĐÓ THAY ĐỔI
function UpdateInforBookingOfCustomer($booking_id)
{
    global $db;
    $bk = new EC_Flight_Bookings;
    $bk->retrieve($booking_id);

    // UPDATE
    // get booking_list hiện có của KH đó
    $sql_get_booking_info = '
        SELECT c.info_data
        FROM ec_customer c
        WHERE c.phone = "' . $bk->phone . '"';
    $current_booking = $db->getOne($sql_get_booking_info);

    // convert booking_list hiện có thành array
    $current_booking_array     = json_decode(html_entity_decode($current_booking), true);

    if (isset($current_booking_array[$bk->id])) {
        $current_booking_array[$bk->id]['booking_status']         = $bk->booking_status;
    }

    // Cập nhật field info_data
    $updated_booking_data  = json_encode($current_booking_array);
    $sql_update_booking = '
            UPDATE ec_customer c
            SET info_data = \'' . $updated_booking_data . '\'
            WHERE c.phone = "' . $bk->phone . '"';

    $db->query($sql_update_booking);
}

// Get customer type
function getCustomerType($phone)
{
    if (!$phone || is_null($phone) || empty($phone)) return "NEW";

    global $db;
    $sql = 'SELECT id, info_data
		   FROM ec_customer
		   WHERE phone = "' . $phone . '"
           AND deleted = 0';

    $res = $db->query($sql);

    $current_date = date("Y-m-d");
    // $recent_date = $recent_date_2 = '';
    $count_booking = array(
        'done' => array('14' => 0, '30' => 0, '90' => 0, '365' => 0),
        'all' => array('14' => 0, '30' => 0, '90' => 0, '365' => 0)
    );
    $count_ticket = array('14' => 0, '30' => 0, '90' => 0, '365' => 0);
    $journey = array('14' => array(), '30' => array(), '90' => array(), '365' => array());
    $revenue = array('14' => 0, '30' => 0, '90' => 0, '365' => 0);
    $is_return = 0;

    while ($row = $db->fetchByAssoc($res)) {

        if (array_key_exists('info_data', $row)) {
            $data = json_decode(html_entity_decode($row['info_data']), true);
            $count = count($data);
            $i = 0;
            foreach ($data as $k => $v) {
                $booking_date = date('Y-m-d', strtotime($v['booking_date']));
                $time = floor((strtotime($current_date) - strtotime($booking_date)) / (24 * 60 * 60));
                $done = in_array($v['booking_status'], array('3', '7', '8')) ? true : false;
                $amount = (float)$v['customer_price_revenue'];

                // if ($i == $count - 1) $recent_date = $booking_date;
                // elseif ($i == $count - 2) $recent_date_2 = $booking_date;

                if ($done) {
                    if ($i != $count - 1) $is_return++;

                    if ($time <= 14) {
                        $count_booking['done']['14']++;
                        $count_ticket['14'] += $v['booking_quantity'];
                        $revenue['14'] += $amount;
                    }
                    if ($time <= 30) {
                        $count_booking['done']['30']++;
                        $count_ticket['30'] += $v['booking_quantity'];
                        $revenue['30'] += $amount;
                    }
                    if ($time <= 90) {
                        $count_booking['done']['90']++;
                        $count_ticket['90'] += $v['booking_quantity'];
                        $revenue['90'] += $amount;
                    }
                    if ($time <= 365) {
                        $count_booking['done']['365']++;
                        $count_ticket['365'] += $v['booking_quantity'];
                        $revenue['365'] += $amount;
                    }
                }
                if ($time <= 14) {
                    $count_booking['all']['14']++;
                    // $journey['14'][$v['journey']] = 1; //$journey['14']['SGN - HAN'] = 1
                    if (empty($journey['14']) || !array_key_exists($v['journey'], $journey['14'])) {
                        $journey['14'][] = $v['journey']; //$journey['14']['SGN - HAN'] = 1
                    }
                }
                if ($time <= 30) {
                    $count_booking['all']['30']++;
                    // $journey['30'][$v['journey']] = 1;
                }
                if ($time <= 90) {
                    $count_booking['all']['90']++;
                    // $journey['90'][$v['journey']] = 1;
                }
                if ($time <= 365) {
                    $count_booking['all']['365']++;
                    // $journey['365'][$v['journey']] = 1;
                }

                $i++;
            }

            // Count journey
            $journey['14'] = count($journey['14']);
            // $journey['30'] = count($journey['30']);
            // $journey['90'] = count($journey['90']);
            // $journey['365'] = count($journey['365']);
        } else {
            $journey['14'] = 0;
            // $journey['30'] = 0;
            // $journey['90'] = 0;
            // $journey['365'] = 0;
        }
    }

    if ($count_booking['done']['30'] > 0 && $count_ticket['30'] > 8 && $revenue['30'] > 2000000) return "VIP";
    elseif ($count_booking['done']['90'] >= 30 && $revenue['90'] > 6000000) return "LOYAL";
    elseif ($is_return > 0) return "RETURN";
    elseif ($count_booking['all']['14'] - $count_booking['done']['14'] > 8) return "DANGER";
    elseif ($count_booking['all']['14'] - $count_booking['done']['14'] > 3 && $journey['14'] > 3) return "WARNING";
    elseif ($count_booking['all']['90'] - $count_booking['done']['90'] > 3) return "IGNORE";
    else return "NEW";
}

// Duration khoảng thời gian customer đặt booking  - Dùng cho nút check
function getDurationDateBetweenBookingLastest($phone)
{
    global $db;
    $sql = 'SELECT info_data
		   FROM ec_customer
		   WHERE phone = "' . trim($phone) . '"';
    $res = $db->query($sql);

    $count_paymented = 0;
    $current_date    = date('Y-m-d');
    while ($row = $db->fetchByAssoc($res)) {
        $data  = json_decode(html_entity_decode($row['info_data']), true);
        $last_element = end($data);

        $booking_date_lastest = empty($last_element['booking_date']) ? false : date('Y-m-d', strtotime('+7 hours', strtotime($last_element['booking_date'])));

        foreach ($data as $id => $v) {
            if ($v['booking_status'] == 8 || $v['booking_status'] == 7 || $v['booking_status'] == 3) {
                $count_paymented += 1;
            }
        }
    }

    if ($booking_date_lastest == false) {
        $duration_date = '- Ngày đặt booking gần nhất chưa xác định!';
    } else {
        $date_before   = strtotime($current_date) - strtotime($booking_date_lastest);
        $duration_date = '- Khách hàng đã đặt booking <b class="color-red"> ' . abs($date_before / (60 * 60) / 24) . '</b> ngày trước.';
    }

    return array(
        'booking_date_lastest' => $booking_date_lastest,
        'duration_date' => $duration_date,
        'count_paymented' => $count_paymented
    );
}

// Print varlue to browser
function pr($data)
{
    echo "<style>
        pre {
            color: #343a40;
            padding: 1rem;
            overflow: auto;
            font-size: 100%;
            line-height: 1.45;
            background-color: #e2e1ea;
            border-radius: 3px;
            -moz-tab-size: 4;
            -o-tab-size: 4;
            tab-size: 4;
            -webkit-hyphens: none;
            -moz-hyphens: none;
            -ms-hyphens: none;
            hyphens: none;
            display:block;
        }
    </style>";
    $track =  debug_backtrace();
    $file_called = $track[0]['file'] . ' at line: ' . $track[0]['line'];

    if (is_string($data)) {
        echo "<pre>" . $file_called . '<br/> ' . $data . "</pre>";
    } else {
        echo "<pre>" . $file_called . '<br/> ';
        print_r($data);
        echo "</pre>";
    }
}

function global_test_input($data)
{
    if (is_null($data)) return '';
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}


function custom_get_sip_number($key = '')
{
    $arr = [
        /************************  IT  ************************/
        'dedf3602-b1ec-97da-abbe-6656e656f5eb' => ['user' => '001', 'password' => '0Cm1Wc$bQd%ZTK5tnGGZ'], // Admin
        '168889bb-54c2-59c7-8b3f-649102530d3c' => ['user' => '010', 'password' => 'JgXTH7xYX?A4qLzK%vAD'], // Admin
        '1' => ['user' => '012', 'password' => 'QAnTigDjZ8WSw%4finb1'], // Admin
        '622ecf27-f729-7187-7e27-6520e0dab882' => ['user' => '222', 'password' => '246357@89'], // Admin

        /************************  BOOKER  ************************/
        // Nguyễn Ngọc Lan Phương
        '976a054f-370f-7945-7776-5d1c28f96e8a' => ['user' => '101', 'password' => '%22ZAQfS1!cb2UIZtB%H'],
        // Nguyễn Thị Đông
        '2d7dfd04-2e91-8302-0c91-56d69b4cc08e' => ['user' => '102', 'password' => 'Tw.gyg5JSErHR40XKVku'],
        // Đoàn Thị Kim Ly
        'a2dae06b-ca09-7b35-b1a2-5ccb9c7cab3d' => ['user' => '103', 'password' => 'hUJPhU6wF9NIvGb!s$K0'],
        // Trần Minh Tuấn
        'da25400e-a030-389c-4228-5c233d8cd04e' => ['user' => '104', 'password' => 'L6U%a9^%Ggzkb9u4ryIx'],
        // Trần Như Điền
        // '7c20e013-b0d6-e1f3-b113-53deed58f0a2' => ['user' => '105', 'password' => '1uQH?M6tD6GgrXW3*IA^'],

        // Mai Thị Anh Đào
        'e692a4e4-b402-4ffa-ce78-68c904aa4086' => ['user' => '105', 'password' => '1uQH?M6tD6GgrXW3*IA^'],

        // Trương Mỹ Nhân
        '9a9ba7fd-bb1a-e132-b5fc-5bee7dcada12' => ['user' => '106', 'password' => 'ct0*LiQHAo1B5?s.C$Zq'],
        // Lê Tín Nghĩa
        'ebc40fa1-8878-1a86-000d-5b6949a87e11' => ['user' => '107', 'password' => 'C1UtQnCWTpUmH8C5?9wE'],
        // Nguyễn Duy Đăng
        // 'cb0ad38e-3524-deea-220f-62f20cec08d5' => ['user' => '108', 'password' => 'bxzL$q.R?m^q1$eVju%n'],

        // Trịnh Thị Kim Ly
        // '2037c237-a846-7dc4-0b76-68c7699f5a03' => ['user' => '108', 'password' => 'bxzL$q.R?m^q1$eVju%n'],

        // Nguyễn Thị Kim Loan
        'f299609a-28c0-c30e-d661-68ccb9aec236' => ['user' => '108', 'password' => 'bxzL$q.R?m^q1$eVju%n'],
        
        // Nguyễn Lộc Danh
        '4ef24994-3d8e-ff0d-2784-599d0b3e56e1' => ['user' => '109', 'password' => 'rRTMeTJDrHJG7skLtnzd'],
        // Đỗ Nhật
        '245134a3-0382-7578-601a-6790ab9bb6b3' => ['user' => '789', 'password' => 't5scZL2Gnpuvc1JNYQYW'],

        /************************  KẾ TOÁN  ************************/
        // Đỗ Thị Kim Ngân
        '37cd4853-721c-9808-af64-5600c8835d03' => ['user' => '120', 'password' => 'epqUwwnKzvfoW*Gmmn1k'],
        // Nguyễn Trang Đài
        'b4ff32c8-8a1e-0648-b20d-63437ab44554' => ['user' => '121', 'password' => 'gMDB5Gn8tyvg1emav5cb'],
        // Nhân Thanh Chung
        'd61ac0c1-91b3-0dc8-049a-518b21d2deb9' => ['user' => '122', 'password' => 'e5C2FUk3^VbqCBH47Fq1'],

        // Booker test
        '493ad5e5-ffea-a84f-96d7-6577fed623d6' => ['user' => '130', 'password' => '24635789'], // Booker


        /************************  LAPTOP  ************************/
        // Phạm Chiến Thắng
        '61b537e5-6bc5-77e5-1102-5ff3dc1e40ee' => ['user' => '203', 'password' => '9$K4V2.8ofNf^jKGoQg4'],


        /************************  ĐẶC BIỆT  ************************/
        // trangbtq
        '72ece22c-cb25-8e30-9dea-56f2201cd359' => ['user' => '123', 'password' => 'tESRN16LC5z*jqcBumN%'],
        // soinau
        '9ba5c5a0-a402-02f4-76d3-53ba0481ce45' => ['user' => '124', 'password' => 'po*HUpMmx8.nLPAjj6Vb'],
        // thu
        'b5523dbd-b9a7-67c0-77b5-533e6ece89b1' => ['user' => '125', 'password' => 'E*UX8bbm8oSyj?jzQySj'],
        // pandapo
        '4f4d7a13-4171-9b7d-251c-64dd8f9885e4' => ['user' => '888', 'password' => '8sfJMj0hDWWPbvtcDg!e'],

        // Tiên TĐ
        'c57196c6-e211-9856-43d5-6695498f39ae' => ['user' => '998', 'password' => 'Bhq*B1rWSZ%n!dFEBJ$k'],
        // BinhLD
        '6eb3570d-ee8d-d834-c016-6846ed8c8811' => ['user' => '996', 'password' => 'oH.1JxtenKcIbVVnz7N0'],

    ];

    if (strlen($key) == 3) {
        foreach ($arr as $k => $v) {
            if ($v['user'] == $key) {
                return $k;
            }
        }
    } else if (!empty($key) && isset($arr[$key])) return $arr[$key]['user'];
    else if (empty($key)) return $arr;
    return '';
}

function getNameGroupCalls($sip = "")
{
    $name = array();

    $arr_group = array(
        '<span class="badge bg-primary">Booker</span>' => array('101', '102', '103', '104', '106', '107', '109', '201'),
        '<span class="badge bg-warning text-dark">Kế toán</span>' => array('120', '121', '122', '123', '124', '125'),
        '<span class="badge bg-danger">Laptop</span>' => array('201', '202', '203'),
        '<span class="badge bg-dark">IT</span>' => array('010', '012', '130'),
        '<span class="badge bg-info">Telesale</span>' => array('108', '105'),
    );

    foreach ($arr_group as $name_group => $arr_sip) {
        if (in_array(trim($sip), $arr_sip)) {
            $name[] = $name_group;
        }
    }

    if (count($name) == 0) {
        return "";
    } elseif (count($name) == 1) {
        return $name[0];
    } else {
        return implode('&ensp;', $name);
    }
}

// Format phone number
function formatPhoneNumber($phoneNumber)
{
    // Loại bỏ mọi ký tự không phải là số
    $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

    // Kiểm tra xem số điện thoại có đúng 10 chữ số không
    if (strlen($phoneNumber) == 10) {
        // Chia thành 3 phần: 03x, xxx, xxxx
        $part1 = substr($phoneNumber, 0, 3);
        $part2 = substr($phoneNumber, 3, 3);
        $part3 = substr($phoneNumber, 6, 4);

        // Kết hợp các phần với dấu cách
        $formattedPhoneNumber = $part1 . ' ' . $part2 . ' ' . $part3;

        return $formattedPhoneNumber;
    } else if (strlen($phoneNumber) == 11) {
        // Chia thành 3 phần: 03x, xxx0, xxxx
        $part1 = substr($phoneNumber, 0, 3);
        $part2 = substr($phoneNumber, 3, 4);
        $part3 = substr($phoneNumber, 7, 4);

        // Kết hợp các phần với dấu cách
        $formattedPhoneNumber = $part1 . ' ' . $part2 . ' ' . $part3;

        return $formattedPhoneNumber;
    } else {
        return $phoneNumber;
    }
}

// Format duration 
function secondsToTimeFormat($seconds)
{
    $hours      = floor($seconds / 3600);
    $minutes    = floor(($seconds % 3600) / 60);
    $seconds    = $seconds % 60;

    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
}

// ========= ONLINE - OFFLINE ===========
// ======================================
function read_file_logs_online($user_id)
{
    $file_name = 'secure_sessions/check_online_logs/' . str_replace('-', '_', $user_id) . '.json';

    if (file_exists($file_name)) {
        $myfile = file_get_contents($file_name);
        return $myfile;
    }

    return null;
}

function write_file_logs_online($json, $user_id)
{
    if (empty($json)) return false;

    $file_name = 'secure_sessions/check_online_logs/' . str_replace('-', '_', $user_id) . '.json';

    $myfile = fopen($file_name, "w") or die("Error something !!!");

    fwrite($myfile, $json);
    fclose($myfile);
}

function content_log($current_user_id, $time, $busy = 0)
{
    if (is_null($current_user_id) || empty($current_user_id) || is_null($time) || empty($time)) return false;

    $row = array(
        'agent' => custom_get_sip_number($current_user_id),
        'current_user_id' => $current_user_id,
        'last_time' => $time,
        'busy' => $busy,
    );

    write_file_logs_online(json_encode($row), $current_user_id);

    return true;
}

// GHI FILE LOGS BEHAVIOR ONLINE
function write_file_logs_behavior($json)
{
    if (empty($json)) return false;

    $year = date('Y');
    $month = str_pad(date('m'), 2, "0", STR_PAD_LEFT);
    $file_name = "secure_sessions/behavior_user_logs/$year/$month/" . str_replace('-', '_', date('d-m-Y') . '_log');

    // Check if the file exists
    if (!file_exists($file_name)) {
        $dir_name = dirname($file_name);
        if (!is_dir($dir_name)) {
            mkdir($dir_name, 0777, true);
        }
        touch($file_name);
    }


    $myfile = fopen($file_name, "a") or die("Error something !!!");
    fwrite($myfile, $json);
    fclose($myfile);
}

// CONTENT LOGS BEHAVIOR ONLINE
function content_logs_behavior($current_user_id, $time, $name_user = '', $url = '', $e_target = '')
{
    if (is_null($current_user_id) || empty($current_user_id) || is_null($time) || empty($time)) return false;

    $row = '[' . $time . '][' . $name_user . '][' . $e_target . ']: ' . $url . PHP_EOL;
    write_file_logs_behavior($row);

    echo 200;
    return true;
}

// GET WEBSITE LINK - CREATEDBY
function get_server_name($created_by = '')
{
    global $db;

    $arr = [
        'dc22131a-795a-6cd3-2caa-52d40d3b5622', // bookingvj
        '557d4a5b-27ce-5cb1-4531-5800ab9ed31d', // timcbcom
        '2b2c93b3-e916-113c-29bc-5b4c6de75db4' // timcbvn
    ];
    if (!in_array($created_by, $arr)) return 'timchuyenbay.com';

    // Query từ db - another
    $sql = "SELECT last_name FROM users WHERE id = '$created_by' AND deleted = 0 LIMIT 1";
    $res = $db->query($sql);

    while ($row = $db->fetchByAssoc($res)) {
        return $row['last_name'];
    }
    return '';
}

// Function to get the client ip address
function get_ip_address_from_client()
{
    $white_list_ip = ['127.0.0.1', '::1'];
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if (getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if (getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if (getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if (getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
    else if (getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    if (in_array($_SERVER['REMOTE_ADDR'], $white_list_ip)) {
        $ipaddress = '127.0.0.1';
    }
    return $ipaddress;
}

// Detect Browser
function get_browser_name($user_agent)
{
    $t = strtolower($user_agent);
    $t = " " . $t;
    if (strpos($t, 'opera') || strpos($t, 'opr/')) return 'Opera';
    elseif (strpos($t, 'edge')) return 'Edge';
    elseif (strpos($t, 'chrome')) return 'Chrome';
    elseif (strpos($t, 'safari')) return 'Safari';
    elseif (strpos($t, 'firefox')) return 'Firefox';
    elseif (strpos($t, 'brave')) return 'Brave';
    elseif (strpos($t, 'msie') || strpos($t, 'trident/7')) return 'Internet Explorer';
    return 'Unkown';
}

// RANDOM NGANLUONG_CODE
function get_payment_link()
{
    $length = 10;
    $characters = 'qwertyuiopasdfghjklzxcvbnm0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ@!';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    $randomString = $randomString . substr(time(), 4);
    return $randomString;
}

function update_field_booking($id, $field, $value, $datatype = 'string') {
	if(is_null($id) || is_null($field) || is_null($value) || empty($id) || empty($field) || empty($value)) return false;
	global $db;
	$value_format = $datatype == 'string' ? "'$value'" : $value;
	$sql = "UPDATE ec_flight_bookings
			SET $field = $value_format
			WHERE id = '$id' AND deleted = 0";
	$db->query($sql);
}

require_once 'custom/include/utils/address.php';
require_once 'custom/include/utils/Telegram.php';
require_once 'custom/include/utils/Mattermost.php';
require_once 'custom/include/utils/exits.php';
require_once 'custom/include/utils/booking.php';
require_once 'custom/include/utils/calls.php';
require_once 'custom/include/utils/string.php';
require_once 'custom/include/utils/Flight.php';
require_once 'custom/include/utils/FareClass.php';
require_once 'custom/include/utils/Baggage.php';
// Init helpers
foreach (glob("custom/include/helpers/*Helper.php") as $file) {
    if (is_file($file)) require_once $file;
}