<?php

class AlertsController extends SugarController
{
	public function process(){
        switch ($this->action) {
            case "Error":
                $this->action = "Error";
                break;
        }

		parent::process();
    }

    public function action_get()
    {
        global $current_user, $app_strings;
        $bean = BeanFactory::getBean('Alerts');

        $this->view_object_map['Flash'] = '';
        // $this->view_object_map['Results'] = $bean->get_full_list("alerts.date_entered", "alerts.assigned_user_id = '" . $current_user->id . "' AND is_read != '1'");
        $this->view_object_map['Results'] = $bean->get_full_list("alerts.date_entered desc", "alerts.assigned_user_id = '" . $current_user->id . "' AND alerts.deleted = '0'");

        if ($this->view_object_map['Results'] == '') {
            $this->view_object_map['Flash'] = '
                <div class="d-flex align-items-center no-notification flex-column gap-2 no-notification">
                    <div class="no-bell-icon" tabindex="0">
                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="50px" height="30px" viewBox="0 0 50 30" enable-background="new 0 0 50 30" xml:space="preserve">
                            <g class="bell-icon__group">
                            <path class="bell-icon__ball" id="ball" fill-rule="evenodd" stroke-width="1.5" clip-rule="evenodd" fill="none" stroke="#currentColor" stroke-miterlimit="10" d="M28.7,25 c0,1.9-1.7,3.5-3.7,3.5s-3.7-1.6-3.7-3.5s1.7-3.5,3.7-3.5S28.7,23,28.7,25z"/>
                            <path class="bell-icon__shell" id="shell" fill-rule="evenodd" clip-rule="evenodd" fill="#FFFFFF" stroke="#currentColor" stroke-width="2" stroke-miterlimit="10" d="M35.9,21.8c-1.2-0.7-4.1-3-3.4-8.7c0.1-1,0.1-2.1,0-3.1h0c-0.3-4.1-3.9-7.2-8.1-6.9c-3.7,0.3-6.6,3.2-6.9,6.9h0 c-0.1,1-0.1,2.1,0,3.1c0.6,5.7-2.2,8-3.4,8.7c-0.4,0.2-0.6,0.6-0.6,1v1.8c0,0.2,0.2,0.4,0.4,0.4h22.2c0.2,0,0.4-0.2,0.4-0.4v-1.8 C36.5,22.4,36.3,22,35.9,21.8L35.9,21.8z"/>
                            </g>
                        </svg>
                        <div class="notification-amount">
                            <span>0</span>
                        </div>
                    </div>
                    <p>'.$app_strings['LBL_NOTIFICATIONS_NONE'].'</p>
                </div>
            ';
        }

        $this->view = 'default';
    }

    public function action_get_unread()
    {
        global $current_user, $app_strings;
        $bean = BeanFactory::getBean('Alerts');

        $this->view_object_map['Flash'] = '';
        $this->view_object_map['Results'] = $bean->get_full_list("alerts.date_entered desc", "alerts.assigned_user_id = '" . $current_user->id . "' AND alerts.deleted = '0' AND alerts.is_read = '0'");

        
        if ($this->view_object_map['Results'] == '') {
            $this->view_object_map['Flash'] = '
                <div class="d-flex align-items-center no-notification flex-column gap-2 no-notification">
                    <div class="no-bell-icon" tabindex="0">
                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="50px" height="30px" viewBox="0 0 50 30" enable-background="new 0 0 50 30" xml:space="preserve">
                            <g class="bell-icon__group">
                            <path class="bell-icon__ball" id="ball" fill-rule="evenodd" stroke-width="1.5" clip-rule="evenodd" fill="none" stroke="#currentColor" stroke-miterlimit="10" d="M28.7,25 c0,1.9-1.7,3.5-3.7,3.5s-3.7-1.6-3.7-3.5s1.7-3.5,3.7-3.5S28.7,23,28.7,25z"/>
                            <path class="bell-icon__shell" id="shell" fill-rule="evenodd" clip-rule="evenodd" fill="#FFFFFF" stroke="#currentColor" stroke-width="2" stroke-miterlimit="10" d="M35.9,21.8c-1.2-0.7-4.1-3-3.4-8.7c0.1-1,0.1-2.1,0-3.1h0c-0.3-4.1-3.9-7.2-8.1-6.9c-3.7,0.3-6.6,3.2-6.9,6.9h0 c-0.1,1-0.1,2.1,0,3.1c0.6,5.7-2.2,8-3.4,8.7c-0.4,0.2-0.6,0.6-0.6,1v1.8c0,0.2,0.2,0.4,0.4,0.4h22.2c0.2,0,0.4-0.2,0.4-0.4v-1.8 C36.5,22.4,36.3,22,35.9,21.8L35.9,21.8z"/>
                            </g>
                        </svg>
                        <div class="notification-amount">
                            <span>0</span>
                        </div>
                    </div>
                    <p>'.$app_strings['LBL_NOTIFICATIONS_NONE'].'</p>
                </div>
            ';
        }

        $this->view = 'default';
    }

    public function action_add()
    {
        global $current_user;
        $name = null;
        $description = null;

        $assigned_user_id = $current_user->id;
        $is_read = 0;
        $url_redirect = null;
        $target_module = null;
        $reminder_id = '';
        $type = 'info';


        if (isset($_POST['name'])) {
            $name = $_POST['name'];
        }
        if (isset($_POST['description'])) {
            $description = $_POST['description'];
        }
        if (isset($_POST['is_read'])) {
            $is_read = $_POST['is_read'];
        }
        if (isset($_POST['url_redirect'])) {
            $url_redirect = $_POST['url_redirect'];
        } else {
            $url_redirect = null;
        }

        if ($url_redirect == null) {
            $url_redirect = 'index.php?fakeid=' . uniqid('fake_', true);
        }

        if (isset($_POST['target_module'])) {
            $target_module = $_POST['target_module'];
        }
        if (isset($_POST['type'])) {
            $type = $_POST['type'];
        }
        if (isset($_POST['reminder_id'])) {
            $reminder_id = $_POST['reminder_id'];
        }

        $shouldShowReminderPopup = false;

        if (isset($_POST) && $reminder_id) {
            $bean = BeanFactory::getBean('Alerts');
            $result = $bean->get_full_list(
                "",
                "alerts.assigned_user_id = '" . $current_user->id . "' AND reminder_id = '" . $reminder_id . "'"
            );
            if (empty($result)) {
                $bean = BeanFactory::newBean('Alerts');
                $bean->name = $name;
                $bean->description = $description;
                $bean->url_redirect = $url_redirect;
                $bean->target_module = $target_module;
                $bean->is_read = $is_read;
                $bean->assigned_user_id = $assigned_user_id;
                $bean->type = $type;
                $bean->reminder_id = $reminder_id;
                $bean->save();

                $shouldShowReminderPopup = true;
            }
        }

        $this->view_object_map['Flash'] = '';
        $this->view_object_map['Result'] = '';
        $this->view = 'ajax';

        echo json_encode(['result' => (int)$shouldShowReminderPopup], true);
    }

    public function action_markAsRead()
    {
        $bean = BeanFactory::getBean('Alerts', $_GET['record']);
        $bean->is_read = 1;
        $bean->save();

        $this->view = 'json';
    }

    public function action_clearAlert()
    {
        $bean = BeanFactory::getBean('Alerts', $_GET['record']);
        $bean->deleted = 1;
        $bean->save();

        $this->view = 'json';
    }

    public function action_redirect()
    {
        $bean = BeanFactory::getBean('Alerts', $_GET['record']);
        $redirect_url = $bean->url_redirect;
        $bean->is_read = 1;
        $bean->save();

        if ($redirect_url) {
            SugarApplication::redirect($redirect_url);
        }

        if (!empty($_SERVER['HTTP_REFERER'])) {
            SugarApplication::redirect($_SERVER['HTTP_REFERER']);
        }

        SugarApplication::redirect('index.php');
    }
}
