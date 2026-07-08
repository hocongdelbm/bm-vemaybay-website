<?php

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
        $html .= '<option ' . ($optKey == $selectedValue ? 'selected' : '') . ' value="' . $optKey . '" data-term="' . $optVal['term'] . '" data-year="' . $optVal['year'] . '" data-fromdate="' . $optVal['from_date'] . '" data-todate="' . $optVal['to_date'] . '">' . $optVal['name'] . '</option>';
    }

    if ($returnType == 'array') {
        return $options;
    } else {
        return $html;
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
        if ($row[$val_name] == $val || (is_array($val) && in_array($row[$val_name], $val))) $selected = 'selected';
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
            $selected = 'selected';
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
        $selected = !empty($val) && $i == $val ? 'selected' : '';
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
        $selected = !empty($val) && $i == $val ? 'selected' : '';
        $html .= '<option ' . $selected . ' value="' . $i . '">' . $i . '</option>';
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

    $sql = "SELECT COUNT(id) FROM " . strtolower($module) . " WHERE id <> '$id' AND deleted = 0 " . $field_con;
    $rowcount = $db->getOne($sql);

    if ($rowcount > 0) return true;
    return false;
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

function myGetAirlineInfo2($airline_code, $search_by, $case_sensitive = 1, $format = 'array')
{
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
			WHERE parent_id = '{$parent_id}' AND parent_type = '{$parent_type}' AND deleted = 0";
    if (!empty($field)) {
        $sql .= " AND {$field} IS NOT NULL ";
    }
    return $db->query($sql);
}

// Create working process
function myCreateWorkingProcess($parent_type, $parent_id, $parent_name, $description, $assigned_user_id, $field)
{
    if (!empty($field)) {
        $work = new EC_Working_Process();
        $work->id = '';
        $work->name = $parent_name;
        $work->parent_type = $parent_type;
        $work->parent_id = $parent_id;
        $work->description = trim($description);
        $work->assigned_user_id = $assigned_user_id;
        $work->$field = 1;
        return $work->save();
    }
    return false;
}

// Get location list by deparment ID
function myGetLocationListByDepID($select_val = '')
{
    global $db;
    $html = '<option value="">-- Trống --</option>';
    $sql = "SELECT id, name
            FROM ec_location
            WHERE deleted = 0
            AND is_display = 0 ";

    $sql .= "ORDER BY date_entered ";
    $res = $db->query($sql);
    while ($row = $db->fetchByAssoc($res)) {
        $selected = ($row['id'] == $select_val) ? 'selected' : '';
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

function mySendMail($user_id, $to_email, $to_name, $subject, $body)
{
    try {
        $send_ok = true;

        require_once('include/SugarPHPMailer.php');
        $mail = new SugarPHPMailer();
        
        $mail->ClearAllRecipients();
        $mail->ClearAttachments();
        $mail->ClearCustomHeaders();
        
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64'; 
        
        $department_info = myGetDepartmentInfo("48840c01-3a4f-c430-f703-56f32c7cd8a4"); // Security travelpass 

        if (isset($department_info['mail_smtpserver']) && isset($department_info['mail_smtpport'])) {
            $user_email = getEmailFromUser($user_id);
            $from_mail = !empty($user_email['second_email']) ? $user_email['second_email'] : $user_email['primary_email'];

            $mail->Host          = $department_info['mail_smtpserver'];
            $mail->Port          = $department_info['mail_smtpport'];
            $mail->SMTPAuth      = TRUE;
            $mail->SMTPSecure    = $department_info['mail_smtpssl'] == 1 ? 'ssl' : 'tls';
            $mail->SMTPKeepAlive = false;
            // $mail->SMTPDebug     = 4;
            $mail->Mailer        = "smtp";
            $mail->Timeout       = 300;
            $mail->Username      = $user_email['primary_email'];
            $mail->Password      = $user_email['primary_pwd'];
            
            $mail->From          = $from_mail;
            $mail->FromName      = ucwords(myRemoveUnicodeChars($user_email['primary_fullname']));
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
            } else {
                $mail->Mailer = 'sendmail';
            }
            $mail->From     = $admin->settings['notify_fromaddress'];
            $mail->FromName = $admin->settings['notify_fromname'];
        }

        // Cấu hình nội dung 
        $mail->isHTML(true);
        $mail->Subject = $subject;
        
        // Tạo nội dung HTML sạch
        $full_body = '<html><head><meta charset="UTF-8"></head><body>' . from_html($body) . '</body></html>';
        $mail->Body = $full_body;
 
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '</p>'], "\n", from_html($body)));

        $mail->AddAddress($to_email, $to_name);
        $mail->AddReplyTo($mail->From);
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
        
        if (!$mail->send()) {
            $send_ok = false;
            $GLOBALS['log']->fatal(json_encode([
                "Mailer error" => $mail->ErrorInfo,
                "Mailer Host" => $mail->Host,
                "Mailer Username" => $mail->Username,
            ]));
        }
        
        // save sent
        $imapPath = '{'.$mail->Host.':993/imap/ssl}Sent Items';
        $imap = imap_open($imapPath, $mail->Username , $mail->Password); 
        imap_append($imap, $imapPath, $mail->getSentMIMEMessage());
        imap_close($imap);

        return $send_ok;
    } catch (Exception $e) {
        $GLOBALS['log']->fatal("Exception: {$e->getMessage()} in mySendMail()");
        return false;
    } catch (Throwable $th) {
        $GLOBALS['log']->fatal("Throwable: {$th->getMessage()} in mySendMail()");
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
    } else if ($airline == 'VJA' || $airline == 'VJ') {
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
    } else if ($airline == 'BBA' || $airline == 'QH') {
        if ($pass_type == '2') {
            $pass_ticket_class = '_infant';
            $luggage_arr = $app_list_strings[$arr_replace[$airline] . $pass_ticket_class . '_luggage_price_list'];
        } else if (!empty($ticket_class)) {
            $pass_ticket_class = '_' . strtolower(str_replace(' ', '', $ticket_class));
            $luggage_list = $app_list_strings[$arr_replace[$airline] . $pass_ticket_class . '_luggage_price_list'];
            if (empty($luggage_list)) $luggage_list = array();
            $luggage_arr = $luggage_list + $app_list_strings['bambooair_advanced_luggage_price_list'];
        }
    } else if ($airline == 'VNP' || $airline == 'BL') {
        if ($pass_type == '2') {
            $pass_ticket_class = '_infant';
            $luggage_arr = $app_list_strings[$arr_replace[$airline] . $pass_ticket_class . '_luggage_price_list'];
        }
    } else if ($airline == 'VTA' || $airline == 'VU') {
        // Từ ngày 06-01-2023 thì lấy thông tin hành lý mới lần 2
        if (strtotime($booking_date) >= strtotime('2023-01-06')) {
            $luggage_arr = $app_list_strings['new_' . $arr_replace[$airline] . '_luggage_price_list2'];
        }
        // Booking đặt từ ngày 11-08-2022 thì lấy thông tin hành lý mới
        else if (strtotime($booking_date) >= strtotime('2022-08-11')) {
            $luggage_arr = $app_list_strings['new_' . $arr_replace[$airline] . '_luggage_price_list1'];
        }
    } else {
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

// Admin hệ thống và quản lý
function isQLUser()
{
    global $current_user, $db;

    if (is_admin($current_user)) {
        return true;
    }

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
    global $db;

    $arr = array();
    $sql = "SELECT id, td_sip, td_password
            FROM users
            WHERE td_sip IS NOT NULL AND deleted = 0";

    $res = $db->query($sql);
    while ($row = $db->fetchByAssoc($res)) {
        $arr[$row['id']] = array(
            'user' => $row['td_sip'],
            'password' => $row['td_password']
        );
    }

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
function get_server_name(string $user_id): string {
    $arr = [
        'dc22131a-795a-6cd3-2caa-52d40d3b5622' => 'vietjet.net',
        '557d4a5b-27ce-5cb1-4531-5800ab9ed31d' => 'timchuyenbay.com',
        '2b2c93b3-e916-113c-29bc-5b4c6de75db4' => 'timchuyenbay.vn',
        '940beedb-4f03-0e00-1a16-5456ebc43fc0' => 'vemaybay5s.com'
    ];
    if (isset($arr[$user_id])) return $arr[$user_id];

    global $db;
    return $db->getOne("SELECT last_name FROM users WHERE id = '$user_id' AND deleted = 0") ?? '';
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

function update_field_booking($id, $field, $value, $datatype = 'string')
{
    if (is_null($id) || is_null($field) || is_null($value) || empty($id) || empty($field) || empty($value)) return false;
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
require_once 'custom/include/utils/printSendTicket.php';
require_once 'custom/include/utils/daily_ad_cost.php';

// Init helpers
foreach (glob("custom/include/helpers/*Helper.php") as $file) {
    if (is_file($file)) require_once $file;
}

foreach (glob("custom/include/helpers/cache/*Helper.php") as $file) {
    if (is_file($file)) require_once $file;
}

foreach (glob("custom/include/helpers/modules/*Helper.php") as $file) {
    if (is_file($file)) require_once $file;
}
// Services
require_once 'custom/services/services_autoload.php'; 
// Init entry
require_once 'custom/entrypoints/entryFactory.php';
