<?php

$searchdefs['Schedulers'] = array(
  'templateMeta' => array(
    'maxColumns' => '3',
    'widths' => array('label' => '10', 'field' => '30'),
  ),
  'layout' =>
  array(
    'basic_search' =>
    array(
      'name' =>
      array(
        'name' => 'name',
        'default' => true,
        'width' => '10%',
      ),
    ),
    'advanced_search' => array(
      'name' =>
      array(
        'name' => 'name',
        'default' => true,
        'width' => '10%',
      ),
      'status' => array(
        'name' => 'status',
        'default' => true,
        'width' => '10%',
    ),
    )
  ),
);
