<?php

$module_name = 'EC_HoaDonMua';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
        'form' => array (
            'buttons' => 
            array (
                array('customCode' => '{$CUSTOM_SAVE}'),
                'CANCEL',
            ),
        ),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'includes' => 
        array (
            array (
            'file' => 'modules/EC_HoaDonMua/js/EC_HoaDonMua.js',
            ),
        ),
    ),

    'panels' => 
    array (
      'default' => 
      array (
        array (
          array (
            'name' => 'name',
            'label' => 'LBL_NAME',
			'customCode' => '{$CUSTOM_NAME}',
          ),
          array (
            'name' => 'nhacungcap',
            'studio' => 'visible',
            'label' => 'LBL_NHACUNGCAP',
          ),
        ),
        array (
          array (
            'name' => 'diachi',
            'studio' => 'visible',
            'label' => 'LBL_DIACHI',
			'displayParams' => array(
				'rows' => 2,
				'cols' => 45,
			),
          ),
          array (
            'name' => 'booking',
            'studio' => 'visible',
            'label' => 'LBL_BOOKING',
          ),
        ),
        array (
          array (
            'name' => 'nguoigiao',
            'label' => 'LBL_NGUOIGIAO',
          ),
          array (
            'name' => 'nhanhoadon',
            'label' => 'LBL_NHANHOADON',
			'customCode' => '{$NhanHoaDon}',
          ),
        ),
        array (
          array (
            'name' => 'description',
            'comment' => 'Full text of the note',
            'label' => 'LBL_DESCRIPTION',
			'displayParams' => array(
				'rows' => 2,
				'cols' => 45,
			),
          ),
          array (
            'name' => 'kemtheo',
            'studio' => 'visible',
            'label' => 'LBL_KEMTHEO',
			'displayParams' => array(
				'rows' => 2,
				'cols' => 45,
			),
          ),
        ),
        array (
          array (
            'name' => 'ngaychungtu',
            'label' => 'LBL_NGAYCHUNGTU',
          ),
          array (
            'name' => 'ngayhachtoan',
            'label' => 'LBL_NGAYHACHTOAN',
          ),
        ),
        array (
          array (
            'name' => 'hanthanhtoan',
            'label' => 'LBL_HANTHANHTOAN',
          ),
          array (
            'name' => 'loaitien',
            'studio' => 'visible',
            'label' => 'LBL_LOAITIEN',
          ),
        ),
        array (
          array (
            'name' => 'pt_thanhtoan',
            'studio' => 'visible',
            'label' => 'LBL_PT_THANHTOAN',
          ),
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
          ),
        ),
        array (
          array (
            'name' => 'loaichungtu',
            'studio' => 'visible',
            'label' => 'LBL_LOAICHUNGTU',
          ),
        ),
      ),
      'lbl_panel1' => 
      array (
        array (
          array (
            'name' => 'line_items',
            'comment' => 'Chi tiet hoa don',
            'label' => 'LBL_LINE_ITEMS',
            'customCode' => '{$LINE_ITEMS}',
          ),
        ),
      ),
    ),

);
