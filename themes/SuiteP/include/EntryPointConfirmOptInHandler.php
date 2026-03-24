<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * Class EntryPointConfirmOptInHandler
 */
class EntryPointConfirmOptInHandler
{

    /**
     * @var EmailAddress $emailAddress
     */
    private $emailAddress;

    /**
     *
     * @param array $request
     * @param array $post
     */
    public function __construct($request = null, $post = null)
    {
        if (is_null($request)) {
            $request = $_REQUEST;
        }

        if (is_null($post)) {
            $post = $_POST;
        }

        $method = isset($request['method']) && $request['method'] ? $request['method'] : null;

        $output = $this->callMethod($method, $post, $request);

        echo $output;
        sugar_cleanup();
    }

    /**
     *
     * @param string $method
     * @param array $post
     * @param array $request
     * @return string
     */
    protected function callMethod($method, $post, $request)
    {
        switch ($method) {
            case 'confirmOptInSelected':
                $output = $this->methodConfirmOptInSelected($post);
                break;
            default:
                $output = $this->methodConfirmOptInUser($request);
                break;
        }
        return $output;
    }

    /**
     * @global array $app_strings
     * @param array $post
     * @return string|boolean
     */
    private function methodConfirmOptInSelected($post)
    {
        global $app_strings;

        $configurator = new Configurator();
        if (!$configurator->isConfirmOptInEnabled()) {
            return false;
        }

        $module = $post['module'];
        $uids = explode(',', $post['uid']);
        $confirmedOptInEmailsSent = 0;
        $errors = 0;
        $warnings = 0;
        $msg = '';

        foreach ($uids as $uid) {
            $emailMan = BeanFactory::newBean('EmailMan');
            if (!$emailMan->addOptInEmailToEmailQueue($module, $uid)) {
                $errors++;
            } elseif ($emailMan->getLastOptInWarn()) {
                $warnings++;
            } else {
                $confirmedOptInEmailsSent++;
            }
        }

        if ($confirmedOptInEmailsSent > 0) {
            $msg .= sprintf($app_strings['RESPONSE_SEND_CONFIRM_OPT_IN_EMAIL'], $confirmedOptInEmailsSent);
        }

        if ($warnings > 0) {
            $msg .=  sprintf($app_strings['RESPONSE_SEND_CONFIRM_OPT_IN_EMAIL_NOT_OPT_IN'], $warnings);
        }

        if ($errors > 0) {
            $msg .=  sprintf($app_strings['RESPONSE_SEND_CONFIRM_OPT_IN_EMAIL_MISSING_EMAIL_ADDRESS_ID'], $errors);
        }


        return $msg;
    }

    /**
     * Confirm Opt In User
     *
     * @param array $request
     * @return string
     */
    private function methodConfirmOptInUser($request)
    {
        $emailAddress = BeanFactory::getBean('EmailAddresses');
        $this->emailAddress = $emailAddress->retrieve_by_string_fields([
            'confirm_opt_in_token' => $request['from']
        ]);

        if ($this->emailAddress) {
            $this->emailAddress->confirmOptIn();
            $this->emailAddress->save();

            $people = $this->getIDs($this->emailAddress->email_address, 'Contacts');
            if ($people) {
                $this->setLawfulBasisForEachPerson($people, 'Contacts');
            }
            $people = $this->getIDs($this->emailAddress->email_address, 'Leads');
            if ($people) {
                $this->setLawfulBasisForEachPerson($people, 'Leads');
            }
        }
        $template = new Sugar_Smarty();
        $template->assign('FOCUS', $this->emailAddress);

        return $template->fetch('include/EntryPointConfirmOptIn.tpl');
    }

    /**
     * @param String $email
     * @param String $module
     *
     * @return array|bool
     */
    private function getIDs($email, $module)
    {
        $people = $this->emailAddress->getRelatedId($email, $module);
        return $people;
    }

    /**
     * @param array $people
     */
    private function setLawfulBasisForEachPerson(array $people, $module)
    {
        /** @var Person $person */
        foreach ($people as $person) {
            $bean = BeanFactory::getBean($module, $person);
            if ($bean) {
                if (!$bean->setLawfulBasis('consent', 'email')) {
                    LoggerManager::getLogger()->warn('Lawful basis saving failed for record ' . $bean->name);
                }
            }
        }
    }
}
