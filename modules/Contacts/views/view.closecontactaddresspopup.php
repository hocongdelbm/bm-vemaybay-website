<?php
class ContactsViewCloseContactAddressPopup extends ViewList
{
    public function CloseContactAddressPopup()
    {
        parent::__construct();
    }

    public function display()
    {
        if (isset($_REQUEST['close_window'])) {
            echo "<script>window.close();</script>";
        }
        parent::display();
    }
}
