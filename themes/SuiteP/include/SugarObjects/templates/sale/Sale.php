<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'include/SugarObjects/templates/basic/Basic.php';

class Sale extends Basic
{
    public $amount_usdollar;
    public $currency_id;

    /**
     * Sale constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param string $order_by
     * @param string $where
     * @param array $filter
     * @param array $params
     * @param int $show_deleted
     * @param string $join_type
     * @param bool $return_array
     * @param null $parentbean
     * @param bool $singleSelect
     *
     * @param bool $ifListForExport
     *
     * @return String
     */
    public function create_new_list_query(
        $order_by,
        $where,
        $filter = array(),
        $params = array(),
        $show_deleted = 0,
        $join_type = '',
        $return_array = false,
        $parentbean = null,
        $singleSelect = false,
        $ifListForExport = false
    ) {
        //Ensure that amount is always on list view queries if amount_usdollar is as well.
        if (!empty($filter) && isset($filter['amount_usdollar']) && !isset($filter['amount'])) {
            $filter['amount'] = true;
        }

        return parent::create_new_list_query(
            $order_by,
            $where,
            $filter,
            $params,
            $show_deleted,
            $join_type,
            $return_array,
            $parentbean,
            $singleSelect
        );
    }

    /**
     *
     */
    public function fill_in_additional_list_fields()
    {
        parent::fill_in_additional_list_fields();

        //Ensure that the amount_usdollar field is not null.
        if (empty($this->amount_usdollar) && !empty($this->amount)) {
            $this->amount_usdollar = $this->amount;
        }
    }

    /**
     *
     */
    public function fill_in_additional_detail_fields()
    {
        parent::fill_in_additional_detail_fields();
        //Ensure that the amount_usdollar field is not null.
        if (empty($this->amount_usdollar) && !empty($this->amount)) {
            $this->amount_usdollar = $this->amount;
        }
    }

    /**
     * @param bool $check_notify
     *
     * @return string
     */
    public function save($check_notify = false)
    {
        //"amount_usdollar" is really amount_basecurrency. We need to save a copy of the amount in the base currency.
        if (isset($this->amount) && !number_empty($this->amount)) {
            if (!number_empty($this->currency_id)) {
                $currency = BeanFactory::newBean('Currencies');
                $currency->retrieve($this->currency_id);
                $this->amount_usdollar = $currency->convertToDollar($this->amount);
            } else {
                $this->amount_usdollar = $this->amount;
            }
        }

        return parent::save($check_notify);
    }
}
