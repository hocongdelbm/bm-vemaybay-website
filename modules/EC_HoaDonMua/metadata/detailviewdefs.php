<?php

$module_name = 'EC_HoaDonMua';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                'EDIT',
                'DUPLICATE',
                'DELETE',
            )
        ),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
    ),

    'panels' =>
    array(
      'default' =>
      array(
        array(
          'name',
          array(
            'name' => 'nhacungcap',
            'studio' => 'visible',
            'label' => 'LBL_NHACUNGCAP',
          ),
        ),
        array(
          array(
            'name' => 'diachi',
            'studio' => 'visible',
            'label' => 'LBL_DIACHI',
          ),
          array(
            'name' => 'booking',
            'studio' => 'visible',
            'label' => 'LBL_BOOKING',
          ),
        ),
        array(
          array(
            'name' => 'nguoigiao',
            'label' => 'LBL_NGUOIGIAO',
          ),
          array(
            'name' => 'nhanhoadon',
            'label' => 'LBL_NHANHOADON',
          ),
        ),
        array(
          'description',
          array(
            'name' => 'kemtheo',
            'studio' => 'visible',
            'label' => 'LBL_KEMTHEO',
          ),
        ),
        array(
          array(
            'name' => 'ngaychungtu',
            'label' => 'LBL_NGAYCHUNGTU',
          ),
          array(
            'name' => 'ngayhachtoan',
            'label' => 'LBL_NGAYHACHTOAN',
          ),
        ),
        array(
          array(
            'name' => 'hanthanhtoan',
            'label' => 'LBL_HANTHANHTOAN',
          ),
          array(
            'name' => 'loaitien',
            'studio' => 'visible',
            'label' => 'LBL_LOAITIEN',
          ),
        ),
        array(
          array(
            'name' => 'pt_thanhtoan',
            'studio' => 'visible',
            'label' => 'LBL_PT_THANHTOAN',
          ),
          'assigned_user_name',
        ),
        array(
          array(
            'name' => 'loaichungtu',
            'studio' => 'visible',
            'label' => 'LBL_LOAICHUNGTU',
          ),
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
      ),
      'lbl_panel1' =>
      array(
        array(
          array(
            'name' => 'line_items',
            'label' => 'LBL_LINE_ITEMS',
            'customCode' => '{$LINE_ITEMS}',
          ),
        ),
      ),
    ),
);
