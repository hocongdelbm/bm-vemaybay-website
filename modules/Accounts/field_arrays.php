<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$fields_array['Account'] = array('column_fields' => array(
        "annual_revenue"
        ,"billing_address_street"
        ,"billing_address_city"
        ,"billing_address_state"
        ,"billing_address_postalcode"
        ,"billing_address_country"
        ,"date_entered"
        ,"date_modified"
        ,"modified_user_id"
        ,"assigned_user_id"
        ,"description"
        ,"email1"
        ,"email2"
        ,"employees"
        ,"id"
        ,"industry"
        ,"name"
        ,"ownership"
        ,"parent_id"
        ,"phone_alternate"
        ,"phone_fax"
        ,"phone_office"
        ,"rating"
        ,"shipping_address_street"
        ,"shipping_address_city"
        ,"shipping_address_state"
        ,"shipping_address_postalcode"
        ,"shipping_address_country"
        ,"sic_code"
        ,"ticker_symbol"
        ,"account_type"
        ,"website"
        , "created_by"
        ),
        'list_fields' => array('id', 'name', 'website', 'phone_office', 'assigned_user_name', 'assigned_user_id'
    , 'billing_address_street'
    , 'billing_address_city'
    , 'billing_address_state'
    , 'billing_address_postalcode'
    , 'billing_address_country'
    , 'shipping_address_street'
    , 'shipping_address_city'
    , 'shipping_address_state'
    , 'shipping_address_postalcode'
    , 'shipping_address_country'
        ),
        'required_fields' => array("name"=>1),
);
