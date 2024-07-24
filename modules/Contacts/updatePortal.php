<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'modules/AOP_Case_Updates/util.php';

class updatePortal
{
    /**
     * @param Contact $bean
     */
    public function updateUser($bean)
    {
        if (!isAOPEnabled()) {
            return;
        }
        if (isset($bean->joomla_account_access) && $bean->joomla_account_access !== '') {
            global $sugar_config;
            $aop_config = $sugar_config['aop'];

            $template = BeanFactory::getBean('EmailTemplates', $aop_config['joomla_account_creation_email_template_id']);

            $search = array("\$joomla_pass", "\$portal_address");
            $replace = array($bean->joomla_account_access, $aop_config['joomla_url']);

            $object_arr['Contacts'] = $bean->id;
            $body_html = aop_parse_template($template->body_html, $object_arr);
            $body_html = str_replace($search, $replace, $body_html);

            $body_plain = aop_parse_template($template->body, $object_arr);
            $body_plain = str_replace($search, $replace, $body_plain);

            $this->sendEmail($bean->email1, $template->subject, $body_html, $body_plain, $bean);
        }
    }

    /**
     * @param $emailTo
     * @param $emailSubject
     * @param $emailBody
     * @param $altEmailBody
     * @param SugarBean|null $relatedBean
     */
    public function sendEmail($emailTo, $emailSubject, $emailBody, $altEmailBody, SugarBean $relatedBean = null)
    {
        require_once 'modules/Emails/Email.php';
        require_once 'include/SugarPHPMailer.php';

        $emailObj = BeanFactory::newBean('Emails');
        $emailSettings = getPortalEmailSettings();

        $mail = new SugarPHPMailer();
        $mail->setMailerForSystem();
        $mail->From = $emailSettings['from_address'];
        isValidEmailAddress($mail->From);
        $mail->FromName = $emailSettings['from_name'];
        $mail->clearAllRecipients();
        $mail->clearReplyTos();
        $mail->Subject = from_html($emailSubject);
        $mail->Body = $emailBody;
        $mail->AltBody = $altEmailBody;
        $mail->prepForOutbound();
        $mail->addAddress($emailTo);

        //now create email
        if (@$mail->send()) {
            $emailObj->to_addrs_names = $emailTo;
            $emailObj->type = 'out';
            $emailObj->deleted = '0';
            $emailObj->name = $mail->Subject;
            $emailObj->description = $mail->AltBody;
            $emailObj->description_html = $mail->Body;
            $emailObj->from_addr_name = $mail->From;
            if ($relatedBean instanceof SugarBean && !empty($relatedBean->id)) {
                $emailObj->parent_type = $relatedBean->module_dir;
                $emailObj->parent_id = $relatedBean->id;
            }
            $emailObj->date_sent_received = TimeDate::getInstance()->nowDb();
            $emailObj->modified_user_id = '1';
            $emailObj->created_by = '1';
            $emailObj->status = 'sent';
            $emailObj->save();
        }
    }
}
