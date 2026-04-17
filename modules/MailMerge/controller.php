<?php
class MailMergeController extends SugarController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function action_search()
    {
        global $beanList;

        //set ajax view
        $this->view = 'ajax';
        //get the module
        $module = !empty($_REQUEST['qModule']) ? $_REQUEST['qModule'] : '';
        //lowercase module name
        $lmodule = strtolower($module);
        //get the search term
        $term = !empty($_REQUEST['term']) ? DBManagerFactory::getInstance()->quote($_REQUEST['term']) : '';

        $max = !empty($_REQUEST['max']) ? $_REQUEST['max'] : 10;
        $order_by = !empty($_REQUEST['order_by']) ? $_REQUEST['order_by'] : $lmodule . ".name";
        $offset = !empty($_REQUEST['offset']) ? $_REQUEST['offset'] : 0;
        $response = array();

        if (!empty($module)) {
            $where = '';
            $deleted = '0';
            $using_cp = false;

            if (!empty($term)) {
                if ($module == 'Contacts' || $module == 'Leads') {
                    $where = $lmodule . ".first_name like '%" . $term . "%' OR " . $lmodule . ".last_name like '%" . $term . "%'";
                    $order_by = $lmodule . ".last_name";
                } else {
                    $where = $lmodule . ".name like '" . $term . "%'";
                }
            }

            $seed = SugarModule::get($module)->loadBean();

            if ($using_cp) {
                $fields = array('id', 'first_name', 'last_name');
                $dataList = $seed->retrieveTargetList($where, $fields, $offset, -1, $max, $deleted);
            } else {
                $dataList = $seed->get_list($order_by, $where, $offset, -1, $max, $deleted);
            }

            $list = $dataList['list'];
            $row_count = $dataList['row_count'];

            $output_list = array();
            // foreach ($list as $value) {
            //     $output_list[] = get_return_value($value, $module);
            // }

            $response['result'] = array('result_count' => $row_count, 'entry_list' => $output_list);
        }

        $json = getJSONobj();
        $json_response = $json->encode($response, true);
        print $json_response;
    }
}
