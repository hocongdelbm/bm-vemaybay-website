<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


class SchedulersViewDetail extends ViewDetail
{

    /**
     * @see SugarView::_getModuleTitleListParam()
     */
    protected function _getModuleTitleListParam($browserTitle = false)
    {
        global $mod_strings;

        return "<a href='index.php?module=Schedulers&action=index'>" . $mod_strings['LBL_MODULE_TITLE'] . "</a>";
    }

    /**
     * display
     */
    public function display()
    {
        $this->bean->parseInterval();
        $this->bean->setIntervalHumanReadable();
        $this->ss->assign('JOB_INTERVAL', $this->bean->intervalHumanReadable);
        $this->bean->created_by_name = get_assigned_user_name($this->bean->created_by);
        $this->bean->modified_by_name = get_assigned_user_name($this->bean->modified_user_id);

        $this->updateOnlineReport();

        parent::display();
    }

    function updateOnlineReport() {
        global $db;
    
        $sql    = 'SELECT id, first_name, last_name, title, 
                    IFNULL(init_exp_mark, 0) AS init_exp_mark,
                    (IFNULL(init_exp_mark, 0) + IFNULL(exp_mark, 0)) AS kpi,
                    (
                        SELECT date_start
                        FROM ec_workhistory
                        WHERE deleted = 0 
                        AND assigned_user_id = users.id
                        ORDER BY date_start DESC
                        LIMIT 1
                    ) AS latest_date_start,
                    (
                        SELECT status
                        FROM ec_workhistory
                        WHERE deleted = 0 
                        AND assigned_user_id = users.id
                        ORDER BY date_start DESC
                        LIMIT 1
                    ) AS work_stt,
                    (
                        SELECT date_start
                        FROM ec_workhistory
                        WHERE deleted = 0 
                        AND assigned_user_id = users.id
                        ORDER BY date_start
                        LIMIT 1
                    ) AS date_begin_work
                FROM users 
                WHERE deleted = 0 AND is_admin = 0
                GROUP BY id
                HAVING (work_stt = "Active" OR work_stt = "Online") 
                    AND latest_date_start <= "' . date('Y-m-d') . '"
                ORDER BY init_exp_mark DESC, date_begin_work';
    
        $res    = $db->query($sql);
        $i 	    = 1;
        
        while ($row = $db->fetchByAssoc($res)) {
            $sql_exist = '
                SELECT IF(COUNT(id) > 0, 1, 0)
                FROM ec_online_report
                WHERE deleted = 0 
                    AND assigned_user_id = "' . $row['id'] . '" 
                    AND DATE_ADD(date_entered, INTERVAL 7 HOUR) >= "' . date('Y-m-d') . '"
            ';

            $is_exist = $db->getOne($sql_exist);

            if (!$is_exist) {
                $online = new EC_Online_Report;
                $online->name = trim($row['last_name']) . ' ' . trim($row['first_name']);
                $online->assigned_user_id = $row['id'];
                $online->status = 0;
                if ($row['title'] != 'Leader' && $row['title'] != 'Booker') {
                    $online->ranking = 0;
                } else {
                    $online->ranking = $i;
                }
                $online->title = $row['title'];
                $online->round = 0;
                $online->kpi = $row['init_exp_mark'];
                $online->save();
            }

            if ($row['title'] != 'QuanLy') {
                $i++;
            }
        }
    
        return true;
    }

}
