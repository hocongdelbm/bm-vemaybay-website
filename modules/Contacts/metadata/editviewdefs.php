<?php
$viewdefs['Contacts'] = array(
	'EditView' => array(
		'templateMeta' => array(
			'form' => array(
				'hidden' => array(
					'<input type="hidden" name="opportunity_id" value="{$smarty.request.opportunity_id}">',
					'<input type="hidden" name="case_id" value="{$smarty.request.case_id}">',
					'<input type="hidden" name="bug_id" value="{$smarty.request.bug_id}">',
					'<input type="hidden" name="email_id" value="{$smarty.request.email_id}">',
					'<input type="hidden" name="inbound_email_id" value="{$smarty.request.inbound_email_id}">',
				),
			),
			'maxColumns' => '2',
			'widths' => array(
				array('label' => '10', 'field' => '30'),
				array('label' => '10', 'field' => '30'),
			),
			'includes' => array(
				array('file' => 'custom/jqueryui/plugins/jquery.number.min.js'),
				array('file' => 'custom/jqueryui/plugins/mcautocomplete.js'),
				array('file' => 'modules/Contacts/js/view.edit.js'),
			),
			'useTabs' => false,
			'tabDefs' => array(
				'LBL_CONTACT_INFORMATION' => array(
					'newTab' => false,
					'panelDefault' => 'expanded',
				),
				'LBL_PANEL_ADVANCED' => array(
					'newTab' => false,
					'panelDefault' => 'expanded',
				),
			),
		),
		'panels' => array(
			'lbl_contact_information' => array(
				array(
					array(
						'name' => 'last_name',
						'customCode' => '
							<div class="d-flex gap-2">
								{html_options name="salutation" id="salutation" options=$fields.salutation.options selected=$fields.salutation.value}
								<input name="last_name" id="last_name" type="text" value="{$fields.last_name.value}">
							</div>',
					),
					array(
						'name' => 'phone_mobile',
						'label' => 'LBL_PHONE_MOBILE',
					)
				),

				array(
					array(
						'name' => 'zalo_id',
						'label' => 'LBL_ZALO_ID',
					),
					array(
						'name' => 'telegram_id',
						'label' => 'LBL_TELEGRAM_ID',
					),
				),

				array(
					array(
						'name' => 'birthdate',
						'label' => 'LBL_BIRTHDATE',
					),
					array(
						'name' => 'assigned_user_name',
						'label' => 'LBL_ASSIGNED_TO_NAME',
					),
				),

				array(
					array(
						'name' => 'description',
						'label' => 'LBL_DESCRIPTION',
					),
					array()
				),

				array(
					array(
						'name' => 'email1',
						'studio' => 'false',
						'label' => 'LBL_EMAIL_ADDRESS',
					),
				),
			),

			'LBL_PANEL_ADDRESS' => array(
				array(
					array(
						'name' => 'primary_address_street',
						'hideLabel' => true,
						'type' => 'address',
						'displayParams' => array(
							'key' => 'primary',
							'rows' => 2,
							'cols' => 30,
							'maxlength' => 150,
						),
					),
					array(
						'name' => 'alt_address_street',
						'hideLabel' => true,
						'type' => 'address',
						'displayParams' => array(
							'key' => 'alt',
							'copy' => 'primary',
							'rows' => 2,
							'cols' => 30,
							'maxlength' => 150,
						),
					),
				),
			),
			
			// 'LBL_PANEL_ADVANCED' => array(
			// 	array(
			// 		array(
			// 			'name' => 'lead_source',
			// 			'comment' => 'How did the contact come about',
			// 			'label' => 'LBL_LEAD_SOURCE',
			// 		),
			// 	),

			// 	array(
			// 		array(
			// 			'name' => 'report_to_name',
			// 			'label' => 'LBL_REPORTS_TO',
			// 		),
			// 		'campaign_name',
			// 	),
			// ),
		),
	),
);
