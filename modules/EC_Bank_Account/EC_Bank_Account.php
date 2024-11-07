<?php
class EC_Bank_Account extends Basic
{
    public $new_schema = true;
    public $module_dir = 'EC_Bank_Account';
    public $object_name = 'EC_Bank_Account';
    public $table_name = 'ec_bank_account';
    public $importable = true;

    public $id;
    public $name;
    public $date_entered;
    public $date_modified;
    public $modified_user_id;
    public $modified_by_name;
    public $created_by;
    public $created_by_name;
    public $description;
    public $deleted;
    public $created_by_link;
    public $modified_user_link;
    public $assigned_user_id;
    public $assigned_user_name;
    public $assigned_user_link;
    public $SecurityGroups;

    public $account_number;
    public $account_holder;
    public $bank_id;
    public $bank;
    public $branch;
    public $unfollow;
    public $is_display;
    public $is_sms;
    public $account;
    public $is_roll;
    public $lastest_get;

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }

        return false;
    }

    function save($check_notify = FALSE)
    {
        if (empty($this->assigned_user_id)) {
            $this->assigned_user_id = $GLOBALS['current_user']->id;
        }
        
        if(isset($_POST['account_number']) && !empty($_POST['account_number'])){
            if (myCheckValueExist('EC_Bank_Account', array('account_number', 'bank_id'), array($_POST['account_number'], $_POST['bank_id']), $this->id)) {
                header('Location: index.php?module=EC_Bank_Account&action=Error&error_string=' . urlencode('Số tài khoản <' . $_POST['account_number'] . '> đã bị trùng trong danh sách nhập. Vui lòng kiểm tra lại.'));
                exit();
            }
        }

        parent::save($check_notify);
    }

    public function changeBankAccountPosition($stk_id, $change_type, $booking_id)
    {
        global $db;

        if (empty($stk_id) &&  strtolower($change_type) == 'get_infor_bank') {
            // Booking - get infor bank from booking
            $content = '';
            $sql = 'SELECT ba.id AS id,
                            ba.account_number AS account_number,
                            ba.lastest_get,
                            ba.account_holder AS account_holder,
                            b.short_name AS short_name,
                            ba.description,
                            b.name 
                    FROM ec_bank_account ba 
                    INNER JOIN ec_banks b ON b.id = ba.bank_id
                    WHERE ba.deleted = 0 AND ba.is_roll = 1
                    ORDER BY ba.lastest_get ASC
                    LIMIT 1';

            if (!empty($booking_id)) {
                $sql_bk = 'SELECT total_amount, phone 
                            FROM ec_flight_bookings 
                            WHERE deleted = 0
                            AND id = "' . $booking_id . '"';
                $res_bk = $db->query($sql_bk);
                if ($row_bk = $db->fetchByAssoc($res_bk)) {
                    $total_amount   = $row_bk['total_amount'];
                    $phone_bk       = $row_bk['phone'];
                }
            }


            $res = $db->query($sql);
            if ($row = $db->fetchByAssoc($res)) {
                $content .= 'Ngân hàng: ' . $row['short_name'] . ' - Số TK: ' . $row['account_number'] .  ' - ' . $row['account_holder'];
                $content .= '. Số tiền: ' . format_number($total_amount) . '. Nội dung: thanh toán ' . $phone_bk;

                $stk = new EC_Bank_Account;
                $stk->retrieve($row['id']);
                $stk->lastest_get     = date('Y-m-d H:i:s');
                $stk->save();
            } else {
                $content .= 'Không tìm thấy thông tin ngân hàng.';
            }

            return $content;
        } else {
            if (strtolower($change_type) == 'up') {
                $sql1 = '
                    SELECT id, lastest_get
                    FROM ec_bank_account
                    WHERE is_roll = 1
                        AND deleted = 0
                    ORDER BY lastest_get 
                    LIMIT 1
                ';
                $res1 = $this->db->query($sql1);
                $row1 = $this->db->fetchByAssoc($res1);

                $sql2 = '
                    UPDATE ec_bank_account
                    SET lastest_get = "' . date('Y-m-d H:i:s', strtotime('-1 minute', strtotime($row1['lastest_get']))) . '"
                    WHERE id = "' . $stk_id . '" AND is_roll = 1 AND deleted = 0
                ';
                $this->db->query($sql2);
            } else if (strtolower($change_type) == 'down' || strtolower($change_type) == 'copy') {
                $stk = new EC_Bank_Account;
                $stk->retrieve($stk_id);
                $stk->lastest_get     = date('Y-m-d H:i:s');
                $stk->save();
            }

            return '';
        }
    }
}
