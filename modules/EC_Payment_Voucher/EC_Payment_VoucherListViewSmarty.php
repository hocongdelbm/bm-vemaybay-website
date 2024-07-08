<?php
require_once('include/ListView/ListViewSmarty.php');

class EC_Payment_VoucherListViewSmarty extends ListViewSmarty
{

    function __construct() {
		parent::__construct();
	}

    function buildExportLink($id = 'export_link')
    {
        global $app_strings;
        $script = '<input class="btn btn-success" type="button" value="Xuất Excel"' .
            'onclick="return sListView.send_form(true, \'' . $_REQUEST['module'] .
            '\', \'index.php?entryPoint=export&fileName=PhieuChi&fileType=xls\',\'' . $app_strings['LBL_LISTVIEW_NO_SELECTED'] . '\')">';

        return $script;
    }
}

?>