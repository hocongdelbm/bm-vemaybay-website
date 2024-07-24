<?php
$module_name = 'EC_HoaDonMua';
$viewdefs [$module_name] = 
array (
  'DetailView' => 
  array (
    'templateMeta' => 
    array (
      'form' => 
      array (
        'buttons' => 
        array (
          0 => 'EDIT',
          1 => 'DUPLICATE',
          2 => 'DELETE',
        ),
      ),
      'maxColumns' => '2',
      'widths' => 
      array (
        0 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
        1 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
      ),
    ),
    'panels' => 
    array (
      'default' => 
      array (
        0 => 
        array (
          0 => 'name',
          1 => 
          array (
            'name' => 'nhacungcap',
            'studio' => 'visible',
            'label' => 'LBL_NHACUNGCAP',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'diachi',
            'studio' => 'visible',
            'label' => 'LBL_DIACHI',
          ),
          1 => '',
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'nguoigiao',
            'label' => 'LBL_NGUOIGIAO',
          ),
          1 => 
          array (
            'name' => 'nhanhoadon',
            'label' => 'LBL_NHANHOADON',
          ),
        ),
        3 => 
        array (
          0 => 'description',
          1 => 
          array (
            'name' => 'kemtheo',
            'studio' => 'visible',
            'label' => 'LBL_KEMTHEO',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'ngaychungtu',
            'label' => 'LBL_NGAYCHUNGTU',
          ),
          1 => 
          array (
            'name' => 'ngayhachtoan',
            'label' => 'LBL_NGAYHACHTOAN',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'hanthanhtoan',
            'label' => 'LBL_HANTHANHTOAN',
          ),
          1 => 
          array (
            'name' => 'loaitien',
            'studio' => 'visible',
            'label' => 'LBL_LOAITIEN',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'pt_thanhtoan',
            'studio' => 'visible',
            'label' => 'LBL_PT_THANHTOAN',
          ),
          1 => 'assigned_user_name',
        ),
        7 => 
        array (
          0 => 
          array (
            'name' => 'loaichungtu',
            'studio' => 'visible',
            'label' => 'LBL_LOAICHUNGTU',
          ),
        ),
        8 => 
        array (
          0 => 
          array (
            'name' => 'date_entered',
            'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
            'label' => 'LBL_DATE_ENTERED',
          ),
          1 => 
          array (
            'name' => 'date_modified',
            'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
            'label' => 'LBL_DATE_MODIFIED',
          ),
        ),
      ),
      'lbl_panel1' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'line_items',
            'label' => 'LBL_LINE_ITEMS',
            'customCode' => '{$LINE_ITEMS}',
          ),
        ),
      ),
    ),
  ),
);
;
?>
