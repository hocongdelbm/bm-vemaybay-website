<?php
$module_name = 'EC_Booking_Passengers';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                'EDIT',
                // 'DUPLICATE',
                // 'DELETE',
                // 'FIND_DUPLICATES',
            )
        ),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
    ),

    'panels' => array(
        'default' => array(
            array(
                'name',
                array(),
            ),
            array(
                'salutation',
                'birthday',
            ),
            array(
                'booking',
                'type',
            ),
            array(
                'pnr_outbound',
                'pnr_inbound',
            ),
            array(
                'eticket_outbound',
                'eticket_inbound',
            ),
            array(
                'eluggage_outbound',
                'eluggage_inbound',
            ),
            array(
                'luggage_price',
                'luggage_price_inbound',
            ),
            array(
                'supplier',
                'supplier_inbound',
            ),
            array(
                'direction',
                'go_with',
            ),
            array(
                'add_type',
                'agent_pax_detail_id',
            ),
            array(
                'ib_bag_wgt',
                'ob_bag_wgt',
            ),
            array(
                'parent_detail_id',
                '',
            ),
            array(
                'luggage_purchase_no_vat',
                'luggage_purchase_inbound_no_vat',
            ),
            array(
                'vat_luggage_purchase',
                'vat_luggage_purchase_inbound',
            ),
            array(
                'luggage_purchase',
                'luggage_purchase_inbound',
            ),
            array(
                'luggage_purchase_text',
                'luggage_purchase_text_inbound',
            ),
            array(
                'luggage_index_outbound',
                'luggage_index_inbound',
            ),
            array(
                'hand_baggage_outbound',
                'hand_baggage_inbound',
            ),
            array(
                'passport_number',
                'passport_type',
            ),
            array(
                'passport_expired_date',
                'passport_nationality',
            ),
            array(
                'passport_issue_date',
                'passport_issue_country',
            ),
            array(
                'description',
                array()
            ),
            array(
                array(
                    'name' => 'date_entered',
                    'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
                    'label' => 'LBL_DATE_ENTERED',
                ),
                array(
                    'name' => 'date_modified',
                    'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
                    'label' => 'LBL_DATE_MODIFIED',
                ),
            ),
        )
    )
);
