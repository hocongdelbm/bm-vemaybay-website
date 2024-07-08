<?php
require_once("include/Sugar_Smarty.php");

class Viewsmstool extends SugarView {
    function display() {
        if (ACLController::checkAccess('EC_Payment_Voucher', 'edit', true)) {
            $smartyCont = new Sugar_Smarty();
            $this->populateContent($smartyCont);
            $smartyCont->display('modules/EC_Flight_Bookings/tpls/view_smstool.tpl');
        } else {
            header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
            exit();
        }
    }

    function populateContent($smartyObj) {
        global $app_list_strings;
        $carriers = array(
            'viettel' => array('content' => 'VT200', 'send_to' => '109'),
            'vinaphone' => array('content' => 'S50', 'send_to' => '900')
        );
        $port_list      = 'all';
        $sms_func       = 'check_amount';
        $card_number    = '';
        $carrier        = '';

        if (isset($_POST['port_list']) && trim($_POST['port_list']) !== '') {
            $port_list = trim($_POST['port_list']);
        }
        if (!empty($_POST['sms_func'])) {
            $sms_func = trim($_POST['sms_func']);
        }
        if (!empty($_POST['card_number'])) {
            $card_number = preg_replace('/[^0-9]/isU', '', $_POST['card_number']);
        }
        if (!empty($_POST['carrier'])) {
            $carrier = trim($_POST['carrier']);
        }

        $arr = array();
        if (isset($_POST['btnProcess'])) {

            if (($sms_func == 'recharge_amount' || $sms_func == 'viettel' || $sms_func == 'vinaphone' || $sms_func == 'mobifone')
                && ($port_list === '' || $port_list == 'all')
            ) {
                echo '<p class="error">Vui lòng chọn 1 SIM để tiếp tục</p>';
                return false;
            }
            if ($sms_func == 'recharge_amount' && empty($card_number)) {
                echo '<p class="error">Vui lòng nhập mã thẻ cào để tiếp tục</p>';
                return false;
            }
            if (($sms_func == 'viettel' || $sms_func == 'vinaphone' || $sms_func == 'mobifone') && $sms_func != $carrier) {
                echo '<p class="error">Vui lòng chọn đúng gói đăng ký ứng với từng nhà mạng</p>';
                return false;
            }

            if (($sms_func == 'viettel' || $sms_func == 'vinaphone' || $sms_func == 'mobifone') && !empty($carrier)) {
                if (mySendSMS($port_list, $carriers[$carrier]['send_to'], $carriers[$carrier]['content'], 1))
                    $resp_data = 'Tin nhắn gửi thành công';
                else
                    $resp_data = 'Tin nhắn gửi thất bại';
            } else {
                if ($port_list == 'all') {
                    foreach ($app_list_strings['sms_port_list'] as $port_key => $port_val) {
                        if ($port_key !== '')
                            $arr[] = array('port' => $port_key, 'command' => $this->getUSSDCommand($sms_func, $card_number));
                    }
                } else {
                    $arr[] = array('port' => $port_list, 'command' => $this->getUSSDCommand($sms_func, $card_number));
                }
                $resp = myGatewaySendUSSD($arr);
                $resp_data = '';
                if ($resp['status'] == 200) {
                    if (!empty($resp['message'])) {
                        foreach ($resp['message'] as $msg_data) {
                            $resp_data .= "\r\n" . $app_list_strings['sms_port_list'][$msg_data['port']];
                            $resp_data .= "\r\n" . str_replace('" + "', '', $msg_data['content']);
                            $resp_data .= "\r\n--------------------";
                        }
                    }
                } else {
                    $resp_data .= $resp['message'];
                }
            }
            $smartyObj->assign('RESP_DATA', $resp_data);
        }
        
        $smartyObj->assign('RECHECK_AMOUNT_CHECKED', ($sms_func == 'check_amount' ? 'checked="checked"' : 'checked="checked"'));
        $smartyObj->assign('RECHARGE_AMOUNT_CHECKED', ($sms_func == 'recharge_amount' ? 'checked="checked"' : ''));
        $smartyObj->assign('VIETTEL_CHECKED', ($sms_func == 'viettel' ? 'checked="checked"' : ''));
        $smartyObj->assign('VINAPHONE_CHECKED', ($sms_func == 'vinaphone' ? 'checked="checked"' : ''));
        $smartyObj->assign('MOBIFONE_CHECKED', ($sms_func == 'mobifone' ? 'checked="checked"' : ''));
        $smartyObj->assign('PORT_LIST', $this->getGatewayPortList($port_list));
    }

    function getGatewayPortList($selected_port = 'all') {
        global $app_list_strings;
        $html = '<option ' . ($selected_port == 'all' ? 'selected="selected"' : '') . ' value="all">Tất cả SIM</option>';
        foreach ($app_list_strings['sms_port_list'] as $port_key => $port_val) {
            if ($port_key !== '') {
                $html .= '<option ' . ($selected_port === $port_key ? 'selected="selected"' : '') . ' value="' . $port_key . '">' . $port_val . '</option>';
            }
        }
        return $html;
    }

    function getUSSDCommand($sms_func, $card_number = '') {
        $cmd = '';
        if ($sms_func == 'check_amount')
            $cmd .= '*101#';
        else if ($sms_func == 'recharge_amount' && $card_number != '')
            $cmd .= '*100*' . preg_replace('/[^0-9]/isU', '', $card_number) . '#';
        return $cmd;
    }
}
