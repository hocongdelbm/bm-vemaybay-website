<?php

class AlertsController extends SugarController
{
    public function action_get()
    {
        global $current_user, $app_strings;
        $bean = BeanFactory::getBean('Alerts');

        $this->view_object_map['Flash'] = '';
        $this->view_object_map['Results'] = $bean->get_full_list("alerts.date_entered", "alerts.assigned_user_id = '" . $current_user->id . "' AND is_read != '1'");
        if ($this->view_object_map['Results'] == '') {
            $this->view_object_map['Flash'] = $app_strings['LBL_NOTIFICATIONS_NONE'];
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
