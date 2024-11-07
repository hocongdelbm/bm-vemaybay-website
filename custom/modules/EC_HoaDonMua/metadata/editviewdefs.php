<?php
$module_name = 'EC_HoaDonMua';
$viewdefs [$module_name] = 
array (
  'EditView' => 
  array (
    'templateMeta' => 
    array (
      'form' => 
      array (
        'buttons' => 
        array (
          0 => 
          array (
            'customCode' => '{$CUSTOM_SAVE}',
          ),
          1 => 'CANCEL',
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
      'includes' => 
      array (
        0 => 
        array (
          'file' => 'modules/EC_HoaDonMua/js/EC_HoaDonMua.js',
        ),
      ),
    ),
    'panels' => 
    array (
      'default' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'name',
            'label' => 'LBL_NAME',
            'customCode' => '{$CUSTOM_NAME}',
          ),
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
            'displayParams' => 
            array (
              'rows' => 2,
              'cols' => 45,
            ),
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
            'customCode' => '{$NhanHoaDon}',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'description',
            'comment' => 'Full text of the note',
            'label' => 'LBL_DESCRIPTION',
            'displayParams' => 
            array (
              'rows' => 2,
              'cols' => 45,
            ),
          ),
          1 => 
          array (
            'name' => 'kemtheo',
            'studio' => 'visible',
            'label' => 'LBL_KEMTHEO',
            'displayParams' => 
            array (
              'rows' => 2,
              'cols' => 45,
            ),
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
          1 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
          ),
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
      ),
      'lbl_panel1' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'line_items',
            'comment' => 'Chi tiet hoa don',
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
