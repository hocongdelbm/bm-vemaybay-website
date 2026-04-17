<?php




class Basic extends SugarBean
{
    /**
     * @var array
     */
    protected static $doNotDisplayOptInTickForModule = array(
        'Users',
        'Employees'
    );

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see SugarBean::get_summary_text()
     */
    public function get_summary_text()
    {
        return (string)$this->name;
    }

    /**
     * Return Email address from an email address field eg email1
     * @param string $emailField
     * @return \EmailAddress|null
     * @throws InvalidArgumentException
     */
    public function getEmailAddressFromEmailField($emailField)
    {
        $this->validateSugarEmailAddressField($emailField);

        /** @var EmailAddress $emailAddressBean */
        $emailAddressBean = BeanFactory::getBean('EmailAddresses');

        // Fixed #5657: Only update state if email address is exist
        $emailAddressId = $this->getEmailAddressId($emailField);
        $emailAddressBean->retrieve($emailAddressId);

        return $emailAddressBean;
    }

    /**
     *
     * @param string $emailField
     * @return string|null EmailAddress ID or null on error
     * @throws \InvalidArgumentException
     */
    private function getEmailAddressId($emailField)
    {
        $log = LoggerManager::getLogger();

        $this->validateSugarEmailAddressField($emailField);
        $emailAddress = $this->cleanUpEmailAddress($this->{$emailField});

        if (!$emailAddress) {
            $log->warn('Trying to get an empty email address.');
            return null;
        }

        // List view requires us to retrieve the mail so we can see the email addresses
        if (!$this->retrieve()) {
            $log->fatal('A Basic can not retrive.');
            return null;
        }

        $found = false;
        $addresses = $this->emailAddress->addresses;
        foreach ($addresses as $address) {
            if ($this->cleanUpEmailAddress($address['email_address']) === $emailAddress) {
                $found = true;
                $emailAddressId = $address['email_address_id'];
                break;
            }
        }

        if (!$found) {
            // Changed exception to error as demo data is never selected.
            $log->fatal('A Basic bean has not selected email address. (' . $emailAddress . ')');
            return null;
        }

        return $emailAddressId;
    }

    /**
     *
     * @param string $emailField
     * @throws InvalidArgumentException
     */
    protected function validateSugarEmailAddressField($emailField)
    {
        if (!is_string($emailField)) {
            throw new InvalidArgumentException('Invalid type. $emailField must be a string value, eg. email1');
        }

        if (!preg_match('/^email\d+/', $emailField)) {
            throw new InvalidArgumentException(
                '$emailField is invalid, "' . $emailField . '" given. Expected valid name eg. email1'
            );
        }
    }

    /**
     *
     * @param string $emailAddress
     * @return string
     */
    private function cleanUpEmailAddress($emailAddress)
    {
        $ret = $emailAddress;
        $ret = trim($ret);
        $ret = strtolower($ret);

        return $ret;
    }
}
