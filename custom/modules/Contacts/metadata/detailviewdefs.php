<?php
// created: 2024-08-27 06:30:57
<<<<<<< HEAD
$viewdefs = array (
  'Contacts' => 
  array (
    'DetailView' => 
    array (
      'templateMeta' => 
      array (
        'form' => 
        array (
          'buttons' => 
          array (
            'SEND_CONFIRM_OPT_IN_EMAIL' => 
            array (
              'customCode' => '<input type="submit" class="btn btn-primary hidden" disabled="disabled" title="{$APP.LBL_SEND_CONFIRM_OPT_IN_EMAIL}" onclick="this.form.return_module.value=\'Contacts\'; this.form.return_action.value=\'Contacts\'; this.form.return_id.value=\'{$fields.id.value}\'; this.form.action.value=\'sendConfirmOptInEmail\'; this.form.module.value=\'Contacts\'; this.form.module_tab.value=\'Contacts\';" name="send_confirm_opt_in_email" value="{$APP.LBL_SEND_CONFIRM_OPT_IN_EMAIL}"/>',
              'sugar_html' => 
              array (
                'type' => 'submit',
                'value' => '{$APP.LBL_SEND_CONFIRM_OPT_IN_EMAIL}',
                'htmlOptions' => 
                array (
=======
$viewdefs = array(
  'Contacts' =>
  array(
    'DetailView' =>
    array(
      'templateMeta' =>
      array(
        'form' =>
        array(
          'buttons' =>
          array(
            'SEND_CONFIRM_OPT_IN_EMAIL' =>
            array(
              'customCode' => '<input type="submit" class="btn btn-primary hidden" disabled="disabled" title="{$APP.LBL_SEND_CONFIRM_OPT_IN_EMAIL}" onclick="this.form.return_module.value=\'Contacts\'; this.form.return_action.value=\'Contacts\'; this.form.return_id.value=\'{$fields.id.value}\'; this.form.action.value=\'sendConfirmOptInEmail\'; this.form.module.value=\'Contacts\'; this.form.module_tab.value=\'Contacts\';" name="send_confirm_opt_in_email" value="{$APP.LBL_SEND_CONFIRM_OPT_IN_EMAIL}"/>',
              'sugar_html' =>
              array(
                'type' => 'submit',
                'value' => '{$APP.LBL_SEND_CONFIRM_OPT_IN_EMAIL}',
                'htmlOptions' =>
                array(
>>>>>>> 4888d25d1719bc87d035ee04ff9031be7d023969
                  'class' => 'btn btn-primary hidden',
                  'id' => 'send_confirm_opt_in_email',
                  'title' => '{$APP.LBL_SEND_CONFIRM_OPT_IN_EMAIL}',
                  'onclick' => 'this.form.return_module.value=\'Contacts\'; this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$fields.id.value}\'; this.form.action.value=\'sendConfirmOptInEmail\'; this.form.module.value=\'Contacts\'; this.form.module_tab.value=\'Contacts\';',
                  'name' => 'send_confirm_opt_in_email',
                  'disabled' => true,
                ),
              ),
            ),
            0 => 'EDIT',
            1 => 'DELETE',
          ),
        ),
        'maxColumns' => '2',
<<<<<<< HEAD
        'widths' => 
        array (
          0 => 
          array (
            'label' => '10',
            'field' => '30',
          ),
          1 => 
          array (
=======
        'widths' =>
        array(
          0 =>
          array(
            'label' => '10',
            'field' => '30',
          ),
          1 =>
          array(
>>>>>>> 4888d25d1719bc87d035ee04ff9031be7d023969
            'label' => '10',
            'field' => '30',
          ),
        ),
<<<<<<< HEAD
        'includes' => 
        array (
          0 => 
          array (
=======
        'includes' =>
        array(
          0 =>
          array(
>>>>>>> 4888d25d1719bc87d035ee04ff9031be7d023969
            'file' => 'modules/Contacts/Contact.js',
          ),
        ),
        'useTabs' => false,
<<<<<<< HEAD
        'tabDefs' => 
        array (
          'LBL_CONTACT_INFORMATION' => 
          array (
            'newTab' => true,
            'panelDefault' => 'expanded',
          ),
          'LBL_PANEL_ADVANCED' => 
          array (
            'newTab' => true,
            'panelDefault' => 'expanded',
          ),
          'LBL_PANEL_ASSIGNMENT' => 
          array (
=======
        'tabDefs' =>
        array(
          'LBL_CONTACT_INFORMATION' =>
          array(
            'newTab' => true,
            'panelDefault' => 'expanded',
          ),
          'LBL_PANEL_ADVANCED' =>
          array(
            'newTab' => true,
            'panelDefault' => 'expanded',
          ),
          'LBL_PANEL_ASSIGNMENT' =>
          array(
>>>>>>> 4888d25d1719bc87d035ee04ff9031be7d023969
            'newTab' => true,
            'panelDefault' => 'expanded',
          ),
        ),
      ),
<<<<<<< HEAD
      'panels' => 
      array (
        'lbl_contact_information' => 
        array (
          0 => 
          array (
            0 => 
            array (
              'name' => 'name',
              'label' => 'LBL_NAME',
            ),
            1 => 
            array (
=======
      'panels' =>
      array(
        'lbl_contact_information' =>
        array(
          0 =>
          array(
            0 =>
            array(
              'name' => 'name',
              'label' => 'LBL_NAME',
            ),
            1 =>
            array(
>>>>>>> 4888d25d1719bc87d035ee04ff9031be7d023969
              'name' => 'phone_mobile',
              'label' => 'LBL_PHONE_MOBILE',
            ),
          ),
<<<<<<< HEAD
          1 => 
          array (
            0 => 
            array (
              'name' => 'birthdate',
              'label' => 'LBL_BIRTHDATE',
            ),
            1 => 
            array (
=======
          1 =>
          array(
            0 =>
            array(
              'name' => 'birthdate',
              'label' => 'LBL_BIRTHDATE',
            ),
            1 =>
            array(
>>>>>>> 4888d25d1719bc87d035ee04ff9031be7d023969
              'name' => 'email1',
              'studio' => 'false',
              'label' => 'LBL_EMAIL_ADDRESS',
            ),
          ),
<<<<<<< HEAD
          2 => 
          array (
            0 => 
            array (
              'name' => 'zalo_id',
              'label' => 'LBL_ZALO_ID',
            ),
            1 => 
            array (
=======
          2 =>
          array(
            0 =>
            array(
              'name' => 'zalo_id',
              'label' => 'LBL_ZALO_ID',
            ),
            1 =>
            array(
>>>>>>> 4888d25d1719bc87d035ee04ff9031be7d023969
              'name' => 'telegram_id',
              'label' => 'LBL_TELEGRAM_ID',
            ),
          ),
<<<<<<< HEAD
          3 => 
          array (
            0 => 
            array (
              'name' => 'primary_address_street',
              'label' => 'LBL_PRIMARY_ADDRESS',
              'type' => 'address',
              'displayParams' => 
              array (
                'key' => 'primary',
              ),
            ),
            1 => 
            array (
              'name' => 'alt_address_street',
              'label' => 'LBL_ALTERNATE_ADDRESS',
              'type' => 'address',
              'displayParams' => 
              array (
=======
          3 =>
          array(
            0 =>
            array(
              'name' => 'primary_address_street',
              'label' => 'LBL_PRIMARY_ADDRESS',
              'type' => 'address',
              'displayParams' =>
              array(
                'key' => 'primary',
              ),
            ),
            1 =>
            array(
              'name' => 'alt_address_street',
              'label' => 'LBL_ALTERNATE_ADDRESS',
              'type' => 'address',
              'displayParams' =>
              array(
>>>>>>> 4888d25d1719bc87d035ee04ff9031be7d023969
                'key' => 'alt',
              ),
            ),
          ),
<<<<<<< HEAD
          4 => 
          array (
            0 => 
            array (
              'name' => 'assigned_user_name',
              'label' => 'LBL_ASSIGNED_TO_NAME',
            ),
            1 => 
            array (
=======
          4 =>
          array(
            0 =>
            array(
              'name' => 'assigned_user_name',
              'label' => 'LBL_ASSIGNED_TO_NAME',
            ),
            1 =>
            array(
>>>>>>> 4888d25d1719bc87d035ee04ff9031be7d023969
              'name' => 'description',
              'comment' => 'Full text of the note',
              'label' => 'LBL_DESCRIPTION',
            ),
          ),
<<<<<<< HEAD
          5 => 
          array (
            0 => 
            array (
=======
          5 =>
          array(
            0 =>
            array(
>>>>>>> 4888d25d1719bc87d035ee04ff9031be7d023969
              'name' => 'date_entered',
              'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
              'label' => 'LBL_DATE_ENTERED',
            ),
<<<<<<< HEAD
            1 => 
            array (
=======
            1 =>
            array(
>>>>>>> 4888d25d1719bc87d035ee04ff9031be7d023969
              'name' => 'date_modified',
              'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
              'label' => 'LBL_DATE_MODIFIED',
            ),
          ),
        ),
<<<<<<< HEAD
        'LBL_PANEL_SOCIAL_FEED' => 
        array (
          0 => 
          array (
            0 => 
            array (
              'name' => 'facebook_user_c',
              'label' => 'LBL_FACEBOOK_USER_C',
            ),
            1 => 
            array (
              'name' => 'twitter_user_c',
              'label' => 'LBL_TWITTER_USER_C',
            ),
          ),
        ),
      ),
    ),
  ),
);
=======
        // 'LBL_PANEL_SOCIAL_FEED' =>
        // array(
        //   0 =>
        //   array(
        //     0 =>
        //     array(
        //       'name' => 'facebook_user_c',
        //       'label' => 'LBL_FACEBOOK_USER_C',
        //     ),
        //     1 =>
        //     array(
        //       'name' => 'twitter_user_c',
        //       'label' => 'LBL_TWITTER_USER_C',
        //     ),
        //   ),
        // ),
      ),
    ),
  ),
);
>>>>>>> 4888d25d1719bc87d035ee04ff9031be7d023969
