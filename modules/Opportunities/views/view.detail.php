<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class OpportunitiesViewDetail extends ViewDetail
{
    public function __construct()
    {
        parent::__construct();
    }




    public function display()
    {
        $currency = BeanFactory::newBean('Currencies');
        if (isset($this->bean->currency_id) && !empty($this->bean->currency_id)) {
            $currency->retrieve($this->bean->currency_id);
            if ($currency->deleted != 1) {
                $this->ss->assign('CURRENCY', $currency->iso4217 .' '.$currency->symbol);
            } else {
                $this->ss->assign('CURRENCY', $currency->getDefaultISO4217() .' '.$currency->getDefaultCurrencySymbol());
            }
        } else {
            $this->ss->assign('CURRENCY', $currency->getDefaultISO4217() .' '.$currency->getDefaultCurrencySymbol());
        }

        parent::display();
    }
}
