<?php
$viewdefs['Contacts'] = array(
	'DetailView' => array(
		'templateMeta' => array(
			'form' => array(
				'buttons' => array(
					'SEND_CONFIRM_OPT_IN_EMAIL' => EmailAddress::getSendConfirmOptInEmailActionLinkDefs('Contacts'),
					'EDIT',
					// 'DUPLICATE',
					'DELETE',
					array(
						'customCode' => '{$DETAIL_BOOKING}',
					),
					// 'FIND_DUPLICATES',
					// array(
					// 	'customCode' => '<input type="submit" class="button" title="{$APP.LBL_MANAGE_SUBSCRIPTIONS}" onclick="this.form.return_module.value=\'Contacts\'; this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$fields.id.value}\'; this.form.action.value=\'Subscriptions\'; this.form.module.value=\'Campaigns\'; this.form.module_tab.value=\'Contacts\';" name="Manage Subscriptions" value="{$APP.LBL_MANAGE_SUBSCRIPTIONS}"/>',
					// 	'sugar_html' => array(
					// 		'type' => 'submit',
					// 		'value' => '{$APP.LBL_MANAGE_SUBSCRIPTIONS}',
					// 		'htmlOptions' => array(
					// 			'class' => 'button',
					// 			'id' => 'manage_subscriptions_button',
					// 			'title' => '{$APP.LBL_MANAGE_SUBSCRIPTIONS}',
					// 			'onclick' => 'this.form.return_module.value=\'Contacts\'; this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$fields.id.value}\'; this.form.action.value=\'Subscriptions\'; this.form.module.value=\'Campaigns\'; this.form.module_tab.value=\'Contacts\';',
					// 			'name' => 'Manage Subscriptions',
					// 		),
					// 	),
					// ),
					/*
					'AOS_GENLET' => array(
						'customCode' => '<input type="button" class="button" onClick="showPopup();" value="{$APP.LBL_PRINT_AS_PDF}">',
					),
					'AOP_CREATE' => array(
						'customCode' => '{if !$fields.joomla_account_id.value && $AOP_PORTAL_ENABLED}<input type="submit" class="button" onClick="this.form.action.value=\'createPortalUser\';" value="{$MOD.LBL_CREATE_PORTAL_USER}"> {/if}',
						'sugar_html' => array(
							'type' => 'submit',
							'value' => '{$MOD.LBL_CREATE_PORTAL_USER}',
							'htmlOptions' => array(
								'title' => '{$MOD.LBL_CREATE_PORTAL_USER}',
								'class' => 'button',
								'onclick' => 'this.form.action.value=\'createPortalUser\';',
								'name' => 'buttonCreatePortalUser',
								'id' => 'createPortalUser_button',
							),
							'template' => '{if !$fields.joomla_account_id.value && $AOP_PORTAL_ENABLED}[CONTENT]{/if}',
						),
					),
					'AOP_DISABLE' => array(
						'customCode' => '{if $fields.joomla_account_id.value && !$fields.portal_account_disabled.value && $AOP_PORTAL_ENABLED}<input type="submit" class="button" onClick="this.form.action.value=\'disablePortalUser\';" value="{$MOD.LBL_DISABLE_PORTAL_USER}"> {/if}',
						'sugar_html' => array(
							'type' => 'submit',
							'value' => '{$MOD.LBL_DISABLE_PORTAL_USER}',
							'htmlOptions' => array(
								'title' => '{$MOD.LBL_DISABLE_PORTAL_USER}',
								'class' => 'button',
								'onclick' => 'this.form.action.value=\'disablePortalUser\';',
								'name' => 'buttonDisablePortalUser',
								'id' => 'disablePortalUser_button',
							),
							'template' => '{if $fields.joomla_account_id.value && !$fields.portal_account_disabled.value && $AOP_PORTAL_ENABLED}[CONTENT]{/if}',
						),
					),
					'AOP_ENABLE' => array(
						'customCode' => '{if $fields.joomla_account_id.value && $fields.portal_account_disabled.value && $AOP_PORTAL_ENABLED}<input type="submit" class="button" onClick="this.form.action.value=\'enablePortalUser\';" value="{$MOD.LBL_ENABLE_PORTAL_USER}"> {/if}',
						'sugar_html' => array(
							'type' => 'submit',
							'value' => '{$MOD.LBL_ENABLE_PORTAL_USER}',
							'htmlOptions' => array(
								'title' => '{$MOD.LBL_ENABLE_PORTAL_USER}',
								'class' => 'button',
								'onclick' => 'this.form.action.value=\'enablePortalUser\';',
								'name' => 'buttonENablePortalUser',
								'id' => 'enablePortalUser_button',
							),
							'template' => '{if $fields.joomla_account_id.value && $fields.portal_account_disabled.value && $AOP_PORTAL_ENABLED}[CONTENT]{/if}',
						),
					),
					*/
				),
			),
			'maxColumns' => '2',
			'widths' => array(
				array('label' => '10', 'field' => '30'),
				array('label' => '10', 'field' => '30'),
			),
			'includes' => array(
				array('file' => 'themes/SuiteP/js/reset.js'),
				array('file' => 'modules/Contacts/Contact.js'),
				array('file' => 'modules/Contacts/js/view.detail.js'),
			),
			'useTabs' => false,
			'tabDefs' => array(
				'LBL_CONTACT_INFORMATION' => array(
					'newTab' => true,
					'panelDefault' => 'expanded',
				),
				'LBL_PANEL_ADVANCED' => array(
					'newTab' => true,
					'panelDefault' => 'expanded',
				),
				'LBL_PANEL_ASSIGNMENT' => array(
					'newTab' => true,
					'panelDefault' => 'expanded',
				),
			),
		),
		'panels' => array(
			'lbl_contact_information' => array(
				array(
					array(
						'name' => 'name',
						'label' => 'LBL_NAME',
					),
					array(
						'name' => 'phone_mobile',
						'label' => 'LBL_PHONE_MOBILE',
					),
				),
				array(
					array(
						'name' => 'lead_source',
						'label' => 'LBL_LEAD_SOURCE',
					),
					array(
						'name' => 'email1',
						'studio' => 'false',
						'label' => 'LBL_EMAIL_ADDRESS',
					),
		
				),
				array(
					array(
						'name' => 'birthdate',
						'label' => 'LBL_BIRTHDATE',
					),
					array(
						'name' => 'points',
						'label' => 'LBL_POINTS',
					),
				),
				array(
					array(
						'name' => 'primary_address_street',
						'label' => 'LBL_PRIMARY_ADDRESS',
						'type' => 'address',
						'displayParams' => array(
							'key' => 'primary',
						),
					),
					array(
						'name' => 'alt_address_street',
						'label' => 'LBL_ALTERNATE_ADDRESS',
						'type' => 'address',
						'displayParams' => array(
							'key' => 'alt',
						),
					),
				),
				array(
					array(
						'name' => 'description',
						'comment' => 'Full text of the note',
						'label' => 'LBL_DESCRIPTION',
					),
					array(
						'name' => 'assigned_user_name',
						'label' => 'LBL_ASSIGNED_TO_NAME',
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
				)
			),

			'LBL_INFO_ZALO' => array(
				array(
					array(
						'name' => 'line_items',
						'label' => 'LBL_LINE_ITEMS',
						'customCode' => '{$INFO_ZALO}',
					),
				),
			),

			'LBL_INFO_CALLS' => array(
				array(
					array(
						'name' => 'line_items',
						'label' => 'LBL_LINE_ITEMS',
						'customCode' => '{$INFO_CALLS}',
					),
				),
			),

			'LBL_INFO_POINTS' => array(
				array(
					array(
						'name' => 'line_items',
						'label' => 'LBL_LINE_ITEMS',
						'customCode' => '{$INFO_POINTS}',
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
			// 		array(
			// 			'name' => 'campaign_name',
			// 			'label' => 'LBL_CAMPAIGN',
			// 		),
			// 	),
			// ),
		),
	),
);
