<?php
$searchdefs['Documents'] =
  array(
    'layout' =>
    array(
      'basic_search' =>
      array(
        0 => 'document_name',
        1 => 
        array(
          'name' => 'booking_name',
          'label' => 'LBL_BOOKING_NAME',
          'type' => 'relate',
          'default' => true,
          'width' => '10%',
          'displayParams' =>
          array(
            'field' =>
            array(
              'readonly' => 'readonly',
              'style' => 'background-color: #f0f0f0;',
            ),
          ),
        ),
        2 => 'active_date',
      ),
      'advanced_search' =>
      array(
        'document_name' =>
        array(
          'name' => 'document_name',
          'default' => true,
          'width' => '10%',
        ),
        'active_date' =>
        array(
          'name' => 'active_date',
          'default' => true,
          'width' => '10%',
        ),
        'exp_date' =>
        array(
          'name' => 'exp_date',
          'default' => true,
          'width' => '10%',
        ),
        
        'booking_name' =>
        array(
          'type' => 'relate',
          'studio' => 'visible',
          'label' => 'LBL_BOOKING_NAME',
          'width' => '10%',
          'default' => true,
          'name' => 'booking_name',
          'displayParams' =>
          array(
            'field' =>
            array(
              'readonly' => 'readonly',
              'style' => 'background-color: #f0f0f0;',
            ),
          ),
        ),

        'status_id' => array(
          'name' => 'status_id',
          'type' => 'enum',
          'label' => 'LBL_DOC_STATUS',
          'width' => '10%',
          'default' => true,
          'options' => 'document_status_dom',
        ),


        'template_type' =>
        array(
          'type' => 'enum',
          'label' => 'LBL_TEMPLATE_TYPE',
          'width' => '10%',
          'default' => true,
          'name' => 'template_type',
        ),
        'category_id' =>
        array(
          'name' => 'category_id',
          'default' => true,
          'width' => '10%',
        ),
        // 'subcategory_id' =>
        // array(
        //   'name' => 'subcategory_id',
        //   'default' => true,
        //   'width' => '10%',
        // ),



        // 'assigned_user_id' =>
        // array(
        //   'name' => 'assigned_user_id',
        //   'type' => 'enum',
        //   'label' => 'LBL_ASSIGNED_TO',
        //   'function' =>
        //   array(
        //     'name' => 'get_user_array',
        //     'params' =>
        //     array(
        //       0 => false,
        //     ),
        //   ),
        //   'default' => true,
        //   'width' => '10%',
        // ),
      ),
    ),
    'templateMeta' =>
    array(
      'maxColumns' => '3',
      'maxColumnsBasic' => '4',
      'widths' =>
      array(
        'label' => '10',
        'field' => '30',
      ),
    ),
  );
